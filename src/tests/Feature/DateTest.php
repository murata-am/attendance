<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class DateTest extends TestCase
{
    use RefreshDatabase;

    // 現在の日時情報がUIと同じ形式で出力されている
    public function test_show_datetime()
    {
        $this->withoutMiddleware(\Illuminate\Auth\Middleware\EnsureEmailIsVerified::class);
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => Carbon::now(),
        ]);
        $this->actingAs($user);

        $now = Carbon::now();
        $weekdays = ['日', '月', '火', '水', '木', '金', '土'];

        $date = $now->format('Y年n月j日').'(' . $weekdays[$now->dayOfWeek] . ')';
        $time = $now->format('H:i');

        $response = $this->get('/attendance');
        $response->assertSee($date);
        $response->assertSee($time);
    }
}
