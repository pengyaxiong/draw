<?php

use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
    'as'            => config('admin.route.prefix') . '.',
], function (Router $router) {

    $router->get('/', 'HomeController@index')->name('home');

    $router->resource('configs', 'ConfigController');

    $router->resource('customers', 'CustomerController');

    Route::group(['prefix' => 'shop', 'namespace' => 'Shop', 'as' => 'shop.'], function (Router $router) {

        $router->resource('categories', 'CategoryController');

        $router->resource('products', 'ProductController');

        $router->resource('orders', 'OrderController');

        $router->resource('addresses', 'AddressController');

        $router->resource('coins', 'CoinController');
    });
});
