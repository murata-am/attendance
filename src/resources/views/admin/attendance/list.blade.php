@extends('layouts/app')

@section('css')
    <link rel="stylesheet" href={{ asset("css/admin/list.css") }}>
@endsection

@section('content')
    <div class="attendance_container">
        <h1 class="title">
            {{ $date_formatted }}の勤怠
        </h1>

        @php
            $today = \Carbon\Carbon::today();
            $next = \Carbon\Carbon::parse($nextDate);
        @endphp
        <div class="days-nav">
            <a href="{{ route('admin.attendance.list', ['date' => $prevDate]) }}" class="yesterday">← 前日</a>

            <div class="days">
                <img src={{ asset("img/calender-icon.svg") }} alt="calender-icon" class="calender-icon">
                <span class="today">{{ $date }}</span>
            </div>

            @if ($next->lte($today))
                <a href="{{ route('admin.attendance.list', ['date' => $nextDate]) }}" class="tomorrow">翌日→</a>
            @else
                <span class="tomorrow">翌日→</span>
            @endif
        </div>

        <div class="today_attendance_list">
            <table class="attendance-table">
                <thead>
                    <tr>
                        <th>名前</th>
                        <th>出勤</th>
                        <th>退勤</th>
                        <th>休憩</th>
                        <th>合計</th>
                        <th>詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($records as $record)
                        <tr>
                            <td>{{ $record['user_name'] }}</td>
                            <td>{{ $record['clock_in'] }}</td>
                            <td>{{ $record['clock_out'] }}</td>
                            <td>
                                @php
                                    $breakMinutes = $record['break_minutes'];
                                    $breakHours = floor($breakMinutes / 60);
                                    $breakRemain = $breakMinutes % 60;
                                @endphp
                                {{ sprintf('%02d:%02d', $breakHours, $breakRemain) }}
                            </td>
                            <td>
                                @php
                                    $workMinutes = $record['work_minutes'];
                                    $workHours = floor($workMinutes / 60);
                                    $workRemain = $workMinutes % 60;
                                @endphp
                                {{ sprintf('%02d:%02d', $workHours, $workRemain) }}
                            </td>
                            <td>
                                @if (isset($record['attendance_id']))
                                    <a href="{{ route('admin.attendance.show', $record['attendance_id']) }}"
                                        class="detail_link">詳細</a>
                                @else

                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>


@endsection