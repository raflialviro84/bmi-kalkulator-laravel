// BMI Kalkulator JS (hanya untuk modal close)
window.addEventListener('DOMContentLoaded', function() {
    var closeBtns = document.getElementsByClassName('close');
    Array.from(closeBtns).forEach(function(btn) {
        btn.onclick = function() {
            var modal = btn.closest('.modal');
            if (modal) modal.style.display = 'none';
        };
    });
    window.onclick = function(event) {
        var modals = document.getElementsByClassName('modal');
        Array.from(modals).forEach(function(modal) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        });
    };
});
