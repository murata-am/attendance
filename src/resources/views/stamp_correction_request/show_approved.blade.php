@extends('layouts/app')

@section('css')
    <link rel="stylesheet" href="{{ asset("css/showApproved.css") }}">
@endsection

@section('content')
    <div class="detail_container">
        <h2 class="title">勤怠詳細</h2>

        <table class="detail_table">
            <tr>
                <th class="row-name">名前</th>
                <th>{{ $correction->user->name }}</th>
            </tr>

            <tr>
                <th class="row-name">日付</th>
                <th>{{ $work_year }}</th>
                <th></th>
                <th>{{ $work_month_day }}</th>
                <th></th>
            </tr>

            <tr>
                <th class="row-name">出勤・退勤</th>
                <th>{{ \Carbon\Carbon::parse($correction->corrected_clock_in)->format('H:i') }}</th>
                <th>～</th>
                <th>{{ \Carbon\Carbon::parse($correction->corrected_clock_out)->format('H:i') }}</th>
            </tr>

            @foreach ($correction->correctionBreakTimes as $i => $break)
                <tr>
                    <th class="row-name">休憩{{ $i + 1 }}</th>
                    <th>{{ \Carbon\Carbon::parse($break->corrected_break_start)->format('H:i') }}</th>
                    <th>～</th>
                    <th>{{ \Carbon\Carbon::parse($break->corrected_break_end)->format('H:i') }}</th>
                </tr>
            @endforeach

            <tr>
                <th class="row-name">備考</th>
                <th class="reason">{{ $correction->reason }}</th>
            </tr>
        </table>

        <div class="approved-status">
            <span class="approved">承認済み</span>
        </div>
    </div>
@endsection