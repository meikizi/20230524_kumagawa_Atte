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

        @isset($date)
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
        @endisset
        <div class="database__container">
            <div class="database__header">
                <p class="database__p">名前</p>
                <p class="database__p">勤務開始</p>
                <p class="database__p">勤務終了</p>
                <p class="database__p">休憩時間</p>
                <p class="database__p">勤務時間</p>
            </div>
            @isset($items)
            <div class="database__content">
                @foreach ($items as $item)
                <div class="database__data">
                    <p class="database__p">{{ $item['name'] }}</p>
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
                {{ $items->appends($paginate_params)->links('pagination::bootstrap-4') }}
            @else
                <div class="paginate">
                    <a class="paginate-prev">&lt;</a><a class="current">1</a><a class="paginate-next">&gt;</a>
                </div>
            @endif
            @endisset
    </div>
@endsection
