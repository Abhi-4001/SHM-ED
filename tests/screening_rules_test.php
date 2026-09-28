<?php
declare(strict_types=1);

require dirname(__DIR__) . '/includes/bootstrap.php';

function screening_input(array $overrides = []): array
{
    return array_replace([
        'age' => 30,
        'gender' => 'prefer_not_to_say',
        'height_cm' => 170.0,
        'weight_kg' => 65.0,
        'glucose' => 90,
        'glucose_type' => 'fasting',
        'systolic_bp' => 120,
        'diastolic_bp' => 75,
        'family_history' => 'no',
        'activity_level' => 'moderate',
        'smoking' => 'no',
        'alcohol_use' => 'no',
        'previous_history' => 'none',
    ], $overrides);
}

function expect_same(mixed $expected, mixed $actual, string $description): void
{
    if ($expected !== $actual) {
        throw new RuntimeException(sprintf(
            '%s: expected %s, got %s',
            $description,
            var_export($expected, true),
            var_export($actual, true)
        ));
    }
}

expect_same('low', calculate_screening(screening_input())['level'], 'Baseline stays low');
expect_same(1, calculate_screening(screening_input(['weight_kg' => 72.25]))['score'], 'BMI 25 adds one point');
expect_same(2, calculate_screening(screening_input(['glucose' => 100]))['score'], 'Fasting glucose 100 adds two points');
expect_same('moderate', calculate_screening(screening_input(['glucose' => 100]))['level'], 'Fasting glucose 100 is moderate');
expect_same('high', calculate_screening(screening_input(['glucose' => 126]))['level'], 'Fasting glucose 126 prompts high category');
expect_same('moderate', calculate_screening(screening_input(['glucose_type' => 'random', 'glucose' => 199]))['level'], 'Random glucose 199 remains in score bands');
expect_same('high', calculate_screening(screening_input(['glucose_type' => 'random', 'glucose' => 200]))['level'], 'Random glucose 200 prompts high category');
expect_same(true, calculate_screening(screening_input(['systolic_bp' => 180, 'diastolic_bp' => 120]))['urgent_blood_pressure'], 'Urgent pressure is flagged');
expect_same('high', calculate_screening(screening_input(['systolic_bp' => 180, 'diastolic_bp' => 120]))['level'], 'Urgent pressure prompts high category');
expect_same('moderate', calculate_screening(screening_input(['age' => 45, 'family_history' => 'yes']))['level'], 'Two points are moderate');
expect_same('high', calculate_screening(screening_input(['age' => 45, 'family_history' => 'yes', 'activity_level' => 'low', 'glucose' => 100]))['level'], 'Five points are high');
expect_same('high', calculate_screening(screening_input(['previous_history' => 'diabetes']))['level'], 'Known diabetes history prompts clinical follow-up');
expect_same(0, calculate_screening(screening_input(['gender' => 'female', 'alcohol_use' => 'yes']))['score'], 'Unscored optional context does not change points');

echo "Screening rule checks passed.\n";
