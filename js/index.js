console.log("JS CHARGE")

let slideIndex = 1;

document.addEventListener("DOMContentLoaded", () => {
    showSlides(slideIndex);
});

function plusSlides(n) {
    showSlides(slideIndex += n);
}

function currentSlide(n) {
    showSlides(slideIndex = n);
}

function showSlides(n) {
    let slides = document.getElementsByClassName("mySlides");

    if (slides.length === 0) return;

    if (n > slides.length) slideIndex = 1;
    if (n < 1) slideIndex = slides.length;

    for (let i = 0; i < slides.length; i++) {
        slides[i].className = "mySlides";
    }

    let current = slideIndex - 1;
    let prev = (current - 1 + slides.length) % slides.length;
    let next = (current + 1) % slides.length;

    slides[current].classList.add("active");
    slides[prev].classList.add("prevSlide");
    slides[next].classList.add("nextSlide");
}