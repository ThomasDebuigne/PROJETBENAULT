<?php

declare(strict_types=1);

/*
    Configuration de session plus sécurisée.
    Attention : session_set_cookie_params doit être appelé AVANT session_start().
*/

$sessionSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => $sessionSecure,
    'httponly' => true,
    'samesite' => 'Strict'
]);

session_start();

/*
    Durée maximale d'inactivité.
    Ici : 30 minutes.
*/
$sessionTimeout = 1800;

if (
    isset($_SESSION['admin_derniere_activite']) &&
    time() - $_SESSION['admin_derniere_activite'] > $sessionTimeout
) {
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();

        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();

    header('Location: login.php?timeout=1');
    exit;
}

$_SESSION['admin_derniere_activite'] = time();

/*
    Vérification de connexion admin.
*/
if (!isset($_SESSION['admin_connecte']) || $_SESSION['admin_connecte'] !== true) {
    header('Location: login.php');
    exit;
}
