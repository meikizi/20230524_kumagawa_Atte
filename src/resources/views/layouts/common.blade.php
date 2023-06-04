<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Atte</title>
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    @yield('css')
</head>
<body>
    <header class="header">
        <div class="header__container">
            <h1 class="header__title">
                <a href="{{ url('/') }}">
                    Atte
                </a>
            </h1>
            <ul class="nav">
                <li class="nav_item">
                    <a href="{{ route('timecard') }}">
                        ホーム
                    </a>
                </li>
                <li class="nav_item">
                    <a href="{{ route('attendance') }}">
                        日付一覧
                    </a>
                </li>
                <li class="nav_item">
                    <a href="{{ route('user_list') }}">
                        ユーザー一覧
                    </a>
                </li>
                <li class="nav_item">
                    <a href="{{ route('user_attendance') }}">
                        ユーザー勤怠表
                    </a>
                </li>
                <li class="nav_item">
                    <a href="{{ route('logout') }}">
                        ログアウト
                    </a>
                </li>
            </ul>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="footer">
        <div class="footer__content">
            <small class="copyright">Atte,inc.</small>
        </div>
    </footer>
</body>
</html>
