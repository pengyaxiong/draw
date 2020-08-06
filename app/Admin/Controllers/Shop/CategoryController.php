<?php

namespace App\Admin\Controllers\Shop;

use App\Handlers\PinyinHandler;
use App\Models\Shop\Category;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Table;

class CategoryController extends AdminController
{

    private $Pinyin;

    /**
     * CategoryController constructor.
     * @param PinyinHandler $Pinyin
     * $pinyin->getFirstChar("湖北武汉")       H;
      $pinyin->getPinyin("北上广")             bei shang guang;
     */
    public function __construct(PinyinHandler $Pinyin)
    {
        $this->Pinyin = $Pinyin;
    }

    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '分类管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Category());

        $grid->model()->where('parent_id',0)->orderBy('sort_order');

        $grid->column('id', __('Id'));
        $grid->column('name', __('Name'));
        $grid->column('image', __('Image'))->image();
        $grid->column('parent_id', '下级')->display(function () {
            return '下级';
        })->modal('下级', function ($model) {

            $children = $model->children->map(function ($child) {
                return $child->only(['id', 'name']);
            });

            $array = $children->toArray();
            foreach ($array as $k => $v) {
                $url = route('admin.shop.categories.edit', $v['id']);
                $array[$k]['edit'] = '<div class="btn">
              <a class="btn btn-sm btn-default pull-right" target="_blank" href="' . $url . '" rel="external" >
              <i class="fa fa-edit"></i> 编辑</a>
                 </div>';
            }

            return new Table(['ID', __('名称'), '操作'], $array);
        });
        $states = [
            'on' => ['value' => 1, 'text' => '是', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => '否', 'color' => 'danger'],
        ];
        $grid->column('is_show', '是否显示')->switch($states);

        $grid->column('sort_order', __('Sort order'))->sortable()->editable()->help('按数字大小正序排序');

        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));


        $grid->filter(function ($filter) {
            $filter->like('name', __('Name'));
            $status_text = [
                1 => '显示',
                0 => '未显示'
            ];
            $filter->equal('is_show', __('是否显示'))->select($status_text);
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
        $show = new Show(Category::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name', __('Name'));
        $show->field('image', __('Image'));
        $show->field('parent_id', __('Parent id'));
        $show->field('is_show', __('Is show'));
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
        $form = new Form(new Category());

        $form->text('name', __('Name'))->rules('required');

        $parents = Category::where('parent_id', false)->get()->toArray();
        $select_ = array_prepend($parents, ['id' => 0, 'name' => '顶级']);
        $select_array = array_column($select_, 'name', 'id');

//        echo json_encode($select_array);exit();
        //创建select
        $form->select('parent_id', '上级')->options($select_array);

        $form->image('image', __('Image'))->rules('required|image');

        $states = [
            'on' => ['value' => 1, 'text' => '是', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => '否', 'color' => 'danger'],
        ];
        $form->switch('is_show', __('Is show'))->states($states)->default(1);

        $form->number('sort_order', __('Sort order'))->default(99);


        //保存后回调
        $form->saved(function (Form $form) {
            $name = $form->model()->name;
            $model = $form->model();
            $model->pinyin=$this->Pinyin->getFirstChar($name);
            $model->save();
        });

        return $form;
    }
}
