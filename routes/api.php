<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\MahasiswaMataKuliahController;
use App\Http\Controllers\MataKuliahController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OTPController;
use App\Http\Controllers\PostRandomDataController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserMenuController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/otp/generate', [OTPController::class, 'generateOTP']);
    Route::post('/otp/verifikasi-otp', [OTPController::class, 'verifikasiOTP']);
    Route::post('/otp/kirim-ulang', [OTPController::class, 'kirimUlangOTP']);
});

Route::post('/user/register', [AuthController::class, 'register']);
Route::post('/user/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::get('/user/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function() {
    Route::apiResource('/mahasiswas', MahasiswaController::class);
    Route::apiResource('/mahasiswa-mata-kuliah', MahasiswaMataKuliahController::class);
    Route::apiResource('/mata-kuliah', MataKuliahController::class);
    Route::apiResource('/user', UserController::class);
    Route::apiResource('/menu', MenuController::class);
    Route::post('/mahasiswa/random-data', [PostRandomDataController::class, 'postRandomData']);
    Route::get('/select-mhs', [MahasiswaController::class, 'select']);
    Route::get('/select-mk', [MataKuliahController::class, 'select']);
    Route::get('/select-user', [UserController::class, 'select']);
    Route::get('/select-menu', [MenuController::class, 'select']);

    Route::get('/users-menu', [UserMenuController::class, 'getUsers']);
    Route::get('/menus/{userId}', [UserMenuController::class, 'getUserMenus']);
    Route::post('/menus/{userId}', [UserMenuController::class, 'updateMenus']);
    Route::get('/menus', [UserMenuController::class, 'getAllMenus']);
});
