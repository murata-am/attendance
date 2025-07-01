@extends('layouts/app')

@section('css')
    <link rel="stylesheet" href="{{ asset("css/approve.css") }}">
@endsection

@section('content')
    <div class="detail_container">
        <h2 class="title">勤怠詳細</h2>

        <table class="detail_table">
            <tr>
                <th class="row-name">名前</th>
                <td>{{ $correction->user->name }}</td>
                <td></td>
                <td></td>
                <td></td>
            </tr>

            <tr>
                <th class="row-name">日付</th>
                <td>{{ $work_year }}</td>
                <td></td>
                <td>{{ $work_month_day }}</td>
                <td></td>
            </tr>

            <tr>
                <th class="row-name">出勤・退勤</th>
                <td>{{ \Carbon\Carbon::parse($correction->corrected_clock_in)->format('H:i') }}</td>
                <td>～</td>
                <td>{{ \Carbon\Carbon::parse($correction->corrected_clock_out)->format('H:i') }}</td>
                <td></td>
            </tr>

            @foreach ($correction->correctionBreakTimes as $i => $break)
                <tr>
                    <th class="row-name">休憩{{ $i + 1 }}</th>
                    <td>{{ \Carbon\Carbon::parse($break->corrected_break_start)->format('H:i') }}</td>
                    <td>～</td>
                    <td>{{ \Carbon\Carbon::parse($break->corrected_break_end)->format('H:i') }}</td>
                    <td></td>
                </tr>
            @endforeach

            <tr>
                <th class="row-name">備考</th>
                <td colspan="3" class="reason">{{ $correction->reason }}</td>
                <td></td>
            </tr>
        </table>

        <div class="correction-status">
            @if($status === 'approved')
                <button type="submit" class="approved-btn">承認済み</button>
            @elseif($status === 'pending')
                <form action="{{ route('correction.approve.update', ['attendance_correct_request' => $correction->id]) }}" method="POST">
                @csrf
                    <button type="submit" class="correction-btn">承認</button>
            @endif
        </div>
    </div>
@endsection