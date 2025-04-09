// Auto-clear alerts after 5 seconds
setTimeout(function() {
    var alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        alert.remove();
    });
}, 5000);

// Close modal after submitting form
document.addEventListener('DOMContentLoaded', function() {
    var form = document.querySelector('form');
    form.addEventListener('submit', function() {
        var modal = bootstrap.Modal.getInstance(document.getElementById('createAccountModal'));
        modal.hide();
    });
});

