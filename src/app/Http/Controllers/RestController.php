<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Rest;
use Illuminate\Validation\ValidationException;

class RestController extends Controller
{
    public function startRest()
    {
        $user_id = Auth::id();

        $rest = Rest::where('user_id', $user_id)
            ->orderBy('id', 'DESC')->first();
        if (!empty($rest->start_rest) && $rest->end_rest === null) {
            throw ValidationException::withMessages(['start_rest' => '休憩終了打刻されていません']);
            return redirect('/');
        }
        $start_rest_time = Carbon::now();
        Rest::create([
            'user_id' => $user_id,
            'date' => $start_rest_time->format('Y-m-d'),
            'start_rest' => $start_rest_time->format('Y-m-d H:i:s'),
        ]);
        return redirect('/')->with('message', '休憩開始打刻が完了しました');
    }

    public function endRest()
    {
        $user_id = Auth::id();
        $rest = Rest::where('user_id', $user_id)
            ->orderBy('id', 'DESC')->first();

        if (empty($rest->start_rest) || !empty($rest->end_rest)) {
            throw ValidationException::withMessages(['end_rest' => '既に休憩終了打刻がされているか、休憩開始打刻されていません']);
            return redirect('/');
        }
        $end_rest_time = Carbon::now();
        $rest->update([
            'end_rest' => $end_rest_time->format('Y-m-d H:i:s')
        ]);
        return redirect('/')->with('message', '休憩終了打刻が完了しました');
    }
}
