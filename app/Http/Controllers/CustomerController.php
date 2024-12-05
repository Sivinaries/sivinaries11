<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\User;
use App\Models\Order;
use Ramsey\Uuid\Uuid;
use App\Models\Profil;
use App\Models\CartMenu;
use App\Models\Category;
use App\Models\Discount;
use App\Models\Showcase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CustomerController extends Controller
{
    public function home()
    {
        $showcase = Cache::remember('showcase', now()->addMinutes(60), function () {
            return Showcase::select('id', 'img')->get();
        });

        $profil = Cache::remember('profil', now()->addMinutes(60), function () {
            return Profil::select('id', 'name', 'alamat')->get();
        });

        $menus = Cache::remember('menus', now()->addMinutes(60), function () {
            return Menu::select('id', 'name', 'price', 'img')->paginate(10); // Use pagination
        });

        return view('user.home', compact('profil', 'menus', 'showcase'));
    }

    public function product()
    {
        $category = Cache::remember('categories_with_menus', now()->addMinutes(60), function () {
            return Category::with(['menus'])->get();
        });

        $user = auth()->user();

        $cart = $user->carts()->latest()->first();

        if (!$cart) {
            $cart = $user->carts()->create([]);
        }

        return view('user.product', compact('category', 'cart'));
    }

    public function cart()
    {
        $user = auth()->user();

        $cart = $user->carts()->with(['cartMenus.menu'])->latest()->first();

        if (!$cart) {
            $cart = $user->carts()->create([]);
        }

        return view('user.cart', compact('cart'));
    }

    public function show($id)
    {
        $menu = Cache::remember("menu_{$id}", now()->addMinutes(60), function () use ($id) {
            return Menu::find($id);
        });
        $discount = Cache::remember('discounts', now()->addMinutes(60), function () {
            return Discount::all();
        });

        return view('user.show', compact('menu', 'discount'));
    }

    public function postcart(Request $request)
    {
        $request->validate([
            'menu_id' => 'required|exists:menus,id',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
            'discount_id' => 'nullable|exists:discounts,id', // Discount can be nullable
        ]);

        $user = auth()->user();
        $cart = $user->carts()->latest()->first() ?? $user->carts()->create(['total_amount' => 0]);

        $menu = Menu::findOrFail($request->input('menu_id'));
        $quantity = $request->input('quantity');

        $subtotal = (float)$menu->price * (int)$quantity;

        $discount = null;

        if ($request->filled('discount_id')) {
            $discount = Discount::find($request->input('discount_id'));
            if ($discount) {
                $discountAmount = $subtotal * ($discount->percentage / 100);
                $subtotal -= $discountAmount; // Apply discount
            }
        }

        $existingCartMenu = CartMenu::where('cart_id', $cart->id)
            ->where('menu_id', $menu->id)
            ->where('notes', $request->input('notes'))
            ->where('discount_id', $discount ? $discount->id : null)
            ->first();

        if ($existingCartMenu) {
            $existingCartMenu->quantity += $quantity;
            $existingCartMenu->subtotal += $subtotal;
            $existingCartMenu->save();
        } else {
            CartMenu::create([
                'cart_id' => $cart->id,
                'menu_id' => $menu->id,
                'quantity' => $quantity,
                'notes' => $request->input('notes'),
                'subtotal' => $subtotal,
                'discount_id' => $discount ? $discount->id : null, // Assign discount ID if applicable
            ]);
        }

        $cart->update(['total_amount' => $cart->total_amount + $subtotal]);

        return redirect(route('user-product'));
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


        return redirect()->route('user-cart');
    }

    public function serve()
    {
        return view('user.serve');
    }

    public function locate()
    {
        $user = auth()->user();

        $cart = $user->carts()->latest()->first();

        $order = Order::where('cart_id', $cart->id)->first();

        return view('user.locate', compact('order'));
    }

    public function payment(Request $request)
    {
        $user = auth()->user();

        $cart = $user->carts()->with('cartMenus.menu')->latest()->first();

        $order = Order::where('cart_id', $cart->id)->first();

        return view('user.payment', compact('order', 'cart'));
    }

    public function postDineIn()
    {
        $user = auth()->user();
        $cabang = Profil::pluck('alamat')->first();
        $cart = $user->carts()->with('user')->latest()->first();

        if (!$cart) {
            return redirect()->back()->with('error', 'No cart found.');
        }

        $order = Order::where('cart_id', $cart->id)->first();

        if ($order) {
            $order->layanan = 'dineIn';
            $order->alamat = null; // Set alamat to null
            $order->ongkir = null; // Set ongkir to null    
            $order->save();
        } else {
            $order = new Order();
            $order->cart_id = $cart->id;
            $order->cabang = $cabang;
            $order->layanan = 'dineIn';
            $order->alamat = null; // Set alamat to null
            $order->ongkir = null; // Set ongkir to null    
            $order->save();
        }

        return redirect()->route('user-payment');
    }

    public function postDelivery(Request $request)
    {
        $user = auth()->user();
        $cabang = Profil::pluck('alamat')->first();
        $cart = $user->carts()->with('user')->latest()->first();

        if (!$cart) {
            return redirect()->back()->with('error', 'No cart found.');
        }

        $order = Order::where('cart_id', $cart->id)->first();

        if ($order) {
            $order->layanan = 'delivery';
            $order->save();
        } else {
            $order = new Order();
            $order->cart_id = $cart->id;
            $order->cabang = $cabang;
            $order->layanan = 'delivery';
            $order->save();
        }

        return redirect()->route('user-locate');
    }

    public function ongkir(Request $request)
    {
        $user = auth()->user();
        $cart = $user->carts()->with('user')->latest()->first();

        $order = Order::where('cart_id', $cart->id)->first();

        if (!$order) {
            return redirect()->back()->with('error', 'No order found for the cart.');
        }

        $order->alamat = $request->input('alamat');
        $order->ongkir = $request->input('ongkir');
        $order->save();

        return redirect()->route('user-payment');
    }

    public function postorder(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'no_telpon' => 'required|string|max:15',
            'atas_nama' => 'required|string|max:255',
            'cabang' => 'required|string|max:255',
            'alamat' => 'nullable',
            'ongkir' => 'nullable',
        ]);

        $cart = $user->carts()->with('cartMenus.menu')->latest()->first();

        if (!$cart || $cart->cartMenus->isEmpty()) {
            return redirect()->route('user-cart')->with('error', 'Your cart is empty.');
        }

        $order = Order::where('cart_id', $cart->id)->first();

        if (!$order) {
            return redirect()->route('user-home');
        }

        $orderId = 'ORDER-' . strtoupper(substr(Uuid::uuid4()->toString(), 0, 8));

        $order->update([
            'no_order' => $orderId,
            'atas_nama' => $request->atas_nama,
            'no_telpon' => $request->no_telpon,
        ]);

        $cart->update(['total_amount' => $cart->total_amount + ($order->ongkir ?? 0)]);

        \Midtrans\Config::$serverKey = config('midtrans.server_key');
        \Midtrans\Config::$isProduction = true;
        \Midtrans\Config::$isSanitized = true;
        \Midtrans\Config::$is3ds = true;

        $params = array(
            'transaction_details' => array(
                'order_id' => $orderId,
                'gross_amount' => $order->cart->total_amount,
            )
        );

        $snapToken = \Midtrans\Snap::getSnapToken($params);

        $user->carts()->create();

        return view('user.checkout', compact('order', 'snapToken'));
    }
    
    public function antrian()
    {
        $orders = Order::with(['cart.user', 'cart.cartMenus.menu'])->get();
        $statuses = [];

        foreach ($orders as $order) {
            try {
                if ($order->status === 'settlement' && $order->payment_type === 'cash') {
                    $statuses[$order->no_order] = (object) [
                        'status' => $order->status,
                        'bg_color' => 'text-white text-center bg-green-500 w-fit rounded-xl'
                    ];
                    continue; // Skip further processing for this order
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
                    'bg_color' => $status->transaction_status === 'settlement' ? 'text-white text-center bg-green-500 w-fit rounded-xl' : 'text-white text-center bg-red-500 w-fit rounded-xl'
                ];
            } catch (\Exception $e) {
                $statuses[$order->no_order] = (object) [
                    'status' => 'Error: ' . $e->getMessage(),
                    'bg_color' => 'bg-red-500 w-fit text-white text-center rounded-xl'
                ];
            }
        }

        return view('user.antrian', compact('orders', 'statuses'));
    }

    public function game()
    {
        $products = Menu::select('name', 'img')->get();  // Get product names and image URLs

        return view('user.game', compact('products'));
    }

    public function akun()
    {
        $userId = auth()->id();

        $user = Cache::remember("akun_{$userId}", now()->addMinutes(60), function () use ($userId) {
            return User::find($userId); // Fetches the user from the database by ID
        });
        return view('user.akun', compact('user'));
    }
}
