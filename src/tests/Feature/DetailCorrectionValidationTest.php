<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;

class DetailCorrectionValidationTest extends TestCase
{
    use RefreshDatabase;

    // 出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function test_validation__when_clock_in_after_clock_out()
    {
        $this->withoutMiddleware(\Illuminate\Auth\Middleware\EnsureEmailIsVerified::class);
        $user = User::factory()->create();

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

        $response = $this->actingAs($user)->post(route('attendance.store', $attendance->id), [
            'clock_in' => '18:00',
            'clock_out' => '09:00',
            'reason' => 'テストの理由',
        ]);

        $response->assertSessionHasErrors([
            'clock_in_out' => '出勤時間もしくは退勤時間が不適切な値です'
        ]);
    }

    // 休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function test_validation_when_clock_out_after_break_start()
    {
        $this->withoutMiddleware(\Illuminate\Auth\Middleware\EnsureEmailIsVerified::class);
        $user = User::factory()->create();

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

        $response = $this->actingAs($user)->post(route('attendance.store', $attendance->id), [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'break_start' => ['19:00'],
            'break_end' => ['20:00'],
            'reason' => 'テストの理由',
        ]);

        $response->assertSessionHasErrors([
            'break_time.0' => '休憩時間が勤務時間外です',
        ]);
    }

    // 休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function test_validation__when_clock_out_after_break_end()
    {
        $this->withoutMiddleware(\Illuminate\Auth\Middleware\EnsureEmailIsVerified::class);
        $user = User::factory()->create();

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

        $response = $this->actingAs($user)->post(route('attendance.store', $attendance->id), [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'break_start' => ['12:00'],
            'break_end' => ['19:00'],
            'reason' => 'テストの理由',
        ]);

        $response->assertSessionHasErrors([
            'break_time.0' => '休憩時間が勤務時間外です',
        ]);
    }

    // 備考欄が未入力の場合、エラーメッセージが表示される
    public function test_validation_when_reason_empty()
    {
        $this->withoutMiddleware(\Illuminate\Auth\Middleware\EnsureEmailIsVerified::class);
        $user = User::factory()->create();

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

        $response = $this->actingAs($user)->post(route('attendance.store', $attendance->id), [
            'clock_in' => '10:00',
            'clock_out' => '18:00',
            'reason' => '',
        ]);

        $errors = session('errors');
        $this->assertEquals('備考を記入してください', $errors->first('reason'));
    }
}
