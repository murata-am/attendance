<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Vtiful\Kernel\Format;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\BreakTime;



class AttendanceController extends Controller
{
    public function index(){
        $attendance = Attendance::where('user_id', Auth::id())
            ->whereDate('work_date', today())
            ->first();
        $statusMap = [
            'work_off' => '勤務外',
            'working' => '勤務中',
            'break' => '休憩中',
            'finished_work' => '退勤済',
        ];
        $statusKey = $attendance->status ?? 'work_off';
        $status = $statusMap[$statusKey];


        $today = Carbon::now();
        $date = $today->isoFormat('YYYY年M月D日(ddd)');
        $time = $today->format('H:i');

        return view('attendance.index', compact('statusKey', 'status', 'date', 'time'));
    }

    public function storeClockIn(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        Attendance::firstOrCreate(
            [
                'user_id' => $user->id,
                'work_date' => $today,
                'clock_in' => Carbon::now()->format('H:i:s'),
                'status' => 'working',
            ]
        );
        return redirect('/attendance')->with('status', '出勤を記録しました。');
    }

    public function storeClockOut(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', $today)
            ->first();

        $attendance->update([
            'clock_out' => Carbon::now()->format('H:i:s'),
            'status' => 'finished_work',
        ]);

        return redirect('/attendance')->with('status', '退勤を記録しました。');
    }

    public function storeBreakStart(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', $today)
            ->first();

        BreakTime::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'break_start' => Carbon::now()->format('H:i:s'),
        ]);

        $attendance->update(['status' => 'break']);

        return redirect('/attendance')->with('status', '休憩を開始しました。');
    }

    public function storeBreakEnd(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', $today)
            ->first();
        $break = BreakTime::where('attendance_id', $attendance->id)
            ->whereNull('break_end')
            ->first();

        $break->update([
            'break_end' => Carbon::now()->format('H:i:s'),
        ]);

        $attendance->update(['status' => 'working']);

        return redirect('/attendance')->with('status', '休憩を終了しました。');
    }

}
