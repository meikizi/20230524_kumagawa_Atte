<?php

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;

if (!function_exists('searchAttendance')) {
    function searchAttendance(string $date)
    {
        $daily_attendance = Attendance::where('date', $date)->get();
        $data_set = collect([]);
        // 全レコードから$user_idを元に個人ごとのレコードを取得。
        if ($daily_attendance->isNotEmpty()) {
            $id_list_att = $daily_attendance->unique('user_id')->pluck('user_id');
            for ($i = 0; $i < count($id_list_att); $i++) {
                $person_attendance = $daily_attendance->where('user_id', $id_list_att[$i]);
                $start_work = new Carbon($person_attendance->whereNotNull('start_work')->pluck('start_work')->first());
                $end_work = new Carbon($person_attendance->whereNotNull('end_work')->pluck('end_work')->first());
                // 勤務時間の算出
                $work_time = $start_work->diff($end_work);
                $name = User::where('id', $id_list_att[$i])->value('name');
                $person_data  = collect(['id_list_att' => $id_list_att[$i], 'name' => $name, 'start_work' => $start_work, 'end_work' => $end_work, 'work_time' => $work_time]);
                $data_set[$i] = $person_data;
            }
        } else {
            $data_set = null;
        }
        return $data_set;
    }
}

if (!function_exists('searchRest')) {
    function searchRest(string $date)
    {
        $daily_rest = Rest::where('date', $date)->get();
        $data_set = collect([]);
        if ($daily_rest->isNotEmpty()) {
            $id_list_rest = $daily_rest->unique('user_id')->pluck('user_id');
            for ($i = 0; $i < count($id_list_rest); $i++) {
                $person_rest = $daily_rest->where('user_id', $id_list_rest[$i]);
                $total_rest_secondes = 0;
                if (count($person_rest) % 2 == 0) {
                    $rest_num = (count($person_rest) / 2);
                } else {
                    $rest_num = ((count($person_rest) + 1) / 2);
                }
                for ($j = 0; $j < $rest_num; $j++) {
                    $start_rest = new Carbon($person_rest->whereNotNull('start_rest')->pluck('start_rest')->get($j));
                    $end_rest = new Carbon($person_rest->whereNotNull('end_rest')->pluck('end_rest')->get($j));
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
                $total_rest = new DateInterval("PT{$display_Hours}H{$display_minutes}M{$display_seconds}S");
                $person_data = collect(['id_list_rest' => $id_list_rest[$i], 'rest_time' => $total_rest->format('%H:%I:%S')]);
                $data_set[$i] = $person_data;
            }
        }
        return $data_set;
    }
}
