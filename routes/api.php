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

Route::group(['namespace' => 'Api'], function () {

    //订单状态统计
    Route::get('order_status', 'VisualizationController@order_status');
    //本月热门销量
    Route::get('order_count', 'VisualizationController@order_count');
    //本周销售额
    Route::get('sales_amount', 'VisualizationController@sales_amount');
    //本周订单数
    Route::get('sales_count', 'VisualizationController@sales_count');
    //会员注册量
    Route::get('statistics_customer', 'VisualizationController@statistics_customer');

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
    Route::get('category_child/{id}', 'IndexController@category_child');
    //分类商品
    Route::get('category/{id}', 'IndexController@category');
    //积分商城
    Route::get('coin_category', 'IndexController@coin_category');
    //商品详情
    Route::get('product/{id}', 'IndexController@product');
    //搜索
    Route::get('search', 'IndexController@search');

    //用户信息
    Route::get('customer', 'IndexController@customer');

    /**
     * 二期
     */
    //商品评论
    Route::get('product_comments', 'IndexController@product_comments');


    //关于我们
    Route::get('about', 'IndexController@about');
    //帮助中心
    Route::get('problem_category', 'IndexController@problem_category');
    //帮助中心详情
    Route::get('problem/{id}', 'IndexController@problem');

    //上传图片
    Route::post('upload_img', 'IndexController@upload_img');

    //我的团队
    Route::get('group', 'IndexController@group');
    //提现
    Route::post('do_withdraw', 'IndexController@do_withdraw');
    //佣金明细
    Route::get('money', 'IndexController@money');
    //邀请用户
    Route::get('code', 'IndexController@code');

    //添加到购物车
    Route::post('add_cart', 'IndexController@add_cart');
    //购物车列表
    Route::get('cart', 'IndexController@cart');
    //删除选中
    Route::post('destroy_checked', 'IndexController@destroy_checked');
    //修改购物车商品数量
    Route::post('change_num', 'IndexController@change_num');

    //评价
    Route::post('comment', 'IndexController@comment');

    //积分兑换
    Route::post('exchange', 'IndexController@exchange');



    //下单
    Route::post('add_order', 'IndexController@add_order');
    //确认订单
    Route::post('checkout', 'IndexController@checkout');
    //取消订单
    Route::post('del_order', 'IndexController@del_order');

    //付款
    Route::post('pay', 'IndexController@pay');
    //付款回调
    Route::any('paid', 'IndexController@paid');
    //退款
    Route::post('refund', 'IndexController@refund');
    //退款回调
    Route::any('refund_back', 'IndexController@refund_back');



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
    //提现记录
    Route::get('withdraw', 'IndexController@withdraw');
    //每日签到领取积分
    Route::get('do_coin', 'IndexController@do_coin');
    //每日分享就有积分，一天一次
    Route::get('get_coin', 'IndexController@get_coin');

    //奖品
    Route::get('draw', 'IndexController@draw');
    //抽奖
    Route::post('do_draw', 'IndexController@do_draw');

    //我的订单
    Route::get('order', 'IndexController@order');
    //订单
    Route::get('order_info/{id}', 'IndexController@order_info');
    //完成订单
    Route::post('finish', 'IndexController@finish');
    //修改订单
    Route::post('order_address', 'IndexController@order_address');

});