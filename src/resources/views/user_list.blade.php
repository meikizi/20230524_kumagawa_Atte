@extends('layouts.common')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')
    <div class="database">
        <div class="database__date">
            <div class="database__container">
                <div class="user-database__header">
                    <h2 class="user-database__p">名前一覧</h2>
                </div>
                @isset($user_names)
                <div class="user-database__content">
                    @foreach ($user_names as $user_name)
                    <div class="user-database__data">
                        <form method="post" action="{{ route('user_list') }}">
                            @csrf
                            <input type="text" name="name" value="{{ $user_name['name'] }}" class="user-name">
                            <button type="submit" class="submit-button">検索</button>
                        </form>
                    </div>
                    @endforeach
                </div>
                <div class="paginate">
                    @if ($user_names->hasPages())
                        {{ $user_names->links('pagination::bootstrap-4') }}
                    @else
                    <a class="paginate__prev">&lt;</a><a class="current">1</a><a class="paginate__next">&gt;</a>
                    @endif
                </div>
                @endisset
            </div>
        </div>
    </div>
@endsection
