<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use Illuminate\Support\Facades\Hash;

class ClockInTest extends TestCase
{
    use RefreshDatabase;


    // 出勤ボタンが正しく機能する
    public function test_clock_in()
    {
        $this->withoutMiddleware(\Illuminate\Auth\Middleware\EnsureEmailIsVerified::class);
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->get('/attendance');
        $response->assertSee('出勤');

        $this->post('/attendance/clockIn');

        $response = $this->get('/attendance');
        $response->assertSee('出勤中');

        $attendance = Attendance::where('user_id', $user->id)->where('work_date', now()->toDateString())->first();
        $this->assertEquals('working', $attendance->status);
    }


    // 出勤は1日1回のみできる
    public function test_clock_in_button_not_visible_after_finish_work()
    {
        $this->withoutMiddleware(\Illuminate\Auth\Middleware\EnsureEmailIsVerified::class);
        $user = User::factory()->create();

        $this->actingAs($user);
        Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now()->subMinutes(10)->format('H:i:s'),
            'status' => 'finished_work',
        ]);

        $response = $this->get("/attendance");
        $response->assertDontSee('<button type="submit" class="btn-black" name="status" value="working">出勤</button>');
    }

    // 出勤時刻が管理画面で確認できる
    public function test_clock_in_admin_show()
    {
        $this->withoutMiddleware(\Illuminate\Auth\Middleware\EnsureEmailIsVerified::class);

        $user = User::factory()->create();
        $admin = Admin::factory()->create();

        $this->actingAs($user);
        $this->post('/attendance/clockIn');

        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', now()->toDateString())
            ->first();

        $clockInTime = \Carbon\Carbon::createFromFormat('H:i:s', $attendance->clock_in)->format('H:i');

        $this->actingAs($admin, 'admin');
        $response = $this->get('/admin/attendance/list');
        $response->assertSee($clockInTime);

    }


}
