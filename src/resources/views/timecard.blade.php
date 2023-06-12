@extends('layouts.common')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/timecard.css') }}">
@endsection

@section('content')
    <div class="timecard">
        <div class="timecard__container">
            <div class="timecard__message">
                <p>{{ Auth::user()->name }}さんお疲れ様です!</p>
            </div>
            <div class="timecard__inner">
                <div class="timecard__button">
                    <form action="{{ route('start_work') }}" method="POST">
                        @csrf
                        @if(session('startWork'))
                            <button class="timecard__submit" disabled>勤務開始</button>
                        @else
                            <button class="timecard__submit">勤務開始</button>
                        @endif
                    </form>
                </div>
                <div class="timecard__button">
                    <form action="{{ route('end_work') }}" method="POST">
                        @method('PATCH')
                        @csrf
                        @if(session('startWork'))
                            @if(session('endWork'))
                                <button class="timecard__submit" disabled>勤務終了</button>
                            @else
                                <button class="timecard__submit">勤務終了</button>
                            @endif
                        @else
                            <button class="timecard__submit" disabled>勤務終了</button>
                        @endif
                    </form>
                </div>
            </div>
            <div class="timecard__inner">
                <div class="timecard__button">
                    <form action="{{ route('start_rest') }}" method="POST">
                        @csrf
                        @if(session('startWork'))
                            @if(session('endWork'))
                                <button class="timecard__submit" disabled>休憩開始</button>
                            @else
                                @if(session('startRest'))
                                    <button class="timecard__submit" disabled>休憩開始</button>
                                @else
                                    <button class="timecard__submit">休憩開始</button>
                                @endif
                            @endif
                        @else
                            <button class="timecard__submit" disabled>休憩開始</button>
                        @endif
                    </form>
                </div>
                <div class="timecard__button">
                    <form action="{{ route('end_rest') }}" method="POST">
                        @method('PATCH')
                        @csrf
                        @if(session('startWork'))
                            @if(session('endWork'))
                                <button class="timecard__submit" disabled>休憩終了</button>
                            @else
                                @if(session('startRest'))
                                    <button class="timecard__submit">休憩終了</button>
                                @else
                                    <button class="timecard__submit" disabled>休憩終了</button>
                                @endif
                            @endif
                        @else
                            <button class="timecard__submit" disabled>休憩終了</button>
                        @endif
                    </form>
                </div>
            </div>
            <div class="timecard__alert">
                @if(session('message'))
                    <div class="timecard__alert--success">
                        {{ session('message') }}
                    </div>
                @endif
                @error('start_work')
                    <p class="error-message">{{ $message }}</p>
                @enderror
                @error('end_work')
                    <p class="error-message">{{ $message }}</p>
                @enderror
                @error('start_rest')
                    <p class="error-message">{{ $message }}</p>
                @enderror
                @error('end_rest')
                    <p class="error-message">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>
@endsection
