<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( "AyeCode_Connect_Helper" ) ) {
	/**
	 * Allow the quick setup and connection of our AyeCode Connect plugin.
	 *
	 * Class AyeCode_Connect_Helper
	 */
	class AyeCode_Connect_Helper {

		// Hold the version number
		var $version = "1.0.4";

		// Hold the default strings.
		var $strings = array();

		// Hold the default pages.
		var $pages = array();

		/**
		 * The constructor.
		 *
		 * AyeCode_Connect_Helper constructor.
		 *
		 * @param array $strings
		 * @param array $pages
		 */
		public function __construct( $strings = array(), $pages = array() ) {
			// Only fire if not localhost and the current user has the right permissions.
			if ( ! $this->is_localhost() && current_user_can( 'manage_options' ) ) {
				// set default strings
				$default_strings = array(
					'connect_title'     => __( "Thanks for choosing an AyeCode Product!", 'ayecode-connect' ),
					'connect_external'  => __( "Please confirm you wish to connect your site?", 'ayecode-connect' ),
					'connect'           => wp_sprintf( __( "<strong>Have a license?</strong> Forget about entering license keys or downloading zip files, connect your site for instant access. %slearn more%s", 'ayecode-connect' ), "<a href='https://ayecode.io/introducing-ayecode-connect/' target='_blank'>", "</a>" ),
					'connect_button'    => __( "Connect Site", 'ayecode-connect' ),
					'connecting_button' => __( "Connecting...", 'ayecode-connect' ),
					'error_localhost'   => __( "This service will only work with a live domain, not a localhost.", 'ayecode-connect' ),
					'error'             => __( "Something went wrong, please refresh and try again.", 'ayecode-connect' ),
				);
				$this->strings   = array_merge( $default_strings, $strings );

				// set default pages
				$default_pages = array();
				$this->pages   = array_merge( $default_pages, $pages );

				// maybe show connect site notice
				add_action( 'admin_notices', array( $this, 'ayecode_connect_install_notice' ) );

				// add ajax action if not already added
				if ( ! has_action( 'wp_ajax_ayecode_connect_helper' ) ) {
					add_action( 'wp_ajax_ayecode_connect_helper', array( $this, 'ayecode_connect_install' ) );
				}
			}

			// add ajax action if not already added
			if ( ! has_action( 'wp_ajax_nopriv_ayecode_connect_helper_installed' ) ) {
				add_action( 'wp_ajax_nopriv_ayecode_connect_helper_installed', array( $this, 'ayecode_connect_helper_installed' ) );
			}
		}

		/**
		 * Give a way to check we can connect via a external redirect.
		 */
		public function ayecode_connect_helper_installed(){
			$active = array(
				'gd'    =>  defined('GEODIRECTORY_VERSION') && version_compare(GEODIRECTORY_VERSION,'2.0.0.79','>') ? 1 : 0,
				'uwp'    =>  defined('USERSWP_VERSION') && version_compare(USERSWP_VERSION,'1.2.1.5','>') ? 1 : 0,
				'wpi'    =>  defined('WPINV_VERSION') && version_compare(WPINV_VERSION,'1.0.14','>') ? 1 : 0,
			);
			wp_send_json_success( $active );
			wp_die();
		}

		/**
		 * Get slug from path
		 *
		 * @param  string $key
		 *
		 * @return string
		 */
		private function format_plugin_slug( $key ) {
			$slug = explode( '/', $key );
			$slug = explode( '.', end( $slug ) );

			return $slug[0];
		}

		/**
		 * Install and activate the AyeCode Connect Plugin
		 */
		public function ayecode_connect_install() {
			// bail if localhost
			if ( $this->is_localhost() ) {
				wp_send_json_error( $this->strings['error_localhost'] );
			}

			// Explicitly clear the event.
			wp_clear_scheduled_hook( 'geodir_plugin_background_installer', func_get_args() );

			$success     = true;
			$plugin_slug = "ayecode-connect";
			if ( ! empty( $plugin_slug ) ) {
				require_once( ABSPATH . 'wp-admin/includes/file.php' );
				require_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
				require_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

				WP_Filesystem();

				$skin              = new Automatic_Upgrader_Skin;
				$upgrader          = new WP_Upgrader( $skin );
				$installed_plugins = array_map( array( $this, 'format_plugin_slug' ), array_keys( get_plugins() ) );
				$plugin_slug       = $plugin_slug;
				$plugin            = $plugin_slug . '/' . $plugin_slug . '.php';
				$installed         = false;
				$activate          = false;

				// See if the plugin is installed already
				if ( in_array( $plugin_slug, $installed_plugins ) ) {
					$installed = true;
					$activate  = ! is_plugin_active( $plugin );
				}

				// Install this thing!
				if ( ! $installed ) {

					// Suppress feedback
					ob_start();

					try {
						$plugin_information = plugins_api( 'plugin_information', array(
							'slug'   => $plugin_slug,
							'fields' => array(
								'short_description' => false,
								'sections'          => false,
								'requires'          => false,
								'rating'            => false,
								'ratings'           => false,
								'downloaded'        => false,
								'last_updated'      => false,
								'added'             => false,
								'tags'              => false,
								'homepage'          => false,
								'donate_link'       => false,
								'author_profile'    => false,
								'author'            => false,
							),
						) );

						if ( is_wp_error( $plugin_information ) ) {
							throw new Exception( $plugin_information->get_error_message() );
						}

						$package  = $plugin_information->download_link;
						$download = $upgrader->download_package( $package );

						if ( is_wp_error( $download ) ) {
							throw new Exception( $download->get_error_message() );
						}

						$working_dir = $upgrader->unpack_package( $download, true );

						if ( is_wp_error( $working_dir ) ) {
							throw new Exception( $working_dir->get_error_message() );
						}

						$result = $upgrader->install_package( array(
							'source'                      => $working_dir,
							'destination'                 => WP_PLUGIN_DIR,
							'clear_destination'           => false,
							'abort_if_destination_exists' => false,
							'clear_working'               => true,
							'hook_extra'                  => array(
								'type'   => 'plugin',
								'action' => 'install',
							),
						) );

						if ( is_wp_error( $result ) ) {
							throw new Exception( $result->get_error_message() );
						}

						$activate = true;

					} catch ( Exception $e ) {
						$success = false;
					}

					// Discard feedback
					ob_end_clean();
				}

				wp_clean_plugins_cache();

				// Activate this thing
				if ( $activate ) {
					try {
						$result = activate_plugin( $plugin );

						if ( is_wp_error( $result ) ) {
							$success = false;
						} else {
							$success = true;
						}
					} catch ( Exception $e ) {
						$success = false;
					}
				}
			}

			if ( $success && function_exists( 'ayecode_connect_args' ) ) {
				ayecode_connect();// init
				$args        = ayecode_connect_args();
				$client      = new AyeCode_Connect( $args );
				$redirect_to = ! empty( $_POST['redirect_to'] ) ? esc_url_raw( $_POST['redirect_to'] ) : '';
				$redirect    = $client->build_connect_url( $redirect_to );
				wp_send_json_success( array( 'connect_url' => $redirect ) );
			} else {
				wp_send_json_error( $this->strings['error_localhost'] );
			}
			wp_die();
		}

		/**
		 * Check if maybe localhost.
		 *
		 * @return bool
		 */
		public function is_localhost() {
			$localhost = false;

			$host              = isset( $_SERVER['HTTP_HOST'] ) ? $_SERVER['HTTP_HOST'] : '';
			$localhost_domains = array(
				'localhost',
				'localhost.localdomain',
				'127.0.0.1',
				'::1'
			);

			if ( in_array( $host, $localhost_domains ) ) {
				$localhost = true;
			}

			return $localhost;
		}

		/**
		 * Show notice to connect site.
		 */
		public function ayecode_connect_install_notice() {
			if ( $this->maybe_show() ) {
				$connect_title_string     = $this->strings['connect_title'];
				$connect_external_string  = $this->strings['connect_external'];
				$connect_string           = $this->strings['connect'];
				$connect_button_string    = $this->strings['connect_button'];
				$connecting_button_string = $this->strings['connecting_button'];
				?>
				<div class="notice notice-info acch-notice">
					<span class="acch-float-left">
						<svg width="61px" height="61px" viewBox="0 0 61 61" version="1.1"
						     xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
						<defs>
							<polygon id="path-1"
							         points="4.70437018e-05 0.148846272 60.8504481 0.148846272 60.8504481 61 4.70437018e-05 61"></polygon>
						</defs>
						<g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
							<g id="Group-6" transform="translate(-8.000000, -4.000000)">
								<g id="Group-5" transform="translate(8.000000, 4.000000)">
									<g id="Group-17">
										<mask id="mask-2" fill="white">
											<use xlink:href="#path-1"></use>
										</mask>
										<g id="Clip-16"></g>
										<path
											d="M60.8504481,30.5740468 C60.8504481,47.3793116 47.229101,61.0000314 30.4252476,61.0000314 C13.6215509,61.0000314 4.70437018e-05,47.3793116 4.70437018e-05,30.5740468 C4.70437018e-05,13.7711342 13.6215509,0.148846272 30.4252476,0.148846272 C47.229101,0.148846272 60.8504481,13.7711342 60.8504481,30.5740468"
											id="Fill-15" fill="#4C96D7" mask="url(#mask-2)"></path>
									</g>
									<path
										d="M7.34736527,20.4434788 C7.34736527,14.815441 10.231253,12 16,12 L16,16.2224505 C13.1153027,16.2224505 11.6736826,17.6294599 11.6736826,20.4434788 L11.6736826,26.7780236 C11.6736826,28.9016534 10.59182,30.310085 8.42858032,30.9988939 C10.5775721,31.7039788 11.6519871,33.1116203 11.6519871,35.2221344 L11.6519871,41.5566793 C11.6519871,44.3705401 13.0927976,45.7777075 15.9783045,45.7777075 L15.9783045,50 C10.2246148,50 7.34736527,47.184717 7.34736527,41.5566793 L7.34736527,35.2221344 C7.34736527,33.815283 5.89748795,33.1116203 3,33.1116203 L3,28.8883797 C5.89748795,28.8883797 7.34736527,28.185033 7.34736527,26.7780236 L7.34736527,20.4434788 Z"
										id="Fill-18" fill="#FFFFFF"></path>
									<path
										d="M53.6524181,41.5551342 C53.6524181,47.1845707 50.7690344,50 45,50 L45,45.7775671 C47.8841934,45.7775671 49.3266948,44.3691413 49.3266948,41.5551342 L49.3266948,35.2221959 C49.3266948,33.0969947 50.4079637,31.689201 52.5719588,30.9989729 C50.4222123,30.2954711 49.3483914,28.8884675 49.3483914,26.77654 L49.3483914,20.4434437 C49.3483914,17.6281723 47.90589,16.2225909 45.021049,16.2225909 L45.021049,12 C50.7758348,12 53.6524181,14.8140072 53.6524181,20.4434437 L53.6524181,26.77654 C53.6524181,28.1835435 55.1023677,28.8884675 58,28.8884675 L58,33.1116905 C55.1023677,33.1116905 53.6524181,33.8151923 53.6524181,35.2221959 L53.6524181,41.5551342 Z"
										id="Fill-20" fill="#FFFFFF"></path>
									<path
										d="M46.0272652,44 C48.1048141,44 48.9396754,43.2042837 48.9795214,41.1979284 L34.4844624,30.499526 L49,19.7867451 C48.9558678,17.7920822 48.1210065,17 46.0479025,17 L45.8064452,17 L30.9992856,27.9275105 L16.1929198,17 L15.9727348,17 C13.8943922,17 13.0596896,17.7958743 13.0206374,19.8023876 L27.5141088,30.499526 L13,41.2126229 C13.0434972,43.2071278 13.8781998,44 15.9513037,44 L16.1929198,44 L30.9992856,33.0718574 L45.8064452,44 L46.0272652,44 Z"
										id="Fill-22" fill="#FFFFFF"></path>
								</g>
							</g>
						</g>
					</svg>
					</span>
					<span class="acch-float-left acch-text">
						<h3 class="acch-title"><?php echo esc_attr( $connect_title_string ); ?></h3>
					<p><?php echo $connect_string; ?>
					</p>
					</span>

					<span class="acch-float-left acch-button">
						<button onclick="ayecode_connect_helper(this);" id="gd-connect-site" class="button button-primary" data-connecting="<?php echo esc_attr( $connecting_button_string ); ?>"><?php echo esc_attr( $connect_button_string ) ?></button>
					</span>
				</div>

				<?php
				// only include the popup HTML if needed.
				if ( ! empty( $_REQUEST['external-connect-request'] ) ) {
					?>
					<div id="ayecode-connect-helper-external-confirm" style="display:none;">
						<div class="noticex notice-info acch-notice" style="border: none;">
					<span class="acch-float-left">
						<svg width="61px" height="61px" viewBox="0 0 61 61" version="1.1"
						     xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
						<defs>
							<polygon id="path-1"
							         points="4.70437018e-05 0.148846272 60.8504481 0.148846272 60.8504481 61 4.70437018e-05 61"></polygon>
						</defs>
						<g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
							<g id="Group-6" transform="translate(-8.000000, -4.000000)">
								<g id="Group-5" transform="translate(8.000000, 4.000000)">
									<g id="Group-17">
										<mask id="mask-2" fill="white">
											<use xlink:href="#path-1"></use>
										</mask>
										<g id="Clip-16"></g>
										<path
											d="M60.8504481,30.5740468 C60.8504481,47.3793116 47.229101,61.0000314 30.4252476,61.0000314 C13.6215509,61.0000314 4.70437018e-05,47.3793116 4.70437018e-05,30.5740468 C4.70437018e-05,13.7711342 13.6215509,0.148846272 30.4252476,0.148846272 C47.229101,0.148846272 60.8504481,13.7711342 60.8504481,30.5740468"
											id="Fill-15" fill="#4C96D7" mask="url(#mask-2)"></path>
									</g>
									<path
										d="M7.34736527,20.4434788 C7.34736527,14.815441 10.231253,12 16,12 L16,16.2224505 C13.1153027,16.2224505 11.6736826,17.6294599 11.6736826,20.4434788 L11.6736826,26.7780236 C11.6736826,28.9016534 10.59182,30.310085 8.42858032,30.9988939 C10.5775721,31.7039788 11.6519871,33.1116203 11.6519871,35.2221344 L11.6519871,41.5566793 C11.6519871,44.3705401 13.0927976,45.7777075 15.9783045,45.7777075 L15.9783045,50 C10.2246148,50 7.34736527,47.184717 7.34736527,41.5566793 L7.34736527,35.2221344 C7.34736527,33.815283 5.89748795,33.1116203 3,33.1116203 L3,28.8883797 C5.89748795,28.8883797 7.34736527,28.185033 7.34736527,26.7780236 L7.34736527,20.4434788 Z"
										id="Fill-18" fill="#FFFFFF"></path>
									<path
										d="M53.6524181,41.5551342 C53.6524181,47.1845707 50.7690344,50 45,50 L45,45.7775671 C47.8841934,45.7775671 49.3266948,44.3691413 49.3266948,41.5551342 L49.3266948,35.2221959 C49.3266948,33.0969947 50.4079637,31.689201 52.5719588,30.9989729 C50.4222123,30.2954711 49.3483914,28.8884675 49.3483914,26.77654 L49.3483914,20.4434437 C49.3483914,17.6281723 47.90589,16.2225909 45.021049,16.2225909 L45.021049,12 C50.7758348,12 53.6524181,14.8140072 53.6524181,20.4434437 L53.6524181,26.77654 C53.6524181,28.1835435 55.1023677,28.8884675 58,28.8884675 L58,33.1116905 C55.1023677,33.1116905 53.6524181,33.8151923 53.6524181,35.2221959 L53.6524181,41.5551342 Z"
										id="Fill-20" fill="#FFFFFF"></path>
									<path
										d="M46.0272652,44 C48.1048141,44 48.9396754,43.2042837 48.9795214,41.1979284 L34.4844624,30.499526 L49,19.7867451 C48.9558678,17.7920822 48.1210065,17 46.0479025,17 L45.8064452,17 L30.9992856,27.9275105 L16.1929198,17 L15.9727348,17 C13.8943922,17 13.0596896,17.7958743 13.0206374,19.8023876 L27.5141088,30.499526 L13,41.2126229 C13.0434972,43.2071278 13.8781998,44 15.9513037,44 L16.1929198,44 L30.9992856,33.0718574 L45.8064452,44 L46.0272652,44 Z"
										id="Fill-22" fill="#FFFFFF"></path>
								</g>
							</g>
						</g>
					</svg>
					</span>
					<span class="acch-float-left acch-text">
						<h3 class="acch-title"><?php echo esc_attr( $connect_external_string ); ?></h3>
					</span>

					<span class="acch-float-left acch-button">
						<button onclick="ayecode_connect_helper(this);" id="gd-connect-site" class="button button-primary" data-connecting="<?php echo esc_attr( $connecting_button_string ); ?>"><?php echo esc_attr( $connect_button_string ) ?></button>
					</span>
						</div>
					</div>
					<?php
				}

				// add required scripts
				$this->script();
			}
		}

		/**
		 * Get the JS Script.
		 */
		public function script() {

			// add thickbox if external request is requested
			if ( ! empty( $_REQUEST['external-connect-request'] ) ) {
				add_thickbox();
			}
			?>
			<style>
				.acch-title {
					margin: 0;
					padding: 0;
				}

				.acch-notice {
					display: table;
					width: 99%;
					position: relative;
					margin: 0;
					padding: 5px;
					border: 1px solid #ccc;
					border-radius: 3px;
				}

				.acch-float-left {
					display: table-cell;
					vertical-align: middle;
				}

				.acch-float-left svg {
					vertical-align: middle;
				}

				.acch-button {
					zoom: 1.3;
				}
			</style>
			<script>
				/**
				 * Ajax function to install and activate the AyeCode Connect plugin.
				 *
				 * @param $this
				 */
				function ayecode_connect_helper($this) {
					$connect_text = jQuery($this).text();
					$connecting_text = jQuery($this).data('connecting');
					$current_url = window.location.href + "&ayecode-connected=1";
					$current_url = $current_url.replace("&external-connect-request=true", ""); // strip external request param
					$current_url = $current_url.replace("&external-connect-request=1", ""); // strip external request param

					jQuery.ajax({
						type: "POST",
						url: ajaxurl,
						data: {
							action: 'ayecode_connect_helper',
							security: '<?php echo wp_create_nonce( 'ayecode-connect-helper' );?>',
							redirect_to: $current_url
						},
						beforeSend: function () {
							jQuery($this).html('<i class="fas fa-circle-notch fa-spin"></i> ' + $connecting_text).prop('disabled', true);// disable submit
						},
						success: function (data) {
							console.log(data);
							if (data.success == true && data.data.connect_url) {
								window.location.href = data.data.connect_url;
							} else if (data.success === false) {
								alert(data.data);
								jQuery($this).html($connect_text).prop('disabled', false);// enable submit
							}
						}
					});
				} 
				<?php
				// add thickbox if external request is requested
				if(! empty( $_REQUEST['external-connect-request'] )) {
				?>
				jQuery(function () {
					setTimeout(function () {
						tb_show("AyeCode Connect", "?TB_inline?width=300&height=80&inlineId=ayecode-connect-helper-external-confirm");
					}, 200);
				});
				<?php
				}
				?>
			</script>
			<?php
		}

		/**
		 * Decide what pages to show on.
		 *
		 * @return bool
		 */
		public function maybe_show() {
			$show = false;

			// check if on a page set to show
			if ( isset( $_REQUEST['page'] ) && in_array( $_REQUEST['page'], $this->pages ) ) {
				// check if not active and connected
				if ( ! defined( 'AYECODE_CONNECT_VERSION' ) || ! get_option( 'ayecode_connect_blog_token' ) ) {
					$show = true;
				}
			}

			return $show;
		}
	}
}
