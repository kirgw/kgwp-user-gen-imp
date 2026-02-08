<?php

/**
 * Admin page template - Settings
 * @package KGWP\USERGENIMP\Templates
 */

// Security check - exit if accessed directly
defined('ABSPATH') || exit;

?>

<div class="kgwp-user-gen-imp-admin-container" role="main" aria-labelledby="plugin-title">

    <h1 id="plugin-title"><?php _e('KG WP User Generation & Import', KGWP_USERGENIMP_SLUG); ?></h1>

    <p id="plugin-description"><?php esc_html_e('Effortlessly add new users to your WordPress site.', KGWP_USERGENIMP_SLUG); ?></p>

    <!-- Tab Navigation -->
    <div class="kgwp-tabs-navigation" role="tablist" aria-label="Plugin functionality tabs">
        <button class="kgwp-tab-button active" role="tab" aria-selected="true" aria-controls="import-tab" id="import-tab-button" tabindex="0">
            <?php esc_html_e('Import', KGWP_USERGENIMP_SLUG); ?></button>
        <button class="kgwp-tab-button" role="tab" aria-selected="false" aria-controls="generate-tab" id="generate-tab-button" tabindex="-1">
            <?php esc_html_e('Generate', KGWP_USERGENIMP_SLUG); ?></button>
    </div>

    <!-- Tab Content -->
    <div class="kgwp-tabs-content">

        <!-- Import Tab -->
        <div id="import-tab" class="kgwp-tab-content active" role="tabpanel" aria-labelledby="import-tab-button">
            <div class="wrap0" role="region" aria-labelledby="import-users-heading">

                <h2 id="import-users-heading"><?php esc_html_e('Import Users', KGWP_USERGENIMP_SLUG); ?></h2>

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
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data" aria-labelledby="csv-upload-heading">
                        <input type="hidden" name="action" value="upload_csv">
                        <?php wp_nonce_field('upload_csv_nonce', 'upload_csv_nonce'); ?>

                        <div class="file-upload-wrapper">
                            <label for="csv_file" class="screen-reader-text"><?php esc_html_e('Choose CSV file', KGWP_USERGENIMP_SLUG); ?></label>
                            <input type="file" name="csv_file" id="csv_file" accept=".csv" required aria-required="true" aria-describedby="csv-file-description">
                            <button type="submit" class="button button-secondary" aria-label="<?php esc_attr_e('Upload CSV file', KGWP_USERGENIMP_SLUG); ?>">
                                <?php esc_html_e('Upload CSV', KGWP_USERGENIMP_SLUG); ?></button>
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
                            <table class="wp-list-table widefat fixed striped" role="grid" aria-describedby="csv-preview-description">
                                <thead>
                                    <tr role="row">

                                        <?php
                                        // Check if $users_import_data is an array and not empty before array_shift
                                        if (is_array($users_import_data) && !empty($users_import_data)) {
                                            $header_row = array_shift($users_import_data);
                                            foreach ($header_row as $cell) : ?>
                                                <th role="columnheader" scope="col"><?php echo esc_html($cell); ?></th>
                                            <?php endforeach;
                                        }
                                        else { ?>
                                            <th role="columnheader" scope="col"><?php esc_html_e('No data available or invalid CSV format.', KGWP_USERGENIMP_SLUG); ?></th>
                                        <?php } ?>

                                    </tr>
                                </thead>
                                <tbody>

                                    <?php
                                    // Ensure $users_import_data is an array before iterating
                                    if (is_array($users_import_data)) :
                                        foreach ($users_import_data as $line) : ?>
                                            <tr role="row">
                                                <?php foreach ($line as $cell) : ?>
                                                    <td role="gridcell"><?php echo esc_html($cell); ?></td>
                                                <?php endforeach; ?>
                                            </tr>
                                        <?php endforeach;
                                    else : ?>
                                        <tr role="row">
                                            <td role="gridcell" colspan="3"><?php esc_html_e('No user data to display.', KGWP_USERGENIMP_SLUG); ?></td>
                                        </tr>
                                    <?php endif; ?>

                                </tbody>
                            </table>
                            <p id="csv-preview-description" class="screen-reader-text"><?php esc_html_e('CSV data preview table showing users to be imported', KGWP_USERGENIMP_SLUG); ?></p>
                        </div>

                    <?php endif; ?>

                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" aria-labelledby="csv-import-heading">
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
                        <input type="submit" class="button button-primary button-hero" value="<?php esc_html_e('Import from CSV', KGWP_USERGENIMP_SLUG); ?>" data-user-count="<?php echo esc_attr($csv_user_count); ?>" aria-label="<?php esc_attr_e('Import users from CSV file', KGWP_USERGENIMP_SLUG); ?> <?php echo esc_attr(sprintf(__('(%d users)', KGWP_USERGENIMP_SLUG), $csv_user_count)); ?>">
                    </form>
                    <br>

                </div>
            </div>
        </div>

        <!-- Generate Tab -->
        <div id="generate-tab" class="kgwp-tab-content" role="tabpanel" aria-labelledby="generate-tab-button" hidden>
            <div class="wrap0" role="region" aria-labelledby="generate-users-heading">

                <h2 id="generate-users-heading"><?php esc_html_e('Generate Users', KGWP_USERGENIMP_SLUG); ?></h2>

                <p id="generate-users-description"><?php esc_html_e('Here you can generate (almost) any amount of random users. Source data can be found in file users-random-src.json.', KGWP_USERGENIMP_SLUG); ?></p>

                <div class="generate-section" role="region" aria-labelledby="generate-users-heading" aria-describedby="generate-users-description">
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" aria-labelledby="generate-users-heading">
                        <input type="hidden" name="action" value="generate_users">
                        <?php wp_nonce_field('generate_users_nonce', 'generate_nonce'); ?>

                        <div class="generate-settings">
                            <label for="num-users"><?php esc_html_e('Number of users to generate:', KGWP_USERGENIMP_SLUG); ?></label>
                            <input type="number" id="num-users" name="kgwp_usergenimp_users_amount" value="<?php echo esc_attr(get_option('kgwp_usergenimp_users_amount', 10)); ?>" aria-required="true" min="1" aria-describedby="num-users-description">
                            <span id="num-users-description" class="screen-reader-text"><?php esc_html_e('Enter the number of users to generate', KGWP_USERGENIMP_SLUG); ?></span>
                            <br><br>

                            <!-- Role selection field -->
                            <div class="kgwp-roles-selection" role="group" aria-labelledby="roles-heading">
                                <h4 id="roles-heading"><?php esc_html_e('User Roles', KGWP_USERGENIMP_SLUG); ?></h4>
                                <p class="description" id="roles-description"><?php esc_html_e('Select which roles should be used when generating users.', KGWP_USERGENIMP_SLUG); ?></p>

                                <?php
                                $available_roles = \KGWP\UserGenImp\Inc\Generate::get_available_roles();
                                $selected_roles = get_option('kgwp_usergenimp_selected_roles', array());

                                foreach ($available_roles as $role_key => $role_name) {
                                    $checkbox_id = 'role-' . esc_attr($role_key);
                                    $checked = in_array($role_key, $selected_roles) ? 'checked="checked"' : '';
                                    echo '<label class="kgwp-role-checkbox" for="' . $checkbox_id . '">';
                                    echo '<input type="checkbox" name="kgwp_usergenimp_selected_roles[]" id="' . $checkbox_id . '" value="' . esc_attr($role_key) . '" ' . $checked . ' aria-describedby="roles-description"> ';
                                    echo esc_html($role_name);
                                    echo '</label>';
                                }
                                ?>

                                <div id="select-all-roles-container">
                                    <label class="kgwp-role-checkbox" for="select-all-roles">
                                        <input type="checkbox" id="select-all-roles" aria-label="<?php esc_attr_e('Select or deselect all roles', KGWP_USERGENIMP_SLUG); ?>"> <?php esc_html_e('Select/Deselect All', KGWP_USERGENIMP_SLUG); ?>
                                    </label>
                                </div>

                            </div>

                            <button type="submit" class="button button-primary button-large" name="generate_users" aria-label="<?php esc_attr_e('Generate users with selected settings', KGWP_USERGENIMP_SLUG); ?>">
                                <?php esc_html_e('Generate', KGWP_USERGENIMP_SLUG); ?></button>
                        </div>
                    </form>

                    <h3><?php esc_html_e('Generated Users (data stored for 1 hour):', KGWP_USERGENIMP_SLUG); ?></h3>

                    <div class="kgwp-users-table-container">
                        <table class="wp-list-table widefat fixed striped" role="grid" aria-describedby="generated-users-description">

                            <thead>
                                <tr role="row">
                                    <th style="width: 50px;" role="columnheader" scope="col"><?php esc_html_e('Num', KGWP_USERGENIMP_SLUG); ?></th>
                                    <th role="columnheader" scope="col"><?php esc_html_e('Username', KGWP_USERGENIMP_SLUG); ?></th>
                                    <th role="columnheader" scope="col"><?php esc_html_e('First Name', KGWP_USERGENIMP_SLUG); ?></th>
                                    <th role="columnheader" scope="col"><?php esc_html_e('Last Name', KGWP_USERGENIMP_SLUG); ?></th>
                                    <th role="columnheader" scope="col"><?php esc_html_e('Email', KGWP_USERGENIMP_SLUG); ?></th>
                                    <th role="columnheader" scope="col"><?php esc_html_e('Role', KGWP_USERGENIMP_SLUG); ?></th>
                                    <th role="columnheader" scope="col"><?php esc_html_e('Bio', KGWP_USERGENIMP_SLUG); ?></th>
                                </tr>
                            </thead>

                            <tbody>

                                <?php if (!empty($generated_users)) : ?>

                                    <?php $i = 1;
                                    foreach ($generated_users as $user) : ?>
                                        <tr role="row">
                                            <td role="gridcell"><?php echo esc_html($i++); ?></td>
                                            <td role="gridcell"><b><?php echo esc_html($user['user_login']); ?></b></td>
                                            <td role="gridcell"><?php echo esc_html($user['first_name']); ?></td>
                                            <td role="gridcell"><?php echo esc_html($user['last_name']); ?></td>
                                            <td role="gridcell"><?php echo esc_html($user['user_email']); ?></td>
                                            <td role="gridcell"><?php echo esc_html($user['role']); ?></td>
                                            <td role="gridcell"><?php echo esc_html($user['description']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>

                                <?php else : ?>
                                    <tr role="row">
                                        <td role="gridcell" colspan="6"><?php esc_html_e('No users generated yet, or previous data expired.', KGWP_USERGENIMP_SLUG); ?></td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        <p id="generated-users-description" class="screen-reader-text"><?php esc_html_e('Generated users table showing users that can be imported', KGWP_USERGENIMP_SLUG); ?></p>
                    </div>

                    <?php if (!empty($generated_users)) : // Add buttons for data
                    ?>
                        <div class="generated-users-actions">
                            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" aria-labelledby="generated-import-heading">
                                <input type="hidden" name="action" value="import_users">
                                <input type="hidden" name="import_type" value="generated">
                                <?php wp_nonce_field('import_generated_nonce', 'import_nonce'); ?>
                                <input type="submit" class="button button-primary button-hero" value="<?php esc_html_e('Import from generated', KGWP_USERGENIMP_SLUG); ?>" data-user-count="<?php echo esc_attr(count($generated_users)); ?>" aria-label="<?php esc_attr_e('Import generated users', KGWP_USERGENIMP_SLUG); ?> <?php echo esc_attr(sprintf(__('(%d users)', KGWP_USERGENIMP_SLUG), count($generated_users))); ?>">
                            </form>

                            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" aria-labelledby="generated-download-heading">
                                <input type="hidden" name="action" value="download_generated_csv">
                                <?php wp_nonce_field('download_generated_csv_nonce', 'download_csv_nonce'); ?>
                                <input type="submit" class="button button-secondary button-hero" value="<?php esc_html_e('Download as CSV', KGWP_USERGENIMP_SLUG); ?>" aria-label="<?php esc_attr_e('Download generated users as CSV', KGWP_USERGENIMP_SLUG); ?> <?php echo esc_attr(sprintf(__('(%d users)', KGWP_USERGENIMP_SLUG), count($generated_users))); ?>">
                            </form>
                        </div>
                    <?php endif; ?>
                    <br>

                </div>
            </div>
        </div>
    </div>
</div>
