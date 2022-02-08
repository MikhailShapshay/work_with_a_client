<?php

namespace App;

use Illuminate\Support\Facades\DB;

class ClientSettings extends Model
{
    protected $table = 'client_settings';
    public $timestamps = false;

    /**
     * Метод проверки прохождения опроса
     *
     * @author Mikhail Shapshay
     */
    public static function getQuestionnaire()
    {
        $qf = DB::table('client_settings')
            ->select('questionnaire_finished')
            ->where('client_id', client()->client_code1c)
            ->first();

        if(is_null($qf)){
            static::insert([
                'client_id' => client()->client_code1c,
                'forbid_extra_discount_on_pvl' => 0,
                'questionnaire_finished' => 0,
            ]);
            return 0;
        }else{
            return $qf->questionnaire_finished;
        }
    }
}
