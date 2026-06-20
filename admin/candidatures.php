<?php

declare(strict_types=1);

require_once('_auth.php');
require_once('../php/config.php');

if (empty($_SESSION['csrf_suppression'])) {
    $_SESSION['csrf_suppression'] = bin2hex(random_bytes(32));
}

$conn = getConnexion();

$stmt = $conn->query("
    SELECT
        id,
        nom,
        prenom,
        email,
        telephone,
        annonce,
        message,
        cv_nom_original,
        cv_fichier_stocke,
        lettre_nom_original,
        lettre_fichier_stocke,
        date_envoi
    FROM candidatures
    ORDER BY date_envoi DESC
");

$candidatures = $stmt->fetchAll(PDO::FETCH_ASSOC);

function formatAnnonce(?string $annonce): string {
    if ($annonce === 'monteur-charpente-metallique') {
        return 'Monteurs en charpente métallique';
    }

    if ($annonce === 'profil-polyvalent-atelier') {
        return 'Profils polyvalents et autonomes pour atelier';
    }

    return $annonce ?? '';
}

function e(?string $valeur): string {
    return htmlspecialchars($valeur ?? '', ENT_QUOTES, 'UTF-8');
}

$messageSucces = $_GET['supprime'] ?? '';
$messageErreur = $_GET['erreur'] ?? '';
?>

<!DOCTYPE html>
<html lang="fr-FR">
<head>
    <meta charset="UTF-8">
    <title>Administration - Candidatures</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="stylesheet" href="../css/candidatures.css">
</head>

<body>
    <main class="admin-container">
        <div class="admin-header">
            <div>
                <h1>Candidatures reçues</h1>
                <p class="admin-subtitle">
                    Liste des candidatures envoyées depuis le site.
                </p>
            </div>

            <a href="logout.php" class="logout-link">Déconnexion</a>
        </div>

        <?php if ($messageSucces === '1'): ?>
            <div class="admin-alert success">
                La candidature a bien été supprimée.
            </div>
        <?php endif; ?>

        <?php if (!empty($messageErreur)): ?>
            <div class="admin-alert error">
                <?php
                    if ($messageErreur === 'csrf') {
                        echo 'Erreur de sécurité. Merci de réessayer.';
                    } elseif ($messageErreur === 'id') {
                        echo 'Identifiant de candidature invalide.';
                    } elseif ($messageErreur === 'introuvable') {
                        echo 'Candidature introuvable.';
                    } elseif ($messageErreur === 'bdd') {
                        echo 'Erreur lors de la suppression en base de données.';
                    } else {
                        echo 'Une erreur est survenue.';
                    }
                ?>
            </div>
        <?php endif; ?>

        <?php if (empty($candidatures)): ?>
            <div class="empty-state">
                Aucune candidature pour le moment.
            </div>
        <?php else: ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Candidat</th>
                            <th>Contact</th>
                            <th>Annonce</th>
                            <th>Message</th>
                            <th>CV</th>
                            <th>Lettre</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($candidatures as $candidature): ?>
                            <tr>
                                <td>
                                    <?php echo e($candidature['date_envoi'] ?? ''); ?>
                                </td>

                                <td>
                                    <strong>
                                        <?php echo e(($candidature['prenom'] ?? '') . ' ' . ($candidature['nom'] ?? '')); ?>
                                    </strong>
                                </td>

                                <td>
                                    <?php if (!empty($candidature['email'])): ?>
                                        <a href="mailto:<?php echo e($candidature['email']); ?>">
                                            <?php echo e($candidature['email']); ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="muted">Email absent</span>
                                    <?php endif; ?>

                                    <br>

                                    <?php if (!empty($candidature['telephone'])): ?>
                                        <a href="tel:<?php echo e($candidature['telephone']); ?>">
                                            <?php echo e($candidature['telephone']); ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="muted">Téléphone absent</span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <?php echo e(formatAnnonce($candidature['annonce'] ?? '')); ?>
                                </td>

                                <td class="message-cell">
                                    <?php echo nl2br(e($candidature['message'] ?? '')); ?>
                                </td>

                                <td>
                                    <?php if (!empty($candidature['cv_fichier_stocke'])): ?>
                                        <a 
                                            href="download.php?id=<?php echo urlencode((string) $candidature['id']); ?>&type=cv" 
                                            class="file-link"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        >
                                            Voir CV
                                        </a>

                                        <?php if (!empty($candidature['cv_nom_original'])): ?>
                                            <div class="file-name">
                                                <?php echo e($candidature['cv_nom_original']); ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="muted">Aucun CV</span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <?php if (!empty($candidature['lettre_fichier_stocke'])): ?>
                                        <a 
                                            href="download.php?id=<?php echo urlencode((string) $candidature['id']); ?>&type=lettre" 
                                            class="file-link"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        >
                                            Voir lettre
                                        </a>

                                        <?php if (!empty($candidature['lettre_nom_original'])): ?>
                                            <div class="file-name">
                                                <?php echo e($candidature['lettre_nom_original']); ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="muted">Aucune lettre</span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <form 
                                        method="post" 
                                        action="supprimer-candidature.php" 
                                        onsubmit="return confirm('Voulez-vous vraiment supprimer cette candidature ? Cette action est définitive.');"
                                    >
                                        <input 
                                            type="hidden" 
                                            name="id" 
                                            value="<?php echo e((string) $candidature['id']); ?>"
                                        >

                                        <input 
                                            type="hidden" 
                                            name="csrf_token" 
                                            value="<?php echo e($_SESSION['csrf_suppression']); ?>"
                                        >

                                        <button type="submit" class="delete-button">
                                            Supprimer
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
