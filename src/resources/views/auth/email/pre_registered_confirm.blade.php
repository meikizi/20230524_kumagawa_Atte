@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/register.css') }}">
@endsection

@section('content')
サイトへのアカウント仮登録が完了しました。<br>
<br>
以下のURLからログインして、本登録を完了させてください。<br>
<a href="{{ url('register/verify/'.$token) }}">
    {{url('register/verify/'.$token)}}
</a>
@endsection
