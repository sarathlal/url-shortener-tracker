<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://tinylab.dev
 * @since      1.0.0
 *
 * @package    URL_Shortener_Tracker
 * @subpackage URL_Shortener_Tracker/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    URL_Shortener_Tracker
 * @subpackage URL_Shortener_Tracker/admin
 * @author     TinyLab <hello@tinylab.dev>
 */
class URL_Shortener_Tracker_Admin {

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
	// 	 * defined in URL_Shortener_Tracker_Loader as all of the hooks are defined
	// 	 * in that particular class.
	// 	 *
	// 	 * The URL_Shortener_Tracker_Loader will then create the relationship
	// 	 * between the defined hooks and the functions defined in this
	// 	 * class.
	// 	 */

	// 	wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/url-shortener-tracker-admin.css', array(), $this->version, 'all' );

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
	// 	 * defined in URL_Shortener_Tracker_Loader as all of the hooks are defined
	// 	 * in that particular class.
	// 	 *
	// 	 * The URL_Shortener_Tracker_Loader will then create the relationship
	// 	 * between the defined hooks and the functions defined in this
	// 	 * class.
	// 	 */

	// 	wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/url-shortener-tracker-admin.js', array( 'jquery' ), $this->version, false );

	// }


    public function add_plugin_admin_menu() {
        add_menu_page(
            'URLs',
            'URLs',
            'manage_options',
            'url-shortener-tracker',
            array($this, 'display_plugin_admin_page'),
            'dashicons-admin-links'
        );

        add_submenu_page(
            'url-shortener-tracker',
            'Settings',
            'Settings',
            'manage_options',
            'url-shortener-tracker-settings',
            array($this, 'display_plugin_settings_page')
        );

    }

    public function display_plugin_admin_page() {
        include_once('partials/url-shortener-tracker-admin-display.php');
    }

