<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class UserMenuController extends Controller
{
    public function getUsers()
    {
        $users = User::paginate(10);

        if ($users->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Belum ada data pengguna',
                'data' => []
            ], 200);
        }

        // $transformedUsers = $users->getCollection()->map(function ($user) {
        //     return [
        //         'id' => Crypt::encryptString($user->id),
        //         'name' => $user->name,
        //         'created_at' => $user->created_at,
        //         'updated_at' => $user->updated_at,
        //     ];
        // });

        // $users->setCollection($transformedUsers);

        return response()->json([
            'success' => true,
            'message' => 'Data pengguna ditemukan',
            'data' => $users
        ], 200);
    }

    public function getUserMenus($userId)
    {
        $user = User::with('menus')->find($userId);

        if (!$user) {
            return response()->json([
                "success" => false,
                "message" => "User tidak ditemukan."
            ], 404);
        }
    
        return response()->json([
            "success" => true,
            "menus" => $user->menus->pluck('menu')
        ], 200);
    }

    public function updateMenus(Request $request, $userId)
    {
        $user = User::find($userId);

        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }

        $menuIds = Menu::whereIn('menu', $request->menus)->pluck('id')->toArray();

        if ($user->menus()) {
            $user->menus()->sync($menuIds);
            return response()->json(['message' => 'Berhasil mengupdate otorisasi menu'], 200);
        }
        
        return response()->json(['message' => 'Gagal memperbarui otorisasi menu'], 500);
    }

    public function getAllMenus()
    {
        return response()->json(Menu::all());
    }
}
