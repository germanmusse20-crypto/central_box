/**
 * central_box — Dashboard JavaScript
 */
document.addEventListener('DOMContentLoaded', function() {
    // Animate stat card values with counting effect
    const statValues = document.querySelectorAll('.stat-card-value');
    statValues.forEach(function(el) {
        const text = el.textContent.trim();
        // Only animate if it's a simple number
        const num = parseInt(text.replace(/[^0-9]/g, ''));
        if (!isNaN(num) && num > 0 && !text.includes('$')) {
            animateCounter(el, num);
        }
    });
});

function animateCounter(element, target) {
    var current = 0;
    var increment = Math.ceil(target / 40);
    var duration = 800;
    var stepTime = duration / (target / increment);
    var originalText = element.textContent;

    var timer = setInterval(function() {
        current += increment;
        if (current >= target) {
            current = target;
            clearInterval(timer);
        }
        element.textContent = current.toLocaleString('es-CO');
    }, stepTime);
}
