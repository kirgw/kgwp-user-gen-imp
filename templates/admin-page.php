<?php

/**
 * Admin page template - Settings
 * @package KGWP\USERGENIMP\Templates
 */

// Security check - exit if accessed directly
defined('ABSPATH') || exit;

?>

<div class="kgwp-user-gen-imp-admin-container">


    <h1><?php _e('KG WP User Generation & Import', KGWP_USERGENIMP_SLUG); ?></h1>

    <p><?php _e('Effortlessly create/add new users to your WordPress site.', KGWP_USERGENIMP_SLUG); ?></p>

    <hr />

    <div class="wrap0">

        <h2>
            <?php esc_html_e('Import Users - reading from file: ', KGWP_USERGENIMP_SLUG); ?>
            <?php echo KGWP_USERGENIMP_DEFAULT_USERS_FILE; ?>
        </h2>

        <?php if (!empty($import_result)) : ?>
            <div class="notice notice-success is-dismissible">
                <p><?php echo esc_html($import_result); ?></p>
            </div>
        <?php endif; ?>

        <p><?php esc_html_e('Here you can import users from a users.csv file in plugin folder (feel free to edit it as needed).', KGWP_USERGENIMP_SLUG); ?></p>

        <div class="import-section">

            <?php if (isset($users_import_data['error'])) : ?>

                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php echo esc_html($users_import_data['error']); ?></th>
                        </tr>
                    </thead>
                </table>

            <?php else : ?>

                <div class="kgwp-users-table-container">
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <?php foreach (array_shift($users_import_data) as $cell) : ?>
                                    <th><?php echo esc_html($cell); ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users_import_data as $line) : ?>
                                <tr>
                                    <?php foreach ($line as $cell) : ?>
                                        <td><?php echo esc_html($cell); ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <?php endif; ?>

            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="import_users">
                <input type="hidden" name="import_type" value="csv">
                <?php wp_nonce_field('import_csv_nonce', 'import_nonce'); ?>
                <input type="submit" class="button button-primary button-hero" value="<?php esc_html_e('Import from CSV', KGWP_USERGENIMP_SLUG); ?>">
            </form>

            <br>

        </div>

        <hr />

        <h2><?php esc_html_e('Generate Users', KGWP_USERGENIMP_SLUG); ?></h2>

        <p><?php esc_html_e('Here you can generate (almost) any amount of random users. Source data can be found in file users-random-src.json.', KGWP_USERGENIMP_SLUG); ?></p>

        <div class="generate-section">
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="generate_users">
                <?php wp_nonce_field('generate_users_nonce', 'generate_nonce'); ?>

                <label for="num-users"><?php esc_html_e('Number of users to generate:', KGWP_USERGENIMP_SLUG); ?></label>
                <input type="number" id="num-users" name="kgwp_usergenimp_users_amount" value="<?php echo esc_attr(get_option('kgwp_usergenimp_users_amount', 10)); ?>">
                <button type="submit" class="button button-primary button-large" name="generate_users"><?php esc_html_e('Generate', KGWP_USERGENIMP_SLUG); ?></button>
            </form>

            <h3><?php esc_html_e('Generated Users (data stored for 1 hour):', KGWP_USERGENIMP_SLUG); ?></h3>

            <div class="kgwp-users-table-container">
            <table class="wp-list-table widefat fixed striped">

                <thead>
                    <tr>
                        <th style="width: 50px;"><?php esc_html_e('Num', KGWP_USERGENIMP_SLUG); ?></th>
                        <th><?php esc_html_e('Username', KGWP_USERGENIMP_SLUG); ?></th>
                        <th><?php esc_html_e('First Name', KGWP_USERGENIMP_SLUG); ?></th>
                        <th><?php esc_html_e('Last Name', KGWP_USERGENIMP_SLUG); ?></th>
                        <th><?php esc_html_e('Email', KGWP_USERGENIMP_SLUG); ?></th>
                        <th><?php esc_html_e('Role', KGWP_USERGENIMP_SLUG); ?></th>
                    </tr>
                </thead>

                <tbody>

                    <?php if (!empty($generated_users)) : ?>

                        <?php $i = 1;
                        foreach ($generated_users as $user) : ?>
                            <tr>
                                <td><?php echo esc_html($i++); ?></td>
                                <td><b><?php echo esc_html($user['user_login']); ?></b></td>
                                <td><?php echo esc_html($user['first_name']); ?></td>
                                <td><?php echo esc_html($user['last_name']); ?></td>
                                <td><?php echo esc_html($user['user_email']); ?></td>
                                <td><?php echo esc_html($user['role']); ?></td>
                            </tr>
                        <?php endforeach; ?>

                    <?php else : ?>
                        <tr><td colspan="6"><?php esc_html_e('No users generated yet, or previous data expired.', KGWP_USERGENIMP_SLUG); ?></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
            </div>

            <?php if (!empty($generated_users)) : // Add buttons for data ?>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <input type="hidden" name="action" value="import_users">
                    <input type="hidden" name="import_type" value="generated">
                    <?php wp_nonce_field('import_generated_nonce', 'import_nonce'); ?>
                    <input type="submit" class="button button-primary button-hero" value="<?php esc_html_e('Import from generated', KGWP_USERGENIMP_SLUG); ?>">
                </form>
            <?php endif; ?>

            <br>

        </div>
    </div>
</div>