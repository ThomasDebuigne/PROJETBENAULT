<?php

declare(strict_types=1);

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

if (isset($_SESSION['admin_connecte']) && $_SESSION['admin_connecte'] === true) {
    header('Location: candidatures.php');
    exit;
}

/*
    Identifiant admin.
    Change-le si tu veux éviter "admin".
*/
$admin_identifiant = 'admin';

/*
    Mot de passe hashé.
    Ne mets jamais le vrai mot de passe ici.
*/
$admin_mot_de_passe_hash = '$2y$10$9zW6LuQaKD1v1/0TG1FAhe.nLAUt1oQbd.vdsWQVfqZDvE4HKDT9i';

$message = '';

/*
    Token CSRF pour éviter les soumissions externes.
*/
if (empty($_SESSION['csrf_login'])) {
    $_SESSION['csrf_login'] = bin2hex(random_bytes(32));
}

/*
    Limitation simple des tentatives.
    Ici : 5 essais puis blocage 15 minutes.
*/
if (!isset($_SESSION['login_tentatives'])) {
    $_SESSION['login_tentatives'] = 0;
}

if (!isset($_SESSION['login_blocage_jusqua'])) {
    $_SESSION['login_blocage_jusqua'] = 0;
}

if (isset($_GET['timeout'])) {
    $message = 'Votre session a expiré. Merci de vous reconnecter.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_SESSION['login_blocage_jusqua'] > time()) {
        $minutesRestantes = ceil(($_SESSION['login_blocage_jusqua'] - time()) / 60);
        $message = 'Trop de tentatives. Réessayez dans ' . $minutesRestantes . ' minute(s).';
    } else {
        $csrf = $_POST['csrf_token'] ?? '';

        if (!hash_equals($_SESSION['csrf_login'], $csrf)) {
            $message = 'Erreur de sécurité. Merci de réessayer.';
        } else {
            $identifiant = trim($_POST['identifiant'] ?? '');
            $mot_de_passe = $_POST['mot_de_passe'] ?? '';

            if ($identifiant === '' || $mot_de_passe === '') {
                $message = 'Veuillez remplir tous les champs.';
            } elseif (
                hash_equals($admin_identifiant, $identifiant) &&
                password_verify($mot_de_passe, $admin_mot_de_passe_hash)
            ) {
                session_regenerate_id(true);

                $_SESSION['admin_connecte'] = true;
                $_SESSION['admin_identifiant'] = $admin_identifiant;
                $_SESSION['admin_derniere_activite'] = time();

                $_SESSION['login_tentatives'] = 0;
                $_SESSION['login_blocage_jusqua'] = 0;
                $_SESSION['csrf_login'] = bin2hex(random_bytes(32));

                header('Location: candidatures.php');
                exit;
            } else {
                $_SESSION['login_tentatives']++;

                if ($_SESSION['login_tentatives'] >= 5) {
                    $_SESSION['login_blocage_jusqua'] = time() + 900;
                    $_SESSION['login_tentatives'] = 0;
                    $message = 'Trop de tentatives. Réessayez dans 15 minutes.';
                } else {
                    $message = 'Identifiant ou mot de passe incorrect.';
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr-FR">
<head>
    <meta charset="UTF-8">
    <title>Connexion administration</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="stylesheet" href="../css/candidatures.css">
</head>

<body>
    <main class="login-container">
        <form method="post" class="login-form" autocomplete="on">
            <h1>Connexion</h1>

            <?php if (!empty($message)): ?>
                <div class="login-error">
                    <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <input 
                type="hidden" 
                name="csrf_token" 
                value="<?php echo htmlspecialchars($_SESSION['csrf_login'], ENT_QUOTES, 'UTF-8'); ?>"
            >

            <input 
                type="text" 
                name="identifiant" 
                placeholder="Identifiant" 
                autocomplete="username"
                required
            >

            <input 
                type="password" 
                name="mot_de_passe" 
                placeholder="Mot de passe" 
                autocomplete="current-password"
                required
            >

            <button type="submit">Se connecter</button>
        </form>
    </main>
</body>
</html>
