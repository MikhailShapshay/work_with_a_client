<?php

namespace App;

use DB;
use mysql_xdevapi\Collection;
use Str;
use App\Traits\Client\HasColumnNamesNormalized;

class Client extends OneSModel
{
    use HasColumnNamesNormalized;

    protected $table = '1c_clients';
    public $primaryKey = 'client_code1c';

    protected $appends = [
        'id',
        'name',
        'filial_id',
        'addresses',
        'consignment_allowed',
        'iin',
        'deny_remote_stores',
        'debt',
        'due_debt',
        'due_debt_days',
    ];

    public const NULL_CLIENT_ID = '00000000-0000-0000-0000-000000000000';

    /**
     * Метод Проверки на доступность спецпрайса
     *
     * @return bool
     * @author Mikhail Shapshay
     */
    public function getHasSpecialPriceListAttribute()
    {
        return $this->client_pricelist != null;
    }

    /**
     * Метод Проверки принятия офера
     *
     * @return bool
     * @author Mikhail Shapshay
     */
    public function acceptedOfferAgreement()
    {
        return (bool) $this->offerAgreement;
    }

    /**
     * Связь с таблицей оферов
     *
     * @author Mikhail Shapshay
     */
    public function offerAgreement()
    {
        return $this->hasOne(ClientOffer::class, 'client_code1c', 'client_code1c');
    }

    /**
     * Связь с таблицей Bitrix
     *
     * @author Mikhail Shapshay
     */
    public function toBitrix()
    {
        return $this->hasOne(ClientToBitrix::class, 'client_code1c', 'client_code1c');
    }

    /**
     * Метод вывода дебета клиента
     *
     * @return bool
     * @author Mikhail Shapshay
     */
    public function getDisplayDebtAttribute()
    {
        if ($this->overpayment) {
            return 'Переплата: ' . Number::formatAsDecimalPrice($this->overpayment);
        } elseif ($this->hasDebt()) {
            return 'Долг: ' . Number::formatAsDecimalPrice($this->debt);
        } else {
            return 'Долга нет';
        }
    }

    /**
     * Метод блокировки заказов
     *
     * @author Mikhail Shapshay
     */
    public function blockFromMakingOrders()
    {
        ClientOrderCheckoutBlock::create([
            'client_id' => $this->id,
            'author_id' => ''
        ]);
    }

    /**
     * Метод вывода проверки блокировки
     *
     * @return bool
     * @author Mikhail Shapshay
     */
    public function isBlockedFromMakingOrders()
    {
        return ClientOrderCheckoutBlock::where('client_id', $this->id)->count() !== 0;
    }

    /**
     * Получить клиентов привязанных к менеджеру
     *
     * @param $query
     * @param $manager
     * @return mixed
     * @author Mikhail Shapshay
     */
    public function scopeAttachedTo($query, $manager)
    {
        return $query->whereHas('managers', function ($q) use ($manager) {
            $q->where('clman_person_code1c', $manager->user_code1c);
        });
    }

    public function scopeLike($query, $searchQuery)
    {
        return $query
            ->where('client_name', 'like', "%{$searchQuery}%");
    }

    public function scopeLikeQuery($query, $searchQuery)
    {
        return $query->where(function ($q) use ($searchQuery) {
            $q
                ->where('client_name', 'like', "%{$searchQuery}%")
                ->orWhere('client_code1c', 'like', "%{$searchQuery}%");
        });
    }

    public function scopeInDiscountDocument($query, $documentId)
    {
        return $query->whereHas('discountDocuments', function ($q) use ($documentId) {
            return $q->where('document_id', $documentId);
        })->groupBy('client_code1c');
    }

    public function scopeNameIn($query, $names)
    {
        return $query->whereIn('client_name', $names);
    }

