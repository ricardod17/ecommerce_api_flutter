<?php

namespace App\Repositories;

use App\Http\Resources\TransactionCollection;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransactionRepository
{
    private $transaction;

    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }

    public function getAll($request): TransactionCollection
    {
        $query = $this->transaction->query();
        $page = $query->perPage($request->perPage ?? 10);

        $transactions = $query->latest()->paginate($page);

        return new TransactionCollection($transactions);
    }

    public function getById($id): TransactionResource
    {
        $transaction = $this->transaction->findOrFail($id);

        return new TransactionResource($transaction);
    }

    public function create($data): TransactionResource
    {
        $data['users_id'] =  Auth::user()->id;
        $transactions = $this->transaction->create($data);
        $newid = $transactions->id;
        foreach ($data['items'] as $transaction) {
            TransactionItem::create([
                'users_id' => Auth::user()->id,
                'products_id' => $transaction['id'],
                'transactions_id' => $newid,
                'quantity' => $transaction['quantity']
            ]);
        }

        return new TransactionResource($transactions);
    }

    public function update($transaction, $data): TransactionResource
    {
        $transaction->update($data);
        $newid = $transaction['id'];
        // foreach ($data['items'] as $trans) {
        //     DB::table('transaction_items')->where('transactions_id', $newid)->update([
        //         'products_id' => $trans['id'],
        //         'quantity' => $trans['quantity']
        //     ]);
        // }
        return new TransactionResource($transaction);
    }

    public function delete($transaction): TransactionResource
    {
        $transaction->delete();
        return new TransactionResource($transaction);
    }
}