<?php

namespace App\Services;

use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\User;
use App\Repositories\ProductRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ProductService
{
    private $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function getAll($request): ProductCollection
    {
        $product = $this->productRepository->getAll($request);

        return $product;
    }

    public function getById($id): ProductResource
    {
        $product = $this->productRepository->getById($id);

        return $product;
    }

    public function create($data): \App\Http\Resources\ProductResource
    {
        $validator = $this->validateProduct($data);

        DB::beginTransaction();
        try {
            $product = $this->productRepository->create($data);
        } catch (\Exception $e) {
            DB::rollback();
            throw new \InvalidArgumentException($e->getMessage());
        }

        DB::commit();

        return $product;
    }

    public function update($id, $data): \App\Http\Resources\ProductResource
    {
        $validator = Validator::make($data, [
            'name' => 'string|max:255|required',
            'description' => 'string|max:255|required',
            'price' => 'numeric|required',
            'categories_id' => 'numeric|required',
            'tags' => 'string|max:255|required',
        ]);

        if ($validator->fails()) {
            throw new \InvalidArgumentException($validator->errors()->first());
        }

        $product = product::findOrFail($id);

        if (!$product) {
            throw new ModelNotFoundException('product not found', 404);
        }

        DB::beginTransaction();
        try {
            $product = $this->productRepository->update($product, $data);
        } catch (\Exception $e) {
            DB::rollback();
            throw new \InvalidArgumentException($e->getMessage());
        }

        DB::commit();

        return $product;
    }

    public function delete($id)
    {
        $product = product::findOrFail($id);

        if (!$product) {
            throw new ModelNotFoundException('product not found', 404);
        }

        DB::beginTransaction();
        try {
            $product = $this->productRepository->delete($product);
        } catch (\Exception $e) {
            DB::rollback();
            throw new \InvalidArgumentException($e->getMessage());
        }

        DB::commit();

        return $product;
    }

    public function validateproduct($data)
    {
        $validator = Validator::make($data, [
            'name' => 'string|max:255|required',
            'description' => 'string|max:255|required',
            'price' => 'numeric|required',
            'categories_id' => 'numeric|required',
            'tags' => 'string|max:255|required',
        ]);

        if ($validator->fails()) {
            throw new \InvalidArgumentException($validator->errors()->first());
        }

        return $validator;
    }
}