<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorrectionApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'correction_request_id',
        'status',
        'approved_by',
        'approved_at',
    ];

    public function correctionRequest()
    {
        return $this->belongsTo(CorrectionRequest::class);
    }
}
