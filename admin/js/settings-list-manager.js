(function ($) {
    let allAvailableRoles = {};

    $(document).ready(function () {
        initializeListManager();
        loadAvailableRoles();
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

        $(document).on('change', '.shortcode-cache-role-checkbox', function (e) {
            if ($(this).data('index') !== undefined) {
                const index = $(this).data('index');
                const isChecked = $(this).prop('checked');
                toggleShortcodeRoleCaching(index, isChecked);
            }
        });

        $(document).on('click', '.shortcode-cache-global-roles-btn', function (e) {
            e.preventDefault();
            openGlobalRoleSelectionDialog();
        });

        $(document).on('click', '.shortcode-cache-modal-close', function () {
            closeRoleSelectionDialog();
        });

        $(document).on('click', '.shortcode-cache-modal-cancel', function () {
            closeRoleSelectionDialog();
        });

        $(document).on('click', '.shortcode-cache-modal-save', function () {
            saveGlobalSelectedRoles();
        });

        $(document).on('click', '#shortcode-cache-role-modal', function (e) {
            if ($(e.target).is('#shortcode-cache-role-modal')) {
                closeRoleSelectionDialog();
            }
        });
    }

    function loadAvailableRoles() {
        $.ajax({
            url: shortcodeCacheData.ajaxUrl,
            type: 'POST',
            data: {
                action: 'shortcode_cache_get_roles',
            },
            success: function (response) {
                if (response.success) {
                    allAvailableRoles = response.data.roles;
                }
            },
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

    function toggleShortcodeRoleCaching(index, isChecked) {
        $.ajax({
            url: shortcodeCacheData.ajaxUrl,
            type: 'POST',
            data: {
                action: 'shortcode_cache_update_role_caching',
                index: index,
                cache_by_role: isChecked ? 1 : 0,
            },
            success: function (response) {
                if (!response.success) {
                    alert(response.data.message);
                    location.reload();
                }
            },
            error: function () {
                alert('An error occurred while updating role caching setting.');
                location.reload();
            },
        });
    }

    function openGlobalRoleSelectionDialog() {
        const modal = $('#shortcode-cache-role-modal');
        const checkboxes = modal.find('.shortcode-cache-role-checkbox');

        checkboxes.prop('checked', false);

        const display = $('.shortcode-cache-global-roles-display');
        const badges = display.find('.shortcode-cache-role-badge');

        const selectedRoles = [];
        badges.each(function () {
            const text = $(this).text().trim();
            for (const [slug, name] of Object.entries(allAvailableRoles)) {
                if (name === text) {
                    selectedRoles.push(slug);
                }
            }
        });

        checkboxes.each(function () {
            const value = $(this).val();
            if (selectedRoles.includes(value)) {
                $(this).prop('checked', true);
            }
        });

        modal.show();
    }

    function closeRoleSelectionDialog() {
        $('#shortcode-cache-role-modal').hide();
    }

    function saveGlobalSelectedRoles() {
        const modal = $('#shortcode-cache-role-modal');
        const selectedRoles = [];

        modal.find('.shortcode-cache-role-checkbox:checked').each(function () {
            selectedRoles.push($(this).val());
        });

        $.ajax({
            url: shortcodeCacheData.ajaxUrl,
            type: 'POST',
            data: {
                action: 'shortcode_cache_update_global_roles',
                selected_roles: selectedRoles,
            },
            success: function (response) {
                if (response.success) {
                    updateGlobalRolesDisplay(selectedRoles);
                    closeRoleSelectionDialog();
                } else {
                    alert(response.data.message);
                }
            },
            error: function () {
                alert('An error occurred while updating roles.');
            },
        });
    }

    function updateGlobalRolesDisplay(selectedRoles) {
        const display = $('.shortcode-cache-global-roles-display');

        if (selectedRoles.length === 0) {
            display.html('<span class="shortcode-cache-roles-all">All authenticated roles</span>');
            return;
        }

        let html = '';
        selectedRoles.forEach(function (role) {
            if (allAvailableRoles[role]) {
                html += '<span class="shortcode-cache-role-badge">' + escapeHtml(allAvailableRoles[role]) + '</span>';
            }
        });

        display.html(html);
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