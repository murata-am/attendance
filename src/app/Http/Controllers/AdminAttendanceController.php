<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Carbon\Carbon;


class AdminAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $date = Carbon::parse($request->query('date', Carbon::today()->toDateString()));

        $prevDate = $date->copy()->subDay();
        $nextDate = $date->copy()->addDay();

        $attendances = Attendance::with(['user', 'breakTimes'])->whereDate('work_date', $date)->get();

        $records = $attendances->map(function ($attendance) {
            $clockIn = $attendance->clock_in ? Carbon::parse($attendance->clock_in) : null;
            $clockOut = $attendance->clock_out ? Carbon::parse($attendance->clock_out) : null;

            $totalBreakMinutes = 0;
            foreach ($attendance->BreakTimes as $break) {
                if ($break->break_start && $break->break_end) {
                    $start = Carbon::parse($break->break_start);
                    $end = Carbon::parse($break->break_end);
                    $totalBreakMinutes += $start->diffInMinutes($end);
                }
            }

            $totalWorkMinutes = null;
            if ($clockIn && $clockOut) {
                $totalWorkMinutes = $clockIn->diffInMinutes($clockOut) - $totalBreakMinutes;
            }

            return [
                'attendance_id' => $attendance->id,
                'user_name' => $attendance->user->name,
                'clock_in' => $clockIn ? $clockIn->format('H:i') : '',
                'clock_out' => $clockOut ? $clockOut->format('H:i') : '',
                'break_minutes' => $totalBreakMinutes,
                'work_minutes' => $totalWorkMinutes,
            ];
        });

        return view('admin.attendance.list', [
            'date' => $date->format('Y/m/d'),
            'date_formatted' => $date->format('Y年n月j日'),
            'prevDate' => $prevDate->toDateString(),
            'nextDate' => $nextDate->toDateString(),
            'records' => $records,
        ]);
    }

    public function show($id)
    {
        $attendance = Attendance::with([
            'user',
            'breakTimes',
            'correctionRequest.correctionBreakTimes',
            'CorrectionRequest.approval'
        ])->findOrFail($id);

        $name = $attendance->user->name;
        $carbonDate = Carbon::parse($attendance->work_date);

        $work_year = $carbonDate->year . '年';
        $work_month_day = $carbonDate->format('n月j日');

        $status = optional(optional($attendance->correctionRequest)->approval)->status;

        $correction = null;

        if ($status === 'pending' && $attendance->correctionRequest) {
            $correction = $attendance->correctionRequest;

            $attendance->clock_in = $correction->clock_in;
            $attendance->clock_out = $correction->clock_out;
            $attendance->breakTimes = $correction->correctionBreakTimes;
        }

        return view('attendance.detail', [
            'attendance' => $attendance,
            'name' => $name,
            'work_year' => $work_year,
            'work_month_day' => $work_month_day,
            'status' => $status,
            'correction' => $correction,
        ]);

    }

    public function update(AttendanceRequest $request, $id)
    {
        $data = $request->all();
        $attendance = Attendance::findOrFail($id);

        $attendance->update($data);

        return redirect()->route('admin.attendance.show', $id)->with('success', '勤怠情報を更新しました');

    }
}
