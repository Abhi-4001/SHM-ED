<?php
require __DIR__ . '/includes/bootstrap.php';
$user = require_auth();
$statement = db()->prepare('SELECT id, score, risk_level, created_at FROM assessments WHERE user_id = :user_id ORDER BY created_at DESC, id DESC LIMIT 8');
$statement->execute(['user_id' => $user['id']]);
$assessments = $statement->fetchAll();

$pageTitle = 'Dashboard';
$activePage = 'dashboard';
require __DIR__ . '/includes/header.php';
?>
<section class="section-space">
    <div class="container">
        <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
            <div><span class="eyebrow">Your private overview</span><h1 class="h2 fw-bold mt-2 mb-1">Welcome, <?= h($user['name']) ?></h1><p class="text-secondary mb-0">A personal record for educational screening—not a diagnosis.</p></div>
            <a class="btn btn-primary" href="assessment.php">Start an assessment</a>
        </div>
        <?php if ($assessments === []): ?>
            <div class="empty-state"><div class="feature-number mx-auto">+</div><h2 class="h4 fw-bold mt-3">Your history starts here</h2><p class="text-secondary">Complete an assessment to see your factors, screening category, and suggestions.</p><a class="btn btn-primary" href="assessment.php">Begin assessment</a></div>
        <?php else: ?>
            <div class="row g-4">
                <div class="col-lg-7"><div class="content-card h-100"><div class="d-flex justify-content-between align-items-center mb-3"><div><h2 class="h5 fw-bold mb-1">Screening score history</h2><p class="small text-secondary mb-0">Rule-based points only; not a medical trend or diagnosis.</p></div><a class="small" href="history.php">View all</a></div><div class="chart-wrap"><canvas id="assessmentChart" aria-label="Educational screening score history chart" role="img"></canvas></div></div></div>
                <div class="col-lg-5"><div class="content-card h-100"><h2 class="h5 fw-bold mb-3">Recent assessments</h2><?php foreach (array_slice($assessments, 0, 5) as $assessment): ?><a class="history-row" href="result.php?id=<?= (int) $assessment['id'] ?>"><span><strong><?= h(date('M j, Y', strtotime($assessment['created_at']))) ?></strong><small class="d-block text-secondary"><?= (int) $assessment['score'] ?> educational points</small></span><span class="risk-badge risk-<?= h($assessment['risk_level']) ?>"><?= h(ucfirst($assessment['risk_level'])) ?></span></a><?php endforeach; ?></div></div>
            </div>
        <?php endif; ?>
        <div class="callout-note mt-4">A result cannot diagnose or rule out diabetes. Talk with a healthcare professional about your health and how to interpret any readings.</div>
    </div>
</section>
<?php if ($assessments !== []): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script id="assessment-chart-data" type="application/json"><?= json_encode([
    'labels' => array_map(static fn(array $row): string => date('M j', strtotime($row['created_at'])), array_reverse($assessments)),
    'scores' => array_map(static fn(array $row): int => (int) $row['score'], array_reverse($assessments)),
], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR) ?></script>
<script src="assets/js/dashboard.js"></script>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
