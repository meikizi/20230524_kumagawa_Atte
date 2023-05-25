@extends('layouts.common')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')
    <div class="database">
        <div class="database__date">
            @isset($name)
            <h2 class="attendance_list">{{"{$name}の勤務一覧"}}</h2>
            <form method="get" action="/user_attendance_list">
                @csrf
                <laval for="date" class="date_text">日付を選択して下さい。</laval>
                <input type="date" name="date" value="date" id="date" class="date">
                <button class="date_button">検索</button>
            </form>
            @else
            <div class="date__error">
                下のログインを押して、ログインしてください
            </div>
            <a href="{{ route('login') }}">
                ログイン
            </a>
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
            <div class="database__content">
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
            @if ($items->hasPages())
                {{ $items->appends(request()->query())->links('pagination::bootstrap-4') }}
            @else
                <div class="paginate">
                    <a class="paginate-prev">&lt;</a><a class="current">1</a><a class="paginate-next">&gt;</a>
                </div>
            @endif
            @endisset
        </div>
    </div>
@endsection
