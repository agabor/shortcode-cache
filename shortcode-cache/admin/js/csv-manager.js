(function($) {
    function initializeCsvExport() {
        $(document).on('click', '.shortcode-cache-export-csv-btn', function(e) {
            e.preventDefault();
            handleExportClick();
        });
    }

    function handleExportClick() {
        $.ajax({
            type: 'POST',
            url: shortcodeCacheData.ajaxUrl,
            data: {
                action: 'shortcode_cache_export_csv',
            },
            success: function(response) {
                if (response.success) {
                    downloadCsvFile(response.data.csv_content);
                } else {
                    showCsvMessage('error', response.data.message);
                }
            },
            error: function() {
                showCsvMessage('error', 'An error occurred while exporting.');
            }
        });
    }

    function downloadCsvFile(csvContent) {
        var blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        var link = document.createElement('a');
        var url = URL.createObjectURL(blob);

        link.setAttribute('href', url);
        link.setAttribute('download', 'shortcode-cache-config.csv');
        link.style.visibility = 'hidden';

        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        showCsvMessage('success', 'Configuration exported successfully.');
    }

    function initializeCsvImport() {
        $(document).on('submit', '.shortcode-cache-csv-import-form', function(e) {
            e.preventDefault();
            handleImportSubmit(this);
        });
    }

    function handleImportSubmit(form) {
        var fileInput = $(form).find('.shortcode-cache-csv-file-input')[0];
        var mergeMode = $(form).find('.shortcode-cache-csv-merge-mode').val();

        if (!fileInput.files || !fileInput.files[0]) {
            showCsvMessage('error', 'Please select a file.');
            return;
        }

        var file = fileInput.files[0];

        if (file.type !== 'text/csv' && !file.name.endsWith('.csv')) {
            showCsvMessage('error', 'Please select a valid CSV file.');
            return;
        }

        var formData = new FormData();
        formData.append('action', 'shortcode_cache_import_csv');
        formData.append('csv_file', file);
        formData.append('merge_mode', mergeMode);

        $.ajax({
            type: 'POST',
            url: shortcodeCacheData.ajaxUrl,
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                if (response.success) {
                    showCsvMessage('success', response.data.message);
                    fileInput.value = '';
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    showCsvMessage('error', response.data.message);
                }
            },
            error: function() {
                showCsvMessage('error', 'An error occurred while importing.');
            }
        });
    }

    function showCsvMessage(type, message) {
        const $messageContainer = $('.shortcode-cache-csv-message');
        const cssClass = 'notice notice-' + (type === 'error' ? 'error' : 'success') + ' is-dismissible';

        const $notice = $('<div>')
            .addClass(cssClass)
            .html('<p>' + message + '</p>');

        const $closeBtn = $('<button>')
            .addClass('notice-dismiss')
            .html('<span class="screen-reader-text">Dismiss this notice.</span>')
            .on('click', function () {
                $notice.remove();
            });

        $notice.append($closeBtn);
        $messageContainer.html($notice).show();

        setTimeout(function() {
            $notice.fadeOut(function() {
                $notice.remove();
            });
        }, 5000);
    }

    $(document).ready(function() {
        initializeCsvExport();
        initializeCsvImport();
    });
})(jQuery);