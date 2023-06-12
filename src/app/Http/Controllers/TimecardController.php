<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Attendance;
use Illuminate\Validation\ValidationException;

class TimecardController extends Controller
{
    public function startWork()
    {
        // 現在認証しているユーザーを取得
        $user = Auth::user();

        $old_attendance = Attendance::where('user_id', $user->id)
            ->orderBy('id', 'DESC')->first();
        if ($old_attendance) {
            $old_attendance_start = new Carbon($old_attendance->start_work);
            $old_attendance_day = $old_attendance_start->startOfDay();
        } else {
            $old_attendance_day = null;
        }

        $new_attendance_day = Carbon::today();

        if (session()->missing('startWork')) {
            session(['startWork' => 'exist']);
        }

        // 同日付に、既に出勤打刻している場合にエラーメッセージを出力
        if (($old_attendance_day == $new_attendance_day)) {
            throw ValidationException::withMessages(['start_work' => '既に出勤打刻がされています']);
            return redirect('/');
        }

        $start_work_time = Carbon::now();

        Attendance::create([
            'user_id' => $user->id,
            'date' => $start_work_time->format('Y-m-d'),
            'start_work' => $start_work_time->format('Y-m-d H:i:s'),
        ]);

        return redirect('/')->with('message', '出勤打刻が完了しました');
    }

    public function endWork()
    {
        $user = Auth::user();
        $attendance = Attendance::where('user_id', $user->id)
            ->orderBy('id', 'DESC')->first();

        if (session()->missing('endWork')) {
            session(['endWork' => 'exist']);
        }

        // 出勤打刻していないか、既に退勤打刻している場合にエラーメッセージを出力
        if (empty($attendance->start_work) || !empty($attendance->end_work)) {
            throw ValidationException::withMessages(['end_work' => '既に退勤の打刻がされているか、出勤打刻されていません']);
            return redirect('/');
        }

        $end_work_time = Carbon::now();


        $attendance->update([
            'end_work' => $end_work_time->format('Y-m-d H:i:s')
        ]);

        return redirect('/')->with('message', '退勤打刻が完了しました');
    }

}

