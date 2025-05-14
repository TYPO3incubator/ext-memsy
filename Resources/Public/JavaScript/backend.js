// Add confirmation when user clicks the set members inactive button
const btn = document.querySelector('.set-inactive-btn-js');
if (btn) {
    btn.addEventListener('click', function(event) {
        // ask for confirmation; if cancelled, stop submission
        if (!window.confirm('Are you sure you want to set the selected members inactive?')) {
            event.preventDefault();
        }
    });
}
