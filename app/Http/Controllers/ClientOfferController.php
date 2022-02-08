<?php

namespace App\Http\Controllers;

use App\ClientOffer;
use Exception;
use Illuminate\Http\Request;

class ClientOfferController extends Controller
{
    public function index()
    {
        return view('offer.index');
    }

    /**
     * Метод проверки типа клиента
     *
     * @author Mikhail Shapshay
     */
    public function  phis_yur(Request $request)
    {
        if($request->phis_yur == '0') {
            return view('offer.offer_dog1');
        } else {
            return view('offer.offer_dog2');
        }
    }

    /**
     * Метод регистрации принятия офера
     *
     * @author Mikhail Shapshay
     */
    public function  registration(Request $request)
    {
        $data = $request->validate([
            'phis_yur' => 'required'
        ]);
        $id = auth()->user()->user_client_code1c;
        $offer['ua_browser'] = '';
        $offer['us_device'] = '';
        $offer['us_platform'] = '';
        try{
            $temp = get_browser($_SERVER['HTTP_USER_AGENT'], true);
            $offer['ua_browser'] = $temp['browser'];
            $offer['us_device'] = $temp['device_type'];
            $offer['us_platform'] = $temp['platform'];
        }catch (Error $e){}
        $save = new ClientOffer();
        $save->client_code1c = $id;
        $save->phis_yur = $data['phis_yur'];
        $save->ua_browser = $offer['ua_browser'];
        $save->us_device = $offer['us_device'];
        $save->us_platform = $offer['us_platform'];
        $save->ip = $_SERVER['REMOTE_ADDR'];
        $save->agreement_data = \Carbon\Carbon::now()->format('Y-m-d H:i:s ');
        $save->save();

        if($save) {
            return redirect()->route('home')->with('flash', "Вы успешно согласились с публичной офертой!");
        } else {
            return redirect()->route('home')->withErrors('flash',"Ошибка, при сохранени попробуйте еще раз.");
        }
    }

    /**
     * Страница офера
     *
     * @author Mikhail Shapshay
     */
    public function show()
    {
        if (! client()) {
            return back()->withFlash('Клиент не выбран.');
        }

        $offerAgreement = ClientOffer::where('client_code1c', client()->id)->first();

        return view('offer.show', compact('offerAgreement'));
    }
}
