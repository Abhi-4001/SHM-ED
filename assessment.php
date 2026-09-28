<?php
require __DIR__ . '/includes/bootstrap.php';
$user = require_auth();
$errors = [];
$values = [
    'age' => '', 'gender' => 'prefer_not_to_say', 'height_cm' => '', 'weight_kg' => '',
    'glucose' => '', 'glucose_type' => 'fasting', 'systolic_bp' => '', 'diastolic_bp' => '',
    'family_history' => 'no', 'activity_level' => 'moderate', 'smoking' => 'no',
    'alcohol_use' => 'no', 'previous_history' => 'none',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    foreach (array_keys($values) as $key) {
        if (array_key_exists($key, $_POST) && !is_string($_POST[$key])) {
            $errors[] = 'Invalid value supplied for ' . str_replace('_', ' ', $key) . '.';
        } elseif (isset($_POST[$key])) {
            $values[$key] = trim($_POST[$key]);
        }
    }

    $integerRanges = [
        'age' => [18, 120, 'Age'],
        'glucose' => [40, 600, 'Glucose'],
        'systolic_bp' => [60, 260, 'Systolic blood pressure'],
        'diastolic_bp' => [30, 160, 'Diastolic blood pressure'],
    ];
    foreach ($integerRanges as $field => [$minimum, $maximum, $label]) {
        if (filter_var($values[$field], FILTER_VALIDATE_INT) === false || (int) $values[$field] < $minimum || (int) $values[$field] > $maximum) {
            $errors[] = $label . " must be a whole number from {$minimum} to {$maximum}.";
        }
    }
    foreach (['height_cm' => [120, 230, 'Height'], 'weight_kg' => [30, 300, 'Weight']] as $field => [$minimum, $maximum, $label]) {
        if (!is_numeric($values[$field]) || (float) $values[$field] < $minimum || (float) $values[$field] > $maximum) {
            $errors[] = $label . " must be between {$minimum} and {$maximum}.";
        }
    }

    $allowed = [
        'gender' => ['female', 'male', 'non_binary', 'other', 'prefer_not_to_say'],
        'glucose_type' => ['fasting', 'random'],
        'family_history' => ['yes', 'no'],
        'activity_level' => ['low', 'moderate', 'high'],
        'smoking' => ['yes', 'no'],
        'alcohol_use' => ['yes', 'no'],
        'previous_history' => ['none', 'prediabetes', 'diabetes', 'gestational_diabetes', 'other'],
    ];
    foreach ($allowed as $field => $options) {
        if (!in_array($values[$field], $options, true)) {
            $errors[] = 'Choose a valid option for ' . str_replace('_', ' ', $field) . '.';
        }
    }
    if ((int) $values['diastolic_bp'] >= (int) $values['systolic_bp']) {
        $errors[] = 'Diastolic blood pressure must be lower than systolic blood pressure.';
    }

    if ($errors === []) {
        $input = [
            'age' => (int) $values['age'],
            'gender' => $values['gender'],
            'height_cm' => (float) $values['height_cm'],
            'weight_kg' => (float) $values['weight_kg'],
            'glucose' => (int) $values['glucose'],
            'glucose_type' => $values['glucose_type'],
            'systolic_bp' => (int) $values['systolic_bp'],
            'diastolic_bp' => (int) $values['diastolic_bp'],
            'family_history' => $values['family_history'],
            'activity_level' => $values['activity_level'],
            'smoking' => $values['smoking'],
            'alcohol_use' => $values['alcohol_use'],
            'previous_history' => $values['previous_history'],
        ];
        $result = calculate_screening($input);
        $statement = db()->prepare(
            'INSERT INTO assessments
            (user_id, age, gender, height_cm, weight_kg, bmi, glucose_mg_dl, glucose_type,
             systolic_bp, diastolic_bp, family_history, activity_level, smoking, alcohol_use,
             previous_history, score, risk_level, factors_json, recommendations_json)
             VALUES
            (:user_id, :age, :gender, :height_cm, :weight_kg, :bmi, :glucose_mg_dl, :glucose_type,
             :systolic_bp, :diastolic_bp, :family_history, :activity_level, :smoking, :alcohol_use,
             :previous_history, :score, :risk_level, :factors_json, :recommendations_json)'
        );
        $statement->execute([
            'user_id' => $user['id'], 'age' => $input['age'], 'gender' => $input['gender'],
            'height_cm' => $input['height_cm'], 'weight_kg' => $input['weight_kg'], 'bmi' => $result['bmi'],
            'glucose_mg_dl' => $input['glucose'], 'glucose_type' => $input['glucose_type'],
            'systolic_bp' => $input['systolic_bp'], 'diastolic_bp' => $input['diastolic_bp'],
            'family_history' => $input['family_history'], 'activity_level' => $input['activity_level'],
            'smoking' => $input['smoking'], 'alcohol_use' => $input['alcohol_use'],
            'previous_history' => $input['previous_history'], 'score' => $result['score'],
            'risk_level' => $result['level'],
            'factors_json' => json_encode($result['factors'], JSON_THROW_ON_ERROR),
            'recommendations_json' => json_encode($result['recommendations'], JSON_THROW_ON_ERROR),
        ]);
        redirect('result.php?id=' . (int) db()->lastInsertId());
    }
}

