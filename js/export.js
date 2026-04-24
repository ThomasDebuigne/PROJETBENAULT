// On attend que le HTML soit totalement chargé avant de lancer le script
document.addEventListener("DOMContentLoaded", function() {
    
    // Sélectionne tous les éléments ayant la classe 'pin' (NodeList)
    const listeMarqueurs = document.querySelectorAll('.pin');

    listeMarqueurs.forEach(marqueur => {
        // Ajout d'un écouteur d'événement sur chaque point de la carte
        marqueur.addEventListener('click', function(evenement) {
            
            // Empêche le comportement par défaut (ex: si le pin était dans un lien <a>)
            evenement.preventDefault();

            // Extraction du nom du pays via la balise <span> du Tooltip
            const elementTextePays = this.querySelector('span');
            if (!elementTextePays) return; // Sécurité : si pas de texte, on arrête

            /**
             * NORMALISATION DE LA CHAÎNE DE CARACTÈRES
             * But : Transformer "Thaïlande" en "thailande" pour correspondre aux ID HTML
             */
            const identifiantPays = elementTextePays.textContent
                .toLowerCase()              // 1. Passage en minuscules
                .normalize("NFD")           // 2. Décompose les caractères accentués (é -> e + ´)
                .replace(/[\u0300-\u036f]/g, "") // 3. Supprime les accents via une Expression Régulière
                .trim();                    // 4. Nettoie les espaces inutiles autour

            // Sélection du bloc cible par son ID (ex: document.getElementById('cuba'))
            const blocPaysCible = document.getElementById(identifiantPays);

            if (blocPaysCible) {
                /*
                 * getBoundingClientRect().top : donne la position par rapport à la vue actuelle (viewport)
                 * window.pageYOffset : donne la distance déjà scrollée depuis le haut du document
                 */
                const positionRelativeBloc = blocPaysCible.getBoundingClientRect().top;
                const distanceDefilementActuelle = window.pageYOffset || document.documentElement.scrollTop;
                
                // Constante pour compenser la barre de navigation fixe (qui recouvre le contenu)
                const hauteurHeaderFixe = 130;

                // Formule : Position relative + Défilement total - Taille du header
                const positionFinaleScroll = positionRelativeBloc + distanceDefilementActuelle - hauteurHeaderFixe;

                /*
                 * top : la destination en pixels
                 * behavior: 'smooth' : active l'animation 
                 */
                window.scrollTo({
                    top: positionFinaleScroll,
                    behavior: 'smooth'
                });
            }
        });
    });
});