<?php
require __DIR__ . '/includes/bootstrap.php';
$user = require_auth();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    http_response_code(404);
    exit('Assessment not found. Educational screening only—not a diagnosis.');
}

$statement = db()->prepare('SELECT * FROM assessments WHERE id = :id AND user_id = :user_id LIMIT 1');
$statement->execute(['id' => $id, 'user_id' => $user['id']]);
$assessment = $statement->fetch();
if (!$assessment) {
    http_response_code(404);
    exit('Assessment not found. Educational screening only—not a diagnosis.');
}
$factors = json_decode($assessment['factors_json'], true, 512, JSON_THROW_ON_ERROR);
$recommendations = json_decode($assessment['recommendations_json'], true, 512, JSON_THROW_ON_ERROR);
$pageTitle = 'Screening result';
require __DIR__ . '/includes/header.php';
?>
<section class="section-space">
    <div class="container result-container">
        <div class="content-card">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                <div><span class="eyebrow">Educational screening result · <?= h(date('F j, Y', strtotime($assessment['created_at']))) ?></span><h1 class="h2 fw-bold mt-2 mb-1">Your screening category</h1><p class="text-secondary">This is not a diagnosis, clinical risk estimate, or substitute for a healthcare professional.</p></div>
                <span class="risk-badge risk-<?= h($assessment['risk_level']) ?> risk-badge-large"><?= h(ucfirst($assessment['risk_level'])) ?> screening category</span>
            </div>
            <?php if ((int) $assessment['systolic_bp'] >= 180 || (int) $assessment['diastolic_bp'] >= 120): ?>
                <div class="alert alert-danger mt-3"><strong>Urgent blood-pressure reading entered:</strong> seek urgent medical advice now. If you have chest pain, shortness of breath, weakness, confusion, or severe symptoms, contact local emergency services.</div>
            <?php endif; ?>
            <div class="result-score my-4"><span class="score-number"><?= (int) $assessment['score'] ?></span><div><strong>Educational rule points</strong><div class="small text-secondary">A simple score for this rule set only. It is not a calibrated probability or medical risk score.</div></div></div>
            <p class="small text-secondary">Rule bands: low = 0–1 points; moderate = 2–4; high = 5 or more, or a flagged glucose/history/urgent blood-pressure result. These categories are not clinical risk estimates.</p>
            <div class="row g-4">
                <div class="col-md-6"><h2 class="h5 fw-bold">What contributed</h2><?php if ($factors === []): ?><p class="text-secondary">No additional points were triggered by the factors this limited screen considers.</p><?php else: ?><ul class="reason-list"><?php foreach ($factors as $factor): ?><li><?= h($factor) ?></li><?php endforeach; ?></ul><?php endif; ?><p class="small text-secondary">BMI calculated from the entered adult height and weight: <?= h(number_format((float) $assessment['bmi'], 1)) ?> kg/m². BMI is a limited measure.</p></div>
                <div class="col-md-6"><h2 class="h5 fw-bold">Suggested next steps</h2><ul class="reason-list"><?php foreach ($recommendations as $recommendation): ?><li><?= h($recommendation) ?></li><?php endforeach; ?></ul></div>
            </div>
            <div class="callout-note mt-3"><strong>Important:</strong> Screening thresholds are not diagnostic cutoffs for an individual. Glucose readings depend on fasting status and clinical context; blood pressure should be measured correctly and may need repeating. A low category does not rule out a condition.</div>
            <div class="d-flex flex-wrap gap-2 mt-4"><a class="btn btn-primary" href="assessment.php">New assessment</a><a class="btn btn-outline-secondary" href="history.php">Assessment history</a><a class="btn btn-link" href="dashboard.php">Dashboard</a></div>
        </div>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
