<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Response;


class StaffController extends Controller
{
    public function index()
    {
        $staff = User::select('id', 'name', 'email')
            ->get();

        return view('admin/staff/list', compact('staff'));
    }

    public function show($userId, Request $request)
    {
        $user = User::findOrFail($userId);

        $year = $request->query('year', now()->year);
        $month = $request->query('month', now()->month);

        $currentMonthCarbon = Carbon::createFromDate($year, $month, 1);

        $prevMonth = [
            'year' => $currentMonthCarbon->copy()->subMonth()->year,
            'month' => $currentMonthCarbon->copy()->subMonth()->month,
        ];
        $nextMonth = [
            'year' => $currentMonthCarbon->copy()->addMonth()->year,
            'month' => $currentMonthCarbon->copy()->addMonth()->month,
        ];

        $startOfMonth = $currentMonthCarbon->copy()->startOfMonth();
        $endOfMonth = $currentMonthCarbon->copy()->endOfMonth();

        $weekName = ['日', '月', '火', '水', '木', '金', '土'];
        $dates = [];

        for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()) {
            $weekday = $weekName[$date->dayOfWeek];
            $dates[] = [
                'carbon' => $date->copy(),
                'display' => $date->format("m/d") . "(" . $weekday . ") "
            ];
        }

        $rawAttendances = Attendance::with('breakTimes')
            ->where('user_id', $userId)
            ->whereBetween('work_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->whereBetween('work_date', [$startOfMonth, $endOfMonth])
            ->get()
            ->keyBy('work_date');

        $attendances = collect();

        foreach ($dates as $dateInfo) {
            $dateKey = $dateInfo['carbon']->toDateString();
            $attendance = $rawAttendances->get($dateKey);

            if ($attendance) {
                if ($attendance->clock_in && $attendance->clock_out) {
                    $clockIn = Carbon::parse($attendance->clock_in);
                    $clockOut = Carbon::parse($attendance->clock_out);

                    $attendance->clock_in_formatted = $clockIn->format('H:i');
                    $attendance->clock_out_formatted = $clockOut->format('H:i');

                    $totalBreak = 0;
                    foreach ($attendance->breakTimes as $break) {
                        if ($break->break_start && $break->break_end) {
                            $start = Carbon::parse($break->break_start);
                            $end = Carbon::parse($break->break_end);
                            $totalBreak += $start->diffInMinutes($end);
                        }
                    }

                    $attendance->total_break_minutes = $totalBreak;
                    $attendance->total_work_minutes = $clockIn->diffInMinutes($clockOut) - $totalBreak;
                }
                    $attendances[$dateKey] = $attendance;
            } else {
                    $attendances[$dateKey] = null;
            }
        }

        return view('admin.attendance.staff.list', [
            'user' => $user,
            'year' => $year,
            'month' => $month,
            'dates' => $dates,
            'attendances' => $attendances,
            'prevMonth' => $prevMonth,
            'nextMonth' => $nextMonth,
        ]);
    }

    public function export(Request $request)
    {
        $userId = $request->input('userId');
        $year = $request->input('year');
        $month = $request->input('month');

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $daysInMonth = [];
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $daysInMonth[$date->format('Y-m-d')] = [
                'date' => $date->format('Y/m/d'),
                'clock_in' => '00:00',
                'clock_out' => '00:00',
                'break' => '00:00',
                'work' => '00:00',
            ];
        }

        $attendances = Attendance::where('user_id', $userId)
            ->whereBetween('work_date', [$startDate, $endDate])
            ->with('breakTimes')
            ->get();

        foreach ($attendances as $attendance) {
            $dateKey = Carbon::parse($attendance->work_date)->format('Y-m-d');
            $clockIn = $attendance->clock_in ? Carbon::parse($attendance->clock_in) : null;
            $clockOut = $attendance->clock_out ? Carbon::parse($attendance->clock_out) : null;

            $totalBreakMinutes = 0;
            foreach ($attendance->breakTimes as $break) {
                if ($break->break_start && $break->break_end) {
                    $start = Carbon::parse($break->break_start);
                    $end = Carbon::parse($break->break_end);
                    $totalBreakMinutes += $start->diffInMinutes($end);
                }
            }

            $totalWorkMinutes = 0;
            if ($clockIn && $clockOut) {
                $totalWorkMinutes = $clockIn->diffInMinutes($clockOut) - $totalBreakMinutes;
            }

            $daysInMonth[$dateKey] = [
                'date' => Carbon::parse($attendance->work_date)->format('Y/m/d'),
                'clock_in' => $clockIn ? $clockIn->format('H:i') : '00:00',
                'clock_out' => $clockOut ? $clockOut->format('H:i') : '00:00',
                'break' => gmdate('H:i', $totalBreakMinutes * 60),
                'work' => gmdate('H:i', $totalWorkMinutes * 60),
            ];
        }

        $csvHeader = ['日付', '出勤', '退勤', '休憩時間', '勤務時間'];
        $csvData = array_values($daysInMonth); // 順番を保ったまま出力

        $filename = "{$year}_{$month}_勤怠カレンダー形式.csv";

        $callback = function () use ($csvHeader, $csvData) {
            $file = fopen('php://output', 'w');
            mb_convert_variables('SJIS-win', 'UTF-8', $csvHeader);
            fputcsv($file, $csvHeader);

            foreach ($csvData as $row) {
                mb_convert_variables('SJIS-win', 'UTF-8', $row);
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, [
            "Content-Type" => "text/csv",
            "Content-Disposition" => "attachment; filename={$filename}",
        ]);
    }
}