<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\ProductCategory;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Services\ProductCategoryService;
use App\Http\Resources\ProductCategoryCollection;
use App\Http\Resources\ProductCategoryResource;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ProductCategoryController extends Controller
{
    private $productCategoryService;
    private $request = [
        'name',
    ];

    public function __construct(ProductCategoryService $productCategoryService)
    {
        $this->productCategoryService = $productCategoryService;
    }

    public function index(Request $request): \App\Helpers\ResponseFormatter|ProductCategoryCollection
    {
        try {
            $result = $this->productCategoryService->getAll($request);
        } catch (\Exception $e) {
            return ResponseFormatter::error($e->getMessage());
        }

        return $result;
    }

    public function show($id): \Illuminate\Http\JsonResponse|ProductCategoryResource
    {
        try {
            $result = $this->productCategoryService->getByid($id);
        } catch (ModelNotFoundException $e) {
            return ResponseFormatter::error($e->getMessage());
        } catch (\Exception $e) {
            return ResponseFormatter::error($e->getMessage());
        }

        return $result;
    }

    public function store(Request $request): JsonResponse|ProductCategoryResource
    {
        $data = $request->only($this->request);
        try {
            $result = $this->productCategoryService->create($data);
        } catch (AccessDeniedHttpException $e) {
            return ResponseFormatter::error('You are not authrized to perform this action', 403);
        } catch (\Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }

        return $result;
    }

    public function update($id, Request $request): JsonResponse|ProductCategoryResource
    {
        $data = $request->only($this->request);
        try {
            $result = $this->productCategoryService->update($id, $data);
        } catch (AuthorizationException) {
            return ResponseFormatter::error('You are not authrized to perform this action', 403);
        } catch (ModelNotFoundException $e) {
            return ResponseFormatter::error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }

        return $result;
    }

    public function destroy($id): JsonResponse|ProductCategoryResource
    {
        try {
            $result = $this->productCategoryService->delete($id);
        } catch (ModelNotFoundException $e) {
            return ResponseFormatter::error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }

        return $result;
    }

    public function all(Request $request)
    {
        $id = $request->input('id');
        $limit = $request->input('limit', 6);
        $name = $request->input('name');
        $show_product = $request->input('show_product');

        if ($id) {
            $category = ProductCategory::with(['products'])->find($id);

            if ($category)
                return ResponseFormatter::success(
                    $category,
                    'Data produk berhasil diambil'
                );
            else
                return ResponseFormatter::error(
                    null,
                    'Data kategori produk tidak ada',
                    404
                );
        }

        $category = ProductCategory::query();

        if ($name)
            $category->where('name', 'like', '%' . $name . '%');

        if ($show_product)
            $category->with('products');

        return ResponseFormatter::success(
            $category->paginate($limit),
            'Data list kategori produk berhasil diambil'
        );
    }
}