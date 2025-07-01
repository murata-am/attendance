<?php

use App\Http\Controllers\StaffController;
use App\Models\CorrectionRequest;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceViewController;
use App\Http\Controllers\CorrectionRequestController;
use App\Http\Controllers\AdminLoginController;
use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\CorrectionApproveController;

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

    Route::get('/stamp_correction_request/list', [CorrectionRequestController::class, 'index'])->name('correction.request.list');
    Route::get('/stamp_correction_request/approved/{attendance_correct_request}', [CorrectionApproveController::class, 'show'])->name('stamp_correction_request.approved');
});


Route::get('/admin/login', function () {
    return view('admin.auth.login');})->name('admin.login');
Route::post('/admin/login', [AdminLoginController::class, 'login']);

Route::post('/custom-logout', [LogoutController::class, 'logout'])->name('custom.logout');

Route::middleware(['auth:admin'])->prefix('admin')->group(function () {
    Route::get('/attendance/list', [AdminAttendanceController::class, 'index'])->name('admin.attendance.list');

    Route::get('/attendance/detail/{id}', [AdminAttendanceController::class, 'show'])->name('admin.attendance.show');
    Route::post('/attendance/detail/{id}', [AdminAttendanceController::class, 'update'])->name('admin.attendance.update');

    Route::get('/staff/list', [StaffController::class, 'index'])->name('staff.list');
    Route::get('/attendance/staff/{userId}', [StaffController::class, 'show'])->name('attendance.staff.list');

    Route::post('/admin/attendance/csv', [StaffController::class, 'export'])->name('admin.attendance.csv');

    Route::get('/stamp_correction_request/list', [CorrectionRequestController::class, 'index'])->name('admin.stamp_correction_request.list');

    Route::get('/stamp_correction_request/approve/{attendance_correct_request}',  [CorrectionApproveController::class, 'show'])->name('admin.correction.approve.show');
    Route::post('/stamp_correction_request/approve/{attendance_correct_request}', [CorrectionApproveController::class, 'approve'])->name('correction.approve.update');
});