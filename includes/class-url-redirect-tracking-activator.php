<?php

/**
 * Fired during plugin activation
 *
 * @link       https://tinylab.dev
 * @since      1.0.0
 *
 * @package    Url_Redirect_Tracking
 * @subpackage Url_Redirect_Tracking/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Url_Redirect_Tracking
 * @subpackage Url_Redirect_Tracking/includes
 * @author     TinyLab <hello@tinylab.dev>
 */
class Url_Redirect_Tracking_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
       global $wpdb;

        $table_name = $wpdb->prefix . 'tl_urls';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            url varchar(255) NOT NULL,
            redirect varchar(255) NOT NULL,
            clicks bigint(20) DEFAULT 0 NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        flush_rewrite_rules();
	}

}
