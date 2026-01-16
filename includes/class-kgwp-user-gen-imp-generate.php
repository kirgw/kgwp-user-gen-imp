<?php

/**
 * The file defines the generate class
 *
 * @package    KGWP\UserGenImp
 * @subpackage KGWP\UserGenImp\Inc
 */

namespace KGWP\UserGenImp\Inc;

// Security check - exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Generate users class
 *
 * @class KGWP\UserGenImp\Inc\Generate
 */
class Generate {

    private static $names_data;

    /**
     * Load names data from JSON file
     *
     * @return void
     */
    private static function load_names_data() {
        $json_path = plugin_dir_path(dirname(__FILE__)) . 'users-random-src.json';
        $json_data = file_get_contents($json_path);
        self::$names_data = json_decode($json_data, true);
    }


    /**
     * Generate a random name
     *
     * @return string
     */
    public static function generate_random_name() {
        if (! isset(self::$names_data)) {
            self::load_names_data();
        }

        $first_names = self::$names_data['names'];
        $last_names = self::$names_data['lastnames'];

        $first_name = $first_names[array_rand($first_names)];
        $last_name = $last_names[array_rand($last_names)];

        return array('first_name' => $first_name, 'last_name' => $last_name);
    }


    /**
     * Generate an email from first and last name and use random domain from the list
     *
     * @param string $firstName
     * @param string $lastName
     * @return string
     */
    public static function generate_email($firstName, $lastName) {
        if (! isset(self::$names_data)) {
            self::load_names_data();
        }

        $domains = self::$names_data['email_domains'];
        $domain = $domains[array_rand($domains)];

        return strtolower($firstName . '.' . $lastName . '@' . $domain);
    }


    /**
     * Get all available WordPress roles
     *
     * @return array
     */
    public static function get_available_roles() {
        global $wp_roles;

        if (!isset($wp_roles)) {
            $wp_roles = new \WP_Roles();
        }

        $roles = $wp_roles->get_names();

        // Filter out roles that shouldn't be assignable to users
        $excluded_roles = array('administrator'); // Exclude admin role for security

        foreach ($excluded_roles as $excluded_role) {
            unset($roles[$excluded_role]);
        }

        return $roles;
    }

    /**
     * Generate one random user
     *
     * @param array $available_roles Optional array of roles to choose from
     * @return array
     */
    public static function generate_random_user($available_roles = null) {

        // Get available roles if not provided
        if ($available_roles === null) {
            $available_roles = self::get_available_roles();
            $available_roles = array_keys($available_roles); // Get role keys
        }

        // If we still don't have roles, fall back to default 3
        if (empty($available_roles)) {
            $available_roles = array('subscriber', 'editor', 'author');
        }

        // Generate name and email
        $name = self::generate_random_name();
        $first_name = $name['first_name'];
        $last_name  = $name['last_name'];
        $email = self::generate_email($first_name, $last_name);

        // Generate username
        $username = strtolower($first_name . '.' . $last_name);

        // Generate password
        $password = wp_generate_password(12, false);

        $user_data = array(
            'user_login' => $username,
            'user_pass'  => $password,
            'user_email' => $email,
            'first_name' => $first_name,
            'last_name'  => $last_name,
            'role'       => $available_roles[rand(0, (count($available_roles) - 1))],
        );

        // Apply filter to allow modification of user data
        return apply_filters('kgwp_usergenimp_generate_user_data', $user_data);
    }


    /**
     * Generate multiple random users
     *
     * @param mixed $amount
     * @param array $available_roles Optional array of roles to choose from
     * @return array $users_data
     */
    public static function generate_random_users($amount = 3, $available_roles = null) {

        $users_data = array();

        // Iterate $amount times and create array of user data
        for ($i = 1; $i <= $amount; $i++) {
            $users_data[] = self::generate_random_user($available_roles);
        }

        return $users_data;
    }
}
