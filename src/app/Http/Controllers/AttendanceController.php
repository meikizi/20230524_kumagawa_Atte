<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Pagination\LengthAwarePaginator;

class AttendanceController extends Controller
{
    /**
     * 日付別勤怠情報一覧
     */
    public function DailyAttendance(Request $request)
    {
        $dates = Attendance::groupBy('date')->orderByDesc('date')->pluck('date');
        $dates_count = $dates->count();

        if ($request->date) {
            $date = $request->date;
            // コレクション$datesで値がリクエストで送られた日付と一致するときのキーを取得
            $index = $dates->filter(fn($value) => $value == $date)->keys()->first();
        } else {
            $now = new Carbon();
            $date = $now->format('Y-m-d');
            $index = 0;
        }

        // ヘルパー関数 searchAttendance()、searchRest()
        $attendance_lists = searchAttendance($date);
        $rest_lists = searchRest($date);

        if ($attendance_lists) {
            $per_page = 5;
            // ヘルパー関数 connectCollection()
            // 検索対象日の attendance_id、名前、出勤時間、退勤時間、勤務時間、休憩時間、実労働時間のリストをコレクション形式で格納
            $total_lists = connectCollection($attendance_lists, $rest_lists);
            // 結果セットCollectionに対してページネーション
            $total_lists = new LengthAwarePaginator(
                $total_lists->forPage($request->page, $per_page),
                count($total_lists),
                $per_page,
                $request->page,
                //  検索結果ページのパラメーターを引き継ぐ
                array(
                    'path' => $request->url('/attendance'),
                )
            );

            $param = [
                'items' => $total_lists,
                'dates' => $dates,
                'dates_count' => $dates_count,
                'i' => $index,
            ];
        } else {
            $param = [
                'items' => null,
                'dates' => $dates,
                'dates_count' => $dates_count,
                'i' => $index,
            ];
        }

        return view('attendance')->with($param);
    }

    /**
     * ユーザー一覧
     */
    public function getUserList()
    {
        $user_names = User::select('name')->Paginate(5);
        return view('user_list', compact('user_names'));
    }

    /**
     * ユーザー一覧
     */
    public function postUserList(Request $request)
    {
        $name = $request->name;
        if ($name === null) {
            return view('user_list');
        }
        // リダイレクトする時にセッションにnameの値を入れる
        return redirect()->route('user_attendance')->with(compact('name'));
    }

    /**
     * ユーザー別勤怠情報一覧
     */
    public function getUserAttendance(Request $request)
    {
        $name = session('name');
        // ページネーションした際にも$nameの値を保持
        $request->session()->flash('name', $name);

        $user_atte_list = searchAttendanceUser($name);
        $user_rest_list = searchRestUser($name);

        if ($user_atte_list) {
            $per_page = 5;
            // ヘルパー関数 connectCollection()
            // ユーザー毎の 出勤日、出勤時間、退勤時間、勤務時間、休憩時間、実労働時間のリストをコレクション形式で格納
            $total_lists = connectCollection($user_atte_list, $user_rest_list);
            $total_lists = new LengthAwarePaginator(
                $total_lists->forPage($request->page, $per_page),
                count($total_lists),
                $per_page,
                $request->page,
                array('path' => $request->url('/user_atte_list')),
            );
            $param = [
                'items' => $total_lists,
                'name' => $name,
            ];
        } else {
            $param = [
                'items' => null,
                'name' => null,
            ];
        }
        return view('user_attendance_list', $param);
    }

    /**
     * ログイン中のユーザーの勤怠表
     */
    public function getUserAtte(Request $request)
    {
        $user = Auth::user();
        if ($request->date) {
            $date = $request->date;
            $user_atte_list = searchAtteUser($date);
            $user_rest_list = searchBreakUser($date);
        } else {
            $user_atte_list = searchAtteUser();
            $user_rest_list = searchBreakUser();
        }

        if ($user_atte_list) {
            $per_page = 5;
            $total_lists = connectCollection($user_atte_list, $user_rest_list);
            $total_lists = new LengthAwarePaginator(
                $total_lists->forPage($request->page, $per_page),
                count($total_lists),
                $per_page,
                $request->page,
                array('path' => $request->url()),
            );
            $param = [
                'items' => $total_lists,
                'name' => $user->name,
            ];
        } else {
            $param = [
                'items' => null,
                'name' => null,
            ];
        }
        return view('user_atte_list', $param);
    }
}
