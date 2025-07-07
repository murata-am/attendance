@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/verify-email.css') }}">
@endsection
@section('content')
    <div class="content-container">
        <div>登録していただいたメールアドレスに確認メールを送付しました。<br>
        メール認証を完了してください。</div>

        <a href="https://mailtrap.io/" target="_blank" class="mailtrap-link">認証はこちらから
        </a>

        @if (session('message'))
            <div class="success-message">{{ session('message') }}</div>
        @endif

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="resend-link">認証メールを再送する</button>
        </form>
    </div>
@endsection
