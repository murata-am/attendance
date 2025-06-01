@extends('layouts.app')

@section('css')
<link rel="stylesheet" href={{ asset("css/login.css") }}>
@endsection

@section('content')
    <form action="{{ route('login') }}" method="post" class="login__content">
        @csrf
        <h1 class="title">ログイン</h1>

        <label for="email">メールアドレス</label>
        <input type="text" name="email" id="email" value="{{ old('email') }}">

        <div class="error__message">
            @error('email')
                {{ $message }}
            @enderror
        </div>


        <label for="password">パスワード</label>
        <input type="password" name="password" id="password">

        <div class="error__message">
            @error('password')
                {{ $message }}
            @enderror
        </div>

        <button class="login-btn" type="submit">ログインする</button>
        <a href="/register" class="register_link">会員登録はこちら</a>
    </form>


@endsection