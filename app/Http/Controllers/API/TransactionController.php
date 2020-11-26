<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Illuminate\Support\Facades\Auth;
use App\Helpers\ResponseFormatter;
use Midtrans\Config;

class TransactionController extends Controller
{
    //
    public function all(Request $request)
    {
        $id =$request->input('id');
        $limit = $request->input('limit', 6);
        $item_id = $request->input('item_id');
        $status = $request->input('status');
        

        if($id)
        {
            $transaction = Transaction::with(['item','user'])->find($id);

            if($transaction) {
                return ResponseFormatter::success( $transaction, 'data berhasil diambil');
            } else {
                return ResponseFormatter::error( null, ' data tidak ada',404);
            }
        }
        $transaction = Transaction::with(['item', 'user'])->where('user_id',Auth::user()->id);

        if($item_id){
            $transaction->where('item_id', $item_id);
        }
        if($status){
            $transaction->where('status', $status);
        }


        

        return ResponseFormatter::success($transaction->paginate($limit), 'Data berhasil diambil');
    }

    //untuk update 
    public function update(Request $request, $id)
    {
        $transaction= Transaction::findOrFail($id);

        $transaction->update($request->all());

        return ResponseFormatter::success($transaction, 'transakasi berhasil diubah');
    }
    
    //untuk checkout
    public function checkout(Request $request)
    {
        $request-> validate([
            'item_id' => 'required|exist: item,id',
            'user_id' => 'required|exist: user,id',
            'quantity' => 'required',
            'total' => 'required',
            'status' => 'required',
        ]);
        $transaction = Transaction::create([
            'item_id' => $request->item_id,
            'user_id' => $request->user_id,
            'quantity' => $request->quantity,
            'total' => $request->total,
            'status'=> $request->status,
            'payment_ur' => ' ',
        ]);
        //konfigurasi midtrans
        Config::$serverKey = config('service.midtrans.serverKey');
        Config::$isProduction = config('service.midtrans.isProduction');
        Config::$isSantizied = config('service.midtrans.isSanitized');
        Config::$is3ds= config('service.midtrans.is3ds');

        //panggilan transaksi yang sebelumnya dibuat
        $transaction = Transaction::with(['item','user'])->find($transaction->id);

        //membuat transaksi midtrans
        $midtrans = [
            'transaction_details' => [
                'order_id' => $transaction->id,
                'gross_amount' => (int) $transaction->total,
            ],
            'customer_details' => [
                'frist_name' => $transaction->user->name,
                'email' => $transaction->user->email,
            ],
            'enabeled_payment' => ['gopay', 'bank_transfer'],
            'vtweb' => [],
        ];
        try {
            $paymentUrl = Snap::createTransaction($midtrans) ->redirect_url;
            $transaction->payment_url =$paymentUrl;
            $transaction->save();

        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 'transaksi gagal');
        }
    }
}
