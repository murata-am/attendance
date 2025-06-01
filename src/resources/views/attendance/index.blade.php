@extends('layouts/app')

@section('css')
    <link rel="stylesheet" href={{ asset("css/index.css") }}>
@endsection

@section('content')

    <div class="attendance_container">
    <div class="attendance_content">
        <div class="status">{{ $status }}</div>

        <div class="date_time_content">
            <p class="date">{{ $date }}</p>
            <p class="time">{{ $time }}</p>
        </div>

        <div class="status_btn">
            @if ($statusKey === 'work_off')
            <form method="POST" action="{{ route('attendance.clockIn') }}">
                @csrf
                <button type="submit" class="btn-black" name="status" value="working">出勤</button>
            </form>

            @elseif ($statusKey === 'working')
                <form method="POST" action="{{ route('attendance.clockOut') }}">
                    @csrf
                    <button type="submit" class="btn-black" name="status" value="finished_work">退勤</button>
                </form>
                <form method="POST" action="{{ route('attendance.breakStart') }}">
                    @csrf
                    <button type="submit" class="btn-white" name="status" value="break">休憩入</button>
                </form>

            @elseif ($statusKey === 'break')
                <form method="POST" action="{{ route('attendance.breakEnd') }}">
                    @csrf
                    <button type="submit" class="btn-white" name="status">休憩戻</button>
                </form>

            @elseif ($statusKey === 'finished_work')
                <div class="finish_work">お疲れ様でした。</div>

            @endif
        </div>
    </div>
    </div>


@endsection