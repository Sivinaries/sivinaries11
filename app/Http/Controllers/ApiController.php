<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ApiController extends Controller
{
    public function category()
    {
        
        $category = Cache::remember('categories_with_menus', now()->addMinutes(60), function () {
            return Category::with(['menus'])->get();
        });
    
        return response()->json($category, 200);
    }

    public function cart()
    {
        // $cart = Cart::with('cartMenus')->get();
        $user = auth()->user();

        $cart = $user->carts()->with(['cartMenus.menu'])->latest()->first();

        if (!$cart) {
            $cart = $user->carts()->create([]);
        }

        return response()->json($cart, 200);
    }
    
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);
    
        $credentials = $request->only('email', 'password');
    
        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    
        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;
    
        return response()->json(['token' => $token, 'user' => $user], 200);
    }

    public function logout(Request $request)
    {
        if ($user = Auth::guard('web')->user()) {
            // Revoke all tokens associated with the user
            $user->tokens->each(function ($token) {
                $token->delete();
            });
        }
    
        Auth::guard('web')->logout();
    
        return response()->json(['message' => 'Logged out successfully'], 200);
    }
    
    
}
