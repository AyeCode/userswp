<?php
/**
 * A class for adjusting font awesome settings on WordPress
 *
 * This class can be added to any plugin or theme and will add a settings screen to WordPress to control Font Awesome settings.
 *
 * @link https://github.com/AyeCode/wp-font-awesome-settings
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
if ( ! class_exists( 'WP_Font_Awesome_Settings' ) ) {

	/**
	 * A Class to be able to change settings for Font Awesome.
	 *
	 * Class WP_Font_Awesome_Settings
	 * @ver 1.0.8
	 * @todo decide how to implement textdomain
	 */
	class WP_Font_Awesome_Settings {

		/**
		 * Class version version.
		 *
		 * @var string
		 */
		public $version = '1.0.8';

		/**
		 * Latest version of Font Awesome at time of publish published.
		 *
		 * @var string
		 */
		public $latest = "5.6.0";

		/**
		 * The title.
		 *
		 * @var string
		 */
		public $name = 'Font Awesome';

		/**
		 * Holds the settings values.
		 *
		 * @var array
		 */
		private $settings;

		/**
		 * WP_Font_Awesome_Settings instance.
		 *
		 * @access private
		 * @since  1.0.0
		 * @var    WP_Font_Awesome_Settings There can be only one!
		 */
		private static $instance = null;

		/**
		 * Main WP_Font_Awesome_Settings Instance.
		 *
		 * Ensures only one instance of WP_Font_Awesome_Settings is loaded or can be loaded.
		 *
		 * @since 1.0.0
		 * @static
		 * @return WP_Font_Awesome_Settings - Main instance.
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof WP_Font_Awesome_Settings ) ) {
				self::$instance = new WP_Font_Awesome_Settings;

				add_action( 'init', array( self::$instance, 'init' ) ); // set settings

				if ( is_admin() ) {
					add_action( 'admin_menu', array( self::$instance, 'menu_item' ) );
					add_action( 'admin_init', array( self::$instance, 'register_settings' ) );
				}

				do_action( 'wp_font_awesome_settings_loaded' );
			}

			return self::$instance;
		}

		/**
		 * Initiate the settings and add the required action hooks.
		 *
		 * @since 1.0.8 Settings name wrong - FIXED
		 */
		public function init() {
			$this->settings = $this->get_settings();

			if ( $this->settings['type'] == 'CSS' ) {

				if ( $this->settings['enqueue'] == '' || $this->settings['enqueue'] == 'frontend' ) {
					add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_style' ), 5000 );//echo '###';exit;
				}

				if ( $this->settings['enqueue'] == '' || $this->settings['enqueue'] == 'backend' ) {
					add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_style' ), 5000 );
				}

			} else {

				if ( $this->settings['enqueue'] == '' || $this->settings['enqueue'] == 'frontend' ) {
					add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 5000 );//echo '###';exit;
				}

				if ( $this->settings['enqueue'] == '' || $this->settings['enqueue'] == 'backend' ) {
					add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), 5000 );
				}
			}

			// remove font awesome if set to do so
			if ( $this->settings['dequeue'] == '1' ) {
				add_action( 'clean_url', array( $this, 'remove_font_awesome' ), 5000, 3 );
			}

		}

		/**
		 * Adds the Font Awesome styles.
		 */
		public function enqueue_style() {
			// build url
			$url = $this->get_url();

			wp_deregister_style( 'font-awesome' ); // deregister in case its already there
			wp_register_style( 'font-awesome', $url, array(), null );
			wp_enqueue_style( 'font-awesome' );

			if ( $this->settings['shims'] ) {
				$url = $this->get_url( true );
				wp_deregister_style( 'font-awesome-shims' ); // deregister in case its already there
				wp_register_style( 'font-awesome-shims', $url, array(), null );
				wp_enqueue_style( 'font-awesome-shims' );
			}
		}

		/**
		 * Adds the Font Awesome JS.
		 */
		public function enqueue_scripts() {
			// build url
			$url = $this->get_url();

			wp_deregister_script( 'font-awesome' ); // deregister in case its already there
			wp_register_script( 'font-awesome', $url, array(), null );
			wp_enqueue_script( 'font-awesome' );

			if ( $this->settings['shims'] ) {
				$url = $this->get_url( true );
				wp_deregister_script( 'font-awesome-shims' ); // deregister in case its already there
				wp_register_script( 'font-awesome-shims', $url, array(), null );
				wp_enqueue_script( 'font-awesome-shims' );
			}
		}

		/**
		 * Get the url of the Font Awesome files.
		 *
		 * @param bool $shims If this is a shim file or not.
		 *
		 * @return string The url to the file.
		 */
		public function get_url( $shims = false ) {
			$script  = $shims ? 'v4-shims' : 'all';
			$type    = $this->settings['type'];
			$version = $this->settings['version'];

			$url = "https://use.fontawesome.com/releases/"; // CDN
			$url .= ! empty( $version ) ? "v" . $version . '/' : "v" . $this->get_latest_version() . '/'; // version
			$url .= $type == 'CSS' ? 'css/' : 'js/'; // type
			$url .= $type == 'CSS' ? $script . '.css' : $script . '.js'; // type
			$url .= "?wpfas=true"; // set our var so our version is not removed

			return $url;
		}

		/**
		 * Try and remove any other versions of Font Awesome added by other plugins/themes.
		 *
		 * Uses the clean_url filter to try and remove any other Font Awesome files added, it can also add pseudo-elements flag for the JS version.
		 *
		 * @param $url
		 * @param $original_url
		 * @param $_context
		 *
		 * @return string The filtered url.
		 */
		public function remove_font_awesome( $url, $original_url, $_context ) {

			if ( $_context == 'display'
			     && ( strstr( $url, "fontawesome" ) !== false || strstr( $url, "font-awesome" ) !== false )
			     && ( strstr( $url, ".js" ) !== false || strstr( $url, ".css" ) !== false )
			) {// it's a font-awesome-url (probably)

				if ( strstr( $url, "wpfas=true" ) !== false ) {
					if ( $this->settings['type'] == 'JS' ) {
						if ( $this->settings['js-pseudo'] ) {
							$url .= "' data-search-pseudo-elements defer='defer";
						} else {
							$url .= "' defer='defer";
						}
					}
				} else {
					$url = ''; // removing the url removes the file
				}

			}

			return $url;
		}

		/**
		 * Register the database settings with WordPress.
		 */
		public function register_settings() {
			register_setting( 'wp-font-awesome-settings', 'wp-font-awesome-settings' );
		}

		/**
		 * Add the WordPress settings menu item.
		 */
		public function menu_item() {
			add_options_page( $this->name, $this->name, 'manage_options', 'wp-font-awesome-settings', array(
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

			$db_settings = get_option( 'wp-font-awesome-settings' );

			$defaults = array(
				'type'      => 'CSS', // type to use, CSS or JS
				'version'   => '', // latest
				'enqueue'   => '', // front and backend
				'shims'     => '1', // default on for now, @todo maybe change to off in 2020
				'js-pseudo' => '0', // if the pseudo elements flag should be set (CPU intensive)
				'dequeue'   => '0', // if we should try to remove other versions added by other plugins/themes
			);

			$settings = wp_parse_args( $db_settings, $defaults );

			/**
			 * Filter the Font Awesome settings.
			 *
			 * @todo if we add this filer people might use it and then it defeates the purpose of this class :/
			 */
			return $this->settings = apply_filters( 'wp-font-awesome-settings', $settings, $db_settings, $defaults );
		}


		/**
		 * The settings page html output.
		 */
		public function settings_page() {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
			}

			// a hidden way to force the update of the verison number vai api instead of waiting the 48 hours
			if(isset($_REQUEST['force-version-check'])){
				$this->get_latest_version($force_api = true);
			}
			?>
			<div class="wrap">
				<h1><?php echo $this->name; ?></h1>
				<form method="post" action="options.php">
					<?php
					settings_fields( 'wp-font-awesome-settings' );
					do_settings_sections( 'wp-font-awesome-settings' );
					?>
					<table class="form-table">
						<tr valign="top">
							<th scope="row"><label for="wpfas-type"><?php _e( 'Type' ); ?></label></th>
							<td>
								<select name="wp-font-awesome-settings[type]" id="wpfas-type">
									<option
										value="CSS" <?php selected( $this->settings['type'], 'CSS' ); ?>><?php _e( 'CSS (default)' ); ?></option>
									<option value="JS" <?php selected( $this->settings['type'], 'JS' ); ?>>JS</option>
								</select>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row"><label for="wpfas-version"><?php _e( 'Version' ); ?></label></th>
							<td>
								<select name="wp-font-awesome-settings[version]" id="wpfas-version">
									<option
										value="" <?php selected( $this->settings['version'], '' ); ?>><?php echo sprintf( __( 'Latest - %s (default)' ), $this->get_latest_version() ); ?></option>
									<option value="5.5.0" <?php selected( $this->settings['version'], '5.6.0' ); ?>>
										5.6.0
									</option>
									<option value="5.5.0" <?php selected( $this->settings['version'], '5.5.0' ); ?>>
										5.5.0
									</option>
									<option value="5.4.0" <?php selected( $this->settings['version'], '5.4.0' ); ?>>
										5.4.0
									</option>
									<option value="5.3.0" <?php selected( $this->settings['version'], '5.3.0' ); ?>>
										5.3.0
									</option>
									<option value="5.2.0" <?php selected( $this->settings['version'], '5.2.0' ); ?>>
										5.2.0
									</option>
									<option value="5.1.0" <?php selected( $this->settings['version'], '5.1.0' ); ?>>
										5.1.0
									</option>
									<option value="4.7.0" <?php selected( $this->settings['version'], '4.7.0' ); ?>>
										4.7.1 (CSS only)
									</option>
								</select>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row"><label for="wpfas-enqueue"><?php _e( 'Enqueue' ); ?></label></th>
							<td>
								<select name="wp-font-awesome-settings[enqueue]" id="wpfas-enqueue">
									<option
										value="" <?php selected( $this->settings['enqueue'], '' ); ?>><?php _e( 'Frontend + Backend (default)' ); ?></option>
									<option
										value="frontend" <?php selected( $this->settings['enqueue'], 'frontend' ); ?>><?php _e( 'Frontend' ); ?></option>
									<option
										value="backend" <?php selected( $this->settings['enqueue'], 'backend' ); ?>><?php _e( 'Backend' ); ?></option>
								</select>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row"><label
									for="wpfas-shims"><?php _e( 'Enable v4 shims compatibility' ); ?></label></th>
							<td>
								<input type="hidden" name="wp-font-awesome-settings[shims]" value="0"/>
								<input type="checkbox" name="wp-font-awesome-settings[shims]"
								       value="1" <?php checked( $this->settings['shims'], '1' ); ?> id="wpfas-shims"/>
								<span><?php _e( 'This enables v4 classes to work with v5, sort of like a band-aid until everyone has updated everything to v5.' ); ?></span>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row"><label
									for="wpfas-js-pseudo"><?php _e( 'Enable JS pseudo elements (not recommended)' ); ?></label>
							</th>
							<td>
								<input type="hidden" name="wp-font-awesome-settings[js-pseudo]" value="0"/>
								<input type="checkbox" name="wp-font-awesome-settings[js-pseudo]"
								       value="1" <?php checked( $this->settings['js-pseudo'], '1' ); ?>
								       id="wpfas-js-pseudo"/>
								<span><?php _e( 'Used only with the JS version, this will make pseudo-elements work but can be CPU intensive on some sites.' ); ?></span>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row"><label for="wpfas-dequeue"><?php _e( 'Dequeue' ); ?></label></th>
							<td>
								<input type="hidden" name="wp-font-awesome-settings[dequeue]" value="0"/>
								<input type="checkbox" name="wp-font-awesome-settings[dequeue]"
								       value="1" <?php checked( $this->settings['dequeue'], '1' ); ?>
								       id="wpfas-dequeue"/>
								<span><?php _e( 'This will try to dequeue any other Font Awesome versions loaded by other sources if they are added with `font-awesome` or `fontawesome` in the name.' ); ?></span>
							</td>
						</tr>


					</table>
					<?php
					submit_button();
					?>
				</form>

				<div id="wpfas-version"><?php echo $this->version; ?></div>
			</div>

			<?php
		}

		/**
		 * Check a version number is valid and if so return it or else return an empty string.
		 *
		 * @param $version string The version number to check.
		 * @since 1.0.6
		 *
		 * @return string Either a valid version number or an empty string.
		 */
		public function validate_version_number( $version ) {

			if ( version_compare( $version, '0.0.1', '>=' ) >= 0 ) {
				// valid
			} else {
				$version = '';// not validated
			}

			return $version;
		}


		/**
		 * Get the latest version of Font Awesome.
		 *
		 * We check for a cached bersion and if none we will check for a live version via API and then cache it for 48 hours.
		 *
		 * @since 1.0.7
		 * @return mixed|string The latest version number found.
		 */
		public function get_latest_version($force_api = false) {
			$latest_version = $this->latest;

			$cache = get_transient( 'wp-font-awesome-settings-version' );

			if ( $cache === false || $force_api) { // its not set
				$api_ver = $this->get_latest_version_from_api();
				if ( version_compare( $api_ver, $this->latest, '>=' ) >= 0 ) {
					$latest_version = $api_ver;
					set_transient( 'wp-font-awesome-settings-version', $api_ver, 48 * HOUR_IN_SECONDS );
				}
			} elseif ( $this->validate_version_number( $cache ) ) {
				if ( version_compare( $cache, $this->latest, '>=' ) >= 0 ) {
					$latest_version = $cache;
				}
			}

			return $latest_version;
		}

		/**
		 * Get the latest Font Awesome version from the github API.
		 *
		 * @since 1.0.7
		 * @return string The latest version number or `0` on API fail.
		 */
		public function get_latest_version_from_api() {
			$version  = "0";
			$response = wp_remote_get( "https://api.github.com/repos/FortAwesome/Font-Awesome/releases/latest" );
			if ( ! is_wp_error( $response ) && is_array( $response ) ) {
				$api_response = json_decode( wp_remote_retrieve_body( $response ), true );
				if ( isset( $api_response['tag_name'] ) && version_compare( $api_response['tag_name'], $this->latest, '>=' ) >= 0 && empty( $api_response['prerelease'] ) ) {
					$version = $api_response['tag_name'];
				}
			}

			return $version;
		}

	}

	/**
	 * Run the class if found.
	 */
	WP_Font_Awesome_Settings::instance();
}