<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use DatabaseMigrations;

    //名前が未入力の時、バリデーションメッセージが表示される
    public function test_name_validation()
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $response = $this->from('/register')->post('/register', [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('name');
        $this->assertEquals('お名前を入力してください', session('errors')->first('name'));
    }

    //メールアドレスが未入力の時、バリデーションメッセージが表示される
    public function test_email_validation()
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $response = $this->from('/register')->post('/register', [
                'name' => 'テストユーザー',
                'email' => '',
                'password' => 'password',
                'password_confirmation' => 'password',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');
        $this->assertEquals('メールアドレスを入力してください', session('errors')->first('email'));

    }

    // パスワードが8文字未満の時、バリデーションメッセージが表示される
    public function test_password_min8_validation()
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $response = $this->from('/register')->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'pass', // 7文字以下
            'password_confirmation' => 'pass',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['password']);
        $this->assertEquals('パスワードは8文字以上で入力してください', session('errors')->first('password'));

    }

    //パスワードと確認用パスワードが一致しない時、バリデーションメッセージが表示される
    public function test_password_confirmation_validation()
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $response = $this->from('/register')->post('/register',[
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password', //passwordと確認用が違う
            'password_confirmation' => 'password123',
        ] );

        $response->assertStatus(302);
        $response->assertSessionHasErrors('password_confirmation');

        $this->assertEquals('パスワードと一致しません', session('errors')->first('password_confirmation'));

    }

    //パスワードが未入力の時、バリデーションメッセージが表示される
    public function test_password_validation()
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $response = $this->from('/register')->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => '',// password未入力
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['password']);
        $this->assertEquals('パスワードを入力してください', session('errors')->first('password'));

    }

    //フォームに内容が入力されたとき、正しくデータが保存されている
    public function test_register()
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect('/attendance');

        $this->assertDatabaseHas('users', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            // passwordはハッシュ化されているため確認しない
        ]);
    }
}
