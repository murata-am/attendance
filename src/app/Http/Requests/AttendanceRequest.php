<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;


class AttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'clock_in' => 'required',
            'clock_out' => 'required',
            'break_start' => ['nullable'],
            'break_end' => ['nullable'],
            'reason' => 'required|max:255',
        ];
    }

    public function messages()
    {
        return [
            'clock_in.required' => '出勤時刻を入力してください',
            'clock_out.required' => '退勤時刻を入力してください',
            'reason.required' => '備考を入力してください',
            'reason.max' => '255文字以内で入力してください'
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $clockIn = $this->input('clock_in');
            $clockOut = $this->input('clock_out');

            if (is_string($clockIn)) {
                $clockIn = str_replace('：', ':', $clockIn);
            }
            if (is_string($clockOut)) {
                $clockOut = str_replace('：', ':', $clockOut);
            }

            try {
                if ($clockIn && $clockOut) {
                    $ci = Carbon::parse($clockIn);
                    $co = Carbon::parse($clockOut);

                    if ($ci->gt($co)) {
                        $validator->errors()->add('clock_out', '出勤時間もしくは退勤時間が不適切な値です');
                    }

                    $breakStarts = $this->input('break_start', []);
                    $breakEnds = $this->input('break_end', []);

                    foreach ($breakStarts as $i => $bs) {
                        $be = $breakEnds[$i] ?? null;

                        if (empty($bs) && empty($be)) {
                            continue;
                        }
                        

                        if (empty($bs) || empty($be)) {
                            $validator->errors()->add("break_start.$i", '休憩開始と終了の両方を入力してください');
                            continue;
                        }

                        try {
                            $bsCarbon = Carbon::parse($bs);
                            $beCarbon = Carbon::parse($be);

                            if ($bsCarbon->gt($beCarbon)) {
                                $validator->errors()->add("break_end.$i", '休憩開始時間もしくは終了時間が不正です');
                                }

                            if ($bsCarbon->lt($ci)) {
                                $validator->errors()->add("break_start.$i", '休憩開始時間が勤務時間外です');
                                }

                            if ($beCarbon->gt($co)) {
                                $validator->errors()->add("break_end.$i", '休憩終了時間が勤務時間外です');
                            }
                        } catch (\Exception $e) {
                            $validator->errors()->add("break_start.$i", '休憩時間の形式が不正です');
                        }
                    }
                }
            
            } catch (\Exception $e) {
                $validator->errors()->add('clock_in', '時刻の形式が不正です');
            }
        });
    }
}