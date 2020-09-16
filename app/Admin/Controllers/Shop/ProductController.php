<?php

namespace App\Admin\Controllers\Shop;

use App\Models\Shop\Category;
use App\Models\Shop\Product;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ProductController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '商品管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Product());

        $grid->model()->orderBy('sort_order');

        $grid->column('id', __('Id'));
        $grid->column('category.name', __('所属分类'));
        $grid->column('name', __('Name'));
        $grid->column('sale_num', __('销量'))->hide();
        $grid->column('exchange_num', __('兑换数量'))->hide();
        $grid->column('stock', __('库存'))->editable();
        $grid->column('discuss', __('好评'))->hide();
        $grid->column('image', __('Image'))->image();
        $grid->column('images', __('Images'))->hide();
        $grid->column('content', __('Content'))->hide();
        $grid->column('description', __('Description'))->hide();
//        $grid->column('sku', __('Sku'))->table();
        $grid->column('coin', __('兑换积分'))->editable();
        $grid->column('price', __('Price'))->editable();
        $grid->column('old_price', __('原价'))->editable();

        $states = [
            'on'  => ['value' => 1, 'text' => '是', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => '否', 'color' => 'danger'],
        ];
        $grid->column('is_prize', __('是否是奖品'))->switch($states);
        $grid->column('is_sale', __('是否上下架'))->switch($states);
        $grid->column('is_exchange', __('是否开启兑换'))->switch($states);

        $grid->column('rate_d', __('默认中奖率'))->sortable()->editable()->help('为正整数，数值越大中奖概率越高');
        $grid->column('rate_o', __('10积分中奖率'))->sortable()->editable()->help('为正整数，数值越大中奖概率越高');
        $grid->column('rate_f', __('50积分中奖率'))->sortable()->editable()->help('为正整数，数值越大中奖概率越高');
        $grid->column('sort_order', __('Sort order'))->sortable()->editable()->help('按数字大小正序排序');
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));

        $grid->filter(function ($filter) {
            $filter->like('name', __('Name'));

            $categories = Category::where('parent_id','>',0)->get()->toArray();
            $select_array = array_column($categories, 'name', 'id');

            $filter->equal('category_id', __('所属分类'))->select($select_array);

            $status_text = [
                1 => '是',
                0 => '不是'
            ];
            $filter->equal('is_prize', __('是否是奖品'))->select($status_text);
            $filter->equal('is_sale', __('是否上下架'))->select($status_text);
            $filter->equal('is_exchange', __('是否开启兑换'))->select($status_text);
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
        $show = new Show(Product::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('category_id', __('Category id'));
        $show->field('name', __('Name'));
        $show->field('sale_num', __('Sale num'));
        $show->field('exchange_num', __('兑换数量'));
        $show->field('stock', __('库存'));
        $show->field('discuss', __('Discuss'));
        $show->field('image', __('Image'));
        $show->field('images', __('Images'));
        $show->field('content', __('Content'));
        $show->field('description', __('Description'));
//        $show->field('sku', __('Sku'));
        $show->field('coin', __('兑换积分'));
        $show->field('price', __('Price'));
        $show->field('old_price', __('Old price'));
        $show->field('is_prize', __('Is prize'));
        $show->field('is_sale', __('是否上下架'));
        $show->field('is_exchange', __('是否开启兑换'));
        $show->field('rate_d', __('默认中奖率'));
        $show->field('rate_o', __('10积分中奖率'));
        $show->field('rate_f', __('50积分中奖率'));
        $show->field('sort_order', __('Sort order'));
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
        $form = new Form(new Product());

        $form->text('name', __('Name'))->rules('required');

        $categories = Category::where('parent_id','>',0)->get()->toArray();
        $select_array = array_column($categories, 'name', 'id');
        //创建select
        $form->select('category_id', __('所属分类'))->options($select_array);

        $form->number('sale_num', __('销量'))->rules('required');
        $form->number('exchange_num', __('兑换数量'))->rules('required');
        $form->number('stock', __('库存'))->rules('required');
        $form->text('discuss', __('好评'))->rules('required');

        $form->image('image', __('Image'))->rules('required|image');

        $form->multipleImage('images', __('轮播图'))->sortable()->removable()->rules('image');

        $form->ueditor('content', __('Content'))->rules('required');
        $form->textarea('description', __('Description'))->rules('required');

//        $form->table('sku', __('Sku'), function ($table) {
//
//            $table->text('category', '类别');
//
//            $table->text('type', '规格')->help('不同规格用英文,隔开');
//        });

        
        $form->decimal('coin', __('兑换积分'))->default(99.00);
        $form->decimal('price', __('Price'))->default(99.00);
        $form->decimal('old_price', __('原价'))->default(99.00);

        $states = [
            'on' => ['value' => 1, 'text' => '是', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => '否', 'color' => 'danger'],
        ];
        $form->switch('is_prize', __('是否是奖品'))->states($states)->default(1);
        $form->switch('is_sale', __('是否上下架'))->states($states)->default(1);
        $form->switch('is_exchange', __('是否开启兑换'))->states($states)->default(0);
        $form->number('rate_d', __('默认中奖率'))->help('为正整数，数值越大中奖概率越高')->rules('required');
        $form->number('rate_o', __('10积分中奖率'))->help('为正整数，数值越大中奖概率越高')->rules('required');
        $form->number('rate_f', __('50积分中奖率'))->help('为正整数，数值越大中奖概率越高')->rules('required');
        $form->number('sort_order', __('Sort order'))->default(99);

        return $form;
    }
}
