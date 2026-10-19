<?php

use Illuminate\Support\Facades\Route;
use App\Models\{Branch, Category, Product, RestaurantTable};

Route::get('/', function () {
    return view('home', [
        'portal' => 'customer',
        'branches' => Branch::where('is_active', true)->get(),
        'categories' => Category::where('is_active', true)->get(),
        'products' => Product::with('category')->where('is_active', true)->where('is_featured', true)->take(8)->get(),
    ]);
});

Route::get('/menu', function () {
    return view('menu', [
        'portal' => 'customer',
        'products' => Product::with('category')->where('is_active', true)->paginate(16),
    ]);
});

Route::get('/product/{product:slug}', fn (Product $product) => view('product', ['portal' => 'customer', 'product' => $product]));
Route::get('/portal', fn () => view('portal', ['portal' => 'portal']));
Route::get('/dashboard', fn () => redirect('/branch'));

Route::get('/login/customer', fn () => view('auth-login', ['portal'=>'auth','role'=>'customer']));
Route::get('/customer', fn () => view('customer.app', ['portal' => 'customer']));
Route::get('/app', fn () => redirect('/customer'));

Route::get('/login/admin', fn () => view('auth-login', ['portal'=>'auth','role'=>'admin']));
Route::get('/admin', fn () => view('admin', ['portal' => 'admin']));

Route::get('/login/branch', fn () => view('auth-login', ['portal'=>'auth','role'=>'branch']));
Route::get('/branch', fn () => view('dashboard', ['portal' => 'branch']));

Route::get('/login/kitchen', fn () => view('auth-login', ['portal'=>'auth','role'=>'kitchen']));
Route::get('/kitchen', fn () => view('kitchen', ['portal' => 'kitchen']));

// POS is part of the branch workspace. Keep the old waiter URL as a compatibility redirect.
Route::get('/login/waiter', fn () => redirect('/branch#pos'));
Route::get('/waiter', fn () => redirect('/branch#pos'));

Route::get('/rider', fn () => view('rider-app-only', ['portal' => 'portal']));
Route::get('/reports', fn () => view('reports', ['portal' => 'admin']));
Route::get('/digital-twin', fn () => view('digital-twin', ['portal' => 'admin']));
Route::get('/qr/{token}', function (string $token) { $table=RestaurantTable::with('branch')->where('qr_token',$token)->firstOrFail(); return view('qr',['portal'=>'customer','table'=>$table,'products'=>Product::where('is_active',true)->with('category')->take(50)->get()]); });
