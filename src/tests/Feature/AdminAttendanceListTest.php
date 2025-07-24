<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Admin;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\CorrectionRequest;
use Carbon\Carbon;

class AdminAttendanceListTest extends TestCase
{
    use RefreshDatabase;


    // 管理者の勤怠一覧でユーザーの勤怠情報が見られる
    public function test_admin_attendance_list()
    {
        $user =User::create([
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
        $response = $this->actingAs($admin, 'admin')->get(route('admin.attendance.list'));

        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('01:00');
        $response->assertSee('08:00');
    }

    // 勤怠一覧に遷移したら、現在の日付が表示されている
    public function test_admin_attendance_list_of_the_day()
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
        $response = $this->actingAs($admin, 'admin')->get(route('admin.attendance.list'));

        $response->assertStatus(200);
        $response->assertSee(Carbon::now()->format('Y年n月j日'));
    }

    // 勤怠一覧の「前日」を押したら、前の日の勤怠情報が表示される
    public function test_admin_attendance_list_the_day_before()
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

        $prevDate = Carbon::yesterday()->toDateString();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => $prevDate,
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);
        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);


        $response = $this->actingAs($admin, 'admin')->get(route('admin.attendance.list'));
        $response = $this->actingAs($admin, 'admin')->get(route('admin.attendance.list', ['date' => $prevDate]));

        $response->assertStatus(200);
        $response->assertSee(Carbon::parse($prevDate)->format('Y年n月j日'));
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('01:00');
        $response->assertSee('08:00');
    }

    // 勤怠一覧の「翌日」を押したら、次の日の勤怠情報が表示される
    public function test_admin_attendance_list_the_next_day()
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

        $nextDate = Carbon::tomorrow()->toDateString();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => $nextDate,
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);
        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);


        $response = $this->actingAs($admin, 'admin')->get(route('admin.attendance.list'));
        $response = $this->actingAs($admin, 'admin')->get(route('admin.attendance.list', ['date' => $nextDate]));

        $response->assertStatus(200);
        $response->assertSee(Carbon::parse($nextDate)->format('Y年n月j日'));
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('01:00');
        $response->assertSee('08:00');
    }


}
