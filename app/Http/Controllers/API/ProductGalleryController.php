<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\ProductGallery;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Services\ProductGalleryService;
use App\Http\Resources\ProductGalleryCollection;
use App\Http\Resources\ProductGalleryResource;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ProductGalleryController extends Controller
{
    private $productGalleryService;
    private $request = [
        'products_id',
        'files',
    ];

    public function __construct(ProductGalleryService $productGalleryService)
    {
        $this->productGalleryService = $productGalleryService;
    }

    public function index(Request $request): \App\Helpers\ResponseFormatter|ProductGalleryCollection
    {
        try {
            $result = $this->productGalleryService->getAll($request);
        } catch (\Exception $e) {
            return ResponseFormatter::error($e->getMessage());
        }

        return $result;
    }

    public function show($id): \Illuminate\Http\JsonResponse|ProductGalleryResource
    {
        try {
            $result = $this->productGalleryService->getByid($id);
        } catch (ModelNotFoundException $e) {
            return ResponseFormatter::error($e->getMessage());
        } catch (\Exception $e) {
            return ResponseFormatter::error($e->getMessage());
        }
        // dd($result);
        return $result;
    }

    public function store(Request $request, ProductGallery $product): JsonResponse|ProductGalleryResource
    {
        // dd($request);
        $data = $request->only($this->request);
        try {
            $result = $this->productGalleryService->create($data);
        } catch (AccessDeniedHttpException $e) {
            return ResponseFormatter::error('You are not authrized to perform this action', 403);
        } catch (\Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }

        return $result;
    }

    public function update($id, Request $request): JsonResponse|ProductGalleryResource
    {
        $data = $request->only($this->request);
        try {
            $result = $this->productGalleryService->update($id, $data);
        } catch (AuthorizationException) {
            return ResponseFormatter::error('You are not authrized to perform this action', 403);
        } catch (ModelNotFoundException $e) {
            return ResponseFormatter::error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }

        return $result;
    }

    public function destroy($id): JsonResponse|ProductGalleryResource
    {
        try {
            $result = $this->productGalleryService->delete($id);
        } catch (ModelNotFoundException $e) {
            return ResponseFormatter::error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }

        return $result;
    }
}