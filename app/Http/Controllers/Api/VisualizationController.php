<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Shop\Order;
use App\Models\Shop\OrderProduct;
use DB, Cache;
use Illuminate\Http\Request;

class VisualizationController extends Controller
{
    //本周起止时间unix时间戳
    private $week_start;
    private $week_end;

    //本月起止时间unix时间戳
    private $month_start;
    private $month_end;

    function __construct()
    {
        $this->week_start = mktime(0, 0, 0, date("m"), date("d") - date("w") + 1, date("Y"));
        $this->week_end = mktime(23, 59, 59, date("m"), date("d") - date("w") + 7, date("Y"));

        $this->month_start = mktime(0, 0, 0, date("m"), 1, date("Y"));
        $this->month_end = mktime(23, 59, 59, date("m"), date("t"), date("Y"));
    }

    /**
     * 本周订单数
     * @return array [1 => '待付款', 2 => '待发货', 3 => '待收货', 4 => '待评价', 5 => '已完成']
     */
    function sales_count()
    {
        // return \Cache::remember('xApi_visualization_sales_count', 60, function () {
        $count = [];
        for ($i = 0; $i < 7; $i++) {
            $start = date('Y-m-d H:i:s', strtotime("+" . $i . " day", $this->week_start));
            $end = date('Y-m-d H:i:s', strtotime("+" . ($i + 1) . " day", $this->week_start));

            //待支付
            $count['create'][] = Order::whereBetween('created_at', [$start, $end])->where('pay_type', 1)->where('status', 1)->count();
            //待发货
            $count['pay'][] = Order::whereBetween('pay_time', [$start, $end])->where('pay_type', 1)->where('status', 2)->count();
            //待收货
            $count['shipping'][] = Order::whereBetween('send_time', [$start, $end])->where('pay_type', 1)->where('status', 3)->count();
            //待评价
            $count['comment'][] = Order::whereBetween('send_time', [$start, $end])->where('pay_type', 1)->where('status', 4)->count();
            //已完成
            $count['finish'][] = Order::whereBetween('finish_time', [$start, $end])->where('pay_type', 1)->where('status', 5)->count();
        }

        $data = [
            'week_start' => date("Y年m月d日", $this->week_start),
            'week_end' => date("Y年m月d日", $this->week_end),
            'count' => $count,
        ];
        return $data;
        //  });

    }

    /**
     * 本周销售额
     * @return array
     */
    function sales_amount()
    {
        //  return \Cache::remember('xApi_visualization_sales_amount', 60, function () {
        $amount = [];
        for ($i = 0; $i < 7; $i++) {
            $start = date('Y-m-d H:i:s', strtotime("+" . $i . " day", $this->week_start));
            $end = date('Y-m-d H:i:s', strtotime("+" . ($i + 1) . " day", $this->week_start));
            $amount['create'][] = Order::whereBetween('created_at', [$start, $end])->where('pay_type', 1)->where('status', 1)->sum('total_price');
            $amount['pay'][] = Order::whereBetween('pay_time', [$start, $end])->where('pay_type', 1)->where('status', '>', 1)->sum('total_price');
        }

        $data = [
            'week_start' => date("Y年m月d日", $this->week_start),
            'week_end' => date("Y年m月d日", $this->week_end),
            'amount' => $amount,
        ];
        return $data;
        //  });
    }

    /**
     * 本月热门销量
     * @return mixed
     */
    function order_count()
    {
        //   return \Cache::remember('xApi_visualization_top', 60, function () {
//            DB::enableQueryLog();
        $start = date("Y-m-d H:i:s", $this->month_start);
        $end = date("Y-m-d H:i:s", $this->month_end);

        //本月订单的id
        $order = Order::whereBetween('created_at', [$start, $end])->pluck('id');

        //对应热门商品,前10名. 语句较复杂,请自己return sql出来看
        $products = OrderProduct::with('product')
            ->select('product_id', \DB::raw('sum(num) as sum_num'))
            ->whereIn('order_id', $order)
            ->groupBy('product_id')
            ->orderBy(\DB::raw('sum(num)'), 'desc')
            ->take(5)
            ->get();


        // return DB::getQueryLog();

        $data = [
            'month_start' => date("Y年m月d日", $this->month_start),
            'month_end' => date("Y年m月d日", $this->month_end),
            'products' => $products,
        ];
        return $data;
        //   });

    }

    /**
     * 会员注册量
     * @return array
     */
    public function statistics_customer()
    {

        $year = date("Y", time());
        $num = [];
        for ($i = 1; $i <= 12; $i++) {
            $month = strlen($i) == 1 ? '0' . $i : $i;
            $like = $year . '_' . $month . '%';
            $num[] = Customer::where('created_at', 'like', $like)->count();
        }

        $data = [
            'this_year' => $year,
            'num' => $num
        ];
        return $data;
    }

    /**
     * 性别统计
     * @return \Illuminate\Support\Collection
     */
    function sex_count()
    {
        $male = Customer::where('sex', '1')->count();
        $female = Customer::where('sex', '2')->count();
        return collect(compact('male', 'female'));
    }

    public function order_status()
    {
        $data = [];

        $start = date("Y-m-d H:i:s", $this->month_start);
        $end = date("Y-m-d H:i:s", $this->month_end);

        $data['customers']=Customer::count();
        $data['orders']=Order::count();
        $data['month']=Order::whereBetween('created_at', [$start, $end])->where('status','>',1)->where('pay_type', 1)->sum('total_price');
        $data['all']=Order::where('status','>',1)->where('pay_type', 1)->sum('total_price');
        return $data;
    }
}
