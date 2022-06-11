<?php

namespace App\Services;

use App\Http\Resources\ProductGalleryCollection;
use App\Http\Resources\ProductGalleryResource;
use App\Models\ProductGallery;
use App\Models\User;
use App\Repositories\ProductGalleryRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ProductGalleryService
{
    private $productGalleryRepository;

    public function __construct(ProductGalleryRepository $productGalleryRepository)
    {
        $this->productGalleryRepository = $productGalleryRepository;
    }

    public function getAll($request): ProductGalleryCollection
    {
        $product_galleries = $this->productGalleryRepository->getAll($request);

        return $product_galleries;
    }

    public function getById($id): ProductGalleryResource
    {
        $product_galleries = $this->productGalleryRepository->getById($id);

        return $product_galleries;
    }

    public function create($data): \App\Http\Resources\ProductGalleryResource
    {
        $validator = $this->validateProduct($data);

        DB::beginTransaction();
        try {
            $product_galleries = $this->productGalleryRepository->create($data);
        } catch (\Exception $e) {
            DB::rollback();
            throw new \InvalidArgumentException($e->getMessage());
        }

        DB::commit();

        return $product_galleries;
    }

    public function update($id, $data): \App\Http\Resources\ProductGalleryResource
    {
        $validator = $this->validateProduct($data);

        $product_galleries = ProductGallery::findOrFail($id);

        if (!$product_galleries) {
            throw new ModelNotFoundException('product not found', 404);
        }

        DB::beginTransaction();
        try {
            $product_galleries = $this->productGalleryRepository->update($product_galleries, $data);
        } catch (\Exception $e) {
            DB::rollback();
            throw new \InvalidArgumentException($e->getMessage());
        }

        DB::commit();

        return $product_galleries;
    }

    public function delete($id)
    {
        $product_galleries = ProductGallery::findOrFail($id);

        if (!$product_galleries) {
            throw new ModelNotFoundException('product not found', 404);
        }

        DB::beginTransaction();
        try {
            $product_galleries = $this->productGalleryRepository->delete($product_galleries);
        } catch (\Exception $e) {
            DB::rollback();
            throw new \InvalidArgumentException($e->getMessage());
        }

        DB::commit();

        return $product_galleries;
    }

    public function validateproduct($data)
    {
        $validator = Validator::make($data, [
            'products_id' => 'numeric|required',
            'files' => 'required',
        ]);

        if ($validator->fails()) {
            throw new \InvalidArgumentException($validator->errors()->first());
        }

        return $validator;
    }
}