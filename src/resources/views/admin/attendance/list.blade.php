@extends('layouts/app')

@section('css')
    <link rel="stylesheet" href={{ asset("css/admin/list.css") }}>
@endsection

@section('content')

    <div class="attendance_container">
        <h1 class="title">
            {{ $date_formatted }}の勤怠
        </h1>

        <div class="days-nav">
            <a href="{{ route('admin.attendance.list', ['date' => $prevDate]) }}" class="yesterday">← 前日</a>

            <div class="days">
                <img src={{ asset("img/calender-icon.svg") }} alt="calender-icon" class="calender-icon">
                <span class="today">{{ $date }}</span>
            </div>

            <a href="{{ route('admin.attendance.list', ['date' => $nextDate]) }}" class="tomorrow">翌日
                →</a>
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
                            <td>{{ $record['break_minutes'] }}</td>
                            <td>{{ $record['work_minutes'] }}</td>
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