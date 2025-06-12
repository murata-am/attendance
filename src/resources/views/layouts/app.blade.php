<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>attendance.app</title>
    <link rel="stylesheet" href="{{ asset("css/sanitize.css") }}">
    <link rel="stylesheet" href={{ asset("css/app.css") }}>
    @yield('css')
</head>

<body>
    <header class="header">
        <img src={{ asset("img/logo.svg") }} alt="COACHTECHロゴ">
            <nav class="header__nav">
                <ul class="nav_link">
                    @if(!in_array(Route::currentRouteName(), ['register', 'login', 'verification.notice', 'admin.login']))
                        @php
                            $user = Auth::guard('admin')->check() ? Auth::guard('admin')->user() : Auth::user();
                            $isAdmin = Auth::user() && Auth::user()->role === 'admin';
                        @endphp

                        @if ($isAdmin)
                            <li><a href="/admin/attendance/list" class="nav">勤怠一覧</a></li>
                            <li><a href="/admin/staff/list" class="nav">スタッフ一覧</a></li>
                            <li><a href="{{ route('admin.stamp_correction_request.list') }}" class="nav">申請一覧</a></li>
                        @else
                            @if (($statusKey ?? '') === 'finished_work')
                                <li><a href="/attendance/list" class="nav">今月の出勤一覧</a></li>
                            @else
                                <li><a href="/attendance" class="nav">勤怠</a></li>
                                <li><a href="/attendance/list" class="nav">勤怠一覧</a></li>
                            @endif
                            <li><a href="{{ route('stamp_correction_request.list') }}" class="nav">申請一覧</a></li>
                        @endif
                        <li>
                            <form action="{{ route('custom.logout') }}" method="post">
                                @csrf
                                <button  class="logout-btn" type="submit">ログアウト</button>
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