<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view("shoppingcarts");
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Cart $carts)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cart $carts)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cart $carts)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cart $carts)
    {
        //
    }

    public function add(Request $request) 
    {

        $productData = $request->all();
        // dd($productData);

        //確認是否登入
        if ($productData['user_id'] == 0) {
            return response()->json([
                'success' => false,
                'message' => '請先登入'
            ]);
        }
        //確認是否已經加入購物車
        $cart = Cart::where('product_id', $productData['product_id'])
            ->where('user_id', $productData['user_id'])
            ->first();
        if ($cart) {
            return response()->json([
                'success' => false,
                'message' => '商品已經在購物車中'
            ]);
        }

        try {
            Cart::create($productData);
            return response()->json([
                'success' => true,
                'message' => '購物車新增成功'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '購物車新增失敗',
                'error' => $e->getMessage(),
            ]);
        }
    }
}
