<?php

/**
 * The file defines the core plugin class
 *
 * @package    KGWP\UserGenImp
 * @subpackage KGWP\UserGenImp\Inc
 */

namespace KGWP\UserGenImp\Inc;

// Security check - exit if accessed directly
defined('ABSPATH') || exit;

final class Init {

    /**
     * Instance of the class
     *
     * @var \KGWP\UserGenImp\Inc\Init
     */
    protected static $_instance = null;

    // Class instances
    public $admin_page;
    public $import;
    public $generate;

    /**
     * Store the main instance (singleton)
     *
     * @return \KGWP\UserGenImp\Inc\Init
     */
    public static function instance() {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }


    /**
     * Constructor
     *
     * @return void
     */
    public function __construct() {

        // Load needed classes with autoloader
        $this->admin_page = new \KGWP\UserGenImp\Inc\AdminPages();
        $this->import = new \KGWP\UserGenImp\Inc\Import();
        $this->generate = new \KGWP\UserGenImp\Inc\Generate();

        // Set locale
        $this->set_locale();

        // Enqueue assets
        // add_action('init', array( $this, 'enqueue_assets'));
    }


    /**
     * Enqueue CSS and JS
     *
     * @return void
     */
    public function enqueue_assets() {

        wp_enqueue_style(
            'kgwp-user-gen-imp-styles',
            KGWP_USERGENIMP_PLUGIN_URL . 'assets/kgwp-user-gen-imp-frontend-styles.css', // not used
            array(),
            KGWP_USERGENIMP_PLUGIN_VERSION,
            'all'
        );

        wp_enqueue_script(
            'kgwp-user-gen-imp-scripts',
            KGWP_USERGENIMP_PLUGIN_URL . 'assets/kgwp-user-gen-imp-scripts.js', // not used
            array('jquery'),
            KGWP_USERGENIMP_PLUGIN_VERSION,
            false
        );
    }


    /**
     * Set locale and allow i18n of the plugin
     *
     * @return void
     */
    public function set_locale() {

        // Set the locale, use plugin name as domain
        $locale = determine_locale();
        $locale = apply_filters('plugin_locale', $locale, KGWP_USERGENIMP_PLUGIN_NAME);

        load_textdomain(
            KGWP_USERGENIMP_SLUG,
            WP_LANG_DIR . '/kgwp-user-gen-imp/kgwp-user-gen-imp-' . $locale . '.mo'
        );

        load_plugin_textdomain(
            KGWP_USERGENIMP_SLUG,
            false,
            KGWP_USERGENIMP_PLUGIN_NAME . '/languages/'
        );
    }


    /**
     * Allowed capability to view the data
     *
     * @return string
     */
    public static function allowed_capability() {
        return 'list_users';
    }


    /**
     * User access control
     *
     * @return bool
     */
    public static function is_allowed() {

        // Restriction only for table now
        return current_user_can(self::allowed_capability());
    }
}
