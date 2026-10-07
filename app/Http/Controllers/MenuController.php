<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use App\Models\Item;

class MenuController extends Controller
{
    // menampilkan menu berdasarkan kategori
    public function index(Request $request) {
        $tableNumber = $request->query('meja');
        if ($tableNumber) {
            Session::put('table_number', $tableNumber);
        }

        $items = Item::where('is_active', 1)->orderBy('name', 'asc')->get();

        return view('customer.menu', compact('items', 'tableNumber'));
    }

    // menampilkan keranjang
    public function cart() {
        $cart = Session::get('cart');

        return view('customer.cart', compact('cart'));
    }

    // menambahkan item ke keranjang
    public function addToCart(Request $request) {
        $menuId = $request->input('id');
        $menu = Item::find($menuId);

        if (!$menu) {
            return response()->json([
                'status' => 'error',
                'message' => 'Menu not found'
            ]);
        }
        // Ambil keranjang dari session
        $cart = Session::get('cart');

        // Jika keranjang belum ada, buat keranjang baru
        if (isset($cart[$menuId])) {
            $cart[$menuId]['qty']+= 1;
        } else {
            $cart[$menuId] = [
                'id' => $menu->id,
                'name' => $menu->name,
                'price' => $menu->price,
                'image' => $menu->img,
                'qty' => 1
            ];
        } 

        Session::put('cart', $cart);

        return response()->json([
            'status' => 'success',
            'message' => 'Menu added to cart',
            'cart' => $cart
        ]);
    }

    // nambah jumlah item di keranjang
    public function updateCart(Request $request){
        $itemId = $request->input('id');
        $newQty = $request->input('qty');

        if ($newQty <= 0) {
            return response()->json([
                'success' => false,

            ]);
        }

        $cart = Session::get('cart');
        if (isset($cart[$itemId])) {
            $cart[$itemId]['qty'] = $newQty;
            Session::put('cart', $cart);
            Session::flash('success', 'Keranjangberhasil diperbarui.');

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false]);
    }

    // remove cart
    public function removeCart(Request $request) {
        $itemId = $request->input('id');
        $cart = Session::get('cart');

        if (isset($cart[$itemId])) {
            unset($cart[$itemId]);
            Session::put('cart', $cart);
            Session::flash('success', 'Item berhasil dihapus dari keranjang.');

            return response()->json(['success' => true]);
        }
    }

    // clear cart

    public function clearCart() {
        Session::forget('cart');
        return redirect()->route('cart')->with('success', 'Keranjang berhasil dikosongkan.');
    }
}

