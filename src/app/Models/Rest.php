<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Rest extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'date',
        'start_rest',
        'end_rest',
    ];

    /**
     * Attendances関連付け
     * 1対多
     */
    public function Attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

}
