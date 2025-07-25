<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;

class StatusConfirmationTest extends TestCase
{
    use RefreshDatabase;

    // 勤務外の時、勤怠ステータスに正しく表示される
    public function test_show_status_work_off()
    {
        $this->withoutMiddleware(\Illuminate\Auth\Middleware\EnsureEmailIsVerified::class);
        $user = User::factory()->create();

        $this->actingAs($user);

        Attendance::create([
            'user_id' => $user->id,
            'status' => 'work_off',
            'work_date' => now()->toDateString(),
        ]);

        $response = $this->get("/attendance");
        $response->assertSee('勤務外');
    }

    // 出勤中の時、勤怠ステータスに正しく表示される
    public function test_show_status_working()
    {
        $this->withoutMiddleware(\Illuminate\Auth\Middleware\EnsureEmailIsVerified::class);
        $user = User::factory()->create();

        $this->actingAs($user);

        Attendance::create([
            'user_id' => $user->id,
            'status' => 'working',
            'work_date' => now()->toDateString(),
        ]);

        $response = $this->get("/attendance");

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'status' => 'working',
        ]);
        $response->assertSee('出勤中');
    }

    // 休憩中の時、勤怠ステータスに正しく表示される
    public function test_show_status_break_start()
    {
        $this->withoutMiddleware(\Illuminate\Auth\Middleware\EnsureEmailIsVerified::class);
        $user = User::factory()->create();

        $this->actingAs($user);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'status' => 'break',
        ]);

        $response = $this->get("/attendance");
        $response->assertSee('休憩中');
    }

    // 退勤済の時、勤怠ステータスに正しく表示される
    public function test_show_status_finished_work()
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
        $response->assertSee('退勤済');
    }
}
