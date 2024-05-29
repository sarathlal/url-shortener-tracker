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
 * @package           URL_Shortener_Tracker
 *
 * @wordpress-plugin
 * Plugin Name:       WP URL Shortener & Tracker
 * Plugin URI:        https://tinylab.dev
 * Description:       WP URL Shortener & Tracker is a tool to manage, shorten, and track URLs from WordPress. Monitor click analytics and streamline your URL management.
 * Version:           1.0.0
 * Author:            TinyLab
 * Author URI:        https://tinylab.dev/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       url-shortener-tracker
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
define( 'URL_Shortener_Tracker_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-url-shortener-tracker-activator.php
 */
function activate_URL_Shortener_Tracker() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-url-shortener-tracker-activator.php';
	URL_Shortener_Tracker_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-url-shortener-tracker-deactivator.php
 */
function deactivate_URL_Shortener_Tracker() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-url-shortener-tracker-deactivator.php';
	URL_Shortener_Tracker_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_URL_Shortener_Tracker' );
register_deactivation_hook( __FILE__, 'deactivate_URL_Shortener_Tracker' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-url-shortener-tracker.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_URL_Shortener_Tracker() {

	$plugin = new URL_Shortener_Tracker();
	$plugin->run();

}
run_URL_Shortener_Tracker();
