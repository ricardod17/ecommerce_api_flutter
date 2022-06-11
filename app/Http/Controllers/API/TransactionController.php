<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\TransactionService;
use App\Http\Resources\TransactionCollection;
use App\Http\Resources\TransactionResource;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Auth\Access\AuthorizationException;

class TransactionController extends Controller
{
    private $transactionService;
    private $request = [
        'address', 'items.*', 'total_price', 'shipping_price', 'status'
    ];

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    public function index(Request $request): JsonResponse|TransactionCollection
    {
        try {
            $result = $this->transactionService->getAll($request);
        } catch (\Exception $e) {
            return ResponseFormatter::error($e->getMessage());
        }

        return $result;
    }

    public function all(Request $request)
    {
        $id = $request->input('id');
        $limit = $request->input('limit', 6);
        $status = $request->input('status');

        if ($id) {
            $transaction = Transaction::with(['items.product'])->find($id);

            if ($transaction)
                return ResponseFormatter::success(
                    $transaction,
                    'Data transaksi berhasil diambil'
                );
            else
                return ResponseFormatter::error(
                    null,
                    'Data transaksi tidak ada',
                    404
                );
        }

        $transaction = Transaction::with(['items.product'])->where('users_id', Auth::user()->id);

        if ($status)
            $transaction->where('status', $status);

        return ResponseFormatter::success(
            $transaction->paginate($limit),
            'Data list transaksi berhasil diambil'
        );
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkout(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'exists:products,id',
            'total_price' => 'required',
            'shipping_price' => 'required',
            'status' => 'required|in:PENDING,SUCCESS,CANCELLED,FAILED,SHIPPING,SHIPPED',
        ]);

        $transaction = Transaction::create([
            'users_id' => Auth::user()->id,
            'address' => $request->address,
            'total_price' => $request->total_price,
            'shipping_price' => $request->shipping_price,
            'status' => $request->status
        ]);

        foreach ($request->items as $transaction) {
            TransactionItem::create([
                'users_id' => Auth::user()->id,
                'products_id' => $transaction['id'],
                'transactions_id' => $transaction->id,
                'quantity' => $transaction['quantity']
            ]);
        }

        return ResponseFormatter::success($transaction->load('items.product'), 'Transaksi berhasil');
    }

    public function store(Request $request): JsonResponse|TransactionResource
    {
        $data = $request->all();
        try {
            $result = $this->transactionService->create($data);
        } catch (AccessDeniedHttpException $e) {
            return ResponseFormatter::error('You are not authrized to perform this action', 403);
        } catch (\Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }

        return $result;
    }

    public function show($id): \Illuminate\Http\JsonResponse|TransactionResource
    {
        try {
            $result = $this->transactionService->getById($id);
        } catch (ModelNotFoundException $e) {
            return ResponseFormatter::error($e->getMessage());
        } catch (\Exception $e) {
            return ResponseFormatter::error($e->getMessage());
        }
        return $result;
    }

    public function update($id, Request $request): JsonResponse|TransactionResource
    {
        $data = $request->only($this->request);
        try {
            $result = $this->transactionService->update($id, $data);
        } catch (AuthorizationException) {
            return ResponseFormatter::error('You are not authrized to perform this action', 403);
        } catch (ModelNotFoundException $e) {
            return ResponseFormatter::error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }

        return $result;
    }

    public function destroy($id): JsonResponse|TransactionResource
    {
        try {
            $result = $this->transactionService->delete($id);
        } catch (ModelNotFoundException $e) {
            return ResponseFormatter::error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }

        return $result;
    }
}