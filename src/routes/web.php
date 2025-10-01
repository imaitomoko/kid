<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MypageController;
use App\Http\Controllers\AdminListController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AdminFeeController;
use App\Http\Controllers\AdminScheduleController;
use App\Http\Controllers\AdminReservationController;



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
    Route::get('/book-list', [AdminListController::class, 'list'])->name('book.list');
    Route::post('/attendance/start/{reservation}', [AdminListController::class, 'start'])->name('attendance.start');
    Route::post('/attendance/end/{reservation}', [AdminListController::class, 'end'])->name('attendance.end');
    Route::post('/attendance/meal/{attendance}', [AdminListController::class, 'mealUsed'])->name('attendance.meal');
    Route::post('/attendance/snack/{attendance}', [AdminListController::class, 'snackUsed'])->name('attendance.snack');

    Route::get('/admin/schedule', [AdminScheduleController::class, 'index'])->name('admin.schedule');
    Route::get('/admin/schedule/{date}', [AdminScheduleController::class, 'show'])->name('admin.schedule.show');
    Route::post('/admin/schedule/{date}', [AdminScheduleController::class, 'update'])->name('admin.schedule.update');

    Route::get('admin/reservations', [AdminReservationController::class, 'calendar'])->name('admin.reservation');
    Route::get('admin/reservations/{date}', [AdminReservationController::class, 'list'])->name('admin.reservation.list');
    Route::delete('/admin/reservations/{id}/cancel', [AdminReservationController::class, 'cancel'])
    ->name('admin.reservation.cancel');

    Route::get('admin/nonmember/create/{date}', [AdminReservationController::class, 'createNonMember'])
    ->name('admin.nonmember.create');
    Route::post('admin/nonmember/store', [AdminReservationController::class, 'storeNonMember'])
    ->name('admin.nonmember.store');
    Route::get('admin/reservations/member-proxy/create', [ReservationController::class, 'createMemberProxy'])
    ->name('admin.member_proxy.create');


    Route::get('/user', [AdminUserController::class, 'index'])->name('admin.user');
    Route::get('/create', [AdminUserController::class, 'create'])->name('admin.create');
    Route::post('/store', [AdminUserController::class, 'store'])->name('admin.store');
    Route::get('/search', [AdminUserController::class, 'search'])->name('admin.search');
    Route::get('/show', [AdminUserController::class, 'show'])->name('admin.show');
    Route::get('/edit/{id}', [AdminUserController::class, 'edit'])->name('admin.edit');
    Route::post('/update/{id}', [AdminUserController::class, 'update'])->name('admin.update');
    Route::delete('/destroy/{id}', [AdminUserController::class, 'destroy'])->name('admin.destroy');
    Route::get('/fees', [AdminFeeController::class, 'index'])->name('admin.fee.index');
    Route::post('/fees', [AdminFeeController::class, 'store'])->name('admin.fee.store');
    Route::put('fees/update/{id}', [AdminFeeController::class, 'update'])->name('admin.fee.update');
    Route::delete('fees/destroy/{id}', [AdminFeeController::class, 'destroy'])->name('admin.fee.destroy');
    
});


// 先生専用ルート
Route::middleware(['auth', 'role:teacher'])->group(function () {
    Route::get('/teacher/dashboard', [TeacherController::class, 'index']);
});

// 一般ユーザー
Route::middleware(['auth', 'role:user'])->group(function () {
    Route::get('/dashboard', [MypageController::class, 'index'])->name('user.dashboard');
    
});

