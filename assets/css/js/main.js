// Auto dismiss alerts after 4 seconds
setTimeout(function() {
    let alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        alert.style.display = 'none';
    });
}, 4000);

// Confirm before deleting
function confirmDelete() {
    return confirm('Are you sure you want to delete this record? This action cannot be undone.');
}

// Preview selected image
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('imagePreview').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}