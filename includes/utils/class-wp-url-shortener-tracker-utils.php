<?php

/**
 * Common Utility functions for the plugin
 *
 * @link       https://tinylab.dev
 * @since      1.0.0
 *
 * @package    WP_URL_Shortener_Tracker
 * @subpackage WP_URL_Shortener_Tracker/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    WP_URL_Shortener_Tracker
 * @subpackage WP_URL_Shortener_Tracker/includes
 * @author     TinyLab <hello@tinylab.dev>
 */
class WP_URL_Shortener_Tracker_Utils {

    public static function write_log ( $log )  {
        if ( true === WP_DEBUG ) {
            if ( is_array( $log ) || is_object( $log ) ) {
                error_log( print_r( $log, true ) );
            } else {
                error_log( $log );
            }
        }
    }

}
