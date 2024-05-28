<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://tinylab.dev
 * @since      1.0.0
 *
 * @package    WP_URL_Shortener_Tracker
 * @subpackage WP_URL_Shortener_Tracker/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    WP_URL_Shortener_Tracker
 * @subpackage WP_URL_Shortener_Tracker/includes
 * @author     TinyLab <hello@tinylab.dev>
 */
class WP_URL_Shortener_Tracker_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'wp-url-shortener-tracker',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
