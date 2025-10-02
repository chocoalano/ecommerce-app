<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class HomeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $activeCategories = Category::where('is_active', true)->get();
        $count = $activeCategories->count();
        $half = ceil($count / 2);

        $categoriesFirst  = $activeCategories->take($half);
        $categoriesSecond = $activeCategories->skip($half);

        $hero = Promotion::where([
                                'is_active'=> true,
                                'show_on'=> 'HERO',
                                'page'=> 'beranda',
                                ])
                            ->first();
        $activePromotion = Promotion::where([
                                'is_active'=> true,
                                'show_on'=> 'BANNER',
                                'page'=> 'beranda',
                                ])->get();
        $promo = ceil($activePromotion->count() / 2);

        $promoFirst  = $activePromotion->take($half);
        $promoSecond = $activePromotion->skip($half);
        return view('pages.home', [
            'categoriesFirst'=>$categoriesFirst,
            'categoriesSecond'=>$categoriesSecond,
            'hero'=>$hero,
            'promoFirst'=>$promoFirst,
            'promoSecond'=>$promoSecond
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        $products = [
            [
                'id' => 1,
                'name' => 'Galaxy S25 Ultra',
                'image' => asset('images/galaxy-z-flip7-share-image.png'),
                'price' => 'Rp22.999.000',
            ],
            [
                'id' => 2,
                'name' => 'Galaxy Z Fold7',
                'image' => asset('images/galaxy-z-flip7-share-image.png'),
                'price' => 'Rp27.999.000',
            ],
            [
                'id' => 3,
                'name' => 'Galaxy S24 Ultra',
                'image' => asset('images/galaxy-z-flip7-share-image.png'),
                'price' => 'Rp16.999.000',
            ],
            [
                'id' => 4,
                'name' => 'Galaxy S25 Ultra (Samsung.com only)',
                'image' => asset('images/galaxy-z-flip7-share-image.png'),
                'price' => 'Rp22.999.000',
            ],
            [
                'id' => 5,
                'name' => 'Galaxy A56 5G',
                'image' => asset('images/galaxy-z-flip7-share-image.png'),
                'price' => 'Rp6.199.000',
            ],
            [
                'id' => 6,
                'name' => 'Galaxy A56 5G',
                'image' => asset('images/galaxy-z-flip7-share-image.png'),
                'price' => 'Rp6.199.000',
            ],
            [
                'id' => 7,
                'name' => 'Galaxy A56 5G',
                'image' => asset('images/galaxy-z-flip7-share-image.png'),
                'price' => 'Rp6.199.000',
            ],
            [
                'id' => 8,
                'name' => 'Galaxy A56 5G',
                'image' => asset('images/galaxy-z-flip7-share-image.png'),
                'price' => 'Rp6.199.000',
            ],
            [
                'id' => 9,
                'name' => 'Galaxy A56 5G',
                'image' => asset('images/galaxy-z-flip7-share-image.png'),
                'price' => 'Rp6.199.000',
            ],
            [
                'id' => 10,
                'name' => 'Galaxy A56 5G',
                'image' => asset('images/galaxy-z-flip7-share-image.png'),
                'price' => 'Rp6.199.000',
            ],
            [
                'id' => 11,
                'name' => 'Galaxy A56 5G',
                'image' => asset('images/galaxy-z-flip7-share-image.png'),
                'price' => 'Rp6.199.000',
            ],
            [
                'id' => 12,
                'name' => 'Galaxy A56 5G',
                'image' => asset('images/galaxy-z-flip7-share-image.png'),
                'price' => 'Rp6.199.000',
            ],
            [
                'id' => 13,
                'name' => 'Galaxy A56 5G',
                'image' => asset('images/galaxy-z-flip7-share-image.png'),
                'price' => 'Rp6.199.000',
            ],
            [
                'id' => 14,
                'name' => 'Galaxy A56 5G',
                'image' => asset('images/galaxy-z-flip7-share-image.png'),
                'price' => 'Rp6.199.000',
            ],
            [
                'id' => 15,
                'name' => 'Galaxy A56 5G',
                'image' => asset('images/galaxy-z-flip7-share-image.png'),
                'price' => 'Rp6.199.000',
            ],
            [
                'id' => 16,
                'name' => 'Galaxy A56 5G',
                'image' => asset('images/galaxy-z-flip7-share-image.png'),
                'price' => 'Rp6.199.000',
            ],
            [
                'id' => 17,
                'name' => 'Galaxy A56 5G',
                'image' => asset('images/galaxy-z-flip7-share-image.png'),
                'price' => 'Rp6.199.000',
            ],
            [
                'id' => 18,
                'name' => 'Galaxy A56 5G',
                'image' => asset('images/galaxy-z-flip7-share-image.png'),
                'price' => 'Rp6.199.000',
            ],
            [
                'id' => 19,
                'name' => 'Galaxy A56 5G',
                'image' => asset('images/galaxy-z-flip7-share-image.png'),
                'price' => 'Rp6.199.000',
            ],
            [
                'id' => 20,
                'name' => 'Galaxy A56 5G',
                'image' => asset('images/galaxy-z-flip7-share-image.png'),
                'price' => 'Rp6.199.000',
            ],
            [
                'id' => 21,
                'name' => 'Galaxy A56 5G',
                'image' => asset('images/galaxy-z-flip7-share-image.png'),
                'price' => 'Rp6.199.000',
            ],
            [
                'id' => 22,
                'name' => 'Galaxy A56 5G',
                'image' => asset('images/galaxy-z-flip7-share-image.png'),
                'price' => 'Rp6.199.000',
            ],
            [
                'id' => 23,
                'name' => 'Galaxy A56 5G',
                'image' => asset('images/galaxy-z-flip7-share-image.png'),
                'price' => 'Rp6.199.000',
            ],
            [
                'id' => 24,
                'name' => 'Galaxy A56 5G',
                'image' => asset('images/galaxy-z-flip7-share-image.png'),
                'price' => 'Rp6.199.000',
            ],
            [
                'id' => 25,
                'name' => 'Galaxy A56 5G',
                'image' => asset('images/galaxy-z-flip7-share-image.png'),
                'price' => 'Rp6.199.000',
            ],
        ];

        // Contoh untuk membuat data menjadi Pagination
        $perPage = 5;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentProducts = array_slice($products, ($currentPage - 1) * $perPage, $perPage);

        $paginatedProducts = new LengthAwarePaginator(
            $currentProducts,
            count($products),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        return view('pages.category', ['products' => $paginatedProducts]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function register()
    {
        return view('pages.auth.register');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function login()
    {
        return view('pages.auth.login');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function login_submit(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);
        $guard = Auth::guard('customer');
        if ($guard->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('auth.profile'));

        }
        throw ValidationException::withMessages([
            'email' => 'Email atau kata sandi yang Anda masukkan salah.',
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function profile()
    {
        return view('pages.auth.profile');
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
