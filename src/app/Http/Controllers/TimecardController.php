<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\Rest;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;

class TimecardController extends Controller
{
    public function punchIn()
    {
        /**
         * 現在認証しているユーザーを取得
         */
        $user = Auth::user();

        /**
         * 打刻は1日一回までにしたい
         */
        $old_attendance = Attendance::where('user_id', $user->id)
            ->orderBy('id', 'DESC')->first();
        if ($old_attendance) {
            $old_attendance_punchIn = new Carbon($old_attendance->start_work);
            $old_attendance_day = $old_attendance_punchIn->startOfDay();
        }

        $new_attendance_day = Carbon::today();

        /**
         * 日付を比較する。同日付の出勤打刻で、かつ直前のTimestampの退勤打刻がされていない場合エラーを吐き出す。
         */
        if (($old_attendance_day == $new_attendance_day)) {
            return redirect('/')
                ->route('timecard');
        }

        $start_work_time = Carbon::now();
        Attendance::create([
            'user_id' => $user->id,
            'date' => $start_work_time->format('Y-m-d'),
            'start_work' => $start_work_time->format('H:i:s'),
        ]);

        return redirect('/')
            ->route('timecard');
    }

    public function punchOut()
    {
        $user = Auth::user();
        $attendance = Attendance::where('user_id', $user->id)
            ->orderBy('id', 'DESC')->first();

        if (!empty($attendance->end_work)) {
            return redirect('/')
                ->route('timecard');
        }
        $end_work_time = Carbon::now();
        $attendance->update([
            'end_work' => $end_work_time->format('H:i:s')
        ]);

        return redirect('/')
            ->route('timecard');
    }

    public function showTable(Request $request)
    {
        $dates = Attendance::DateSearch($request->date)
            ->paginate(1);

        $attendances = Attendance::leftJoin('users', 'attendances.user_id', '=', 'users.id')
            ->leftJoin('rests', 'rests.attendance_id', '=', 'attendances.id')
            ->DateSearch($request->date)
            ->paginate(5);

        // $total_work = DB::select(DB::raw('select start_work, end_work, end_work - start_work from attendances'));

        return view('attendance', compact('dates', 'attendances'));
    }
}
