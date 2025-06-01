<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>attendance.app</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href={{ asset('css/app.css') }}>
    @yield('css')
</head>

<body>
    <header class="header">
            <img src={{ asset("img/logo.svg") }} alt="COACHTECHロゴ">
            <nav class="header__nav">
                <ul class="nav_link">
                    @if(!in_array(Route::currentRouteName(), ['register', 'login', 'verification.notice']))
                        @if (($statusKey ?? '') === 'finished_work')
                        <li><a href="/attendance/list" class="nav">今月の出勤一覧</a></li>

                        @else
                        <li><a href="/attendance" class="nav">勤怠</a></li>

                        <li><a href="/attendance/list" class="nav">勤怠一覧</a></li>
                        @endif

                        <li><a href="/stamp_correction_request/list" class="nav">申請一覧</a></li>

                        <li>
                            <form action="/logout" method="post">
                                @csrf
                                <button class="logout-btn" type="submit">ログアウト</button>
                            </form>
                        </li>
                    @endif
                </ul>
            </nav>
    </header>

    <main>
    @yield('content')
    </main>
</body>
</html>