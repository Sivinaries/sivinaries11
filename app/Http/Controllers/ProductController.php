<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Discount;
use App\Models\CartMenu;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    public function index()
    {
        $menus = Cache::remember('menus', now()->addMinutes(60), function () {
            return Menu::all();
        });

        return view('product', compact('menus'));
    }

    public function create()
    {
        $category = Category::all();

        return view('addproduct', compact('category'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'price' => 'required',
            'img' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'description' => 'required',
            'category_id' => 'required',
        ]);

        if ($request->hasFile('img')) {
            $uploadedImage = $request->file('img');
            $imageName = $uploadedImage->getClientOriginalName();
            $imagePath = $uploadedImage->storeAs('img', $imageName, 'public');
            $data['img'] = 'img/' . $imageName;
        }

        Menu::create($data);

        // Clear the old cache
        Cache::forget('menus');
        Cache::remember('menus', now()->addMinutes(60), function () {
            return Menu::all();
        });

        Cache::forget('categories');
        Cache::remember('categories', now()->addMinutes(60), function () {
            return Category::all();
        });

        // Clear and re-cache categories with menus
        Cache::forget('categories_with_menus');
        Cache::remember('categories_with_menus', now()->addMinutes(60), function () {
            return Category::with(['menus'])->get();
        });

        return redirect(route('product'))->with('success', 'Product Sukses Dibuat !');
    }

    public function show($id)
    {
        $menu = Cache::remember("menu_{$id}", now()->addMinutes(60), function () use ($id) {
            return Menu::find($id);
        });
        $discount = Cache::remember('discounts', now()->addMinutes(60), function () {
            return Discount::all();
        });

        return view('showproduct', compact('menu', 'discount'));
    }

    public function edit($id)
    {
        $category = Category::all();

        $menu = Menu::find($id);

        return view('editproduct', compact('menu', 'category'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'price' => 'required',
            'img' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'description' => 'required',
            'category_id' => 'required',
        ]);

        $menuData = $request->only(['name', 'price', 'img', 'description', 'category_id']);

        if ($request->hasFile('img')) {
            $uploadedImage = $request->file('img');
            $imageName = $uploadedImage->getClientOriginalName();
            $imagePath = $uploadedImage->storeAs('img', $imageName, 'public');
            $menuData['img'] = 'img/' . $imageName;
        }

        Menu::where('id', $id)->update($menuData);

        // Clear the old cache
        Cache::forget('menus');
        Cache::remember('menus', now()->addMinutes(60), function () {
            return Menu::all();
        });

        Cache::forget('categories');
        Cache::remember('categories', now()->addMinutes(60), function () {
            return Category::all();
        });

        // Clear and re-cache categories with menus
        Cache::forget('categories_with_menus');
        Cache::remember('categories_with_menus', now()->addMinutes(60), function () {
            return Category::with(['menus'])->get();
        });
        return redirect(route('product'))->with('success', 'Product Sukses Diupdate !');
    }

    public function destroy($id)
    {
        CartMenu::where('menu_id', $id)->delete();
        Menu::destroy($id);

        Cache::forget('menus');
        Cache::forget('categories');

        return redirect(route('product'))->with('success', 'Product Berhasil Dihapus !');
    }
}
