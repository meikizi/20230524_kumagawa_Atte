<?php

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;

/**
 * searchAttendanceが定義済みでない場合に、attendanceモデルから、コレクション作成
 */
if (!function_exists('searchAttendance')) {
    function searchAttendance(string $date)
    {
        // 検索対象日の全レコードを取得
        $daily_attendance = Attendance::where('date', $date)->get();
        $data_set = collect([]);

        if ($daily_attendance->isNotEmpty()) {
            $attendance_id = $daily_attendance
                ->unique('user_id')
                ->pluck('user_id');
            // $user_idを元に個人ごとのレコードを取得。
            for ($i = 0; $i < count($attendance_id); $i++) {
                $user_attendance = $daily_attendance
                    ->where('user_id', $attendance_id[$i]);
                $start_work = new Carbon($user_attendance
                    ->whereNotNull('start_work')
                    ->pluck('start_work')
                    ->first()
                );
                $get_time_value = $user_attendance
                    ->whereNotNull('end_work')
                    ->pluck('end_work')
                    ->first();
                $end_work = new Carbon($get_time_value);

                //出勤時間と退勤時間の差で勤務時間を計算 値を DateInterval 型で取得
                $work_time = $start_work->diff($end_work);

                $name = User::where('id', $attendance_id[$i])->value('name');
                //退勤時間の値が有る（勤務終了）の場合
                if (isset($get_time_value)) {
                    $end_work = $end_work->format('H:i');
                    $work_time = $work_time->format('%H:%I');
                //退勤時間の値が無い（勤務中）なら、それぞれ以下のように記録
                } else {
                    $end_work = '---';
                    $work_time =  $work_time->format('%H:%I');
                }
                // 検索対象日の attendance_id、名前、出勤時間、退勤時間、勤務時間のリストをコレクション形式で格納
                $user_data  = collect([
                    'id_list_att' => $attendance_id[$i],
                    'name' => $name,
                    'start_work' => $start_work->format('H:i'),
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

/**
 * searchRestが定義済みでない場合に、restモデルから、コレクション作成
 */
if (!function_exists('searchRest')) {
    function searchRest(string $date)
    {
        // 検索対象日の全レコードを取得
        $daily_rest = Rest::where('date', $date)->get();
        $data_set = collect([]);
        if ($daily_rest->isNotEmpty()) {
            $rest_id = $daily_rest
                ->unique('user_id')
                ->pluck('user_id');
            // $user_idを元に個人ごとのレコードを取得。
            for ($i = 0; $i < count($rest_id); $i++) {
                $user_rest = $daily_rest->where('user_id', $rest_id[$i]);
                $total_rest_secondes = 0;
                for ($j = 0; $j < count($user_rest); $j++) {
                    $start_rest = new Carbon($user_rest
                        ->whereNotNull('start_rest')
                        ->pluck('start_rest')
                        ->get($j)
                    );
                    $end_rest = new Carbon($user_rest
                        ->whereNotNull('end_rest')
                        ->pluck('end_rest')
                        ->get($j)
                    );
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
                // 日付間隔をオブジェクトで保存
                $total_rest = new DateInterval("PT{$display_Hours}H{$display_minutes}M{$display_seconds}S");

                // 検索対象日の rest_id、休憩時間のリストをコレクション形式で格納
                $user_data = collect([
                    'id_list_rest' => $rest_id[$i],
                    'rest_time' => $total_rest->format('%H:%I')
                ]);
                $data_set[$i] = $user_data;
            }
        }
        return $data_set;
    }
}

/**
 * connectCollectionが定義済みでない場合に、別々のモデルから引き出されたCollectionを結合
 */
if (!function_exists('connectCollection')) {
    function connectCollection(object $collectionA, object $collectionB)
    {
        $attendance_id = $collectionA->pluck('id_list_att');
        $rest_id = $collectionB->pluck('id_list_rest');
        $total_collection = collect([]);
        for ($i = 0; $i < count($attendance_id); $i++) {
            // $rest_id と $attendance_id が一致するものを取得
            $id = $rest_id->search($attendance_id[$i]);
            if (!($id === false)) {
                $rest_time_val = $collectionB
                    ->where('id_list_rest', $attendance_id[$i])
                    ->first()
                    ->get('rest_time');
                $work_time_val = $collectionA
                    ->where('id_list_att', $rest_id[$i])
                    ->first()
                    ->get('work_time');

                $rest_time = new DateTime($rest_time_val);
                $work_time = new DateTime($work_time_val);

                // 実労働時間の算出
                $actual_work_time = $work_time
                    ->diff($rest_time)
                    ->format('%H:%I');

                // 検索対象日の休憩時間、実労働時間のリストをコレクション形式で格納
                $total_collection[$i] = $collectionA[$i]
                    ->put('rest_time', $rest_time_val)
                    ->put('actual_work_time', $actual_work_time);
            } else {
                $total_collection[$i] = $collectionA[$i]
                    ->put('rest_time', '00:00')
                    ->put('actual_work_time', '00:00');
            }
        }
        return $total_collection;
    }
}

/**
 * searchAttendanceUserが定義済みでない場合に、検索した人の勤怠記録のコレクション作成
 */
if (!function_exists('searchAttendanceUser')) {
    function searchAttendanceUser($name = false)
    {
        // 検索した人のidを文字列で取得
        $user_id = User::where('name', $name)->pluck('id')->implode(', ');
        //検索した人のidでこれまでの勤怠記録（休憩除く）を全て取得
        $user_attendance = Attendance::where('user_id', $user_id)->get();

        //日ごとに出勤・退勤のレコードがあるので、それらをまとめて出勤日のカラムの値を順に取得
        $date_list = $user_attendance->unique('date')->pluck('date');
        $data_set = collect([]);

        $dates_count = (count($date_list));

        if ($user_attendance->isNotEmpty()) {
            for ($i = 0; $i < $dates_count; $i++) {
                $start_work = new Carbon(
                    $user_attendance
                        ->whereNotNull('start_work')
                        ->pluck('start_work')
                        ->get($i)
                );
                $get_time_value = $user_attendance
                    ->whereNotNull('end_work')
                    ->pluck('end_work')
                    ->get($i);

                $end_work = new Carbon($get_time_value);
                //出勤時間と退勤時間の差で勤務時間を計算
                $work_time = $start_work->diff($end_work);

                // 退勤時間の値があれば退勤時間と勤務時間を記録
                if (isset($get_time_value)) {
                    $end_work = $end_work->format('H:i');
                    $work_time = $work_time->format('%H:%I');
                } else {
                    //退勤時間の値が無い（勤務中）なら、それぞれ以下のように記録
                    $end_work = '---';
                    $work_time = $work_time->format('%H:%I');
                }
                //ユーザー毎の出勤日、出勤時間、退勤時間、勤務時間のリストをコレクション形式で格納
                $daily_data = collect([
                    'id_list_att' => $date_list[$i],
                    'start_work' => $start_work->format('H:i'),
                    'end_work' => $end_work,
                    'work_time' => $work_time
                ]);
                $data_set[$i] = $daily_data;
            }
        } else {
            $data_set = null;
        }
        return $data_set;
    }
}

/**
 * searchRestUserが定義済みでない場合に、検索した人の休憩記録のコレクション作成
 */
if (!function_exists('searchRestUser')) {
    function searchRestUser($name)
    {
        // 検索した人のidを文字列で取得
        $user_id = User::where('name', $name)->pluck('id')->implode(', ');
        //検索した人のidでこれまでの休憩記録を全て取得
        $user_rest = Rest::where('user_id', $user_id)->get();

        //日ごとに出勤・退勤のレコードがあるので、それらをまとめて出勤日のカラムの値を順に取得
        $date_list = $user_rest->unique('date')->pluck('date');
        $data_set = collect([]);

        $dates_count = (count($date_list));

        if ($user_rest->isNotEmpty()) {
            for ($i = 0; $i < $dates_count; $i++) {
                $daily_rest = $user_rest->where('date', $date_list[$i]);
                $daily_rest_seconds = 0;

                $rest_count = count($daily_rest);
                for ($j = 0; $j < $rest_count; $j++) {
                    $start_rest = new Carbon($daily_rest->whereNotNull('start_rest')->pluck('start_rest')->get($j));
                    // 休憩終了がnull（休憩中）の場合、閲覧時刻でCarbonインスタンス生成→閲覧の時点での総休憩時間を表示
                    $get_time_value = $daily_rest->whereNotNull('end_rest')->pluck('end_rest')->get($j);

                    // unixタイムスタンプを使って休憩時間を求める
                    $end_rest = new Carbon($get_time_value);
                    $start_unix_time = $start_rest->getTimestamp();
                    $end_unix_time = $end_rest->getTimestamp();
                    $rest_seconds = $end_unix_time - $start_unix_time;
                    $daily_rest_seconds += $rest_seconds;
                }

                $date = $daily_rest->pluck('date')->first();

                // 型キャスト
                $display_seconds = (int)$daily_rest_seconds % 60;
                $display_minutes = floor($daily_rest_seconds / 60);
                $display_hours = floor($display_minutes / 60);
                // 日付間隔をオブジェクトで保存
                $daily_rest = new DateInterval("PT{$display_hours}H{$display_minutes}M{$display_seconds}S");

                //ユーザー毎の出勤日、休憩時間のリストをコレクション形式で格納
                $daily_data = collect([
                    'id_list_rest' => $date,
                    'rest_time' => $daily_rest->format('%H:%I')
                ]);
                $data_set[$i] = $daily_data;
            }
        } else {
            $data_set = Null;
        }
        return $data_set;
    }
}

/**
 * searchAtteUserが定義済みでない場合に、ログインしている人の勤怠記録のコレクション作成
 */
if (!function_exists('searchAtteUser')) {
    function searchAtteUser($date = false)
    {
        //現在ログインしている人のidでこれまでの勤怠記録（休憩除く）を全て取得
        //日付で検索した場合は、引数で与えられた日付で絞り込み
        $user_id = Auth::id();
        $user_attendance = Attendance::where('user_id', $user_id)
            ->when($date, function ($query, $date) {
                return $query->where('date', $date);
            }, function ($query) {
                return $query;
            })
            ->get();

        //日ごとに出勤・退勤のレコードがあるので、それらをまとめて出勤日のカラムの値を順に取得
        $date_list = $user_attendance->unique('date')->pluck('date');
        $data_set = collect([]);

        $dates_count = (count($date_list));

        if ($user_attendance->isNotEmpty()) {
            for ($i = 0; $i < $dates_count; $i++) {
                $start_work = new Carbon(
                    $user_attendance
                        ->whereNotNull('start_work')
                        ->pluck('start_work')
                        ->get($i)
                );
                $get_time_value = $user_attendance
                    ->whereNotNull('end_work')
                    ->pluck('end_work')
                    ->get($i);
                $end_work = new Carbon($get_time_value);

                //出勤時間と退勤時間の差で勤務時間を計算
                $work_time = $start_work->diff($end_work);

                //退勤時間の値が有る（勤務終了）の場合
                if (isset($get_time_value)) {
                    $user_end = $end_work->format('H:i');
                    $work_time = $work_time->format('%H:%I');
                    //退勤時間の値が無い（勤務中）なら、それぞれ以下のように記録
                } else {
                    $user_end = '---';
                    $work_time = $work_time->format('%H:%I');
                }
                //検索対象日の出勤日、出勤時間、退勤時間、勤務時間のリストをコレクション形式で格納
                $daily_data = collect([
                    'id_list_att' => $date_list[$i],
                    'start_work' => $start_work->format('H:i'),
                    'end_work' => $user_end,
                    'work_time' => $work_time
                ]);
                $data_set[$i] = $daily_data;
            }
        } else {
            $data_set = null;
        }
        return $data_set;
    }
}

/**
 * searchBreakUserが定義済みでない場合に、ログインしている人の休憩記録のコレクション作成
 */
if (!function_exists('searchBreakUser')) {
    function searchBreakUser($date = 'all')
    {
        $user_id = Auth::id();
        $user_rest = Rest::where('user_id', $user_id)->get();
        //日にちで検索した場合は、引数で与えられた日にちで絞り込み
        if (!($date === 'all')) {
            $user_rest = $user_rest->where('date', $date);
        }
        $date_list = $user_rest->unique('date')->pluck('date');
        $data_set = collect([]);

        $dates_count = (count($date_list));
        if ($user_rest->isNotEmpty()) {
            for ($i = 0; $i < $dates_count; $i++) {
                $daily_rest = $user_rest->where('date', $date_list[$i]);
                $daily_rest_seconds = 0;

                $rest_num = count($daily_rest);
                for ($j = 0; $j < $rest_num; $j++) {
                    $start_rest = new Carbon($daily_rest->whereNotNull('start_rest')->pluck('start_rest')->get($j));
                    // 休憩終了がnull（休憩中）の場合、閲覧時刻でCarbonインスタンス生成→閲覧の時点での総休憩時間を表示
                    $get_time_value = $daily_rest->whereNotNull('end_rest')->pluck('end_rest')->get($j);
                    $end_rest = new Carbon($get_time_value);

                    // unixタイムスタンプを使って休憩時間を求める
                    $start_unix_time = $start_rest->getTimestamp();
                    $end_unix_time = $end_rest->getTimestamp();
                    $rest_seconds = $end_unix_time - $start_unix_time;
                    $daily_rest_seconds += $rest_seconds;
                }

                $date = $daily_rest->pluck('date')->first();
                // 型キャスト
                $display_seconds = (int)$daily_rest_seconds % 60;
                $display_minutes = floor($daily_rest_seconds / 60);
                $display_hours = floor($display_minutes / 60);
                // 日付間隔をオブジェクトで保存
                $daily_rest = new DateInterval("PT{$display_hours}H{$display_minutes}M{$display_seconds}S");

                //検索対象日の出勤日、休憩時間のリストをコレクション形式で格納
                $daily_data = collect([
                    'id_list_rest' => $date,
                    'rest_time' => $daily_rest->format('%H:%I')
                ]);
                $data_set[$i] = $daily_data;
            }
        } else {
            $data_set = Null;
        }
        return $data_set;
    }
}
