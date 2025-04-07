<?php

namespace KGWP\UserGenImp\Inc;

use KGWP\UserGenImp\Inc\Init;

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
        if (Init::is_allowed() === false) {
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


    public static function import_from_csv() {

        if (self::validate_csv() === false) {
            return false;
        }

    }


    public static function validate_csv() {


        // Validate CSV file


        // Validate header fields

    }


    public static function import_from_generated() {

        // Get transient, if not empty
        $generated_users = get_transient('kgwp_generated_users');
        if (empty($generated_users)) {
            return false;
        }

        // Insert the users into the database
        return self::insert_users_in_db($generated_users);
    }




    public static function insert_users_in_db($users_data) {

        $imported = array();

        if (empty($users_data)) {
            return false;
        }

        foreach ($users_data as $usergenimp_user) {

            if (username_exists($usergenimp_user->get_name())) {
                continue;
            }

            $userdata = array(
                'user_login' =>  $usergenimp_user->get_name(),
                'user_email' =>  $usergenimp_user->get_email(),
                'role'       =>  $usergenimp_user->get_role(),
                'user_pass'  =>  '',
            );

            $imported[] = wp_insert_user($userdata);
        }

        return $imported;
    }





}


