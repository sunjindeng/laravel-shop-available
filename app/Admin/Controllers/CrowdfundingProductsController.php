<?php

namespace App\Admin\Controllers;

use App\Models\CrowdfundingProduct;
use App\Models\Product;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Traits\DefaultDatetimeFormat;

class CrowdfundingProductsController extends CommonProductsController
{
    use DefaultDatetimeFormat;

    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '众筹商品';

    public function getProductType()
    {
        return Product::TYPE_CROWDFUNDING;
    }

    protected function customGrid(Grid $grid)
    {
        $grid->column('id', __('Id'))->sortable();
        $grid->column('title', __('商品名称'));
        $grid->column('on_sale', __('是否上架'))->display(function ($value) {
            return $value ? '是' : '否';
        });
        $grid->column('price', __('Price'));
        $grid->column('crowdfunding.target_amount', '目标金额');
        $grid->column('crowdfunding.end_at', '结束时间');
        $grid->column('crowdfunding.total_amount', '目前金额');
        $grid->column('crowdfunding.status', '状态')->display(function ($value) {
            return CrowdfundingProduct::$statusMap[$value];
        });
    }

    protected function customForm(Form $form)
    {
        $form->text('crowdfunding.target_amount', '众筹目标金额')->rules('required|numeric|min:0.01');
        $form->datetime('crowdfunding.end_at', '众筹结束时间')->rules('required|date');
    }
}
