<?php
require __DIR__ . '/includes/bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Use the sign-out form to log out. Educational screening only—not a diagnosis.');
}

verify_csrf();
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $parameters = session_get_cookie_params();
    setcookie(session_name(), '', [
        'expires' => time() - 42000,
        'path' => $parameters['path'],
        'domain' => $parameters['domain'],
        'secure' => $parameters['secure'],
        'httponly' => $parameters['httponly'],
        'samesite' => $parameters['samesite'] ?? 'Lax',
    ]);
}
session_destroy();
redirect('index.php');
