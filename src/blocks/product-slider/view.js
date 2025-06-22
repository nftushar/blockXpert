document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.product-slider-editor-preview').forEach(function (slider) {
    const track = slider.querySelector('.slider-track-preview');
    const items = slider.querySelectorAll('.product-item-preview');
    const prevBtn = slider.querySelector('.slider-nav-preview.prev');
    const nextBtn = slider.querySelector('.slider-nav-preview.next');
    const pagination = slider.querySelector('.slider-pagination-preview');
    const baseProductsPerSlide = parseInt(slider.getAttribute('data-products-per-slide')) || 3;
    const autoPlay = slider.getAttribute('data-auto-play') === 'true';
    const showNavigation = slider.getAttribute('data-show-navigation') === 'true';
    const showPagination = slider.getAttribute('data-show-pagination') === 'true';
    let productsPerSlide = baseProductsPerSlide;
    let currentSlide = 0;
    let totalSlides = 1;
    let autoPlayInterval = null;

    function getResponsiveProductsPerSlide() {
      if (window.innerWidth < 600) return 1;
      if (window.innerWidth < 900) return Math.min(2, baseProductsPerSlide);
      return baseProductsPerSlide;
    }

    function recalculate() {
      productsPerSlide = getResponsiveProductsPerSlide();
      totalSlides = Math.max(1, Math.ceil(items.length / productsPerSlide));
      if (currentSlide >= totalSlides) currentSlide = totalSlides - 1;

      // Set track width and item width
      if (track) {
        track.style.width = `${100 * totalSlides}%`;
      }
      items.forEach(function (item) {
        item.style.flex = `0 0 ${100 / (totalSlides * productsPerSlide)}%`;
        item.style.maxWidth = `${100 / (totalSlides * productsPerSlide)}%`;
      });
      createPagination();
      updateSlider();
    }

    function updateSlider() {
      if (track) {
        track.style.transform = `translateX(-${currentSlide * (100 / totalSlides)}%)`;
      }
      if (pagination) {
        pagination.querySelectorAll('.dot-preview').forEach((dot, idx) => {
          dot.classList.toggle('active', idx === currentSlide);
        });
      }
      if (prevBtn && nextBtn) {
        prevBtn.style.display = showNavigation && totalSlides > 1 ? 'flex' : 'none';
        nextBtn.style.display = showNavigation && totalSlides > 1 ? 'flex' : 'none';
      }
      if (pagination) {
        pagination.style.display = showPagination && totalSlides > 1 ? 'block' : 'none';
      }
    }

    function createPagination() {
      if (!pagination) return;
      pagination.innerHTML = '';
      for (let i = 0; i < totalSlides; i++) {
        const dot = document.createElement('button');
        dot.className = 'dot-preview' + (i === currentSlide ? ' active' : '');
        dot.addEventListener('click', function () {
          currentSlide = i;
          updateSlider();
        });
        pagination.appendChild(dot);
      }
    }

    if (prevBtn) {
      prevBtn.addEventListener('click', function () {
        currentSlide = Math.max(0, currentSlide - 1);
        updateSlider();
      });
    }
    if (nextBtn) {
      nextBtn.addEventListener('click', function () {
        currentSlide = Math.min(totalSlides - 1, currentSlide + 1);
        updateSlider();
      });
    }

    function startAutoPlay() {
      if (autoPlay && totalSlides > 1) {
        autoPlayInterval = setInterval(function () {
          currentSlide = (currentSlide + 1) % totalSlides;
          updateSlider();
        }, 5000);
      }
    }
    function stopAutoPlay() {
      if (autoPlayInterval) {
        clearInterval(autoPlayInterval);
        autoPlayInterval = null;
      }
    }
    slider.addEventListener('mouseenter', stopAutoPlay);
    slider.addEventListener('mouseleave', startAutoPlay);
    window.addEventListener('resize', recalculate);

    recalculate();
    startAutoPlay();
  });
}); 