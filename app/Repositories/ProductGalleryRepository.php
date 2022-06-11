<?php

namespace App\Repositories;

use App\Http\Resources\ProductGalleryCollection;
use App\Http\Resources\ProductGalleryResource;
use App\Models\ProductGallery;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class ProductGalleryRepository
{
    private $productGallery;

    public function __construct(ProductGallery $productGallery)
    {
        $this->productGallery = $productGallery;
    }

    public function getAll($request): ProductGalleryCollection
    {
        $query = $this->productGallery->query();
        $page = $query->perPage($request->perPage ?? 10);

        $productGalleries = $query->latest()->paginate($page);

        return new ProductGalleryCollection($productGalleries);
    }

    public function getById($id): ProductGalleryResource
    {
        $productGallery = $this->productGallery->findOrFail($id);

        return new ProductGalleryResource($productGallery);
    }

    public function create($data): ProductGalleryResource
    {
        $data['users_id'] =  Auth::user()->id;
        $data['url'] = Storage::disk('public')->put('gallery', $data['files']);
        $product = $this->productGallery->create($data);
        return new ProductGalleryResource($product);
    }

    public function update($productGallery, $data): ProductGalleryResource
    {
        $data['url'] = Storage::disk('public')->put('gallery', $data['files']);
        $productGallery->update($data);
        return new ProductGalleryResource($productGallery);
    }

    public function delete($productGallery)
    {
        $productGallery->delete();

        return new ProductGalleryResource($productGallery);
    }
}