<script>
    function confirmPermissionsBeforeSave(options) {
        const permissions = options.collectPermissions();

        $.ajax({
            url: options.previewUrl,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            data: JSON.stringify({ permissions: permissions }),
            contentType: 'application/json',
            success: function(response) {
                if (!response || response.success === false) {
                    notifyPermissionMessage('error', (response && response.error) || 'Unable to preview permission changes.');
                    return;
                }

                if (!response.hasImpact) {
                    options.onConfirm(permissions);
                    return;
                }

                const message = 'Saving these permission changes will remove device categories and templates from child accounts. This action cannot be undone.';

                if (!window.Swal) {
                    if (window.confirm(message)) {
                        options.onConfirm(permissions);
                    }
                    return;
                }

                Swal.fire({
                    title: 'Confirm Permission Changes',
                    text: message,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, save changes',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#64748b'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        options.onConfirm(permissions);
                    }
                });
            },
            error: function(xhr) {
                console.warn('Permission preview failed, saving without impact confirmation.', xhr);
                options.onConfirm(permissions);
            }
        });
    }
</script>
