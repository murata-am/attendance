<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class ClockOutTest extends TestCase
{
    use RefreshDatabase;

    // 退勤ボタンが正しく機能する
    public function test_clock_out()
    {
        $this->withoutMiddleware(\Illuminate\Auth\Middleware\EnsureEmailIsVerified::class);
        $user = User::factory()->create();

        $this->actingAs($user);
        Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now()->subMinutes(10)->format('H:i:s'),
            'status' => 'working',
        ]);

        $response = $this->get('/attendance');
        $response->assertSeeText('退勤');

        $this->post('/attendance/clockOut');
        $response = $this->get('/attendance');

        $response->assertSeeText('退勤済');
    }

    // 退勤時刻が管理画面で確認できる
    public function test_clock_out_admin_show()
    {
        $this->withoutMiddleware(\Illuminate\Auth\Middleware\EnsureEmailIsVerified::class);

        $user = User::factory()->create();
        $admin = Admin::factory()->create();

        $this->actingAs($user);
        $this->post('/attendance/clockIn');
        $this->post('/attendance/clockOut');

        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', now()->toDateString())
            ->first();

        $clockInTime = Carbon::createFromFormat('H:i:s', $attendance->clock_in)->format('H:i');
        $clockOutTime = Carbon::createFromFormat('H:i:s', $attendance->clock_out)->format('H:i');


        $this->actingAs($admin, 'admin');
        $response = $this->get('/admin/attendance/list');
        $response->assertSee($clockInTime);
        $response->assertSee($clockOutTime);

    }
}
