<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class MenuController extends Controller
{
    public function index()
    {
        $menus = Menu::paginate(10);

        if ($menus->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Belum ada data menu',
                'data' => []
            ], 200);
        }

        $transformedMenus = $menus->getCollection()->map(function ($menu) {
            return [
                'id' => Crypt::encryptString($menu->id),
                'menu' => $menu->menu,
                'created_at' => $menu->created_at,
                'updated_at' => $menu->updated_at,
            ];
        });

        $menus->setCollection($transformedMenus);

        return response()->json([
            'success' => true,
            'message' => 'Data menu ditemukan',
            'data' => $menus
        ], 200);
    }

    public function select()
    {
        return response()->json(Menu::select('id', 'menu')->get());
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'menu' => 'required|string|max:40',
            ], [
                'menu.required' => 'menu wajib diisi',
                'menu.string' => 'menu harus berupa teks',
                'menu.max' => 'menu tidak boleh lebih dari 40 karakter',
            ]);

            $menu = Menu::create($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Data menu berhasil disimpan',
                'data' => $menu
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan',
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        $decrypt = Crypt::decryptString($id);

        $menu = Menu::find($decrypt);

        if (!$menu) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        $request->validate([
            'menu' => 'required|string|max:40',
        ], [
            'menu.required' => 'menu wajib diisi',
            'menu.string' => 'menu harus berupa teks',
            'menu.max' => 'menu tidak boleh lebih dari 40 karakter',
        ]);

        $menu->menu = $request->menu;
        $menu->save();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengupdate data',
            'data' => $menu
        ], 200);
    }

    public function destroy(string $id)
    {
        try {
            $decrypt = Crypt::decryptString($id);

            $menu = Menu::find($decrypt);

            if (!$menu) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan.',
                ], 404);
            }

            // Hapus data
            $menu->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data menu berhasil dihapus.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
