(function ($) {
    $(document).ready(function () {
        initializeListManager();
    });

    function initializeListManager() {
        $(document).on('click', '.shortcode-cache-add-btn', function (e) {
            e.preventDefault();
            addShortcode();
        });

        $(document).on('keypress', '.shortcode-cache-new-name', function (e) {
            if (e.which === 13) {
                e.preventDefault();
                addShortcode();
            }
        });

        $(document).on('click', '.shortcode-cache-delete-btn', function (e) {
            e.preventDefault();
            const index = $(this).data('index');
            deleteShortcode(index);
        });

        $(document).on('change', '.shortcode-cache-role-toggle', function () {
            const index = $(this).data('index');
            const enabled = $(this).is(':checked');
            toggleRoleBasedCaching(index, enabled);
        });
    }

    function addShortcode() {
        const nameInput = $('.shortcode-cache-new-name');
        const shortcodeName = nameInput.val().trim();

        if (!shortcodeName) {
            alert('Please enter a shortcode name');
            return;
        }

        const addButton = $('.shortcode-cache-add-btn');
        addButton.prop('disabled', true);
        addButton.text('Adding...');

        $.ajax({
            url: shortcodeCacheData.ajaxUrl,
            type: 'POST',
            data: {
                action: 'shortcode_cache_add',
                shortcode_name: shortcodeName,
            },
            success: function (response) {
                if (response.success) {
                    location.reload();
                } else {
                    addButton.prop('disabled', false);
                    addButton.text('Add Shortcode');
                    alert(response.data.message);
                }
            },
            error: function () {
                addButton.prop('disabled', false);
                addButton.text('Add Shortcode');
                alert('An error occurred while adding the shortcode.');
            },
        });
    }

    function deleteShortcode(index) {
        if (!confirm('Are you sure you want to delete this shortcode? This action cannot be undone.')) {
            return;
        }

        $.ajax({
            url: shortcodeCacheData.ajaxUrl,
            type: 'POST',
            data: {
                action: 'shortcode_cache_delete',
                index: index,
            },
            success: function (response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data.message);
                }
            },
            error: function () {
                alert('An error occurred while deleting the shortcode.');
            },
        });
    }

    function toggleRoleBasedCaching(index, enabled) {
        $.ajax({
            url: shortcodeCacheData.ajaxUrl,
            type: 'POST',
            data: {
                action: 'shortcode_cache_toggle_role',
                index: index,
                enabled: enabled,
            },
            success: function (response) {
                if (!response.success) {
                    alert(response.data.message);
                    location.reload();
                }
            },
            error: function () {
                alert('An error occurred while updating the setting.');
                location.reload();
            },
        });
    }
})(jQuery);