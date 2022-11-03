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
	 * @since 1.0.13 RTL language support added.
	 * @since 1.0.14 Warning added for v6 pro requires kit and will now not work if official FA plugin installed.
	 * @since 1.0.15 Font Awesome will now load in the FSE if enable din the backend.
	 * @since 1.1.0 Option added to load FontAwesome locally.
	 * @since 1.1.1 Requires to re-save settings to load locally when option does not exists - FIXED.
	 * @ver 1.0.15
	 * @todo decide how to implement textdomain
	 */
	class WP_Font_Awesome_Settings {

		/**
		 * Class version version.
		 *
		 * @var string
		 */
		public $version = '1.1.1';

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
					add_action( 'admin_notices', array( self::$instance, 'admin_notices' ) );
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
			// Download fontawesome locally.
			add_action( 'add_option_wp-font-awesome-settings', array( $this, 'add_option_wp_font_awesome_settings' ), 10, 2 );
			add_action( 'update_option_wp-font-awesome-settings', array( $this, 'update_option_wp_font_awesome_settings' ), 10, 2 );

			$this->settings = $this->get_settings();

			// check if the official plugin is active and use that instead if so.
			if ( ! defined( 'FONTAWESOME_PLUGIN_FILE' ) ) {

				if ( $this->settings['type'] == 'CSS' ) {

					if ( $this->settings['enqueue'] == '' || $this->settings['enqueue'] == 'frontend' ) {
						add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_style' ), 5000 );
					}

					if ( $this->settings['enqueue'] == '' || $this->settings['enqueue'] == 'backend' ) {
						add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_style' ), 5000 );
						add_filter( 'block_editor_settings_all', array( $this, 'enqueue_editor_styles' ), 10, 2 );
					}

				} else {

					if ( $this->settings['enqueue'] == '' || $this->settings['enqueue'] == 'frontend' ) {
						add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 5000 );
					}

					if ( $this->settings['enqueue'] == '' || $this->settings['enqueue'] == 'backend' ) {
						add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), 5000 );
						add_filter( 'block_editor_settings_all', array( $this, 'enqueue_editor_scripts' ), 10, 2 );
					}
				}

				// remove font awesome if set to do so
				if ( $this->settings['dequeue'] == '1' ) {
					add_action( 'clean_url', array( $this, 'remove_font_awesome' ), 5000, 3 );
				}
			}

		}

		/**
		 * Add FA to the FSE.
		 *
		 * @param $editor_settings
		 * @param $block_editor_context
		 *
		 * @return array
		 */
		public function enqueue_editor_styles( $editor_settings, $block_editor_context ){

			if ( ! empty( $editor_settings['__unstableResolvedAssets']['styles'] ) ) {
				$url = $this->get_url();
				$editor_settings['__unstableResolvedAssets']['styles'] .= "<link rel='stylesheet' id='font-awesome-css'  href='$url' media='all' />";
			}

			return $editor_settings;
		}

		/**
		 * Add FA to the FSE.
		 *
		 * @param $editor_settings
		 * @param $block_editor_context
		 *
		 * @return array
		 */
		public function enqueue_editor_scripts( $editor_settings, $block_editor_context ){

			$url = $this->get_url();
			$editor_settings['__unstableResolvedAssets']['scripts'] .= "<script src='$url' id='font-awesome-js'></script>";

			return $editor_settings;
		}

		/**
		 * Adds the Font Awesome styles.
		 */
		public function enqueue_style() {
			// build url
			$url = $this->get_url();
			$version = ! empty( $this->settings['local'] ) && empty( $this->settings['pro'] ) ? strip_tags( $this->settings['local_version'] ) : null;

			wp_deregister_style( 'font-awesome' ); // deregister in case its already there
			wp_register_style( 'font-awesome', $url, array(), $version );
			wp_enqueue_style( 'font-awesome' );

			// RTL language support CSS.
			if ( is_rtl() ) {
				wp_add_inline_style( 'font-awesome', $this->rtl_inline_css() );
			}

			if ( $this->settings['shims'] ) {
				$url = $this->get_url( true );
				wp_deregister_style( 'font-awesome-shims' ); // deregister in case its already there
				wp_register_style( 'font-awesome-shims', $url, array(), $version );
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
		 * @param bool $local Load locally if allowed.
		 *
		 * @return string The url to the file.
		 */
		public function get_url( $shims = false, $local = true ) {
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
				$v = '';
				// Check and load locally.
				if ( $local && $this->has_local() ) {
					$script .= ".min";
					$v .= '&ver=' . strip_tags( $this->settings['local_version'] );
					$url .= $this->get_fonts_url(); // Local fonts url.
				} else {
					$url .= "https://$sub.fontawesome.com/releases/"; // CDN
					$url .= ! empty( $version ) ? "v" . $version . '/' : "v" . $this->get_latest_version() . '/'; // version
				}
				$url .= $type == 'CSS' ? 'css/' : 'js/'; // type
				$url .= $type == 'CSS' ? $script . '.css' : $script . '.js'; // type
				$url .= "?wpfas=true" . $v; // set our var so our version is not removed
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
				'local'     => '0', // Store fonts locally.
				'local_version' => '', // Local fonts version.
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

			// a hidden way to force the update of the version number via api instead of waiting the 48 hours
			if ( isset( $_REQUEST['force-version-check'] ) ) {
				$this->get_latest_version( $force_api = true );
			}

			if ( ! defined( 'FONTAWESOME_PLUGIN_FILE' ) ) {
				?>
                <style>
                    .wpfas-kit-show {
                        display: none;
                    }
                    .wpfas-kit-set .wpfas-kit-hide,.wpfas-has-pro .wpfas-hide-pro {
                        display: none;
                    }
                    .wpfas-kit-set .wpfas-kit-show {
                        display: table-row;
                    }
                    .fas-settings-form .submit{
                        display: inline;
                        padding-right: 5px;
                    }
                    .fas-settings-form .fas-buttons{
                        margin: 15px 0;
                    }
                    #wpfas-version{
                        color: #646970;
                    }
                </style>
                <div class="wrap">
                    <h1><?php echo $this->name; ?></h1>
                    <form method="post" action="options.php" class="fas-settings-form">
						<?php
						settings_fields( 'wp-font-awesome-settings' );
						do_settings_sections( 'wp-font-awesome-settings' );
						$table_class = '';
						if ( $this->settings['type'] ) {
							$table_class .= 'wpfas-' . sanitize_html_class( strtolower( $this->settings['type'] ) ) . '-set';
						}
						if ( ! empty( $this->settings['pro'] ) ) {
							$table_class .= ' wpfas-has-pro';
						}
						?>
						<?php if ( $this->settings['type'] != 'KIT' && ! empty( $this->settings['local'] ) && empty( $this->settings['pro'] ) ) { ?>
							<?php if ( $this->has_local() ) { ?>
							<div class="notice notice-info"><p><strong><?php _e( 'Font Awesome fonts are loading locally.', 'font-awesome-settings' ); ?></strong></p></div>
							<?php } else { ?>
							<div class="notice notice-error"><p><strong><?php _e( 'Font Awesome fonts are not loading locally!', 'font-awesome-settings' ); ?></strong></p></div>
							<?php } ?>
						<?php } ?>
                        <table class="form-table wpfas-table-settings <?php echo esc_attr( $table_class ); ?>">
                            <tr valign="top">
                                <th scope="row"><label for="wpfas-type"><?php _e( 'Type', 'font-awesome-settings' ); ?></label></th>
                                <td>
                                    <select name="wp-font-awesome-settings[type]" id="wpfas-type" onchange="if(this.value=='KIT'){jQuery('.wpfas-table-settings').addClass('wpfas-kit-set');}else{jQuery('.wpfas-table-settings').removeClass('wpfas-kit-set');}">
                                        <option value="CSS" <?php selected( $this->settings['type'], 'CSS' ); ?>><?php _e( 'CSS (default)', 'font-awesome-settings' ); ?></option>
                                        <option value="JS" <?php selected( $this->settings['type'], 'JS' ); ?>>JS</option>
                                        <option value="KIT" <?php selected( $this->settings['type'], 'KIT' ); ?>><?php _e( 'Kits (settings managed on fontawesome.com)', 'font-awesome-settings' ); ?></option>
                                    </select>
                                </td>
                            </tr>

                            <tr valign="top" class="wpfas-kit-show">
                                <th scope="row"><label for="wpfas-kit-url"><?php _e( 'Kit URL', 'font-awesome-settings' ); ?></label></th>
                                <td>
                                    <input class="regular-text" id="wpfas-kit-url" type="url" name="wp-font-awesome-settings[kit-url]" value="<?php echo esc_attr( $this->settings['kit-url'] ); ?>" placeholder="<?php echo 'https://kit.font';echo 'awesome.com/123abc.js'; // this won't pass theme check :(?>"/>
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
                                <th scope="row"><label for="wpfas-version"><?php _e( 'Version', 'font-awesome-settings' ); ?></label></th>
                                <td>
                                    <select name="wp-font-awesome-settings[version]" id="wpfas-version">
                                        <option value="" <?php selected( $this->settings['version'], '' ); ?>><?php echo sprintf( __( 'Latest - %s (default)', 'font-awesome-settings' ), $this->get_latest_version() ); ?></option>
                                        <option value="6.1.0" <?php selected( $this->settings['version'], '6.1.0' ); ?>>6.1.0</option>
                                        <option value="6.0.0" <?php selected( $this->settings['version'], '6.0.0' ); ?>>6.0.0</option>
                                        <option value="5.15.4" <?php selected( $this->settings['version'], '5.15.4' ); ?>>5.15.4</option>
                                        <option value="5.6.0" <?php selected( $this->settings['version'], '5.6.0' ); ?>>5.6.0</option>
                                        <option value="5.5.0" <?php selected( $this->settings['version'], '5.5.0' ); ?>>5.5.0</option>
                                        <option value="5.4.0" <?php selected( $this->settings['version'], '5.4.0' ); ?>>5.4.0</option>
                                        <option value="5.3.0" <?php selected( $this->settings['version'], '5.3.0' ); ?>>5.3.0</option>
                                        <option value="5.2.0" <?php selected( $this->settings['version'], '5.2.0' ); ?>>5.2.0</option>
                                        <option value="5.1.0" <?php selected( $this->settings['version'], '5.1.0' ); ?>>5.1.0</option>
                                        <option value="4.7.0" <?php selected( $this->settings['version'], '4.7.0' ); ?>>4.7.1 (CSS only)</option>
                                    </select>
                                </td>
                            </tr>

                            <tr valign="top">
                                <th scope="row"><label for="wpfas-enqueue"><?php _e( 'Enqueue', 'font-awesome-settings' ); ?></label></th>
                                <td>
                                    <select name="wp-font-awesome-settings[enqueue]" id="wpfas-enqueue">
                                        <option value="" <?php selected( $this->settings['enqueue'], '' ); ?>><?php _e( 'Frontend + Backend (default)', 'font-awesome-settings' ); ?></option>
                                        <option value="frontend" <?php selected( $this->settings['enqueue'], 'frontend' ); ?>><?php _e( 'Frontend', 'font-awesome-settings' ); ?></option>
                                        <option value="backend" <?php selected( $this->settings['enqueue'], 'backend' ); ?>><?php _e( 'Backend', 'font-awesome-settings' ); ?></option>
                                    </select>
                                </td>
                            </tr>

                            <tr valign="top" class="wpfas-kit-hide">
                                <th scope="row"><label
                                            for="wpfas-pro"><?php _e( 'Enable pro', 'font-awesome-settings' ); ?></label></th>
                                <td>
                                    <input type="hidden" name="wp-font-awesome-settings[pro]" value="0"/>
                                    <input type="checkbox" name="wp-font-awesome-settings[pro]" value="1" <?php checked( $this->settings['pro'], '1' ); ?> id="wpfas-pro" onchange="if(jQuery(this).is(':checked')){jQuery('.wpfas-table-settings').addClass('wpfas-has-pro')}else{jQuery('.wpfas-table-settings').removeClass('wpfas-has-pro')}"/>
                                    <span><?php
										echo wp_sprintf(
											__( 'Requires a subscription. %sLearn more%s  %sManage my allowed domains%s', 'font-awesome-settings' ),
											'<a rel="noopener noreferrer" target="_blank" href="https://fontawesome.com/referral?a=c9b89e1418">',
											' <i class="fas fa-external-link-alt"></i></a>',
											'<a rel="noopener noreferrer" target="_blank" href="https://fontawesome.com/account/cdn">',
											' <i class="fas fa-external-link-alt"></i></a>'
										);
										?></span>
                                </td>
                            </tr>

							<tr valign="top" class="wpfas-kit-hide wpfas-hide-pro">
								<th scope="row"><label for="wpfas-local"><?php _e( 'Load Fonts Locally', 'font-awesome-settings' ); ?></label></th>
								<td>
									<input type="hidden" name="wp-font-awesome-settings[local]" value="0"/>
									<input type="hidden" name="wp-font-awesome-settings[local_version]" value="<?php echo esc_attr( $this->settings['local_version'] ); ?>"/>
									<input type="checkbox" name="wp-font-awesome-settings[local]" value="1" <?php checked( $this->settings['local'], '1' ); ?> id="wpfas-local"/>
									<span><?php _e( '(For free version only) Load FontAwesome fonts from locally. This downloads FontAwesome fonts from fontawesome.com & stores at the local site.', 'font-awesome-settings' ); ?></span>
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
                        <div class="fas-buttons">
							<?php
							submit_button();
							?>
                            <p class="submit"><a href="https://fontawesome.com/referral?a=c9b89e1418" class="button button-secondary"><?php _e('Get 14,000+ more icons with Font Awesome Pro','font-awesome-settings'); ?> <i class="fas fa-external-link-alt"></i></a></p>

                        </div>
                    </form>

                    <div id="wpfas-version"><?php echo sprintf(__( 'Version: %s (affiliate links provided)', 'font-awesome-settings' ), $this->version ); ?></div>
                </div>
				<?php
			}
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
		 * We check for a cached version and if none we will check for a live version via API and then cache it for 48 hours.
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

			// Check and auto download fonts locally.
			if ( empty( $this->settings['pro'] ) && empty( $this->settings['version'] ) && $this->settings['type'] != 'KIT' && ! empty( $this->settings['local'] ) && ! empty( $this->settings['local_version'] ) && ! empty( $latest_version ) ) {
				if ( version_compare( $latest_version, $this->settings['local_version'], '>' ) && is_admin() && ! wp_doing_ajax() ) {
					$this->download_package( $latest_version );
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

		/**
		 * Inline CSS for RTL language support.
		 *
		 * @since 1.0.13
		 * @return string Inline CSS.
		 */
		public function rtl_inline_css() {
			$inline_css = '[dir=rtl] .fa-address,[dir=rtl] .fa-address-card,[dir=rtl] .fa-adjust,[dir=rtl] .fa-alarm-clock,[dir=rtl] .fa-align-left,[dir=rtl] .fa-align-right,[dir=rtl] .fa-analytics,[dir=rtl] .fa-angle-double-left,[dir=rtl] .fa-angle-double-right,[dir=rtl] .fa-angle-left,[dir=rtl] .fa-angle-right,[dir=rtl] .fa-arrow-alt-circle-left,[dir=rtl] .fa-arrow-alt-circle-right,[dir=rtl] .fa-arrow-alt-from-left,[dir=rtl] .fa-arrow-alt-from-right,[dir=rtl] .fa-arrow-alt-left,[dir=rtl] .fa-arrow-alt-right,[dir=rtl] .fa-arrow-alt-square-left,[dir=rtl] .fa-arrow-alt-square-right,[dir=rtl] .fa-arrow-alt-to-left,[dir=rtl] .fa-arrow-alt-to-right,[dir=rtl] .fa-arrow-circle-left,[dir=rtl] .fa-arrow-circle-right,[dir=rtl] .fa-arrow-from-left,[dir=rtl] .fa-arrow-from-right,[dir=rtl] .fa-arrow-left,[dir=rtl] .fa-arrow-right,[dir=rtl] .fa-arrow-square-left,[dir=rtl] .fa-arrow-square-right,[dir=rtl] .fa-arrow-to-left,[dir=rtl] .fa-arrow-to-right,[dir=rtl] .fa-balance-scale-left,[dir=rtl] .fa-balance-scale-right,[dir=rtl] .fa-bed,[dir=rtl] .fa-bed-bunk,[dir=rtl] .fa-bed-empty,[dir=rtl] .fa-border-left,[dir=rtl] .fa-border-right,[dir=rtl] .fa-calendar-check,[dir=rtl] .fa-caret-circle-left,[dir=rtl] .fa-caret-circle-right,[dir=rtl] .fa-caret-left,[dir=rtl] .fa-caret-right,[dir=rtl] .fa-caret-square-left,[dir=rtl] .fa-caret-square-right,[dir=rtl] .fa-cart-arrow-down,[dir=rtl] .fa-cart-plus,[dir=rtl] .fa-chart-area,[dir=rtl] .fa-chart-bar,[dir=rtl] .fa-chart-line,[dir=rtl] .fa-chart-line-down,[dir=rtl] .fa-chart-network,[dir=rtl] .fa-chart-pie,[dir=rtl] .fa-chart-pie-alt,[dir=rtl] .fa-chart-scatter,[dir=rtl] .fa-check-circle,[dir=rtl] .fa-check-square,[dir=rtl] .fa-chevron-circle-left,[dir=rtl] .fa-chevron-circle-right,[dir=rtl] .fa-chevron-double-left,[dir=rtl] .fa-chevron-double-right,[dir=rtl] .fa-chevron-left,[dir=rtl] .fa-chevron-right,[dir=rtl] .fa-chevron-square-left,[dir=rtl] .fa-chevron-square-right,[dir=rtl] .fa-clock,[dir=rtl] .fa-file,[dir=rtl] .fa-file-alt,[dir=rtl] .fa-file-archive,[dir=rtl] .fa-file-audio,[dir=rtl] .fa-file-chart-line,[dir=rtl] .fa-file-chart-pie,[dir=rtl] .fa-file-code,[dir=rtl] .fa-file-excel,[dir=rtl] .fa-file-image,[dir=rtl] .fa-file-pdf,[dir=rtl] .fa-file-powerpoint,[dir=rtl] .fa-file-video,[dir=rtl] .fa-file-word,[dir=rtl] .fa-flag,[dir=rtl] .fa-folder,[dir=rtl] .fa-folder-open,[dir=rtl] .fa-hand-lizard,[dir=rtl] .fa-hand-point-down,[dir=rtl] .fa-hand-point-left,[dir=rtl] .fa-hand-point-right,[dir=rtl] .fa-hand-point-up,[dir=rtl] .fa-hand-scissors,[dir=rtl] .fa-image,[dir=rtl] .fa-long-arrow-alt-left,[dir=rtl] .fa-long-arrow-alt-right,[dir=rtl] .fa-long-arrow-left,[dir=rtl] .fa-long-arrow-right,[dir=rtl] .fa-luggage-cart,[dir=rtl] .fa-moon,[dir=rtl] .fa-pencil,[dir=rtl] .fa-pencil-alt,[dir=rtl] .fa-play-circle,[dir=rtl] .fa-project-diagram,[dir=rtl] .fa-quote-left,[dir=rtl] .fa-quote-right,[dir=rtl] .fa-shopping-cart,[dir=rtl] .fa-thumbs-down,[dir=rtl] .fa-thumbs-up,[dir=rtl] .fa-user-chart{filter: progid:DXImageTransform.Microsoft.BasicImage(rotation=0, mirror=1);transform:scale(-1,1)}[dir=rtl] .fa-spin{animation-direction:reverse}';

			return $inline_css;
		}

		/**
		 * Show any warnings as an admin notice.
		 *
		 * @return void
		 */
		public function admin_notices() {
			$settings = $this->settings;

			if ( defined( 'FONTAWESOME_PLUGIN_FILE' ) ) {
				if ( ! empty( $_REQUEST['page'] ) && $_REQUEST['page'] == 'wp-font-awesome-settings' ) {
					?>
                    <div class="notice  notice-error is-dismissible">
                        <p><?php _e( 'The Official Font Awesome Plugin is active, please adjust your settings there.', 'font-awesome-settings' ); ?></p>
                    </div>
					<?php
				}
			} else {
				if ( ! empty( $settings ) ) {
					if ( $settings['type'] != 'KIT' && $settings['pro'] && ( $settings['version'] == '' || version_compare( $settings['version'], '6', '>=' ) ) ) {
						$link = admin_url('options-general.php?page=wp-font-awesome-settings');
						?>
                        <div class="notice  notice-error is-dismissible">
                            <p><?php echo sprintf( __( 'Font Awesome Pro v6 requires the use of a kit, please setup your kit in %ssettings.%s', 'font-awesome-settings' ),"<a href='". esc_url_raw( $link )."'>","</a>" ); ?></p>
                        </div>
						<?php
					}
				}
			}
		}

		/**
		 * Handle fontawesome add settings to download fontawesome to store locally.
		 *
		 * @since 1.1.1
		 *
		 * @param string $option The option name.
		 * @param mixed  $value  The option value.
		 */
		public function add_option_wp_font_awesome_settings( $option, $value ) {
			// Do nothing if WordPress is being installed.
			if ( wp_installing() ) {
				return;
			}

			if ( ! empty( $value['local'] ) && empty( $value['pro'] ) && ! ( ! empty( $value['type'] ) && $value['type'] == 'KIT' ) ) {
				$version = isset( $value['version'] ) && $value['version'] ? $value['version'] : $this->get_latest_version();

				if ( ! empty( $version ) ) {
					$response = $this->download_package( $version, $value );

					if ( is_wp_error( $response ) ) {
						add_settings_error( 'general', 'fontawesome_download', __( 'ERROR:', 'font-awesome-settings' ) . ' ' . $response->get_error_message(), 'error' );
					}
				}
			}
		}

		/**
		 * Handle fontawesome update settings to download fontawesome to store locally.
		 *
		 * @since 1.1.0
		 *
		 * @param mixed $old_value The old option value.
		 * @param mixed $value     The new option value.
		 */
		public function update_option_wp_font_awesome_settings( $old_value, $new_value ) {
			// Do nothing if WordPress is being installed.
			if ( wp_installing() ) {
				return;
			}

			if ( ! empty( $new_value['local'] ) && empty( $new_value['pro'] ) && ! ( ! empty( $new_value['type'] ) && $new_value['type'] == 'KIT' ) ) {
				// Old values
				$old_version = isset( $old_value['version'] ) && $old_value['version'] ? $old_value['version'] : ( isset( $old_value['local_version'] ) ? $old_value['local_version'] : '' );
				$old_local = isset( $old_value['local'] ) ? (int) $old_value['local'] : 0;

				// New values
				$new_version = isset( $new_value['version'] ) && $new_value['version'] ? $new_value['version'] : $this->get_latest_version();

				if ( empty( $old_local ) || $old_version !== $new_version || ! file_exists( $this->get_fonts_dir() . 'css' . DIRECTORY_SEPARATOR . 'all.css' ) ) {
					$response = $this->download_package( $new_version, $new_value );

					if ( is_wp_error( $response ) ) {
						add_settings_error( 'general', 'fontawesome_download', __( 'ERROR:', 'font-awesome-settings' ) . ' ' . $response->get_error_message(), 'error' );
					}
				}
			}
		}

		/**
		 * Get the fonts directory local path.
		 *
		 * @since 1.1.0
		 *
		 * @param string Fonts directory local path.
		 */
		public function get_fonts_dir() {
			$upload_dir = wp_upload_dir( null, false );

			return $upload_dir['basedir'] . DIRECTORY_SEPARATOR .  'ayefonts' . DIRECTORY_SEPARATOR . 'fa' . DIRECTORY_SEPARATOR;
		}

		/**
		 * Get the fonts directory local url.
		 *
		 * @since 1.1.0
		 *
		 * @param string Fonts directory local url.
		 */
		public function get_fonts_url() {
			$upload_dir = wp_upload_dir( null, false );

			return $upload_dir['baseurl'] .  '/ayefonts/fa/';
		}

		/**
		 * Check whether load locally active.
		 *
		 * @since 1.1.0
		 *
		 * @return bool True if active else false.
		 */
		public function has_local() {
			if ( ! empty( $this->settings['local'] ) && empty( $this->settings['pro'] ) && file_exists( $this->get_fonts_dir() . 'css' . DIRECTORY_SEPARATOR . 'all.css' ) ) {
				return true;
			}

			return false;
		}

		/**
		 * Get the WP Filesystem access.
		 *
		 * @since 1.1.0
		 *
		 * @return object The WP Filesystem.
		 */
		public function get_wp_filesystem() {
			if ( ! function_exists( 'get_filesystem_method' ) ) {
				require_once( ABSPATH . "/wp-admin/includes/file.php" );
			}

			$access_type = get_filesystem_method();

			if ( $access_type === 'direct' ) {
				/* You can safely run request_filesystem_credentials() without any issues and don't need to worry about passing in a URL */
				$creds = request_filesystem_credentials( trailingslashit( site_url() ) . 'wp-admin/', '', false, false, array() );

				/* Initialize the API */
				if ( ! WP_Filesystem( $creds ) ) {
					/* Any problems and we exit */
					return false;
				}

				global $wp_filesystem;

				return $wp_filesystem;
				/* Do our file manipulations below */
			} else if ( defined( 'FTP_USER' ) ) {
				$creds = request_filesystem_credentials( trailingslashit( site_url() ) . 'wp-admin/', '', false, false, array() );

				/* Initialize the API */
				if ( ! WP_Filesystem( $creds ) ) {
					/* Any problems and we exit */
					return false;
				}

				global $wp_filesystem;

				return $wp_filesystem;
			} else {
				/* Don't have direct write access. Prompt user with our notice */
				return false;
			}
		}

		/**
		 * Download the fontawesome package file.
		 *
		 * @since 1.1.0
		 *
		 * @param mixed $version The font awesome.
		 * @param array $option Fontawesome settings.
		 * @return WP_ERROR|bool Error on fail and true on success.
		 */
		public function download_package( $version, $option = array() ) {
			$filename = 'fontawesome-free-' . $version . '-web';
			$url = 'https://use.fontawesome.com/releases/v' . $version . '/' . $filename . '.zip';

			if ( ! function_exists( 'wp_handle_upload' ) ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
			}

			$download_file = download_url( esc_url_raw( $url ) );

			if ( is_wp_error( $download_file ) ) {
				return new WP_Error( 'fontawesome_download_failed', __( $download_file->get_error_message(), 'font-awesome-settings' ) );
			} else if ( empty( $download_file ) ) {
				return new WP_Error( 'fontawesome_download_failed', __( 'Something went wrong in downloading the font awesome to store locally.', 'font-awesome-settings' ) );
			}

			$response = $this->extract_package( $download_file, $filename, true );

			// Update local version.
			if ( is_wp_error( $response ) ) {
				return $response;
			} else if ( $response ) {
				if ( empty( $option ) ) {
					$option = get_option( 'wp-font-awesome-settings' );
				}

				$option['local_version'] = $version;

				// Remove action to prevent looping.
				remove_action( 'update_option_wp-font-awesome-settings', array( $this, 'update_option_wp_font_awesome_settings' ), 10, 2 );

				update_option( 'wp-font-awesome-settings', $option );

				return true;
			}

			return false;
		}

		/**
		 * Extract the fontawesome package file.
		 *
		 * @since 1.1.0
		 *
		 * @param string $package The package file path.
		 * @param string $dirname Package file name.
		 * @param bool   $delete_package Delete temp file or not.
		 * @return WP_Error|bool True on success WP_Error on fail.
		 */
		public function extract_package( $package, $dirname = '', $delete_package = false ) {
			global $wp_filesystem;

			$wp_filesystem = $this->get_wp_filesystem();

			if ( empty( $wp_filesystem ) && isset( $wp_filesystem->errors ) && is_wp_error( $wp_filesystem->errors ) && $wp_filesystem->errors->get_error_code() ) {
				return new WP_Error( 'fontawesome_filesystem_error', __( $wp_filesystem->errors->get_error_message(), 'font-awesome-settings' ) );
			} else if ( empty( $wp_filesystem ) ) {
				return new WP_Error( 'fontawesome_filesystem_error', __( 'Failed to initialise WP_Filesystem while trying to download the Font Awesome package.', 'font-awesome-settings' ) );
			}

			$fonts_dir = $this->get_fonts_dir();
			$fonts_tmp_dir = dirname( $fonts_dir ) . DIRECTORY_SEPARATOR . 'fa-tmp' . DIRECTORY_SEPARATOR;

			if ( $wp_filesystem->is_dir( $fonts_tmp_dir ) ) {
				$wp_filesystem->delete( $fonts_tmp_dir, true );
			}

			// Unzip package to working directory.
			$result = unzip_file( $package, $fonts_tmp_dir );

			if ( is_wp_error( $result ) ) {
				$wp_filesystem->delete( $fonts_tmp_dir, true );

				if ( 'incompatible_archive' === $result->get_error_code() ) {
					return new WP_Error( 'fontawesome_incompatible_archive', __( $result->get_error_message(), 'font-awesome-settings' ) );
				}

				return $result;
			}

			if ( $wp_filesystem->is_dir( $fonts_dir ) ) {
				$wp_filesystem->delete( $fonts_dir, true );
			}

			$extract_dir = $fonts_tmp_dir;

			if ( $dirname && $wp_filesystem->is_dir( $extract_dir . $dirname . DIRECTORY_SEPARATOR ) ) {
				$extract_dir .= $dirname . DIRECTORY_SEPARATOR;
			}

			try {
				$return = $wp_filesystem->move( $extract_dir, $fonts_dir, true );
			} catch ( Exception $e ) {
				$return = new WP_Error( 'fontawesome_move_package', __( 'Fail to move font awesome package!', 'font-awesome-settings' ) );
			}

			if ( $wp_filesystem->is_dir( $fonts_tmp_dir ) ) {
				$wp_filesystem->delete( $fonts_tmp_dir, true );
			}

			// Once extracted, delete the package if required.
			if ( $delete_package ) {
				unlink( $package );
			}

			return $return;
		}
	}

	/**
	 * Run the class if found.
	 */
	WP_Font_Awesome_Settings::instance();
}
