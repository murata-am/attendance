@extends('layouts/app')

@section('css')
    <link rel="stylesheet" href={{ asset("css/attendance_list.css") }}>
@endsection

@section('content')
    <div class="attendance_container">
        <h2 class="title">勤怠一覧</h2>

        <div class="month-nav">
            <a href="{{ route('attendance.list', ['year' => $prevMonth['year'], 'month' => $prevMonth['month']]) }}"
                class="prev-month">← 前月</a>

            <div class="current-month">
                <img src={{ asset("img/calender-icon.svg") }} alt="calender-icon" class="calender-icon">
                <span class="this-month"> {{ $year }}/{{ str_pad($month, 2, '0', STR_PAD_LEFT) }}</span>
            </div>

            <a href="{{ route('attendance.list', ['year' => $nextMonth['year'], 'month' => $nextMonth['month']]) }}"
                class="next-month">翌月 →</a>
        </div>

        <table class="attendance-table">
            <thead>
                <tr>
                    <th>日付</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休息</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
            </thead>
            @foreach ($dates as $dateInfo)
                <tr>
                    <td>{{ $dateInfo['display'] }}</td>
                    @php
                        $attendance = $attendances[$dateInfo['carbon']->toDateString()] ?? null;
                    @endphp
                    <td>{{ $attendance->clock_in_formatted ?? '' }}</td>
                    <td>{{ $attendance->clock_out_formatted ?? '' }}</td>
                    <td>
                        @if (isset($attendance))
                            @php
                                $breakHours = floor($attendance->total_break_minutes / 60);
                                $breakMinutes = $attendance->total_break_minutes % 60;
                            @endphp
                            {{ sprintf('%d:%02d', $breakHours, $breakMinutes) }}
                        @else

                        @endif
                    </td>
                    <td>
                        @if (isset($attendance))
                            @php
                                $workHours = floor($attendance->total_work_minutes / 60);
                                $workMinutes = $attendance->total_work_minutes % 60;
                            @endphp
                            {{ sprintf('%d:%02d', $workHours, $workMinutes) }}
                        @else

                        @endif
                    </td>
                    <td>
                        @if (isset($attendance))
                            @if (Auth::guard('admin')->check())
                                <a href="{{ route('admin.attendance.show', $attendance->id) }}" class="detail_link">詳細</a>
                            @else
                                <a href="{{ route('attendance.edit', $attendance->id) }}" class="detail_link">詳細</a>
                            @endif
                        @endif
                    </td>
                </tr>
            @endforeach
        </table>

    </div>

@endsection