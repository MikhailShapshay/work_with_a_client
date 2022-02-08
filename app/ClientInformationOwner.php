<?php

namespace App;

use Illuminate\Support\Facades\DB;

class ClientInformationOwner extends Model
{
    protected $table = 'client_info_owner';
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = 'client_id';

    /**
     * Метод получения данных о владельце фирмы
     *
     * @return ClientInformationOwner
     * @author Mikhail Shapshay
     */
    public function getOwnerData()
    {
        return static::where('client_id', auth()->user()->user_code1c)->first();
    }

    /**
     * Метод редактирования данных о владельце фирмы
     *
     * @author Mikhail Shapshay
     */
    public function updateOwnerData($data)
    {
        static::updateOrCreate(
            ['client_id' => auth()->user()->user_code1c],
            [
                'owner_company_name' => $data->owner_company_name,
                'owner_address' => $data->owner_address,
                'owner_bd' => $data->owner_bd,
                'owner_full_name' => $data->owner_full_name,
                'owner_phone' => $data->owner_phone,
            ]
        );
    }

    /**
     * Метод получения владельце фирмы с днем рождения
     *
     * @return ClientInformationOwner
     * @author Mikhail Shapshay
     */
    public static function getTodayBirthdays()
    {
        return static::whereRaw('curdate() = owner_bd')->get()->toArray();
    }

    /**
     * Связи
     */
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_code1c', 'client_id');
    }
}
