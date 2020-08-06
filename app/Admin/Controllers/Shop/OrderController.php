<?php

namespace App\Admin\Controllers\Shop;

use App\Models\Shop\Comment;
use App\Models\Shop\Order;
use App\Models\Shop\OrderAddress;
use App\Models\Shop\Product;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Table;
use App\Admin\Actions\Post\OrderConfirm;
use App\Admin\Actions\Post\OrderOver;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Layout\Column;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
class OrderController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '订单管理';
    protected $status = [];
    protected $pay_type = [];

    public function __construct()
    {
        $this->status = [1 => '待付款', 2 => '待发货', 3 => '待收货', 4 => '待评价', 5 => '已完成', 6 => '已退款'];
        $this->pay_type = [1 => '微信支付', 2 => '抽奖', 3 => '积分兑换'];
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Order());

        $grid->column('id', __('Id'));
        $grid->column('order_sn', __('Order sn'));
        $grid->column('customer.nickname', __('Customer id'));
        $grid->column('order_products', __('商品详情'))->display(function () {
            return '点击查看';
        })->expand(function ($model) {
            $order_products = $model->order_products;
            $data = [];
            foreach ($order_products as $key => $order_product) {
                $url = route('admin.shop.comment_info', [$order_product['order_id'], $order_product['product_id']]);

                $product = Product::find($order_product['product_id']);
                $data[$key]['id'] = $product['id'];
                $data[$key]['name'] = $product['name'];
                $data[$key]['num'] = $order_product['num'];
                $data[$key]['price'] = $order_product['price'];;
                $data[$key]['total_price'] = $order_product['num'] * $order_product['price'];
                $data[$key]['type'] = $order_product['sku'];

                $is_comment=Comment::where(['order_id'=>$order_product['order_id'],'product_id'=>$order_product['product_id']])->exists();

                $data[$key]['comment'] = $is_comment?'<div class="btn">
              <a class="btn btn-sm btn-default pull-right"  href="' . $url . '" rel="external" >
              <i class="fa fa-eye"></i> 查看</a>
                 </div>':'待评价';
            }

            return new Table(['ID', '商品名称', '数量', '单价', '小计', '规格', '评论'], $data);
        });
        $grid->column('status', __('Status'))->replace($this->status)->label([
            1 => 'default',
            2 => 'warning',
            3 => 'info',
            4 => 'primary',
            5 => 'success',
        ]);
        $grid->column('pay_type', __('Pay type'))->using($this->pay_type, '未知')->dot([
            1 => 'primary',
            2 => 'success',
            3 => 'info',
        ], 'warning');

        $grid->column('total_price', __('Total price'));
        $grid->column('pay_time', __('Pay time'));
        $grid->column('send_time', __('Send time'));
        $grid->column('finish_time', __('Finish time'));
        $grid->column('evaluate_time', __('Evaluate time'));

        $grid->column('address_id', __('地址'))->display(function ($model) {
            $address = OrderAddress::where('order_id', $this->id)->first();
            return $address->province . '-' . $address->city . '-' . $address->area . '-' . $address->detail . '-联系人:' . $address->name . '-联系电话:' . $address->tel;
        });
        $grid->column('express_name', __('Express name'));
        $grid->column('express_code', __('Express code'));
        $grid->column('created_at', __('Created at'))->hide();
        $grid->column('updated_at', __('Updated at'))->hide();

        $grid->filter(function ($filter) {
            $filter->equal('order_sn', __('Order sn'));
            $status_text = $this->status;
            $filter->equal('status', __('Status'))->select($status_text);

            $pay_type_text = $this->pay_type;
            $filter->equal('pay_type', __('Pay type'))->select($pay_type_text);

            $filter->between('created_at', __('Created at'))->date();
        });

        //禁用创建按钮
        $grid->disableCreateButton();
        $grid->actions(function ($actions) {
            $actions->disableView();
            //$actions->disableEdit();
            $actions->disableDelete();

            $actions->add(new OrderConfirm());
            $actions->add(new OrderOver());
        });

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Order::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('order_sn', __('Order sn'));
        $show->field('customer_id', __('Customer id'));
        $show->field('product_id', __('Product id'));
        $show->field('status', __('Status'));
        $show->field('pay_type', __('Pay type'));
        $show->field('total_price', __('Total price'));
        $show->field('pay_time', __('Pay time'));
        $show->field('send_time', __('Send time'));
        $show->field('finish_time', __('Finish time'));
        $show->field('address', __('Address'));
        $show->field('name', __('Name'));
        $show->field('tel', __('Tel'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Order());

        $form->text('order_sn', __('Order sn'))->disable();
        $form->text('customer.nickname', __('Customer id'))->disable();
        $form->text('product.name', __('商品名称'))->disable();


        $form->select('status', __('Status'))->options($this->status);
        $form->select('pay_type', __('Pay type'))->options($this->pay_type)->disable();
//        $states = [
//            'on' => ['value' => 1, 'text' => '微信支付', 'color' => 'success'],
//            'off' => ['value' => 0, 'text' => '其他', 'color' => 'danger'],
//        ];
//        $form->switch('pay_type', __('Pay type'))->states($states)->disable();

        $form->decimal('total_price', __('Total price'))->disable();

        $form->datetime('pay_time', __('Pay time'))->default(date('Y-m-d H:i:s'));
        $form->datetime('send_time', __('Send time'))->default(date('Y-m-d H:i:s'));
        $form->datetime('finish_time', __('Finish time'))->default(date('Y-m-d H:i:s'));
        $form->textarea('address', __('Address'));
        $form->text('name', __('收货人姓名'));
        $form->text('tel', __('收货人电话'));



        //保存前回调
        $form->saving(function (Form $form) {
            $status = $form->model()->status;
            if ($status == 3) {
                $form->send_time = date('Y-m-d H:i:s', time());
            }
        });
        return $form;
    }

    public function comment_info(Content $content, $order_id, $product_id)
    {
        $comment = Comment::with('order.order_products', 'customer', 'product')->where('order_id', $order_id)->where('product_id', $product_id)->first();
        return $content
            ->title('订单号:')
            ->description($comment->order->order_sn)
            ->row(function (Row $row) use ($comment) {
                $row->column(12, function (Column $column) use ($comment) {
                    $column->append(new Box('评论详情...', view('admin.shop.comment_info', compact('comment'))));
                });
            });
    }

    public function reply(Request $request)
    {
        $reply = $request->input('reply');
        Comment::where('id',$request->id)->update([
            'reply'=>$reply
        ]);

        $success = new MessageBag([
            'title'   => 'Success',
            'message' => '回复成功！',
        ]);

        return back()->with(compact('success'));

    }

    public function comment_del(Request $request)
    {
        Comment::destroy($request->id);

        return ['code'=>200,'msg'=>'删除成功~'];
    }
}
