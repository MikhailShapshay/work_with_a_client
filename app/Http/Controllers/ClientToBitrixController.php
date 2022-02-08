<?php

namespace App\Http\Controllers;

use App\ClientToBitrix;
use App\Client;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ClientToBitrixController extends Controller
{
    public $clientsLimit = 50;
    protected $webhook = 'https://example.kz/rest/1/token_key/profile/';
    protected $filials = array(
        519=>"Актау",
        520=>"Актобе",
        521=>"Алматы",
        522=>"Астана",
        523=>"Атырау",
        524=>"Караганда",
        525=>"Кокшетау",
        526=>"Костанай",
        527=>"Кызылорда",
        528=>"Павлодар",
        529=>"Петропавловск",
        530=>"Семей",
        531=>"Тараз",
        532=>"Уральск",
        533=>"Усть-Каменогорск",
        534=>"Шымкент"
    );

    public function __construct()
    {
        $this->clientsNotBitrix();
    }

    /**
     * Метод получения новых клиентов неотправленных в CRM
     *
     * @return bool
     * @author Mikhail Shapshay
     */
    public function clientsNotBitrix()
    {
        $clientsNotBitrixArr = Client::has('toBitrix', '=', 0)->with('managers','phoneNumbers','ownerInfo', 'categories', 'users', 'filial')->orderBy('client_changedate', 'desc')->limit($this->clientsLimit)->get();
        $data = array();
        $i = 0;
        foreach ($clientsNotBitrixArr as $client)
        {
            $data[$i]['client'] =  $client->toArray();
            $data[$i]['client']['managers'] = $this->unique_multidim_array($data[$i]['client']['managers'], 'user_code1c');
            $this->clientSendBitrix($data[$i], 1);
            $i++;
        }
        echo "<p>";
        var_dump($data);

        return true;
    }

    /**
     * Метод отправки новых клиентов в CRM
     *
     * @return bool
     * @author Mikhail Shapshay
     */
    public function clientSendBitrix($client_data, $send_type)
    {
        $jsonArr = $client_data['client'];
        // отправка данных компании в Битрикс
        $method = 'lists.element.add';
        $params = array(
            'IBLOCK_TYPE_ID' => 'lists',
            'IBLOCK_ID' => 71,
            'ELEMENT_CODE' => 'client'.date("YmdHis").Str::random(),
            'fields' => array(
                'NAME' => 'Клиент CRM',
                'PROPERTY_695' => array_search($jsonArr['filial']['filial_name'], $this->filials),
                'PROPERTY_694' => $jsonArr['client_name'],
                'PROPERTY_699' => $jsonArr['client_code1c'],
                'PROPERTY_697' => json_encode($jsonArr, JSON_UNESCAPED_UNICODE),
                'PROPERTY_698' => 535 //536
            )
        );

        $resultArr = bx24query($this->webhook, $method, $params);


        $save = new ClientToBitrix();
        $save->client_code1c = $client_data['client']['client_code1c'];
        $save->send_type = $send_type;
        $save->send_ok = 1;
        $save->send_data = \Carbon\Carbon::now()->format('Y-m-d H:i:s ');
        $save->save();
        
        return true;
    }

    /**
     * Метод уникальный ассоциативный массив по ключу
     *
     */
    public function unique_multidim_array($array, $key) {
        $temp_array = array();
        $i = 0;
        $key_array = array();

        foreach($array as $val) {
            if (!in_array($val[$key], $key_array)) {
                $key_array[$i] = $val[$key];
                $temp_array[$i] = $val;
            }
            $i++;
        }
        return $temp_array;
    }


}
