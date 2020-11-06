<?php
namespace App\Http\ViewComposers;

use App\Services\CategoryService;
use Illuminate\View\View;

class CategoryTreeComposer
{
    protected $categoryService;

    //利用laravel的依赖注入，自动加入我们所需的CategoryService类
    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    //当渲染指定的模版是，Laravel会屌用compose方法
    public function compose(View $view)
    {
        //使用with方法植入变量
        $view->with('categoryTree', $this->categoryService->getCategoryTree());
    }
}
