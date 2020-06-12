<?php

namespace App\Admin\Controllers\Shop;

use App\Models\Shop\Address;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class AddressController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '地址';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Address());

        $grid->column('id', __('Id'));
        $grid->column('customer_id', __('Customer id'));
        $grid->column('province', __('Province'));
        $grid->column('city', __('City'));
        $grid->column('area', __('Area'));
        $grid->column('detail', __('Detail'));
        $grid->column('tel', __('Tel'));
        $grid->column('name', __('Name'));
        $grid->column('is_default', __('Is default'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));

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
        $show = new Show(Address::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('customer_id', __('Customer id'));
        $show->field('province', __('Province'));
        $show->field('city', __('City'));
        $show->field('area', __('Area'));
        $show->field('detail', __('Detail'));
        $show->field('tel', __('Tel'));
        $show->field('name', __('Name'));
        $show->field('is_default', __('Is default'));
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
        $form = new Form(new Address());

        $form->text('customer_id', __('Customer id'));
        $form->textarea('province', __('Province'));
        $form->textarea('city', __('City'));
        $form->textarea('area', __('Area'));
        $form->textarea('detail', __('Detail'));
        $form->text('tel', __('Tel'));
        $form->text('name', __('Name'));
        $form->switch('is_default', __('Is default'));

        return $form;
    }
}
