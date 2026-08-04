<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use App\Services\GoogleSheetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserAdminController extends Controller
{
    public function __construct(
        protected GoogleSheetService $sheets,
        protected AuthService $auth
    ) {
    }

    public function index()
    {
        return response()->json(['data' => $this->sheets->getUsers()]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:180',
            'password' => 'required|string|min:6',
            'role' => 'nullable|in:admin,user',
            'avatar' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($this->sheets->getUserByEmail($request->input('email'))) {
            return response()->json(['message' => 'Email already exists.'], 422);
        }

        $data = $validator->validated();
        $data['password'] = Hash::make($data['password']);
        $data['role'] = $data['role'] ?? 'user';

        $sheetUser = $this->sheets->createUser($data);
        $this->auth->syncLocalUser($sheetUser);

        unset($sheetUser['password']);

        return response()->json(['data' => $sheetUser, 'message' => 'User created.'], 201);
    }

    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:120',
            'email' => 'sometimes|required|email|max:180',
            'password' => 'nullable|string|min:6',
            'role' => 'nullable|in:admin,user',
            'avatar' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user = $this->sheets->updateUser($id, $data);

        if (! $user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $this->auth->syncLocalUser($user);
        unset($user['password']);

        return response()->json(['data' => $user, 'message' => 'User updated.']);
    }

    public function destroy(string $id)
    {
        if ((string) $id === (string) auth()->id()) {
            return response()->json(['message' => 'You cannot delete your own account.'], 422);
        }

        if (! $this->sheets->deleteUser($id)) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        \App\Models\User::where('id', $id)->delete();

        return response()->json(['message' => 'User deleted.']);
    }
}
