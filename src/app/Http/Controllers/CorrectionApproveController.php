<?php

namespace App\Http\Controllers;

use App\Models\CorrectionRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CorrectionApproveController extends Controller
{
    public function show(CorrectionRequest $attendance_correct_request)
    {
        $attendance_correct_request->load([
            'attendance',
            'user',
            'correctionBreakTimes',
            'approval',
        ]);

        $name = $attendance_correct_request->attendance->user->name;
        $carbonDate = Carbon::parse($attendance_correct_request->work_date);

        $work_year = $carbonDate->year . '年';
        $work_month_day = $carbonDate->format('n月j日');

        $status = optional($attendance_correct_request->approval)->status;

        return view('stamp_correction_request.approve', [
            'correction' => $attendance_correct_request,
            'name' => $name,
            'work_year' => $work_year,
            'work_month_day' => $work_month_day,
            'status' => $status,
        ]);

    }

    public function approve(CorrectionRequest $attendance_correct_request)
    {
        DB::transaction(function () use ($attendance_correct_request) {
            $attendance = $attendance_correct_request->attendance;
            $attendance->clock_in = $attendance_correct_request->corrected_clock_in;
            $attendance->clock_out = $attendance_correct_request->corrected_clock_out;
            $attendance->reason = $attendance_correct_request->reason;
            $attendance->save();

            $attendance->breakTimes()->delete();

            foreach ($attendance_correct_request->correctionBreakTimes as $correctedBreak) {
                $attendance->breakTimes()->create([
                    'break_start' => $correctedBreak->corrected_break_start,
                    'break_end' => $correctedBreak->corrected_break_end,
                ]);
            }

            $attendance->load('breakTimes');

            $attendance_correct_request->approval->status = 'approved';
            $attendance_correct_request->approval->approved_by = Auth::id();
            $attendance_correct_request->approval->approved_at = Carbon::now();
            $attendance_correct_request->approval->save();
        });


        return redirect()->route('admin.correction.approve.show', ['attendance_correct_request' => $attendance_correct_request->id])
        ->with('success', '修正申請を承認しました。');

    }
}
