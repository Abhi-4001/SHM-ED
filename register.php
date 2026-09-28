<?php
require __DIR__ . '/includes/bootstrap.php';
if (isset($_SESSION['user_id'])) {
    redirect('dashboard.php');
}

$errors = [];
$name = '';
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim((string) ($_POST['name'] ?? ''));
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');
    $confirmation = (string) ($_POST['password_confirmation'] ?? '');

    if ($name === '' || mb_strlen($name) > 100) {
        $errors[] = 'Enter a name of up to 100 characters.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) {
        $errors[] = 'Enter a valid email address.';
    }
    if (strlen($password) < 10 || strlen($password) > 200) {
        $errors[] = 'Use a password between 10 and 200 characters.';
    }
    if ($password !== $confirmation) {
        $errors[] = 'The password confirmation does not match.';
    }

    if ($errors === []) {
        $statement = db()->prepare('INSERT INTO users (name, email, password_hash) VALUES (:name, :email, :password_hash)');
        try {
            $statement->execute([
                'name' => $name,
                'email' => $email,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            ]);
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') {
                $errors[] = 'An account with that email address already exists.';
            } else {
                throw $exception;
            }
        }
        if ($errors === []) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) db()->lastInsertId();
            $_SESSION['user_name'] = $name;
            unset($_SESSION['_csrf']);
            redirect('dashboard.php');
        }
    }
}

$pageTitle = 'Create account';
$activePage = 'register';
require __DIR__ . '/includes/header.php';
?>
<section class="auth-section section-space">
    <div class="container">
        <div class="auth-card">
            <span class="eyebrow">Your private account</span>
            <h1 class="h2 fw-bold mt-2">Create an account</h1>
            <p class="text-secondary">Your assessment history will be linked to this account.</p>
            <?php if ($errors !== []): ?><div class="alert alert-danger" role="alert"><ul class="mb-0"><?php foreach ($errors as $error): ?><li><?= h($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
            <form method="post" action="register.php" novalidate>
                <input type="hidden" name="_csrf" value="<?= h(csrf_token()) ?>">
                <div class="mb-3"><label class="form-label" for="name">Name</label><input class="form-control" id="name" name="name" maxlength="100" autocomplete="name" required value="<?= h($name) ?>"></div>
                <div class="mb-3"><label class="form-label" for="email">Email</label><input class="form-control" id="email" name="email" type="email" maxlength="254" autocomplete="email" required value="<?= h($email) ?>"></div>
                <div class="mb-3"><label class="form-label" for="password">Password</label><input class="form-control" id="password" name="password" type="password" minlength="10" maxlength="200" autocomplete="new-password" required><div class="form-text">Use at least 10 characters.</div></div>
                <div class="mb-4"><label class="form-label" for="password_confirmation">Confirm password</label><input class="form-control" id="password_confirmation" name="password_confirmation" type="password" maxlength="200" autocomplete="new-password" required></div>
                <button class="btn btn-primary btn-lg w-100" type="submit">Create account</button>
            </form>
            <p class="small text-secondary mt-4 mb-0">Educational screening only, not a diagnosis. Use a unique password and contact a clinician with health concerns.</p>
            <p class="mt-3 mb-0 small">Already registered? <a href="login.php">Log in</a></p>
        </div>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