  	public function display_plugin_settings_page() {
        ?>
        <div class="wrap">
            <h1>URL Redirect Tracking Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('URL_Shortener_Tracker_settings_group');
                do_settings_sections('url-shortener-tracker-settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function register_settings() {
        //register_setting('URL_Shortener_Tracker_settings_group', 'URL_Shortener_Tracker_settings');

         register_setting('URL_Shortener_Tracker_settings_group', 'URL_Shortener_Tracker_settings', array($this, 'sanitize_settings'));


        add_settings_section(
            'URL_Shortener_Tracker_main_section',
            'Main Settings',
            null,
            'url-shortener-tracker-settings'
        );

        add_settings_field(
            'endpoint',
            'Custom Endpoint',
            array($this, 'endpoint_callback'),
            'url-shortener-tracker-settings',
            'URL_Shortener_Tracker_main_section'
        );
    }

    public function endpoint_callback() {
        $options = get_option('URL_Shortener_Tracker_settings');
        ?>
        <input type="text" name="URL_Shortener_Tracker_settings[endpoint]" value="<?php echo isset($options['endpoint']) ? esc_attr($options['endpoint']) : 'goto'; ?>">
        <p class="description">Set the custom endpoint for URL tracking (default is 'goto').</p>
        <?php
    }

    public function sanitize_settings($input) {
        add_settings_error('URL_Shortener_Tracker_settings', 'settings_updated', 'Settings saved.', 'updated');
        return $input;
    }


    public function settings_saved_notice() {
        settings_errors('URL_Shortener_Tracker_settings');
    }

    function handle_url_actions() {
        // Ensure we are on the correct admin page by checking the request URI
        $current_url = $_SERVER['REQUEST_URI'];
        if (strpos($current_url, 'page=url-shortener-tracker') === false) {
            return;
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'tl_urls';

        // Handle form submission for adding or editing URL
        if (isset($_POST['action']) && $_POST['action'] == 'add_url') {
            if (!isset($_POST['add_edit_url_nonce']) || !wp_verify_nonce($_POST['add_edit_url_nonce'], 'add_edit_url')) {
                wp_die('Nonce verification failed');
            }

            $url = sanitize_text_field($_POST['url']);
            $redirect = sanitize_text_field($_POST['redirect']);
            $error = false;

            // Validate redirect URL
            if (!filter_var($redirect, FILTER_VALIDATE_URL) || !(strpos($redirect, 'http://') === 0 || strpos($redirect, 'https://') === 0)) {
                $error = 'Invalid redirect URL. Please enter a valid URL starting with http:// or https://.';
            }

            // Check if URL is unique
            $existing_url = wp_cache_get("existing_url_{$url}", 'url_shortener_tracker');
            if ($existing_url === false) {
                $existing_url = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE url = %s", $url));
                wp_cache_set("existing_url_{$url}", $existing_url, 'url_shortener_tracker', 3600);
            }
            if ($existing_url > 0 && empty($_POST['id'])) {
                $error = 'This URL already exists. Please enter a unique URL.';
            }

            // Check if redirect URL is unique
            $existing_redirect = wp_cache_get("existing_redirect_{$redirect}", 'url_shortener_tracker');
            if ($existing_redirect === false) {
                $existing_redirect = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE redirect = %s", $redirect));
                wp_cache_set("existing_redirect_{$redirect}", $existing_redirect, 'url_shortener_tracker', 3600);
            }
            if ($existing_redirect > 0 && empty($_POST['id'])) {
                $error = 'This redirect URL already exists. Please enter a unique redirect URL.';
            }

            if (!$error) {
                if ($_POST['id']) {
                    // Edit existing URL
                    $wpdb->update(
                        $table_name,
                        array(
                            'url' => $url,
                            'redirect' => $redirect,
                            'updated_at' => current_time('mysql')
                        ),
                        array('id' => intval($_POST['id'])),
                        array('%s', '%s', '%s'),
                        array('%d')
                    );
                    wp_cache_delete("edit_url_" . intval($_POST['id']), 'url_shortener_tracker');
                    wp_cache_delete("existing_url_{$url}", 'url_shortener_tracker');
                    wp_cache_delete("existing_redirect_{$redirect}", 'url_shortener_tracker');
                    $notice = 'URL updated successfully.';
                } else {
                    // Add new URL
                    $wpdb->insert(
                        $table_name,
                        array(
                            'url' => $url,
                            'redirect' => $redirect,
                            'clicks' => 0,
                            'created_at' => current_time('mysql'),
                            'updated_at' => current_time('mysql')
                        ),
                        array('%s', '%s', '%d', '%s', '%s')
                    );
                    wp_cache_delete('total_urls', 'url_shortener_tracker');
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
            if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'delete_url_' . intval($_GET['id']))) {
                wp_die('Nonce verification failed');
            }

            $wpdb->delete(
                $table_name,
                array('id' => intval($_GET['id'])),
                array('%d')
            );
            wp_cache_delete("edit_url_" . intval($_GET['id']), 'url_shortener_tracker');
            wp_cache_delete('total_urls', 'url_shortener_tracker');
            $notice = 'URL deleted successfully.';
            wp_redirect(add_query_arg('notice', urlencode($notice), remove_query_arg(array('action', 'id'))));
            exit();
        }

        // Handle bulk delete and export actions
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!isset($_POST['bulk_action_nonce']) || !wp_verify_nonce($_POST['bulk_action_nonce'], 'bulk_action')) {
                wp_die('Nonce verification failed');
            }

            if (isset($_POST['bulk_action']) && $_POST['bulk_action'] == 'delete' && !empty($_POST['url_ids'])) {
                foreach ($_POST['url_ids'] as $url_id) {
                    $wpdb->delete(
                        $table_name,
                        array('id' => intval($url_id)),
                        array('%d')
                    );
                    wp_cache_delete("edit_url_" . intval($url_id), 'url_shortener_tracker');
                }
                wp_cache_delete('total_urls', 'url_shortener_tracker');
                $notice = 'Selected URLs deleted successfully.';
                wp_redirect(add_query_arg('notice', urlencode($notice)));
                exit();
            }

            if (isset($_POST['bulk_action']) && $_POST['bulk_action'] == 'export' && !empty($_POST['url_ids'])) {
                $url_ids = array_map('intval', $_POST['url_ids']);
                $placeholders = implode(',', array_fill(0, count($url_ids), '%d'));
                $urls_to_export = wp_cache_get("export_urls_" . implode('_', $url_ids), 'url_shortener_tracker');
                if ($urls_to_export === false) {
                    $urls_to_export = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE id IN ($placeholders)", $url_ids), ARRAY_A);
                    wp_cache_set("export_urls_" . implode('_', $url_ids), $urls_to_export, 'url_shortener_tracker', 3600);
                }

                if ($urls_to_export) {

                    $file_path = $this->generate_csv_file($urls_to_export);
                    if (is_wp_error($file_path)) {
                        echo 'Error generating CSV file.';
                        return;
                    }

                    $attach_id = $this->create_csv_attachment($file_path);
                    if (is_wp_error($attach_id)) {
                        echo 'Error creating attachment.';
                        return;
                    }

                    $attachment_url = wp_get_attachment_url($attach_id);
                    $notice = 'CSV file generated and attached. Download it <a href="' . esc_url($attachment_url) . '">here</a>.';

                    //$notice = 'Selected URLs deleted successfully.';
                    wp_redirect(add_query_arg('notice', urlencode($notice)));

                    exit();
                } else {
                    $error = 'No URLs found to export.';
                    wp_redirect(add_query_arg('error', urlencode($error)));
                    exit();
                }
            }
        }
    }


