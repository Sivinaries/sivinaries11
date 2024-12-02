<?php

namespace App\Http\Controllers;

use App\Models\Showcase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ShowcaseController extends Controller
{
    public function index()
    {
        $showcase = Cache::remember('showcases', now()->addMinutes(60), function () {
            return Showcase::all();
        });
    
        return view('showcase', compact('showcase'));
    }
    
    public function create()
    {
        return view('addshowcase');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'img' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($request->hasFile('img')) {
            $uploadedImage = $request->file('img');
            $imageName = $uploadedImage->getClientOriginalName();
            $imagePath = $uploadedImage->storeAs('img', $imageName, 'public');
            $data['img'] = 'img/' . $imageName;
        }

        Showcase::create($data);

        Cache::forget('showcase');
        Cache::remember('showcase', now()->addMinutes(60), function () {
            return Showcase::all();
        });

        return redirect(route('showcase'))->with('success', 'Showcase Sukses Dibuat !');
    }

    public function edit($id)
    {
        $showcase = Showcase::find($id);

        return view('editshowcase', compact('showcase'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'img' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $data = $request->only(['name', 'img']);

        if ($request->hasFile('img')) {
            $uploadedImage = $request->file('img');
            $imageName = $uploadedImage->getClientOriginalName();
            $imagePath = $uploadedImage->storeAs('img', $imageName, 'public');
            $data['img'] = 'img/' . $imageName;
        }

        Showcase::where('id', $id)->update($data);

        Cache::forget('showcases');
        Cache::remember('showcases', now()->addMinutes(60), function () {
            return Showcase::all();
        });

        return redirect(route('showcase'))->with('success', 'Showcase Sukses Diupdate !');
    }

    public function destroy($id)
    {
        Showcase::destroy($id);

        Cache::forget('showcases');

        return redirect(route('showcase'))->with('success', 'Showcase Berhasil Dihapus !');
    }
}
