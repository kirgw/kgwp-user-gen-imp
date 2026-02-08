<?php

/**
 * The file defines the settings class
 *
 * @package    KGWP\UserGenImp
 * @subpackage KGWP\UserGenImp\Inc
 */

namespace KGWP\UserGenImp\Inc;

// Security check - exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Admin pages class
 *
 * @class KGWP\UserGenImp\Inc\AdminPages
 */
class AdminPages {

    // Pre-define the capability
    private $menu_capability = 'manage_options';

    // Placeholder to store the settings pages
    private $settings_pages = [];

    // Set the base menu slug (also used in sub-pages as a prefix)
    public $menu_slug = KGWP_USERGENIMP_SLUG;

    // Text domain
    public $text_domain = KGWP_USERGENIMP_SLUG;

    /**
     * Constructor
     *
     * Sets the properties and adds the action hooks to add menu pages and add options.
     *
     * @return void
     */
    public function __construct() {

        $this->settings_pages = [
            'main' => [
                'page_title' => 'KG WP Users Generation & Import',
                'menu_title' => 'Users Import',
                'capability' => $this->menu_capability,
                'menu_slug'  => $this->menu_slug,
                'menu_position' => (int) apply_filters('kgwp_user_gen_imp_menu_position', 71),
                'callback'   => 'render_admin_page_settings',
                'icon_url'   => 'dashicons-groups',
            ]
        ];

        // Add menu pages
        add_action('admin_menu', array($this, 'admin_pages_init'));

        // Add options
        add_action('admin_init', array($this, 'admin_options_init'));

        // Enqueue admin styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));

        // Enqueue admin scripts
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));

        // Handlers
        add_action('admin_post_import_users', array($this, 'handle_import_users'));
        add_action('admin_post_generate_users', array($this, 'handle_generate_users'));
        add_action('admin_post_upload_csv', array($this, 'handle_csv_upload'));
        add_action('admin_post_download_generated_csv', array($this, 'handle_download_generated_csv'));
    }


    /**
     * Handle import users form submission
     *
     * Checks the import nonce, selects the import type and launches the import process.
     *
     * @return void
     */
    public function handle_import_users() {

        // Check capability
        if (!current_user_can($this->menu_capability)) {
            wp_die(__('You do not have sufficient permissions to access this page.', $this->text_domain));
        }

        // Check import type
        if (isset($_POST['import_type'])) {

            $import_type = sanitize_text_field($_POST['import_type']);

            // Check nonce based on import type
            if ($import_type === 'csv') {
                if (!isset($_POST['import_nonce']) || !wp_verify_nonce($_POST['import_nonce'], 'import_csv_nonce')) {
                    wp_die('Security check failed');
                }
            }
            elseif ($import_type === 'generated') {
                if (!isset($_POST['import_nonce']) || !wp_verify_nonce($_POST['import_nonce'], 'import_generated_nonce')) {
                    wp_die('Security check failed');
                }
            }
            else {
                wp_die('Invalid import type');
            }

            $import = new \KGWP\UserGenImp\Inc\Import();

            // 1. Check if we should use an uploaded file for CSV import
            if ($import_type === 'csv') {

                $uploaded_file_path = get_transient('kgwp_uploaded_csv_path');

                if ($uploaded_file_path) {
                    $result = $import->import_from_csv($uploaded_file_path);
                }
                else {
                    $result = $import->launch($import_type);
                }
            }
            elseif ($import_type === 'generated') {
                $result = $import->launch($import_type);
            }

            // 2. Store the result in a transient
            if ($import_type === 'csv') {

                if (is_array($result)) {
                    set_transient('kgwp_import_result', sprintf(__('Successfully imported %d users from CSV.', $this->text_domain), count($result)), 60);
                }
                else {
                    set_transient('kgwp_import_result', __('Failed to import users from CSV.', $this->text_domain), 60);
                }
            }
            elseif ($import_type === 'generated') {

                if (is_array($result)) {
                    set_transient('kgwp_import_result', sprintf(__('Successfully imported %d users from generated data.', $this->text_domain), count($result)), 60);
                }
                else {
                    set_transient('kgwp_import_result', __('Failed to import users from generated data.', $this->text_domain), 60);
                }
            }
        }

        // Redirect back to admin page
        wp_safe_redirect(admin_url('options-general.php?page=' . $this->menu_slug));
        exit;
    }


    /**
     * Handle generate users form submission
     *
     * @return void
     */
    public function handle_generate_users() {

        // Check capability
        if (!current_user_can($this->menu_capability)) {
            wp_die(__('You do not have sufficient permissions to access this page.', $this->text_domain));
        }

        // Check nonce
        if (!isset($_POST['generate_nonce']) || ! wp_verify_nonce($_POST['generate_nonce'], 'generate_users_nonce')) {
            wp_die('Security check failed');
        }

        // Get number of users
        $num_users = isset($_POST['kgwp_usergenimp_users_amount']) ? absint($_POST['kgwp_usergenimp_users_amount']) : KGWP_USERGENIMP_DEFAULT_USERS_AMOUNT;
        update_option('kgwp_usergenimp_users_amount', $num_users);

        // Get selected roles if provided
        $selected_roles = isset($_POST['kgwp_usergenimp_selected_roles']) ? array_map('sanitize_text_field', $_POST['kgwp_usergenimp_selected_roles']) : array();

        if (!empty($selected_roles)) {
            update_option('kgwp_usergenimp_selected_roles', $selected_roles);
        }

        // Generate and store users to display
        $generate = new \KGWP\UserGenImp\Inc\Generate();
        $generated_users = $generate->generate_random_users($num_users, $selected_roles);
        $generated_users_saved = set_transient('kgwp_generated_users', $generated_users, 3600);

        // Redirect back to admin page
        wp_safe_redirect(admin_url('options-general.php?page=' . $this->menu_slug));
        exit;
    }

    /**
     * Handle CSV file upload
     *
     * @return void
     */
    public function handle_csv_upload() {

        // Check capability
        if (!current_user_can($this->menu_capability)) {
            wp_die(__('You do not have sufficient permissions to access this page.', $this->text_domain));
        }

        // Check nonce
        if (!isset($_POST['upload_csv_nonce']) || !wp_verify_nonce($_POST['upload_csv_nonce'], 'upload_csv_nonce')) {
            wp_die('Security check failed');
        }

        // Check if file was uploaded
        if (!isset($_FILES['csv_file']) || empty($_FILES['csv_file']['name'])) {
            set_transient('kgwp_upload_result', __('Please select a CSV file to upload.', $this->text_domain), 60);
            wp_safe_redirect(admin_url('options-general.php?page=' . $this->menu_slug));
            exit;
        }

        $uploaded_file = $_FILES['csv_file'];

        // Check for upload errors
        if ($uploaded_file['error'] !== UPLOAD_ERR_OK) {
            set_transient('kgwp_upload_result', __('Error uploading file: ' . $this->get_upload_error_message($uploaded_file['error']), $this->text_domain), 60);
            wp_safe_redirect(admin_url('options-general.php?page=' . $this->menu_slug));
            exit;
        }

        // Check file extension
        $file_info = pathinfo($uploaded_file['name']);
        if (strtolower($file_info['extension']) !== 'csv') {
            set_transient('kgwp_upload_result', __('Only CSV files are allowed.', $this->text_domain), 60);
            wp_safe_redirect(admin_url('options-general.php?page=' . $this->menu_slug));
            exit;
        }

        // Create uploads directory if it doesn't exist
        $upload_dir = KGWP_USERGENIMP_PLUGIN_PATH . 'uploads/';
        if (!file_exists($upload_dir)) {
            wp_mkdir_p($upload_dir);
        }

        // Move uploaded file to plugin uploads directory
        $destination = $upload_dir . 'uploaded_' . time() . '_' . sanitize_file_name($uploaded_file['name']);

        if (move_uploaded_file($uploaded_file['tmp_name'], $destination)) {

            // Validate the CSV file
            $validation_result = \KGWP\UserGenImp\Inc\Import::validate_csv($destination);

            if (!is_wp_error($validation_result)) {
                // Store the uploaded file path in a transient for immediate use
                set_transient('kgwp_uploaded_csv_path', $destination, 3600);
                set_transient('kgwp_upload_result', __('File uploaded successfully! You can now import users from this file.', $this->text_domain), 60);
            }
            else {

                // Delete invalid file
                unlink($destination);
                $error_messages = $validation_result->get_error_messages();
                $error_message_html = __('Invalid CSV file format. Please fix the following issues:', $this->text_domain) . '<br><br>';
                $error_message_html .= '<ul>';
                foreach ($error_messages as $error) {
                    $error_message_html .= '<li>' . esc_html($error) . '</li>';
                }
                $error_message_html .= '</ul>';
                set_transient('kgwp_upload_result', $error_message_html, 60);
            }
        }
        else {
            set_transient('kgwp_upload_result', __('Failed to move uploaded file.', $this->text_domain), 60);
        }

        // Redirect back to admin page
        wp_safe_redirect(admin_url('options-general.php?page=' . $this->menu_slug));
        exit;
    }

    /**
     * Handle CSV download for generated users
     *
     * @return void
     */
    public function handle_download_generated_csv() {

        // Check capability
        if (!current_user_can($this->menu_capability)) {
            wp_die(__('You do not have sufficient permissions to access this page.', $this->text_domain));
        }

        // Check nonce
        if (!isset($_POST['download_csv_nonce']) || !wp_verify_nonce($_POST['download_csv_nonce'], 'download_generated_csv_nonce')) {
            wp_die('Security check failed');
        }

        // Get generated users from transient
        $generated_users = get_transient('kgwp_generated_users');

        if (empty($generated_users)) {
            wp_die('No generated users found to download.');
        }

        // Generate CSV content
        $csv_content = $this->generate_csv_from_users($generated_users);

        // Set headers for CSV download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=generated_users_' . date('Y-m-d_His') . '.csv');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Output CSV content
        echo $csv_content;
        exit;
    }

    /**
     * Generate CSV content from users data
     *
     * @param array $users
     * @return string
     */
    private function generate_csv_from_users($users) {

        // Open output buffer
        ob_start();

        // Create file pointer in memory
        $output = fopen('php://output', 'w');

        // Add CSV header
        $header = array('user_login', 'user_pass', 'user_email', 'first_name', 'last_name', 'role', 'description');
        fputcsv($output, $header);

        // Add user data
        foreach ($users as $user) {
            $row = array(
                isset($user['user_login']) ? $user['user_login'] : '',
                isset($user['user_pass']) ? $user['user_pass'] : '',
                isset($user['user_email']) ? $user['user_email'] : '',
                isset($user['first_name']) ? $user['first_name'] : '',
                isset($user['last_name']) ? $user['last_name'] : '',
                isset($user['role']) ? $user['role'] : '',
                isset($user['description']) ? $user['description'] : ''
            );
            fputcsv($output, $row);
        }

        // Close file pointer
        fclose($output);

        // Get the CSV content from buffer
        return ob_get_clean();
    }

    /**
     * Get upload error message
     *
     * @param int $error_code
     * @return string
     */
    private function get_upload_error_message($error_code) {
        switch ($error_code) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return __('The uploaded file exceeds the maximum file size.');
            case UPLOAD_ERR_PARTIAL:
                return __('The uploaded file was only partially uploaded.');
            case UPLOAD_ERR_NO_FILE:
                return __('No file was uploaded.');
            case UPLOAD_ERR_NO_TMP_DIR:
                return __('Missing temporary folder.');
            case UPLOAD_ERR_CANT_WRITE:
                return __('Failed to write file to disk.');
            case UPLOAD_ERR_EXTENSION:
                return __('File upload stopped by extension.');
            default:
                return __('Unknown upload error.');
        }
    }


    /**
     * Enqueue admin styles
     *
     * Checks admin page (containing the menu slug),
     * loads the admin styles for that page.
     *
     * @return void
     */
    public function enqueue_admin_styles() {

        // Check if we're on our admin page
        $screen = get_current_screen();

        // Load for all admin pages containing your menu slug (including subpages)
        if (strpos($screen->id, $this->menu_slug) !== false) {

            wp_enqueue_style(
                'kgwp-user-gen-imp-admin-styles',
                KGWP_USERGENIMP_PLUGIN_URL . 'assets/kgwp-user-gen-imp-admin-styles.css',
                array(),
                KGWP_USERGENIMP_PLUGIN_VERSION
            );
        }
    }

    /**
     * Enqueue admin scripts
     *
     * Checks admin page (containing the menu slug),
     * loads the admin scripts for that page.
     *
     * @return void
     */
    public function enqueue_admin_scripts() {

        // Check if we're on our admin page
        $screen = get_current_screen();

        // Load for all admin pages containing your menu slug (including subpages)
        if (strpos($screen->id, $this->menu_slug) !== false) {

            wp_enqueue_script(
                'kgwp-user-gen-imp-admin',
                KGWP_USERGENIMP_PLUGIN_URL . 'assets/kgwp-user-gen-imp-admin.js',
                array('jquery'),
                KGWP_USERGENIMP_PLUGIN_VERSION,
                true
            );
        }
    }


    /**
     * Add admin menu pages
     *
     * Adds the main plugin page and its sub-pages.
     *
     * @return void
     */
    public function admin_pages_init() {

        // Add main page
        $page = $this->settings_pages['main'];

        add_menu_page(
            __($page['page_title'], $this->text_domain),
            __($page['menu_title'], $this->text_domain),
            $page['capability'],
            $page['menu_slug'],
            array($this, $page['callback']),
            $page['icon_url'],
            $page['menu_position']
        );
    }


    /**
     * Initialize admin options
     *
     * Registers settings and adds settings sections for user meta fields.
     *
     * @return void
     */

    public function admin_options_init() {

        register_setting(
            'kgwp_usergenimp_options',
            'kgwp_usergenimp_users_amount',
            'absint'
        );

        add_settings_section(
            'kgwp_usergenimp_default_section',
            __('Default', $this->text_domain),
            array($this, 'render_default_section_description'),
            'kgwp-user-gen-imp'
        );

        add_settings_field(
            'kgwp_usergenimp_users_amount',
            __('Number of Users', $this->text_domain),
            array($this, 'render_users_amount_field'),
            'kgwp-user-gen-imp',
            'kgwp_usergenimp_default_section'
        );
    }


    /**
     * Render admin page for settings
     *
     * @return void
     */
    public function render_admin_page_settings() {

        // Prepare CSV data for display
        $csv_data = $this->prepare_csv_data_for_display();

        // Template variables
        $users_import_data = $csv_data;
        $generated_users = get_transient('kgwp_generated_users');
        $import_result = get_transient('kgwp_import_result');

        // Render template
        include KGWP_USERGENIMP_PLUGIN_PATH . 'templates/admin-page.php';

        // Delete transients
        delete_transient('kgwp_import_result');
        delete_transient('kgwp_upload_result');
    }

    /**
     * Prepare CSV data for display
     *
     * Checks for uploaded files first, then falls back to default users.csv
     *
     * @return array
     */
    private function prepare_csv_data_for_display() {

        // Check if we have an uploaded file to display
        $uploaded_file_path = get_transient('kgwp_uploaded_csv_path');

        if ($uploaded_file_path && file_exists($uploaded_file_path)) {
            return $this->read_csv_file($uploaded_file_path);
        }

        // Fall back to default users.csv
        $file_path = KGWP_USERGENIMP_PLUGIN_PATH . 'users.csv';

        if (file_exists($file_path)) {
            return $this->read_csv_file($file_path);
        } else {
            return array('error' => __('users.csv not found.', KGWP_USERGENIMP_SLUG));
        }
    }

    /**
     * Read CSV file and return data for display
     *
     * @param string $file_path
     * @return array
     */
    private function read_csv_file($file_path) {
        $csv_data = array();

        $file = fopen($file_path, 'r');
        if ($file) {
            while (($line = fgetcsv($file)) !== false) {
                $csv_data[] = $line;
            }
            fclose($file);
        } else {
            $csv_data['error'] = __('Failed to open file', KGWP_USERGENIMP_SLUG);
        }

        return $csv_data;
    }


    /**
     * Render description for default fields section
     *
     * @return void
     */
    public function render_default_section_description() {
        echo '<p>    </p>';
    }


    /**
     * Render input field for number of users to generate
     *
     * @return void
     */
    public function render_users_amount_field() {
        $options = get_option('kgwp_usergenimp_options');
        echo "<input type='number' name='kgwp_usergenimp_users_amount' min='1' max='1000' value='" . esc_attr(get_option('kgwp_usergenimp_users_amount', KGWP_USERGENIMP_DEFAULT_USERS_AMOUNT)) . "' />";
    }
}
