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
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in WP_URL_Shortener_Tracker_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The WP_URL_Shortener_Tracker_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wp-url-shortener-tracker-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in WP_URL_Shortener_Tracker_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The WP_URL_Shortener_Tracker_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wp-url-shortener-tracker-admin.js', array( 'jquery' ), $this->version, false );

	}


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

}
