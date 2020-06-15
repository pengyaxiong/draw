<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['namespace' => 'Wechat', 'prefix' => 'wechat', 'as' => 'wechat.'], function () {

    //授权
    Route::post('/auth', 'IndexController@auth');
    //首页
    Route::get('index', 'IndexController@index');
    //活动规则
    Route::get('configs', 'IndexController@configs');

    //商品分类
    Route::get('categories', 'IndexController@categories');
    //分类商品
    Route::get('category/{id}', 'IndexController@category');
    //商品详情
    Route::get('product/{id}', 'IndexController@product');
    //搜索
    Route::get('search', 'IndexController@search');

    //用户信息
    Route::get('customer', 'IndexController@customer');
    //我的地址
    Route::get('address', 'IndexController@address');
    //新增地址
    Route::post('add_address', 'IndexController@add_address');
    //修改地址
    Route::get('edit_address/{id}', 'IndexController@edit_address');
    //更新地址
    Route::post('update_address', 'IndexController@update_address');
    //删除地址
    Route::post('delete_address', 'IndexController@delete_address');
    //设为默认地址
    Route::post('default_address', 'IndexController@default_address');
    //我的积分记录
    Route::get('coin', 'IndexController@coin');
    //每日领取积分
    Route::get('do_coin', 'IndexController@do_coin');

    //奖品
    Route::get('draw', 'IndexController@draw');
    //抽奖
    Route::post('do_draw', 'IndexController@do_draw');

    //我的订单
    Route::get('order', 'IndexController@order');
    //完成订单
    Route::post('finish', 'IndexController@finish');
    //修改订单
    Route::post('order_address', 'IndexController@order_address');

});