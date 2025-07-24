<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\CorrectionRequest;
use App\Models\CorrectionApproval;

class DetailCorrectionTest extends TestCase
{
    use RefreshDatabase;

    // 出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function test_validation__when_clock_in_after_clock_out()
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

        $response = $this->actingAs($user)->post(route('attendance.store', $attendance->id), [
            'clock_in' => '10:00',
            'clock_out' => '18:00',
            'reason' => '',
        ]);

        $errors = session('errors');
        $this->assertEquals('備考を記入してください', $errors->first('reason'));

    }

    // 修正申請処理が実行される
    public function test_admin_show_correction_request()
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
            'work_date' => '2025-07-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->actingAs($user)->post(route('attendance.store', [$attendance->id]), [
            'attendance_id' => $attendance->id,
            'clock_in' => '10:00',
            'clock_out' => '19:00',
            'break_start' => ['13:00'],
            'break_end' => ['14:00'],
            'reason' => '勤怠修正',
        ]);

        $correctionRequest = CorrectionRequest::where('attendance_id', $attendance->id)->first();

        CorrectionApproval::create([
            'correction_request_id' => $correctionRequest->id,
            'status' => 'pending',
            'created_at' => Carbon::now(),
        ]);

        $this->assertNotNull($correctionRequest, '修正申請が作成されていません');
        $response->assertRedirect('/stamp_correction_request/list');

        $admin = Admin::create([
            'name' => 'テスト管理者',
            'email' => 'admin@exaple.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.attendance.show', [$attendance->id]));

        //承認画面に表示されている
        $response->assertStatus(200);

        $response->assertSee('10:00');
        $response->assertSee('19:00');
        $response->assertSee('13:00');
        $response->assertSee('14:00');
        $response->assertSee('勤怠修正');


        $response = $this->actingAs($admin, 'admin')->get(route('admin.stamp_correction_request.list', ['tab' => 'unapproved']));

        // 申請一覧画面に表示されている
        $response->assertStatus(200);
        $response->assertSee('承認待ち');
        $response->assertSeeText('テストユーザー');
        $response->assertSeeText('2025/07/01');
        $response->assertSeeText('勤怠修正');
        $response->assertSeeText(Carbon::now()->format('Y/m/d'));
    }

    // 「承認待ち」にログインユーザーの修正申請が全て表示されている
    public function test_user_see_all_correction_request_pending_tab()
    {
        $this->withoutMiddleware(\Illuminate\Auth\Middleware\EnsureEmailIsVerified::class);
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($user, 'web');

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2025-07-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->post(route('attendance.store', [$attendance->id]), [
            'attendance_id' => $attendance->id,
            'clock_in' => '10:00',
            'clock_out' => '19:00',
            'break_start' => ['13:00'],
            'break_end' => ['14:00'],
            'reason' => '勤怠修正',
        ]);
        $response->assertRedirect('/stamp_correction_request/list');

        $correctionRequests = CorrectionRequest::where('user_id', $user->id)
            ->whereHas('approval', fn($q) => $q->where('status', 'pending'))
            ->get();
        $this->assertNotEmpty($correctionRequests);

        $listResponse = $this->get(route('correction.request.list', ['tab' => 'unapproved']));
        $listResponse->assertStatus(200);

        $listResponse->assertSeeText('承認待ち');
        $listResponse->assertSeeText('テストユーザー');
        $listResponse->assertSeeText('2025/07/01');
        $listResponse->assertSeeText('勤怠修正');
        $listResponse->assertSeeText(Carbon::now()->format('Y/m/d'));
    }


    // 「承認済み」に管理者が承認した修正申請が全て表示されている
    public function test_admin_see_all_approved_requests()
    {
        $this->withoutMiddleware(\Illuminate\Auth\Middleware\EnsureEmailIsVerified::class);

        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($user, 'web');

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2025-07-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->post(route('attendance.store', [$attendance->id]), [
            'attendance_id' => $attendance->id,
            'clock_in' => '10:00',
            'clock_out' => '19:00',
            'break_start' => ['13:00'],
            'break_end' => ['14:00'],
            'reason' => '勤怠修正',
        ]);
        $response->assertRedirect('/stamp_correction_request/list');

        $correctionRequest = CorrectionRequest::where('user_id', $user->id)->latest()->first();
        $this->assertNotNull($correctionRequest);

        $admin = Admin::create([
            'name' => 'テスト管理者',
            'email' => 'admin@exaple.com',
            'password' => bcrypt('password123'),
        ]);

        CorrectionApproval::create([
            'correction_request_id' => $correctionRequest->id,
            'status' => 'approved',
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);

        $response = $this->get(route('correction.request.list', ['tab' => 'approved']));
        $response->assertStatus(200);

        $response->assertSeeText('承認済み');
        $response->assertSeeText('テストユーザー');
        $response->assertSeeText('2025/07/01');
        $response->assertSeeText('勤怠修正');
        $response->assertSeeText(Carbon::now()->format('Y/m/d'));

    }

    // 各申請の「詳細」を押すと申請詳細画面に遷移する
    public function test_user_see_all_correction_request_detail()
    {
        $this->withoutMiddleware(\Illuminate\Auth\Middleware\EnsureEmailIsVerified::class);
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($user, 'web');

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2025-07-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->post(route('attendance.store', [$attendance->id]), [
            'attendance_id' => $attendance->id,
            'clock_in' => '10:00',
            'clock_out' => '19:00',
            'break_start' => ['13:00'],
            'break_end' => ['14:00'],
            'reason' => '勤怠修正',
        ]);
        $response->assertRedirect('/stamp_correction_request/list');

        // 修正申請が保存されているか確認
        $correctionRequests = CorrectionRequest::where('user_id', $user->id)
            ->whereHas('approval', fn($q) => $q->where('status', 'pending'))
            ->get();
        $this->assertNotEmpty($correctionRequests);

        $listResponse = $this->get(route('correction.request.list', ['tab' => 'unapproved']));
        $listResponse->assertStatus(200);

        $correctionRequest = $correctionRequests->first();

        $response = $this->get(route('attendance.edit', [$attendance->id, $correctionRequest->id]));
    }
}
