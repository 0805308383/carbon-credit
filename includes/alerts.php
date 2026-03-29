<?php
if (isset($_SESSION['flash_alert'])): 
    $alert = $_SESSION['flash_alert'];
    $type = $alert['type'] ?? 'info';
    $title = $alert['title'] ?? '';
    $message = $alert['message'] ?? '';
?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
        icon: '<?= $type ?>',
        title: '<?= $title ?>',
        text: '<?= $message ?>',
        confirmButtonColor: '#10b981'
    });
});
</script>
<?php 
unset($_SESSION['flash_alert']);
endif; 
?>
