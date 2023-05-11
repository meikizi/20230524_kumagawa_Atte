<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Validation\ValidationException;

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
        } else {
            $old_attendance_day = null;
        }

        $new_attendance_day = Carbon::today();

        /**
         * 同日付に、既に出勤打刻している場合エラーを吐き出す。
         */
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

    public function punchOut(Request $request)
    {
        $user = Auth::user();
        $attendance = Attendance::where('user_id', $user->id)
            ->orderBy('id', 'DESC')->first();

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

    public function showTable(Request $request)
    {
        if ($request->date) {
            $date = $request->date;
        } else {
            $now = new Carbon();
            $date = $now->format('Y-m-d');
        }
        // dd($date);
        $attendance_lists = searchAttendance($date);
        $rest_lists = searchRest($date);
        // dd($attendance_lists);
        // dd($rest_lists);

        if ($attendance_lists) {
            $per_page = 5;
            $total_lists = connectCollection($attendance_lists, $rest_lists);
            // dd($total_lists);
            // 結果セットCollectionに対してページネーション
            $total_lists = new LengthAwarePaginator(
                $total_lists->forPage($request->page, $per_page),
                count($total_lists),
                $per_page,
                $request->page,
                //  検索結果ページなどパラメーターを引き継ぐ
                array('path' => $request->url('/attendance'))
            );
            // dd($total_lists);
            $param = [
                'items' => $total_lists,
                'date' => $date,
            ];
            dd($param);
        } else {
            $param = [
                'items' => null,
                'date' => $date,
            ];
        }

        // 検索状態を引き継いでページング
        return view('attendance', $param, [
            'paginate_params' => [
                'date' => $date,
            ]
        ]);
    }

    public function showAttendance(Request $request)
    {
        $attendances = User::leftJoin('attendances', 'users.id', '=', 'attendances.user_id')
            ->leftJoin('rests', 'users.id', '=', 'rests.user_id')
            ->paginate(5);

        // dd($attendances);
        return view('attendance', compact('attendances'));
    }
}
