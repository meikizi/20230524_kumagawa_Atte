@extends('layouts.common')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')
    <div class="database">
        <div class="database__date">
            <div class="prev-button">
                <form action="{{ route('attendance') }}" method="get">
                    @csrf
                    @if($i === 0)
                    <input type="hidden" name="date" value="{{ $dates[$i] }}"/>
                    @else
                    <input type="hidden" name="date" value="{{ $dates[$i - 1] }}"/>
                    @endif
                    <button type="submit" class="previous"><</button>
                </form>
            </div>
            <div class="attendance-date">{{ $dates[$i] }}</div>
            <div class="next-button">
                <form action="{{ route('attendance') }}" method="get">
                    @csrf
                        @if($i === $dates_count - 1)
                        <input type="hidden" name="date" value="{{ $dates[$i] }}"/>
                        @else
                            <input type="hidden" name="date" value="{{ $dates[$i + 1] }}"/>
                        @endif
                        <button type="submit" class="next">></button>
                </form>
            </div>
        </div>

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
