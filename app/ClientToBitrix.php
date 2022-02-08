<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ClientToBitrix extends Model
{
    protected $guarded = [];
    protected $table = 'client_to_bitrix';
    public $timestamps = false;

    /**
     * Связи
     */
    public function client()
    {
        return $this->belongsTo('App\Client', 'client_code1c', 'client_code1c');
    }
}
