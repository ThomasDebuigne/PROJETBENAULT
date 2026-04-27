document.addEventListener("DOMContentLoaded", function() {
    const sliderContainer = document.querySelector('.js-slider');
    const slides = document.querySelectorAll('.js-slider .slide');
    
    if (!sliderContainer || slides.length <= 1) return;

    let current = 0;
    let autoSlideInterval;

    function nextSlide() {
        slides[current].classList.remove('active');

        current = (current + 1) % slides.length;


        slides[current].classList.add('active');
        
        resetTimer();
    }

    function resetTimer() {
        clearInterval(autoSlideInterval);
        autoSlideInterval = setInterval(nextSlide, 4000);
    }

    sliderContainer.addEventListener('click', function() {
        nextSlide();
    });

    autoSlideInterval = setInterval(nextSlide, 4000);
});