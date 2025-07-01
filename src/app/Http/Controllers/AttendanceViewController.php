<?php

namespace App\Http\Controllers;

use App\Models\CorrectionRequest;
use App\Models\CorrectionBreakTime;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\CorrectionApproval;
use Carbon\Carbon;
use App\Http\Requests\AttendanceRequest;

class AttendanceViewController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $year = $request->query('year', now()->year);
        $month = $request->query('month', now()->month);

        $currentMonthCarbon = Carbon::createFromDate($year, $month, 1);

        $prevMonthCarbon = $currentMonthCarbon->copy()->subMonth();
        $nextMonthCarbon = $currentMonthCarbon->copy()->addMonth();

        $prevMonth = [
            'year' => $prevMonthCarbon->year,
            'month' => $prevMonthCarbon->month,
        ];
        $nextMonth = [
            'year' => $nextMonthCarbon->year,
            'month' => $nextMonthCarbon->month,
        ];


        $startOfMonth = $currentMonthCarbon->copy()->startOfMonth();
        $endOfMonth = $currentMonthCarbon->copy()->endOfMonth();

        $dates = [];
        $weekName = ['日', '月', '火', '水', '木', '金', '土'];

        for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()) {
            $weekday = $weekName[$date->dayOfWeek];
            $dates[] = [
                'carbon' => $date->copy(),
                'display' => $date->format("m/d") ."(" . $weekday . ") "
            ];
        }

        $rawAttendances = Attendance::where('user_id', $user->id)
            ->whereBetween('work_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->get()
            ->keyBy('work_date');

        $attendances = collect();

        foreach ($dates as $dateInfo) {
            $carbonDate = $dateInfo['carbon'];
            $dateKey = $carbonDate->toDateString();

            $attendance = $rawAttendances->get($dateKey);

            if ($attendance && $attendance->clock_in && $attendance->clock_out) {
                $clockIn = Carbon::parse($attendance->clock_in);
                $clockOut = Carbon::parse($attendance->clock_out);

                $attendance->clock_in_formatted = $clockIn->format('H:i');
                $attendance->clock_out_formatted = $clockOut->format('H:i');

                $totalBreak = 0;
                foreach ($attendance->breakTimes as $break) {
                    if ($break->break_start && $break->break_end) {
                        $startBreak = Carbon::parse($break->break_start);
                        $endBreak = Carbon::parse($break->break_end);
                        $totalBreak += $startBreak->diffInMinutes($endBreak);
                    }
                }

                $totalMinutes = $clockIn->diffInMinutes($clockOut) - $totalBreak;

                $attendance->total_break_minutes = $totalBreak;
                $attendance->total_work_minutes = $totalMinutes;

                $attendances[$dateKey] = $attendance;
            }
        }

        return view('attendance.list', [
            'dates' => $dates,
            'attendances' => $attendances,
            'year' => $year,
            'month' => $month,
            'prevMonth' => $prevMonth,
            'nextMonth' => $nextMonth,
        ]);
    }

    public function edit($id)
    {
        $attendance = Attendance::with([
            'breakTimes',
            'correctionRequest.correctionBreakTimes',
            'correctionRequest.approval'
        ])->findOrFail($id);

        $name = $attendance->user->name;
        $carbonDate = Carbon::parse($attendance->work_date);

        $work_year = $carbonDate->year . '年';
        $work_month_day = $carbonDate->format('n月j日');

        $status = optional(optional($attendance->correctionRequest)->approval)->status;

        $correction = $attendance->correctionRequest;

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

    public function store(AttendanceRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        $correctionRequest = new CorrectionRequest();
        $correctionRequest->attendance_id = $id;
        $correctionRequest->user_id = auth()->id();
        $correctionRequest->work_date = $attendance->work_date ?? Carbon::parse($attendance->clock_in)->toDateString();

        $clockIn = $request->input('clock_in');
        $clockOut = $request->input('clock_out');

        $correctionRequest->corrected_clock_in = $clockIn;
        $correctionRequest->corrected_clock_out = $clockOut;
        $correctionRequest->reason = $request->reason;

        $correctionRequest->save();

        $approval = new CorrectionApproval();
        $approval->correction_request_id = $correctionRequest->id;
        $approval->status = 'pending';
        $approval->save();

        $breakStarts = $request->input('break_start', []);
        $breakEnds = $request->input('break_end', []);

        foreach ($breakStarts as $index => $start) {
            $end = $breakEnds[$index] ?? null;
            if($start && $end){
                CorrectionBreakTime::create([
                    'correction_request_id' => $correctionRequest->id,
                    'corrected_break_start' => $start,
                    'corrected_break_end' => $end,
                ]);
            }
        }

        return redirect()->route('correction.request.list')->with('success', '修正申請を送信しました');
    }
}
