<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Admin;

class AdminFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */

    protected $model = Admin::class;
    public function definition()
    {
        return [
            'name' => 'テスト管理者',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
        ];
    }
}
