<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\CartMenu;
use App\Services\Apriori;

class AprioriController extends Controller
{
    public function apriori()
    {
        $transactions = CartMenu::join('menus', 'cart_menus.menu_id', '=', 'menus.id')
            ->select('cart_menus.cart_id', 'menus.id', 'menus.name', 'menus.price', 'menus.img', 'menus.description', 'menus.category_id')
            ->get()
            ->groupBy('cart_id')
            ->map(function ($cartItems) {
                return $cartItems->pluck('name')->toArray();  // Menyimpan nama menu/produk
            });
    
        $minSupport = 2;  // Batas minimum support, coba nilai lebih rendah
        $apriori = new Apriori($transactions, $minSupport);
        $recomendations = $apriori->run();
    
        $recommendedMenuNames = collect($recomendations)->flatten()->unique();
    
        $recommendedMenus = Menu::whereIn('name', $recommendedMenuNames)
            ->select('id', 'name', 'price', 'img', 'description', 'category_id')
            ->get();
    
        return response()->json([
            'recommendations' => $recomendations,
            'menus' => $recommendedMenus
        ]);
    }
            
}
