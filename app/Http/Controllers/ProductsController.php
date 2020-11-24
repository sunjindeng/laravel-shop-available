<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidRequestException;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductsController extends Controller
{
    public function index(Request $request)
    {
        //--------es调取数据-----------
        $page = $request->input('page', 1);
        $perPage = 16;
        //构建查询
        $params = [
            'index' => 'products',
            'body' => [
                'from' => ($page - 1) * $perPage,
                'size' => $perPage,
                'query' => [
                    'bool' => [
                        'filter' => [
                            ['term' => ['on_sale' => true]],
                        ]
                    ]
                ],
            ]
        ];
        //是否提交order参数，如果有就赋值给$order变量
        //order 参数用来控制商品的排序规则
        if ($order = $request->input('order', '')) {
            //是不是以_asc或者_desc 结尾
            if(preg_match('/^(.+)_(asc|desc)$/', $order, $m)) {
                if(in_array($m[1],['price', 'rating', 'sold_count'])) {
                    //根据字符串的开头是这三个字符串之一，说明是一个合法的排序值
                    $params['body']['sort'] = [[$m[1] => $m[2]]];
                }
            }
        }
        if ($request->input('category_id') && $category = Category::find($request->input('category_id'))) {
            if($category->is_directory) {
                $params['body']['query']['bool']['filter'][] = [
                    'prefix' => ['category_path' =>$category->path.$category->id.'-'],
                ];
            } else {
                //直接用category_id筛选
                $params['body']['query']['bool']['filter'][] = ['term' => ['category_id' => $category->id]];
            }
        }
        if ($search = $request->input('search', '')) {
            //将搜索词根据空格拆分成数组，并过滤掉空项
            $keywords = array_filter(explode(' ', $search));
            $params['body']['query']['bool']['must'] = [];
            foreach ($keywords as $keyword) {
                $params['body']['query']['bool']['must'][] = [
                    'multi_match' => [
                        'query' => $keyword,
                        'fields' => [
                            'title^2',
                            'long_title^2',
                            'category^2',//类目名称
                            'description',
                            'skus_title',
                            'skus_description',
                            'properties_value',
                        ],
                    ],
                ];
            }

        }
        //只有当用户输入搜索词或者使用了类目帅选的时候才会做聚合
        if ($search || isset($category)) {
            $params['body']['aggs'] = [
                'properties' => [
                    'nested' => [
                        'path' => 'properties',
                    ],
                    'aggs' => [
                        'properties' => [
                            'terms' => [
                                'field' => 'properties.name',
                            ],
                            'aggs' => [
                                'value' => [
                                    'terms' => [
                                        'field' => 'properties.value',
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ];
        }
        //从用户请求参数获取filters
        $propertyFilters = [];
        if ($filterString = $request->input('filters')) {
            //将获取的字符串用符号 ｜ 拆分成数组
            $filterArray = explode('|', $filterString);
            foreach ($filterArray as $filter) {
                //将字符串用符号 ： 拆分成两部分并且分别赋值给$name 和  $value 两个变量
                list($name, $value) = explode(':', $filter);
                $propertyFilters[$name] = $value;
            }
            //添加到filter类型中
            $params['body']['query']['bool']['filter'][] = [
                //由于我们要筛选的是nested类型下的属性，因此需要用nested查询
                'nested' => [
                    //指明nested字段
                    'path' => 'properties',
                    'query' => [
                        ['term' => ['properties.search_value' => $name]],
                    ]
                ]
            ];
        }
        $result = app('es')->search($params);
        //通过cpllect函数将返回结果转为集合，并通过集合的pluck方法取代返回的商品ID数组
        $productIds = collect($result['hits']['hits'])->pluck('_id')->all();
        $properites = [];
        //如果返回结果里有aggregations字段，说明做了分面搜索
        if (isset($result['aggregations'])) {
            //使用collect函数将返回值转为集合
            $properties = collect($result['aggregations']['properties']['properties']['buckets'])
                ->map(function ($bucket) {
                    //通过map方法取出我们需要的字段
                    return [
                        'key' => $bucket['key'],
                        'value' => collect($bucket['value']['buckets'])->pluck('key')->all(),
                    ];
                }   )
                ->filter(function ($property) use ($propertyFilters) {
                    //过滤掉只剩下一个值，或者 已经在筛选条件里的属性
                    return count($property['value']) > 1 && !isset($propertyFilters[$property['key']]);
                });

        }

        //通过wherein方法从数据库中读取商品
        $products = Product::query()
            ->whereIn('id', $productIds)
            //orderByRaw可以让我们用原声的sql来给查询结果排序
            ->orderByRaw(sprintf("FIND_IN_SET(id,'%s')", join(',', $productIds)))
            ->get();
        //返回一个LengthAwarePaginator对象
        $pager = new LengthAwarePaginator($products, $result['hits']['total']['value'], $perPage, $page, [
            'path' => route('products.index', false)
        ]);
        return view('products.index', [
            'products' =>$pager,
            'filters' => [
                'search' => $search,
                'order' => $order
            ],
            'category' => $category ?? null,
            'properties' => $properties ?? null,
            'propertyFilters' => $propertyFilters,
        ]);
        /**
         *
         * //-----------数据库调用数据-----------
        // 创建一个查询构造器
        $builder = Product::query()->where('on_sale', true);
        // 判断是否有提交 search 参数，如果有就赋值给 $search 变量
        // search 参数用来模糊搜索商品
        if ($search = $request->input('search', '')) {
        $like = '%'.$search.'%';
        // 模糊搜索商品标题、商品详情、SKU 标题、SKU描述
        $builder->where(function ($query) use ($like) {
        $query->where('title', 'like', $like)
        ->orWhere('description', 'like', $like)
        ->orWhereHas('skus', function ($query) use ($like) {
        $query->where('title', 'like', $like)
        ->orWhere('description', 'like', $like);
        });
        });
        }

        if ($request->input('category_id') && $category = Category::find($request->input('category_id'))) {
        //如果这是一个父类目
        if ($category->is_directory) {
        $builder->whereHas('category', function ($query) use ($category) {
        $query->where('path', 'like', $category->path.$category->id.'-');
        });
        } else {
        //如果这不是一个父类目，则直接筛选该类目下面的商品
        $builder->where('category_id', $category->id);
        }
        }

        // 是否有提交 order 参数，如果有就赋值给 $order 变量
        // order 参数用来控制商品的排序规则
        if ($order = $request->input('order', '')) {
        // 是否是以 _asc 或者 _desc 结尾
        if (preg_match('/^(.+)_(asc|desc)$/', $order, $m)) {
        // 如果字符串的开头是这 3 个字符串之一，说明是一个合法的排序值
        if (in_array($m[1], ['price', 'sold_count', 'rating'])) {
        // 根据传入的排序值来构造排序参数
        $builder->orderBy($m[1], $m[2]);
        }
        }
        }
        $products = $builder->paginate(16);
        return view('products.index', [
        'products' => $products,
        'filters'  => [
        'search' => $search,
        'order'  => $order,
        ],
        'category' =>$category ?? null,
        ]);
         */

    }

    public function show(Product $product, Request $request)
    {
        if (!$product->on_sale) {
            throw new InvalidRequestException('商品未上架');
        }

        $favored = false;
        // 用户未登录时返回的是 null，已登录时返回的是对应的用户对象
        if($user = $request->user()) {
            // 从当前用户已收藏的商品中搜索 id 为当前商品 id 的商品
            // boolval() 函数用于把值转为布尔值
            $favored = boolval($user->favoriteProducts()->find($product->id));
        }

        $reviews = OrderItem::query()
            ->with(['order.user', 'productSku']) // 预先加载关联关系
            ->where('product_id', $product->id)
            ->whereNotNull('reviewed_at') // 筛选出已评价的
            ->orderBy('reviewed_at', 'desc') // 按评价时间倒序
            ->limit(10) // 取出 10 条
            ->get();

        // 最后别忘了注入到模板中
        return view('products.show', [
            'product' => $product,
            'favored' => $favored,
            'reviews' => $reviews
        ]);
    }

    public function favorites(Request $request)
    {
        $products = $request->user()->favoriteProducts()->paginate(16);

        return view('products.favorites', ['products' => $products]);
    }

    public function favor(Product $product, Request $request)
    {
        $user = $request->user();
        if ($user->favoriteProducts()->find($product->id)) {
            return [];
        }

        $user->favoriteProducts()->attach($product);

        return [];
    }

    public function disfavor(Product $product, Request $request)
    {
        $user = $request->user();
        $user->favoriteProducts()->detach($product);

        return [];
    }
}
