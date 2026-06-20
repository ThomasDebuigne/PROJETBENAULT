<!DOCTYPE html>
<html lang="fr-FR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BENAULT - Contact</title>
    <link rel="icon" type="image/x-icon" href="../images/logo_BENAULT.png">
    <link href="../css/contact.css" rel="stylesheet">
    <link href="../css/commun.css" rel="stylesheet">

</head>

<body>
    <header>
        <div class="logo">
            <img src="../images/logo_BENAULT.png" alt="Logo BENAULT">
        </div>

        <nav>
            <ul>
                <li><a href="../html/index.html" class="nav__link">Accueil</a></li>
                <li><a href="../html/Calcul.html" class="nav__link">Calcul et Conception</a></li>
                <li><a href="../html/Fabrication.html" class="nav__link">Fabrication et Pose</a></li>
                <li><a href="../html/Realisation.html" class="nav__link">Réalisation</a></li>
                <li><a href="../html/Export.html" class="nav__link">Export</a></li>
                <li><a href="contact.php" class="nav__link active">Contact</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section class="hero">
            <h1 class="section__title">CONTACTEZ-NOUS</h1>
        </section>

        <div class="container">
            <section class="contact-grid">
                <a href="tel:+33328401351" class="contact-card">
                    <div class="card-icon">
                        <img src="../images/logo_telephone.png" alt="Téléphone">
                    </div>

                    <div class="card-body">
                        <h3>Appelez-nous</h3>
                        <span class="card-link">03.28.40.13.51</span>
                        <p>Disponible du lundi au vendredi<br>08:30-12:00 | 13:30-18:00</p>
                    </div>
                </a>

                <a href="mailto:contact@benault.com" class="contact-card">
                    <div class="card-icon">
                        <img src="../images/logo_email.png" alt="Email">
                    </div>

                    <div class="card-body">
                        <h3>Écrivez-nous</h3>
                        <span class="card-link">contact@benault.com</span>
                        <p>Nous traitons vos demandes<br>dans les plus brefs délais</p>
                    </div>
                </a>
            </section>

            <section class="offers-section">
                <h2 class="offers-title">NOS OFFRES</h2>
                <div class="offers-underline"></div>

                <div class="offers-list">
                    <a href="../documents/offres/offre1.pdf" class="offer-card" target="_blank">
                        <div class="offer-main">
                            <span class="offer-label">Offre d’emploi</span>
                            <h3>Monteurs en charpente métallique</h3>
                            <p>Déplacements France entière</p>
                        </div>

                        <div class="offer-arrow">→</div>
                    </a>

                    <a href="../documents/offres/offre2.pdf" class="offer-card" target="_blank">
                        <div class="offer-main">
                            <span class="offer-label">Offre d’emploi</span>
                            <h3>Profils polyvalents et autonomes pour atelier</h3>
                            <p>Charpente métallique</p>
                        </div>

                        <div class="offer-arrow">→</div>
                    </a>
                </div>
            </section>

            <section class="contact-form-section" id="formulaire-candidature">
                <h2 class="form-title">REJOIGNEZ-NOUS</h2>
                <div class="form-underline"></div>

                <?php if (isset($_GET['success'])): ?>
                    <div class="form-message success">
                        Votre candidature a bien été envoyée.
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['erreur'])): ?>
                    <div class="form-message error">
                        <?php
                            $erreur = $_GET['erreur'];

                            if ($erreur === 'champs') {
                                echo "Merci de remplir tous les champs obligatoires.";
                            } elseif ($erreur === 'email') {
                                echo "L'adresse e-mail n'est pas valide.";
                            } elseif ($erreur === 'annonce') {
                                echo "L'annonce sélectionnée n'est pas valide.";
                            } elseif ($erreur === 'taille_cv') {
                                echo "Le fichier CV dépasse la taille maximale autorisée de 6 Mo.";
                            } elseif ($erreur === 'taille_lettre') {
                                echo "La lettre de motivation dépasse la taille maximale autorisée de 6 Mo.";
                            } elseif ($erreur === 'format_cv') {
                                echo "Le format du CV n'est pas autorisé. Formats acceptés : PDF, DOC, DOCX, PNG, JPG, JPEG.";
                            } elseif ($erreur === 'format_lettre') {
                                echo "Le format de la lettre de motivation n'est pas autorisé. Formats acceptés : PDF, DOC, DOCX, PNG, JPG, JPEG.";
                            } elseif ($erreur === 'upload_cv') {
                                echo "Une erreur est survenue lors de l'envoi du CV.";
                            } elseif ($erreur === 'upload_lettre') {
                                echo "Une erreur est survenue lors de l'envoi de la lettre de motivation.";
                            } elseif ($erreur === 'dossier') {
                                echo "Erreur lors de la création du dossier de candidature.";
                            } elseif ($erreur === 'bdd') {
                                echo "Erreur lors de l'enregistrement. Merci de réessayer plus tard.";
                            } else {
                                echo "Une erreur est survenue. Merci de réessayer.";
                            }
                        ?>
                    </div>
                <?php endif; ?>

                <form class="contact-form" action="traitement-candidature.php" method="post" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="form-group">
                            <input type="text" name="nom" placeholder="Nom*" required>
                        </div>

                        <div class="form-group">
                            <input type="text" name="prenom" placeholder="Prénom*" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <input type="email" name="email" placeholder="E-mail*" required>
                        </div>

                        <div class="form-group">
                            <input type="tel" name="telephone" placeholder="Tel.*" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <select name="annonce" required>
                            <option value="" selected disabled hidden>
                                Pour quelle annonce souhaitez-vous postuler ?
                            </option>

                            <option value="monteur-charpente-metallique">
                                Monteurs en charpente métallique — Déplacements France entière
                            </option>

                            <option value="profil-polyvalent-atelier">
                                Profils polyvalents et autonomes pour atelier — Charpente métallique
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <textarea name="message" placeholder="Votre message*"></textarea>
                    </div>

                    <div class="form-file-group">
                        <label for="cv">Chargez votre CV *</label>
                        <input
                            type="file"
                            id="cv"
                            name="cv"
                            accept=".png,.jpeg,.jpg,.pdf,.doc,.docx"
                            required
                        >
                        <p>Formats acceptés : .png, .jpeg, .jpg, .pdf, .doc, .docx — Taille maximale : 6Mo</p>
                    </div>

                    <div class="form-file-group">
                        <label for="lettre-motivation">Chargez votre lettre de motivation *</label>
                        <input
                            type="file"
                            id="lettre-motivation"
                            name="lettre_motivation"
                            accept=".png,.jpeg,.jpg,.pdf,.doc,.docx"
                            required
                        >
                        <p>Formats acceptés : .png, .jpeg, .jpg, .pdf, .doc, .docx — Taille maximale : 6Mo</p>
                    </div>

                    <div class="form-consent">
                        <label>
                            <input type="checkbox" name="rgpd" required>
                            <span>
                                J’accepte que mes données soient utilisées dans le cadre du traitement de ma candidature.
                            </span>
                        </label>
                    </div>

                    <p class="rgpd-note">
                        Les informations transmises sont utilisées uniquement pour le traitement de votre candidature.
                    </p>

                    <div class="submit-wrapper">
                        <button type="submit" class="form-submit">Envoyer ma candidature</button>
                    </div>
                </form>
            </section>

            <section class="section--map">
                <h2 class="map-title">OÙ NOUS TROUVER ?</h2>

                <div class="map-wrapper">
                    <iframe
                        class="map-iframe"
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2524.475354508493!2d2.634125376878168!3d50.74823296587425!2m3!1f0!2f0!3f0!2m3!1i1024!2i768!4f13.1!3m3!1m2!1s0x47dd02086e3f6f19%3A0xc47e3a31c513df0d!2s237%20Rue%20Nationale%2C%2059270%20Fl%C3%AAtre!5e0!3m2!1sfr!2sfr!4v1710000000000"
                        allowfullscreen=""
                        loading="lazy">
                    </iframe>
                </div>
            </section>
        </div>
    </main>

    <footer>
        <div class="Description">
            <img class="Logo_footer" src="../images/logo_BENAULT.png" alt="Logo entreprise BENAULT">
            <br>
            <p>
                La société BENAULT est une entreprise de construction spécialisée dans l’étude,
                la conception et la réalisation d’ouvrages à ossature métallique : Bureaux,
                Logements, Industries, Commerces, Entrepôts, Bâtiments Publics
                Sportif, Scolaire, Santé, Bâtiments frigorifiques, Immeubles, Parkings, etc…
            </p>
        </div>

        <nav class="Contact">
            <ul>
                <li class="Gras">Contact</li>
                <br>
                <li>Téléphone : 03.28.40.13.51</li>
                <li>Adresse : 237 Rue Nationale, 59270 FLETRE FRANCE</li>
                <li>Fax : 03.28.40.17.44</li>
                <li>Adresse-mail : contact@benault.com</li>
            </ul>
        </nav>
    </footer>
</body>
</html>
