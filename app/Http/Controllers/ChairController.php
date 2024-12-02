<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\User;
use App\Models\Order;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ChairController extends Controller
{

    public function index()
    {
        $users = User::where('level', 'Chair')->get();

        return view('chair', compact('users'));
    }


    public function create()
    {

        return view('addchair');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $qrToken = Str::random(32);

        $user = new User();
        $user->name = $validatedData['name'];
        $user->password = bcrypt('123456');
        $user->level = 'Chair';
        $user->qr_token = $qrToken;
        $user->save();

        $token = $user->createToken('auth_token')->plainTextToken;

        Cache::forget('users');
        Cache::remember('users', now()->addMinutes(60), function () {
            return User::all();
        });

        return redirect('/chair')->with('toast_success', 'Registration successful!')->with('access_token', $token);
    }

    public function destroy($id)
    {
        Order::whereHas('cart', function ($query) use ($id) {
            $query->where('user_id', $id);
        })->delete();

        Cart::where('user_id', $id)->delete();
        User::destroy($id);

        return redirect(route('chair'))->with('success', 'Kursi Berhasil Dihapus !');
    }

}
