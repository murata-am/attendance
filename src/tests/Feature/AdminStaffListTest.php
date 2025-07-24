<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Admin;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;

class AdminStaffListTest extends TestCase
{
    use RefreshDatabase;

    // 管理者ユーザーが全一般ユーザーの「氏名」「メールアドレス」を確認できる
    public function test_admin_show_staff_list()
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
        ]);

        $user2 = User::create([
            'name' => 'テストユーザー2',
            'email' => 'test2@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
        ]);

        $admin = Admin::create([
            'name' => 'テスト管理者',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('staff.list'));

        $response->assertStatus(200);
        $response->assertSee('テストユーザー');
        $response->assertSee('test@example.com');
        $response->assertSee('テストユーザー2');
        $response->assertSee('test2@example');
    }

    // 管理者ユーザーが一般ユーザーの勤怠一覧を確認できる
    public function test_admin_show_staff_list_this_month()
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

        foreach (['2025-06-25', '2025-07-01', '2025-07-10', '2024-08-01'] as $date) {
            $attendance = Attendance::create([
                'user_id' => $user->id,
                'work_date' => $date,
                'clock_in' => '09:00:00',
                'clock_out' => '18:00:00',
            ]);

            BreakTime::create([
                'attendance_id' => $attendance->id,
                'break_start' => '12:00:00',
                'break_end' => '13:00:00',
            ]);
        }

        $response = $this->actingAs($admin, 'admin')->get(route('attendance.staff.list',[
            'userId'=>$user->id,
            'year' => 2025,
            'month' => 7
        ]));

        $response->assertStatus(200);
        $response->assertSeeText('09:00');
        $response->assertSeeText('18:00');
        $response->assertSeeText('1:00');
        $response->assertSeeText('8:00');
    }

    // 管理者が「前月」を押すと表示月の前月の情報が表示される
    public function test_admin_show_staff_list_prev_month()
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

        foreach (['2025-06-25', '2025-07-01', '2025-07-10', '2024-08-01'] as $date) {
            $attendance = Attendance::create([
                'user_id' => $user->id,
                'work_date' => $date,
                'clock_in' => '09:00:00',
                'clock_out' => '18:00:00',
            ]);

            BreakTime::create([
                'attendance_id' => $attendance->id,
                'break_start' => '12:00:00',
                'break_end' => '13:00:00',
            ]);
        }

        $response = $this->actingAs($admin, 'admin')->get(route('attendance.staff.list', [
            'userId' => $user->id,
            'year' => 2025,
            'month' => 6
        ]));

        $response->assertStatus(200);
        $response->assertSeeText('09:00');
        $response->assertSeeText('18:00');
        $response->assertSeeText('1:00');
        $response->assertSeeText('8:00');
    }

    // 管理者が「翌月」を押すと表示月の前月の情報が表示される
    public function test_admin_show_staff_list_next_month()
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

        foreach (['2025-06-25', '2025-07-01', '2025-07-10', '2025-08-01'] as $date) {
            $attendance = Attendance::create([
                'user_id' => $user->id,
                'work_date' => $date,
                'clock_in' => '09:00:00',
                'clock_out' => '18:00:00',
            ]);

            BreakTime::create([
                'attendance_id' => $attendance->id,
                'break_start' => '12:00:00',
                'break_end' => '13:00:00',
            ]);
        }

        $response = $this->actingAs($admin, 'admin')->get(route('attendance.staff.list', [
            'userId' => $user->id,
            'year' => 2025,
            'month' => 8
        ]));

        $response->assertStatus(200);
        $response->assertSeeText('09:00');
        $response->assertSeeText('18:00');
        $response->assertSeeText('1:00');
        $response->assertSeeText('8:00');
    }



    // 管理者が「詳細」を押すとその日の勤怠詳細画面に遷移する
    public function test_admin_show_staff_list_detail()
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

        $response = $this->actingAs($admin, 'admin')->get(route('admin.attendance.show', [$attendance->id]));

        $response->assertStatus(200);
        $response->assertSeeText('2025年');
        $response->assertSeeText('7月1日');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }



}
