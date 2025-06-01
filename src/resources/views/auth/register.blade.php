@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/register.css') }}">
@endsection

@php
use Illuminate\Support\Str;
@endphp
@section('content')

    <form action="{{ route('register') }}" method="post" class="register__content">
        @csrf

        <h1 class="title">会員登録</h1>

        <label for="name" name="name">名前</label>
        <input class="input" type="text" name="name" id="name" value="{{ old('name') }}">

        <div class="error__message">
            @error('name')
            {{ $message }}
            @enderror
        </div>

        <label for="email">メールアドレス</label>
        <input class="input" type="text" name="email" id="email" value="{{ old('email') }}">

        <div class="error__message">
            @error('email')
            {{ $message }}
            @enderror
        </div>

        <label for="password">パスワード</label>
        <input class="input" type="password" name="password" id="password">

        <div class="error__message">
            @error('password')
                {{ $message }}
            @enderror
        </div>

        <label for="confirm_password">パスワード確認</label>
        <input class="input" type="password" name="password_confirmation" id="password_confirmation" >

        <div class="error__message">
            @error('password_confirmation')
                {{ $message }}
            @enderror
        </div>

        <button class="register-btn" type="submit">登録する</button>
        <a href="/login" class="login_link">ログインはこちら</a>
    </form>
@endsection