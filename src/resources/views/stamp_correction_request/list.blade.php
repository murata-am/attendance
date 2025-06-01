@extends('layouts/app')

@section('css')
    <link rel="stylesheet" href={{ asset("css/correction-list.css") }}>
@endsection

@section('content')
    <div class="list-content">
        <h1>修正申請一覧</h1>

        <div href="request-tab">
            <a href="{{ route('stamp_correction_request.list', ['tab' => 'unapproved', 'query' => request('query')]) }}" class="{{ ($tab ?? 'unapproved') === 'unapproved' ? 'active' : '' }}">
            承認待ち
            </a>

            <a href="{{ route('stamp_correction_request.list', ['tab' => 'unapproved', 'query' => request('query')]) }}"
                class="{{ $tab === 'approved' ? 'active' : '' }}">
            承認済み
            </a>
        </div>

        <div class="correction-table">
            <table>
                <thead>
                    <tr>
                        <th>状態</th>
                        <th>名前</th>
                        <th>対象日時</th>
                        <th>申請理由</th>
                        <th>申請日時</th>
                        <th>詳細</th>
                    </tr>
                </thead>

                @foreach ($attendanceRequests as $attendanceRequest)
                <tr>
                    <th>{{ $status }}</th>
                    <th>{{ $name }}</th>
                    <th>{{ $work_date }}</th>
                    <th></th>
                    <th>{{ $reason }}</th>
                    <th>
                        <a href="{{ route('attendance.edit') }}">詳細</a>
                    </th>
                </tr>
                @endforeach
            </table>

        </div>
    </div>




@endsection