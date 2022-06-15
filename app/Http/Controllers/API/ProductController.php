<?php

namespace App\Http\Controllers\API;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Services\ProductService;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductResource;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ProductController extends Controller
{
    private $productService;
    private $request = [
        'name', 'description', 'price', 'categories_id', 'tags'
    ];

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function index(Request $request): JsonResponse|ProductCollection
    {
        try {
            $result = $this->productService->getAll($request);
        } catch (\Exception $e) {
            return ResponseFormatter::error($e->getMessage());
        }

        return ResponseFormatter::success($result, 'Product successfully retrieved');
    }

    public function show($id): \Illuminate\Http\JsonResponse|ProductResource
    {
        try {
            $result = $this->productService->getByid($id);
        } catch (ModelNotFoundException $e) {
            return ResponseFormatter::error($e->getMessage());
        } catch (\Exception $e) {
            return ResponseFormatter::error($e->getMessage());
        }

        return ResponseFormatter::success($result, 'Product successfully retrieved');
    }

    public function store(Request $request): JsonResponse|ProductResource
    {
        $data = $request->only($this->request);
        try {
            $result = $this->productService->create($data);
        } catch (AccessDeniedHttpException $e) {
            return ResponseFormatter::error('You are not authrized to perform this action', 403);
        } catch (\Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }

        return ResponseFormatter::success($result, 'Product created successfully');
    }

    public function update($id, Request $request): JsonResponse|ProductResource
    {
        $data = $request->only($this->request);
        try {
            $result = $this->productService->update($id, $data);
        } catch (AuthorizationException) {
            return ResponseFormatter::error('You are not authrized to perform this action', 403);
        } catch (ModelNotFoundException $e) {
            return ResponseFormatter::error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }

        return ResponseFormatter::success($result, 'Product updated successfully');
    }

    public function destroy($id): JsonResponse|ProductResource
    {
        try {
            $result = $this->productService->delete($id);
        } catch (ModelNotFoundException $e) {
            return ResponseFormatter::error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }

        return ResponseFormatter::success($result, 'Product deleted successfully');
    }

    public function all(Request $request)
    {
        $id = $request->input('id');
        $limit = $request->input('limit', 6);
        $name = $request->input('name');
        $description = $request->input('description');
        $tags = $request->input('tags');
        $categories = $request->input('categories');

        $price_from = $request->input('price_from');
        $price_to = $request->input('price_to');

        if ($id) {
            $product = Product::with(['category', 'galleries'])->find($id);

            if ($product)
                return ResponseFormatter::success(
                    $product,
                    'Data produk berhasil diambil'
                );
            else
                return ResponseFormatter::error(
                    null,
                    'Data produk tidak ada',
                    404
                );
        }

        $product = Product::with(['category', 'galleries']);

        if ($name)
            $product->where('name', 'like', '%' . $name . '%');

        if ($description)
            $product->where('description', 'like', '%' . $description . '%');

        if ($tags)
            $product->where('tags', 'like', '%' . $tags . '%');

        if ($price_from)
            $product->where('price', '>=', $price_from);

        if ($price_to)
            $product->where('price', '<=', $price_to);

        if ($categories)
            $product->where('categories_id', $categories);

        return ResponseFormatter::success(
            $product->paginate($limit),
            'Data list produk berhasil diambil'
        );
    }
}