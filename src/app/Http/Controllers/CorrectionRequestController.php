<?php

namespace App\Http\Controllers;


use App\Models\CorrectionRequest;
use Illuminate\Http\Request;
use function PHPUnit\Framework\returnArgument;

class CorrectionRequestController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->input('tab');
        $query = CorrectionRequest::with('user');

        if($tab === 'unapproved') {
            $query->whereHas('approval', function($q) {
                $q->where('status', 'pending');
            });
        } elseif ($tab === 'approved') {
            $query->whereHas('approval', function ($q) {
                $q->where('status', 'approved');
            });
        }

        return view('stamp_correction_request.list', [
            'tab' => $query,
        ]);
    }

}
