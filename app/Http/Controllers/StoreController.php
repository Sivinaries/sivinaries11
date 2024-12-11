<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class StoreController extends Controller
{
    public function index()
    {
        $stores = Cache::remember('stores', now()->addMinutes(60), function () {
            return Store::all();
        });

        return view('store', compact('stores'));
    }

    public function create()
    {
        return view('addstore');
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'store' => 'required',
            'address' => 'required',
        ]);

        $data['user_id'] = $user->id;

        Store::create($data);

        return redirect(route('dashboard'))->with('success', 'Store registered!');
    }

    public function show(Store $store)
    {
        //
    }

    public function edit(Store $store)
    {
        //
    }

    public function update(Request $request, Store $store)
    {
        //
    }

    public function destroy(Store $store)
    {
        //
    }
}
