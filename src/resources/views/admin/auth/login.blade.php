@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href={{ asset("css/admin/login.css") }}>
@endsection

@section('content')
    <form action="{{ route('admin.login') }}" method="post" class="login__content">
        @csrf
        <h1 class="title">管理者ログイン</h1>

        @if ($errors->has('auth'))
            <div class="error__message">{{ $errors->first('auth') }}</div>
        @endif

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

        <button class="login-btn" type="submit">管理者ログインする</button>
    </form>


@endsection