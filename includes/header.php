<?php
$pageTitle = $pageTitle ?? 'Smart Health Monitoring';
$activePage = $activePage ?? '';
$signedIn = isset($_SESSION['user_id']);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Private, educational health screening and personal assessment history.">
    <title><?= h($pageTitle) ?> | Smart Health Monitoring</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/app.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php"><span class="brand-mark">+</span> VitalPath</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <div class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                <?php if ($signedIn): ?>
                    <a class="nav-link <?= $activePage === 'dashboard' ? 'active' : '' ?>" href="dashboard.php">Dashboard</a>
                    <a class="nav-link <?= $activePage === 'assessment' ? 'active' : '' ?>" href="assessment.php">New assessment</a>
                    <a class="nav-link <?= $activePage === 'history' ? 'active' : '' ?>" href="history.php">History</a>
                    <form method="post" action="logout.php" class="ms-lg-2">
                        <input type="hidden" name="_csrf" value="<?= h(csrf_token()) ?>">
                        <button class="btn btn-outline-secondary btn-sm" type="submit">Log out</button>
                    </form>
                <?php else: ?>
                    <a class="nav-link <?= $activePage === 'login' ? 'active' : '' ?>" href="login.php">Log in</a>
                    <a class="btn btn-primary btn-sm px-3" href="register.php">Create account</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
<div class="disclaimer-strip">
    Educational screening only — not a diagnosis, medical advice, or a substitute for care from a qualified clinician.
</div>
<main>
