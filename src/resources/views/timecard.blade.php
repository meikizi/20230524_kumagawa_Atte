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
                    <form action="{{ route('punchin') }}" method="POST">
                        @csrf
                        <button class="timecard__submit" type="submit">勤務開始</button>
                    </form>
                </div>
                <div class="timecard__button">
                    <form action="{{ route('punchout') }}" method="POST">
                        @csrf
                        <button class="timecard__submit" type="submit">勤務終了</button>
                    </form>
                </div>
            </div>
            <div class="timecard__inner">
                <div class="timecard__button">
                    <form action="{{ route('start_rest') }}" method="POST">
                        @csrf
                        <button class="timecard__submit">休憩開始</button>
                    </form>
                </div>
                <div class="timecard__button">
                    <form action="{{ route('end_rest') }}" method="POST">
                        @csrf
                        <button class="timecard__submit">休憩終了</button>
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
                <p class="error__message">{{$message}}</p>
                @enderror
                @error('end_work')
                <p class="error__message">{{$message}}</p>
                @enderror
                @error('start_rest')
                <p class="error__message">{{$message}}</p>
                @enderror
                @error('end_rest')
                <p class="error__message">{{$message}}</p>
                @enderror
            </div>
        </div>
    </div>
@endsection
