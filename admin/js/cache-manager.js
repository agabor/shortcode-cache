(function ($) {
    $(document).ready(function () {
        $(document).on('click', '.shortcode-cache-clear-btn', function (e) {
            e.preventDefault();

            const button = $(this);
            const cacheKey = button.data('cache-key');
            const row = button.closest('tr');

            button.prop('disabled', true);
            button.text('Clearing...');

            $.ajax({
                url: shortcodeCacheData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'shortcode_cache_clear',
                    cache_key: cacheKey,
                    nonce: shortcodeCacheData.nonce,
                },
                success: function (response) {
                    if (response.success) {
                        row.fadeOut(300, function () {
                            $(this).remove();

                            if ($('table.wp-list-table tbody tr').length === 0) {
                                location.reload();
                            }
                        });
                    } else {
                        button.prop('disabled', false);
                        button.text('Clear Cache');
                        alert(response.data.message);
                    }
                },
                error: function () {
                    button.prop('disabled', false);
                    button.text('Clear Cache');
                    alert('An error occurred while clearing the cache.');
                },
            });
        });

        $(document).on('click', '.shortcode-cache-clear-all-btn', function (e) {
            e.preventDefault();

            const button = $(this);
            const nonce = button.data('nonce');

            if ( ! confirm( 'Are you sure you want to clear all cached items? This action cannot be undone.' ) ) {
                return;
            }

            button.prop('disabled', true);
            button.text('Clearing All...');

            $.ajax({
                url: shortcodeCacheData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'shortcode_cache_clear_all',
                    nonce: nonce,
                },
                success: function (response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        button.prop('disabled', false);
                        button.text('Clear All Cache');
                        alert(response.data.message);
                    }
                },
                error: function () {
                    button.prop('disabled', false);
                    button.text('Clear All Cache');
                    alert('An error occurred while clearing all cache.');
                },
            });
        });
    });
})(jQuery);