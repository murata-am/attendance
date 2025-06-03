<?php

namespace App\Http\Controllers;


use App\Models\Attendance;
use App\Models\CorrectionRequest;
use Illuminate\Http\Request;
use App\Models\CorrectionApproval;

class CorrectionRequestController extends Controller
{
    public function index(Request $request)
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

}
