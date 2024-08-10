<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://tinylab.dev
 * @since      1.0.0
 *
 * @package    URL_Shortener_Tracker
 * @subpackage URL_Shortener_Tracker/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    URL_Shortener_Tracker
 * @subpackage URL_Shortener_Tracker/public
 * @author     TinyLab <hello@tinylab.dev>
 */
class URL_Shortener_Tracker_Public {

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in URL_Shortener_Tracker_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The URL_Shortener_Tracker_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/url-shortener-tracker-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in URL_Shortener_Tracker_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The URL_Shortener_Tracker_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/url-shortener-tracker-public.js', array( 'jquery' ), $this->version, false );

	}

    public function handle_redirects() {

        global $wp_query, $wpdb;

        URL_Shortener_Tracker_Utils::write_log('handle_redirects');

        if (isset($wp_query->query_vars['tl_redirect_url'])) {

            $requested_url = $wp_query->query_vars['tl_redirect_url'];

            $table_name = $wpdb->prefix . 'tl_urls';

            $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE url = %s", $requested_url));

            if ($row) {
                $wpdb->update($table_name, array(
                    'clicks' => $row->clicks + 1,
                    'updated_at' => current_time('mysql')
                ), array('id' => $row->id));

                $this->save_url_data($row->id);

                wp_redirect($row->redirect);
                exit();
            }
        }

    }


	function save_url_data($url_id) {

	    global $wpdb;

	    // Get details
	    $ip_address = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field($_SERVER['REMOTE_ADDR']) : 'unknown';
	    $referrer = isset($_SERVER['HTTP_REFERER']) ? sanitize_text_field($_SERVER['HTTP_REFERER']) : 'direct';
	    $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field($_SERVER['HTTP_USER_AGENT']) : 'unknown';
	    $query_string = isset($_SERVER['QUERY_STRING']) ? sanitize_text_field($_SERVER['QUERY_STRING']) : '';
	    $timestamp = current_time('mysql');
	    $language = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? sanitize_text_field($_SERVER['HTTP_ACCEPT_LANGUAGE']) : 'unknown';
	    $language = substr($language, 0, 50); // Truncate to 50 characters
	    $method = sanitize_text_field($_SERVER['REQUEST_METHOD']);
	    $page_url = sanitize_text_field($_SERVER['REQUEST_URI']);
	    $user_id = get_current_user_id();

	    // Prepare data for insertion
	    $data = array(
	        'url_id' => $url_id,
	        'ip_address' => $ip_address,
	        'referrer' => $referrer,
	        'user_agent' => $user_agent,
	        'query_string' => $query_string,
	        'timestamp' => $timestamp,
	        'language' => $language,
	        'method' => $method,
	        'page_url' => $page_url,
	        'user_id' => $user_id ? $user_id : null, // Store null if the user is not logged in
	    );

	    // Insert data into the tl_url_data table
	    $result = $wpdb->insert(
	        $wpdb->prefix . 'tl_url_data',
	        $data
	    );

		// Check the result and log errors if any
		if ($result === false) {
			error_log('Insert failed: ' . $wpdb->last_error);
		}

	}


    public function add_rewrite_rules() {
        $options = get_option('url_shortener_tracker_settings');
        $endpoint = isset($options['endpoint']) ? $options['endpoint'] : 'go';
        add_rewrite_rule("^$endpoint/([^/]*)/?", 'index.php?tl_redirect_url=$matches[1]', 'top');
        add_rewrite_tag('%tl_redirect_url%', '([^&]+)');
    }

    public function add_query_vars($vars) {
        $vars[] = 'tl_redirect_url';
        return $vars;
    }

}
