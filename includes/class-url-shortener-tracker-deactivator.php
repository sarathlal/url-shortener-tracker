<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://tinylab.dev
 * @since      1.0.0
 *
 * @package    URL_Shortener_Tracker
 * @subpackage URL_Shortener_Tracker/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    URL_Shortener_Tracker
 * @subpackage URL_Shortener_Tracker/includes
 * @author     TinyLab <hello@tinylab.dev>
 */
class URL_Shortener_Tracker_Deactivator {

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
