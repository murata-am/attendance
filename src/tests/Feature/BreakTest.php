<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;


class BreakTest extends TestCase
{
    use RefreshDatabase;


    // 休憩ボタンが正しく機能する
    public function test_break_start()
    {
        $this->withoutMiddleware(\Illuminate\Auth\Middleware\EnsureEmailIsVerified::class);
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'email_verified_at' => now(),
            'password' => hash::make('password'),
        ]);

        $this->actingAs($user);
        Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now()->subMinutes(10)->format('H:i:s'),
            'status' => 'working',
        ]);

        $response = $this->get('/attendance');
        $response->assertSeeText('休憩入');

        $this->post('/attendance/breakStart');
        $response = $this->get('/attendance');

        $response->assertSeeText('休憩中');

    }

    // 休憩ボタンは1日何回でもできる
    public function test_break_start_many_times()
    {
        $this->withoutMiddleware(\Illuminate\Auth\Middleware\EnsureEmailIsVerified::class);
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'email_verified_at' => now(),
            'password' => hash::make('password'),
        ]);

        $this->actingAs($user);
        Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now()->subMinutes(10)->format('H:i:s'),
            'status' => 'working',
        ]);

        $response = $this->get('/attendance');

        $this->post('/attendance/breakStart');
        $this->post('/attendance/breakEnd');

        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSeeText('休憩入');

    }


    // 休憩戻ボタンが正しく機能する
    public function test_break_end()
    {
        $this->withoutMiddleware(\Illuminate\Auth\Middleware\EnsureEmailIsVerified::class);
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'email_verified_at' => now(),
            'password' => hash::make('password'),
        ]);

        $this->actingAs($user);
        Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now()->subMinutes(10)->format('H:i:s'),
            'status' => 'working',
        ]);

        $response = $this->get('/attendance');

        $this->post('/attendance/breakStart');
        $this->post('/attendance/breakEnd');

        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSeeText('出勤中');

    }


    // 休憩戻ボタンは1日何回でもできる
    public function test_break_end_many_times()
    {
        $this->withoutMiddleware(\Illuminate\Auth\Middleware\EnsureEmailIsVerified::class);
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'email_verified_at' => now(),
            'password' => hash::make('password'),
        ]);

        $this->actingAs($user);
        Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now()->subMinutes(10)->format('H:i:s'),
            'status' => 'working',
        ]);

        $response = $this->get('/attendance');

        $this->post('/attendance/breakStart');
        $this->post('/attendance/breakEnd');
        $this->post('/attendance/breakStart');

        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSeeText('休憩戻');

    }

    // 休憩時間が勤怠一覧画面で確認できる
    public function test_attendance_list_show_break_time()
    {
        $this->withoutMiddleware(\Illuminate\Auth\Middleware\EnsureEmailIsVerified::class);
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'email_verified_at' => now(),
            'password' => hash::make('password'),
        ]);

        $this->actingAs($user);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2025-07-14',
            'clock_in' => '9:00:00',
            'status' => 'working',
        ]);

        // 休憩開始（11:15）
        Carbon::setTestNow(Carbon::create(2025, 7, 14, 11, 15));
        $this->post('/attendance/breakStart');

        // 休憩終了（11:30）
        Carbon::setTestNow(Carbon::create(2025, 7, 14, 11, 30));
        $this->post('/attendance/breakEnd');

        // 退勤（18:00）
        Attendance::where('user_id', $user->id)
            ->where('work_date', '2025-07-14')
            ->update([
                'clock_out' => '18:00:00',
                'status' => 'finished_work',
            ]);


        $response = $this->get('/attendance/list');
        $response->assertStatus(200);

        $response->assertSeeText('07/14(月)');
        $response->assertSeeText('00:15');

    }
}
