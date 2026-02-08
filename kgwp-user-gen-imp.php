<?php
/*
* Plugin Name: KG WP User Generation & Import
* Description: Plugin to generate random users and import them from CSV to WordPress
* Author: Kirill G.
* Version: 1.0.0
* License: GPLv2 or later
* Text Domain: kgwp-user-gen-imp
*/

namespace KGWP\UserGenImp;

// Security check - exit if accessed directly
defined('ABSPATH') || exit;

// Setup the constants
define('KGWP_USERGENIMP_PLUGIN_NAME', plugin_basename(__FILE__));
define('KGWP_USERGENIMP_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('KGWP_USERGENIMP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('KGWP_USERGENIMP_PLUGIN_VERSION', '1.0.0');
define('KGWP_USERGENIMP_SLUG', 'kgwp-user-gen-imp'); // slug both for menu and i18n
define('KGWP_USERGENIMP_DEFAULT_USERS_AMOUNT', 10);
define('KGWP_USERGENIMP_DEFAULT_USERS_FILE', 'users.csv');

// PSR-4 Autoloader (WordPress-style class names)
spl_autoload_register(function ($class) {

    $prefix = 'KGWP\\UserGenImp\\';
    $base_dir = KGWP_USERGENIMP_PLUGIN_PATH . 'includes/';

    // Check if the class uses the namespace prefix
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    // Convert namespace to WordPress-style class filename
    $relative_class = substr($class, $len);
    $relative_class = str_replace('Inc\\', '', $relative_class); // rem "Inc\" from the path
    $relative_class = ltrim($relative_class, '\\');

    // Remove leading slashes and replace namespace separators with hyphens
    $file_name = str_replace('\\', '-', $relative_class);

    // Construct the file path
    $file = $base_dir . 'class-kgwp-user-gen-imp-' . strtolower($file_name) . '.php';

    // Debug
    // error_log('Autoloading: ' . $file . ' | class: ' . $class);

    // Check if the file exists
    if (file_exists($file)) {
        require $file;
    }
    elseif (defined('WP_DEBUG') && WP_DEBUG) {
        error_log("Autoloader failed: Class {$class} not found at {$file}");
    }
});

// Start the plugin
\KGWP\UserGenImp\Inc\Init::instance();
