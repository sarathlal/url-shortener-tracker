<?php

/**
 * Fired during plugin activation
 *
 * @link       https://tinylab.dev
 * @since      1.0.0
 *
 * @package    URL_Shortener_Tracker
 * @subpackage URL_Shortener_Tracker/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    URL_Shortener_Tracker
 * @subpackage URL_Shortener_Tracker/includes
 * @author     TinyLab <hello@tinylab.dev>
 */
class URL_Shortener_Tracker_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
       global $wpdb;

        // Table 1: tl_urls
        $table_name_urls = $wpdb->prefix . 'tl_urls';
        $charset_collate = $wpdb->get_charset_collate();

        $sql_urls = "CREATE TABLE $table_name_urls (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            url varchar(255) NOT NULL,
            redirect varchar(255) NOT NULL,
            clicks bigint(20) DEFAULT 0 NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";


        // Table 2: tl_url_data
        $table_name_url_data = $wpdb->prefix . 'tl_url_data';

        $sql_url_data = "CREATE TABLE $table_name_url_data (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            url_id mediumint(9) NOT NULL,
            ip_address varchar(100) NOT NULL,
            referrer text,
            user_agent text,
            query_string text,
            timestamp datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            language varchar(50),
            method varchar(10),
            page_url text,
            user_id bigint(20),
            PRIMARY KEY  (id),
            FOREIGN KEY (url_id) REFERENCES $table_name_urls(id) ON DELETE CASCADE
        ) $charset_collate;";


        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_urls);
        dbDelta($sql_url_data);

        flush_rewrite_rules();
	}

}
