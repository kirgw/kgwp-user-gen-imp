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

        <h2><?php esc_html_e('Import Users', KGWP_USERGENIMP_SLUG); ?></h2>

        <?php if (!empty($import_result)) : ?>
            <div class="notice notice-success is-dismissible">
                <p><?php echo esc_html($import_result); ?></p>
            </div>
        <?php endif; ?>

        <?php
        // Check for upload result
        $upload_result = get_transient('kgwp_upload_result');
        if (!empty($upload_result)) :
        ?>
            <div class="notice notice-info is-dismissible">
                <?php echo wp_kses_post($upload_result); ?>
            </div>
        <?php endif; ?>

        <p><?php esc_html_e('Upload your own CSV file or use the default users.csv file in the plugin folder.', KGWP_USERGENIMP_SLUG); ?></p>

        <!-- File Upload Section -->
        <div class="csv-upload-section">
            <h3><?php esc_html_e('Upload CSV File', KGWP_USERGENIMP_SLUG); ?></h3>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data">
                <input type="hidden" name="action" value="upload_csv">
                <?php wp_nonce_field('upload_csv_nonce', 'upload_csv_nonce'); ?>

                <div class="file-upload-wrapper">
                    <input type="file" name="csv_file" id="csv_file" accept=".csv" required>
                    <button type="submit" class="button button-secondary"><?php esc_html_e('Upload CSV', KGWP_USERGENIMP_SLUG); ?></button>
                </div>

                <p class="description">
                    <?php esc_html_e('CSV file should have columns: username, email, role', KGWP_USERGENIMP_SLUG); ?>
                </p>
            </form>
        </div>

        <div class="import-section">

            <h3><?php esc_html_e('CSV Data Preview', KGWP_USERGENIMP_SLUG); ?></h3>

            <?php
            // Check if we're displaying an uploaded file
            $uploaded_file_path = get_transient('kgwp_uploaded_csv_path');
            if ($uploaded_file_path && file_exists($uploaded_file_path)) :
            ?>
                <p class="current-file-info">
                    <?php esc_html_e('Currently displaying uploaded file:', KGWP_USERGENIMP_SLUG); ?>
                    <strong><?php echo esc_html(basename($uploaded_file_path)); ?></strong>
                </p>
            <?php else : ?>
                <p class="current-file-info">
                    <?php esc_html_e('Currently displaying default file:', KGWP_USERGENIMP_SLUG); ?>
                    <strong><?php echo esc_html(KGWP_USERGENIMP_DEFAULT_USERS_FILE); ?></strong>
                </p>
            <?php endif; ?>

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
                                <?php
                                // Check if $users_import_data is an array and not empty before array_shift
                                if (is_array($users_import_data) && !empty($users_import_data)) {
                                    $header_row = array_shift($users_import_data);
                                    foreach ($header_row as $cell) : ?>
                                        <th><?php echo esc_html($cell); ?></th>
                                    <?php endforeach;
                                } else { ?>
                                    <th><?php esc_html_e('No data available or invalid CSV format.', KGWP_USERGENIMP_SLUG); ?></th>
                                <?php } ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Ensure $users_import_data is an array before iterating
                            if (is_array($users_import_data)) :
                                foreach ($users_import_data as $line) : ?>
                                    <tr>
                                        <?php foreach ($line as $cell) : ?>
                                            <td><?php echo esc_html($cell); ?></td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach;
                            else : ?>
                                <tr>
                                    <td colspan="3"><?php esc_html_e('No user data to display.', KGWP_USERGENIMP_SLUG); ?></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            <?php endif; ?>

            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="import_users">
                <input type="hidden" name="import_type" value="csv">
                <?php wp_nonce_field('import_csv_nonce', 'import_nonce'); ?>
                <?php
                // Calculate CSV user count (subtract 1 for header row)
                $csv_user_count = 0;
                if (is_array($users_import_data) && !empty($users_import_data)) {
                    // If we have a header row, subtract 1
                    if (isset($users_import_data[0]) && is_array($users_import_data[0])) {
                        $csv_user_count = max(0, count($users_import_data) - 1);
                    }
                }
                ?>
                <input type="submit" class="button button-primary button-hero" value="<?php esc_html_e('Import from CSV', KGWP_USERGENIMP_SLUG); ?>" data-user-count="<?php echo esc_attr($csv_user_count); ?>">
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

                <div class="generate-settings">
                    <label for="num-users"><?php esc_html_e('Number of users to generate:', KGWP_USERGENIMP_SLUG); ?></label>
                    <input type="number" id="num-users" name="kgwp_usergenimp_users_amount" value="<?php echo esc_attr(get_option('kgwp_usergenimp_users_amount', 10)); ?>">
                    <br><br>

                    <!-- Role selection field -->
                    <div class="kgwp-roles-selection">
                        <h4><?php esc_html_e('User Roles', KGWP_USERGENIMP_SLUG); ?></h4>
                        <p class="description"><?php esc_html_e('Select which roles should be used when generating users.', KGWP_USERGENIMP_SLUG); ?></p>

                        <?php
                        $available_roles = \KGWP\UserGenImp\Inc\Generate::get_available_roles();
                        $selected_roles = get_option('kgwp_usergenimp_selected_roles', array());

                        foreach ($available_roles as $role_key => $role_name) {
                            $checked = in_array($role_key, $selected_roles) ? 'checked="checked"' : '';
                            echo '<label class="kgwp-role-checkbox">';
                            echo '<input type="checkbox" name="kgwp_usergenimp_selected_roles[]" value="' . esc_attr($role_key) . '" ' . $checked . '> ';
                            echo esc_html($role_name);
                            echo '</label>';
                        }
                        ?>

                        <div id="select-all-roles-container">
                            <label class="kgwp-role-checkbox">
                                <input type="checkbox" id="select-all-roles"> <?php esc_html_e('Select/Deselect All', KGWP_USERGENIMP_SLUG); ?>
                            </label>
                        </div>

                    </div>

                    <button type="submit" class="button button-primary button-large" name="generate_users"><?php esc_html_e('Generate', KGWP_USERGENIMP_SLUG); ?></button>
                </div>
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
                            <tr>
                                <td colspan="6"><?php esc_html_e('No users generated yet, or previous data expired.', KGWP_USERGENIMP_SLUG); ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if (!empty($generated_users)) : // Add buttons for data
            ?>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <input type="hidden" name="action" value="import_users">
                    <input type="hidden" name="import_type" value="generated">
                    <?php wp_nonce_field('import_generated_nonce', 'import_nonce'); ?>
                    <input type="submit" class="button button-primary button-hero" value="<?php esc_html_e('Import from generated', KGWP_USERGENIMP_SLUG); ?>" data-user-count="<?php echo esc_attr(count($generated_users)); ?>">
                </form>
            <?php endif; ?>

            <br>

        </div>
    </div>
</div>