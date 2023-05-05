@extends('layouts.common')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')
    <div class="database">
        <div class="database_date">
            @foreach ($dates as $date)
            <p>{{ $date['date'] }}</p>
            @endforeach
            {{ $dates->links() }}
        </div>
        {{-- <form action="{{ route('attendance') }}" method="get">
            @csrf
            <div class="search-form__group">
                <div class="search-form__input">
                    <input name="date" value="{{ $attendances['date'] }}" />
                </div>
            </div>
            <div class="search-form__button">
                <button class="search-form__button-submit" type="submit">検索</button>
            </div>
        </form> --}}
        <div class="database__content">
            <div class="database__header">
                <p class="database__p">名前</p>
                <p class="database__p">勤務開始</p>
                <p class="database__p">勤務終了</p>
                <p class="database__p">休憩時間</p>
                <p class="database__p">勤務時間</p>
            </div>
            @foreach ($attendances as $attendance)
            <div class="database__data">
                <p class="database__p">{{ $attendance->name }}</p>
                <p class="database__p">{{ $attendance->start_work }}</p>
                <p class="database__p">{{ $attendance->end_work }}</p>
                <p class="database__p">{{ $attendance->start_rest }}</p>
                <p class="database__p"></p>
            </div>
            @endforeach
            {{ $attendances->links('pagination::bootstrap-4') }}
        </div>
    </div>
@endsection
