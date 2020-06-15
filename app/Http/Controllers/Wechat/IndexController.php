<?php

namespace App\Http\Controllers\Wechat;

use App\Models\Config;
use App\Models\Customer;
use App\Models\Shop\Address;
use App\Models\Shop\Category;
use App\Models\Shop\Coin;
use App\Models\Shop\Order;
use App\Models\Shop\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class IndexController extends Controller
{

    public function __construct()
    {

    }

    public function auth(Request $request)
    {
        //声明CODE，获取小程序传过来的CODE
        $code = $request->code;
        //配置appid
        $appid = env('WECHAT_MINI_PROGRAM_APPID', '');
        //配置appscret
        $secret = env('WECHAT_MINI_PROGRAM_SECRET', '');
        //api接口
        $api = "https://api.weixin.qq.com/sns/jscode2session?appid={$appid}&secret={$secret}&js_code={$code}&grant_type=authorization_code";

        $str = json_decode($this->httpGet($api), true);

        $openid = $str['openid'];

        $customer = Customer::where('openid', $openid)->first();

        if ($customer) {
            $customer->update([
                'openid' => $openid,
                'headimgurl' => $request->headimgurl,
                'nickname' => $request->nickname,
                'tel' => $request->tel,
                'sex' => $request->sex,
            ]);

        } else {
            Customer::create([
                'openid' => $openid,
                'headimgurl' => $request->headimgurl,
                'nickname' => $request->nickname,
                'tel' => $request->tel,
                'sex' => $request->sex,
            ]);

        }

        return $this->array($str, '授权成功');
    }

    //获取GET请求
    function httpGet($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curl, CURLOPT_URL, $url);
        $res = curl_exec($curl);
        curl_close($curl);
        return $res;
    }

    //获取姓氏
    function getXingList()
    {
        $arrXing = array('赵', '钱', '孙', '李', '周', '吴', '郑', '王', '冯', '陈', '褚', '卫', '蒋', '沈', '韩', '杨', '朱', '秦', '尤', '许', '何', '吕', '施', '张', '孔', '曹', '严', '华', '金', '魏', '陶', '姜', '戚', '谢', '邹',
            '喻', '柏', '水', '窦', '章', '云', '苏', '潘', '葛', '奚', '范', '彭', '郎', '鲁', '韦', '昌', '马', '苗', '凤', '花', '方', '任', '袁', '柳', '鲍', '史', '唐', '费', '薛', '雷', '贺', '倪', '汤', '滕', '殷', '罗',
            '毕', '郝', '安', '常', '傅', '卞', '齐', '元', '顾', '孟', '平', '黄', '穆', '萧', '尹', '姚', '邵', '湛', '汪', '祁', '毛', '狄', '米', '伏', '成', '戴', '谈', '宋', '茅', '庞', '熊', '纪', '舒', '屈', '项', '祝',
            '董', '梁', '杜', '阮', '蓝', '闵', '季', '贾', '路', '娄', '江', '童', '颜', '郭', '梅', '盛', '林', '钟', '徐', '邱', '骆', '高', '夏', '蔡', '田', '樊', '胡', '凌', '霍', '虞', '万', '支', '柯', '管', '卢', '莫',
            '柯', '房', '裘', '缪', '解', '应', '宗', '丁', '宣', '邓', '单', '杭', '洪', '包', '诸', '左', '石', '崔', '吉', '龚', '程', '嵇', '邢', '裴', '陆', '荣', '翁', '荀', '于', '惠', '甄', '曲', '封', '储', '仲', '伊',
            '宁', '仇', '甘', '武', '符', '刘', '景', '詹', '龙', '叶', '幸', '司', '黎', '溥', '印', '怀', '蒲', '邰', '从', '索', '赖', '卓', '屠', '池', '乔', '胥', '闻', '莘', '党', '翟', '谭', '贡', '劳', '逄', '姬', '申',
            '扶', '堵', '冉', '宰', '雍', '桑', '寿', '通', '燕', '浦', '尚', '农', '温', '别', '庄', '晏', '柴', '瞿', '阎', '连', '习', '容', '向', '古', '易', '廖', '庾', '终', '步', '都', '耿', '满', '弘', '匡', '国', '文',
            '寇', '广', '禄', '阙', '东', '欧', '利', '师', '巩', '聂', '关', '荆', '司马', '上官', '欧阳', '夏侯', '诸葛', '闻人', '东方', '赫连', '皇甫', '尉迟', '公羊', '澹台', '公冶', '宗政', '濮阳', '淳于', '单于', '太叔',
            '申屠', '公孙', '仲孙', '轩辕', '令狐', '徐离', '宇文', '长孙', '慕容', '司徒', '司空');
        return $arrXing;

    }

    //获取名字
    function getMingList()
    {
        $arrMing = array('先生', '女士');
        return $arrMing;
    }

    //获取时间
    function getTimeList()
    {
        $arrMit = [];
        $arrHour = [];
        for ($i = 1; $i < 60; $i++) {
            $arrMit[$i] = $i . '分钟前';
        }

        for ($ii = 1; $ii < 3; $ii++) {
            $arrHour[$ii] = $ii . '小时前';
        }
        $arrTime = array_merge($arrMit, $arrHour);

        return $arrTime;
    }


    public function index()
    {
        //中奖动态
        $arrXing = $this->getXingList();
        $numbXing = count($arrXing);
        $arrMing = $this->getMingList();
        $numbMing = count($arrMing);
        $arrTime = $this->getTimeList();
        $numbTime = count($arrTime);

        $info = [];
        for ($i = 1; $i < 10; $i++) {
            $Xing = $arrXing[mt_rand(0, $numbXing - 1)];
            $Ming = $arrMing[mt_rand(0, $numbMing - 1)];
            $Time = $arrTime[mt_rand(0, $numbTime - 1)];

            $info[$i]['name'] = $Xing . $Ming . '抽中了一等奖';
            $info[$i]['time'] = $Time;
        }

        $categories = Category::where('is_show', true)->where('parent_id', 0)->orderby('sort_order')->limit(4)->get();

        return $this->array(['info' => $info, 'categories' => $categories]);
    }


    public function configs()
    {
        $configs = Config::first();
        return $this->array(['configs' => $configs]);
    }

    public function categories()
    {
        $categories = Category::with(['children' => function ($query) {
            $query->orderby('sort_order')->get();
        }])->where('is_show', true)->where('parent_id', 0)->orderby('pinyin')->get()->groupby('pinyin');

        return $this->array(['categories' => $categories]);
    }

    public function category(Request $request, $id)
    {
        //多条件查找
        $where = function ($query) use ($request, $id) {
            $query->where('category_id', $id);

            if ($request->has('sale_num') and $request->sale_num != '') {

                $query->orderby('sale_num', 'desc');
            }
            if ($request->has('price_desc') and $request->price_desc != '') {

                $query->orderby('price_desc', 'desc');
            }
            if ($request->has('price_asc') and $request->price_asc != '') {

                $query->orderby('price_asc', 'asc');
            }

        };
        $products = Product::where($where)->paginate($request->total);

        $page = isset($page) ? $request['page'] : 1;
        $products = $products->appends(array(
            'page' => $page,
            'sale_num' => $request->sale_num,
            'price_desc' => $request->price_desc,
            'price_asc' => $request->price_asc,
        ));

        return $this->array(['list' => $products]);
    }

    public function product($id)
    {
        $product = Product::find($id);
        return $this->array(['product' => $product]);
    }

    public function search(Request $request)
    {
        $where = function ($query) use ($request) {
            $keyword = '%' . $request->keyword . '%';
            $query->where('name', 'like', $keyword);

            if ($request->has('sale_num') and $request->sale_num != '') {

                $query->orderby('sale_num', 'desc');
            }
            if ($request->has('price_desc') and $request->price_desc != '') {

                $query->orderby('price_desc', 'desc');
            }
            if ($request->has('price_asc') and $request->price_asc != '') {

                $query->orderby('price_asc', 'asc');
            }

        };
        $products = Product::where($where)->where('id','>',0)->paginate($request->total);
        $page = isset($page) ? $request['page'] : 1;
        $products = $products->appends(array(
            'page' => $page,
            'keyword' => $request->keyword,
            'sale_num' => $request->sale_num,
            'price_desc' => $request->price_desc,
            'price_asc' => $request->price_asc,
        ));

        return $this->array(['list' => $products]);
    }


    public function customer(Request $request)
    {
        $openid = $request->openid;
        if (!$openid) {
            return $this->error(2);
        }
        $customer = Customer::where('openid', $openid)->first();
        return $this->array(['customer' => $customer]);
    }

    public function address(Request $request)
    {
        $openid = $request->openid;
        if (!$openid) {
            return $this->error(2);
        }
        $customer = Customer::where('openid', $openid)->first();
        $addresses = Address::where('customer_id', $customer->id)->get();
        return $this->array(['addresses' => $addresses]);
    }

    public function add_address(Request $request)
    {
        $openid = $request->openid;
        if (!$openid) {
            return $this->error(2);
        }
        $customer = Customer::where('openid', $openid)->first();

        try {
            $messages = [
                'name.required' => '姓名不能为空!',
                'tel.required' => '手机号不能为空!',
                'pca.required' => '地址不能为空!',
                'detail.required' => '详细地址不能为空!',
            ];
            $rules = [
                'name' => 'required',
                'tel' => 'required',
                'pca' => 'required',
                'detail' => 'required',
            ];
            $validator = Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                $error = $validator->errors()->first();

                $this->error(500, $error);
            }
            $num=Address::where('customer_id', $customer->id)->where('is_default',1)->count();
            $is_default=$request->is_default;
            if($num==0){
                $is_default=1;
            }else{

                if($is_default==1){
                    Address::where('customer_id', $customer->id)->update(['is_default' => 0]);
                }
            }


            $pca = explode(",", $request->pca);
            Address::create([
                'customer_id' => $customer->id,
                'is_default' => $is_default,
                'name' => $request->name,
                'province' => $pca[0],
                'city' => $pca[1],
                'area' => $pca[2],
                'tel' => $request->tel,
                'detail' => $request->detail,
            ]);

        } catch (\Exception $exception) {
            Log::error($exception->getMessage());

            $this->error(500, $exception->getMessage());
        }

        return $this->null();
    }

    public function edit_address(Request $request, $id)
    {
        $openid = $request->openid;
        if (!$openid) {
            return $this->error(2);
        }
        $customer = Customer::where('openid', $openid)->first();

        $address = Address::find($id);

        return $this->array(['address' => $address]);
    }

    public function update_address(Request $request)
    {
        $id = $request->id;
        $openid = $request->openid;
        if (!$openid) {
            return $this->error(2);
        }
        $customer = Customer::where('openid', $openid)->first();

        $pca = explode(",", $request->pca);
        $is_default=$request->is_default;
        if($is_default==1){
            Address::where('customer_id', $customer->id)->update(['is_default' => 0]);
        }
        Address::where('id', $id)->update([
            'name' => $request->name,
            'province' => $pca[0],
            'city' => $pca[1],
            'area' => $pca[2],
            'tel' => $request->tel,
            'detail' => $request->detail,
            'is_default' => $is_default,
        ]);

        return $this->null();
    }

    public function default_address(Request $request)
    {
        $openid = $request->openid;
        $address_id = $request->address_id;
        if (!$openid) {
            return $this->error(2);
        }
        $customer = Customer::where('openid', $openid)->first();

        Address::where('customer_id', $customer->id)->update(['is_default' => 0]);

        Address::where('id', $address_id)->update(['is_default' => 1]);

        return $this->null();
    }

    public function delete_address(Request $request)
    {
        $openid = $request->openid;
        $address_id = $request->address_id;
        if (!$openid) {
            return $this->error(2);
        }
        $customer = Customer::where('openid', $openid)->first();

        Address::where('customer_id', $customer->id)->where('id', $address_id)->delete();

        return $this->null();
    }


    public function coin(Request $request)
    {
        $openid = $request->openid;
        if (!$openid) {
            return $this->error(2);
        }
        $customer = Customer::where('openid', $openid)->first();

        $coins = Coin::where('log_name', 'coin')->where('causer_id', $customer->id)->paginate($request->total);
        $page = isset($page) ? $request['page'] : 1;

        $coins = $coins->appends(array(
            'page' => $page,
        ));

        return $this->array(['list' => $coins]);
    }

    public function do_coin(Request $request)
    {
        $openid = $request->openid;

        $configs = Config::first();
        $coin = $configs->coin;

        if (!$openid) {
            return $this->error(2);
        }
        $customer = Customer::where('openid', $openid)->first();

        $is_get = Coin::where('log_name', 'coin')->where('causer_id', $customer->id)->orderby('created_at', 'desc')->first();

        if ($is_get) {
            $get_time = date('Y-m-d', strtotime($is_get->created_at));
            $now = date('Y-m-d');

            if ($get_time == $now) {

                return $this->error(3);
            }
        }

        activity()->inLog('coin')
            ->performedOn($customer)
            ->causedBy($customer)
            ->withProperties(['type' => 1, 'coin' => $coin])
            ->log('每日签到积分');
        $customer->coin+=$coin;
        $customer->save();

        return $this->null();
    }

    public function draw()
    {
        $products=Product::where('is_prize',true)->limit(7)->select('id','name','image')->get()->toarray();

        return $this->array(['list' => $products]);
    }

    /*
     * 经典的概率算法，
     * $proArr是一个预先设置的数组，
     * 假设数组为：array(100,200,300，400)，
     * 开始是从1,1000 这个概率范围内筛选第一个数是否在他的出现概率范围之内，
     * 如果不在，则将概率空间，也就是k的值减去刚刚的那个数字的概率空间，
     * 在本例当中就是减去100，也就是说第二个数是在1，900这个范围内筛选的。
     * 这样 筛选到最终，总会有一个数满足要求。
     * 就相当于去一个箱子里摸东西，
     * 第一个不是，第二个不是，第三个还不是，那最后一个一定是。
     * 这个算法简单，而且效率非常高，
     * 这个算法在大数据量的项目中效率非常棒。
     */
    function get_rand($proArr) {
        $result = '';
        //概率数组的总概率精度
        $proSum = array_sum($proArr);
        //概率数组循环
        foreach ($proArr as $key => $proCur) {
            $randNum = mt_rand(1, $proSum);
            if ($randNum <= $proCur) {
                $result = $key;
                break;
            } else {
                $proSum -= $proCur;
            }
        }
        unset ($proArr);
        return $result;
    }

    public function do_draw(Request $request)
    {
        $openid = $request->openid;
        $coin = $request->coin;

        if (!$openid) {
            return $this->error(2);
        }
        $customer = Customer::where('openid', $openid)->first();

        if ($customer->coin<$coin){
            return $this->error(4);
        }
        $customer->coin=$customer->coin-$coin;
        $customer->save();
        activity()->inLog('coin')
            ->performedOn($customer)
            ->causedBy($customer)
            ->withProperties(['type' => 0, 'coin' => $coin])
            ->log($coin . '积分抽奖');
        //开始抽奖
        $prize_arr=Product::where('is_prize',true)->limit(8)->select('id','name','image','rate_o','rate_f')->get()->toarray();

        /*
         * 每次前端页面的请求，PHP循环奖项设置数组，
         * 通过概率计算函数get_rand获取抽中的奖项id。
         * 将中奖奖品保存在数组$res['yes']中，
         * 而剩下的未中奖的信息保存在$res['no']中，
         * 最后输出json个数数据给前端页面。
         */
        $rate_type=$coin==10?'rate_o':'rate_f';
        foreach ($prize_arr as $key => $val) {
            $arr[$val['id']] = $val[$rate_type];
        }
        $result = $this->get_rand($arr); //根据概率获取奖项id

        //抽奖完成
        if ($result>0){
            $product=Product::find($result);
            $product->sale_num+=1;
            $product->save();

            $order_sn = date('YmdHms', time()) . $customer->id. $result;
            $address=Address::where('customer_id',$customer->id)->where('is_default',true)->first();

            Order::create([
                'customer_id' => $customer->id,
                'order_sn' => $order_sn,
                'product_id' => $result,
                'total_price' => $product->price,
                'status' => 2,
                'name' => $address?$address->name:'',
                'tel' => $address?$address->tel:'',
                'pay_time' => date('Y-m-d H:i:s',time()),
                'address' => $address?$address->province.''.$address->city.''.$address->area.''.$address->detail:'',
            ]);

            if(!$address){
                return $this->error(6);
            }
            activity()->inLog('draw')
                ->performedOn($product)
                ->causedBy($customer)
                ->withProperties([])
                ->log('抽中'.$product->name);

            return $this->object($product);
        }

        return $this->error(5);

    }

    public function order(Request $request)
    {

        $openid = $request->openid;
        if (!$openid) {
            return $this->error(2);
        }
        $customer = Customer::where('openid', $openid)->first();

        //多条件查找
        $where = function ($query) use ($request, $customer) {
            $query->where('customer_id', $customer->id);

            switch ($request->status) {
                case '':
                    break;
                case '1':
                    $query->where('status', 1);
                    break;
                case '2':
                    $query->where('status', 2);
                    break;
                case '3':
                    $query->where('status', 3);
                    break;
                case '4':
                    $query->where('status', 4);
                    break;
            }
        };
        $orders = Order::where($where)->paginate($request->total);

        $page = isset($page) ? $request['page'] : 1;
        $orders = $orders->appends(array(
            'page' => $page,
            'status' => $request->status,
        ));

        return $this->array(['list' => $orders]);
    }

    public function finish(Request $request)
    {
        $openid = $request->openid;
        $order_id = $request->order_id;
        if (!$openid) {
            return $this->error(2);
        }
        $customer = Customer::where('openid', $openid)->first();

        $order = Order::where('customer_id', $customer->id)->find($order_id);
        if ($order->status == 3) {
            $order->status = 4;
            $order->save();
        }

        return $this->null();
    }

    public function order_address(Request $request)
    {
        $openid = $request->openid;
        $order_id = $request->order_id;

        $name = $request->name;
        $tel = $request->tel;
        $address = $request->pca.$request->detail;

        if (!$openid) {
            return $this->error(2);
        }

        $customer = Customer::where('openid', $openid)->first();

        $order = Order::where('customer_id', $customer->id)->find($order_id);

        $order->name = $name;
        $order->tel = $tel;
        $order->address = $address;
        $order->save();

        return $this->null();
    }
}
