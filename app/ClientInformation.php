<?php

namespace App;

class ClientInformation extends Model
{
    protected $table = 'client_info_stores';

    /**
     * Метод получения складов клиента
     *
     * @return ClientInformation
     * @author Mikhail Shapshay
     */
    public function getStores()
    {
        return static
            ::select(
                    'id as id',
                    'store_contact as contact',
                    'store_address as address',
                    'store_name as name',
                    'store_position as position',
                    'store_phone as phone')
            ->where('client_id', auth()->user()->user_code1c)->get();
    }

    /**
     * Метод записи складов клиента
     *
     * @return int
     * @author Mikhail Shapshay
     */
    public function addStore($request)
    {
        return static::insertGetId([
            'client_id' => auth()->user()->user_code1c,
            'store_contact' => $request->store_contact,
            'store_address' => $request->store_address,
            'store_name' => $request->store_name,
            'store_position' => $request->store_position,
            'store_phone' => $request->store_phone,
        ]);
    }

    /**
     * Метод удаления складов клиента
     *
     * @author Mikhail Shapshay
     */
    public function deleteStore($id)
    {
        return static::where('id', $id)->delete();
    }

    /**
     * Связи
     */
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_code1c', 'client_id');
    }
}
