<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UsernameController extends Controller
{
    public function checkUsername(Request $request): JsonResponse
    {
        $username = $request->get('username', '');
        $excludeUserId = $request->get('exclude_user_id');

        if (empty($username)) {
            return response()->json(['available' => false, 'message' => 'Username não pode ser vazio']);
        }

        if (strlen($username) < 3) {
            return response()->json(['available' => false, 'message' => 'Username deve ter pelo menos 3 caracteres']);
        }

        if (! preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            return response()->json(['available' => false, 'message' => 'Username pode conter apenas letras, números e underscore']);
        }

        $query = User::where('username', $username);

        if ($excludeUserId) {
            $query->where('id', '!=', $excludeUserId);
        }

        $exists = $query->exists();

        return response()->json([
            'available' => ! $exists,
            'message' => $exists ? 'Username já está em uso' : 'Username disponível',
        ]);
    }
}
