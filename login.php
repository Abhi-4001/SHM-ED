<?php
require __DIR__ . '/includes/bootstrap.php';
if (isset($_SESSION['user_id'])) {
    redirect('dashboard.php');
}

$error = '';
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');
    $statement = db()->prepare('SELECT id, name, password_hash FROM users WHERE email = :email LIMIT 1');
    $statement->execute(['email' => $email]);
    $user = $statement->fetch();
    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['user_name'] = $user['name'];
        unset($_SESSION['_csrf']);
        redirect('dashboard.php');
    }
    $error = 'The email or password was not recognized.';
}

$pageTitle = 'Log in';
$activePage = 'login';
require __DIR__ . '/includes/header.php';
?>
<section class="auth-section section-space">
    <div class="container">
        <div class="auth-card">
            <span class="eyebrow">Welcome back</span>
            <h1 class="h2 fw-bold mt-2">Log in</h1>
            <p class="text-secondary">View your private educational screening history.</p>
            <?php if ($error !== ''): ?><div class="alert alert-danger" role="alert"><?= h($error) ?></div><?php endif; ?>
            <form method="post" action="login.php">
                <input type="hidden" name="_csrf" value="<?= h(csrf_token()) ?>">
                <div class="mb-3"><label class="form-label" for="email">Email</label><input class="form-control" id="email" name="email" type="email" maxlength="254" autocomplete="email" required value="<?= h($email) ?>"></div>
                <div class="mb-4"><label class="form-label" for="password">Password</label><input class="form-control" id="password" name="password" type="password" autocomplete="current-password" required></div>
                <button class="btn btn-primary btn-lg w-100" type="submit">Log in</button>
            </form>
            <p class="small text-secondary mt-4 mb-0">Educational screening only, not a diagnosis or medical advice.</p>
            <p class="mt-3 mb-0 small">New here? <a href="register.php">Create an account</a></p>
        </div>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
