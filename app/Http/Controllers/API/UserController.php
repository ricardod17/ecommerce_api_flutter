<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Rules\Password;
use App\Services\UserService;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Error;
use InvalidArgumentException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{

    private $userService;
    private $request = [
        'name',
        'email',
        'username',
        'roles',
        'password',
        'phone',
        'photo_profile_url',
    ];

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function postRegister(Request $request)
    {
        $data = $request->all();

        try {
            $data = $this->userService->postRegister($data);
        } catch (\InvalidArgumentException $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        } catch (\Exception $e) {
            return ResponseFormatter::error('General error : ' . $e->getMessage(), 500);
        }
        return ResponseFormatter::success($data, "Register success");
    }

    public function index(Request $request): JsonResponse|UserCollection
    {
        try {
            $result = $this->userService->getAll($request);
        } catch (\Exception $e) {
            return ResponseFormatter::error($e->getMessage());
        }

        return ResponseFormatter::success($result, 'Users successfully retrieved');
    }

    public function show($id): JsonResponse|UserResource
    {
        try {
            $result = $this->userService->getById($id);
        } catch (ModelNotFoundException $e) {
            return ResponseFormatter::error($e->getMessage());
        } catch (\Exception $e) {
            return ResponseFormatter::error($e->getMessage());
        }

        return ResponseFormatter::success($result, 'User successfully retrieved');
    }
    /**
     * @param Request $request
     * @return mixed
     */
    public function fetch(Request $request)
    {
        return ResponseFormatter::success($request->user(), 'Data profile user berhasil diambil');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */

    public function postLogin(Request $request)
    {
        $data = $request->only([
            'username',
            'password',
        ]);

        try {
            $result = $this->userService->loginPost($data);
        } catch (ModelNotFoundException $e) {
            return ResponseFormatter::error($e->getMessage(), 404);
        } catch (Error $e) {
            return ResponseFormatter::error("error : " . $e->getMessage(), 500);
        } catch (InvalidArgumentException $e) {
            return ResponseFormatter::error($e->getMessage(), 401);
        } catch (Exception $e) {
            return ResponseFormatter::error("General error : " . $e->getMessage(), 500);
        }

        return ResponseFormatter::success($result, 'Successfully login');
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'email|required',
                'password' => 'required'
            ]);

            $credentials = request(['email', 'password']);
            if (!Auth::attempt($credentials)) {
                return ResponseFormatter::error([
                    'message' => 'Unauthorized'
                ], 'Authentication Failed', 500);
            }

            $user = User::where('email', $request->email)->first();
            if (!Hash::check($request->password, $user->password, [])) {
                throw new \Exception('Invalid Credentials');
            }

            $tokenResult = $user->createToken('authToken')->plainTextToken;
            return ResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user
            ], 'Authenticated');
        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $error,
            ], 'Authentication Failed', 500);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'username' => ['required', 'string', 'max:255', 'unique:users'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string', new Password]
            ]);

            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'username' => $request->username,
                'password' => Hash::make($request->password),
            ]);

            $user = User::where('email', $request->email)->first();

            $tokenResult = $user->createToken('authToken')->plainTextToken;

            return ResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user
            ], 'User Registered');
        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $error,
            ], 'Authentication Failed', 500);
        }
    }

    public function logout(Request $request)
    {
        $token = $request->user()->token()->delete();

        return ResponseFormatter::success($token, 'Token Revoked');
    }


    public function destroy($id): JsonResponse|UserResource
    {
        try {
            $result = $this->userService->delete($id);
        } catch (ModelNotFoundException $e) {
            return ResponseFormatter::error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }

        return ResponseFormatter::success($result, 'User successfully deleted');
    }

    public function updateProfile(Request $request)
    {
        $data = $request->all();

        $user = Auth::user();
        $user->update($data);

        return ResponseFormatter::success($user, 'Profile successfully Updated');
    }

    public function update($id, Request $request): JsonResponse|UserResource
    {
        $data = $request->only($this->request);
        try {
            $result = $this->userService->update($id, $data);
        } catch (AuthorizationException) {
            return ResponseFormatter::error('You are not authrized to perform this action', 403);
        } catch (ModelNotFoundException $e) {
            return ResponseFormatter::error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }

        return ResponseFormatter::success($result, 'User successfully updated');
    }
}