@extends('layouts/app')

@section('css')
    <link rel="stylesheet" href="{{ asset("css/detail.css") }}">
@endsection

@section('content')

    <div class="detail_container">
        <h2 class="title">勤怠詳細</h2>

        <form action="{{ route('attendance.store', ['id' => $attendance->id]) }}" method="post">
            @csrf
            <table class="detail_table">
                <tr>
                    <th class="row-name">名前</th>
                    <th>{{ $name }}</th>
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
                    <th>
                        @if (empty($status))
                            <input type="time" name="clock_in" value="{{ old('clock_in', \Carbon\Carbon::parse($attendance->clock_in)->format('H:i')) }}">
                        @elseif($status === 'approved')
                            @if(request()->get('from') === 'approved_list')
                                {{ \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') }}
                            @else
                                <input type="time" name="clock_in" value="{{ old('clock_in', \Carbon\Carbon::parse($attendance->clock_in)->format('H:i')) }}">
                            @endif
                        @elseif($status === 'pending')
                            {{ \Carbon\Carbon::parse($correction->corrected_clock_in)->format('H:i') }}
                        @endif

                        <div class="error__message">
                            @error('clock_in')
                                {{ $message }}
                            @enderror
                        </div>
                    </th>
                    <th class="error">
                        <span>～</span>
                        <div class="error__message__center">
                            @error('clock_in_out')
                                {{ $message }}
                            @enderror
                        </div>
                    </th>
                    <th>
                        @if (empty($status))
                            <input type="time" name="clock_out" value="{{ old('clock_out', \Carbon\Carbon::parse($attendance->clock_out)->format('H:i')) }}">
                        @elseif($status === 'approved')
                            @if(request()->get('from') === 'approved_list')
                                {{ \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') }}
                            @else
                                <input type="time" name="clock_out" value="{{ old('clock_out', \Carbon\Carbon::parse($attendance->clock_out)->format('H:i')) }}">
                            @endif
                        @elseif($status === 'pending')
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
                    $breakTimes = ($correction && $correction->correctionBreakTimes->isNotEmpty()) ? $correction->correctionBreakTimes : $attendance->breakTimes;
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

                        <th>
                            @if (empty($status))
                                <input type="time" name="break_start[]" value="{{ old('break_start.' . $i, $break->break_start ? \Carbon\Carbon::parse($break->break_start)->format('H:i') : '') }}">
                            @elseif($status === 'approved')
                                @if (request()->get('from') === 'approved_list')
                                    {{ $break->break_start ? \Carbon\Carbon::parse($break->break_start)->format('H:i') : '' }}
                                @else
                                    <input type="time" name="break_start[]" value="{{ old('break_start.' . $i, $break->break_start ? \Carbon\Carbon::parse($break->break_start)->format('H:i') : '') }}">
                                @endif
                            @elseif($status === 'pending')
                                {{ $break->corrected_break_start ? \Carbon\Carbon::parse($break->corrected_break_start)->format('H:i') : '' }}
                            @endif
                        </th>
                        <th class="error">
                            <span>～</span>
                            <div class="error__message__center">
                                @error("break_time.$i")
                                    {{ $message }}
                                @enderror
                            </div>
                        </th>
                        <th>
                            @if (empty($status))
                                <input type="time" name="break_end[]" value="{{ old('break_end.' . $i, $break->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '') }}">
                            @elseif($status === 'approved')
                                @if (request()->get('from') === 'approved_list')
                                    {{ $break->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '' }}
                                @else
                                    <input type="time" name="break_end[]" value="{{ old('break_end.' . $i, $break->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '') }}">
                                @endif
                            @elseif($status === 'pending')
                                {{ $break->corrected_break_end ? \Carbon\Carbon::parse($break->corrected_break_end)->format('H:i') : '' }}
                            @endif
                        </th>
                        <th></th>
                    </tr>
                    @endforeach

                    @php
                        $nextIndex = count($breakTimes ?? []);
                        /*$nextIndex = isset($breakTimes) ? count($breakTimes) : 0;*/
                    @endphp

                    @if (empty($status || $status === 'approved'))
                        <tr>
                            <th class="row-name">休憩{{ $nextIndex + 1 }}</th>
                            <th>
                                <input type="time" name="break_start[]" value="{{ old('break_start.' . $nextIndex) }}">
                            </th>
                            <th class="error">
                                <span>～</span>
                                <div class="error__message__center">
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
                        <th colspan="3">
                        @if (empty($status))
                            <textarea name="reason" class="reason-textarea">{{ old('reason', $attendance->reason) }}</textarea>
                            <div class="error__message">
                                @error("reason")
                                    {{ $message }}
                                @enderror
                            </div>
                        @elseif($status === 'approved')
                            @if (request()->get('from') === 'approved_list')
                                <div class="reason-left">{{ $attendance->reason }}</div>
                            @else
                                <textarea name="reason" class="reason-textarea">{{ old('reason', $attendance->reason) }}</textarea>
                            @endif
                        @elseif($status === 'pending')
                            <div class="reason-left">
                                {{ $correction->reason }}
                            </div>
                        @endif
                        </th>
                    </tr>
                </table>

                <div class="correction-status">
                @if (empty($status))
                    <button type="submit" class="correction-btn">修正</button>
                @elseif ($status === 'approved')
                    @if(request()->get('from') === 'approved_list')
                        <div class="approved-btn">承認済み</div>
                    @else
                        <button type="submit" class="correction-btn">修正</button>
                    @endif
                @elseif($status === 'pending')
                    <div class="approved-comment">*承認待ちのため修正はできません。</div>
                @endif
                </div>
            </form>
        </div>
@endsection