<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Histoy;
use App\Models\CartMenu;
use App\Models\Category;
use App\Models\Discount;
use App\Models\Settlement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ApiController extends Controller
{
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

    public function category()
    {

        $category = Cache::remember('categories_with_menus', now()->addMinutes(60), function () {
            return Category::with(['menus'])->get();
        });

        return response()->json([
            'category' => $category
        ], 200);
    }

    public function cart()
    {
        // $cart = Cart::with('cartMenus')->get();
        $user = auth()->user();

        $cart = $user->carts()->with(['cartMenus.menu'])->latest()->first();

        if (!$cart) {
            $cart = $user->carts()->create([]);
        }

        return response()->json([
            'cart' => $cart
        ], 200);
    }

    public function order()
    {
        $orders = Order::with(['cart.user', 'cart.cartMenus.menu'])->get();
        $statuses = [];

        foreach ($orders as $order) {
            try {
                if ($order->status === 'settlement' && $order->payment_type === 'cash') {
                    $statuses[$order->no_order] = (object) [
                        'status' => $order->status,
                    ];
                    continue;
                }

                \Midtrans\Config::$serverKey = config('midtrans.server_key');
                \Midtrans\Config::$isProduction = true;

                $status = \Midtrans\Transaction::status($order->no_order);

                $order->update([
                    'status' => $status->transaction_status,
                    'payment_type' => $status->payment_type ?? null,
                ]);

                if ($status->transaction_status === 'expire') {
                    $order->delete();
                    continue;
                }

                $statuses[$order->no_order] = (object) [
                    'status' => $status->transaction_status,
                ];
            } catch (\Exception $e) {
                $statuses[$order->no_order] = (object) [
                    'status' => 'Error: ' . $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'orders' => $orders,
            'statuses' => $statuses,
            200
        ]);
    }

    public function history()
    {
        $history = Cache::remember('history', now()->addMinutes(60), function () {
            return Histoy::all();
        });

        return response()->json([
            'history' => $history
        ], 200);
    }

    public function settlement()
    {
        $settlement = Cache::remember('settlement_with_users', now()->addMinutes(60), function () {
            return Settlement::with('user')->get();
        });

        return response()->json([
            'settlement' => $settlement
        ], 200);
    }

    public function showproduct($id)
    {
        $menu = Cache::remember("menu_{$id}", now()->addMinutes(60), function () use ($id) {
            return Menu::find($id);
        });
        $discount = Cache::remember('discounts', now()->addMinutes(60), function () {
            return Discount::all();
        });

        return response()->json([
            'menu' => $menu,
            'discount' => $discount
        ], 200);
    }

    public function removecart($id)
    {
        $user = auth()->user();
        $cart = $user->carts()->latest()->first();

        $cartMenu = CartMenu::findOrFail($id);

        $subtotal = $cartMenu->subtotal;

        $cartMenu->discount_id;

        $cartMenu->delete();

        $cart->update(['total_amount' => $cart->total_amount - $subtotal]);

        return response()->json([
            'cart' => $cart
        ], 200);
    }
}
