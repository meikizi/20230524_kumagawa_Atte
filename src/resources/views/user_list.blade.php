@extends('layouts.common')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')
    <div class="database">
        <div class="database__date">
            <div class="database__container">
                <div class="database__header">
                    <p class="user-database__p">名前</p>
                </div>
                @isset($user_names)
                <div class="database__content">
                    @foreach ($user_names as $user_name)
                    <div class="database__data">
                        <p class="user-database__p">{{ $user_name['name'] }}</p>
                    </div>
                    @endforeach
                </div>
                @if ($user_names->hasPages())
                    {{ $user_names->links('pagination::bootstrap-4') }}
                @else
                    <div class="paginate">
                        <a class="paginate-prev">&lt;</a><a class="current">1</a><a class="paginate-next">&gt;</a>
                    </div>
                @endif
                @endisset
            </div>
        </div>
    </div>
@endsection
