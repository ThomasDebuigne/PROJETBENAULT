<?php

declare(strict_types=1);

require_once('_auth.php');
require_once('../php/config.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: candidatures.php');
    exit;
}

$csrf = $_POST['csrf_token'] ?? '';

if (
    empty($_SESSION['csrf_suppression']) ||
    !hash_equals($_SESSION['csrf_suppression'], $csrf)
) {
    header('Location: candidatures.php?erreur=csrf');
    exit;
}

$id = $_POST['id'] ?? '';

if (!ctype_digit((string) $id)) {
    header('Location: candidatures.php?erreur=id');
    exit;
}

function supprimerDossierRecursif(string $dossier): void {
    if (!is_dir($dossier)) {
        return;
    }

    $elements = scandir($dossier);

    if ($elements === false) {
        return;
    }

    foreach ($elements as $element) {
        if ($element === '.' || $element === '..') {
            continue;
        }

        $chemin = $dossier . DIRECTORY_SEPARATOR . $element;

        if (is_dir($chemin)) {
            supprimerDossierRecursif($chemin);
        } elseif (is_file($chemin)) {
            @unlink($chemin);
        }
    }

    @rmdir($dossier);
}

try {
    $conn = getConnexion();

    $stmt = $conn->prepare("
        SELECT 
            cv_fichier_stocke,
            lettre_fichier_stocke
        FROM candidatures
        WHERE id = :id
        LIMIT 1
    ");

    $stmt->execute([
        ':id' => (int) $id
    ]);

    $candidature = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$candidature) {
        header('Location: candidatures.php?erreur=introuvable');
        exit;
    }

    /*
        Exemple :
        cv_fichier_stocke = 20260620_debuigne_thomas_abcd/cv_xxx.pdf

        On veut supprimer le dossier :
        prive/candidatures/20260620_debuigne_thomas_abcd
    */

    $baseCandidatures = realpath(__DIR__ . '/../prive/candidatures');

    $dossierASupprimer = null;

    if ($baseCandidatures !== false && !empty($candidature['cv_fichier_stocke'])) {
        $cheminCv = (string) $candidature['cv_fichier_stocke'];

        if (
            !str_contains($cheminCv, '..') &&
            !str_contains($cheminCv, "\0") &&
            !preg_match('#^([a-zA-Z]:|/|\\\\)#', $cheminCv)
        ) {
            $parties = explode('/', str_replace('\\', '/', $cheminCv));
            $nomDossier = $parties[0] ?? '';

            if ($nomDossier !== '') {
                $cheminDossier = realpath($baseCandidatures . DIRECTORY_SEPARATOR . $nomDossier);

                if (
                    $cheminDossier !== false &&
                    str_starts_with($cheminDossier, $baseCandidatures . DIRECTORY_SEPARATOR) &&
                    is_dir($cheminDossier)
                ) {
                    $dossierASupprimer = $cheminDossier;
                }
            }
        }
    }

    /*
        Suppression en base d'abord.
        Si elle réussit, on tente ensuite de supprimer les fichiers.
    */
    $stmtDelete = $conn->prepare("
        DELETE FROM candidatures
        WHERE id = :id
        LIMIT 1
    ");

    $stmtDelete->execute([
        ':id' => (int) $id
    ]);

    if ($stmtDelete->rowCount() < 1) {
        header('Location: candidatures.php?erreur=introuvable');
        exit;
    }

    if ($dossierASupprimer !== null) {
        supprimerDossierRecursif($dossierASupprimer);
    }

    $_SESSION['csrf_suppression'] = bin2hex(random_bytes(32));

    header('Location: candidatures.php?supprime=1');
    exit;

} catch (Exception $e) {
    header('Location: candidatures.php?erreur=bdd');
    exit;
}
