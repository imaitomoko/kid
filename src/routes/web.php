<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MypageController;
use App\Http\Controllers\AdminListController;
use App\Http\Controllers\AdminUserController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// ログインルート
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/admin/login', [AuthController::class, 'showLogin'])->defaults('role', 'admin')->name('admin.login');
Route::post('/admin/login', [AuthController::class, 'login'])->defaults('role', 'admin');
Route::post('/admin/logout', [AuthController::class, 'logout'])->defaults('role', 'admin')->name('admin.logout');

Route::get('/teacher/login', [AuthController::class, 'showLogin'])->defaults('role', 'teacher')->name('teacher.login');
Route::post('/teacher/login', [AuthController::class, 'login'])->defaults('role', 'teacher');
Route::post('/teacher/logout', [AuthController::class, 'logout'])->defaults('role', 'teacher')->name('teacher.logout');

// 管理者専用ルート
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin', [AdminListController::class, 'index'])->name('admin');
    Route::get('/user', [AdminUserController::class, 'index'])->name('admin.user');
    Route::get('/create', [AdminUserController::class, 'create'])->name('admin.create');
    Route::post('/store', [AdminUserController::class, 'store'])->name('admin.store');

});


// 先生専用ルート
Route::middleware(['auth', 'role:teacher'])->group(function () {
    Route::get('/teacher/dashboard', [TeacherController::class, 'index']);
});

// 一般ユーザー
Route::middleware(['auth', 'role:user'])->group(function () {
    Route::get('/dashboard', [MypageController::class, 'index'])->name('dashboard');
    
});

