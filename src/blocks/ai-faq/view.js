document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.ai-faq-block .faq-question-header').forEach(function (header) {
    header.addEventListener('click', function () {
      const question = header.closest('.faq-question');
      const answer = question.querySelector('.faq-answer');
      if (answer.style.display === 'none' || !answer.style.display) {
        answer.style.display = 'block';
        header.querySelector('.faq-toggle-icon').textContent = '−';
      } else {
        answer.style.display = 'none';
        header.querySelector('.faq-toggle-icon').textContent = '+';
      }
    });
  });
}); 