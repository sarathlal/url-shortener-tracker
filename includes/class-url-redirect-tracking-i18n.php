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
 * @package    Url_Redirect_Tracking
 * @subpackage Url_Redirect_Tracking/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Url_Redirect_Tracking
 * @subpackage Url_Redirect_Tracking/includes
 * @author     TinyLab <hello@tinylab.dev>
 */
class Url_Redirect_Tracking_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'url-redirect-tracking',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
