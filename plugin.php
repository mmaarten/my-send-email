<?php
/**
 * Plugin Name:       Send Email
 * Plugin URI:        https://github.com/mmaarten/my-send-email
 * Description:       Send email via admin area.
 * Version:           1.0.0
 * Requires at least: 5.0
 * Requires PHP:      5.6
 * Author:            Maarten Menten
 * Author URI:        https://profiles.wordpress.org/maartenm/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       my-send-email
 * Domain Path:       /languages
 */

$autoloader = __DIR__ . '/vendor/autoload.php';
if (!is_readable($autoloader)) {
    error_log(
        sprintf(
            // translators: 1: plugin name. 2: Composer command. 3: theme directory
            __('%1$s installation is incomplete. Run %2$s within the %3$s directory.', 'my-postloaders'),
            __('My Postloaders', 'my-postloaders'),
            '<code>composer install</code>',
            '<code>' . str_replace(ABSPATH, '', __DIR__) . '</code>'
        )
    );
    return;
}
require $autoloader;

define('MY_SEND_EMAIL_PLUGIN_FILE', __FILE__);
define('MY_SEND_EMAIL_NONCE_NAME', 'my_send_email_nonce');

add_action('plugins_loaded', ['\My\SendEmail\App', 'init']);
