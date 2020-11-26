<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MidtransController extends Controller
{
    //
    public function callback(Request $request)
    {
        //set konfigurasi midtrans
        Config::$serverKey = config('service.midtrans.serverKey');
        Config::$isProduction = config('service.midtrans.isProduction');
        Config::$isSantizied = config('service.midtrans.isSanitized');
        Config::$is3ds= config('service.midtrans.is3ds');

        //buat instance midtrans configuration

    }
}
