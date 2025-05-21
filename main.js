document.addEventListener('DOMContentLoaded', function() {
    const slides = document.querySelectorAll('.banner-slide');
    const prevBtn = document.getElementById('banner-prev');
    const nextBtn = document.getElementById('banner-next');
    let current = 0;

    function showSlide(idx) {
        slides.forEach((slide, i) => {
            slide.style.display = (i === idx) ? 'block' : 'none';
        });
    }

    prevBtn.addEventListener('click', function() {
        current = (current - 1 + slides.length) % slides.length;
        showSlide(current);
    });

    nextBtn.addEventListener('click', function() {
        current = (current + 1) % slides.length;
        showSlide(current);
    });

    showSlide(current);
});