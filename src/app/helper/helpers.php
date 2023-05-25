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
                $user_attendance = $daily_attendance
                    ->where('user_id', $id_list_att[$i]);
                //出勤時間のカラムから値を順に取得,その値でCarbonインスタンス生成
                $start_work = new Carbon($user_attendance
                    ->whereNotNull('start_work')
                    ->pluck('start_work')
                    ->first());
                $get_time_value = $user_attendance
                    ->whereNotNull('end_work')
                    ->pluck('end_work')
                    ->first();
                $end_work = new Carbon($get_time_value);
                //出勤時間と退勤時間の差で勤務時間を計算
                $work_time = $start_work->diff($end_work);
                $name = User::where('id', $id_list_att[$i])->value('name');
                //退勤時間の値が無い（勤務中）なら、それぞれ以下のように記録
                if (isset($get_time_value)) {
                    $end_work = $end_work->format('H:i:s');
                    // DateInterval::format 間隔をフォーマットする
                    $work_time = $work_time->format('%H:%I:%S');
                } else {
                    $end_work = '---';
                    $work_time =  $work_time->format('%H:%I:%S');
                }
                $user_data  = collect([
                    'id_list_att' => $id_list_att[$i],
                    'name' => $name,
                    'start_work' => $start_work->format('H:i:s'),
                    'end_work' => $end_work,
                    'work_time' => $work_time
                ]);
                $data_set[$i] = $user_data;
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
            $id_list_rest = $daily_rest->unique('user_id')->pluck('user_id');
            for ($i = 0; $i < count($id_list_rest); $i++) {
                $user_rest = $daily_rest->where('user_id', $id_list_rest[$i]);
                $total_rest_secondes = 0;
                for ($j = 0; $j < count($user_rest); $j++) {
                    $start_rest = new Carbon($user_rest->whereNotNull('start_rest')->pluck('start_rest')->get($j));
                    $end_rest = new Carbon($user_rest->whereNotNull('end_rest')->pluck('end_rest')->get($j));
                    // dd($start_rest);
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
                $user_data = collect([
                    'id_list_rest' => $id_list_rest[$i],
                    'rest_time' => $total_rest->format('%H:%I:%S')
                ]);
                $data_set[$i] = $user_data;
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
                // dd($rest_time_val);
                $work_time_val = $collectionA
                    ->where('id_list_att', $id_list_rest[$i])
                    ->first()
                    ->get('work_time');
                // dd($work_time_val);

                $rest_time = new DateTime($rest_time_val);
                // dd($rest_time);
                $work_time = new DateTime($work_time_val);
                // dd($work_time);

                // 実労働時間の算出
                $actual_work_time = $work_time->diff($rest_time);
                // dd($actual_work_time);
                $actual_work_time = $actual_work_time->format('%H:%I:%S');

                $total_collection[$i] = $collectionA[$i]
                    ->put('rest_time', $rest_time_val)
                    ->put('actual_work_time', $actual_work_time);
                // dd($total_collection[$i]);
            } else {
                $total_collection[$i] = $collectionA[$i]
                    ->put('rest_time', '00:00:00')
                    ->put('actual_work_time', '00:00:00');
                // dd($total_collection[$i]);
            }
        }
        return $total_collection;
    }
}

