<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return view('content.users.index', compact('users'));
    }

    public function saveUser(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'phone' => 'required',
            'email' => 'required|email|unique:users,email,' . $request->user_id . ',user_id',
            'username' => 'required|unique:users,username,' . $request->user_id . ',user_id',
            'role' => 'required',
        ]);

        $data = $request->all();
        
        $extraData = [];
        foreach ($data as $key => $value) {
            if (!in_array($key, ['name', 'phone', 'email', 'username', 'password', 'role', 'user_id'])) {
                $extraData[$key] = $value;
                unset($data[$key]);
            }
        }

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        if ($request->user_id) {
            $user = User::find($request->user_id);
            if (!$request->filled('password')) {
                unset($data['password']);
            }
            $user->update($data);
            $user->extra = array_merge($user->extra ?? [], $extraData);
            $user->save();
            $message = 'User updated successfully';
        } else {
            $data['password'] = Hash::make($request->password ?? 'indonesia2025');
            $data['extra'] = $extraData;
            User::create($data);
            $message = 'User created successfully';
        }

        return redirect()->route('users.index')->with('success', $message);
    }

    public function deleteUser($id)
    {
        $user = User::find($id);
        if ($user) {
            $user->delete();
            return redirect()->route('users.index')->with('success', 'User deleted successfully');
        }
        return redirect()->route('users.index')->with('error', 'User not found');
    }
}
