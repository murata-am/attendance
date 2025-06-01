<header class="header">

    @if(!in_array(Route::currentRouteName(), ['register', 'login', 'verification.notice']))
    <nav class="header__nav">
        <ul>
            <li ><a href="/attendance">勤怠</a></li>

            <li><a href="/attendance/list">勤怠一覧</a></li>

            <li><a href="/">申請一覧</a></li>

            <li>
                <form action="/logout" method="post">
                    @csrf
                    <button>ログアウト</button>
                </form>
            </li>
        </ul>
    </nav>
    @endif

</header>