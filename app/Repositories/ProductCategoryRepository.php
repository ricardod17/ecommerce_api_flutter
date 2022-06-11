<?php

namespace App\Repositories;

use App\Http\Resources\ProductCategoryCollection;
use App\Http\Resources\ProductCategoryResource;
use App\Models\ProductCategory;
use Illuminate\Support\Facades\Auth;

class ProductCategoryRepository
{
    private $productCategory;

    public function __construct(ProductCategory $productCategory)
    {
        $this->productCategory = $productCategory;
    }

    public function getAll($request): ProductCategoryCollection
    {
        $query = $this->productCategory->query();
        $page = $query->perPage($request->perPage ?? 10);

        $productCategorys = $query->latest()->paginate($page);

        return new ProductCategoryCollection($productCategorys);
    }

    public function getById($id): ProductCategoryResource
    {
        $productCategory = $this->productCategory->findOrFail($id);

        return new ProductCategoryResource($productCategory);
    }

    public function create($data): ProductCategoryResource
    {
        $data['users_id'] =  Auth::user()->id;
        $productCategory = $this->productCategory->create($data);

        return new ProductCategoryResource($productCategory);
    }

    public function update($productCategory, $data): ProductCategoryResource
    {
        $productCategory->update($data);

        return new ProductCategoryResource($productCategory);
    }

    public function delete($productCategory)
    {
        $productCategory->delete();

        return new ProductCategoryResource($productCategory);
    }
}