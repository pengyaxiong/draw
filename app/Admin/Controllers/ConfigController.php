<?php

namespace App\Admin\Controllers;

use App\Models\Config;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ConfigController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '系统配置';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Config());

        $grid->column('id', __('Id'));
        $grid->column('rule', __('活动规则'))->display(function ($model){
            return implode('<br>',array_pluck($model,'description'));
        });
        $grid->column('coin', __('签到积分'));
        $grid->column('get_coin', __('分享积分'))->help("用户只要点保存或分享就有积分，一天一次");
        $grid->column('share_coin', __('邀请积分'))->help("新用户通过邀请者分享的小程序或二维码授权登录的，邀请者获得积分，且无上限");
        $grid->column('draw_coin', __('抽奖积分'));
        $grid->column('commission_rate', __('佣金比列'));
        $grid->column('goods_rate', __('商品反积分比列'));
        $grid->column('coin_search', __('积分筛选区间'))->display(function ($model){
            return implode('<br>',array_pluck($model,'description'));
        });
        $grid->column('withdraw_info', __('提现说明'))->width(200);

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
        $show = new Show(Config::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('rule', __('活动规则'));
        $show->field('coin', __('签到积分'));
        $show->field('get_coin', __('分享积分'));
        $show->field('share_coin', __('邀请积分'));
        $show->field('draw_coin', __('抽奖积分'));
        $show->field('commission_rate', __('佣金比列'));
        $show->field('goods_rate', __('商品反积分比列'));
        $show->field('coin_search', __('积分筛选区间'));
        $show->field('withdraw_info', __('提现说明'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Config());

        $form->table('rule', __('活动规则'), function ($table) {
            $table->textarea('description', __('Description'));
        });

        $form->number('coin', __('签到积分'));
        $form->number('get_coin', __('分享积分'));
        $form->number('share_coin', __('邀请积分'));
        $form->number('draw_coin', __('抽奖积分'));

        $form->text('commission_rate', __('佣金比列'));
        $form->text('goods_rate', __('商品反积分比列'));

        $form->table('coin_search', __('积分筛选区间'), function ($table) {
            $table->text('description', __('Description'))->help('以-连接');
        });
        $form->ueditor('withdraw_info', __('提现说明'));

        return $form;
    }
}
