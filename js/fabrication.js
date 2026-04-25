document.addEventListener("DOMContentLoaded", function() {
    const sliders = document.querySelectorAll('.js-slider');

    sliders.forEach(slider => {
        const slides = slider.querySelectorAll('.slide');
        let current = 0;

        function next() {
            slides[current].classList.remove('active');
            current = (current + 1) % slides.length;
            slides[current].classList.add('active');
        }

        let itv = setInterval(next, 6000);

        slider.addEventListener('click', () => {
            clearInterval(itv);
            next();
            itv = setInterval(next, 6000);
        });
    });
});