<?php

namespace App;

class ClientInformationSettings extends Model
{
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = 'client_id';
    protected $table = 'client_info_settings';
    protected $casts = [
        'setting_product' => 'array'
    ];
    public $client_categories = [
        ["title" => "A", "value" => "A"],
        ["title" => "B", "value" => "B"],
        ["title" => "C", "value" => "C"],
        ["title" => "D", "value" => "D"],
    ];
    public $client_loyalty = [
        ["title" => "1 год", "value" => "1 год"],
        ["title" => "2-3", "value" => "2-3"],
        ["title" => "3-5", "value" => "3-5"],
        ["title" => "От 5 лет", "value" => "От 5 лет"],
        ["title" => "Более 20", "value" => "Более 20"],
        ["title" => "Новый клиент", "value" => "Новый клиент"],
    ];
    public $client_sector = [
        ["title" => "ПЗМ", "value" => "ПЗМ"],
        ["title" => "Магазин", "value" => "Магазин"],
        ["title" => "АЗС", "value" => "АЗС"],
    ];

    /**
     * Метод редактирования данных о клиенте
     *
     * @author Mikhail Shapshay
     */
    public function updateSettings($request)
    {
        static::updateOrCreate(
            ['client_id' => $request->client_id],
            [
                'setting_category' => $request->client_category,
                'setting_loyalty' => $request->client_loyalty,
                'setting_product' => json_encode($request->product_category),
                'setting_sector' => $request->sector
            ]
        );
    }

    /**
     * Метод получения данных о клиенте
     *
     * @author Mikhail Shapshay
     */
    public function getValues($id)
    {
        $settings = static::where('client_id',$id)->get()->toArray();
        return empty($settings) ? [] : static::where('client_id',$id)->get()->toArray()[0] ;
    }

    /**
     * Связи
     */
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_code1c', 'client_id');
    }
}
