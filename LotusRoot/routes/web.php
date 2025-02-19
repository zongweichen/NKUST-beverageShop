<?php

use Illuminate\Support\Facades\Route;
//首頁
Route::get('/',  "App\Http\Controllers\ProductController@indexForMainPage");

//Menu
Route::get('/menu', function () {
    return view('menu');
});


Route::group(['prefix' => 'user'], function () {
    Route::group(['prefix' => 'auth'], function () {

        Route::post('signup', 'App\Http\Controllers\UserAuthController@SignUpProcess');
        Route::post('signin', 'App\Http\Controllers\UserAuthController@SigninProcess');
        Route::get('signout', 'App\Http\Controllers\UserAuthController@SignOut')->name('signout');
        
    });
});

Route::get("products", 'App\Http\Controllers\ProductController@index');

//使用者
Route::group(['prefix' => 'product'], function () {
    Route::get("/", "App\Http\Controllers\ProductController@index");
    // gitRoute::post("create", "App\Http\Controllers\ProductController@store");
    Route::get("description/{id}","App\Http\Controllers\ProductController@description");
    Route::get('onlineShop', 'App\Http\Controllers\ProductController@onlineShop')->name('product.onlineShop');
});


//管理者
Route::group(['prefix' => 'manage'], function () {
    Route::group(["prefix" => "product"], function () {
        Route::get("create", "App\Http\Controllers\ProductController@create");
        Route::post("create", "App\Http\Controllers\ProductController@store");
        Route::put("edit/{id}", "App\Http\Controllers\ProductController@update");
        Route::delete("{id}", "App\Http\Controllers\ProductController@destroy");
    });
});