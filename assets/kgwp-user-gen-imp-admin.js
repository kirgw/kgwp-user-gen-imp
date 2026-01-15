jQuery(document).ready(function ($) {

    // File upload form submission handling
    $('form[action*="admin-post.php"][action*="upload_csv"]').on('submit', function (e) {
        var $form = $(this);
        var $fileInput = $form.find('input[type="file"]');

        // Check if file is selected
        if ($fileInput.val() === '') {
            alert('Please select a CSV file to upload.');
            e.preventDefault();
            return false;
        }

        // Check file extension
        var fileName = $fileInput.val();
        var extension = fileName.split('.').pop().toLowerCase();

        if (extension !== 'csv') {
            alert('Only CSV files are allowed.');
            e.preventDefault();
            return false;
        }

        // Show loading indicator
        $form.find('button[type="submit"]').prop('disabled', true).text('Uploading...');

        return true;
    });

    // Import form confirmation
    $('form[action*="admin-post.php"][action*="import_users"]').on('submit', function (e) {
        var importType = $(this).find('input[name="import_type"]').val();
        var confirmationMessage = 'Are you sure you want to import users?';

        if (importType === 'csv') {
            confirmationMessage = 'Are you sure you want to import users from the CSV file?';
        } else if (importType === 'generated') {
            confirmationMessage = 'Are you sure you want to import the generated users?';
        }

        return confirm(confirmationMessage);
    });

    // File input styling
    $('.file-upload-wrapper input[type="file"]').on('change', function () {
        var fileName = $(this).val().split('\\').pop();
        $(this).sibling('span.file-name').text(fileName);
    });
});