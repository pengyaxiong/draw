<?php

namespace App\Admin\Controllers\Shop;

use App\Models\Customer;
use App\Models\Shop\Withdraw;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\MessageBag;

class WithdrawController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '提现管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Withdraw());

        $grid->column('id', __('Id'));
        $grid->column('customer.nickname', __('会员昵称'));
        $states = [
            'on' => ['value' => 1, 'text' => '已完成', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => '已申请', 'color' => 'default'],
        ];
        $grid->column('alipay', __('支付宝账号'));
        $grid->column('name', __('联系姓名'));
        $grid->column('money', __('提现金额'));
        $grid->column('status', __('Status'))->switch($states);
        $grid->column('finish_time', __('打款时间'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'))->hide();

        $grid->filter(function ($filter) {
            $filter->like('name', __('联系姓名'));
            $filter->like('alipay', __('支付宝账号'));

            $customers = Customer::all()->toArray();
            $select_array = array_column($customers, 'nickname', 'id');

            $filter->equal('customer_id', __('会员昵称'))->select($select_array);
            $status_text = [
                0 => '已申请',
                1 => '已完成',
            ];
            $filter->equal('status', __('Status'))->select($status_text);

            $filter->between('created_at', __('Created at'))->date();

        });

        //禁用创建按钮
        $grid->disableCreateButton();

        $grid->actions(function ($actions) {
            $actions->disableView();
            //  $actions->disableEdit();
            $actions->disableDelete();
        });

        $grid->tools(function ($tools) {
            // 禁用批量删除按钮
            $tools->batch(function ($batch) {
                $batch->disableDelete();
            });
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
        $show = new Show(Withdraw::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('customer.nickname', __('会员昵称'));
        $show->field('status', __('Status'));
        $show->field('name', __('联系姓名'));
        $show->field('money', __('提现金额'));
        $show->field('finish_time', __('打款时间'));
        $show->field('alipay', __('支付宝账号'));
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
        $form = new Form(new Withdraw());

        $customers = Customer::all()->toArray();
        $select_array = array_column($customers, 'nickname', 'id');
        $form->select('customer_id', __('Customer id'))->options($select_array)->rules('required');

        $states = [
            'on' => ['value' => 1, 'text' => '已完成', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => '已申请', 'color' => 'default'],
        ];
        $form->switch('status', __('Status'))->states($states)->default(0);
        $form->text('name', __('联系姓名'));
        $form->decimal('money', __('提现金额'));
        $form->datetime('finish_time', __('打款时间'))->default(date('Y-m-d H:i:s'));
        $form->text('alipay', __('支付宝账号'));

        //保存前回调
        $form->saving(function (Form $form) {

            if ($form->model()->status==1){
                return response()->json([
                    'status'  => false,
                    'message' => '提现已完成，请勿重复提交。。',
                ]);
            }
            $form->finish_time=date('Y-m-d H:i:s');
        });

        return $form;
    }
}
