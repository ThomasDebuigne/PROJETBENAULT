<?php

declare(strict_types=1);

require_once('_auth.php');
require_once('../php/config.php');

$id = $_GET['id'] ?? '';
$type = $_GET['type'] ?? '';

if (!ctype_digit((string) $id)) {
    http_response_code(400);
    exit('ID invalide.');
}

$colonnesAutorisees = [
    'cv' => [
        'fichier' => 'cv_fichier_stocke',
        'original' => 'cv_nom_original'
    ],
    'lettre' => [
        'fichier' => 'lettre_fichier_stocke',
        'original' => 'lettre_nom_original'
    ]
];

if (!isset($colonnesAutorisees[$type])) {
    http_response_code(400);
    exit('Type de fichier invalide.');
}

$colonneFichier = $colonnesAutorisees[$type]['fichier'];
$colonneOriginal = $colonnesAutorisees[$type]['original'];

$conn = getConnexion();

$stmt = $conn->prepare("
    SELECT $colonneFichier, $colonneOriginal
    FROM candidatures
    WHERE id = :id
    LIMIT 1
");

$stmt->execute([
    ':id' => (int) $id
]);

$candidature = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$candidature || empty($candidature[$colonneFichier])) {
    http_response_code(404);
    exit('Fichier introuvable en base.');
}

$cheminEnBase = (string) $candidature[$colonneFichier];

/*
    Sécurité anti traversal :
    on refuse les chemins suspects.
*/
if (
    str_contains($cheminEnBase, '..') ||
    str_contains($cheminEnBase, "\0") ||
    preg_match('#^([a-zA-Z]:|/|\\\\)#', $cheminEnBase)
) {
    http_response_code(400);
    exit('Chemin invalide.');
}

$cheminRelatif = ltrim($cheminEnBase, '/\\');

$baseCandidatures = realpath(__DIR__ . '/../prive/candidatures');

if ($baseCandidatures === false) {
    http_response_code(500);
    exit('Dossier privé introuvable.');
}

$cheminComplet = realpath($baseCandidatures . DIRECTORY_SEPARATOR . $cheminRelatif);

if (
    $cheminComplet === false ||
    !str_starts_with($cheminComplet, $baseCandidatures . DIRECTORY_SEPARATOR) ||
    !is_file($cheminComplet) ||
    !is_readable($cheminComplet)
) {
    http_response_code(404);
    exit('Fichier introuvable sur le serveur.');
}

$extension = strtolower(pathinfo($cheminComplet, PATHINFO_EXTENSION));

$typesMime = [
    'pdf'  => 'application/pdf',
    'doc'  => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png'  => 'image/png'
];

if (!isset($typesMime[$extension])) {
    http_response_code(403);
    exit('Type de fichier non autorisé.');
}

$typeMime = $typesMime[$extension];

$nomOriginal = $candidature[$colonneOriginal] ?? basename($cheminComplet);
$nomOriginal = basename((string) $nomOriginal);

/*
    Nettoyage du nom envoyé au navigateur.
*/
$nomOriginal = str_replace(["\r", "\n", '"'], '', $nomOriginal);

if ($nomOriginal === '') {
    $nomOriginal = 'document.' . $extension;
}

header('Content-Type: ' . $typeMime);
header('Content-Disposition: inline; filename="' . $nomOriginal . '"');
header('Content-Length: ' . filesize($cheminComplet));
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

readfile($cheminComplet);
exit;
