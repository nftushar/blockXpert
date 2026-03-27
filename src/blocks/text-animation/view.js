import gsap from 'gsap';

/**
 * Initialize GSAP Text Animations
 * Handles character/word animations with SplitText-like functionality
 */
document.addEventListener('DOMContentLoaded', function () {
  const animationWrappers = document.querySelectorAll('.blockxpert-text-animation-wrapper');

  animationWrappers.forEach((wrapper) => {
    const headline = wrapper.querySelector('.main-headline');
    const tagline = wrapper.querySelector('.tagline');

    if (!headline || !tagline) return;

    // Get animation settings from data attributes
    const animationType = wrapper.dataset.animationType || 'characters';
    const duration = parseFloat(wrapper.dataset.duration) || 0.8;
    const stagger = parseFloat(wrapper.dataset.stagger) || 0.03;
    const ease = wrapper.dataset.ease || 'expo.out';
    const enableBlur = wrapper.dataset.enableBlur !== 'false';
    const headlineColor = headline.dataset.headlineColor || '#FFFFFF';
    const animColor = headline.dataset.animColor || '#6C5CE7';
    const taglineColor = tagline.style.color || '#FFFFFF';

    // Create timeline with default settings
    const tl = gsap.timeline({
      defaults: { duration, ease },
    });

    // Split text based on animation type
    const headlineElements = splitText(headline, animationType);
    const taglineElements = splitText(tagline, 'words');

    if (headlineElements.length === 0 || taglineElements.length === 0) return;

    // Animate headline
    if (animationType === 'characters') {
      tl.from(
        headlineElements,
        {
          y: 100,
          rotationX: 90,
          opacity: 0,
          color: '#FFFFFF',
          stagger,
          transformOrigin: 'center top',
          perspective: 700,
        },
        0
      );

      // Color transition for characters
      tl.to(
        headlineElements,
        {
          color: animColor,
          duration: duration * 1.1,
          stagger,
          ease: 'power2.out',
        },
        `-=${duration}`
      );
    } else if (animationType === 'words') {
      tl.from(
        headlineElements,
        {
          y: 100,
          rotationX: 90,
          opacity: 0,
          stagger,
          transformOrigin: 'center top',
          perspective: 700,
        },
        0
      );

      tl.to(
        headlineElements,
        {
          color: animColor,
          duration: duration * 1.1,
          stagger,
          ease: 'power2.out',
        },
        `-=${duration}`
      );
    } else if (animationType === 'lines') {
      tl.from(
        headlineElements,
        {
          y: 80,
          opacity: 0,
          stagger: stagger * 5,
          transformOrigin: 'center top',
        },
        0
      );

      tl.to(
        headlineElements,
        {
          color: animColor,
          duration: duration,
          stagger: stagger * 5,
          ease: 'power2.out',
        },
        `-=${duration}`
      );
    }

    // Animate tagline
    tl.from(
      taglineElements,
      {
        y: 60,
        opacity: 0,
        filter: enableBlur ? 'blur(16px)' : 'blur(0px)',
        stagger: stagger * 4,
        duration: duration * 0.875,
        ease: 'power3.out',
      },
      `-=${duration * 1.2}`
    );

    if (enableBlur) {
      tl.to(
        taglineElements,
        {
          filter: 'blur(0px)',
          duration: duration * 0.5,
          stagger: stagger * 4,
        },
        `-=${duration * 0.5}`
      );
    }
  });
});

/**
 * Split text into individual elements
 * @param {HTMLElement} element - The element to split
 * @param {string} type - 'characters', 'words', or 'lines'
 * @returns {Array} Array of span elements
 */
function splitText(element, type) {
  const text = element.textContent;
  element.innerHTML = '';

  let parts = [];

  if (type === 'characters') {
    parts = text.split('');
  } else if (type === 'words') {
    parts = text.split(' ');
  } else if (type === 'lines') {
    parts = text.split('\n');
  }

  const fragments = document.createDocumentFragment();

  parts.forEach((part, index) => {
    const span = document.createElement('span');
    span.className = `text-${type}-${index}`;
    span.textContent = part;
    span.style.display = 'inline-block';

    // Add space after words (except last)
    if (type === 'words' && index < parts.length - 1) {
      span.textContent += ' ';
    }

    // Add line break for lines (except last)
    if (type === 'lines' && index < parts.length - 1) {
      fragments.appendChild(span);
      const br = document.createElement('br');
      fragments.appendChild(br);
      return;
    }

    fragments.appendChild(span);
  });

  element.appendChild(fragments);
  return Array.from(element.querySelectorAll(`span[class^="text-${type}"]`));
}
