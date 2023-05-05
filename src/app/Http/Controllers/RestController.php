<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Attendance;

class RestController extends Controller
{
    public function startRest()
    {
        $start_rest_time = Carbon::now();
        Attendance::create([
            'start_rest' => $start_rest_time->format('H:i:s'),
            'user_id' => Auth::id()
        ]);
        return redirect('/')
            ->route('timecard');
    }

    public function endRest()
    {
        $end_rest_time = Carbon::now();
        Attendance::create([
            'end_rest' => $end_rest_time->format('H:i:s'),
            'user_id' => Auth::id()
        ]);
        return redirect('/')
            ->route('timecard');
    }
}
