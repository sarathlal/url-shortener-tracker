<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://tinylab.dev
 * @since      1.0.0
 *
 * @package    Url_Redirect_Tracking
 * @subpackage Url_Redirect_Tracking/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Url_Redirect_Tracking
 * @subpackage Url_Redirect_Tracking/public
 * @author     TinyLab <hello@tinylab.dev>
 */
class Url_Redirect_Tracking_Public {

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
		 * defined in Url_Redirect_Tracking_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Url_Redirect_Tracking_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/url-redirect-tracking-public.css', array(), $this->version, 'all' );

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
		 * defined in Url_Redirect_Tracking_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Url_Redirect_Tracking_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/url-redirect-tracking-public.js', array( 'jquery' ), $this->version, false );

	}

    public function handle_redirects() {

    	Url_Redirect_Tracking_Utils::write_log('handle_redirects');

        global $wp_query, $wpdb;

        if (isset($wp_query->query_vars['enter'])) {
            $requested_url = $wp_query->query_vars['enter'];

            $table_name = $wpdb->prefix . 'tl_urls';

            $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE url = %s", $requested_url));

            if ($row) {
                $wpdb->update($table_name, array(
                    'clicks' => $row->clicks + 1,
                    'updated_at' => current_time('mysql')
                ), array('id' => $row->id));

                wp_redirect($row->redirect);
                exit();
            }
        }

    }	


    public function add_rewrite_rules() {
        add_rewrite_rule('^enter/([^/]*)/?', 'index.php?enter=$matches[1]', 'top');
   		add_rewrite_tag('%enter%', '([^&]+)');
    }

    public function add_query_vars($vars) {
        $vars[] = 'enter';
        return $vars;
    }

}
