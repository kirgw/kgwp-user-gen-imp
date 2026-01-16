// assets/kgwp-user-gen-imp-admin.js
document.addEventListener('DOMContentLoaded', () => {
    console.log('KG WP User Generation & Import loaded...');

    // File upload form submission handling
    const fileUploadForms = document.querySelectorAll('form[action*="admin-post.php"][action*="upload_csv"]');
    fileUploadForms.forEach(form => {
        form.addEventListener('submit', (e) => {
            const fileInput = form.querySelector('input[type="file"]');
            const submitButton = form.querySelector('button[type="submit"]');

            // Check if file is selected
            if (!fileInput.value) {
                alert('Please select a CSV file to upload.');
                e.preventDefault();
                return false;
            }

            // Check file extension
            const fileName = fileInput.value;
            const extension = fileName.split('.').pop().toLowerCase();

            if (extension !== 'csv') {
                alert('Only CSV files are allowed.');
                e.preventDefault();
                return false;
            }

            // Show loading indicator
            submitButton.disabled = true;
            submitButton.textContent = 'Uploading...';

            return true;
        });
    });

    // Import form confirmation
    const importForms = document.querySelectorAll('form[action*="admin-post.php"][action*="import_users"]');
    importForms.forEach(form => {
        form.addEventListener('submit', (e) => {
            const importType = form.querySelector('input[name="import_type"]').value;
            let confirmationMessage = 'Are you sure you want to import users?';

            if (importType === 'csv') {
                confirmationMessage = 'Are you sure you want to import users from the CSV file?';
            } else if (importType === 'generated') {
                confirmationMessage = 'Are you sure you want to import the generated users?';
            }

            return confirm(confirmationMessage);
        });
    });

    // File input styling
    const fileInputs = document.querySelectorAll('.file-upload-wrapper input[type="file"]');
    fileInputs.forEach(input => {
        input.addEventListener('change', () => {
            const fileName = input.value.split('\\\\').pop();
            const fileNameSpan = input.closest('.file-upload-wrapper').querySelector('span.file-name');
            if (fileNameSpan) {
                fileNameSpan.textContent = fileName;
            }
        });
    });

    // Role selection toggle - select/deselect all
    const selectAllRolesCheckbox = document.getElementById('select-all-roles');
    if (selectAllRolesCheckbox) {
        selectAllRolesCheckbox.addEventListener('click', () => {
            const isChecked = selectAllRolesCheckbox.checked;
            const roleCheckboxes = document.querySelectorAll('.kgwp-roles-selection .kgwp-role-checkbox input[type="checkbox"]:not(#select-all-roles)');
            roleCheckboxes.forEach(checkbox => {
                checkbox.checked = isChecked;
            });
        });
    }

    // Individual role checkbox click handler
    const roleCheckboxes = document.querySelectorAll('.kgwp-roles-selection .kgwp-role-checkbox input[type="checkbox"]');
    roleCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', () => {
            // Skip if this is the select-all checkbox
            if (checkbox.id === 'select-all-roles') {
                return;
            }

            const selectAllCheckbox = document.getElementById('select-all-roles');
            if (!checkbox.checked) {
                selectAllCheckbox.checked = false;
            } else {
                // Check if all checkboxes are checked
                const allCheckboxes = document.querySelectorAll('.kgwp-roles-selection .kgwp-role-checkbox input[type="checkbox"]:not(#select-all-roles)');
                const allChecked = Array.from(allCheckboxes).every(cb => cb.checked);
                selectAllCheckbox.checked = allChecked;
            }
        });
    });
});