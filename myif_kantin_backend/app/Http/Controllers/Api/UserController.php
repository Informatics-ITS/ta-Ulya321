<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Exception;

class UserController extends Controller
{
    public function show($id): JsonResponse
    {
        try {
            $user = User::where('user_id', $id)->firstOrFail();

            $userData = [
                'user_id' => $user->user_id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?? null,
                'username' => $user->username,
                'role' => $user->role ?? 'user',
                'extra' => $user->extra ?? null,
                'created_at' => $user->created_at ? $user->created_at->toISOString() : null,
                'updated_at' => $user->updated_at ? $user->updated_at->toISOString() : null
            ];

            return response()->json([
                'success' => true,
                'message' => 'User retrieved successfully',
                'data' => $userData
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $user = User::where('user_id', $id)->firstOrFail();

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|email|unique:users,email,' . $user->user_id . ',user_id',
                'phone' => 'sometimes|nullable|string|max:20',
                'username' => 'sometimes|required|string|max:50|unique:users,username,' . $user->user_id . ',user_id',
                'password' => 'sometimes|nullable|string|min:8|confirmed',
                'current_password' => 'required_with:password|string',
                'extra' => 'sometimes|nullable|array'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            if ($request->has('password') && $request->filled('password')) {
                if (!Hash::check($request->input('current_password'), $user->password)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Current password is incorrect',
                        'errors' => [
                            'current_password' => ['Current password is incorrect']
                        ]
                    ], 422);
                }
            }

            $updateData = [];
            
            if ($request->has('name')) {
                $updateData['name'] = $request->input('name');
            }
            
            if ($request->has('email')) {
                $updateData['email'] = $request->input('email');
            }
            
            if ($request->has('phone')) {
                $updateData['phone'] = $request->input('phone');
            }
            
            if ($request->has('username')) {
                $updateData['username'] = $request->input('username');
            }
            
            if ($request->has('extra')) {
                $updateData['extra'] = $request->input('extra');
            }
            
            if ($request->has('password') && $request->filled('password')) {
                $updateData['password'] = Hash::make($request->input('password'));
            }

            if (!empty($updateData)) {
                $user->update($updateData);
            }

            $userData = [
                'user_id' => $user->user_id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'username' => $user->username,
                'role' => $user->role ?? 'user',
                'extra' => $user->extra,
                'updated_at' => $user->updated_at->toISOString()
            ];

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'data' => $userData
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }
    }

    public function register(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'phone' => 'required|string|max:20|unique:users,phone',
                'email' => 'required|string|email|max:255|unique:users,email',
                'username' => 'required|string|max:50|unique:users,username',
                'password' => 'required|string|min:8|confirmed',
                'extra' => 'nullable|array'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = User::create([
                'name' => $request->input('name'),
                'phone' => $request->input('phone'),
                'email' => $request->input('email'),
                'username' => $request->input('username'),
                'password' => Hash::make($request->input('password')),
                'role' => 'user',
                'extra' => $request->input('extra', [])
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            $userData = [
                'user_id' => $user->user_id,
                'name' => $user->name,
                'phone' => $user->phone,
                'email' => $user->email,
                'username' => $user->username,
                'role' => $user->role,
                'extra' => $user->extra,
                'created_at' => $user->created_at->toISOString()
            ];

            return response()->json([
                'success' => true,
                'message' => 'Registration successful',
                'data' => [
                    'user' => $userData,
                    'token' => $token
                ]
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'username' => 'required|string',
                'password' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = User::where('username', $request->input('username'))
                    ->orWhere('email', $request->input('username'))
                    ->first();

            if (!$user || !Hash::check($request->input('password'), $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials'
                ], 401);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            $userData = [
                'user_id' => $user->user_id,
                'name' => $user->name,
                'phone' => $user->phone ?? null,
                'email' => $user->email,
                'username' => $user->username,
                'role' => $user->role ?? 'user',
                'extra' => $user->extra
            ];

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => $userData,
                    'token' => $token
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Login failed: ' . $e->getMessage()
            ], 500);
        }
    }
}