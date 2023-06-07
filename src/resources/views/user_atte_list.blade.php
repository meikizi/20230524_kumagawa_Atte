@extends('layouts.common')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')
    <div class="database">
        <div class="attendance-database__date">
            @isset($name)
            <h2 class="attendance_list">{{"{$name}の勤務一覧"}}</h2>
            <form method="get" action="/user_atte_list">
                @csrf
                <laval for="date" class="laval">日付を選択して下さい。</laval>
                <input type="date" name="date" value="date" id="date" class="input-date">
                <button type="submit" class="submit-button">検索</button>
            </form>
            @else
            <p class="error-date">
                指定した日付の出退勤はありません
            </p>
            <form method="get" action="/user_atte_list">
                @csrf
                <laval for="date" class="date_text">日付を選択して下さい。</laval>
                <input type="date" name="date" value="date" id="date" class="input__date">
                <button type="submit" class="submit-button">検索</button>
            </form>
            @endisset
        </div>
        <div class="database__container">
            <div class="database__header">
                <p class="database__p">日付</p>
                <p class="database__p">勤務開始</p>
                <p class="database__p">勤務終了</p>
                <p class="database__p">休憩時間</p>
                <p class="database__p">勤務時間</p>
            </div>
            @isset($items)
            <div class="attendance-database__content">
                @foreach($items as $item)
                <div class="database__data">
                    <p class="database__p">{{ $item['id_list_att'] }}</p>
                    <p class="database__p">{{ $item['start_work'] }}</p>
                    <p class="database__p">{{ $item['end_work'] }}</p>
                    <p class="database__p">{{ $item['rest_time'] }}</p>
                    @if(!($item['rest_time'] === "00:00:00"))
                        <p class="database__p">{{ $item['actual_work_time'] }}</p>
                    @else
                        <p class="database__p">{{ $item['work_time'] }}</p>
                    @endif
                </div>
                @endforeach
            </div>
            <div class="paginate">
                @if ($items->hasPages())
                    {{ $items->appends(request()->query())->links('pagination::bootstrap-4') }}
                @else
                <a class="paginate__prev">&lt;</a><a class="current">1</a><a class="paginate__next">&gt;</a>
                @endif
            </div>
            @endisset
        </div>
    </div>
@endsection
