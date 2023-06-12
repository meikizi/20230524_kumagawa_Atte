<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <link rel="stylesheet" href="{{ asset('css/register.css') }}">
</head>
<body>
    <h1>Atte</h1>
    <br>
    サイトへのアカウント仮登録が完了しました。<br>
    <br>
    以下のURLからログインして、本登録を完了させてください。<br>
    <a href="{{ url('register/verify/'.$token) }}">
        {{url('register/verify/'.$token)}}
    </a>
</body>
</html>
