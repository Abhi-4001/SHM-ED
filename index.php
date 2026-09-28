<?php
require __DIR__ . '/includes/bootstrap.php';
$pageTitle = 'A clearer next step';
require __DIR__ . '/includes/header.php';
?>
<section class="hero-section">
    <div class="container py-5 py-lg-6">
        <div class="row align-items-center g-5">
            <div class="col-lg-7">
                <span class="eyebrow">A thoughtful first step for your health</span>
                <h1 class="display-4 fw-bold mt-3">A clearer first step for diabetes-risk screening.</h1>
                <p class="lead text-secondary mt-4">Record a few health measures, see how a transparent educational screening rule considers them, and keep your personal assessment history in one place—without mistaking a screen for a diagnosis.</p>
                <div class="d-flex flex-wrap gap-3 mt-4">
                    <a class="btn btn-primary btn-lg px-4" href="register.php">Start a free assessment</a>
                    <a class="btn btn-outline-secondary btn-lg px-4" href="login.php">Log in</a>
                </div>
                <div class="small text-secondary mt-4"><span class="secure-dot"></span> Your assessments are private to your account.</div>
            </div>
            <div class="col-lg-5">
                <div class="hero-card">
                    <div class="d-flex align-items-center justify-content-between">
                        <div><div class="small text-secondary">A more informed conversation</div><div class="h4 fw-bold mb-0 mt-1">Start with the basics</div></div>
                        <div class="pulse-icon">+</div>
                    </div>
                    <div class="progress-item mt-4"><div class="d-flex justify-content-between small mb-2"><span>Health measures</span><span>Personal context</span></div><div class="progress"><div class="progress-bar" style="width:72%"></div></div></div>
                    <div class="insight-box mt-4"><strong>Designed for clarity</strong><p class="mb-0 mt-1 small">See which reported factors affected the result and what a sensible next step could be.</p></div>
                    <div class="small text-secondary mt-3">Not a diagnostic test. Never use a result to delay care or change medication.</div>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="section-space">
    <div class="container">
        <div class="text-center mb-5"><span class="eyebrow">Simple, transparent, private</span><h2 class="fw-bold mt-2">A useful overview, not a medical verdict</h2></div>
        <div class="row g-4">
            <div class="col-md-4"><div class="feature-card h-100"><div class="feature-number">01</div><h3 class="h5 fw-bold mt-3">Enter your measures</h3><p class="text-secondary mb-0">Share only the screening information requested. BMI is calculated from height and weight.</p></div></div>
            <div class="col-md-4"><div class="feature-card h-100"><div class="feature-number">02</div><h3 class="h5 fw-bold mt-3">See the reasoning</h3><p class="text-secondary mb-0">Review a cautious rule-based category, the factors behind it, and practical follow-up suggestions.</p></div></div>
            <div class="col-md-4"><div class="feature-card h-100"><div class="feature-number">03</div><h3 class="h5 fw-bold mt-3">Keep your history</h3><p class="text-secondary mb-0">Return to your own assessment records. They are accessible only after signing in to your account.</p></div></div>
        </div>
        <div class="callout-note mt-5"><strong>Important:</strong> Results are educational only—not a diagnosis or medical advice. Glucose and blood pressure readings depend on context and proper measurement. Discuss concerns with a qualified healthcare professional.</div>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