if (!function_exists('searchAtteUser')) {
    /**
     * ログインしている人の勤怠記録のコレクション作成
     */
    function searchAtteUser($date = false)
    {
        //現在ログインしている人のidでモデルからこれまでの勤怠記録（休憩除く）を全て取得
        //日にちで検索した場合は、引数で与えられた日にちで絞り込み
        $user_id = Auth::id();
        $user_attendance = Attendance::where('user_id', $user_id)->when($date, function ($query, $date) {
            return $query->where('date', $date);
        }, function ($query) {
            return $query;
        })
            ->get();
        // dd($user_attendance);

        //日ごとに出勤・退勤のレコードがあるので、それらをまとめて出勤日のカラムの値を順に取得
        $date_list = $user_attendance->unique('date')->pluck('date');
        // dd($date_list);
        $data_set = collect([]);

        //for文を回す回数
        $for_num = (count($date_list));

        //これまでの勤務記録があれば、for文内で計算
        if ($user_attendance->isNotEmpty()) {
            for ($i = 0; $i < $for_num; $i++) {
                //出勤時間のカラムから値を順に取得,その値でCarbonインスタンス生成
                $user_start = new Carbon($user_attendance
                    ->whereNotNull('start_work')
                    ->pluck('start_work')
                    // $i番目のデータを取得
                    ->get($i)
                );
                // dd($user_start);
                //退勤時間のカラムから値を順に取得,その値でCarbonインスタンス生成
                $get_time_value = $user_attendance
                    ->whereNotNull('end_work')
                    ->pluck('end_work')
                    ->get($i);
                // dd($get_time_value);
                $user_end = new Carbon($get_time_value);
                // dd($user_end);
                //出勤時間と退勤時間の差で勤務時間を計算
                $work_time = $user_start->diff($user_end);
                // dd($work_time);

                // 退勤時間の値があれば退勤時間と勤務時間を記録
                if (isset($get_time_value)) {
                    $user_end = $user_end->format('H:i:s');
                    // DateInterval::format 間隔をフォーマットする
                    $work_time = $work_time->format('%H:%I:%S');
                } else {
                    //退勤時間の値が無い（勤務中）なら、それぞれ以下のように記録
                    $user_end = '---';
                    $work_time = $work_time->format('%H:%I:%S');
                }
                //一日の出勤日、出勤時間、退勤時間、勤務時間のリストをコレクションにする。
                $daily_data = collect([
                    'id_list_att' => $date_list[$i],
                    'start_work' => $user_start->format('H:i:s'),
                    'end_work' => $user_end,
                    'work_time' => $work_time
                ]);
                // dd($daily_data);
                $data_set[$i] = $daily_data;
            }
        } else {
            $data_set = null;
        }
        // dd($data_set);
        return $data_set;
    }
}

if (!function_exists('searchRestUser')) {
    /**
     * ログインしている人の休憩記録のコレクション作成
     */
    function searchRestUser($date = 'all')
    {
        $user_id = Auth::id();
        $user_rest = Rest::where('user_id', $user_id)->get();
        //日にちで検索した場合は、引数で与えられた日にちで絞り込み
        if (!($date === 'all')) {
            $user_rest = $user_rest->where('date', $date);
            // dd($user_rest);
        }
        $date_list = $user_rest->unique('date')->pluck('date');
        // dd($date_list);
        $data_set = collect([]);
        if ($user_rest->isNotEmpty()) {
            for ($i = 0; $i < count($date_list); $i++) {
                $daily_rest = $user_rest->where('date', $date_list[$i]);
                // dd($daily_rest);
                $daily_rest_seconds = 0;
                    $rest_num = count($daily_rest);
                for ($j = 0; $j < $rest_num; $j++) {
                    $start_rest = new Carbon($daily_rest->whereNotNull('start_rest')->pluck('start_rest')->get($j));
                    // dd($start_rest);
                    // 休憩終了がnull（休憩中）の場合、閲覧時刻でCarbonインスタンス生成→閲覧の時点での総休憩時間を表示
                    $get_time_value = $daily_rest->whereNotNull('end_rest')->pluck('end_rest')->get($j);
                    $end_rest = new Carbon($get_time_value);
                    // dd($end_rest);
                    $start_unix_time = $start_rest->getTimestamp();
                    // dd($start_unix_time);
                    $end_unix_time = $end_rest->getTimestamp();
                    // dd($end_unix_time);
                    $rest_seconds = $end_unix_time - $start_unix_time;
                    // dd($rest_seconds);
                    $daily_rest_seconds += $rest_seconds;
                }
                $date = $daily_rest->pluck('date')->first();
                // dd($date);
                $display_seconds = (int)$daily_rest_seconds % 60;
                // dd($display_seconds);
                $display_minutes = floor($daily_rest_seconds / 60);
                $display_hours = floor($display_minutes / 60);
                $daily_rest = new DateInterval("PT{$display_hours}H{$display_minutes}M{$display_seconds}S");
                // dd($daily_rest);
                $daily_data = collect([
                    'id_list_rest' => $date,
                    'rest_time' => $daily_rest->format('%H:%I:%S')
                ]);
                $data_set[$i] = $daily_data;
            }
        } else {
            $data_set = Null;
        }
        // dd($data_set);
        return $data_set;
    }
}

