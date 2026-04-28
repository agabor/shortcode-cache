(function($) {
    $(document).ready(function() {
        $('body').on('click', '.shortcode-cache-admin-bar-clear-all', function(e) {
            e.preventDefault();

            if (!confirm(shortcodeCacheAdminBar.confirmMessage || 'Are you sure you want to clear all cache?')) {
                return;
            }

            $.ajax({
                url: shortcodeCacheAdminBar.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'shortcode_cache_clear_all_cache',
                    nonce: shortcodeCacheAdminBar.nonce,
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.data.message || 'Cache cleared successfully');
                        location.reload();
                    } else {
                        alert(response.data.message || 'Failed to clear cache');
                    }
                },
                error: function() {
                    alert('An error occurred while clearing the cache');
                },
            });
        });
    });
})(jQuery);