<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorrectionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attendance_id',
        'work_date',
        'corrected_clock_in',
        'corrected_clock_out',
        'reason',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function correctionBreakTimes()
    {
        return $this->hasMany(CorrectionBreakTime::class);
    }

    public function approval()
    {
        return $this->hasOne(CorrectionApproval::class);
    }
}
