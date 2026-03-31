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

        $(document).on('click', '.shortcode-cache-clear-detected-btn', function (e) {
            e.preventDefault();

            const button = $(this);

            if ( ! confirm( 'Are you sure you want to clear detected shortcodes? This action cannot be undone.' ) ) {
                return;
            }

            button.prop('disabled', true);
            button.text('Clearing...');

            $.ajax({
                url: shortcodeCacheData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'shortcode_cache_clear_detected',
                },
                success: function (response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        button.prop('disabled', false);
                        button.text('Clear Detected Shortcodes');
                        alert(response.data.message);
                    }
                },
                error: function () {
                    button.prop('disabled', false);
                    button.text('Clear Detected Shortcodes');
                    alert('An error occurred while clearing detected shortcodes.');
                },
            });
        });

        $(document).on('click', '.shortcode-cache-view-content-btn', function (e) {
            e.preventDefault();

            const button = $(this);
            const cacheKey = button.data('cache-key');

            openContentModal(cacheKey);
        });

        $(document).on('click', '.shortcode-cache-content-modal-close', function () {
            closeContentModal();
        });

        $(document).on('click', '.shortcode-cache-content-modal-cancel', function () {
            closeContentModal();
        });

        $(document).on('click', '#shortcode-cache-content-modal', function (e) {
            if ($(e.target).is('#shortcode-cache-content-modal')) {
                closeContentModal();
            }
        });
    });

    function openContentModal(cacheKey) {
        const modal = $('#shortcode-cache-content-modal');
        const contentDisplay = modal.find('.shortcode-cache-content-display');

        contentDisplay.html('<span class="shortcode-cache-content-loading">Loading...</span>');
        modal.show();

        $.ajax({
            url: shortcodeCacheData.ajaxUrl,
            type: 'POST',
            data: {
                action: 'shortcode_cache_get_content',
                cache_key: cacheKey,
            },
            success: function (response) {
                if (response.success) {
                    contentDisplay.text(response.data.content);
                } else {
                    contentDisplay.html('<span class="shortcode-cache-content-error">Error: ' + escapeHtml(response.data.message) + '</span>');
                }
            },
            error: function () {
                contentDisplay.html('<span class="shortcode-cache-content-error">An error occurred while retrieving the content.</span>');
            },
        });
    }

    function closeContentModal() {
        $('#shortcode-cache-content-modal').hide();
    }

    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;',
        };
        return text.replace(/[&<>"']/g, function (m) {
            return map[m];
        });
    }
})(jQuery);