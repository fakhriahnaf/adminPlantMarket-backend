<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    //
    public function all(Request $request)
    {
        $id =$request->input('id');
        $limit = $request->input('limit', 6);
        $name = $request->input('name');
        $price_from = $request->input('price_from');
        $price_to = $request->input('price_to');

        $rate_from = $request->input('rate_from');
        $rate_to =$request->input('rate-to');

        if($id)
        {
            $item = Item::find($id);

            if($item) {
                return ResponseFormatter::success( $item, 'data berhasil diambil');
            } else {
                return ResponseFormatter::error( null, ' data tidak ada',404);
            }
        }
        $item = Item::query();

        if($name){
            $item->where('name', 'like', '%' .$name, '%');
        }

        if($types){
            $item->where('types', 'like', '%' .$types, '%');
        }
        if($price_from){
            $item->where('price', '>=' , $price_from);
        }
        if($price_to){
            $item->where('price', '<=' , $price_to);
        }
        if($rate_from){
            $item->where('rate', '>=' , $price_from);
        }
        if($rate_to){
            $item->where('rate', '<=' , $rate_to);
        }

        return ResponseFormatter::success($item->paginate($limit), 'Data berhasil diambil');
    }
}
