<?php

namespace App\Admin\Controllers;

use App\Models\Problem;
use App\Models\ProblemCategory;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ProblemController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '帮助中心';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Problem());
        $grid->model()->orderby('sort_order');

        $grid->column('id', __('Id'));
        $grid->column('category.name', __('Category id'));
        $grid->column('title', __('Title'));
        $grid->column('content', __('Content'))->hide();
        $grid->column('sort_order', __('Sort order'))->sortable()->editable()->help('按数字大小正序排序');
        $grid->column('created_at', __('Created at'))->hide();
        $grid->column('updated_at', __('Updated at'))->hide();

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
        $show = new Show(Problem::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('category_id', __('Category id'));
        $show->field('title', __('Title'));
        $show->field('content', __('Content'));
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
        $form = new Form(new Problem());

        $categories = ProblemCategory::orderby('sort_order')->get()->toArray();
        $select_array = array_column($categories, 'name', 'id');
        //创建select
        $form->select('category_id', __('所属栏目'))->options($select_array);

        $form->text('title', __('Title'))->rules('required');
        $form->textarea('content', __('内容'))->rules('required');
        $form->number('sort_order', __('Sort order'))->default(99);

        return $form;
    }
}
