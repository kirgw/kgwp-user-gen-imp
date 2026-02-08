=== KG WP User Generation & Import ===
Contributors: kirgw
Tags: users, generation, import, csv, random users
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Easily generate and/or import user data on your site. Useful for testing or migration.

== Description ==

KG WP User Generation & Import is a powerful tool for developers and site administrators who need to quickly populate their WordPress site with user data. Whether you're testing a new theme, developing a membership site, or migrating data, this plugin simplifies the process of creating multiple users.

= Features =

*   **Generate Random Users:** Create any number of random users with realistic names, emails, and bios.
*   **Customizable Source Data:** Random data is pulled from a JSON file that you can easily modify.
*   **Import from CSV:** Upload your own CSV file to import users in bulk.
*   **Data Preview:** Preview your CSV data or generated users before importing them.
*   **Download Generated Data:** Export your generated random users to a CSV file for use elsewhere.
*   **Role Selection:** Choose which WordPress roles to assign to generated users.

== Installation ==

1. Upload the plugin folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to 'Users Import' in the admin menu to start using the plugin.

== Frequently Asked Questions ==

= What CSV format is required? =
The CSV should include headers: `user_login`, `user_email`, and `role`. Optional fields include `first_name`, `last_name`, `user_pass`, and `description`.

= Can I customize the random data? =
Yes, you can edit the `users-random-src.json` file in the plugin directory to add your own names, domains, and bio templates.

== Screenshots ==

1. The main plugin interface showing the Import tab.
2. The Generate tab with role selection and user count.

== Changelog ==

= 1.0.0 =
* Added random user generation.
* Added CSV import functionality.
* Added data preview and export.

= 0.1.0 =
*   Plugin initial release
