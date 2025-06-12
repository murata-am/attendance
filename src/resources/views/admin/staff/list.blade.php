@extends('layouts/app')

@section('css')
    <link rel="stylesheet" href="{{ asset("css/admin/staff-list.css") }}">
@endsection

@section('content')

    <div class="staff_container">
        <h2 class="title">スタッフ一覧</h2>

        <table class="staff_table">
            <thead>
                <tr>
                    <th>名前</th>
                    <th>メールアドレス</th>
                    <th>月次勤怠</th>
                </tr>
            </thead>

            @foreach($staff as $user)
                <tr>
                    <th>{{ $user->name }}</th>
                    <th>{{ $user->email }}</th>
                    <th>
                        <a href="{{ route('attendance.list', ['user_id'=>$user->id]) }}">詳細</a>
                    </th>
                </tr>
            @endforeach
        </table>
    </div>


@endsection