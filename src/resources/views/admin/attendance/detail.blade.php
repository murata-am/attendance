@extends('layouts/app')

@section('css')
    <link rel="stylesheet" href="{{ asset("css/detail.css") }}">
@endsection

@section('content')

    <div class="detail_container">
        <h2 class="title">勤怠詳細</h2>

        <form action="{{ route('admin.attendance.update', ['id' => $attendance->id]) }}" method="post">
            @csrf
            <table class="detail_table">
                <tr>
                    <th class="row-name">名前</th>
                    <th></th>
                    <th>{{ $name }}</th>
                </tr>

                <tr>
                    <th class="row-name">日付</th>
                    <th></th>
                    <th>{{ $work_year }}</th>
                    <th></th>
                    <th>{{ $work_month_day }}</th>
                    <th></th>
                </tr>

                <tr>
                    <th class="row-name">出勤・退勤</th>
                    <th></th>
                    <th>
                        @if (empty($status) || $status === 'approved')
                            <input type="time" name="clock_in"
                                value="{{ old('clock_in', \Carbon\Carbon::parse($attendance->clock_in)->format('H:i')) }}">
                        @else
                            {{ \Carbon\Carbon::parse($correction->corrected_clock_in)->format('H:i') }}
                        @endif


                        <div class="error__message">
                            @error('clock_in')
                                {{ $message }}
                            @enderror
                        </div>
                    </th>
                    <th>
                        <span>～</span>
                        <div class="error__message">
                            @error('clock_in_out')
                                {{ $message }}
                            @enderror
                        </div>
                    </th>
                    <th>
                        @if (empty($status) || $status === 'approved')
                            <input type="time" name="clock_out"
                                value="{{ old('clock_out', \Carbon\Carbon::parse($attendance->clock_out)->format('H:i')) }}">
                        @else
                            {{ \Carbon\Carbon::parse($correction->corrected_clock_out)->format('H:i') }}
                        @endif

                        <div class="error__message">
                            @error('clock_out')
                                {{ $message }}
                            @enderror
                        </div>

                    </th>
                    <th></th>
                </tr>

                @php
                    $breakTimes = $correction ? $correction->correctionBreakTimes : $attendance->breakTimes;
                @endphp

                @foreach ($breakTimes ?? [] as $i => $break)
                    <tr>
                        <th class="row-name">
                            @if ($i === 0)
                                休憩
                            @else
                                休憩{{ $i + 1 }}
                            @endif
                        </th>
                        <th></th>
                        <th>
                            @if (empty($status) || $status === 'approved')
                                <input type="time" name="break_start[]"
                                    value="{{ old('break_start.' . $i, $break->break_start ? \Carbon\Carbon::parse($break->break_start)->format('H:i') : '') }}">
                            @else
                                {{ $break->corrected_break_start ? \Carbon\Carbon::parse($break->corrected_break_start)->format('H:i') : '-' }}
                            @endif

                        </th>
                        <th>
                            <span>～</span>
                            <div class="error__message">
                                @error("break_time.$i")
                                    {{ $message }}
                                @enderror
                            </div>
                        </th>
                        <th>
                            @if (empty($status) || $status === 'approved')
                                <input type="time" name="break_end[]"
                                    value="{{ old('break_end.' . $i, $break->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '') }}">
                            @else
                                {{ $break->corrected_break_end ? \Carbon\Carbon::parse($break->corrected_break_end)->format('H:i') : '-' }}
                            @endif
                        </th>
                        <th></th>
                    </tr>
                @endforeach

                @php
                    $nextIndex = count($breakTimes ?? []);
                @endphp

                @if (empty($status) || $status === 'approved')
                    <tr>
                        <th class="row-name">休憩{{ $nextIndex + 1 }}</th>
                        <th></th>
                        <th>
                            <input type="time" name="break_start[]" value="{{ old('break_start.' . $nextIndex) }}">
                        </th>
                        <th>
                            <span>～</span>
                            <div class="error__message">
                                @error("break_time.$nextIndex")
                                    {{ $message }}
                                @enderror
                            </div>

                        </th>
                        <th>
                            <input type="time" name="break_end[]" value="{{ old('break_end.' . $nextIndex) }}">

                            <div class="error__message">
                                @error("break_end.$nextIndex")
                                    {{ $message }}
                                @enderror
                            </div>
                        </th>
                        <th></th>
                    </tr>
                @endif

                <tr>
                    <th class="row-name">備考</th>
                    <th></th>
                    <th colspan="3">
                        @if (empty($status) || $status === 'approved')
                            <textarea name="reason" class="reason-textarea">{{ old('reason') }}</textarea>

                            <div class="error__message">
                                @error("reason")
                                    {{ $message }}
                                @enderror
                            </div>

                        @else
                            <div class="reason-left">
                                {{ $correction->reason }}
                            </div>
                        @endif
                    </th>
                    <th></th>
                </tr>
            </table>
            <div class="correction-status">
                @if(empty($status) || $status === 'approved')
                    <button type="submit" class="correction-btn">修正</button>
                @elseif($status === 'pending')
                    <div class="approved-comment">*承認待ちのため修正はできません。</div>
                @endif
            </div>
        </form>
    </div>
@endsection