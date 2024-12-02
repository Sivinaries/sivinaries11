<?php

namespace App\Http\Controllers;

use App\Models\Profil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProfilController extends Controller
{
    public function index()
    {
        $profil = Cache::remember('profil', now()->addMinutes(60), function () {
            return Profil::all();
        });

        return view('profil', compact('profil'));
    }
    
    public function edit($id)
    {
        $profil = Profil::find($id);
        return view('editprofil', ['profil' => $profil]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'alamat' => 'required',
            'jam' => 'required',
            'no_wa' => 'required',
            'deskripsi' => 'required',
        ]);
    
        $data = $request->only(['name', 'alamat', 'jam', 'no_wa', 'deskripsi']);
    
        Profil::where('id', $id)->update($data);
    
        Cache::forget('profil');
        Cache::remember('profil', now()->addMinutes(60), function(){
            return Profil::all();
        });
        
        return redirect(route('profil'))->with('success', 'Profil Sukses Diupdate!');
    }
}
