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
    });
})(jQuery);