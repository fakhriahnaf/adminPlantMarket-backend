<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Notification;

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
        $notification = new Notification();

        //asign variable
        $status = $notification->transaction_status;
        $type = $notification->payment_type;
        $fraud = $notification->fraud_status;
        $order_id = $notification->order_id;

        //cari berdasarkan id
        $transaction= Transaction::findOrFail($order_id);

        //handle notifikasi status midtrans
        if($status == 'capture')
        {
            if($type == 'credit_card')
            {
                if($fraud == 'chalenge')
                {
                    $transaction->status = 'PENDING';
                } else {
                    $transaction->status = 'SUCCESS';
                }
            }
        }
        else if ($status == 'pending')
        {
            $transaction->status='PENDING';
        }
        else if ($status == 'settlement')
        {
            $transaction->status = 'SUCCESS';
        }
        else if ($status == 'deny')
        {
            $transaction->status = 'CANCELLED'; 
        }
        else if ($status == 'expired')
        {
            $transaction->status = 'CANCELLED'; 
        }
        else if ($status == 'cancel')
        {
            $transaction->status = 'CANCELLED'; 
        }
    }

    public function unfinish() 
    {
        return view('midtrans.unfinish');
    }
    public function error()
    {
        return view('midtrans.error');
    }

    public function success()
    {
        return view('midtrans.success');
    }
}
