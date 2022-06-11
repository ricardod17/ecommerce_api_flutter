<?php

namespace App\Services;

use App\Http\Resources\ProductCategoryCollection;
use App\Http\Resources\ProductCategoryResource;
use App\Models\ProductCategory;
use App\Models\User;
use App\Repositories\ProductCategoryRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ProductCategoryService
{
    private $productCategoryRepository;

    public function __construct(ProductCategoryRepository $productCategoryRepository)
    {
        $this->productCategoryRepository = $productCategoryRepository;
    }

    public function getAll($request): ProductCategoryCollection
    {
        $product_categories = $this->productCategoryRepository->getAll($request);

        return $product_categories;
    }
    public function getById($id): ProductCategoryResource
    {
        $product_categories = $this->productCategoryRepository->getById($id);

        return $product_categories;
    }

    public function create($data): \App\Http\Resources\ProductCategoryResource
    {
        $validator = $this->validateProduct($data);

        DB::beginTransaction();
        try {
            $product_categories = $this->productCategoryRepository->create($data);
        } catch (\Exception $e) {
            DB::rollback();
            throw new \InvalidArgumentException($e->getMessage());
        }

        DB::commit();

        return $product_categories;
    }

    public function update($id, $data): \App\Http\Resources\ProductCategoryResource
    {
        $validator = Validator::make($data, [
            'name' => 'string|max:255|required',
        ]);

        if ($validator->fails()) {
            throw new \InvalidArgumentException($validator->errors()->first());
        }

        $product_categories = ProductCategory::findOrFail($id);

        if (!$product_categories) {
            throw new ModelNotFoundException('product not found', 404);
        }

        DB::beginTransaction();
        try {
            $product_categories = $this->productCategoryRepository->update($product_categories, $data);
        } catch (\Exception $e) {
            DB::rollback();
            throw new \InvalidArgumentException($e->getMessage());
        }

        DB::commit();

        return $product_categories;
    }

    public function delete($id)
    {
        $product_categories = ProductCategory::findOrFail($id);

        if (!$product_categories) {
            throw new ModelNotFoundException('product not found', 404);
        }

        DB::beginTransaction();
        try {
            $product_categories = $this->productCategoryRepository->delete($product_categories);
        } catch (\Exception $e) {
            DB::rollback();
            throw new \InvalidArgumentException($e->getMessage());
        }

        DB::commit();

        return $product_categories;
    }

    public function validateproduct($data)
    {
        $validator = Validator::make($data, [
            'name' => 'string|max:255|required',
        ]);

        if ($validator->fails()) {
            throw new \InvalidArgumentException($validator->errors()->first());
        }

        return $validator;
    }
}