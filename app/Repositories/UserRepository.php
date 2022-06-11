<?php

namespace App\Repositories;

use App\Http\Resources\UserCollection;
use App\Models\User;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Hash;

class UserRepository
{
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    private function filterUsername($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
    }


    public function getAll($request): UserCollection
    {
        $query = $this->user->query();
        $page = $query->perPage($request->perPage ?? 10);

        $users = $query->latest()->paginate($page);

        return new UserCollection($users);
    }

    public function getById($id): UserResource
    {
        $user = $this->user->findOrFail($id);

        return new UserResource($user);
    }

    public function register($data)
    {
        $data['password'] = Hash::make($data['password']);
        $user = $this->user->create($data);

        return new UserResource($user);
    }

    public function update($user, $data): UserResource
    {
        $data['password'] = Hash::make($data['password']);
        $user->update($data);
        return new UserResource($user);
    }

    public function delete($user)
    {
        $user->delete();

        return new UserResource($user);
    }

    public function login($data)
    {
        $fieldType = $this->filterUsername($data['username']);

        if (Auth::attempt(array($fieldType => $data['username'], 'password' => $data['password']))) {
            $user = Auth::user();
            $login['user'] = new UserResource($user);
            $login['token'] = Auth::user()->createToken('Laravel8PassportAuth')->accessToken;
            return $login;
        }

        throw new ModelNotFoundException(__('failed-login'));
    }
}