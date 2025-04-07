<?php

/**
 * The file defines the import class
 *
 * @package    KGWP\UserGenImp
 * @subpackage KGWP\UserGenImp\Inc
 */

namespace KGWP\UserGenImp\Inc;

// Security check - exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Import class (CSV)
 *
 * @class KGWP\UserGenImp\Inc\Import
 */
class Import {

    /**
     * Import type
     *
     * @var string
     */
    public $import_type;


    /**
     * Constructor
     *
     * @return void
     */
    public function __construct($import_type = 'generated') {

        // Set the type
        $this->import_type = $import_type;
    }


    /**
     * Gather and insert the data
     *
     * @return void
     */
    public function launch($import_type = 'generated') {

        // Check the access
        if (\KGWP\UserGenImp\Inc\Init::is_allowed() === false) {
            return;
        }

        // Check the import type
        if (!in_array($import_type, array('csv', 'generated'))) {
            return;
        }

        // Based on the import type, call the appropriate method
        switch ($import_type) {
            case 'csv':
                return self::import_from_csv();
            case 'generated':
                return self::import_from_generated();
        }

        // Return false if the import type is not valid
        return false;
    }


    /**
     * Import users from a CSV file
     *
     * The CSV file should be located in the plugin directory and named "users.csv".
     * The file should have the following columns:
     *  - username
     *  - email
     *  - role
     *
     * @return array An array of integers representing the IDs of the users that were successfully imported
     */
    public static function import_from_csv() {

        $file_path = KGWP_USERGENIMP_PLUGIN_PATH . 'users.csv';

        if (self::validate_csv() === false) {
            return false;
        }

        $file = fopen($file_path, 'r');
        if (!$file) {
            error_log('CSV Import: Could not open file at ' . $file_path);
            return false;
        }

        $header = fgetcsv($file); // Skip the header row

        $users_data = array();
        while (($data = fgetcsv($file)) !== false) {

            $username = $data[0];
            $email = $data[1];
            $role = $data[2];

            $users_data[] = array(
                'username' => $username,
                'email' => $email,
                'role' => $role,
            );
        }

        fclose($file);

        $imported = self::insert_users_in_db($users_data);

        return $imported;
    }


    /**
     * Insert a single user into the database
     *
     * @param string $username The username of the user to insert
     * @param string $email The email of the user to insert
     * @param string $role The role of the user to insert
     *
     * @return int|false The ID of the inserted user, or false on failure
     */
    private static function insert_single_user_in_db($username, $email, $role) {

        $username = sanitize_text_field($username);
        $email = sanitize_email($email);
        $role = sanitize_text_field($role);

        if (username_exists($username) || email_exists($email)) {
            error_log("CSV Import: User already exists: username=$username, email=$email");
            return false;
        }

        $userdata = array(
            'user_login' => $username,
            'user_email' => $email,
            'role' => $role,
            'user_pass' => wp_generate_password(12, false), // Generate a random password
        );

        $user_id = wp_insert_user($userdata);

        if (is_wp_error($user_id)) {
            error_log('CSV Import: Error creating user: ' . $user_id->get_error_message());
            return false;
        }

        return $user_id;
    }


    /**
     * Validate a CSV file
     *
     * Checks if the file exists, can be opened, has a valid header row, and contains the required fields
     *
     * @return bool
     */
    public static function validate_csv() {

        $file_path = KGWP_USERGENIMP_PLUGIN_PATH . 'users.csv';

        if (!file_exists($file_path)) {
            error_log('CSV Import: File not found at ' . $file_path);
            return false;
        }

        $file = fopen($file_path, 'r');
        if (!$file) {
            error_log('CSV Import: Could not open file at ' . $file_path);
            fclose($file); // Close file before returning
            return false;
        }

        $header = fgetcsv($file);
        fclose($file);

        if ($header === false) {
            error_log('CSV Import: Could not read header row from ' . $file_path);
            return false;
        }

        $required_fields = array('username', 'email', 'role');
        foreach ($required_fields as $field) {

            if (!in_array($field, $header)) {
                error_log('CSV Import: Missing required field "' . $field . '" in header row of ' . $file_path);
                return false;
            }
        }

        return true;
    }


    /**
     * Import users from the transient variable 'kgwp_generated_users' to the database
     *
     * @return array|false An array of user IDs of the inserted users, or false on failure
     */
    public static function import_from_generated() {

        // Get transient, if not empty
        $generated_users = get_transient('kgwp_generated_users');
        if (empty($generated_users)) {
            return false;
        }

        $users_data = array();
        foreach ($generated_users as $user_data) {
            $users_data[] = array(
                'username' => $user_data['username'],
                'email' => $user_data['email'],
                'role' => $user_data['role'],
            );
        }

        // Insert the users into the database
        return self::insert_users_in_db($users_data);
    }


    /**
     * Insert users into the database
     *
     * @param array $users_data array of objects with properties 'name', 'email', 'role'
     *
     * @return array|false array of user IDs of the inserted users
     */
    public static function insert_users_in_db($users_data) {

        $imported = array();

        if (empty($users_data)) {
            return false;
        }

        foreach ($users_data as $user_data) {

            $username = $user_data['username'];
            $email = $user_data['email'];
            $role = $user_data['role'];

            if (username_exists($username) || email_exists($email)) {
                continue;
            }

            $user_id = self::insert_single_user_in_db($username, $email, $role);

            if ($user_id) {
                $imported[] = $user_id;
            }
        }

        return $imported;
    }
}
