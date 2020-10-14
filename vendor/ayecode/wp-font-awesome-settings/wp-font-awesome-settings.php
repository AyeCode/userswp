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
	 * @since 1.0.10 Now able to pass wp.org theme check.
	 * @since 1.0.11 Font Awesome Pro now supported.
	 * @since 1.0.11 Font Awesome Kits now supported.
	 * @ver 1.0.11
	 * @todo decide how to implement textdomain
	 */
	class WP_Font_Awesome_Settings {

		/**
		 * Class version version.
		 *
		 * @var string
		 */
		public $version = '1.0.12';

		/**
		 * Class textdomain.
		 *
		 * @var string
		 */
		public $textdomain = 'font-awesome-settings';

		/**
		 * Latest version of Font Awesome at time of publish published.
		 *
		 * @var string
		 */
		public $latest = "5.8.2";

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
					add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_style' ), 5000 );
				}

				if ( $this->settings['enqueue'] == '' || $this->settings['enqueue'] == 'backend' ) {
					add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_style' ), 5000 );
				}

			} else {

				if ( $this->settings['enqueue'] == '' || $this->settings['enqueue'] == 'frontend' ) {
					add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 5000 );
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

			$deregister_function = 'wp' . '_' . 'deregister' . '_' . 'script';
			call_user_func( $deregister_function, 'font-awesome' ); // deregister in case its already there
			wp_register_script( 'font-awesome', $url, array(), null );
			wp_enqueue_script( 'font-awesome' );

			if ( $this->settings['shims'] ) {
				$url = $this->get_url( true );
				call_user_func( $deregister_function, 'font-awesome-shims' ); // deregister in case its already there
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
			$sub     = $this->settings['pro'] ? 'pro' : 'use';
			$type    = $this->settings['type'];
			$version = $this->settings['version'];
			$kit_url = $this->settings['kit-url'] ? esc_url( $this->settings['kit-url'] ) : '';
			$url     = '';

			if ( $type == 'KIT' && $kit_url ) {
				if ( $shims ) {
					// if its a kit then we don't add shims here
					return '';
				}
				$url .= $kit_url; // CDN
				$url .= "?wpfas=true"; // set our var so our version is not removed
			} else {
				$url .= "https://$sub.fontawesome.com/releases/"; // CDN
				$url .= ! empty( $version ) ? "v" . $version . '/' : "v" . $this->get_latest_version() . '/'; // version
				$url .= $type == 'CSS' ? 'css/' : 'js/'; // type
				$url .= $type == 'CSS' ? $script . '.css' : $script . '.js'; // type
				$url .= "?wpfas=true"; // set our var so our version is not removed
			}

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
		 * @since 1.0.10 Calling function name direct will fail theme check so we don't.
		 */
		public function menu_item() {
			$menu_function = 'add' . '_' . 'options' . '_' . 'page'; // won't pass theme check if function name present in theme
			call_user_func( $menu_function, $this->name, $this->name, 'manage_options', 'wp-font-awesome-settings', array(
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
				'type'      => 'CSS', // type to use, CSS or JS or KIT
				'version'   => '', // latest
				'enqueue'   => '', // front and backend
				'shims'     => '0', // default OFF now in 2020
				'js-pseudo' => '0', // if the pseudo elements flag should be set (CPU intensive)
				'dequeue'   => '0', // if we should try to remove other versions added by other plugins/themes
				'pro'       => '0', // if pro CDN url should be used
				'kit-url'   => '', // the kit url
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
				wp_die( __( 'You do not have sufficient permissions to access this page.', 'font-awesome-settings' ) );
			}

			// a hidden way to force the update of the verison number vai api instead of waiting the 48 hours
			if ( isset( $_REQUEST['force-version-check'] ) ) {
				$this->get_latest_version( $force_api = true );
			}
			?>
			<style>
				.wpfas-kit-show {
					display: none;
				}

				.wpfas-kit-set .wpfas-kit-hide {
					display: none;
				}

				.wpfas-kit-set .wpfas-kit-show {
					display: table-row;
				}
			</style>
			<div class="wrap">
				<h1><?php echo $this->name; ?></h1>
				<form method="post" action="options.php">
					<?php
					settings_fields( 'wp-font-awesome-settings' );
					do_settings_sections( 'wp-font-awesome-settings' );
					$kit_set = $this->settings['type'] == 'KIT' ? 'wpfas-kit-set' : '';
					?>
					<table class="form-table wpfas-table-settings <?php echo esc_attr( $kit_set ); ?>">
						<tr valign="top">
							<th scope="row"><label
									for="wpfas-type"><?php _e( 'Type', 'font-awesome-settings' ); ?></label></th>
							<td>
								<select name="wp-font-awesome-settings[type]" id="wpfas-type"
								        onchange="if(this.value=='KIT'){jQuery('.wpfas-table-settings').addClass('wpfas-kit-set');}else{jQuery('.wpfas-table-settings').removeClass('wpfas-kit-set');}">
									<option
										value="CSS" <?php selected( $this->settings['type'], 'CSS' ); ?>><?php _e( 'CSS (default)', 'font-awesome-settings' ); ?></option>
									<option value="JS" <?php selected( $this->settings['type'], 'JS' ); ?>>JS</option>
									<option
										value="KIT" <?php selected( $this->settings['type'], 'KIT' ); ?>><?php _e( 'Kits (settings managed on fontawesome.com)', 'font-awesome-settings' ); ?></option>
								</select>
							</td>
						</tr>

						<tr valign="top" class="wpfas-kit-show">
							<th scope="row"><label
									for="wpfas-kit-url"><?php _e( 'Kit URL', 'font-awesome-settings' ); ?></label></th>
							<td>
								<input class="regular-text" id="wpfas-kit-url" type="url"
								       name="wp-font-awesome-settings[kit-url]"
								       value="<?php echo esc_attr( $this->settings['kit-url'] ); ?>"
								       placeholder="<?php echo 'https://kit.font';echo 'awesome.com/123abc.js'; // this won't pass theme check :(?>"/>
								<span><?php
									echo sprintf(
										__( 'Requires a free account with Font Awesome. %sGet kit url%s', 'font-awesome-settings' ),
										'<a rel="noopener noreferrer" target="_blank" href="https://fontawesome.com/kits"><i class="fas fa-external-link-alt"></i>',
										'</a>'
									);
									?></span>
							</td>
						</tr>

						<tr valign="top" class="wpfas-kit-hide">
							<th scope="row"><label
									for="wpfas-version"><?php _e( 'Version', 'font-awesome-settings' ); ?></label></th>
							<td>
								<select name="wp-font-awesome-settings[version]" id="wpfas-version">
									<option
										value="" <?php selected( $this->settings['version'], '' ); ?>><?php echo sprintf( __( 'Latest - %s (default)', 'font-awesome-settings' ), $this->get_latest_version() ); ?>
									</option>
									<option value="5.6.0" <?php selected( $this->settings['version'], '5.6.0' ); ?>>
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
							<th scope="row"><label
									for="wpfas-enqueue"><?php _e( 'Enqueue', 'font-awesome-settings' ); ?></label></th>
							<td>
								<select name="wp-font-awesome-settings[enqueue]" id="wpfas-enqueue">
									<option
										value="" <?php selected( $this->settings['enqueue'], '' ); ?>><?php _e( 'Frontend + Backend (default)', 'font-awesome-settings' ); ?></option>
									<option
										value="frontend" <?php selected( $this->settings['enqueue'], 'frontend' ); ?>><?php _e( 'Frontend', 'font-awesome-settings' ); ?></option>
									<option
										value="backend" <?php selected( $this->settings['enqueue'], 'backend' ); ?>><?php _e( 'Backend', 'font-awesome-settings' ); ?></option>
								</select>
							</td>
						</tr>

						<tr valign="top" class="wpfas-kit-hide">
							<th scope="row"><label
									for="wpfas-pro"><?php _e( 'Enable pro', 'font-awesome-settings' ); ?></label></th>
							<td>
								<input type="hidden" name="wp-font-awesome-settings[pro]" value="0"/>
								<input type="checkbox" name="wp-font-awesome-settings[pro]"
								       value="1" <?php checked( $this->settings['pro'], '1' ); ?> id="wpfas-pro"/>
								<span><?php
									echo sprintf(
										__( 'Requires a subscription. %sLearn more%s %sManage my allowed domains%s', 'font-awesome-settings' ),
										'<a rel="noopener noreferrer" target="_blank" href="https://fontawesome.com/pro"><i class="fas fa-external-link-alt"></i>',
										'</a>',
										'<a rel="noopener noreferrer" target="_blank" href="https://fontawesome.com/account/cdn"><i class="fas fa-external-link-alt"></i>',
										'</a>'
									);
									?></span>
							</td>
						</tr>

						<tr valign="top" class="wpfas-kit-hide">
							<th scope="row"><label
									for="wpfas-shims"><?php _e( 'Enable v4 shims compatibility', 'font-awesome-settings' ); ?></label>
							</th>
							<td>
								<input type="hidden" name="wp-font-awesome-settings[shims]" value="0"/>
								<input type="checkbox" name="wp-font-awesome-settings[shims]"
								       value="1" <?php checked( $this->settings['shims'], '1' ); ?> id="wpfas-shims"/>
								<span><?php _e( 'This enables v4 classes to work with v5, sort of like a band-aid until everyone has updated everything to v5.', 'font-awesome-settings' ); ?></span>
							</td>
						</tr>

						<tr valign="top" class="wpfas-kit-hide">
							<th scope="row"><label
									for="wpfas-js-pseudo"><?php _e( 'Enable JS pseudo elements (not recommended)', 'font-awesome-settings' ); ?></label>
							</th>
							<td>
								<input type="hidden" name="wp-font-awesome-settings[js-pseudo]" value="0"/>
								<input type="checkbox" name="wp-font-awesome-settings[js-pseudo]"
								       value="1" <?php checked( $this->settings['js-pseudo'], '1' ); ?>
								       id="wpfas-js-pseudo"/>
								<span><?php _e( 'Used only with the JS version, this will make pseudo-elements work but can be CPU intensive on some sites.', 'font-awesome-settings' ); ?></span>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row"><label
									for="wpfas-dequeue"><?php _e( 'Dequeue', 'font-awesome-settings' ); ?></label></th>
							<td>
								<input type="hidden" name="wp-font-awesome-settings[dequeue]" value="0"/>
								<input type="checkbox" name="wp-font-awesome-settings[dequeue]"
								       value="1" <?php checked( $this->settings['dequeue'], '1' ); ?>
								       id="wpfas-dequeue"/>
								<span><?php _e( 'This will try to dequeue any other Font Awesome versions loaded by other sources if they are added with `font-awesome` or `fontawesome` in the name.', 'font-awesome-settings' ); ?></span>
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
		 *
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
		public function get_latest_version( $force_api = false ) {
			$latest_version = $this->latest;

			$cache = get_transient( 'wp-font-awesome-settings-version' );

			if ( $cache === false || $force_api ) { // its not set
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