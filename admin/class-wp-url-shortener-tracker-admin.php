<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://tinylab.dev
 * @since      1.0.0
 *
 * @package    WP_URL_Shortener_Tracker
 * @subpackage WP_URL_Shortener_Tracker/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    WP_URL_Shortener_Tracker
 * @subpackage WP_URL_Shortener_Tracker/admin
 * @author     TinyLab <hello@tinylab.dev>
 */
class WP_URL_Shortener_Tracker_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	// public function enqueue_styles() {

	// 	/**
	// 	 * This function is provided for demonstration purposes only.
	// 	 *
	// 	 * An instance of this class should be passed to the run() function
	// 	 * defined in WP_URL_Shortener_Tracker_Loader as all of the hooks are defined
	// 	 * in that particular class.
	// 	 *
	// 	 * The WP_URL_Shortener_Tracker_Loader will then create the relationship
	// 	 * between the defined hooks and the functions defined in this
	// 	 * class.
	// 	 */

	// 	wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wp-url-shortener-tracker-admin.css', array(), $this->version, 'all' );

	// }

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	// public function enqueue_scripts() {

	// 	/**
	// 	 * This function is provided for demonstration purposes only.
	// 	 *
	// 	 * An instance of this class should be passed to the run() function
	// 	 * defined in WP_URL_Shortener_Tracker_Loader as all of the hooks are defined
	// 	 * in that particular class.
	// 	 *
	// 	 * The WP_URL_Shortener_Tracker_Loader will then create the relationship
	// 	 * between the defined hooks and the functions defined in this
	// 	 * class.
	// 	 */

	// 	wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wp-url-shortener-tracker-admin.js', array( 'jquery' ), $this->version, false );

	// }


    public function add_plugin_admin_menu() {
        add_menu_page(
            'URLs',
            'URLs',
            'manage_options',
            'wp-url-shortener-tracker',
            array($this, 'display_plugin_admin_page'),
            'dashicons-admin-links'
        );

        add_submenu_page(
            'wp-url-shortener-tracker',
            'Settings',
            'Settings',
            'manage_options',
            'wp-url-shortener-tracker-settings',
            array($this, 'display_plugin_settings_page')
        );

    }

    public function display_plugin_admin_page() {
        include_once('partials/wp-url-shortener-tracker-admin-display.php');
    }

  public function display_plugin_settings_page() {
        ?>
        <div class="wrap">
            <h1>URL Redirect Tracking Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('WP_URL_Shortener_Tracker_settings_group');
                do_settings_sections('wp-url-shortener-tracker-settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function register_settings() {
        //register_setting('WP_URL_Shortener_Tracker_settings_group', 'WP_URL_Shortener_Tracker_settings');

         register_setting('WP_URL_Shortener_Tracker_settings_group', 'WP_URL_Shortener_Tracker_settings', array($this, 'sanitize_settings'));


        add_settings_section(
            'WP_URL_Shortener_Tracker_main_section',
            'Main Settings',
            null,
            'wp-url-shortener-tracker-settings'
        );

        add_settings_field(
            'endpoint',
            'Custom Endpoint',
            array($this, 'endpoint_callback'),
            'wp-url-shortener-tracker-settings',
            'WP_URL_Shortener_Tracker_main_section'
        );
    }

    public function endpoint_callback() {
        $options = get_option('WP_URL_Shortener_Tracker_settings');
        ?>
        <input type="text" name="WP_URL_Shortener_Tracker_settings[endpoint]" value="<?php echo isset($options['endpoint']) ? esc_attr($options['endpoint']) : 'goto'; ?>">
        <p class="description">Set the custom endpoint for URL tracking (default is 'goto').</p>
        <?php
    }

    public function sanitize_settings($input) {
        add_settings_error('WP_URL_Shortener_Tracker_settings', 'settings_updated', 'Settings saved.', 'updated');
        return $input;
    }


    public function settings_saved_notice() {
        settings_errors('WP_URL_Shortener_Tracker_settings');
    }

	public function handle_url_actions() {

		$url = isset($_SERVER['REQUEST_URI']) ? sanitize_url($_SERVER['REQUEST_URI']) : false;
		if($url && is_admin() && strpos($url, "wp-url-shortener-tracker")  !== false){

		    global $wpdb;
		    $table_name = $wpdb->prefix . 'tl_urls';

		    // Handle form submission for adding or editing URL
		    if (isset($_POST['action']) && $_POST['action'] == 'add_url') {
		        $url = sanitize_text_field($_POST['url']);
		        $redirect = sanitize_text_field($_POST['redirect']);
		        $error = false;

		        // Validate redirect URL
		        if (!filter_var($redirect, FILTER_VALIDATE_URL) || !(strpos($redirect, 'http://') === 0 || strpos($redirect, 'https://') === 0)) {
		            $error = 'Invalid redirect URL. Please enter a valid URL starting with http:// or https://.';
		        }

		        // Check if URL is unique
		        $existing_url = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE url = %s", $url));
		        if ($existing_url > 0 && empty($_POST['id'])) {
		            $error = 'This URL already exists. Please enter a unique URL.';
		        }

		        // Check if redirect URL is unique
		        $existing_redirect = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE redirect = %s", $redirect));
		        if ($existing_redirect > 0 && empty($_POST['id'])) {
		            $error = 'This redirect URL already exists. Please enter a unique redirect URL.';
		        }

		        if (!$error) {
		            if ($_POST['id']) {
		                // Edit existing URL
		                $wpdb->update($table_name, array(
		                    'url' => $url,
		                    'redirect' => $redirect,
		                    'updated_at' => current_time('mysql')
		                ), array('id' => intval($_POST['id'])));
		                $notice = 'URL updated successfully.';
		            } else {
		                // Add new URL
		                $wpdb->insert($table_name, array(
		                    'url' => $url,
		                    'redirect' => $redirect,
		                    'clicks' => 0,
		                    'created_at' => current_time('mysql'),
		                    'updated_at' => current_time('mysql')
		                ));
		                $notice = 'URL added successfully.';
		            }
		            wp_redirect(add_query_arg('notice', urlencode($notice), remove_query_arg(array('action', 'id'))));
		            exit();
		        } else {
		            wp_redirect(add_query_arg('error', urlencode($error), remove_query_arg(array('action', 'id'))));
		            exit();
		        }
		    }

		    // Handle URL deletion
		    if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
		        $wpdb->delete($table_name, array('id' => intval($_GET['id'])));
		        $notice = 'URL deleted successfully.';
		        wp_redirect(add_query_arg('notice', urlencode($notice), remove_query_arg(array('action', 'id'))));
		        exit();
		    }

		    // Handle bulk delete and export actions
		    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		        if (isset($_POST['bulk_action']) && $_POST['bulk_action'] == 'delete' && !empty($_POST['url_ids'])) {
		            foreach ($_POST['url_ids'] as $url_id) {
		                $wpdb->delete($table_name, array('id' => intval($url_id)));
		            }
		            $notice = 'Selected URLs deleted successfully.';
		            wp_redirect(add_query_arg('notice', urlencode($notice)));
		            exit();
		        }

		        if (isset($_POST['bulk_action']) && $_POST['bulk_action'] == 'export' && !empty($_POST['url_ids'])) {
		            $url_ids = array_map('intval', $_POST['url_ids']);
		            $urls_to_export = $wpdb->get_results("SELECT * FROM $table_name WHERE id IN (" . implode(',', $url_ids) . ")", ARRAY_A);

		            if ($urls_to_export) {
		                // Set headers to force download of the CSV file
		                header('Content-Type: text/csv');
		                header('Content-Disposition: attachment; filename="exported_urls.csv"');
		                header('Pragma: no-cache');
		                header('Expires: 0');

		                $output = fopen('php://output', 'w');
		                fputcsv($output, array('ID', 'URL', 'Redirect', 'Clicks', 'Created At', 'Updated At'));

		                foreach ($urls_to_export as $url) {
		                    fputcsv($output, $url);
		                }

		                fclose($output);
		                exit();
		            } else {
		                $error = 'No URLs found to export.';
		                wp_redirect(add_query_arg('error', urlencode($error)));
		                exit();
		            }
		        }
		    }
		}
	}

}
