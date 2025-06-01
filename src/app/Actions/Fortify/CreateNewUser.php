<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => [
                'required', 'string', 'min:8', 'confirmed'
            ],

        ];
        $messages = [
            'name.required' => 'お名前を入力してください',
            'name.string' => '名前は文字で入力してください',
            'name.max' => '名前は255字以内で入力してください',
            'email.required' => 'メールアドレスを入力してください',
            'email.string' => 'メールアドレスは文字で入力してください',
            'email.email' => '「ユーザー名@ドメイン」形式で入力してください',
            'email.max' => 'メールアドレスは255字以内で入力してください',
            'email.unique' => 'こちらのメールアドレスはすでに登録されています',
            'password.required' => 'パスワードを入力してください',
            'password.string' => 'パスワードは文字列で入力してください',
            'password.min' => 'パスワードは8文字以上で入力してください',
            'password.confirmed' => 'パスワードと一致しません',
        ];

        $validator = Validator::make($input, $rules, $messages);

        $validator->after(function ($validator) {
            if ($validator->errors()->has('password')) {
                $messages = $validator->errors()->get('password');

                $remainingMessages = [];

                foreach ($messages as $message) {
                    if (str_contains($message, '一致しません')) {
                        $validator->errors()->add('password_confirmation', $message);
                    } else {
                        $remainingMessages[] = $message;
                    }
                }
                $newMessages = $validator->errors()->getMessages();
                $newMessages['password'] = $remainingMessages;

                $reflection = new \ReflectionObject($validator);
                $property = $reflection->getProperty('messages');
                $property->setAccessible(true);
                $property->setValue($validator, new \Illuminate\Support\MessageBag($newMessages));
            }
        });

        $validator->validate();

        return User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
            'role' => 'user',
        ]);
    }
}
