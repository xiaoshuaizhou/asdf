<?php

namespace App\Repositories;

use App\Http\Resources\ProductProperty;
use App\Http\Resources\ProductResource;
use App\Models\Banner;
use App\Http\Resources\BannerResource;
use App\Models\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Predis\PredisException;

/**
 * Class ProductsRepository
 * @package App\Repositories
 */
class ProductsRepository
{
    /**
     * @var Product
     */
    public $productModel;

    /**
     * ProductsRepository constructor.
     * @param $productModel
     */
    public function __construct(Product $productModel)
    {
        $this->productModel = $productModel;
    }

    /**
     * 最新商品列表
     * @param $count
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function getRecent($count)
    {
        $res = ProductResource::collection($this->productModel->limit($count)->orderBy('created_at',' desc')->get());

        if ($res->isEmpty()){
            throw new ModelNotFoundException('商品不存在');
        }

        return $res;
    }

    /**
     * 根据category_id 查找商品
     * @param $id
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function getAllProductByCategoryId($id)
    {
        $res = ProductResource::collection($this->productModel->where('category_id', $id)->limit(15)->get());

        if ($res->isEmpty()){
            throw new ModelNotFoundException('分类不存在或者该分类下无商品');
        }

        return $res;
    }

    /**
     * 获取商品详情
     * @param $id
     * @return ProductResource
     */
    public function getProductDetail($id)
    {
        try{
//            $res = new ProductProperty($this->productModel::with(['productImages.image', 'productProperty'])->find($id));
            $res = new ProductProperty(
                $this->productModel->with([
                    'productImages' => function ($query) {
                        return $query->with(['image'])->orderBy('order', 'asc');
                    }])
                    ->with(['productProperty'])
                    ->find($id)
            );
        }catch (\Exception $exception){
            throw new ModelNotFoundException('商品不存在');
        }

        return $res;
    }
}
