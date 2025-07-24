<?php

namespace Tests\Feature;

use App\Models\BreakTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;


class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    // 自分が行った勤怠情報がすべて表示されている
    public function test_all_show_attendance_list()
    {
        $this->withoutMiddleware(\Illuminate\Auth\Middleware\EnsureEmailIsVerified::class);
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2024-07-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);
        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance/list?year=2024&month=7');

        $response->assertStatus(200);
        $response->assertSee('07/01');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('1:00');
        $response->assertSee('8:00');
    }

    // 勤怠一覧に遷移した際に現在の月が表示されている
    public function test_attendance_list_show_current_month()
    {
        $this->withoutMiddleware(\Illuminate\Auth\Middleware\EnsureEmailIsVerified::class);
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');

        $currentMonth = now()->format('Y/m');
        $response->assertStatus(200);
        $response->assertSee($currentMonth);
    }

    // 前月を押下したときに表示月の前月の情報が表示される
    public function test_show_prev_month()
    {
        $this->withoutMiddleware(\Illuminate\Auth\Middleware\EnsureEmailIsVerified::class);
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
        ]);

        $prevMonth = now()->subMonth();
        $attendanceDate = $prevMonth->copy()->startOfMonth();
        $displayDate = $attendanceDate->format('m/d');

        Attendance::create([
            'user_id' => $user->id,
            'work_day' => $attendanceDate->format('Y-m-d'),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance/list?year=' . $prevMonth->year . '&month=' . $prevMonth->month);
        $response->assertStatus(200);

        $response->assertSee($prevMonth->format('Y/m'));

    }

    // 翌月を押下したときに表示月の前月の情報が表示される
    public function test_show_next_month()
    {
        $this->withoutMiddleware(\Illuminate\Auth\Middleware\EnsureEmailIsVerified::class);
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
        ]);

        $nextMonth = now()->addMonth();
        $attendanceDate = $nextMonth->copy()->startOfMonth();
        $displayDate = $attendanceDate->format('m/d');

        Attendance::create([
            'user_id' => $user->id,
            'work_day' => $attendanceDate->format('Y-m-d'),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance/list?year=' . $nextMonth->year . '&month=' . $nextMonth->month);
        $response->assertStatus(200);

        $response->assertSee($nextMonth->format('Y/m'));

    }

    // 詳細を押下したときにその日の勤怠詳細画面に遷移する
    public function test_show_attendance_detail()
    {
        $this->withoutMiddleware(\Illuminate\Auth\Middleware\EnsureEmailIsVerified::class);
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2024-07-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);
        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);

        $listResponse = $this->actingAs($user)->get('/attendance/list?year=2024&month=7');
        $listResponse->assertStatus(200);

        $detailResponse = $this->actingAs($user)->get('/attendance/detail/' . $attendance->id);
        $detailResponse->assertStatus(200);

    }



}
