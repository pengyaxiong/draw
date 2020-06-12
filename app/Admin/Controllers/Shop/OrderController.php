<?php

namespace App\Admin\Controllers\Shop;

use App\Models\Shop\Order;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class OrderController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '订单管理';

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
        $grid->column('product.name', __('商品名称'));
        $grid->column('status', __('Status'))->replace([
            1 => '待支付',
            2 => '待发货',
            3 => '待收货',
            4 => '已完成',
        ])->label([
            1 => 'default',
            2 => 'warning',
            3 => 'primary',
            4 => 'success',
        ]);
        $grid->column('pay_type', __('Pay type'))->using([
            1 => '微信支付',
        ], '未知')->dot([
            1 => 'primary',
        ], 'warning');

        $grid->column('total_price', __('Total price'));
        $grid->column('pay_time', __('Pay time'));
        $grid->column('send_time', __('Send time'));
        $grid->column('finish_time', __('Finish time'));
        $grid->column('address', __('Address'));
        $grid->column('name', __('Name'));
        $grid->column('tel', __('Tel'));
        $grid->column('created_at', __('Created at'))->hide();
        $grid->column('updated_at', __('Updated at'))->hide();

        $grid->filter(function ($filter) {
            $filter->equal('order_sn', __('Order sn'));
            $status_text = [
                1 => '待支付',
                2 => '待发货',
                3 => '待收货',
                4 => '已完成',
            ];
            $filter->equal('status', __('Status'))->select($status_text);
            $filter->between('created_at', __('Created at'))->date();
        });

        //禁用创建按钮
        $grid->disableCreateButton();
        $grid->actions(function ($actions) {
            $actions->disableView();
            // $actions->disableEdit();
            $actions->disableDelete();
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


        $form->select('status', __('Status'))->options([
            1 => '待支付',
            2 => '待发货',
            3 => '待收货',
            4 => '已完成',
            ]);
        $states = [
            'on' => ['value' => 1, 'text' => '微信支付', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => '其他', 'color' => 'danger'],
        ];
        $form->switch('pay_type', __('Pay type'))->states($states)->disable();

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
            if ($status==3) {
                $form->send_time = date('Y-m-d H:i:s', time());
            }
        });
        return $form;
    }
}
