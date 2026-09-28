<?php
declare(strict_types=1);

$appConfig = require dirname(__DIR__) . '/config/config.php';

ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_httponly', '1');
session_name('shm_session');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => $appConfig['session_secure'],
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Cache-Control: no-store, private');

set_exception_handler(static function (Throwable $exception): void {
    error_log(sprintf(
        'Smart Health Monitoring error: %s in %s:%d',
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine()
    ));
    http_response_code(500);
    echo '<!doctype html><html lang="en"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">';
    echo '<title>Service unavailable</title><body style="font:16px system-ui;max-width:680px;margin:4rem auto;padding:0 1rem">';
    echo '<h1>We could not complete that request</h1><p>Check the database setup in README.md and try again.</p>';
    echo '<p><strong>Educational screening only—not a diagnosis or medical advice.</strong></p>';
    echo '<p><a href="index.php">Return to the home page</a></p></body></html>';
});

function db(): PDO
{
    static $pdo;
    global $appConfig;

    if (!$pdo instanceof PDO) {
        $database = $appConfig['database'];
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $database['host'],
            $database['port'],
            $database['name'],
            $database['charset']
        );
        $pdo = new PDO($dsn, $database['username'], $database['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }

    return $pdo;
}

function h(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrf_token(): string
{
    if (!isset($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['_csrf'];
}

function verify_csrf(): void
{
    $submitted = $_POST['_csrf'] ?? '';
    if (!is_string($submitted) || !isset($_SESSION['_csrf']) || !hash_equals($_SESSION['_csrf'], $submitted)) {
        http_response_code(400);
        exit('Request validation failed. Refresh and try again. Educational screening only—not a diagnosis.');
    }
}

function require_auth(): array
{
    if (!isset($_SESSION['user_id'], $_SESSION['user_name'])) {
        header('Location: login.php');
        exit;
    }

    return ['id' => (int) $_SESSION['user_id'], 'name' => (string) $_SESSION['user_name']];
}

function redirect(string $location): never
{
    header('Location: ' . $location);
    exit;
}

function calculate_screening(array $input): array
{
    $score = 0;
    $factors = [];
    $urgentBloodPressure = $input['systolic_bp'] >= 180 || $input['diastolic_bp'] >= 120;
    $glucoseConcerning = ($input['glucose_type'] === 'fasting' && $input['glucose'] >= 126)
        || ($input['glucose_type'] === 'random' && $input['glucose'] >= 200);

    if ($input['age'] >= 45) {
        $score++;
        $factors[] = 'Age 45 or older adds one screening point in this educational rule set.';
    }

    $bmi = $input['weight_kg'] / (($input['height_cm'] / 100) ** 2);
    if ($bmi >= 25) {
        $score++;
        $factors[] = 'Adult BMI is 25 or higher: +1 point. BMI is a limited screening measure, not a diagnosis.';
    }
    if ($bmi >= 30) {
        $score++;
        $factors[] = 'Adult BMI is 30 or higher: +1 additional point. BMI is a limited screening measure, not a diagnosis.';
    }

    if (($input['glucose_type'] === 'fasting' && $input['glucose'] >= 100 && $input['glucose'] < 126)
        || ($input['glucose_type'] === 'random' && $input['glucose'] >= 140 && $input['glucose'] < 200)) {
        $score += 2;
        $factors[] = 'Glucose is in a range that merits clinical interpretation: +2 points. One reading cannot diagnose a condition.';
    } elseif ($glucoseConcerning) {
        $score += 4;
        $factors[] = 'Glucose is at or above a threshold commonly used to prompt clinical follow-up: +4 points. Repeat testing and context matter.';
    }

    if ($input['systolic_bp'] >= 140 || $input['diastolic_bp'] >= 90) {
        $score += 2;
        $factors[] = 'Blood pressure is at least 140 systolic or 90 diastolic: +2 points. Repeat measurement and clinical review are important.';
    } elseif ($input['systolic_bp'] >= 130 || $input['diastolic_bp'] >= 80) {
        $score++;
        $factors[] = 'Blood pressure is at least 130 systolic or 80 diastolic: +1 point. A single reading is not a diagnosis.';
    }

    if ($input['family_history'] === 'yes') {
        $score++;
        $factors[] = 'A reported family history of diabetes: +1 point.';
    }
    if ($input['activity_level'] === 'low') {
        $score++;
        $factors[] = 'Reported activity below 150 minutes per week: +1 point in this rule set.';
    }
    if ($input['smoking'] === 'yes') {
        $score++;
        $factors[] = 'Current smoking: +1 general health screening point; it is not specific to diabetes.';
    }
    if (in_array($input['previous_history'], ['prediabetes', 'gestational_diabetes'], true)) {
        $score += 2;
        $factors[] = 'Reported prior prediabetes or gestational diabetes: +2 points. Discuss appropriate follow-up with a clinician.';
    } elseif ($input['previous_history'] === 'diabetes') {
        $score += 4;
        $factors[] = 'Reported diabetes history: +4 follow-up points. This tool cannot assess or replace your ongoing care plan.';
    }

    if ($glucoseConcerning || $urgentBloodPressure || $input['previous_history'] === 'diabetes' || $score >= 5) {
        $level = 'high';
    } elseif ($score >= 2) {
        $level = 'moderate';
    } else {
        $level = 'low';
    }

    $recommendations = match ($level) {
        'high' => [
            'Arrange clinical follow-up soon to discuss your readings and whether properly conducted repeat testing is appropriate.',
            'If your blood pressure is 180/120 mmHg or higher, seek urgent medical advice now; if you have chest pain, shortness of breath, weakness, confusion, or severe symptoms, contact local emergency services.',
            'If you already have diabetes, follow your clinician’s care plan rather than using this screening result to change treatment.',
        ],
        'moderate' => [
            'Consider discussing your results and personal risk factors with a healthcare professional, especially if readings were not taken under recommended conditions.',
            'Ask a clinician whether repeat glucose or blood-pressure measurements are appropriate for you.',
            'Choose sustainable activity and food habits that fit your needs; a clinician can help tailor advice.',
        ],
        default => [
            'This limited screen found few of the listed factors, but it cannot rule out diabetes or future risk.',
            'Continue routine preventive care and ask a healthcare professional about screening intervals that suit you.',
            'Seek medical advice for symptoms or concerns regardless of this result.',
        ],
    };

    if ($urgentBloodPressure) {
        array_unshift($factors, 'Your entered blood pressure is 180 systolic or 120 diastolic and needs urgent medical attention; this urgency flag adds no separate points.');
    }

    if ($input['alcohol_use'] === 'yes') {
        $factors[] = 'Alcohol use was recorded as requested but was not used to calculate this screening score.';
    }
    if ($input['gender'] !== 'prefer_not_to_say') {
        $factors[] = 'Gender was recorded for your personal context but is not used in this screening score.';
    }

    return [
        'score' => $score,
        'level' => $level,
        'bmi' => round($bmi, 1),
        'factors' => $factors,
        'recommendations' => $recommendations,
        'urgent_blood_pressure' => $urgentBloodPressure,
        'glucose_concerning' => $glucoseConcerning,
    ];
}
