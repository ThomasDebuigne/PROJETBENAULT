document.addEventListener('DOMContentLoaded', () => {
    const accordionHeaders = document.querySelectorAll('.accordion-header');
    const pins = document.querySelectorAll('.pin');

    // Fonction pour gérer l'ouverture/fermeture
    function handleAccordion(item) {
        const content = item.querySelector('.accordion-content');
        const statusIcon = item.querySelector('.status-icon');
        const flag = item.querySelector('.flag-icon');

        // Fermer les autres accordéons
        document.querySelectorAll('.accordion-item').forEach(otherItem => {
            if (otherItem !== item && otherItem.classList.contains('active')) {
                otherItem.classList.remove('active');
                otherItem.querySelector('.accordion-content').style.maxHeight = null;
                otherItem.querySelector('.status-icon').innerText = "+";
                if(otherItem.querySelector('.flag-icon')) {
                    otherItem.querySelector('.flag-icon').style.transform = "rotate(0deg)";
                }
            }
        });

        // Toggle l'item actuel
        const isActive = item.classList.toggle('active');
        if (isActive) {
            content.style.maxHeight = content.scrollHeight + "px";
            statusIcon.innerText = "-";
            if(flag) {
                flag.style.transform = "rotate(360deg)";
                flag.style.transition = "0.6s";
            }
        } else {
            content.style.maxHeight = null;
            statusIcon.innerText = "+";
            if(flag) flag.style.transform = "rotate(0deg)";
        }
    }

    // Événement sur les headers
    accordionHeaders.forEach(header => {
        header.addEventListener('click', () => {
            handleAccordion(header.parentElement);
        });
    });

    // Événement sur les pins de la carte
    pins.forEach(pin => {
        pin.addEventListener('click', (e) => {
            e.preventDefault();
            const targetId = pin.getAttribute('href').substring(1);
            const targetElement = document.getElementById(targetId);

            if (targetElement) {
                if (!targetElement.classList.contains('active')) {
                    handleAccordion(targetElement);
                }
                // Défilement fluide vers la section
                targetElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    });
});