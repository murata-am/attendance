<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::all();

        foreach ($users as $user) {
            for ($day = 1; $day <= 30; $day++) {

                $date = Carbon::create(2025, 6, $day);
                if ($date->isWeekend()) {
                    continue;
                }

                $attendance = Attendance::create([
                    'user_id' => $user->id,
                    'work_date' => $date->format('Y-m-d'),
                    'clock_in' => $date->copy()->setTime(9, 0, 0),
                    'clock_out' => $date->copy()->setTime(18, 0, 0),
                    'status' => 'finished_work',
                    'reason' => null,
                ]);

                BreakTime::create([
                    'attendance_id' => $attendance->id,
                    'break_start' => $date->copy()->setTime(12, 0, 0),
                    'break_end' => $date->copy()->setTime(13, 0, 0),
                ]);
            }
        }
    }
}
