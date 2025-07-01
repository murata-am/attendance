@extends('layouts/app')

@section('css')
    <link rel="stylesheet" href={{ asset('css/correction_list.css') }}>
@endsection

@section('content')
    <div class="list-content">
        <h1 class="list-title">申請一覧</h1>

        <div class="request-tab">
            <a href="{{ route(Route::currentRouteName(),  ['tab' => 'unapproved']) }}" class="{{ ($tab ?? 'unapproved') === 'unapproved' ? 'active' : '' }}">
            承認待ち
            </a>

            <a href="{{ route(Route::currentRouteName(), ['tab' => 'approved']) }}"
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

                @foreach ($correctionRequests as $correctionRequest)
                    <tr>
                        <th>
                            @php
                                $statusMap = [
                                    'pending' => '承認待ち',
                                    'approved' => '承認済み',
                                ];
                                $statusValue = $correctionRequest->approval->status ?? null;
                            @endphp
                            {{ $statusMap[$statusValue] ?? '申請なし' }}
                        </th>
                        <th>{{ $correctionRequest->user->name ?? '名前なし' }}</th>
                        <th>{{ \Carbon\Carbon::parse($correctionRequest->attendance->work_date)->format('Y/m/d') ?? '日付なし' }}</th>
                        <th>{{ $correctionRequest->reason ?? '理由なし' }}</th>
                        <th>{{ $correctionRequest->created_at->format('Y/m/d') }}</th>
                        <th>
                        @if (Auth::guard('admin')->check())
                            <a href="{{ route('admin.correction.approve.show', ['attendance_correct_request' => $correctionRequest]) }}" class="detail_link">詳細</a>
                        @else
                            <a href="{{ route('attendance.edit',  ['id' => $correctionRequest->attendance->id, 'from' => 'approved_list']) }}" class="detail_link">詳細</a>
                        @endif
                        </th>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>
@endsection