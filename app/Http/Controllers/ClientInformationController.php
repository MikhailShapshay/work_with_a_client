<?php

namespace App\Http\Controllers;

use App\ClientInformation;
use App\ClientInformationOwner;
use Illuminate\Http\Request;

class ClientInformationController extends Controller
{
    public $clientInfo;
    public $clientInfoOwner;

    public function __construct()
    {
        $this->middleware('has-client');
        $this->clientInfo = new ClientInformation();
        $this->clientInfoOwner = new ClientInformationOwner();
    }

    public function index()
    {
        return view('client-information.index');
    }

    /**
     * Метод получения складов клиента
     *
     * @return ClientInformation
     * @author Mikhail Shapshay
     */
    public function getStores()
    {
        return $this->clientInfo->getStores();
    }

    /**
     * Метод записи складов клиента
     *
     * @return int
     * @author Mikhail Shapshay
     */
    public function addStore(Request $request)
    {
        $result = $this->clientInfo->addStore($request);
//        return response()->json(['result' => $result]);
        return back();
    }

    /**
     * Метод удаления складов клиента
     *
     * @author Mikhail Shapshay
     */
    public function deleteStore(Request $request)
    {
        $result = $this->clientInfo->deleteStore($request->id);
//        return response()->json(['result' => $result]);
        return back();

    }

    /**
     * Метод получения данных о владельце фирмы
     *
     * @return ClientInformationOwner
     * @author Mikhail Shapshay
     */
    public function getOwnerData(Request $request)
    {
        return $this->clientInfoOwner->getOwnerData();
    }

    /**
     * Метод редактирования данных о владельце фирмы
     *
     * @author Mikhail Shapshay
     */
    public function updateOwner(Request $request)
    {
        $this->clientInfoOwner->updateOwnerData($request);
        return back();
    }
}
