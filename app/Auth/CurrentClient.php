<?php

namespace App\Auth;

use App\User;
use App\Client;
use Illuminate\Support\Facades\Session;

class CurrentClient extends Client
{
    protected static $client;

    public $user;

    /**
     * Получить текущего клиента
     *
     * Если вошел менеджер, то вернуть выбранного клиента.
     * Если вошел клиент, то вернуть самого клиента
     *
     * @return CurrentClient | null
     * @author Mikhail Shapshay
     */
    public static function get()
    {
        //logger('static property client is ' . serialize(optional(static::$client)->toArray() ?? ''));
        //logger('session current client is  ' . Session::get('CURRENT_CLIENT_ID'));

        // -----------------------------------------------------
        // Если клиент еще не инициализирован
        // и в сессии есть ID клиента,
        // то сохранить его в статичное свойство
        // для доступа к нему на протяжении всего запроса,
        // без необходимости получать его из БД каждый раз
        // -----------------------------------------------------
        if (! static::$client && Session::has('CURRENT_CLIENT_ID')) {
            static::$client = static::find(Session::get('CURRENT_CLIENT_ID'));
            //logger('get method: client model in static property is ' . serialize(static::$client->toArray()));

            // -----------------------------------------------------
            // Загрузить филиал клиента
            // -----------------------------------------------------
            if (static::$client) {
                static::$client->load('filial');
            }
        }

        // -----------------------------------------------------
        // Если пользователь клиента еще не инициализирован
        // и в сессии есть ID пользователя,
        // то сохранить его в статичное свойство
        // для доступа к нему на протяжении всего запроса,
        // без необходимости получать его из БД каждый раз
        // -----------------------------------------------------
        if (static::$client && ! static::$client->user && Session::has('CURRENT_CLIENT_USER_ID')) {
            static::$client->user = User::find(Session::get('CURRENT_CLIENT_USER_ID'));

            if (static::$client && static::$client->user) {
                static::$client->user->load('filial', 'password');
            }
        }

        return static::$client;
    }

    /**
     * Установить текущего клиента
     *
     * Если вошел менеджер, то вернуть выбранного клиента.
     * Если вошел клиент, то вернуть самого клиента
     *
     * @return CurrentClient | null
     * @author Mikhail Shapshay
     */
    public static function setClient($id)
    {
        // -----------------------------------------------------
        // Сохранить id клиента в сессию
        // для использования между запросами
        // -----------------------------------------------------
        //logger('puting CURRENT_CLIENT_ID into session');
        Session::put('CURRENT_CLIENT_ID', $id);
        Session::save();
        //logger('persisted CURRENT_CLIENT_ID ' . Session::get('CURRENT_CLIENT_ID'));

        // -----------------------------------------------------
        // Сохранить клиента в статичное свойство
        // для сохранения на протяжении текущего запроса
        // -----------------------------------------------------
        //logger('puting client model into static property');
        static::$client = Client::find($id);
        //logger('client model in static property is ' . serialize(static::$client->toArray()));

        if (static::$client) {
            //logger('client is persisted in static peroprty. Loading filial');
            static::$client->load('filial');
        }
        //logger('done');
    }

    /**
     * Установить текущего пользователя
     *
     * @author Mikhail Shapshay
     */
    public static function setUser($id)
    {
        $client = static::get();

        if (! $client) {
            return;
        }

        // -----------------------------------------------------
        // Сохранить id пользователя в сессию
        // для сохранения между запросами
        // -----------------------------------------------------
        Session::put('CURRENT_CLIENT_USER_ID', $id);
        Session::save();

        // -----------------------------------------------------
        // Сохранить пользователя в статичное свойство
        // для сохранения на протяжении текущего запроса
        // -----------------------------------------------------
        $client->user = User::find($id);

        if ($client->user) {
            $client->user->load('filial', 'password');
        }
    }

    /**
     * Сбросить текущего клиента т пользователя
     *
      @return CurrentClient | null
     * @author Mikhail Shapshay
     */
    public static function unset()
    {
        Session::forget([ 'CURRENT_CLIENT_ID', 'CURRENT_CLIENT_USER_ID' ]);

        static::$client = null;
    }
}
