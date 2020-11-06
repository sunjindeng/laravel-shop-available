<?php

namespace App\Services;

use App\Models\Category;

class CategoryService
{
    /**
     * 递归
     * @param null $parentId 获取子类目的父目录ID，null代表是获取所有根目录
     * @param null $allCategories 获取所有的目录，null代表从数据库中查询
     * @return Category[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
     */
    public function getCategoryTree($parentId = null, $allCategories = null)
    {
        if (is_null($allCategories)) {
            //查询所有的类目
            $allCategories = Category::all();
        }
        return $allCategories
            //从所有的类目中查询出父类目为parent_id的类目
            ->where('parent_id', $parentId)
            //遍历目录，并用返回值构建一个新的集合
            ->map(function (Category $category) use ($allCategories) {
                $data = ['id' => $category->id, 'name' => $category->name];
                //如果当前目录为父目录，则直接返回
                if (!$category->is_directory) {
                    return $data;
                }
                //否则递归屌用本方法，将返回值放入children字段中
                $data['children'] = $this->getCategoryTree($category->id, $allCategories);
                return $data;
            });
    }
}
