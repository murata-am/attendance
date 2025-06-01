<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorrectionBreakTime extends Model
{
    use HasFactory;

    protected $fillable = [
        'correction_request_id',
        'corrected_break_start',
        'corrected_break_end',
    ];

    public function correctionRequest()
    {
        return $this->belongsTo(CorrectionRequest::class);
    }
}
