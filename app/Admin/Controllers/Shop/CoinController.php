<?php

namespace App\Admin\Controllers\Shop;

use App\Models\Customer;
use App\Models\Shop\Coin;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class CoinController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '财务管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Coin());

        $grid->column('id', __('Id'));
        $grid->column('log_name', __('Log name'));
        $grid->column('description', __('Description'));
        $grid->column('subject_id', __('Subject id'));
        $grid->column('subject_type', __('Subject type'));
        $grid->column('causer_id', __('Causer id'));
        $grid->column('causer_type', __('Causer type'));
        $grid->column('properties', __('Properties'))->display(function ($properties) {
            $type=isset($properties['type'])?$properties['type']:'';
            $num=isset($properties['num'])?$properties['num']:'';
            return $type . ' ' . $num;
        });
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));

        $grid->filter(function ($filter) {
            $filter->equal('log_name', __('Log name'));

            $customers = Customer::all()->toArray();
            $select_array = array_column($customers, 'nickname', 'id');

            $filter->equal('causer_id', __('微信昵称'))->select($select_array);

            $filter->between('created_at', __('Created at'))->date();
        });

        //禁用创建按钮
        $grid->disableCreateButton();
        $grid->actions(function ($actions) {
            $actions->disableView();
             $actions->disableEdit();
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
        $show = new Show(Coin::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('log_name', __('Log name'));
        $show->field('description', __('Description'));
        $show->field('subject_id', __('Subject id'));
        $show->field('subject_type', __('Subject type'));
        $show->field('causer_id', __('Causer id'));
        $show->field('causer_type', __('Causer type'));
        $show->field('properties', __('Properties'));
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
        $form = new Form(new Coin());

        $form->text('log_name', __('Log name'));
        $form->text('description', __('Description'));
        $form->number('subject_id', __('Subject id'));
        $form->text('subject_type', __('Subject type'));
        $form->number('causer_id', __('Causer id'));
        $form->text('causer_type', __('Causer type'));
        $form->textarea('properties', __('Properties'));

        return $form;
    }
}
