(function ($) {
    $(document).ready(function () {
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
    });
})(jQuery);