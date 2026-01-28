import gsap from 'gsap';

document.querySelectorAll('.blockxpert-product-slider').forEach((slider) => {
    const track = slider.querySelector('.slider-track');
    const cards = track.querySelectorAll('.product-card');
    const autoPlay = slider.dataset.autoplay === 'true';
    let index = 0;

    function goNext() {
        index = (index + 1) % cards.length;
        gsap.to(track, { x: -index * (cards[0].offsetWidth + 10), duration: 0.5 });
    }

    function goPrev() {
        index = (index - 1 + cards.length) % cards.length;
        gsap.to(track, { x: -index * (cards[0].offsetWidth + 10), duration: 0.5 });
    }

    const prevBtn = slider.querySelector('.slider-prev');
    const nextBtn = slider.querySelector('.slider-next');
    if (prevBtn) prevBtn.addEventListener('click', goPrev);
    if (nextBtn) nextBtn.addEventListener('click', goNext);

    if (autoPlay) {
        setInterval(goNext, 3000);
    }
});
