<?php

namespace App\Services;

use App\Http\Resources\TransactionCollection;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Models\User;
use App\Repositories\TransactionRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class TransactionService
{
    private $transactionRepository;

    public function __construct(TransactionRepository $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
    }

    public function getAll($request): TransactionCollection
    {
        $transaction = $this->transactionRepository->getAll($request);

        return $transaction;
    }


    public function getById($id): TransactionResource
    {
        $transaction = $this->transactionRepository->getById($id);

        return $transaction;
    }


    public function create($data): \App\Http\Resources\TransactionResource
    {
        $validator = $this->validateTransaction($data);

        DB::beginTransaction();
        try {
            $transaction = $this->transactionRepository->create($data);
        } catch (\Exception $e) {
            DB::rollback();
            throw new \InvalidArgumentException($e->getMessage());
        }

        DB::commit();

        return $transaction;
    }

    public function update($id, $data): \App\Http\Resources\TransactionResource
    {

        $validator = $this->validateTransaction($data);
        $transaction = Transaction::findOrFail($id);

        if (!$transaction) {
            throw new ModelNotFoundException('Transaction not found', 404);
        }

        DB::beginTransaction();
        try {
            $transaction = $this->transactionRepository->update($transaction, $data);
        } catch (\Exception $e) {
            DB::rollback();
            throw new \InvalidArgumentException($e->getMessage());
        }

        DB::commit();

        return $transaction;
    }


    public function delete($id)
    {
        $transaction = Transaction::findOrFail($id);

        if (!$transaction) {
            throw new ModelNotFoundException('Transaction not found', 404);
        }

        DB::beginTransaction();
        try {
            $transaction = $this->transactionRepository->delete($transaction);
        } catch (\Exception $e) {
            DB::rollback();
            throw new \InvalidArgumentException($e->getMessage());
        }

        DB::commit();

        return $transaction;
    }

    protected function validateTransaction($data)
    {
        $validator = Validator::make($data, [
            'items' => 'required|array',
            // 'items.*' => 'exists:id,quantity',
            'total_price' => 'required',
            'shipping_price' => 'required',
            'status' => 'required|in:PENDING,SUCCESS,CANCELLED,FAILED,SHIPPING,SHIPPED',
        ]);

        if ($validator->fails()) {
            throw new \InvalidArgumentException($validator->errors()->first());
        }

        return $validator;
    }
}