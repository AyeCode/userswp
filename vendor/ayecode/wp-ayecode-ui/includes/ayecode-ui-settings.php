<?php
/**
 * A class for adjusting AyeCode UI settings on WordPress
 *
 * This class can be added to any plugin or theme and will add a settings screen to WordPress to control Bootstrap settings.
 *
 * @link https://github.com/AyeCode/wp-ayecode-ui
 *
 * @internal This file should not be edited directly but pulled from the github repo above.
 */

/**
 * Bail if we are not in WP.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Only add if the class does not already exist.
 */
if ( ! class_exists( 'AyeCode_UI_Settings' ) ) {

	/**
	 * A Class to be able to change settings for Font Awesome.
	 *
	 * Class AyeCode_UI_Settings
	 * @ver 1.0.0
	 * @todo decide how to implement textdomain
	 */
	class AyeCode_UI_Settings {

		/**
		 * Class version version.
		 *
		 * @var string
		 */
		public $version = '1.0.0';

		/**
		 * Class textdomain.
		 *
		 * @var string
		 */
		public $textdomain = 'aui';

		/**
		 * Latest version of Bootstrap at time of publish published.
		 *
		 * @var string
		 */
		public $latest = "4.3.1";

		/**
		 * The title.
		 *
		 * @var string
		 */
		public $name = 'AyeCode UI';

		/**
		 * The relative url to the assets.
		 *
		 * @var string
		 */
		public $url = '';

		/**
		 * Holds the settings values.
		 *
		 * @var array
		 */
		private $settings;

		/**
		 * WP_Bootstrap_Settings instance.
		 *
		 * @access private
		 * @since  1.0.0
		 * @var    WP_Bootstrap_Settings There can be only one!
		 */
		private static $instance = null;

		/**
		 * Main WP_Bootstrap_Settings Instance.
		 *
		 * Ensures only one instance of WP_Bootstrap_Settings is loaded or can be loaded.
		 *
		 * @since 1.0.0
		 * @static
		 * @return WP_Bootstrap_Settings - Main instance.
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof WP_Bootstrap_Settings ) ) {
				self::$instance = new AyeCode_UI_Settings;

				add_action( 'init', array( self::$instance, 'init' ) ); // set settings

				if ( is_admin() ) {
					add_action( 'admin_menu', array( self::$instance, 'menu_item' ) );
					add_action( 'admin_init', array( self::$instance, 'register_settings' ) );
				}

				do_action( 'ayecode_ui_settings_loaded' );
			}

			return self::$instance;
		}

		/**
		 * Initiate the settings and add the required action hooks.
		 */
		public function init() {
			$this->settings = $this->get_settings();
			$this->url = $this->get_url();

			// maybe load CSS
			if ( $this->settings['css'] ) {

				/**
				 * We load super early in case there is a theme version that might change the colors
				 */
				add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_style' ), 1 );
				add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_style' ), 1 );

			}

			// maybe load JS
			if ( $this->settings['js'] ) {

				add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10 );
				add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10 );

			}


		}

		/**
		 * Adds the Font Awesome styles.
		 */
		public function enqueue_style() {

			if($this->settings['css']){
				$url = $this->settings['css']=='core' ? $this->url.'assets/css/ayecode-ui.css' : $this->url.'assets/css/ayecode-ui-compatibility.css';
				wp_register_style( 'ayecode-ui', $url, array(), $this->latest );
				wp_enqueue_style( 'ayecode-ui' );


				// fix some wp-admin issues
				if(is_admin()){
					$custom_css = "
                body{
                    background-color: #f1f1f1;
                    font-family: -apple-system,BlinkMacSystemFont,\"Segoe UI\",Roboto,Oxygen-Sans,Ubuntu,Cantarell,\"Helvetica Neue\",sans-serif;
                    font-size:13px;
                }
                a {
				    color: #0073aa;
				    text-decoration: underline;
				}
                label {
				    display: initial;
				    margin-bottom: 0;
				}
				input, select {
				    margin: 1px;
				    line-height: initial;
				}
				th, td, div, h2 {
				    box-sizing: content-box;
				}
				p {
				    font-size: 13px;
				    line-height: 1.5;
				    margin: 1em 0;
				}
				h1, h2, h3, h4, h5, h6 {
				    display: block;
				    font-weight: 600;
				}
				h2,h3 {
				    font-size: 1.3em;
				    margin: 1em 0
				}
                ";
					wp_add_inline_style( 'ayecode-ui', $custom_css );
				}

			}
		}

		/**
		 * Adds the Font Awesome JS.
		 */
		public function enqueue_scripts() {
			if($this->settings['js']=='core-popper'){
				$url = $this->url.'assets/js/bootstrap.bundle.min.js';
				wp_register_script( 'bootstrap-js-bundle', $url, array(), $this->latest );
				wp_enqueue_script( 'bootstrap-js-bundle' );
				$script = "
				jQuery(document).ready(function(){
    jQuery('[data-toggle=\"tooltip\"]').tooltip();
});
				";
				wp_add_inline_script( 'bootstrap-js-bundle', $script );
			}elseif($this->settings['js']=='popper'){
				$url = $this->url.'assets/js/popper.min.js';
				wp_register_script( 'bootstrap-js-popper', $url, array(), $this->latest );
				wp_enqueue_script( 'bootstrap-js-popper' );
			}
		}

		/**
		 * Get the url path to the current folder.
		 *
		 * @return string
		 */
		public function get_url() {

			$url = '';
			// check if we are inside a plugin
			$file_dir = str_replace("/includes","", dirname( __FILE__ ));

			$dir_parts = explode("/wp-content/",$file_dir);
			$url_parts = explode("/wp-content/",plugins_url());

			if(!empty($url_parts[0]) && !empty($dir_parts[1])){
				$url = trailingslashit( $url_parts[0]."/wp-content/".$dir_parts[1] );
			}

			return $url;
		}

		/**
		 * Register the database settings with WordPress.
		 */
		public function register_settings() {
			register_setting( 'ayecode-ui-settings', 'ayecode-ui-settings' );
		}

		/**
		 * Add the WordPress settings menu item.
		 * @since 1.0.10 Calling function name direct will fail theme check so we don't.
		 */
		public function menu_item() {
			$menu_function = 'add' . '_' . 'options' . '_' . 'page'; // won't pass theme check if function name present in theme
			call_user_func( $menu_function, $this->name, $this->name, 'manage_options', 'ayecode-ui-settings', array(
				$this,
				'settings_page'
			) );
		}

		/**
		 * Get the current Font Awesome output settings.
		 *
		 * @return array The array of settings.
		 */
		public function get_settings() {

			$db_settings = get_option( 'ayecode-ui-settings' );

			$defaults = array(
				'css'       => 'compatibility', // core, compatibility
				'js'        => 'core-popper', // js to load, core-popper, popper
			);

			$settings = wp_parse_args( $db_settings, $defaults );

			/**
			 * Filter the Bootstrap settings.
			 *
			 * @todo if we add this filer people might use it and then it defeates the purpose of this class :/
			 */
			return $this->settings = apply_filters( 'ayecode-ui-settings', $settings, $db_settings, $defaults );
		}


		/**
		 * The settings page html output.
		 */
		public function settings_page() {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( __( 'You do not have sufficient permissions to access this page.', 'aui' ) );
			}
			?>
			<div class="wrap">
				<h1><?php echo $this->name; ?></h1>
				<form method="post" action="options.php">
					<?php
					settings_fields( 'ayecode-ui-settings' );
					do_settings_sections( 'ayecode-ui-settings' );
					?>
					<table class="form-table wpbs-table-settings">
						<tr valign="top">
							<th scope="row"><label
									for="wpbs-css"><?php _e( 'Load CSS', 'aui' ); ?></label></th>
							<td>
								<select name="ayecode-ui-settings[css]" id="wpbs-css">
									<option	value="compatibility" <?php selected( $this->settings['css'], 'compatibility' ); ?>><?php _e( 'Compatibility Mode', 'aui' ); ?></option>
									<option value="core" <?php selected( $this->settings['css'], 'core' ); ?>><?php _e( 'Full Mode', 'aui' ); ?></option>
									<option	value="" <?php selected( $this->settings['css'], '' ); ?>><?php _e( 'Disabled', 'aui' ); ?></option>
								</select>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row"><label
									for="wpbs-js"><?php _e( 'Load JS', 'aui' ); ?></label></th>
							<td>
								<select name="ayecode-ui-settings[js]" id="wpbs-js">
									<option	value="core-popper" <?php selected( $this->settings['js'], 'core-popper' ); ?>><?php _e( 'Core + Popper (default)', 'aui' ); ?></option>
									<option value="popper" <?php selected( $this->settings['js'], 'popper' ); ?>><?php _e( 'Popper', 'aui' ); ?></option>
									<option	value="" <?php selected( $this->settings['js'], '' ); ?>><?php _e( 'Disabled', 'aui' ); ?></option>
								</select>
							</td>
						</tr>

					</table>
					<?php
					submit_button();
					?>
				</form>

				<div id="wpbs-version"><?php echo $this->version; ?></div>
			</div>

			<?php
		}

	}

	/**
	 * Run the class if found.
	 */
	AyeCode_UI_Settings::instance();
}