    public function scopeSlavesOf($query, $userId)
    {
        $user = User::findOrFail($userId);

        if ($user->hasRole(Role::SUPER_ADMIN) || $user->hasPermissionTo(Permission::SEE_ALL_CLIENTS)) {
            // -----------------------------------------------------
            // Менедежеры с разрешением "полный доступ",
            // а так же с разрешением "все клиенты"
            // могут просматривать всех клиентов.
            // Поэтому не накладываем никаких фильтров
            // -----------------------------------------------------
        }

        // -----------------------------------------------------
        // Менедежеры с разрешением "директор филиала",
        // а так же с разрешением на "просмотр всех клиентов филиала"
        // могут просматривать всех клиентов своего филиала
        // -----------------------------------------------------
        elseif ($user->hasRole(Role::FILIAL_DIRECTOR) || $user->hasPermissionTo(Permission::SEE_ALL_CLIENTS_IN_FILIAL)
        ) {
            $filials = collect([ $user->filial_id ]);

            if ($user->filial_id == Filial::NUR_SULTAN) {
                $filials->push(Filial::KOKSHETAU);
            }

            if ($user->id == '881be559-0ed6-11e9-9be3-00155d648092') {  // Ташимов Айбек региональный супервайзер
                $filials->push(Filial::ALMATY);
                $filials->push(Filial::TARAZ);
                $filials->push(Filial::SHYMKENT);
                $filials->push(Filial::KYZYLORDA);
            }

            if ($user->email == 'ust_sv02@kulanoil.kz') {  // Любимов Олег региональный супервайзер
                $filials->push(Filial::PAVLODAR);
                $filials->push(Filial::SEMEY);
                $filials->push(Filial::USK_KAMENOGORSK);
            }

            if ($user->email == 'gusmanov.k@kulanoil.kz') {  // региональный супервайзер
                $filials->push(Filial::KYZYLORDA);
                $filials->push(Filial::TARAZ);
            }

            $query->whereIn('client_filial_code1c', $filials);
        }

        // -----------------------------------------------------
        // Простой менеджер может просматривать только клиентов
        // привязынных к нему через таблицу 1c_client_person
        // -----------------------------------------------------
        else {
            $query->attachedTo($user);
        }

        return $query;
    }

    /**
     * Связь с таблицей скидок
     *
     * @author Mikhail Shapshay
     */
    public function discountDocuments()
    {
        return $this->belongsToMany(DiscountDocument::class, 'discounts', 'client_id', 'document_id');
    }

    /**
     * Метод вывода адресов доставки клиента
     *
     * @return Collection
     * @author Mikhail Shapshay
     */
    public function allAddresses()
    {
        return collect($this->addresses)->concat($this->deliveryAddresses()->pluck('address'));
    }

    /**
     * Метод вывода всех клиентов
     *
     * @return Collection
     * @author Mikhail Shapshay
     */
    public function allClients()
    {
        return DB::table($this->table)->select('client_code1c', 'client_name')->get();
    }


    /**
     * Найти клиентов в филиале или списке филиалов
     *
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public static function findByFilial($id)
    {
        return static::whereIn('client_filial_code1c', (array) $id)->get();
    }

    public static function findByFilialRaw($id)
    {
        return DB::table(static::getTableName())
            ->whereIn('client_filial_code1c', (array) $id)
            ->get();
    }

    /**
     * Метод проверки возможности заказа по дебету
     *
     * @return bool
     * @author Mikhail Shapshay
     */
    public function canCheckoutOrdersWithPastDueDebt()
    {
        // до 1000 тенге при любом сроке
        if ($this->due_debt < 1000) {
            return true;
        }

        // при сроке до 2 дней при любой сумме
        if ($this->due_debt_days <= 2) {
            return true;
        }

        // меньше 2 недель и при сумме до 10 тыс тенге
        return $this->due_debt_days <= 14 &&
               $this->due_debt <= 10000;
    }

    public function hasDebt()
    {
        return $this->client_debd > 0;
    }

    /**
     * Связи
     */
    public function filial()
    {
        return $this->belongsTo(Filial::class, 'client_filial_code1c', 'filial_code1c');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'user_client_code1c', 'client_code1c');
    }

    public function managers()
    {
        return $this
            ->belongsToMany(User::class, '1c_client_person', 'clman_client_code1c', 'clman_person_code1c')
            ->withPivot('clman_client_code1c', 'clman_person_code1c', 'clman_prgr_code1c');
    }

    public function specialPriceList()
    {
        return $this->belongsToMany(
            Product::class,
            SpecialPriceList::getTableName(),
            'code_1c', // pivot keys
            'prod_code_1c', // pivot keys
            'client_pricelist',
            'prod_code1c'
        )->withPivot('price');
    }

    public function deliveryAddresses()
    {
        return $this->hasMany(DeliveryAddress::class, 'client_id');
    }

    public function phoneNumbers()
    {
        return $this->hasMany(ClientPhoneNumber::class, 'client_id', 'client_code1c');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'client_id');
    }

    public function partnerSettings()
    {
        return $this->hasOne(ClientPartnerSetting::class, 'client_code1c')
            ->withDefault(ClientPartnerSetting::defaultSetting());
    }

    public function ownerInfo()
    {
        return $this->hasOne(ClientInformationOwner::class, 'client_id', 'client_code1c');
    }

    public function clientInfo()
    {
        return $this->hasMany(ClientInformation::class, 'client_id', 'client_code1c');
    }

    public function clientSettings()
    {
        return $this->hasOne(ClientInformationSettings::class, 'client_id', 'client_code1c');
    }
    //переименовать
    public function clientPersonalSettings()
    {
        return $this->hasOne(ClientSettings::class, 'client_id', 'client_code1c');
    }

    public function categories()
    {
        return $this->hasMany(ClientCategory::class, 'client_id');
    }
}