$pageTitle = 'New assessment';
$activePage = 'assessment';
require __DIR__ . '/includes/header.php';
?>
<section class="section-space">
    <div class="container form-container">
        <span class="eyebrow">Your details stay in your account</span>
        <h1 class="h2 fw-bold mt-2">Educational health assessment</h1>
        <p class="text-secondary">Complete the fields as accurately as you can. The result is an educational rule-based screening category—not a diagnosis.</p>
        <div class="alert alert-warning"><strong>Before you begin:</strong> Do not use this form for urgent symptoms. If you feel seriously unwell, contact your local emergency services or a clinician.</div>
        <?php if ($errors !== []): ?><div class="alert alert-danger" role="alert"><strong>Please review:</strong><ul class="mb-0 mt-2"><?php foreach ($errors as $error): ?><li><?= h($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
        <form method="post" action="assessment.php" class="content-card" novalidate>
            <input type="hidden" name="_csrf" value="<?= h(csrf_token()) ?>">
            <h2 class="h5 fw-bold">Basic measures</h2>
            <div class="row g-3 mt-1">
                <div class="col-sm-6"><label class="form-label" for="age">Age (years)</label><input class="form-control" id="age" name="age" type="number" min="18" max="120" step="1" required value="<?= h($values['age']) ?>"></div>
                <div class="col-sm-6"><label class="form-label" for="gender">Gender (optional)</label><select class="form-select" id="gender" name="gender"><option value="prefer_not_to_say" <?= $values['gender'] === 'prefer_not_to_say' ? 'selected' : '' ?>>Prefer not to say</option><option value="female" <?= $values['gender'] === 'female' ? 'selected' : '' ?>>Woman</option><option value="male" <?= $values['gender'] === 'male' ? 'selected' : '' ?>>Man</option><option value="non_binary" <?= $values['gender'] === 'non_binary' ? 'selected' : '' ?>>Non-binary</option><option value="other" <?= $values['gender'] === 'other' ? 'selected' : '' ?>>Another identity</option></select><div class="form-text">Not used in the screening score.</div></div>
                <div class="col-sm-6"><label class="form-label" for="height_cm">Height (cm)</label><input class="form-control" id="height_cm" name="height_cm" type="number" min="120" max="230" step="0.1" required value="<?= h($values['height_cm']) ?>"></div>
                <div class="col-sm-6"><label class="form-label" for="weight_kg">Weight (kg)</label><input class="form-control" id="weight_kg" name="weight_kg" type="number" min="30" max="300" step="0.1" required value="<?= h($values['weight_kg']) ?>"><div class="form-text">BMI is calculated from height and weight for adults.</div></div>
            </div>
            <hr class="my-4">
            <h2 class="h5 fw-bold">Readings</h2>
            <div class="row g-3 mt-1">
                <div class="col-sm-6"><label class="form-label" for="glucose">Glucose (mg/dL)</label><input class="form-control" id="glucose" name="glucose" type="number" min="40" max="600" step="1" required value="<?= h($values['glucose']) ?>"></div>
                <div class="col-sm-6"><label class="form-label" for="glucose_type">Reading type</label><select class="form-select" id="glucose_type" name="glucose_type"><option value="fasting" <?= $values['glucose_type'] === 'fasting' ? 'selected' : '' ?>>Fasting</option><option value="random" <?= $values['glucose_type'] === 'random' ? 'selected' : '' ?>>Random / not fasting</option></select><div class="form-text">Use the units shown by your test. This tool cannot interpret a result clinically.</div></div>
                <div class="col-sm-6"><label class="form-label" for="systolic_bp">Systolic blood pressure (mmHg)</label><input class="form-control" id="systolic_bp" name="systolic_bp" type="number" min="60" max="260" step="1" required value="<?= h($values['systolic_bp']) ?>"></div>
                <div class="col-sm-6"><label class="form-label" for="diastolic_bp">Diastolic blood pressure (mmHg)</label><input class="form-control" id="diastolic_bp" name="diastolic_bp" type="number" min="30" max="160" step="1" required value="<?= h($values['diastolic_bp']) ?>"></div>
            </div>
            <hr class="my-4">
            <h2 class="h5 fw-bold">Health context</h2>
            <div class="row g-3 mt-1">
                <div class="col-sm-6"><label class="form-label" for="family_history">Family history of diabetes</label><select class="form-select" id="family_history" name="family_history"><option value="no" <?= $values['family_history'] === 'no' ? 'selected' : '' ?>>No / not known</option><option value="yes" <?= $values['family_history'] === 'yes' ? 'selected' : '' ?>>Yes</option></select></div>
                <div class="col-sm-6"><label class="form-label" for="activity_level">Usual activity level</label><select class="form-select" id="activity_level" name="activity_level"><option value="low" <?= $values['activity_level'] === 'low' ? 'selected' : '' ?>>Below 150 minutes per week</option><option value="moderate" <?= $values['activity_level'] === 'moderate' ? 'selected' : '' ?>>About 150–300 minutes per week</option><option value="high" <?= $values['activity_level'] === 'high' ? 'selected' : '' ?>>More than 300 minutes per week</option></select></div>
                <div class="col-sm-6"><label class="form-label" for="smoking">Do you currently smoke?</label><select class="form-select" id="smoking" name="smoking"><option value="no" <?= $values['smoking'] === 'no' ? 'selected' : '' ?>>No</option><option value="yes" <?= $values['smoking'] === 'yes' ? 'selected' : '' ?>>Yes</option></select></div>
                <div class="col-sm-6"><label class="form-label" for="alcohol_use">Do you use alcohol?</label><select class="form-select" id="alcohol_use" name="alcohol_use"><option value="no" <?= $values['alcohol_use'] === 'no' ? 'selected' : '' ?>>No</option><option value="yes" <?= $values['alcohol_use'] === 'yes' ? 'selected' : '' ?>>Yes</option></select><div class="form-text">Recorded as requested, but not scored.</div></div>
                <div class="col-sm-6"><label class="form-label" for="previous_history">Previous health history</label><select class="form-select" id="previous_history" name="previous_history"><option value="none" <?= $values['previous_history'] === 'none' ? 'selected' : '' ?>>No known history / prefer not to say</option><option value="prediabetes" <?= $values['previous_history'] === 'prediabetes' ? 'selected' : '' ?>>Prediabetes</option><option value="diabetes" <?= $values['previous_history'] === 'diabetes' ? 'selected' : '' ?>>Diabetes</option><option value="gestational_diabetes" <?= $values['previous_history'] === 'gestational_diabetes' ? 'selected' : '' ?>>Gestational diabetes</option><option value="other" <?= $values['previous_history'] === 'other' ? 'selected' : '' ?>>Other / prefer not to say</option></select></div>
            </div>
            <div class="callout-note my-4"><strong>Privacy:</strong> Do not enter identifying details about family members or add medical notes. Only the listed screening fields are saved to your account.</div>
            <button class="btn btn-primary btn-lg" type="submit">Calculate educational screen</button>
            <p class="small text-secondary mt-3 mb-0">The rule uses cautious educational thresholds and cannot diagnose, predict with certainty, or rule out diabetes.</p>
        </form>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
