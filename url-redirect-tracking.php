<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://tinylab.dev
 * @since             1.0.0
 * @package           Url_Redirect_Tracking
 *
 * @wordpress-plugin
 * Plugin Name:       URL Redirect and Tracking
 * Plugin URI:        https://tinylab.dev
 * Description:       Add URL, redirect & track the URLs for better clarity
 * Version:           1.0.0
 * Author:            TinyLab
 * Author URI:        https://tinylab.dev/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       url-redirect-tracking
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'URL_REDIRECT_TRACKING_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-url-redirect-tracking-activator.php
 */
function activate_url_redirect_tracking() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-url-redirect-tracking-activator.php';
	Url_Redirect_Tracking_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-url-redirect-tracking-deactivator.php
 */
function deactivate_url_redirect_tracking() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-url-redirect-tracking-deactivator.php';
	Url_Redirect_Tracking_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_url_redirect_tracking' );
register_deactivation_hook( __FILE__, 'deactivate_url_redirect_tracking' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-url-redirect-tracking.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_url_redirect_tracking() {

	$plugin = new Url_Redirect_Tracking();
	$plugin->run();

}
run_url_redirect_tracking();
