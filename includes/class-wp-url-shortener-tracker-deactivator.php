<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://tinylab.dev
 * @since      1.0.0
 *
 * @package    WP_URL_Shortener_Tracker
 * @subpackage WP_URL_Shortener_Tracker/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    WP_URL_Shortener_Tracker
 * @subpackage WP_URL_Shortener_Tracker/includes
 * @author     TinyLab <hello@tinylab.dev>
 */
class WP_URL_Shortener_Tracker_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {

		flush_rewrite_rules();
	}

}
