    </div><!-- end admin-content -->
</div><!-- end admin-main -->

<script>
// Confirm delete
function confirmDelete(msg = 'Yakin mau hapus data ini?') {
    return confirm(msg);
}

// Auto hide alerts
setTimeout(() => {
    document.querySelectorAll('.alert').forEach(el => {
        el.style.transition = 'opacity 0.5s';
        el.style.opacity = '0';
        setTimeout(() => el.remove(), 500);
    });
}, 4000);

// Image preview
function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById(previewId).innerHTML = 
                '<img src="' + e.target.result + '">';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
</body>
</html>