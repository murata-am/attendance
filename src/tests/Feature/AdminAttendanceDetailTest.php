<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Admin;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class AdminAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    // 管理者は選択した詳細画面のデータが見られる
    public function test_admin_attendance_detail()
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
        ]);
        $admin = Admin::create([
            'name' => 'テスト管理者',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::now()->toDateString(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);
        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);
        $response = $this->actingAs($admin, 'admin')->get(route('admin.attendance.show', [$attendance->id]));

        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee(Carbon::now()->format('Y年'));
        $response->assertSee(Carbon::now()->format('n月j日'));
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('12:00');
        $response->assertSee('13:00');

    }

    // 出勤時間が退勤時間より後の場合、バリデーションメッセージが表示される
    public function test_validation_clock_out_before_clock_in
    (
    ) {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
        ]);
        $admin = Admin::create([
            'name' => 'テスト管理者',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::now()->toDateString(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);
        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);

        $response = $this->actingAs($admin, 'admin')->post(route('admin.attendance.update', $attendance->id), [
            'clock_in' => '18:00',
            'clock_out' => '09:00',
            'reason' => 'テストの理由',
        ]);

        $response->assertSessionHasErrors([
            'clock_in_out' => '出勤時間もしくは退勤時間が不適切な値です'
        ]);


    }

    // 休憩開始時間が退勤時間より後になっている時、バリデーションメッセージが表示される
    public function test_validation_break_start_before_clock_out
    (
    ) {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
        ]);
        $admin = Admin::create([
            'name' => 'テスト管理者',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->actingAs($admin, 'admin')->post(route('admin.attendance.update', ['id'=>$attendance->id]), [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'break_start' => ['19:00'],
            'break_end' => ['20:00'],
            'reason' => 'テストの理由',
        ]);

        $response->assertSessionHasErrors(['break_time.0' => '休憩時間が勤務時間外です']);

    }

    // 休憩終了時間が退勤時間より後になっている時、バリデーションメッセージが表示される
    public function test_validation_break_end_after_clock_out
    (
    ) {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
        ]);
        $admin = Admin::create([
            'name' => 'テスト管理者',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->actingAs($admin, 'admin')->post(route('admin.attendance.update', ['id' => $attendance->id]), [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'break_start' => ['17:00'],
            'break_end' => ['19:00'],
            'reason' => 'テストの理由',
        ]);

        $response->assertSessionHasErrors(['break_time.0' => '休憩時間が勤務時間外です']);

    }

    // 備考欄が未入力の時、バリデーションメッセージが表示される
    public function test_validation_reason
    (
    ) {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
        ]);
        $admin = Admin::create([
            'name' => 'テスト管理者',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->actingAs($admin, 'admin')->post(route('admin.attendance.update', ['id' => $attendance->id]), [
            'clock_in' => '010:00',
            'clock_out' => '18:00',
            'reason' => '',
        ]);

        $response->assertSessionHasErrors(['reason' => '備考を記入してください']);

    }
}