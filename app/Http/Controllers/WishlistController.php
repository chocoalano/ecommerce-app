<?php

namespace App\Http\Controllers;

use App\Models\Product\Wishlist;
use App\Models\Product\WishlistItem;
use App\Models\Product\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WishlistController extends Controller
{
    /**
     * Tampilkan halaman wishlist
     */
    public function index()
    {
        $customer = Auth::guard('customer')->user();
        if (!$customer) {
            return redirect()->route('auth.login')->with('error', 'Silakan login terlebih dahulu');
        }

        $wishlist = Wishlist::firstOrCreate(
            ['customer_id' => $customer->id],
            ['name' => 'My Wishlist']
        );

        $items = WishlistItem::where('wishlist_id', $wishlist->id)
            ->with(['product.media', 'product'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('pages.auth.wishlist.index', compact('items', 'wishlist'));
    }

    /**
     * Toggle wishlist item (add/remove)
     */
    public function toggle(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
        ]);

        $customer = Auth::guard('customer')->user();

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan login terlebih dahulu',
            ], 401);
        }

        try {
            DB::beginTransaction();

            // Get or create wishlist for customer
            $wishlist = Wishlist::firstOrCreate(
                ['customer_id' => $customer->id],
                ['name' => 'My Wishlist']
            );

            // Check if product already in wishlist
            $product = Product::findOrFail($validated['product_id']);
            $existingItem = WishlistItem::where('wishlist_id', $wishlist->id)
                ->where('product_id', $product->id)
                ->first();

            if ($existingItem) {
                // Remove from wishlist
                $existingItem->delete();
                $inWishlist = false;
                $message = 'Produk dihapus dari wishlist';
            } else {
                // Add to wishlist
                WishlistItem::create([
                    'wishlist_id' => $wishlist->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'meta_json' => [],
                ]);
                $inWishlist = true;
                $message = 'Produk ditambahkan ke wishlist';
            }

            // Get updated wishlist count
            $wishlistCount = WishlistItem::where('wishlist_id', $wishlist->id)->count();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message,
                'in_wishlist' => $inWishlist,
                'wishlist_count' => $wishlistCount,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get wishlist status for products
     */
    public function status(Request $request)
    {
        $customer = Auth::guard('customer')->user();

        if (!$customer) {
            return response()->json([
                'success' => false,
                'items' => [],
            ]);
        }

        $wishlist = Wishlist::where('customer_id', $customer->id)->first();

        if (!$wishlist) {
            return response()->json([
                'success' => true,
                'items' => [],
                'count' => 0,
            ]);
        }

        $productIds = $request->input('product_ids', []);

        $items = WishlistItem::where('wishlist_id', $wishlist->id)
            ->when(!empty($productIds), function($q) use ($productIds) {
                return $q->whereIn('product_id', $productIds);
            })
            ->pluck('product_id')
            ->toArray();

        return response()->json([
            'success' => true,
            'items' => $items,
            'count' => WishlistItem::where('wishlist_id', $wishlist->id)->count(),
        ]);
    }

    /**
     * Get wishlist count
     */
    public function count()
    {
        $customer = Auth::guard('customer')->user();

        if (!$customer) {
            return response()->json(['count' => 0]);
        }

        $wishlist = Wishlist::where('customer_id', $customer->id)->first();
        $count = $wishlist ? $wishlist->items()->count() : 0;

        return response()->json(['count' => $count]);
    }

    /**
     * Hapus item dari wishlist
     */
    public function remove(Request $request, $id)
    {
        $customer = Auth::guard('customer')->user();
        if (!$customer) {
            return redirect()->route('auth.login');
        }

        $wishlist = Wishlist::where('customer_id', $customer->id)->first();
        if ($wishlist) {
            WishlistItem::where('wishlist_id', $wishlist->id)
                ->where('id', $id)
                ->delete();
        }

        return redirect()->back()->with('success', 'Produk dihapus dari wishlist');
    }
}
