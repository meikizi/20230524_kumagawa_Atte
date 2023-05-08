<?php

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;

if (!function_exists('searchAttendance')) {
    /**
     * attendanceモデルから、コレクション作成
     */
    function searchAttendance(string $date)
    {
        // 検索対象日の全レコードを取得
        $daily_attendance = Attendance::where('date', $date)->get();
        $data_set = collect([]);
        // $user_idを元に個人ごとのレコードを取得。
        if ($daily_attendance->isNotEmpty()) {
            $id_list_att = $daily_attendance->unique('user_id')->pluck('user_id');
            for ($i = 0; $i < count($id_list_att); $i++) {
                $individual_attendance = $daily_attendance
                    ->where('user_id', $id_list_att[$i]);
                $start_work = new Carbon($individual_attendance
                    ->whereNotNull('start_work')
                    ->pluck('start_work')
                    ->first());
                $get_time_value = $individual_attendance
                    ->whereNotNull('end_work')
                    ->pluck('end_work')
                    ->first();
                $end_work = new Carbon($get_time_value);
                // 勤務時間の算出
                $work_time = $start_work->diff($end_work);
                $name = User::where('id', $id_list_att[$i])->value('name');
                //勤務中の場合は勤務終了を---、勤務時間の後に（勤務中）と表示する。
                if (isset($get_time_value)) {
                    $end_work = $end_work->format('H:i:s');
                    $work_time = $work_time->format('%H:%I:%S');
                } else {
                    $end_work = '---';
                    $work_time =  $work_time->format('%H:%I:%S') . '（勤務中）';
                }
                $individual_data  = collect([
                    'id_list_att' => $id_list_att[$i],
                    'name' => $name,
                    'start_work' => $start_work->format('H:i:s'),
                    'end_work' => $end_work, 'work_time' => $work_time
                ]);
                $data_set[$i] = $individual_data;
            }
        } else {
            $data_set = null;
        }
        return $data_set;
    }
}

if (!function_exists('searchRest')) {
    /**
     * restモデルから、コレクション作成
     */
    function searchRest(string $date)
    {
        $daily_rest = Rest::where('date', $date)->get();
        $data_set = collect([]);
        if ($daily_rest->isNotEmpty()) {
            $id_list_rest = $daily_rest->unique('attendance_id')->pluck('attendance_id');
            for ($i = 0; $i < count($id_list_rest); $i++) {
                $individual_rest = $daily_rest->where('attendance_id', $id_list_rest[$i]);
                $total_rest_secondes = 0;
                for ($j = 0; $j < count($individual_rest); $j++) {
                    $start_rest = new Carbon($individual_rest->whereNotNull('start_rest')->pluck('start_rest')->get($j));
                    $end_rest = new Carbon($individual_rest->whereNotNull('end_rest')->pluck('end_rest')->get($j));
                    // unixタイムスタンプを使って休憩時間を求める
                    $start_unix_time = $start_rest->getTimestamp();
                    $end_unix_time = $end_rest->getTimestamp();
                    $rest_time = $end_unix_time - $start_unix_time;
                    $total_rest_secondes += $rest_time;
                }
                // 型キャスト
                $display_seconds = (int)$total_rest_secondes % 60;
                $display_minutes = floor($total_rest_secondes / 60);
                $display_Hours = floor($display_minutes / 60);
                // $display_Hours時と$display_minutes分と$display_seconds秒の日付間隔をオブジェクトで保存
                $total_rest = new DateInterval("PT{$display_Hours}H{$display_minutes}M{$display_seconds}S");
                $individual_data = collect([
                    'id_list_rest' => $id_list_rest[$i],
                    'rest_time' => $total_rest->format('%H:%I:%S')
                ]);
                $data_set[$i] = $individual_data;
            }
        }
        return $data_set;
    }
}

if (!function_exists('connectCollection')) {
    /**
     * 別々のモデルから引き出されたCollectionをつなげる
     */
    function connectCollection(object $collectionA, object $collectionB)
    {
        $id_list_att = $collectionA->pluck('id_list_att');
        // dd($id_list_att);
        $id_list_rest = $collectionB->pluck('id_list_rest');
        // dd($id_list_rest);
        $total_collection = collect([]);
        for ($i = 0; $i < count($id_list_att); $i++) {
            $id_rest_exist = $id_list_rest->search($id_list_att[$i]);
            // dd($id_rest_exist);
            if (!($id_rest_exist === false)) {
                $rest_time_val = $collectionB
                    ->where('id_list_rest', $id_list_att[$i])
                    ->first()
                    ->get('rest_time');
                    dd($rest_time_val);
                $total_collection[$i] = $collectionA[$i]
                    ->put('rest_time', $rest_time_val);
            } else {
                $total_collection[$i] = $collectionA[$i]
                    ->put('rest_time', '00:00:00');
            }
        }
        return $total_collection;
    }
}
