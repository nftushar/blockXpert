document.addEventListener('DOMContentLoaded', function() {
    const faqBlocks = document.querySelectorAll('.wp-block-blockxpert-ai-faq');
    
    faqBlocks.forEach(block => {
        const animationDuration = block.dataset.animationDuration || '300';

        const faqQuestions = block.querySelectorAll('.faq-question-header');
        
        faqQuestions.forEach(question => {
            const content = question.closest('.faq-question-content');
            if (!content) return;

            const answer = content.querySelector('.faq-answer');
            const icon = question.querySelector('.faq-toggle-icon');

            if(!answer || !icon) return;

            answer.style.transitionDuration = `${animationDuration}ms`;
            
            question.addEventListener('click', function() {
                answer.classList.toggle('is-open');
                icon.classList.toggle('open');
            });
        });
    });
}); 