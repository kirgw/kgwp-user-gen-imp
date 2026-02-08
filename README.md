# KG WP User Generation & Import

[![License](https://img.shields.io/badge/license-GPLv2-blue.svg)](http://www.gnu.org/licenses/gpl-2.0.html)

Easily generate and/or import user data on your site. Useful for testing or migration.

## Description

**KG WP User Generation & Import** is a powerful tool for developers and site administrators who need to quickly populate their WordPress site with user data. Whether you're testing a new theme, developing a membership site, or migrating data, this plugin simplifies the process of creating multiple users.

## Features

*   **Generate Random Users:** Create any number of random users with realistic names, emails, and bios.
*   **Customizable Source Data:** Random data is pulled from a JSON file (`users-random-src.json`) that you can easily modify.
*   **Import from CSV:** Upload your own CSV file to import users in bulk.
*   **Data Preview:** Preview your CSV data or generated users before importing them.
*   **Download Generated Data:** Export your generated random users to a CSV file for use elsewhere.
*   **Role Selection:** Choose which WordPress roles to assign to generated users.

## Installation

1.  Upload the plugin folder to the `/wp-content/plugins/` directory.
2.  Activate the plugin through the 'Plugins' menu in WordPress.
3.  Go to **Users Import** in the admin menu to start using the plugin.

## Usage

### Generating Users
1.  Navigate to the **Generate** tab.
2.  Enter the number of users you wish to generate.
3.  Select the roles you want to assign.
4.  Click **Generate**.
5.  Review the generated users and click **Import from generated** to add them to your site.

### Importing from CSV
1.  Navigate to the **Import** tab.
2.  Upload a CSV file with the required columns (`user_login`, `user_email`, `role`).
3.  Review the data in the preview table.
4.  Click **Import from CSV**.

## Security

This plugin follows WordPress security best practices:
*   **Nonces:** All form submissions are protected with nonces.
*   **Capability Checks:** All actions are restricted to users with `manage_options` capability.
*   **Sanitization:** All user inputs are sanitized before processing.
*   **Escaping:** All outputs are escaped to prevent XSS.

## License

This plugin is licensed under the [GPLv2 or later](http://www.gnu.org/licenses/gpl-2.0.html).
