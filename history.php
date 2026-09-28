<?php
require __DIR__ . '/includes/bootstrap.php';
$user = require_auth();
$statement = db()->prepare('SELECT id, score, risk_level, glucose_type, glucose_mg_dl, systolic_bp, diastolic_bp, created_at FROM assessments WHERE user_id = :user_id ORDER BY created_at DESC, id DESC');
$statement->execute(['user_id' => $user['id']]);
$assessments = $statement->fetchAll();

$pageTitle = 'Assessment history';
$activePage = 'history';
require __DIR__ . '/includes/header.php';
?>
<section class="section-space">
    <div class="container history-container">
        <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
            <div><span class="eyebrow">Private to your account</span><h1 class="h2 fw-bold mt-2 mb-1">Assessment history</h1><p class="text-secondary mb-0">These records show your prior educational screens; they are not diagnoses.</p></div>
            <a class="btn btn-primary" href="assessment.php">New assessment</a>
        </div>
        <div class="content-card">
            <?php if ($assessments === []): ?>
                <div class="empty-state"><h2 class="h5 fw-bold">No assessments yet</h2><p class="text-secondary">Your completed screens will appear here.</p><a class="btn btn-primary" href="assessment.php">Start an assessment</a></div>
            <?php else: ?>
                <div class="table-responsive"><table class="table align-middle history-table"><thead><tr><th scope="col">Date</th><th scope="col">Category</th><th scope="col">Points</th><th scope="col">Entered readings</th><th scope="col"><span class="visually-hidden">Details</span></th></tr></thead><tbody><?php foreach ($assessments as $assessment): ?><tr><td><?= h(date('M j, Y · g:i a', strtotime($assessment['created_at']))) ?></td><td><span class="risk-badge risk-<?= h($assessment['risk_level']) ?>"><?= h(ucfirst($assessment['risk_level'])) ?></span></td><td><?= (int) $assessment['score'] ?></td><td><?= (int) $assessment['glucose_mg_dl'] ?> mg/dL <?= h($assessment['glucose_type']) ?> · <?= (int) $assessment['systolic_bp'] ?>/<?= (int) $assessment['diastolic_bp'] ?> mmHg</td><td><a class="btn btn-sm btn-outline-primary" href="result.php?id=<?= (int) $assessment['id'] ?>">View result</a></td></tr><?php endforeach; ?></tbody></table></div>
                <p class="small text-secondary mb-0">For questions about readings or changes over time, consult a qualified healthcare professional.</p>
            <?php endif; ?>
        </div>
        <div class="callout-note mt-4">Educational screening only—not a diagnosis or a substitute for ongoing medical care.</div>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
