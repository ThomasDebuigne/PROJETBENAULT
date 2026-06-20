<?php

require_once('config.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: contact.php');
    exit;
}

$nom = trim($_POST['nom'] ?? '');
$prenom = trim($_POST['prenom'] ?? '');
$email = trim($_POST['email'] ?? '');
$telephone = trim($_POST['telephone'] ?? '');
$annonce = trim($_POST['annonce'] ?? '');
$message = trim($_POST['message'] ?? '');
$rgpd = isset($_POST['rgpd']);

if (
    empty($nom) ||
    empty($prenom) ||
    empty($email) ||
    empty($telephone) ||
    empty($annonce) ||
    empty($message) ||
    !$rgpd
) {
    header('Location: contact.php?erreur=champs');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: contact.php?erreur=email');
    exit;
}

$annoncesAutorisees = [
    'monteur-charpente-metallique',
    'profil-polyvalent-atelier'
];

if (!in_array($annonce, $annoncesAutorisees, true)) {
    header('Location: contact.php?erreur=annonce');
    exit;
}

function nettoyerNom($texte) {
    $texte = strtolower(trim($texte));

    $texte = str_replace(
        ['à', 'á', 'â', 'ä', 'ã', 'å', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'ö', 'õ', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ'],
        ['a', 'a', 'a', 'a', 'a', 'a', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y'],
        $texte
    );

    $texte = preg_replace('/[^a-z0-9]+/', '_', $texte);
    $texte = trim($texte, '_');

    return $texte ?: 'candidat';
}

function supprimerDossier($dossier) {
    if (!is_dir($dossier)) {
        return;
    }

    $elements = scandir($dossier);

    foreach ($elements as $element) {
        if ($element === '.' || $element === '..') {
            continue;
        }

        $chemin = $dossier . DIRECTORY_SEPARATOR . $element;

        if (is_dir($chemin)) {
            supprimerDossier($chemin);
        } else {
            unlink($chemin);
        }
    }

    rmdir($dossier);
}

function enregistrerFichier($champ, $prefixe, $dossierCandidat) {
    if (!isset($_FILES[$champ])) {
        return [
            'success' => false,
            'erreur' => $champ === 'cv' ? 'upload_cv' : 'upload_lettre'
        ];
    }

    if ($_FILES[$champ]['error'] !== UPLOAD_ERR_OK) {
        if (
            $_FILES[$champ]['error'] === UPLOAD_ERR_INI_SIZE ||
            $_FILES[$champ]['error'] === UPLOAD_ERR_FORM_SIZE
        ) {
            return [
                'success' => false,
                'erreur' => $champ === 'cv' ? 'taille_cv' : 'taille_lettre'
            ];
        }

        return [
            'success' => false,
            'erreur' => $champ === 'cv' ? 'upload_cv' : 'upload_lettre'
        ];
    }

    $tailleMax = 6 * 1024 * 1024;

    if ($_FILES[$champ]['size'] > $tailleMax) {
        return [
            'success' => false,
            'erreur' => $champ === 'cv' ? 'taille_cv' : 'taille_lettre'
        ];
    }

    $nomOriginal = $_FILES[$champ]['name'];
    $extension = strtolower(pathinfo($nomOriginal, PATHINFO_EXTENSION));

    $extensionsAutorisees = ['pdf', 'doc', 'docx', 'png', 'jpg', 'jpeg'];

    if (!in_array($extension, $extensionsAutorisees, true)) {
        return [
            'success' => false,
            'erreur' => $champ === 'cv' ? 'format_cv' : 'format_lettre'
        ];
    }

    $mimesAutorises = [
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg'
    ];

    if (!isset($mimesAutorises[$extension])) {
        return [
            'success' => false,
            'erreur' => $champ === 'cv' ? 'format_cv' : 'format_lettre'
        ];
    }

    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $_FILES[$champ]['tmp_name']);
        finfo_close($finfo);

        if ($mime !== $mimesAutorises[$extension]) {
            return [
                'success' => false,
                'erreur' => $champ === 'cv' ? 'format_cv' : 'format_lettre'
            ];
        }
    }

    if (!is_dir($dossierCandidat)) {
        if (!mkdir($dossierCandidat, 0755, true)) {
            return [
                'success' => false,
                'erreur' => 'dossier'
            ];
        }
    }

    $nomStocke = $prefixe . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
    $cheminComplet = $dossierCandidat . '/' . $nomStocke;

    if (!move_uploaded_file($_FILES[$champ]['tmp_name'], $cheminComplet)) {
        return [
            'success' => false,
            'erreur' => $champ === 'cv' ? 'upload_cv' : 'upload_lettre'
        ];
    }

    return [
        'success' => true,
        'nom_original' => $nomOriginal,
        'nom_stocke' => $nomStocke
    ];
}

$nomNettoye = nettoyerNom($nom);
$prenomNettoye = nettoyerNom($prenom);

$nomDossierCandidat = date('Ymd_His') . '_' . $nomNettoye . '_' . $prenomNettoye . '_' . bin2hex(random_bytes(4));

$dossierBase = __DIR__ . '/../prive/candidatures/';
$dossierCandidat = $dossierBase . $nomDossierCandidat;

if (!is_dir($dossierBase)) {
    if (!mkdir($dossierBase, 0755, true)) {
        header('Location: contact.php?erreur=dossier');
        exit;
    }
}

$cv = enregistrerFichier('cv', 'cv', $dossierCandidat);

if (!$cv['success']) {
    header('Location: contact.php?erreur=' . $cv['erreur']);
    exit;
}

$lettre = enregistrerFichier('lettre_motivation', 'lettre_motivation', $dossierCandidat);

if (!$lettre['success']) {
    supprimerDossier($dossierCandidat);
    header('Location: contact.php?erreur=' . $lettre['erreur']);
    exit;
}

try {
    $conn = getConnexion();

    $stmt = $conn->prepare("
        INSERT INTO candidatures (
            nom,
            prenom,
            email,
            telephone,
            annonce,
            message,
            cv_nom_original,
            cv_fichier_stocke,
            lettre_nom_original,
            lettre_fichier_stocke
        ) VALUES (
            :nom,
            :prenom,
            :email,
            :telephone,
            :annonce,
            :message,
            :cv_nom_original,
            :cv_fichier_stocke,
            :lettre_nom_original,
            :lettre_fichier_stocke
        )
    ");

    $stmt->execute([
        ':nom' => $nom,
        ':prenom' => $prenom,
        ':email' => $email,
        ':telephone' => $telephone,
        ':annonce' => $annonce,
        ':message' => $message,
        ':cv_nom_original' => $cv['nom_original'],
        ':cv_fichier_stocke' => $nomDossierCandidat . '/' . $cv['nom_stocke'],
        ':lettre_nom_original' => $lettre['nom_original'],
        ':lettre_fichier_stocke' => $nomDossierCandidat . '/' . $lettre['nom_stocke']
    ]);

    header('Location: contact.php?success=1');
    exit;

} catch (Exception $e) {
    supprimerDossier($dossierCandidat);
    header('Location: contact.php?erreur=bdd');
    exit;
}
