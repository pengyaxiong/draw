<?php

namespace App\Http\Controllers\Wechat;

use App\Handlers\WechatConfigHandler;
use App\Models\About;
use App\Models\Config;
use App\Models\Customer;
use App\Models\Problem;
use App\Models\ProblemCategory;
use App\Models\Shop\Address;
use App\Models\Shop\Cart;
use App\Models\Shop\Category;
use App\Models\Shop\Coin;
use App\Models\Shop\Comment;
use App\Models\Shop\Order;
use App\Models\Shop\OrderAddress;
use App\Models\Shop\OrderProduct;
use App\Models\Shop\Product;
use App\Models\Shop\Withdraw;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class IndexController extends Controller
{

    protected $wechat;

    public function __construct(WechatConfigHandler $wechat)
    {
        $this->wechat = $wechat;
    }

    public function auth(Request $request)
    {
        //声明CODE，获取小程序传过来的CODE
        $code = $request->code;
        //配置appid
        $appid = 'wxc17fa5e880635165';
        //配置appscret
        $secret = '05cc5f89ffe0f93d023937c3af593b5a';
        //api接口
        $api = "https://api.weixin.qq.com/sns/jscode2session?appid={$appid}&secret={$secret}&js_code={$code}&grant_type=authorization_code";

        $str = json_decode($this->httpGet($api), true);

//return $str;
        $openid = $str['openid'];

        $customer = Customer::where('openid', $openid)->first();

        if ($customer) {

            $register_url = 'https://' . $_SERVER['SERVER_NAME'] . '/code?code=' . $customer->code;

            QrCode::encoding('UTF-8')->format('png')->size(500)->generate($register_url, public_path('qrcodes/' . $openid . '.png'));

            $customer->update([
                'openid' => $openid,
                'headimgurl' => $request->headimgurl,
                'nickname' => $request->nickname,
                'tel' => $request->tel,
                'sex' => $request->sex,
            ]);

        } else {

            $invitation_code = substr($code, 0, 4) . substr($openid, 0, 2);

            $register_url = 'https://' . $_SERVER['SERVER_NAME'] . '/code?code=' . $invitation_code;

            QrCode::encoding('UTF-8')->format('png')->size(500)->generate($register_url, public_path('qrcodes/' . $openid . '.png'));

            $customer = Customer::create([
                'openid' => $openid,
                'code' => $invitation_code,
                'code_image' => 'https://' . $_SERVER['SERVER_NAME'] . '/qrcodes/' . $openid . '.png',
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

    //获取奖项
    function getDrawList()
    {
        $arrDraw = array('一等奖', '二等奖', '三等奖');
        return $arrDraw;
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

        $arrDraw = $this->getDrawList();
        $numbDraw = count($arrDraw);

        $info = [];
        for ($i = 1; $i < 10; $i++) {
            $Xing = $arrXing[mt_rand(0, $numbXing - 1)];
            $Ming = $arrMing[mt_rand(0, $numbMing - 1)];
            $Time = $arrTime[mt_rand(0, $numbTime - 1)];
            $Draw = $arrDraw[mt_rand(0, $numbDraw - 1)];

            $info[$i]['name'] = $Xing . $Ming;
            $info[$i]['draw'] = $Draw;
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

    public function about()
    {
        $about = About::first();
        return $this->array(['about' => $about]);
    }

    public function problem_category()
    {
        $categories = ProblemCategory::with(['problems' => function ($query) {
            $query->orderby('sort_order')->get();
        }])->orderby('sort_order')->get();

        return $this->array(['categories' => $categories]);
    }

    public function problem($id)
    {
        $problem = Problem::find($id);
        return $this->array(['problem' => $problem]);
    }

    public function categories()
    {
        $categories = Category::with(['children' => function ($query) {
            $query->orderby('sort_order')->get();
        }])->where('is_show', true)->where('parent_id', 0)->orderby('pinyin')->get();//->groupby('pinyin');

        return $this->array(['categories' => $categories]);
    }

    public function category_child($id)
    {
        $category = Category::with('children')->find($id);

        return $this->array(['category' => $category]);
    }

    public function category(Request $request, $id)
    {
        // //多条件查找
        // $where = function ($query) use ($request, $id) {

        //     $query->where('category_id', $id);
        // };

        $products = Product::where('is_sale', true)->where('category_id', $id)->paginate($request->total);
        if ($request->has('sale_num') and $request->sale_num != '') {
            $products = Product::where('is_sale', true)->where('category_id', $id)->orderby('sale_num', 'desc')->paginate($request->total);

        }
        if ($request->has('price_desc') and $request->price_desc != '') {
            $products = Product::where('is_sale', true)->where('category_id', $id)->orderby('price', 'desc')->paginate($request->total);

        }
        if ($request->has('price_asc') and $request->price_asc != '') {
            $products = Product::where('is_sale', true)->where('category_id', $id)->orderby('price', 'asc')->paginate($request->total);

        }


        $page = isset($page) ? $request['page'] : 1;
        $products = $products->appends(array(
            'page' => $page,
            'sale_num' => $request->sale_num,
        ));

        return $this->array(['list' => $products]);
    }

    public function coin_category(Request $request)
    {
        //多条件查找
        $where = function ($query) use ($request) {

            if ($request->has('coin_search') and $request->coin_search != '') {
                $min = explode('-', $request->coin_search)[0];
                $max = explode('-', $request->coin_search)[1];
                $query->whereBetween('coin', [$min, $max]);
            }
            $query->where('is_sale', true);
            $query->where('is_exchange', true);
        };

        $products = Product::where($where)->orderby('sort_order')->paginate($request->total);

        $page = isset($page) ? $request['page'] : 1;
        $products = $products->appends(array(
            'page' => $page,
        ));

        return $this->array(['list' => $products]);
    }

    public function product($id)
    {
        $product = Product::with('comments')->find($id);
        return $this->array(['product' => $product]);
    }

    public function product_comments(Request $request)
    {
        //多条件查找
        $where = function ($query) use ($request) {
            if ($request->has('good') and $request->good != '') {
                $query->where('grade', '>=', 3);
            }
            if ($request->has('bad') and $request->bad != '') {
                $query->where('grade', '<', 3);
            }
            if ($request->has('photo') and $request->photo != '') {
                $query->where('images', true);
            }
            $query->where('product_id', $request->product_id);
        };
        if ($request->has('new') and $request->new != '') {
            $comments = Comment::with('customer')->where($where)->orderby('created_at', 'desc')->paginate($request->total);
        } else {
            $comments = Comment::with('customer')->where($where)->paginate($request->total);
        }

        $page = isset($page) ? $request['page'] : 1;
        $comments = $comments->appends(array(
            'page' => $page,
            'product_id' => $request->product_id,
        ));

        return $this->array(['list' => $comments]);
    }

    public function search(Request $request)
    {
        $keyword = '%' . $request->keyword . '%';
        //  $where = function ($query) use ($request) {
        //     $keyword = '%' . $request->keyword . '%';
        //     $query->where('name', 'like', $keyword);

        //     if ($request->has('sale_num') and $request->sale_num != '') {

        //         $query->orderby('sale_num', 'desc');
        //     }
        //     if ($request->has('price_desc') and $request->price_desc != '') {

        //         $query->orderby('price', 'desc');
        //     }
        //     if ($request->has('price_asc') and $request->price_asc != '') {

        //         $query->orderby('price', 'asc');
        //     }

        // };
        $products = Product::where('is_sale', true)->where('name', 'like', $keyword)->where('id', '>', 0)->paginate($request->total);
        if ($request->has('sale_num') and $request->sale_num != '') {
            $products = Product::where('is_sale', true)->where('name', 'like', $keyword)->where('id', '>', 0)->orderby('sale_num', 'desc')->paginate($request->total);

        }
        if ($request->has('price_desc') and $request->price_desc != '') {
            $products = Product::where('is_sale', true)->where('name', 'like', $keyword)->where('id', '>', 0)->orderby('price', 'desc')->paginate($request->total);

        }
        if ($request->has('price_asc') and $request->price_asc != '') {
            $products = Product::where('is_sale', true)->where('name', 'like', $keyword)->where('id', '>', 0)->orderby('price', 'asc')->paginate($request->total);

        }

        //    $products = Product::where($where)->where('id','>',0)->paginate($request->total);
        $page = isset($page) ? $request['page'] : 1;
        $products = $products->appends(array(
            'page' => $page,
            'keyword' => $request->keyword,
            'sale_num' => $request->sale_num,
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
            $num = Address::where('customer_id', $customer->id)->where('is_default', 1)->count();
            $is_default = $request->is_default;
            if ($num == 0) {
                $is_default = 1;
            } else {

                if ($is_default == 1) {
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
        $is_default = $request->is_default;
        if ($is_default == 1) {
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

    //我的积分记录
    public function coin(Request $request)
    {
        $openid = $request->openid;
        if (!$openid) {
            return $this->error(2);
        }
        $customer = Customer::where('openid', $openid)->first();

        $coins = Coin::wherein('log_name', ['coin','get_coin','share_coin'])->where('causer_id', $customer->id)->paginate($request->total);
        $page = isset($page) ? $request['page'] : 1;

        $coins = $coins->appends(array(
            'page' => $page,
        ));

        return $this->array(['list' => $coins]);
    }


    public function draw()
    {
        $products = Product::where('is_sale', true)->where('is_prize', true)->limit(7)->select('id', 'name', 'image')->get()->toarray();

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
    function get_rand($proArr)
    {
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
        $configs = Config::first();
        $draw_coin = $configs->draw_coin;

        $openid = $request->openid;
        $coin = $request->coin ? $request->coin : $draw_coin;


        if (!$openid) {
            return $this->error(2);
        }
        $customer = Customer::where('openid', $openid)->first();

        if ($customer->coin < $coin) {
            return $this->error(4);
        }
        $customer->coin = $customer->coin - $coin;
        $customer->save();
        activity()->inLog('coin')
            ->performedOn($customer)
            ->causedBy($customer)
            ->withProperties(['type' => '-', 'num' => $coin])
            ->log($coin . '积分抽奖');
        //开始抽奖
        $prize_arr = Product::where('is_prize', true)->limit(8)->select('id', 'name', 'image', 'rate_o', 'rate_f', 'rate_d')->get()->toarray();

        /*
         * 每次前端页面的请求，PHP循环奖项设置数组，
         * 通过概率计算函数get_rand获取抽中的奖项id。
         * 将中奖奖品保存在数组$res['yes']中，
         * 而剩下的未中奖的信息保存在$res['no']中，
         * 最后输出json个数数据给前端页面。
         */
        if ($coin == 10) {
            $rate_type = 'rate_o';
        } elseif ($coin == 50) {
            $rate_type = 'rate_f';
        } else {
            $rate_type = 'rate_d';
        }
        foreach ($prize_arr as $key => $val) {
            $arr[$val['id']] = $val[$rate_type];
        }
        $result = $this->get_rand($arr); //根据概率获取奖项id

        //抽奖完成
        if ($result > 0) {
            $product = Product::find($result);
            $product->sale_num += 1;
            $product->stock -= 1;
            $product->save();

            $order_sn = date('YmdHms', time()) . $customer->id . $result;
            $address = Address::where('customer_id', $customer->id)->where('is_default', true)->first();

            $order = Order::create([
                'customer_id' => $customer->id,
                'order_sn' => $order_sn,
                'total_price' => $product->price,
                'status' => 2,
                'pay_type' => 2,
                'pay_time' => date('Y-m-d H:i:s', time()),
                'address_id' => $address->id,
            ]);

            $order->order_products()->create(['product_id' => $product->id, 'num' => 1, 'price' => $product->price, 'sku' => '']);

            $order->address()->create([
                'province' => $address->province,
                'city' => $address->city,
                'area' => $address->area,
                'detail' => $address->detail,
                'tel' => $address->tel,
                'name' => $address->name
            ]);
            // if(!$address){
            //     return $this->error(6);
            // }
            activity()->inLog('draw')
                ->performedOn($product)
                ->causedBy($customer)
                ->withProperties([])
                ->log('抽中' . $product->name);

            return $this->object($product);
        }

        return $this->error(5);

    }

    public function exchange(Request $request)
    {
        $product = Product::find($request->product_id);
        $openid = $request->openid;
        $coin = $product ? $product->coin : 999999;
        if (!$openid) {
            return $this->error(2);
        }
        $customer = Customer::where('openid', $openid)->first();

        if ($customer->coin < $coin) {
            return $this->error(4);
        }

        $product->sale_num += 1;
        $product->exchange_num += 1;
        $product->stock -= 1;
        $product->save();

        $order_sn = date('YmdHms', time()) . $customer->id;
        $address = Address::where('customer_id', $customer->id)->where('is_default', true)->first();

        $order = Order::create([
            'customer_id' => $customer->id,
            'order_sn' => $order_sn,
            'total_price' => $product->price,
            'total_coin' => $coin,
            'status' => 2,
            'pay_type' => 3,
            'pay_time' => date('Y-m-d H:i:s', time()),
            'address_id' => $address->id,
        ]);

        $order->order_products()->create(['product_id' => $product->id, 'num' => 1, 'price' => $product->price, 'sku' => '']);

        $order->address()->create([
            'province' => $address->province,
            'city' => $address->city,
            'area' => $address->area,
            'detail' => $address->detail,
            'tel' => $address->tel,
            'name' => $address->name
        ]);

        activity()->inLog('coin')
            ->performedOn($product)
            ->causedBy($customer)
            ->withProperties(['type' => '-', 'num' => $coin])
            ->log('积分兑换' . $product->name);

        return $this->object($product);
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
                case '5':
                    $query->where('status', 5);
                    break;
            }
        };
        $orders = Order::with(['order_products.product', 'address'])->where($where)->orderby('created_at', 'desc')->paginate($request->total);

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

    public function order_info(Request $request, $id)
    {
        $openid = $request->openid;
        if (!$openid) {
            return $this->error(2);
        }
        $customer = Customer::where('openid', $openid)->first();

        $order = Order::with(['order_products.product.category', 'address'])->where('customer_id', $customer->id)->find($id);


        return $this->array(['order' => $order]);
    }

    public function order_address(Request $request)
    {
        $openid = $request->openid;
        $order_id = $request->order_id;

        $name = $request->name;
        $tel = $request->tel;
        $address = explode(",", $request->pca);
        $detail = $request->detail;


        if (!$openid) {
            return $this->error(2);
        }

        $customer = Customer::where('openid', $openid)->first();

        $order_address = OrderAddress::where('order_id', $order_id)->fist();

        $order_address->name = $name;
        $order_address->tel = $tel;
        $order_address->province = $address[0];
        $order_address->city = $address[1];
        $order_address->area = $address[2];
        $order_address->detail = $detail;
        $order_address->save();

        return $this->null();
    }

    /**
     * 二期
     */
    public function group(Request $request)
    {
        $openid = $request->openid;
        if (!$openid) {
            return $this->error(2);
        }
        $type = $request->type;  //0直接邀请 1间接邀请
        $customer = Customer::where('openid', $openid)->first();

        $customer_id = $customer->id;

        if ($type == 0) {
            $customers = Customer::where('parent_id', $customer_id)->paginate($request->total);
        } else {
            $ps = Customer::where('parent_id', $customer_id)->pluck('id');
            $customers = Customer::wherein('parent_id', $ps)->paginate($request->total);
        }
        $page = isset($page) ? $request['page'] : 1;
        $customers = $customers->appends(array(
            'page' => $page,
        ));

        return $this->array(['list' => $customers]);
    }

    //余额记录
    public function money(Request $request)
    {
        $openid = $request->openid;
        if (!$openid) {
            return $this->error(2);
        }

        $customer = Customer::where('openid', $openid)->first();

        $coins = Coin::where('log_name', 'money')->where('subject_id', $customer->id)->paginate($request->total);

        foreach ($coins as $key => $coin) {
            $coins[$key]['customer'] = Customer::find($coin['causer_id']);
        }
        $page = isset($page) ? $request['page'] : 1;

        $coins = $coins->appends(array(
            'page' => $page,
        ));

        return $this->array(['list' => $coins]);
    }

    //提现记录
    public function withdraw(Request $request)
    {
        $openid = $request->openid;
        if (!$openid) {
            return $this->error(2);
        }
        $customer = Customer::where('openid', $openid)->first();

        $withdraws = Withdraw::where('customer_id', $customer->id)->paginate($request->total);

        $page = isset($page) ? $request['page'] : 1;

        $withdraws = $withdraws->appends(array(
            'page' => $page,
        ));

        return $this->array(['list' => $withdraws]);
    }

    //邀请用户
    public function code(Request $request)
    {

        $openid = $request->openid;
        if (!$openid) {
            return $this->error(2);
        }
        $customer = Customer::where('openid', $openid)->first();

        $code = $request->code;
        $customer_id = $customer->id;
        $customer = Customer::find($customer_id);

        $parent = Customer::where('code', $code)->first();

        if ($customer->parent_id > 0) {
            return $this->error(500, '你已经绑定过');
        }

        if (!empty($parent)) {
            $configs = Config::first();
            $share_coin = $configs->share_coin;

            activity()->inLog('share_coin')
                ->performedOn($parent)
                ->causedBy($parent)
                ->withProperties(['type' => '+', 'num' => $share_coin])
                ->log('邀请新用户奖励积分');
            $parent->coin += $share_coin;
            $parent->save();


            $customer->parent_id = $parent->id;
            $customer->save();
            return $this->null();
        } else {
            return $this->error(500, '邀请码错误！');
        }
    }

    //每日领取积分
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
            ->withProperties(['type' => '+', 'num' => $coin])
            ->log('每日签到奖励积分');
        $customer->coin += $coin;
        $customer->save();

        return $this->null();
    }

    //分享就有积分，一天一次
    public function get_coin(Request $request)
    {
        $openid = $request->openid;
        if (!$openid) {
            return $this->error(2);
        }
        $customer = Customer::where('openid', $openid)->first();

        $configs = Config::first();
        $coin = $configs->get_coin;

        $is_get = Coin::where('log_name', 'get_coin')->where('causer_id', $customer->id)->orderby('created_at', 'desc')->first();

        if ($is_get) {
            $get_time = date('Y-m-d', strtotime($is_get->created_at));
            $now = date('Y-m-d');

            if ($get_time == $now) {

                return $this->error(3);
            }
        }

        activity()->inLog('get_coin')
            ->performedOn($customer)
            ->causedBy($customer)
            ->withProperties(['type' => '+', 'num' => $coin])
            ->log('每日分享奖励积分');
        $customer->coin += $coin;
        $customer->save();

        return $this->null();

    }

    public function do_withdraw(Request $request)
    {

        $openid = $request->openid;
        if (!$openid) {
            return $this->error(2);
        }
        $customer = Customer::where('openid', $openid)->first();

        try {
            $messages = [
                'money.required' => '提现金额不能为空!',
                'alipay.required' => '支付宝不能为空!',
                'name.required' => '收款人姓名不能为空!',
            ];
            $rules = [
                'money' => 'required',
                'alipay' => 'required',
                'name' => 'required',
            ];
            $validator = Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                $error = $validator->errors()->first();

                return $this->error(500, $error);
            }

            // $request->offsetSet('customer_id', $customer->id);


            Withdraw::create([
                'customer_id' => $customer->id,
                'money' => $request->money,
                'name' => $request->name,
                'alipay' => $request->alipay,
            ]);

        } catch (\Exception $exception) {
            Log::error($exception->getMessage());

            return $this->error(500, $exception->getMessage());
        }

        return $this->null();
    }

    public function upload_img(Request $request)
    {

        if ($request->hasFile('file') and $request->file('file')->isValid()) {

            //文件大小判断$filePath
            $max_size = 1024 * 1024 * 3;
            $size = $request->file('file')->getClientSize();
            if ($size > $max_size) {
                return $this->error(500, '文件大小不能超过3M！');
            }

            $path = $request->file->store('upload', 'public');

            return $this->array(['image' => '/' . $path, 'image_url' => '/' . $path]);

        }
    }

    function add_cart(Request $request)
    {
        $openid = $request->openid;
        if (!$openid) {
            return $this->error(2);
        }
        $customer = Customer::where('openid', $openid)->first();
        $addresses = Address::where('customer_id', $customer->id)->get();
        if (empty($addresses)) {
            return $this->error(500, '请填写收货地址');
        }
        //判断购物车是否有当前商品,如果有,那么 num +1
        $product_id = $request->product_id;

        $cart = Cart::where('product_id', $product_id)->where('sku', $request->sku)->where('customer_id', $customer->id)->first();

        if ($cart) {
            Cart::where('id', $cart->id)->increment('num');
        } else {
            //否则购物车表,创建新数据
            $cart = Cart::create([
                'product_id' => $request->product_id,
                'num' => $request->num,
                'sku' => $request->sku,
                'customer_id' => $customer->id,
            ]);
        }

        return $this->object('添加到购物车', $cart);
    }

    public function cart(Request $request)
    {
        $openid = $request->openid;
        if (!$openid) {
            return $this->error(2);
        }
        $customer = Customer::where('openid', $openid)->first();

        $carts = Cart::with('product')->where('customer_id', $customer->id)->get();

        $count = Cart::count_cart($carts, $customer->id);


        return $this->array(['customer' => $customer, 'carts' => $carts, 'count' => $count,]);
    }

    function change_num(Request $request)
    {
        $openid = $request->openid;
        if (!$openid) {
            return $this->error(2);
        }
        $customer = Customer::where('openid', $openid)->first();

        if ($request->type == 'add') {
            Cart::where('id', $request->id)->increment('num');
        } else {
            Cart::where('id', $request->id)->decrement('num');
        }
        $count = Cart::count_cart('', $customer->id);
        // return $count;
        return $this->array($count);
    }

    function destroy_checked(Request $request)
    {
        $openid = $request->openid;
        if (!$openid) {
            return $this->error(2);
        }
        $customer = Customer::where('openid', $openid)->first();

        $checked_id = explode(',', $request->checked_id);
        Cart::wherein('id', $checked_id)->delete();

        $count = Cart::count_cart('', $customer->id);

        return $this->array($count);
    }

    public function del_order(Request $request)
    {
        $openid = $request->openid;
        if (!$openid) {
            return $this->error(2);
        }
        $customer = Customer::where('openid', $openid)->first();

        $id = $request->order_id;

        Order::where('customer_id', $customer->id)->where('id', $id)->delete();

        return $this->null();
    }

    /**
     * @param Request $request
     * @return array
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    public function refund(Request $request)
    {
        $order_id = $request->order_id;
        $order = Order::find($order_id);
        $total_price = $order ? $order->total_price : 9999;
        $openid = $request->openid;
        if (!$openid) {
            return $this->error(2);
        }
        $customer = Customer::where('openid', $openid)->first();

        $out_refund_no = date('YmdHms', time()) . '_' . $customer->id;//商户系统内部的退款单号
        $out_trade_no = $order->order_sn;//商户系统内部订单号
        $total_fee = $total_price * 100;
        $refund_fee = $total_price * 100;
        $app = $this->wechat->pay();

        // 参数分别为：微信订单号、商户退款单号、订单金额、退款金额、其他参数
        $result = $app->refund->byOutTradeNumber($out_trade_no, $out_refund_no, $total_fee, $refund_fee, [
            // 可在此处传入其他参数，详细参数见微信支付文档
            'refund_desc' => '退款',
            'notify_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/api/wechat/refund_back',
        ]);

        if ($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS') {
            return $this->error(200, '退款申请请求成功', 200);
        }

        return $this->error(500, '退款申请请求失败~');
    }


    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \EasyWeChat\Kernel\Exceptions\Exception
     */
    public function refund_back(Request $request)
    {
        $app = $this->wechat->pay();
        $response = $app->handleRefundedNotify(function ($message, $reqInfo, $fail) use ($request) {
            // 其中 $message['req_info'] 获取到的是加密信息
            // $reqInfo 为 message['req_info'] 解密后的信息

            $order = Order::where('order_sn', $reqInfo['out_trade_no'])->first();

            if (!$order || $order->status == '6') { // 如果订单不存在 或者 订单已经退过款了
                return $this->error(200, '退款成功~', 200); // 告诉微信，我已经处理完了，订单没找到，别再通知我了
            }
            if ($message['return_code'] == 'SUCCESS') {
                if ($reqInfo['refund_status'] == 'SUCCESS') {
                    $order->finish_time = date('Y-m-d H:i:s', time());
                    $order->status = 6;
                    $order->save();

                    $customer_id = $order->customer_id;
                    $customer = Customer::find($customer_id);

                    $activity = activity()->inLog('refund')
                        ->performedOn($customer)
                        ->withProperties(['type' => '+', 'num' => $order->total_price])
                        ->causedBy($customer)
                        ->log("微信退款");
                }
                return $this->error(200, '退款成功~', 200); // 返回 true 告诉微信“我已处理完成”
                // 或返回错误原因 $fail('参数格式校验错误');
            } else {
                return $fail('参数格式校验错误');
            }

        });

        return $response;
    }

    /**
     * 购物车点击结算跳到下单页面，即check_out
     * 此页面需要的数据：用户的收货地址；要购买的商品信息；若购物车没有商品，跳回购物车页面。
     */
    public function checkout(Request $request)
    {
        $openid = $request->openid;
        if (!$openid) {
            return $this->error(2);
        }
        $customer = Customer::where('openid', $openid)->first();

        if ($request->cart_id) {
            $cart_id = $request->cart_id;
            $cart_id_ = explode(',', $cart_id);
            $carts = Cart::with('product')->whereIn('id', $cart_id_)->get();

            $count = Cart::count_cart($carts, $customer->id);
        }
        if ($request->product_id) {
            $carts = [];
            $product = Product::find($request->product_id);
            $total_price = $product->price;

            $carts[0]['product'] = $product;
            $carts[0]['num'] = $request->num;
            $carts[0]['sku'] = $request->sku;

            $count['num'] = $request->num;
            $count['total_price'] = $total_price * $request->num;;
        }
        $address = Address::find($customer->address_id);

        return $this->array(['carts' => $carts, 'count' => $count, 'address' => $address]);
    }

    public function add_order(Request $request)
    {
        $openid = $request->openid;
        if (!$openid) {
            return $this->error(2);
        }
        $customer = Customer::where('openid', $openid)->first();

        $product_id = $request->product_id;
        $cart_id = $request->cart_id;

        $order_sn = date('YmdHms', time()) . '_' . $customer->id;

        if ($product_id) {
            $product = Product::find($product_id);
            $total_price = $product->price;


            $num = $request->num ? $request->num : 1;
            $product->sale_num += $num;
            $product->stock -= $num;
            $product->save();

            $order = Order::create([
                'customer_id' => $customer->id,
                'order_sn' => $order_sn,
                'address_id' => $request->address_id,
                'total_price' => $total_price * $num,
                'remark' => $request->remark,
            ]);
            $address = Address::find($request->address_id);
            $order->address()->create([
                'province' => $address->province,
                'city' => $address->city,
                'area' => $address->area,
                'detail' => $address->detail,
                'tel' => $address->tel,
                'name' => $address->name
            ]);

            $order->order_products()->create(['product_id' => $product_id, 'price' => $product->price, 'num' => $request->num, 'sku' => $request->sku]);
            $result = Order::with(['order_products.product', 'address'])->find($order->id);
        }

        if ($cart_id) {

            $cart_id_ = explode(',', $cart_id);
            $carts = Cart::with('product')->whereIn('id', $cart_id_)->get();

            if (count($carts) < 1) {
                return $this->error(500, '请勿重复下单~');
            }

            $count = Cart::count_cart($carts, $customer->id);
            $total_price = $count['total_price'];

            $order = Order::create([
                'customer_id' => $customer->id,
                'order_sn' => $order_sn,
                'address_id' => $request->address_id,
                'total_price' => $total_price,
                'remark' => $request->remark,
            ]);
            $address = Address::find($request->address_id);
            $order->address()->create([
                'province' => $address->province,
                'city' => $address->city,
                'area' => $address->area,
                'detail' => $address->detail,
                'tel' => $address->tel,
                'name' => $address->name
            ]);

            foreach ($carts as $cart) {
                $product = Product::find($cart['product_id']);

                $product->sale_num += $cart->num;
                $product->stock -= $cart->num;
                $product->save();

                $result_ = $order->order_products()->create(['product_id' => $cart->product_id, 'price' => $product->price, 'num' => $cart->num, 'sku' => $cart->sku]);
                if ($result_) {
                    Cart::destroy($cart->id);
                }
            }
            $result = Order::with(['order_products.product', 'address'])->find($order->id);
        }

        return $this->array(['order' => $result]);

    }

    public function pay(Request $request)
    {

        $openid = $request->openid;
        if (!$openid) {
            return $this->error(2);
        }
        $customer = Customer::where('openid', $openid)->first();

        $address_id = $request->address_id;
        $cart_id = $request->cart_id;
        $order_id = $request->order_id;
        $remark = $request->remark;

        $app = $this->wechat->pay();

        $title = '';
        if ($order_id) {
            $order = Order::with('order_products.product')->find($order_id);
            $total_price = $order->total_price;
            $order_sn = $order->order_sn;
            $products = $order->order_products;
            foreach ($products as $product) {
                $title .= $product->product->name . '_';
            }

            $w_order = $app->order->queryByOutTradeNumber($order_sn);

            // if ($w_order['trade_state'] == "NOTPAY") {

            $order_config = [
                'body' => $title,
                'out_trade_no' => date('YmdHms', time()) . '_' . $customer->id,
                'total_fee' => $total_price * 100,
                //'spbill_create_ip' => '', // 可选，如不传该参数，SDK 将会自动获取相应 IP 地址
                'notify_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/api/wechat/paid', // 支付结果通知网址，如果不设置则会使用配置里的默认地址
                'trade_type' => 'JSAPI', // 请对应换成你的支付方式对应的值类型
                'openid' => $openid,
            ];

            $order->order_sn = $order_config['out_trade_no'];
            $order->save();

            //重新生成预支付生成订单
            $result = $app->order->unify($order_config);

            if ($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS') {
                $prepayId = $result['prepay_id'];

                $config = $app->jssdk->sdkConfig($prepayId);

                return response()->json($config);
            }
            // }

        } else {
            $carts = Cart::with('product')->whereIn('id', $cart_id)->get();
            $count = Cart::count_cart($carts, $customer->id);
            $total_price = $count['total_price'];
            $order_sn = date('YmdHms', time()) . '_' . $customer->id;

            foreach ($carts as $cart) {
                $title .= $cart->product->name . '_';
            }

            $order_config = [
                'body' => $title,
                'out_trade_no' => $order_sn,
                'total_fee' => $total_price * 100,
                //'spbill_create_ip' => '', // 可选，如不传该参数，SDK 将会自动获取相应 IP 地址
                'notify_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/api/wechat/paid', // 支付结果通知网址，如果不设置则会使用配置里的默认地址
                'trade_type' => 'JSAPI', // 请对应换成你的支付方式对应的值类型
                'openid' => $openid,
            ];

            //生成订单
            $result = $app->order->unify($order_config);
            if ($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS') {
                $order = Order::create([
                    'customer_id' => $customer->id,
                    'order_sn' => $order_sn,
                    'total_price' => $total_price,
                    'remark' => $remark,
                    'address_id' => $address_id,
                ]);
                $address = Address::find($address_id);
                $order->address()->create([
                    'province' => $address->province,
                    'city' => $address->city,
                    'area' => $address->area,
                    'detail' => $address->detail,
                    'tel' => $address->tel,
                    'name' => $address->name
                ]);

                foreach ($carts as $cart) {
                    $result_ = $order->order_products()->create(['product_id' => $cart->product_id, 'price' => $cart->product->price, 'sku' => $cart->sku, 'num' => $cart->num]);
                    if ($result_) {
                        Cart::destroy($cart->id);
                    }
                }
                $prepayId = $result['prepay_id'];

                $config = $app->jssdk->sdkConfig($prepayId);
                return response()->json($config);
            }
        }
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \EasyWeChat\Kernel\Exceptions\Exception
     */
    public function paid(Request $request)
    {
        $app = $this->wechat->pay();
        $response = $app->handlePaidNotify(function ($message, $fail) use ($request) {
            // 使用通知里的 "微信支付订单号" 或者 "商户订单号" 去自己的数据库找到订单
            $order = Order::where('order_sn', $message['out_trade_no'])->first();

            ///////////// <- 建议在这里调用微信的【订单查询】接口查一下该笔订单的情况，确认是已经支付 /////////////
            if ($message['return_code'] === 'SUCCESS') { // return_code 表示通信状态，不代表支付状态
                // 用户是否支付成功
                if (array_get($message, 'result_code') === 'SUCCESS') {
                    $order->pay_time = date('Y-m-d H:i:s', time()); // 更新支付时间为当前时间
                    $order->status = 2;
                    $order->save();

                    $customer_id = $order->customer_id;
                    $customer = Customer::find($customer_id);

                    activity()->inLog('buy')
                        ->performedOn($customer)
                        ->causedBy($order)
                        ->withProperties(['type' => '-', 'num' => $order->total_price])
                        ->log('购买商品');

                }
            } else {
                return $fail('通信失败，请稍后再通知我');
            }

            return true; // 返回处理完成
        });

        return $response;
    }

    public function comment(Request $request)
    {
        $openid = $request->openid;
        if (!$openid) {
            return $this->error(2);
        }
        $customer = Customer::where('openid', $openid)->first();

        $order = Order::with('order_products.product')->find($request->order_id);

        foreach ($order->order_products as $order_products) {

            Comment::create([
                'order_id' => $request->order_id,
                'product_id' => $order_products->product_id,
                'customer_id' => $customer->id,
                'images' => is_array($request->images) ? $request->images : [],
                'grade' => $request->grade,
                'content' => $request['content'],
            ]);
        }


        $pay_type = $order->pay_type;
        if ($pay_type == 1) {
            $order_product = $order->order_products->first();
            $product_name = $order_product->product->name;

            $total_price = $order->total_price;
            $commission_rate = Config::first()->commission_rate;
            $goods_rate = Config::first()->goods_rate;

            $g_num = $total_price * $goods_rate;
            $customer->coin += $g_num;
            $customer->save();
            activity()->inLog('coin')
                ->performedOn($customer)
                ->causedBy($order)
                ->withProperties(['type' => '+', 'num' => $g_num])
                ->log('购买商品反积分');

            $parent = Customer::find($customer->parent_id);
            if (!empty($parent) && $total_price > 0) {
                $num = $total_price * $commission_rate;
                $parent->money += $num;
                $parent->save();
                activity()->inLog('money')
                    ->performedOn($parent)
                    ->causedBy($customer)
                    ->withProperties(['type' => '+', 'num' => $num, 'product_name' => $product_name])
                    ->log('下级' . $customer->nickname . '购买商品返佣');
            }

        }
//        OrderProduct::where('order_id',$request->order_id)->where('product_id',$request->product_id)->update(['is_comment'=>1]);
        $order->status = 5;
        $order->comment_time = date('Y-m-d H:i:s', time());
        $order->finish_time = date('Y-m-d H:i:s', time());
        $order->save();

        return $this->null();
    }
}
