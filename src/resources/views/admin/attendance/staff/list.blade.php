@extends('layouts/app')

@section('css')
    <link rel="stylesheet" href={{ asset("css/admin/attendance-staff.css") }}>
@endsection

@section('content')
    <div class="attendance_container">
        <h2 class="title">{{ $user->name }}さんの勤怠</h2>

        @php
            $nextMonthCarbon = \Carbon\Carbon::createFromDate($nextMonth['year'], $nextMonth['month'], 1);
            $currentMonthCarbon = \Carbon\Carbon::now()->startOfMonth();
        @endphp
        <div class="month-nav">
            <a href="{{ route('attendance.staff.list', [
                'userId' => $user->id,
                'year' => $prevMonth['year'],
                'month' => $prevMonth['month']
                ]) }}" class="prev-month">← 前月
            </a>

        <div class="current-month">
            <img src={{ asset("img/calender-icon.svg") }} alt="calender-icon" class="calender-icon">
            <span class="this-month"> {{ $year }}/{{ str_pad($month, 2, '0', STR_PAD_LEFT) }}</span>
        </div>

        @if ($nextMonthCarbon->lt($currentMonthCarbon->addMonth()))
            <a href="{{ route('attendance.staff.list', [
            'userId' => $user->id,
            'year' => $nextMonth['year'],
            'month' => $nextMonth['month']
            ]) }}" class="next-month">翌月 →
            </a>
        @else
            <span class="next-month">翌月 →</span>
        @endif
        </div>

        <table class="attendance-table">
            <thead>
                <tr>
                    <th>日付</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
            </thead>
            @foreach ($dates as $dateInfo)
                @php
                    $dateKey = $dateInfo['carbon']->toDateString();
                    $attendance = $attendances[$dateKey] ?? null;
                @endphp

            <tr>
                <td>{{ $dateInfo['display'] }}</td>
                <td>{{ $attendance->clock_in_formatted ?? '' }}</td>
                <td>{{ $attendance->clock_out_formatted ?? '' }}</td>
                <td>
                    @if ($attendance && isset($attendance->total_break_minutes))
                        @php
                            $breakHours = floor($attendance->total_break_minutes / 60);
                            $breakMinutes = $attendance->total_break_minutes % 60;
                        @endphp
                        {{ sprintf('%d:%02d', $breakHours, $breakMinutes) }}
                    @else

                    @endif
                </td>
                <td>
                    @if ($attendance && isset($attendance->total_work_minutes))
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
                    @else
                        <span class="detail_link">詳細</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </table>

        <div class="csv-container">
            <form method="POST" action="{{ route('admin.attendance.csv') }}">
            @csrf
                <input type="hidden" name="userId" value="{{ $user->id }}">
                <input type="hidden" name="year" value="{{ $year }}">
                <input type="hidden" name="month" value="{{ $month }}">
                <button type="submit" class="csv-btn">CSV出力</button>
        </div>
    </div>
@endsection