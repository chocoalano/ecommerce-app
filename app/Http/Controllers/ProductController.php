<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(int $id)
    {
        return view('pages.products.cart');
    }
    /**
     * Store a newly created resource in storage.
     */
    public function wislist(int $id)
    {
        return view('pages.products.wislist');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function checkout(Request $request)
    {
        return view('pages.products.checkout');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function transaction(Request $request)
    {
        return view('pages.products.transaction');
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        $product = Product::find($id);
        return view('pages.products.detail', compact('product'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
