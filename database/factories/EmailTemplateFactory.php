<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EmailTemplate>
 */
class EmailTemplateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => 'test_'.fake()->unique()->slug(),
            'name' => fake()->sentence(3),
            'subject_template' => 'Test Email - {{ $user->name }}',
            'body_content' => $this->getSimpleTemplate(),
            'is_active' => fake()->boolean(80),
        ];
    }

    protected function getSimpleTemplate(): string
    {
        return <<<'BLADE'
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Hello {{ $user->name }}</h1>
        <p>This is a test email template.</p>
    </div>
</body>
</html>
BLADE;
    }
}
