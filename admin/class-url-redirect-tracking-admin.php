<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://tinylab.dev
 * @since      1.0.0
 *
 * @package    Url_Redirect_Tracking
 * @subpackage Url_Redirect_Tracking/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Url_Redirect_Tracking
 * @subpackage Url_Redirect_Tracking/admin
 * @author     TinyLab <hello@tinylab.dev>
 */
class Url_Redirect_Tracking_Admin {

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
		 * defined in Url_Redirect_Tracking_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Url_Redirect_Tracking_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/url-redirect-tracking-admin.css', array(), $this->version, 'all' );

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
		 * defined in Url_Redirect_Tracking_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Url_Redirect_Tracking_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/url-redirect-tracking-admin.js', array( 'jquery' ), $this->version, false );

	}


    public function add_plugin_admin_menu() {
        add_menu_page(
            'URLs',
            'URLs',
            'manage_options',
            'url-redirect-tracking',
            array($this, 'display_plugin_admin_page'),
            'dashicons-admin-links'
        );

        add_submenu_page(
            'url-redirect-tracking',
            'Settings',
            'Settings',
            'manage_options',
            'url-redirect-tracking-settings',
            array($this, 'display_plugin_settings_page')
        );

    }

    public function display_plugin_admin_page() {
        include_once('partials/url-redirect-tracking-admin-display.php');
    }

  public function display_plugin_settings_page() {
        ?>
        <div class="wrap">
            <h1>URL Redirect Tracking Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('url_redirect_tracking_settings_group');
                do_settings_sections('url-redirect-tracking-settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function register_settings() {
        //register_setting('url_redirect_tracking_settings_group', 'url_redirect_tracking_settings');

         register_setting('url_redirect_tracking_settings_group', 'url_redirect_tracking_settings', array($this, 'sanitize_settings'));


        add_settings_section(
            'url_redirect_tracking_main_section',
            'Main Settings',
            null,
            'url-redirect-tracking-settings'
        );

        add_settings_field(
            'endpoint',
            'Custom Endpoint',
            array($this, 'endpoint_callback'),
            'url-redirect-tracking-settings',
            'url_redirect_tracking_main_section'
        );
    }

    public function endpoint_callback() {
        $options = get_option('url_redirect_tracking_settings');
        ?>
        <input type="text" name="url_redirect_tracking_settings[endpoint]" value="<?php echo isset($options['endpoint']) ? esc_attr($options['endpoint']) : 'goto'; ?>">
        <p class="description">Set the custom endpoint for URL tracking (default is 'goto').</p>
        <?php
    }

    public function sanitize_settings($input) {
        add_settings_error('url_redirect_tracking_settings', 'settings_updated', 'Settings saved.', 'updated');
        return $input;
    }


    public function settings_saved_notice() {
        settings_errors('url_redirect_tracking_settings');
    }     

}
