<?php

use App\Models\CorrectionRequest;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceViewController;
use App\Http\Controllers\CorrectionRequestController;
use App\Http\Controllers\AdminLoginController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/admin/login', function () {
    return view('admin.auth.login');
})->name('admin.login');

Route::post('/admin/login', [AdminLoginController::class, 'login']);

// 後でミドルウェアのauth横に'verified'を付け加える

Route::middleware(['auth'])->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'index']);

    Route::post('/attendance/clockIn', [AttendanceController::class, 'storeClockIn'])->name('attendance.clockIn');
    Route::post('/attendance/breakStart', [AttendanceController::class, 'storeBreakStart'])->name('attendance.breakStart');
    Route::post('/attendance/breakEnd', [AttendanceController::class, 'storeBreakEnd'])->name('attendance.breakEnd');
    Route::post('/attendance/clockOut', [AttendanceController::class, 'storeClockOut'])->name('attendance.clockOut');

    Route::get('/attendance/list', [AttendanceViewController::class, 'index'])->name('attendance.list');

    Route::get('/attendance/detail/{id}', [AttendanceViewController::class, 'edit'])->name('attendance.edit');
    Route::post('/attendance/detail/{id}', [AttendanceViewController::class, 'store'])->name('attendance.store');

    Route::get('/stamp_correction_request/list', [CorrectionRequestController::class, 'index'])->name('stamp_correction_request.list');

});

