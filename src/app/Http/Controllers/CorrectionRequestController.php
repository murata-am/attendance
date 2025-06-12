<?php

namespace App\Http\Controllers;


use App\Models\Attendance;
use App\Models\CorrectionRequest;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\CorrectionApproval;

class CorrectionRequestController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->input('tab', 'unapproved');

        $query = CorrectionRequest::with(['user', 'attendance', 'approval']);

        if (Auth::guard('web')->check()) {
            $query->where('user_id', Auth::id());
        }

        if ($tab === 'unapproved') {
            $query->whereHas('approval', fn($q) =>$q->where('status', 'pending'));
        } elseif ($tab === 'approved') {
            $query->whereHas('approval', fn($q) => $q->where('status', 'approved'));
        }

        return view('stamp_correction_request.list', [
            'correctionRequests' => $query->get(),
            'tab' => $tab,
        ]);
    }

    public function adminIndex(Request $request)
    {
        $tab = $request->input('tab', 'unapproved');

        $query = CorrectionRequest::with(['user', 'attendance', 'approval']);

        if ($tab === 'unapproved') {
            $query->whereHas('approval', function ($q) {
                $q->where('status', 'pending');
            });
        } elseif ($tab === 'approved') {
            $query->whereHas('approval', function ($q) {
                $q->where('status', 'approved');
            });
        }

        $correctionRequests = $query->get();

        return view('stamp_correction_request.list', [
            'correctionRequests' => $correctionRequests,
            'tab' => $tab,
        ]);
    }

    public function showApproved($id)
    {
        $correction = CorrectionRequest::with([
            'attendance',
            'user',
            'correctionBreakTimes',
            'approval'
        ])->findOrFail($id);

        if (optional($correction->approval)->status !== 'approved') {
            return redirect()->back()->with('error', 'この勤怠修正はまだ承認されていません。');
        }

        $name = $correction->user->name;
        $carbonDate = Carbon::parse($correction->attendance->work_date);

        $work_year = $carbonDate->year . '年';
        $work_month_day = $carbonDate->format('n月j日');

        return view('stamp_correction_request.show_approved', [
            'correction' => $correction,
            'name' => $name,
            'work_year' => $work_year,
            'work_month_day' => $work_month_day,
        ]);

    }

}