    private function generate_csv_file($data) {

        if ( ! function_exists( 'wp_handle_upload' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/file.php' );
        }

        WP_Filesystem();
        global $wp_filesystem;    

        if ( empty( $data ) ) {
            return new WP_Error( 'no_data', 'No data found to export.' );
        }

        // CSV content
        $csv_content = '';

        // Add column headers
        $headers = array_keys( $data[0] );
        $csv_content .= implode( ',', $headers ) . "\n";

        // Add rows
        foreach ( $data as $row ) {
            $csv_content .= implode( ',', $row ) . "\n";
        }

        // Get the WordPress uploads directory
        $upload_dir = wp_upload_dir();
        $file_path = trailingslashit( $upload_dir['path'] ) . 'url-export-'.current_datetime()->format('Y-m-d-H-i-s').'.csv';

        // Write the CSV content to a file
        if ( ! $wp_filesystem->put_contents( $file_path, $csv_content, FS_CHMOD_FILE ) ) {
            return new WP_Error( 'write_error', 'Could not write the file.' );
        }

        return $file_path;
    }


    private function create_csv_attachment($file_path) {
        // Check the type of file. We'll use this as the 'post_mime_type'.
        $filetype = wp_check_filetype(basename($file_path), null);

        // Get the path to the upload directory.
        $wp_upload_dir = wp_upload_dir();

        // Prepare an array of post data for the attachment.
        $attachment = array(
            'guid'           => $wp_upload_dir['url'] . '/' . basename($file_path),
            'post_mime_type' => $filetype['type'],
            'post_title'     => preg_replace('/\.[^.]+$/', '', basename($file_path)),
            'post_content'   => '',
            'post_status'    => 'inherit'
        );

        // Insert the attachment.
        $attach_id = wp_insert_attachment($attachment, $file_path);

        // Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        // Generate the metadata for the attachment, and update the database record.
        $attach_data = wp_generate_attachment_metadata($attach_id, $file_path);
        wp_update_attachment_metadata($attach_id, $attach_data);

        return $attach_id;
    }



}
