<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\ItemRequest;


class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $itemz = Item::paginate(10);

        return view('items.index', ['itemz' => $itemz]);
        // return view('items.index')->with([
        //     'item' => $item
        // ]);
        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        return view('items.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ItemRequest $request)
    {
        //
        $data = $request->all();
        //$data['profile_photo_path'] = $request->file('profile_photo_path')->store('asssets/user','public');
        $data['picturePath'] = $request->file('picturePath')->store('assets/item', 'public');
        Item::create($data);

        return redirect()->route('items.index');

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Item $item)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Item $item)
    {
        //
        return view('items.edit', [
            'item' => $item
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ItemRequest $request, Item $item)
    {
        //
        $data = $request->all();

        if($request->file('picturePath'))
        {
            $data['picturePath'] = $request->file('picturePath')->store('assets/item', 'public');
        }

        $item->update($data);

        return redirect()->route('items.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Item $item)
    {
        //
        $item->delete();

        return redirect()->route('items.index');
    }
}
