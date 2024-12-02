<?php

namespace App\Http\Controllers;

use App\Models\Discount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DiscountController extends Controller
{
    public function index()
    {
        $discounts = Cache::remember('discounts', now()->addMinutes(60), function () {
            return Discount::all();
        });

        return view('discount', compact('discounts'));
    }

    public function create()
    {
        return view('adddiscount');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'percentage' => 'required',
        ]);

        Discount::create($data);

        Cache::forget('discounts');
        Cache::remember('discounts', now()->addMinutes(60), function () {
            return Discount::all();
        });

        return redirect(route('discount'))->with('success', 'Discount Sukses Dibuat !');
    }

    public function edit($id)
    {
        $discount = Discount::find($id);
        return view('editdiscount', compact('discount'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'percentage' => 'required',
        ]);

        $data = $request->only(['name', 'nominal']);

        Discount::where('id', $id)->update($data);

        Cache::forget('discounts');
        Cache::remember('discounts', now()->addMinutes(60), function () {
            return Discount::all();
        });

        return redirect(route('discount'))->with('success', 'Discount Sukses Diupdate !');
    }

    public function destroy($id)
    {
        Discount::destroy($id);

        Cache::forget('discounts');

        return redirect(route('discount'))->with('success', 'Discount Berhasil Dihapus !');
    }
}
