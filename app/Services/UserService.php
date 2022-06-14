<?php

namespace App\Services;

use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Error;



class UserService
{
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function getAll($request): UserCollection
    {
        $user = $this->userRepository->getAll($request);

        return $user;
    }

    public function getById($id): UserResource
    {
        $user = $this->userRepository->getById($id);

        return $user;
    }

    public function postRegister($data)
    {
        $validator = $this->validateUser($data);
        DB::beginTransaction();
        try {
            $result = $this->userRepository->register($data);
        } catch (\Exception $e) {
            DB::rollback();
            throw new \Exception($e->getMessage());
        }
        DB::commit();

        return $result;
    }

    public function loginPost($data)
    {
        $validator = Validator::make($data, [
            'password' => [
                'required',
                'min:8',
                'regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\x])(?=.*[!$#%]).*$/',
            ],
            'username' => 'required|min:6|max:18',
        ]);

        if ($validator->fails()) {
            throw new Error($validator->errors()->first());
        }

        try {
            $result = $this->userRepository->login($data);
        } catch (ModelNotFoundException $e) {
            throw new ModelNotFoundException($e->getMessage());
        }

        return $result;
    }

    public function update($id, $data): \App\Http\Resources\UserResource
    {
        $validator = $this->validateUser($data);
        $user = User::findOrFail($id);

        if (!$user) {
            throw new ModelNotFoundException('User not found', 404);
        }

        DB::beginUser();
        try {
            $user = $this->UserRepository->update($user, $data);
        } catch (\Exception $e) {
            DB::rollback();
            throw new \InvalidArgumentException($e->getMessage());
        }

        DB::commit();

        return $user;
    }

    public function delete($id)
    {
        $user = User::findOrFail($id);

        if (!$user) {
            throw new ModelNotFoundException('User not found', 404);
        }

        DB::beginTransaction();
        try {
            $user = $this->userRepository->delete($user);
        } catch (\Exception $e) {
            DB::rollback();
            throw new \InvalidArgumentException($e->getMessage());
        }

        DB::commit();

        return $user;
    }

    protected function validateUser($data)
    {
        $validator = Validator::make($data, [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => [
                'required',
                'min:8',
                'regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\x])(?=.*[!$#%]).*$/',
            ],
            'username' => 'required|unique:users|min:6|max:18',
            'phone_number' => 'numeric|nullable|min:11|max:15',
        ]);

        if ($validator->fails()) {
            throw new \InvalidArgumentException($validator->errors()->first());
        }

        return $validator;
    }
}