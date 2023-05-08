@extends('layouts.common')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')
    <div class="database">
        {{-- <div class="database_date">
            @foreach ($dates as $date)
            <p>{{ $date['date'] }}</p>
            @endforeach
            {{ $dates->links() }}
        </div> --}}
        @isset($date)
        <div class="database_date">
            <p>{{ $date }}</p>
        </div>
        @endisset

        <form action="{{ route('attendance') }}" method="post">
            @csrf
            <div class="search-form__group">
                <div class="search-form__input">
                    <input name="date" />
                </div>
            </div>
            <div class="search-form__button">
                <button class="search-form__button-submit" type="submit">検索</button>
            </div>
        </form>
        <div class="database__content">
            <div class="database__header">
                <p class="database__p">名前</p>
                <p class="database__p">勤務開始</p>
                <p class="database__p">勤務終了</p>
                <p class="database__p">休憩時間</p>
                <p class="database__p">勤務時間</p>
            </div>
            @isset($items)
            @foreach ($items as $item)
            <div class="database__data">
                <p class="database__p">{{ $item['name'] }}</p>
                <p class="database__p">{{ $item['start_work'] }}</p>
                <p class="database__p">{{ $item['end_work'] }}</p>
                <p class="database__p">{{ $item['rest_time'] }}</p>
                <p class="database__p">{{ $item['work_time'] }}</p>
            </div>
            @endforeach
            @endisset
            @isset($items)
            {{ $items->links('pagination::bootstrap-4') }}
            @endisset
        </div>
    </div>
@endsection
