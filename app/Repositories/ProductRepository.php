<?php

namespace App\Repositories;

use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class ProductRepository
{
    private $product;

    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    public function getAll($request): ProductCollection
    {
        if ($request->has('categories')) {
            $query = $this->product->query();
            $query->where('categories_id', $request->categories);
        } else if ($request->has('tags')) {
            $query = $this->product->query();
            $query->where('tags', $request->tags);
        } else {
            $query = $this->product->query();
        }
        $page = $query->perPage($request->perPage ?? 10);

        $products = $query->latest()->paginate($page);

        return new ProductCollection($products);
    }

    public function getById($id): ProductResource
    {
        $product = $this->product->findOrFail($id);

        return new ProductResource($product);
    }

    public function create($data): ProductResource
    {
        $data['users_id'] =  Auth::user()->id;
        $product = $this->product->create($data);

        return new ProductResource($product);
    }

    public function update($product, $data): ProductResource
    {
        $product->update($data);

        return new ProductResource($product);
    }

    public function delete($product)
    {
        $product->delete();

        return new ProductResource($product);
    }
}