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
        //二期
        //提现关联
        $router->resource('withdraws', 'WithdrawController');
        //查看评价
        $router->get('comment_info/{order_id}/{product_id}', 'OrderController@comment_info')->name('comment_info');
        //回复评价
        $router->post('reply', 'OrderController@reply')->name('reply');
        //删除评价
        $router->post('comment_del', 'OrderController@comment_del');
    });

    //二期
    //关于我们
    $router->resource('abouts', 'AboutController');
    //帮助中心
    $router->resource('problem-categories', 'ProblemCategoryController');
    $router->resource('problems', 'ProblemController');
});
