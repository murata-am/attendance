<?php

namespace Tests\Feature;

use App\Models\CorrectionBreakTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Admin;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\CorrectionRequest;
use App\Models\CorrectionApproval;
use Carbon\Carbon;

class AdminAttendanceCorrectionTest extends TestCase
{
    use RefreshDatabase;

    // 承認待ちの修正申請が全て表示されている
    public function test_Admin_show_pending_correction_request()
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
                'work_date' => '2025-07-01',
                'clock_in' => '09:00:00',
                'clock_out' => '18:00:00',
            ]);

            BreakTime::create([
                'attendance_id' => $attendance->id,
                'break_start' => '12:00:00',
                'break_end' => '13:00:00',
            ]);

        $correction = CorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'work_date' => '2025-07-01',
            'corrected_clock_in' => '10:00',
            'corrected_clock_out' => '19:00',
            'reason' => 'テストの理由',
        ]);
        CorrectionApproval::create([
            'correction_request_id' => $correction->id,
            'status' => 'pending',
            'created_at' => Carbon::now()
        ]);


        $response = $this->actingAs($admin, 'admin')->get(route('admin.stamp_correction_request.list'));
        $response->assertStatus(200);
        $response->assertSee('承認待ち');
        $response->assertSee('テストユーザー');
        $response->assertSee('2025/07/01');
        $response->assertSee('テストの理由');
        $response->assertSee(now()->format('Y/m/d'));

    }

    // 承認済みの修正申請が全て表示されている
    public function test_Admin_show_approved_correction_request()
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
            'work_date' => '2025-07-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);

        $correction = CorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'work_date' => '2025-07-01',
            'corrected_clock_in' => '10:00',
            'corrected_clock_out' => '19:00',
            'reason' => 'テストの理由',
        ]);
        CorrectionApproval::create([
            'correction_request_id' => $correction->id,
            'status' => 'approved',
            'approved_by' => $admin->id,
            'approved_at'=> Carbon::now(),
            'created_at' => Carbon::now()
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.stamp_correction_request.list',['tab'=>'approved']));
        $response->assertStatus(200);
        $response->assertSee('承認済み');
        $response->assertSee('テストユーザー');
        $response->assertSee('2025/07/01');
        $response->assertSee('テストの理由');
        $response->assertSee(now()->format('Y/m/d'));
    }

    // 修正申請の詳細内容が正しく表示されている
    public function test_Admin_show_correction_request_detail()
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
            'work_date' => '2025-07-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);

        $correction = CorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'work_date' => '2025-07-01',
            'corrected_clock_in' => '10:00',
            'corrected_clock_out' => '19:00',
            'reason' => 'テストの理由',
        ]);
        CorrectionBreakTime::create([
            'correction_request_id' => $correction->id,
            'corrected_break_start' => '12:00:00',
            'corrected_break_end' => '13:00:00',
        ]);

        CorrectionApproval::create([
            'correction_request_id' => $correction->id,
            'status' => 'pending',
            'created_at' => Carbon::now()
        ]);


        $response = $this->actingAs($admin, 'admin')->get(route('admin.correction.approve.show', [$correction->id]));
        $response->assertStatus(200);
        $response->assertSee('テストユーザー');
        $response->assertSee('2025年');
        $response->assertSee('7月1日');
        $response->assertSee('10:00');
        $response->assertSee('19:00');
        $response->assertSee('12:00');
        $response->assertSee('13:00');
        $response->assertSee('テストの理由');
    }

    // 修正申請の承認処理が正しく表示されている
    public function test_Admin_correction_request_approval()
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
            'work_date' => '2025-07-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);

        $correction = CorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'work_date' => '2025-07-01',
            'corrected_clock_in' => '10:00',
            'corrected_clock_out' => '19:00',
            'reason' => 'テストの理由',
        ]);
        CorrectionBreakTime::create([
            'correction_request_id' => $correction->id,
            'corrected_break_start' => '12:00:00',
            'corrected_break_end' => '13:00:00',
        ]);

        CorrectionApproval::create([
            'correction_request_id' => $correction->id,
            'status' => 'pending',
            'created_at' => Carbon::now()
        ]);


        $response = $this->actingAs($admin, 'admin')->post(route('correction.approve.update', [$correction->id]));

        $response->assertRedirect(route('admin.correction.approve.show',[$correction->id]));

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'clock_in' => '10:00:00',
            'clock_out' => '19:00:00',
        ]);

        $this->assertDatabaseHas('correction_approvals', [
            'correction_request_id' => $correction->id,
            'status' => 'approved',
            'approved_by' => $admin->id,
            'approved_at' => Carbon::now(),
        ]);
    }
}
