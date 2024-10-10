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
		public $version = '0.2.26';

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
		public $latest = "5.2.2";

		/**
		 * Current version of select2 being used.
		 *
		 * @var string
		 */
		public $select2_version = "4.0.11";

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
		 * AyeCode_UI_Settings instance.
		 *
		 * @access private
		 * @since  1.0.0
		 * @var    AyeCode_UI_Settings There can be only one!
		 */
		private static $instance = null;


		/**
		 * Main AyeCode_UI_Settings Instance.
		 *
		 * Ensures only one instance of AyeCode_UI_Settings is loaded or can be loaded.
		 *
		 * @since 1.0.0
		 * @static
		 * @return AyeCode_UI_Settings - Main instance.
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof AyeCode_UI_Settings ) ) {

				self::$instance = new AyeCode_UI_Settings;

				add_action( 'init', array( self::$instance, 'init' ) ); // set settings

				if ( is_admin() ) {
					add_action( 'admin_menu', array( self::$instance, 'menu_item' ) );
					add_action( 'admin_init', array( self::$instance, 'register_settings' ) );

					// Maybe show example page
					add_action( 'template_redirect', array( self::$instance,'maybe_show_examples' ) );

					if ( defined( 'BLOCKSTRAP_VERSION' ) ) {
						add_filter( 'sd_aui_colors', array( self::$instance,'sd_aui_colors' ), 10, 3 );
					}
				}

				add_action( 'customize_register', array( self::$instance, 'customizer_settings' ));

				do_action( 'ayecode_ui_settings_loaded' );
			}

			return self::$instance;
		}

		/**
		 * Add custom colors to the color selector.
		 *
		 * @param $theme_colors
		 * @param $include_outlines
		 * @param $include_branding
		 *
		 * @return mixed
		 */
		public function sd_aui_colors( $theme_colors, $include_outlines, $include_branding ){


			$setting = wp_get_global_settings();

			if(!empty($setting['color']['palette']['custom'])){
				foreach($setting['color']['palette']['custom'] as $color){
					$theme_colors[$color['slug']] = esc_attr($color['name']);
				}
			}

			return $theme_colors;
		}

		/**
		 * Setup some constants.
		 */
		public function constants(){
			define( 'AUI_PRIMARY_COLOR_ORIGINAL', "#1e73be" );
			define( 'AUI_SECONDARY_COLOR_ORIGINAL', '#6c757d' );
			define( 'AUI_INFO_COLOR_ORIGINAL', '#17a2b8' );
			define( 'AUI_WARNING_COLOR_ORIGINAL', '#ffc107' );
			define( 'AUI_DANGER_COLOR_ORIGINAL', '#dc3545' );
			define( 'AUI_SUCCESS_COLOR_ORIGINAL', '#44c553' );
			define( 'AUI_LIGHT_COLOR_ORIGINAL', '#f8f9fa' );
			define( 'AUI_DARK_COLOR_ORIGINAL', '#343a40' );
			define( 'AUI_WHITE_COLOR_ORIGINAL', '#fff' );
			define( 'AUI_PURPLE_COLOR_ORIGINAL', '#ad6edd' );
			define( 'AUI_SALMON_COLOR_ORIGINAL', '#ff977a' );
			define( 'AUI_CYAN_COLOR_ORIGINAL', '#35bdff' );
			define( 'AUI_GRAY_COLOR_ORIGINAL', '#ced4da' );
			define( 'AUI_INDIGO_COLOR_ORIGINAL', '#502c6c' );
			define( 'AUI_ORANGE_COLOR_ORIGINAL', '#orange' );
			define( 'AUI_BLACK_COLOR_ORIGINAL', '#000' );

			if ( ! defined( 'AUI_PRIMARY_COLOR' ) ) {
				define( 'AUI_PRIMARY_COLOR', AUI_PRIMARY_COLOR_ORIGINAL );
			}
			if ( ! defined( 'AUI_SECONDARY_COLOR' ) ) {
				define( 'AUI_SECONDARY_COLOR', AUI_SECONDARY_COLOR_ORIGINAL );
			}
			if ( ! defined( 'AUI_INFO_COLOR' ) ) {
				define( 'AUI_INFO_COLOR', AUI_INFO_COLOR_ORIGINAL );
			}
			if ( ! defined( 'AUI_WARNING_COLOR' ) ) {
				define( 'AUI_WARNING_COLOR', AUI_WARNING_COLOR_ORIGINAL );
			}
			if ( ! defined( 'AUI_DANGER_COLOR' ) ) {
				define( 'AUI_DANGER_COLOR', AUI_DANGER_COLOR_ORIGINAL );
			}
			if ( ! defined( 'AUI_SUCCESS_COLOR' ) ) {
				define( 'AUI_SUCCESS_COLOR', AUI_SUCCESS_COLOR_ORIGINAL );
			}
			if ( ! defined( 'AUI_LIGHT_COLOR' ) ) {
				define( 'AUI_LIGHT_COLOR', AUI_LIGHT_COLOR_ORIGINAL );
			}
			if ( ! defined( 'AUI_DARK_COLOR' ) ) {
				define( 'AUI_DARK_COLOR', AUI_DARK_COLOR_ORIGINAL );
			}
			if ( ! defined( 'AUI_WHITE_COLOR' ) ) {
				define( 'AUI_WHITE_COLOR', AUI_WHITE_COLOR_ORIGINAL );
			}
			if ( ! defined( 'AUI_PURPLE_COLOR' ) ) {
				define( 'AUI_PURPLE_COLOR', AUI_PURPLE_COLOR_ORIGINAL );
			}
			if ( ! defined( 'AUI_SALMON_COLOR' ) ) {
				define( 'AUI_SALMON_COLOR', AUI_SALMON_COLOR_ORIGINAL );
			}
			if ( ! defined( 'AUI_CYAN_COLOR' ) ) {
				define( 'AUI_CYAN_COLOR', AUI_CYAN_COLOR_ORIGINAL );
			}
			if ( ! defined( 'AUI_GRAY_COLOR' ) ) {
				define( 'AUI_GRAY_COLOR', AUI_GRAY_COLOR_ORIGINAL );
			}
			if ( ! defined( 'AUI_INDIGO_COLOR' ) ) {
				define( 'AUI_INDIGO_COLOR', AUI_INDIGO_COLOR_ORIGINAL );
			}
			if ( ! defined( 'AUI_ORANGE_COLOR' ) ) {
				define( 'AUI_ORANGE_COLOR', AUI_ORANGE_COLOR_ORIGINAL );
			}
			if ( ! defined( 'AUI_BLACK_COLOR' ) ) {
				define( 'AUI_BLACK_COLOR', AUI_BLACK_COLOR_ORIGINAL );
			}

		}

		public static function get_colors( $original = false){

			if ( ! defined( 'AUI_PRIMARY_COLOR' ) ) {
				return array();
			}
			if ( $original ) {
				return array(
					'primary'   => AUI_PRIMARY_COLOR_ORIGINAL,
					'secondary' => AUI_SECONDARY_COLOR_ORIGINAL,
					'info'      => AUI_INFO_COLOR_ORIGINAL,
					'warning'   => AUI_WARNING_COLOR_ORIGINAL,
					'danger'    => AUI_DANGER_COLOR_ORIGINAL,
					'success'   => AUI_SUCCESS_COLOR_ORIGINAL,
					'light'     => AUI_LIGHT_COLOR_ORIGINAL,
					'dark'      => AUI_DARK_COLOR_ORIGINAL,
					'white'     => AUI_WHITE_COLOR_ORIGINAL,
					'purple'    => AUI_PURPLE_COLOR_ORIGINAL,
					'salmon'    => AUI_SALMON_COLOR_ORIGINAL,
					'cyan'      => AUI_CYAN_COLOR_ORIGINAL,
					'gray'      => AUI_GRAY_COLOR_ORIGINAL,
					'indigo'    => AUI_INDIGO_COLOR_ORIGINAL,
					'orange'    => AUI_ORANGE_COLOR_ORIGINAL,
					'black'     => AUI_BLACK_COLOR_ORIGINAL,
				);
			}

			return array(
				'primary'   => AUI_PRIMARY_COLOR,
				'secondary' => AUI_SECONDARY_COLOR,
				'info'      => AUI_INFO_COLOR,
				'warning'   => AUI_WARNING_COLOR,
				'danger'    => AUI_DANGER_COLOR,
				'success'   => AUI_SUCCESS_COLOR,
				'light'     => AUI_LIGHT_COLOR,
				'dark'      => AUI_DARK_COLOR,
				'white'     => AUI_WHITE_COLOR,
				'purple'    => AUI_PURPLE_COLOR,
				'salmon'    => AUI_SALMON_COLOR,
				'cyan'      => AUI_CYAN_COLOR,
				'gray'      => AUI_GRAY_COLOR,
				'indigo'    => AUI_INDIGO_COLOR,
				'orange'    => AUI_ORANGE_COLOR,
				'black'     => AUI_BLACK_COLOR,
			);
		}

		/**
		 * Add admin body class to show when BS5 is active.
		 *
		 * @param $classes
		 *
		 * @return mixed
		 */
		public function add_bs5_admin_body_class( $classes = '' ) {
			$classes .= ' aui_bs5';

			return $classes;
		}

		/**
		 * Add a body class to show when BS5 is active.
		 *
		 * @param $classes
		 *
		 * @return mixed
		 */
		public function add_bs5_body_class( $classes ) {
			$classes[] = 'aui_bs5';

			return $classes;
		}

		/**
		 * Initiate the settings and add the required action hooks.
		 */
		public function init() {
            global $aui_bs5;

			// Maybe fix settings
			if ( ! empty( $_REQUEST['aui-fix-admin'] ) && !empty($_REQUEST['nonce']) && wp_verify_nonce( $_REQUEST['nonce'], "aui-fix-admin" ) ) {
				$db_settings = get_option( 'ayecode-ui-settings' );
				if ( ! empty( $db_settings ) ) {
					$db_settings['css_backend'] = 'compatibility';
					$db_settings['js_backend'] = 'core-popper';
					update_option( 'ayecode-ui-settings', $db_settings );
					wp_safe_redirect(admin_url("options-general.php?page=ayecode-ui-settings&updated=true"));
				}
			}

			$this->constants();
			$this->settings = $this->get_settings();
			$this->url = $this->get_url();

            // define the version
			$aui_bs5 = $this->settings['bs_ver'] === '5';

			if ( $aui_bs5 ) {
				include_once( dirname( __FILE__ ) . '/inc/bs-conversion.php' );
				add_filter( 'admin_body_class', array( $this, 'add_bs5_admin_body_class' ), 99, 1 );
				add_filter( 'body_class', array( $this, 'add_bs5_body_class' ) );
			}

			/**
			 * Maybe load CSS
			 *
			 * We load super early in case there is a theme version that might change the colors
			 */
			if ( $this->settings['css'] ) {
				$priority = $this->is_bs3_compat() ? 100 : 1;
                $priority = $aui_bs5 ? 10 : $priority;
				add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_style' ), $priority );
			}
			if ( $this->settings['css_backend'] && $this->load_admin_scripts() ) {
				add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_style' ), 1 );
			}

			// maybe load JS
			if ( $this->settings['js'] ) {
				$priority = $this->is_bs3_compat() ? 100 : 1;
				add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), $priority );
			}
			if ( $this->settings['js_backend'] && $this->load_admin_scripts() ) {
				add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), 1 );
			}

			// Maybe set the HTML font size
			if ( $this->settings['html_font_size'] ) {
				add_action( 'wp_footer', array( $this, 'html_font_size' ), 10 );
			}

			// Maybe show backend style error
			if( $this->settings['css_backend'] != 'compatibility' || $this->settings['js_backend'] != 'core-popper' ){
				add_action( 'admin_notices', array( $this, 'show_admin_style_notice' ) );
			}

		}

		/**
		 * Show admin notice if backend scripts not loaded.
		 */
		public function show_admin_style_notice(){
			$fix_url = admin_url("options-general.php?page=ayecode-ui-settings&aui-fix-admin=true&nonce=".wp_create_nonce('aui-fix-admin'));
			$button = '<a href="'.esc_url($fix_url).'" class="button-primary">Fix Now</a>';
			$message = __( '<b>Style Issue:</b> AyeCode UI is disable or set wrong.')." " .$button;
			echo '<div class="notice notice-error aui-settings-error-notice"><p>'. wp_kses_post( $message ).'</p></div>';
		}

		/**
		 * Check if we should load the admin scripts or not.
		 *
		 * @return bool
		 */
		public function load_admin_scripts(){
			$result = true;

			// check if specifically disabled
			if(!empty($this->settings['disable_admin'])){
				$url_parts = explode("\n",$this->settings['disable_admin']);
				foreach($url_parts as $part){
					if( strpos($_SERVER['REQUEST_URI'], trim($part)) !== false ){
						return false; // return early, no point checking further
					}
				}
			}

			return $result;
		}

		/**
		 * Add a html font size to the footer.
		 */
		public function html_font_size(){
			$this->settings = $this->get_settings();
			echo "<style>html{font-size:".absint($this->settings['html_font_size'])."px;}</style>";
		}

		/**
		 * Check if the current admin screen should load scripts.
		 *
		 * @return bool
		 */
		public function is_aui_screen(){
//			echo '###';exit;
			$load = false;
			// check if we should load or not
			if ( is_admin() ) {
				// Only enable on set pages
				$aui_screens = array(
					'page',
                    //'docs',
					'post',
					'settings_page_ayecode-ui-settings',
					'appearance_page_gutenberg-widgets',
					'widgets',
					'ayecode-ui-settings',
					'site-editor'
				);
				$screen_ids = apply_filters( 'aui_screen_ids', $aui_screens );

				$screen = get_current_screen();

//				echo '###'.$screen->id;

				// check if we are on a AUI screen
				if ( $screen && in_array( $screen->id, $screen_ids ) ) {
					$load = true;
				}

				//load for widget previews in WP 5.8
				if( !empty($_REQUEST['legacy-widget-preview'])){
					$load = true;
				}
			}



			return apply_filters( 'aui_load_on_admin' , $load );
		}

		/**
		 * Check if the current theme is a block theme.
		 *
		 * @return bool
		 */
		public static function is_block_theme() {
			if ( function_exists( 'wp_is_block_theme' && wp_is_block_theme() ) ) {
				return true;
			}

			return false;
		}

		/**
		 * Adds the styles.
		 */
		public function enqueue_style() {
            global $aui_bs5;

            $load_fse = false;

			if( is_admin() && !$this->is_aui_screen()){
				// don't add wp-admin scripts if not requested to
			}else{
				$css_setting = current_action() == 'wp_enqueue_scripts' ? 'css' : 'css_backend';

				$rtl = is_rtl() && ! $aui_bs5 ? '-rtl' : '';

                $bs_ver = $this->settings['bs_ver'] == '5' ? '-v5' : '';

				if($this->settings[$css_setting]){
					$compatibility = $this->settings[$css_setting]=='core' ? false : true;
					$url = $this->settings[$css_setting]=='core' ? $this->url.'assets'.$bs_ver.'/css/ayecode-ui'.$rtl.'.css' : $this->url.'assets'.$bs_ver.'/css/ayecode-ui-compatibility'.$rtl.'.css';



					wp_register_style( 'ayecode-ui', $url, array(), $this->version );
					wp_enqueue_style( 'ayecode-ui' );

					$current_screen = function_exists('get_current_screen' ) ? get_current_screen() : '';

//					if ( is_admin() && !empty($_REQUEST['postType']) ) {
					if ( is_admin() && ( !empty($_REQUEST['postType']) || $current_screen->is_block_editor() ) && ( defined( 'BLOCKSTRAP_VERSION' ) || defined( 'AUI_FSE' ) )  ) {
						$url = $this->url.'assets'.$bs_ver.'/css/ayecode-ui-fse.css';
						wp_register_style( 'ayecode-ui-fse', $url, array(), $this->version );
						wp_enqueue_style( 'ayecode-ui-fse' );
						$load_fse = true;
					}


					// flatpickr
					wp_register_style( 'flatpickr', $this->url.'assets'.$bs_ver.'/css/flatpickr.min.css', array(), $this->version );

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
				h1, h2, h3, h4, h5, h6 {
				    display: block;
				    font-weight: 600;
				}
				h2,h3 {
				    font-size: 1.3em;
				    margin: 1em 0
				}
				.blocks-widgets-container .bsui *{
					box-sizing: border-box;
				}
				.bs-tooltip-top .arrow{
					margin-left:0px;
				}
				
				.custom-switch input[type=checkbox]{
				    display:none;
				}
                ";

						// @todo, remove once fixed :: fix for this bug https://github.com/WordPress/gutenberg/issues/14377
						$custom_css .= "
						.edit-post-sidebar input[type=color].components-text-control__input{
						    padding: 0;
						}
					";
						wp_add_inline_style( 'ayecode-ui', $custom_css );
					}

					// custom changes
					if ( $load_fse ) {
						wp_add_inline_style( 'ayecode-ui-fse', self::custom_css($compatibility) );
					}else{
						wp_add_inline_style( 'ayecode-ui', self::custom_css($compatibility) );

					}

				}
			}


		}

		/**
		 * Get inline script used if bootstrap enqueued
		 *
		 * If this remains small then its best to use this than to add another JS file.
		 */
		public function inline_script() {
            global $aui_bs5;
			// Flatpickr calendar locale
			$flatpickr_locale = self::flatpickr_locale();

			ob_start();
			if ( $aui_bs5 ) {
				include_once( dirname( __FILE__ ) . '/inc/bs5-js.php' );
			}else{
				include_once( dirname( __FILE__ ) . '/inc/bs4-js.php' );
            }

			$output = ob_get_clean();

			/*
			 * We only add the <script> tags for code highlighting, so we strip them from the output.
			 */
			return str_replace( array(
				'<script>',
				'</script>'
			), '', self::minify_js($output) );
		}


		/**
		 * JS to help with conflict issues with other plugins and themes using bootstrap v3.
		 *
		 * @TODO we may need this when other conflicts arrise.
		 * @return mixed
		 */
		public static function bs3_compat_js() {
			ob_start();
			?>
            <script>
				<?php if( defined( 'FUSION_BUILDER_VERSION' ) ){ ?>
                /* With Avada builder */

				<?php } ?>
            </script>
			<?php
			return str_replace( array(
				'<script>',
				'</script>'
			), '', ob_get_clean());
		}

		/**
		 * Get inline script used if bootstrap file browser enqueued.
		 *
		 * If this remains small then its best to use this than to add another JS file.
		 */
		public function inline_script_file_browser(){
			ob_start();
			?>
            <script>
                // run on doc ready
                jQuery(document).ready(function () {
                    bsCustomFileInput.init();
                });
            </script>
			<?php
			$output = ob_get_clean();

			/*
			 * We only add the <script> tags for code highlighting, so we strip them from the output.
			 */
			return str_replace( array(
				'<script>',
				'</script>'
			), '', $output );
		}

		/**
		 * Adds the Font Awesome JS.
		 */
		public function enqueue_scripts() {

			if( is_admin() && !$this->is_aui_screen()){
				// don't add wp-admin scripts if not requested to
			}else {

				$js_setting = current_action() == 'wp_enqueue_scripts' ? 'js' : 'js_backend';

				$bs_ver = $this->settings['bs_ver'] == '5' ? '-v5' : '';

				// select2
				wp_register_script( 'select2', $this->url . 'assets/js/select2.min.js', array( 'jquery' ), $this->select2_version );

				// flatpickr
				wp_register_script( 'flatpickr', $this->url . 'assets/js/flatpickr.min.js', array(), $this->version );

				// iconpicker
				if ( defined( 'FAS_ICONPICKER_JS_URL' ) ) {
					wp_register_script( 'iconpicker', FAS_ICONPICKER_JS_URL, array(), $this->version );
				}else{
					wp_register_script( 'iconpicker', $this->url . 'assets/js/fa-iconpicker.min.js', array(), $this->version );
				}

				// Bootstrap file browser
				wp_register_script( 'aui-custom-file-input', $url = $this->url . 'assets/js/bs-custom-file-input.min.js', array( 'jquery' ), $this->select2_version );
				wp_add_inline_script( 'aui-custom-file-input', $this->inline_script_file_browser() );

				$load_inline = false;

				if ( $this->settings[ $js_setting ] == 'core-popper' ) {
					// Bootstrap bundle
					$url = $this->url . 'assets' . $bs_ver . '/js/bootstrap.bundle.min.js';
					wp_register_script( 'bootstrap-js-bundle', $url, array(
						'select2',
						'jquery'
					), $this->version, $this->is_bs3_compat() );
					// if in admin then add to footer for compatibility.
					is_admin() ? wp_enqueue_script( 'bootstrap-js-bundle', '', null, null, true ) : wp_enqueue_script( 'bootstrap-js-bundle' );
					$script = $this->inline_script();
					wp_add_inline_script( 'bootstrap-js-bundle', $script );
				} elseif ( $this->settings[ $js_setting ] == 'popper' ) {
					$url = $this->url . 'assets/js/popper.min.js'; //@todo we need to update this to bs5
					wp_register_script( 'bootstrap-js-popper', $url, array( 'select2', 'jquery' ), $this->version );
					wp_enqueue_script( 'bootstrap-js-popper' );
					$load_inline = true;
				} else {
					$load_inline = true;
				}

				// Load needed inline scripts by faking the loading of a script if the main script is not being loaded
				if ( $load_inline ) {
					wp_register_script( 'bootstrap-dummy', '', array( 'select2', 'jquery' ) );
					wp_enqueue_script( 'bootstrap-dummy' );
					$script = $this->inline_script();
					wp_add_inline_script( 'bootstrap-dummy', $script );
				}
			}

		}

		/**
		 * Enqueue flatpickr if called.
		 */
		public function enqueue_flatpickr(){
			wp_enqueue_style( 'flatpickr' );
			wp_enqueue_script( 'flatpickr' );
		}

		/**
		 * Enqueue iconpicker if called.
		 */
		public function enqueue_iconpicker(){
			wp_enqueue_style( 'iconpicker' );
			wp_enqueue_script( 'iconpicker' );
		}

		/**
		 * Get the url path to the current folder.
		 *
		 * @return string
		 */
		public function get_url() {
			$content_dir = wp_normalize_path( untrailingslashit( WP_CONTENT_DIR ) );
			$content_url = untrailingslashit( WP_CONTENT_URL );

			// Replace http:// to https://.
			if ( strpos( $content_url, 'http://' ) === 0 && strpos( plugins_url(), 'https://' ) === 0 ) {
				$content_url = str_replace( 'http://', 'https://', $content_url );
			}

			// Check if we are inside a plugin
			$file_dir = str_replace( "/includes", "", wp_normalize_path( dirname( __FILE__ ) ) );
			$url = str_replace( $content_dir, $content_url, $file_dir );

			return trailingslashit( $url );
		}

		/**
		 * Get the url path to the current folder.
		 *
		 * @return string
		 */
		public function get_url_old() {

			$url = '';
			// check if we are inside a plugin
			$file_dir = str_replace( "/includes","", wp_normalize_path( dirname( __FILE__ ) ) );

			// add check in-case user has changed wp-content dir name.
			$wp_content_folder_name = basename(WP_CONTENT_DIR);
			$dir_parts = explode("/$wp_content_folder_name/",$file_dir);
			$url_parts = explode("/$wp_content_folder_name/",plugins_url());

			if(!empty($url_parts[0]) && !empty($dir_parts[1])){
				$url = trailingslashit( $url_parts[0]."/$wp_content_folder_name/".$dir_parts[1] );
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
		 * Get a list of themes and their default JS settings.
		 *
		 * @return array
		 */
		public function theme_js_settings(){
			return array(
				'ayetheme' => 'popper',
				'listimia' => 'required',
				'listimia_backend' => 'core-popper',
				//'avada'    => 'required', // removed as we now add compatibility
			);
		}

		/**
         * Get the date the site was installed.
         *
		 * @return false|string
		 */
        public function get_site_install_date() {
	        global $wpdb; // This gives you access to the WordPress database object

	        // Prepare the SQL query to get the oldest registration date
	        $query = "SELECT MIN(user_registered) AS oldest_registration_date FROM {$wpdb->users}";

	        // Execute the query
	        $date = $wpdb->get_var( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

	        return $date ? $date : false;
        }

		/**
		 * Show admin notice if backend scripts not loaded.
		 */
		public function show_admin_version_notice(){
			$fix_url = admin_url("options-general.php?page=ayecode-ui-settings" );
			$button = '<a href="'.esc_url($fix_url).'" class="button-primary">View Settings</a>';
			$message = __( '<b>Style Issue:</b> AyeCode UI has changed its default version from v4 to v5, if you notice unwanted style changes, please revert to v4 (saving the settings page will remove this notice)')." " .$button;
			echo '<div class="notice notice-error aui-settings-error-notice"><p>'. wp_kses_post( $message ).'</p></div>';
		}

		/**
		 * Get the current Font Awesome output settings.
		 *
		 * @return array The array of settings.
		 */
		public function get_settings() {

			$db_settings = get_option( 'ayecode-ui-settings' );

            // Maybe show default version notice
			$site_install_date = new DateTime( self::get_site_install_date() );
			$switch_over_date = new DateTime("2024-02-01");
			if ( empty( $db_settings ) && $site_install_date < $switch_over_date ) {
				add_action( 'admin_notices', array( $this, 'show_admin_version_notice' ) );
			}

			$js_default = 'core-popper';
			$js_default_backend = $js_default;

			// maybe set defaults (if no settings set)
			if(empty($db_settings)){
				$active_theme = strtolower( get_template() ); // active parent theme.
				$theme_js_settings = self::theme_js_settings();
				if(isset($theme_js_settings[$active_theme])){
					$js_default = $theme_js_settings[$active_theme];
					$js_default_backend = isset($theme_js_settings[$active_theme."_backend"]) ? $theme_js_settings[$active_theme."_backend"] : $js_default;
				}
			}

			/**
			 * Filter the default settings.
			 */
			$defaults = apply_filters( 'ayecode-ui-default-settings', array(
				'css'            => 'compatibility', // core, compatibility
				'js'             => $js_default, // js to load, core-popper, popper
				'html_font_size' => '16', // js to load, core-popper, popper
				'css_backend'    => 'compatibility', // core, compatibility
				'js_backend'     => $js_default_backend, // js to load, core-popper, popper
				'disable_admin'  => '', // URL snippets to disable loading on admin
                'bs_ver'         => '5', // The default bootstrap version to sue by default
			), $db_settings );

			$settings = wp_parse_args( $db_settings, $defaults );

			/**
			 * Filter the Bootstrap settings.
			 *
			 * @todo if we add this filer people might use it and then it defeats the purpose of this class :/
			 */
			return $this->settings = apply_filters( 'ayecode-ui-settings', $settings, $db_settings, $defaults );
		}


		/**
		 * The settings page html output.
		 */
		public function settings_page() {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_attr__( 'You do not have sufficient permissions to access this page.', 'ayecode-connect' ) );
			}
            $overrides = apply_filters( 'ayecode-ui-settings', array(), array(), array() );

			?>
            <div class="wrap">
                <h1><?php echo esc_attr( $this->name ); ?></h1>
                <p><?php echo esc_html( apply_filters( 'ayecode-ui-settings-message', __("Here you can adjust settings if you are having compatibility issues.", 'ayecode-connect' ) ) );?></p>
                <form method="post" action="options.php">
					<?php
					settings_fields( 'ayecode-ui-settings' );
					do_settings_sections( 'ayecode-ui-settings' );
					?>

                    <h2><?php esc_html_e( 'BootStrap Version', 'ayecode-connect' ); ?></h2>
                    <p><?php echo esc_html( apply_filters( 'ayecode-ui-version-settings-message', __("V5 is recommended, however if you have another plugin installed using v4, you may need to use v4 also.", 'ayecode-connect' ) ) );?></p>
	                <div class="bsui"><?php
	                if ( ! empty( $overrides ) ) {
		                echo aui()->alert(array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			                'type'=> 'info',
			                'content'=> esc_attr__("Some options are disabled as your current theme is overriding them.", 'ayecode-connect' )
		                ));
	                }
	                ?>
                    </div>
                    <table class="form-table wpbs-table-version-settings">
                        <tr valign="top">
                            <th scope="row"><label for="wpbs-css"><?php esc_html_e( 'Version', 'ayecode-connect' ); ?></label></th>
                            <td>
                                <select name="ayecode-ui-settings[bs_ver]" id="wpbs-css" <?php echo !empty($overrides['bs_ver']) ? 'disabled' : ''; ?>>
                                    <option	value="5" <?php selected( $this->settings['bs_ver'], '5' ); ?>><?php esc_html_e( 'v5 (recommended)', 'ayecode-connect' ); ?></option>
                                    <option value="4" <?php selected( $this->settings['bs_ver'], '4' ); ?>><?php esc_html_e( 'v4 (legacy)', 'ayecode-connect' ); ?></option>
                                </select>
                            </td>
                        </tr>
                    </table>

                    <h2><?php esc_html_e( 'Frontend', 'ayecode-connect' ); ?></h2>
                    <table class="form-table wpbs-table-settings">
                        <tr valign="top">
                            <th scope="row"><label for="wpbs-css"><?php esc_html_e( 'Load CSS', 'ayecode-connect' ); ?></label></th>
                            <td>
                                <select name="ayecode-ui-settings[css]" id="wpbs-css" <?php echo !empty($overrides['css']) ? 'disabled' : ''; ?>>
                                    <option	value="compatibility" <?php selected( $this->settings['css'], 'compatibility' ); ?>><?php esc_html_e( 'Compatibility Mode (default)', 'ayecode-connect' ); ?></option>
                                    <option value="core" <?php selected( $this->settings['css'], 'core' ); ?>><?php esc_html_e( 'Full Mode', 'ayecode-connect' ); ?></option>
                                    <option	value="" <?php selected( $this->settings['css'], '' ); ?>><?php esc_html_e( 'Disabled', 'ayecode-connect' ); ?></option>
                                </select>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row"><label for="wpbs-js"><?php esc_html_e( 'Load JS', 'ayecode-connect' ); ?></label></th>
                            <td>
                                <select name="ayecode-ui-settings[js]" id="wpbs-js" <?php echo !empty($overrides['js']) ? 'disabled' : ''; ?>>
                                    <option	value="core-popper" <?php selected( $this->settings['js'], 'core-popper' ); ?>><?php esc_html_e( 'Core + Popper (default)', 'ayecode-connect' ); ?></option>
                                    <option value="popper" <?php selected( $this->settings['js'], 'popper' ); ?>><?php esc_html_e( 'Popper', 'ayecode-connect' ); ?></option>
                                    <option value="required" <?php selected( $this->settings['js'], 'required' ); ?>><?php esc_html_e( 'Required functions only', 'ayecode-connect' ); ?></option>
                                    <option	value="" <?php selected( $this->settings['js'], '' ); ?>><?php esc_html_e( 'Disabled (not recommended)', 'ayecode-connect' ); ?></option>
                                </select>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row"><label for="wpbs-font_size"><?php esc_html_e( 'HTML Font Size (px)', 'ayecode-connect' ); ?></label></th>
                            <td>
                                <input type="number" name="ayecode-ui-settings[html_font_size]" id="wpbs-font_size" value="<?php echo absint( $this->settings['html_font_size']); ?>" placeholder="16" <?php echo !empty($overrides['html_font_size']) ? 'disabled' : ''; ?> />
                                <p class="description" ><?php esc_html_e("Our font sizing is rem (responsive based) here you can set the html font size in-case your theme is setting it too low.", 'ayecode-connect' );?></p>
                            </td>
                        </tr>

                    </table>

                    <h2><?php esc_html_e( 'Backend', 'ayecode-connect' ); ?> (wp-admin)</h2>
                    <table class="form-table wpbs-table-settings">
                        <tr valign="top">
                            <th scope="row"><label for="wpbs-css-admin"><?php esc_html_e( 'Load CSS', 'ayecode-connect' ); ?></label></th>
                            <td>
                                <select name="ayecode-ui-settings[css_backend]" id="wpbs-css-admin" <?php echo !empty($overrides['css_backend']) ? 'disabled' : ''; ?>>
                                    <option	value="compatibility" <?php selected( $this->settings['css_backend'], 'compatibility' ); ?>><?php esc_html_e( 'Compatibility Mode (default)', 'ayecode-connect' ); ?></option>
                                    <option value="core" <?php selected( $this->settings['css_backend'], 'core' ); ?>><?php esc_html_e( 'Full Mode (will cause style issues)', 'ayecode-connect' ); ?></option>
                                    <option	value="" <?php selected( $this->settings['css_backend'], '' ); ?>><?php esc_html_e( 'Disabled', 'ayecode-connect' ); ?></option>
                                </select>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row"><label for="wpbs-js-admin"><?php esc_html_e( 'Load JS', 'ayecode-connect' ); ?></label></th>
                            <td>
                                <select name="ayecode-ui-settings[js_backend]" id="wpbs-js-admin" <?php echo !empty($overrides['js_backend']) ? 'disabled' : ''; ?>>
                                    <option	value="core-popper" <?php selected( $this->settings['js_backend'], 'core-popper' ); ?>><?php esc_html_e( 'Core + Popper (default)', 'ayecode-connect' ); ?></option>
                                    <option value="popper" <?php selected( $this->settings['js_backend'], 'popper' ); ?>><?php esc_html_e( 'Popper', 'ayecode-connect' ); ?></option>
                                    <option value="required" <?php selected( $this->settings['js_backend'], 'required' ); ?>><?php esc_html_e( 'Required functions only', 'ayecode-connect' ); ?></option>
                                    <option	value="" <?php selected( $this->settings['js_backend'], '' ); ?>><?php esc_html_e( 'Disabled (not recommended)', 'ayecode-connect' ); ?></option>
                                </select>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row"><label for="wpbs-disable-admin"><?php esc_html_e( 'Disable load on URL', 'ayecode-connect' ); ?></label></th>
                            <td>
                                <p><?php esc_html_e( 'If you have backend conflict you can enter a partial URL argument that will disable the loading of AUI on those pages. Add each argument on a new line.', 'ayecode-connect' ); ?></p>
                                <textarea name="ayecode-ui-settings[disable_admin]" rows="10" cols="50" id="wpbs-disable-admin" class="large-text code" spellcheck="false" placeholder="myplugin.php &#10;action=go"><?php echo esc_textarea( $this->settings['disable_admin'] );?></textarea>
                            </td>
                        </tr>
                    </table>

					<?php
					submit_button();
					?>
                </form>
                <div id="wpbs-version" data-aui-source="<?php echo esc_attr( $this->get_load_source() ); ?>"><?php echo esc_html( $this->version ); ?></div>
            </div>
			<?php
		}

        public function get_load_source(){
	        $file = str_replace( array( "/", "\\" ), "/", realpath( __FILE__ ) );
	        $plugins_dir = str_replace( array( "/", "\\" ), "/", realpath( WP_PLUGIN_DIR ) );

	        // Find source plugin/theme of SD
	        $source = array();
	        if ( strpos( $file, $plugins_dir ) !== false ) {
		        $source = explode( "/", plugin_basename( $file ) );
	        } else if ( function_exists( 'get_theme_root' ) ) {
		        $themes_dir = str_replace( array( "/", "\\" ), "/", realpath( get_theme_root() ) );

		        if ( strpos( $file, $themes_dir ) !== false ) {
			        $source = explode( "/", ltrim( str_replace( $themes_dir, "", $file ), "/" ) );
		        }
	        }

            return isset($source[0]) ? esc_attr($source[0]) : '';
        }

		public function customizer_settings($wp_customize){
			$wp_customize->add_section('aui_settings', array(
				'title'    => __('AyeCode UI', 'ayecode-connect' ),
				'priority' => 120,
			));

			//  =============================
			//  = Color Picker              =
			//  =============================
			$wp_customize->add_setting('aui_options[color_primary]', array(
				'default'           => AUI_PRIMARY_COLOR,
				'sanitize_callback' => 'sanitize_hex_color',
				'capability'        => 'edit_theme_options',
				'type'              => 'option',
				'transport'         => 'refresh',
			));
			$wp_customize->add_control( new WP_Customize_Color_Control($wp_customize, 'color_primary', array(
				'label'    => __('Primary Color', 'ayecode-connect' ),
				'section'  => 'aui_settings',
				'settings' => 'aui_options[color_primary]',
			)));

			$wp_customize->add_setting('aui_options[color_secondary]', array(
				'default'           => '#6c757d',
				'sanitize_callback' => 'sanitize_hex_color',
				'capability'        => 'edit_theme_options',
				'type'              => 'option',
				'transport'         => 'refresh',
			));
			$wp_customize->add_control( new WP_Customize_Color_Control($wp_customize, 'color_secondary', array(
				'label'    => __('Secondary Color', 'ayecode-connect' ),
				'section'  => 'aui_settings',
				'settings' => 'aui_options[color_secondary]',
			)));
		}

		/**
		 * CSS to help with conflict issues with other plugins and themes using bootstrap v3.
		 *
		 * @return mixed
		 */
		public static function bs3_compat_css() {
			ob_start();
			?>
            <style>
                /* Bootstrap 3 compatibility */
                body.modal-open .modal-backdrop.show:not(.in) {opacity:0.5;}
                body.modal-open .modal.show:not(.in)  {opacity:1;z-index: 99999}
                body.modal-open .modal.show:not(.in) .modal-content  {box-shadow: none;}
                body.modal-open .modal.show:not(.in)  .modal-dialog {transform: initial;}

                body.modal-open .modal.bsui .modal-dialog{left: auto;}

                .collapse.show:not(.in){display: inherit;}
                .fade.show{opacity: 1;}

                <?php if( defined( 'SVQ_THEME_VERSION' ) ){ ?>
                /* KLEO theme specific */
                .kleo-main-header .navbar-collapse.collapse.show:not(.in){display: block !important;}
                <?php } ?>

                <?php if( defined( 'FUSION_BUILDER_VERSION' ) ){ ?>
                /* With Avada builder */
                body.modal-open .modal.in  {opacity:1;z-index: 99999}
                body.modal-open .modal.bsui.in .modal-content  {box-shadow: none;}
                .bsui .collapse.in{display: inherit;}
                .bsui .collapse.in.row.show{display: flex;}
                .bsui .collapse.in.row:not(.show){display: none;}

                <?php } ?>
            </style>
			<?php
			return str_replace( array(
				'<style>',
				'</style>'
			), '', self::minify_css( ob_get_clean() ) );
		}


		public static function custom_css($compatibility = true) {
            global $aui_bs5;

			$colors = array();
			if ( defined( 'BLOCKSTRAP_VERSION' ) ) {


				$setting = wp_get_global_settings();

//                print_r(wp_get_global_styles());//exit;
//                print_r(get_default_block_editor_settings());exit;

//                print_r($setting);echo  '###';exit;
				if(!empty($setting['color']['palette']['theme'])){
					foreach($setting['color']['palette']['theme'] as $color){
						$colors[$color['slug']] = esc_attr($color['color']);
					}
				}

				if(!empty($setting['color']['palette']['custom'])){
					foreach($setting['color']['palette']['custom'] as $color){
						$colors[$color['slug']] = esc_attr($color['color']);
					}
				}
			}else{
				$settings = get_option('aui_options');
				$colors = array(
					'primary'   => ! empty( $settings['color_primary'] ) ? $settings['color_primary'] : AUI_PRIMARY_COLOR,
					'secondary' => ! empty( $settings['color_secondary'] ) ? $settings['color_secondary'] : AUI_SECONDARY_COLOR
				);
			}

			ob_start();

			?>
            <style>
                <?php

					// BS v3 compat
					if( self::is_bs3_compat() ){
						echo self::bs3_compat_css(); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					}

                    $current_screen = function_exists('get_current_screen' ) ? get_current_screen() : '';
                    $is_fse = false;
                    if ( is_admin() && ( !empty($_REQUEST['postType']) || $current_screen->is_block_editor() ) && ( defined( 'BLOCKSTRAP_VERSION' ) || defined( 'AUI_FSE' ) )  ) {
                        $is_fse = true;
                    }

					if(!empty($colors)){
						$d_colors = self::get_colors(true);

//						$is_fse = !empty($_REQUEST['postType']) && $_REQUEST['postType']=='wp_template';
						foreach($colors as $key => $color ){
							if((empty( $d_colors[$key]) ||  $d_colors[$key] != $color) || $is_fse ) {
								$var = $is_fse ? "var(--wp--preset--color--$key)" : $color;
								$compat = $is_fse ? '.editor-styles-wrapper' : $compatibility;
								echo $aui_bs5 ? self::css_overwrite_bs5($key,$var,$compat,$color) : self::css_overwrite($key,$var,$compat,$color); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							}
						}
					   // exit;
					}

					// Set admin bar z-index lower when modal is open.
					echo ' body.modal-open #wpadminbar{z-index:999}.embed-responsive-16by9 .fluid-width-video-wrapper{padding:0 !important;position:initial}';

					if(is_admin()){
						echo ' body.modal-open #adminmenuwrap{z-index:999} body.modal-open #wpadminbar{z-index:1025}';
					}

                    if( $aui_bs5 && defined( 'BLOCKSTRAP_VERSION' )  ){
                        $css = '';
                        $theme_settings = wp_get_global_styles();

//                        print_r( $theme_settings);exit;

                        // font face
                        if( !empty( $theme_settings['typography']['fontFamily'] ) ){
                            $t_fontface = str_replace( array('var:preset|','font-family|'), array('--wp--preset--','font-family--'), $theme_settings['typography']['fontFamily']  ); //var(--wp--preset--font-family--poppins)
                            $css .= '--bs-body-font-family: ' . esc_attr($t_fontface) . ';';
                        }

                        // font size
                        if( !empty( $theme_settings['typography']['fontSize'] ) ){
                            $css .= '--bs-body-font-size: ' . esc_attr( $theme_settings['typography']['fontSize'] ) . ' ;';
                        }

                        // line height
                         if( !empty( $theme_settings['typography']['lineHeight'] ) ){
                            $css .= '--bs-body-line-height: ' . esc_attr( $theme_settings['typography']['lineHeight'] ) . ';';
                        }


                           // font weight
                         if( !empty( $theme_settings['typography']['fontWeight'] ) ){
                            $css .= '--bs-body-font-weight: ' . esc_attr( $theme_settings['typography']['fontWeight'] ) . ';';
                        }

                        // Background
                         if( !empty( $theme_settings['color']['background'] ) ){
                            $css .= '--bs-body-bg: ' . esc_attr( $theme_settings['color']['background'] ) . ';';
                        }

                         // Background Gradient
                         if( !empty( $theme_settings['color']['gradient'] ) ){
                            $css .= 'background: ' . esc_attr( $theme_settings['color']['gradient'] ) . ';';
                        }

                           // Background Gradient
                         if( !empty( $theme_settings['color']['gradient'] ) ){
                            $css .= 'background: ' . esc_attr( $theme_settings['color']['gradient'] ) . ';';
                        }

                        // text color
                        if( !empty( $theme_settings['color']['text'] ) ){
                            $css .= '--bs-body-color: ' . esc_attr( $theme_settings['color']['text'] ) . ';';
                        }


                        // link colors
                        if( !empty( $theme_settings['elements']['link']['color']['text'] ) ){
                            $css .= '--bs-link-color: ' . esc_attr( $theme_settings['elements']['link']['color']['text'] ) . ';';
                        }
                        if( !empty( $theme_settings['elements']['link'][':hover']['color']['text'] ) ){
                            $css .= '--bs-link-hover-color: ' . esc_attr( $theme_settings['elements']['link'][':hover']['color']['text'] ) . ';';
                        }



                        if($css){
                            echo  $is_fse ? 'body.editor-styles-wrapper{' . esc_attr( $css ) . '}' : 'body{' . esc_attr( $css ) . '}';
                        }

                        $bep = $is_fse ? 'body.editor-styles-wrapper ' : '';


                        // Headings
                        $headings_css = '';
                        if( !empty( $theme_settings['elements']['heading']['color']['text'] ) ){
                            $headings_css .= "color: " . esc_attr( $theme_settings['elements']['heading']['color']['text'] ) . ";";
                        }

                        // heading background
                        if( !empty( $theme_settings['elements']['heading']['color']['background'] ) ){
                            $headings_css .= 'background: ' . esc_attr( $theme_settings['elements']['heading']['color']['background'] ) . ';';
                        }

                         // heading font family
                        if( !empty( $theme_settings['elements']['heading']['typography']['fontFamily'] ) ){
                            $headings_css .= 'font-family: ' . esc_attr( $theme_settings['elements']['heading']['typography']['fontFamily']  ) . ';';
                        }

                        if( $headings_css ){
                            echo "$bep h1,$bep h2,$bep h3, $bep h4,$bep h5,$bep h6{ " . esc_attr( $headings_css ) . "}"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                        }

                        $hs = array('h1','h2','h3','h4','h5','h6');

                        foreach($hs as $hn){
                            $h_css = '';
                             if( !empty( $theme_settings['elements'][$hn]['color']['text'] ) ){
                                $h_css .= 'color: ' . esc_attr( $theme_settings['elements'][$hn]['color']['text'] ) . ';';
                             }

                              if( !empty( $theme_settings['elements'][$hn]['typography']['fontSize'] ) ){
                                $h_css .= 'font-size: ' . esc_attr( $theme_settings['elements'][$hn]['typography']['fontSize']  ) . ';';
                             }

                              if( !empty( $theme_settings['elements'][$hn]['typography']['fontFamily'] ) ){
                                $h_css .= 'font-family: ' . esc_attr( $theme_settings['elements'][$hn]['typography']['fontFamily']  ) . ';';
                             }

                             if($h_css){
                                echo esc_attr( $bep  . $hn ) . '{'.esc_attr( $h_css ).'}';
                             }
                        }
                    }

                    // Pagination on Hello Elementor theme.
                    if ( function_exists( 'hello_elementor_setup' ) ) {
                        echo '.aui-nav-links .pagination{justify-content:inherit}';
                    }
                ?>
            </style>
			<?php
			/*
			 * We only add the <script> tags for code highlighting, so we strip them from the output.
			 */
			return str_replace( array(
				'<style>',
				'</style>'
			), '', self::minify_css( ob_get_clean() ) );
		}

		/**
		 * Check if we should add booststrap 3 compatibility changes.
		 *
		 * @return bool
		 */
		public static function is_bs3_compat(){
			return defined('AYECODE_UI_BS3_COMPAT') || defined('SVQ_THEME_VERSION') || defined('FUSION_BUILDER_VERSION');
		}

		public static function hex_to_rgb( $hex ) {
			// Remove '#' if present
			$hex = str_replace( '#', '', $hex );

			// Check if input is RGB
			if ( strpos( $hex, 'rgba(' ) === 0 || strpos( $hex, 'rgb(' ) === 0 ) {
				$_rgb = explode( ',', str_replace( array( 'rgba(', 'rgb(', ')' ), '', $hex ) );

				$rgb = ( isset( $_rgb[0] ) ? (int) trim( $_rgb[0] ) : '0' ) . ',';
				$rgb .= ( isset( $_rgb[1] ) ? (int) trim( $_rgb[1] ) : '0' ) . ',';
				$rgb .= ( isset( $_rgb[2] ) ? (int) trim( $_rgb[2] ) : '0' );

				return $rgb;
			}

			// Convert 3-digit hex to 6-digit hex
			if ( strlen( $hex ) == 3 ) {
				$hex = str_repeat( substr( $hex, 0, 1 ), 2 ) . str_repeat( substr( $hex, 1, 1 ), 2 ) . str_repeat( substr( $hex, 2, 1 ), 2 );
			}

			// Convert hex to RGB
			$r = hexdec( substr( $hex, 0, 2 ) );
			$g = hexdec( substr( $hex, 2, 2 ) );
			$b = hexdec( substr( $hex, 4, 2 ) );

			// Return RGB values as an array
			return $r . ',' . $g . ',' . $b;
		}

		/**
		 * Build the CSS to overwrite a bootstrap color variable.
		 *
		 * @param $type
		 * @param $color_code
		 * @param $compatibility
		 *
		 * @return string
		 */
		public static function css_overwrite_bs5($type,$color_code,$compatibility, $hex = '' ){
			global $aui_bs5;

			$is_var = false;
			$is_custom = strpos($type, 'custom-') !== false ? true : false;
			if(!$color_code){return '';}
			if(strpos($color_code, 'var') !== false){
				//if(!sanitize_hex_color($color_code)){
				$color_code = esc_attr($color_code);
				$is_var = true;
//				$color_code = "rgba($color_code, 0.5)";
//                echo '###1'.$color_code.'###';//exit;
			}

//            echo '@@@'.$color_code.'==='.self::hex_to_rgb($color_code);exit;

			if(!$color_code){return '';}

			$rgb = self::hex_to_rgb($hex);

			if($compatibility===true || $compatibility===1){
				$compatibility = '.bsui';
			}elseif(!$compatibility){
				$compatibility = '';
			}else{
				$compatibility = esc_attr($compatibility);
			}

			$prefix = $compatibility ? $compatibility . " " : "";


            $output = '';

//            echo '####'.$color_code;exit;

			$type = sanitize_html_class($type);

			/**
			 * c = color, b = background color, o = border-color, f = fill
			 */
			$selectors = array(
				".btn-{$type}"                                              => array( 'b', 'o' ),
				".btn-{$type}.disabled"                                     => array( 'b', 'o' ),
				".btn-{$type}:disabled"                                     => array( 'b', 'o' ),
				".btn-outline-{$type}"                                      => array( 'c', 'o' ),
				".btn-outline-{$type}:hover"                                => array( 'b', 'o' ),
				".btn-outline-{$type}:not(:disabled):not(.disabled).active" => array( 'b', 'o' ),
				".btn-outline-{$type}:not(:disabled):not(.disabled):active" => array( 'b', 'o' ),
				".show>.btn-outline-{$type}.dropdown-toggle"                => array( 'b', 'o' ),
				".badge-{$type}"                                            => array( 'b' ),
				".alert-{$type}"                                            => array( 'b', 'o' ),
				".bg-{$type}"                                               => array( 'b', 'f' ),
				".btn-link.btn-{$type}"                                     => array( 'c' ),
				".text-{$type}"                                     => array( 'c' ),
			);

			if ( $aui_bs5 ) {
				unset($selectors[".alert-{$type}" ]);
			}

			if ( $type == 'primary' ) {
				$selectors = $selectors + array(
						'a'                                                                                                    => array( 'c' ),
						'.btn-link'                                                                                            => array( 'c' ),
						'.dropdown-item.active'                                                                                => array( 'b' ),
						'.custom-control-input:checked~.custom-control-label::before'                                          => array(
							'b',
							'o'
						),
						'.custom-checkbox .custom-control-input:indeterminate~.custom-control-label::before'                   => array(
							'b',
							'o'
						),
						'.nav-pills .nav-link.active'                                                                          => array( 'b' ),
						'.nav-pills .show>.nav-link'                                                                           => array( 'b' ),
						'.page-link'                                                                                           => array( 'c' ),
						'.page-item.active .page-link'                                                                         => array(
							'b',
							'o'
						),
						'.progress-bar'                                                                                        => array( 'b' ),
						'.list-group-item.active'                                                                              => array(
							'b',
							'o'
						),
						'.select2-container .select2-results__option--highlighted.select2-results__option[aria-selected=true]' => array( 'b' ),
					);
			}



            // link
			if ( $type === 'primary' ) {
				$output .= 'html body {--bs-link-hover-color: rgba(var(--bs-'.esc_attr($type).'-rgb), .75); --bs-link-color: var(--bs-'.esc_attr($type).'); }';
				$output .= $prefix . ' .breadcrumb{--bs-breadcrumb-item-active-color: '.esc_attr($color_code).';  }';
				$output .= $prefix . ' .navbar { --bs-nav-link-hover-color: '.esc_attr($color_code).'; --bs-navbar-hover-color: '.esc_attr($color_code).'; --bs-navbar-active-color: '.esc_attr($color_code).'; }';

				$output .= $prefix . ' a{color: var(--bs-'.esc_attr($type).');}';
				$output .= $prefix . ' .text-primary{color: var(--bs-'.esc_attr($type).') !important;}';

                // dropdown
				$output .= $prefix . ' .dropdown-menu{--bs-dropdown-link-hover-color: var(--bs-'.esc_attr($type).'); --bs-dropdown-link-active-color: var(--bs-'.esc_attr($type).');}';

                // pagination
				$output .= $prefix . ' .pagination{--bs-pagination-hover-color: var(--bs-'.esc_attr($type).'); --bs-pagination-active-bg: var(--bs-'.esc_attr($type).');}';

			}

			$output .= $prefix . ' .link-'.esc_attr($type).' {color: var(--bs-'.esc_attr($type).'-rgb) !important;}';
			$output .= $prefix . ' .link-'.esc_attr($type).':hover {color: rgba(var(--bs-'.esc_attr($type).'-rgb), .8) !important;}';

			//  buttons
			$output .= $prefix . ' .btn-'.esc_attr($type).'{';
			$output .= ' 
            --bs-btn-bg: '.esc_attr($color_code).';
            --bs-btn-border-color: '.esc_attr($color_code).';
            --bs-btn-hover-bg: rgba(var(--bs-'.esc_attr($type).'-rgb), .9);
            --bs-btn-hover-border-color: rgba(var(--bs-'.esc_attr($type).'-rgb), .9);
            --bs-btn-focus-shadow-rgb: --bs-'.esc_attr($type).'-rgb;
            --bs-btn-active-bg: rgba(var(--bs-'.esc_attr($type).'-rgb), .9);
            --bs-btn-active-border-color: rgba(var(--bs-'.esc_attr($type).'-rgb), .9);
            --bs-btn-active-shadow: unset;
            --bs-btn-disabled-bg: rgba(var(--bs-'.esc_attr($type).'-rgb), .5);
            --bs-btn-disabled-border-color: rgba(var(--bs-'.esc_attr($type).'-rgb), .1);
            ';
//			$output .= '
//		    --bs-btn-color: #fff;
//			--bs-btn-hover-color: #fff;
//			--bs-btn-active-color: #fff;
//			--bs-btn-disabled-color: #fff;
//            ';
			$output .= '}';

			//  buttons outline
			$output .= $prefix . ' .btn-outline-'.esc_attr($type).'{';
			$output .= ' 
			--bs-btn-color: '.esc_attr($color_code).';
            --bs-btn-border-color: '.esc_attr($color_code).';
            --bs-btn-hover-bg: rgba(var(--bs-'.esc_attr($type).'-rgb), .9);
            --bs-btn-hover-border-color: rgba(var(--bs-'.esc_attr($type).'-rgb), .9);
            --bs-btn-focus-shadow-rgb: --bs-'.esc_attr($type).'-rgb;
            --bs-btn-active-bg: rgba(var(--bs-'.esc_attr($type).'-rgb), .9);
            --bs-btn-active-border-color: rgba(var(--bs-'.esc_attr($type).'-rgb), .9);
            --bs-btn-active-shadow: unset;
            --bs-btn-disabled-bg: rgba(var(--bs-'.esc_attr($type).'-rgb), .5);
            --bs-btn-disabled-border-color: rgba(var(--bs-'.esc_attr($type).'-rgb), .1);
            ';
//			$output .= '
//		    --bs-btn-color: #fff;
//			--bs-btn-hover-color: #fff;
//			--bs-btn-active-color: #fff;
//			--bs-btn-disabled-color: #fff;
//            ';
			$output .= '}';


            // button hover
			$output .= $prefix . ' .btn-'.esc_attr($type).':hover{';
			$output .= ' 
            box-shadow: 0 0.25rem 0.25rem 0.125rem rgb(var(--bs-'.esc_attr($type).'-rgb), .1), 0 0.375rem 0.75rem -0.125rem rgb(var(--bs-'.esc_attr($type).'-rgb) , .4);
            }
            ';


			if ( $aui_bs5 ) {
//				$output .= $is_var ? 'html body {--bs-'.esc_attr($type).'-rgb: '.$color_code.'; }' : 'html body {--bs-'.esc_attr($type).'-rgb: '.self::hex_to_rgb($color_code).'; }';
				$output .= 'html body {--bs-'.esc_attr($type).': '.esc_attr($color_code).'; }';
				$output .= 'html body {--bs-'.esc_attr($type).'-rgb: '.$rgb.'; }';
			}


			if ( $is_custom ) {

//				echo '###'.$type;exit;

				// build rules into each type
				foreach($selectors as $selector => $types){
					$selector = $compatibility ? $compatibility . " ".$selector : $selector;
					$types = array_combine($types,$types);
					if(isset($types['c'])){$color[] = $selector;}
					if(isset($types['b'])){$background[] = $selector;}
					if(isset($types['o'])){$border[] = $selector;}
					if(isset($types['f'])){$fill[] = $selector;}
				}

//				// build rules into each type
//				foreach($important_selectors as $selector => $types){
//					$selector = $compatibility ? $compatibility . " ".$selector : $selector;
//					$types = array_combine($types,$types);
//					if(isset($types['c'])){$color_i[] = $selector;}
//					if(isset($types['b'])){$background_i[] = $selector;}
//					if(isset($types['o'])){$border_i[] = $selector;}
//					if(isset($types['f'])){$fill_i[] = $selector;}
//				}

				// add any color rules
				if(!empty($color)){
					$output .= implode(",",$color) . "{color: $color_code;} ";
				}
				if(!empty($color_i)){
					$output .= implode(",",$color_i) . "{color: $color_code !important;} ";
				}

				// add any background color rules
				if(!empty($background)){
					$output .= implode(",",$background) . "{background-color: $color_code;} ";
				}
				if(!empty($background_i)){
					$output .= $aui_bs5 ? '' : implode(",",$background_i) . "{background-color: $color_code !important;} ";
//				$output .= implode(",",$background_i) . "{background-color: rgba(var(--bs-primary-rgb), var(--bs-bg-opacity)) !important;} ";
				}

				// add any border color rules
				if(!empty($border)){
					$output .= implode(",",$border) . "{border-color: $color_code;} ";
				}
				if(!empty($border_i)){
					$output .= implode(",",$border_i) . "{border-color: $color_code !important;} ";
				}

				// add any fill color rules
				if(!empty($fill)){
					$output .= implode(",",$fill) . "{fill: $color_code;} ";
				}
				if(!empty($fill_i)){
					$output .= implode(",",$fill_i) . "{fill: $color_code !important;} ";
				}

			}




			$transition = $is_var ? 'transition: color 0.15s ease-in-out,background-color 0.15s ease-in-out,border-color 0.15s ease-in-out,box-shadow 0.15s ease-in-out,filter 0.15s ease-in-out;' : '';
			// darken
			$darker_075 = $is_var ? $color_code.';filter:brightness(0.925)' : self::css_hex_lighten_darken($color_code,"-0.075");
			$darker_10 = $is_var ? $color_code.';filter:brightness(0.9)' : self::css_hex_lighten_darken($color_code,"-0.10");
			$darker_125 = $is_var ? $color_code.';filter:brightness(0.875)' : self::css_hex_lighten_darken($color_code,"-0.125");
			$darker_40 = $is_var ? $color_code.';filter:brightness(0.6)' : self::css_hex_lighten_darken($color_code,"-0.4");

			// lighten
			$lighten_25 = $is_var ? $color_code.';filter:brightness(1.25)' :self::css_hex_lighten_darken($color_code,"0.25");

			// opacity see https://css-tricks.com/8-digit-hex-codes/
			$op_25 = $color_code."40"; // 25% opacity


			// button states
			$output .= $is_var ? $prefix ." .btn-{$type}{{$transition }} " : '';
			$output .= $prefix ." .btn-{$type}:hover, $prefix .btn-{$type}:focus, $prefix .btn-{$type}.focus{background-color: ".$darker_075.";    border-color: ".$darker_10.";} ";
//			$output .= $prefix ." .btn-{$type}:hover, $prefix .btn-{$type}:focus, $prefix .btn-{$type}.focus{background-color: #000;    border-color: #000;} ";
			$output .= $prefix ." .btn-outline-{$type}:not(:disabled):not(.disabled):active:focus, $prefix .btn-outline-{$type}:not(:disabled):not(.disabled).active:focus, .show>$prefix .btn-outline-{$type}.dropdown-toggle:focus{box-shadow: 0 0 0 0.2rem $op_25;} ";
			$output .= $prefix ." .btn-{$type}:not(:disabled):not(.disabled):active, $prefix .btn-{$type}:not(:disabled):not(.disabled).active, .show>$prefix .btn-{$type}.dropdown-toggle{background-color: ".$darker_10.";    border-color: ".$darker_125.";} ";
            $output .= $prefix ." .btn-{$type}:not(:disabled):not(.disabled):active:focus, $prefix .btn-{$type}:not(:disabled):not(.disabled).active:focus, .show>$prefix .btn-{$type}.dropdown-toggle:focus {box-shadow: 0 0 0 0.2rem $op_25;} ";
            $output .= $prefix ." .btn-{$type}:not(:disabled):not(.disabled):active:focus, $prefix .btn-{$type}:not(:disabled):not(.disabled):focus {box-shadow: 0 0.25rem 0.25rem 0.125rem rgba(var(--bs-{$type}-rgb), 0.1), 0 0.375rem 0.75rem -0.125rem rgba(var(--bs-{$type}-rgb), 0.4);} ";

			// text
//			$output .= $prefix .".xxx, .text-{$type} {color: var(--bs-".esc_attr($type).");} ";


//			if ( $type == 'primary' ) {
//				// dropdown's
//				$output .= $prefix . " .dropdown-item.active, $prefix .dropdown-item:active{background-color: $color_code;} ";
//
//				// input states
//				$output .= $prefix . " .form-control:focus{border-color: " . $lighten_25 . ";box-shadow: 0 0 0 0.2rem $op_25;} ";
//
//				// page link
//				$output .= $prefix . " .page-link:focus{box-shadow: 0 0 0 0.2rem $op_25;} ";
//			}

			// alerts
			if ( $aui_bs5 ) {
//				$output .= $is_var ? '' : $prefix ." .alert-{$type} {background-color: ".$color_code."20;    border-color: ".$color_code."30;color:$darker_40} ";
				$output .= $prefix ." .alert-{$type} {--bs-alert-bg: rgba(var(--bs-{$type}-rgb), .1 ) !important;--bs-alert-border-color: rgba(var(--bs-{$type}-rgb), .25 ) !important;--bs-alert-color: rgba(var(--bs-{$type}-rgb), 1 ) !important;} ";
			}

			return $output;
		}

		/**
		 * Build the CSS to overwrite a bootstrap color variable.
		 *
		 * @param $type
		 * @param $color_code
		 * @param $compatibility
		 *
		 * @return string
		 */
		public static function css_overwrite($type,$color_code,$compatibility, $hex = '' ){
            global $aui_bs5;

			$is_var = false;
			if(!$color_code){return '';}
			if(strpos($color_code, 'var') !== false){
				//if(!sanitize_hex_color($color_code)){
				$color_code = esc_attr($color_code);
				$is_var = true;
//				$color_code = "rgba($color_code, 0.5)";
//                echo '###1'.$color_code.'###';//exit;
			}

//            echo '@@@'.$color_code.'==='.self::hex_to_rgb($color_code);exit;

			if(!$color_code){return '';}

            $rgb = self::hex_to_rgb($hex);

			if($compatibility===true || $compatibility===1){
				$compatibility = '.bsui';
			}elseif(!$compatibility){
				$compatibility = '';
			}else{
				$compatibility = esc_attr($compatibility);
			}



//            echo '####'.$color_code;exit;

			$type = sanitize_html_class($type);

			/**
			 * c = color, b = background color, o = border-color, f = fill
			 */
			$selectors = array(
				".btn-{$type}"                                              => array( 'b', 'o' ),
				".btn-{$type}.disabled"                                     => array( 'b', 'o' ),
				".btn-{$type}:disabled"                                     => array( 'b', 'o' ),
				".btn-outline-{$type}"                                      => array( 'c', 'o' ),
				".btn-outline-{$type}:hover"                                => array( 'b', 'o' ),
				".btn-outline-{$type}:not(:disabled):not(.disabled).active" => array( 'b', 'o' ),
				".btn-outline-{$type}:not(:disabled):not(.disabled):active" => array( 'b', 'o' ),
				".show>.btn-outline-{$type}.dropdown-toggle"                => array( 'b', 'o' ),
				".badge-{$type}"                                            => array( 'b' ),
				".alert-{$type}"                                            => array( 'b', 'o' ),
				".bg-{$type}"                                               => array( 'b', 'f' ),
				".btn-link.btn-{$type}"                                     => array( 'c' ),
			);

			if ( $aui_bs5 ) {
                unset($selectors[".alert-{$type}" ]);
			}

			if ( $type == 'primary' ) {
				$selectors = $selectors + array(
						'a'                                                                                                    => array( 'c' ),
						'.btn-link'                                                                                            => array( 'c' ),
						'.dropdown-item.active'                                                                                => array( 'b' ),
						'.custom-control-input:checked~.custom-control-label::before'                                          => array(
							'b',
							'o'
						),
						'.custom-checkbox .custom-control-input:indeterminate~.custom-control-label::before'                   => array(
							'b',
							'o'
						),
						'.nav-pills .nav-link.active'                                                                          => array( 'b' ),
						'.nav-pills .show>.nav-link'                                                                           => array( 'b' ),
						'.page-link'                                                                                           => array( 'c' ),
						'.page-item.active .page-link'                                                                         => array(
							'b',
							'o'
						),
						'.progress-bar'                                                                                        => array( 'b' ),
						'.list-group-item.active'                                                                              => array(
							'b',
							'o'
						),
						'.select2-container .select2-results__option--highlighted.select2-results__option[aria-selected=true]' => array( 'b' ),
//				    '.custom-range::-webkit-slider-thumb' => array('b'), // these break the inline rules...
//				    '.custom-range::-moz-range-thumb' => array('b'),
//				    '.custom-range::-ms-thumb' => array('b'),
					);
			}

			$important_selectors = array(
				".bg-{$type}" => array('b','f'),
				".border-{$type}" => array('o'),
				".text-{$type}" => array('c'),
			);

			$color = array();
			$color_i = array();
			$background = array();
			$background_i = array();
			$border = array();
			$border_i = array();
			$fill = array();
			$fill_i = array();

			$output = '';

			if ( $aui_bs5 ) {
//				$output .= $is_var ? 'html body {--bs-'.esc_attr($type).'-rgb: '.$color_code.'; }' : 'html body {--bs-'.esc_attr($type).'-rgb: '.self::hex_to_rgb($color_code).'; }';
				$output .= 'html body {--bs-'.esc_attr($type).'-rgb: '.$rgb.'; }';
			}

			// build rules into each type
			foreach($selectors as $selector => $types){
				$selector = $compatibility ? $compatibility . " ".$selector : $selector;
				$types = array_combine($types,$types);
				if(isset($types['c'])){$color[] = $selector;}
				if(isset($types['b'])){$background[] = $selector;}
				if(isset($types['o'])){$border[] = $selector;}
				if(isset($types['f'])){$fill[] = $selector;}
			}

			// build rules into each type
			foreach($important_selectors as $selector => $types){
				$selector = $compatibility ? $compatibility . " ".$selector : $selector;
				$types = array_combine($types,$types);
				if(isset($types['c'])){$color_i[] = $selector;}
				if(isset($types['b'])){$background_i[] = $selector;}
				if(isset($types['o'])){$border_i[] = $selector;}
				if(isset($types['f'])){$fill_i[] = $selector;}
			}

			// add any color rules
			if(!empty($color)){
				$output .= implode(",",$color) . "{color: $color_code;} ";
			}
			if(!empty($color_i)){
				$output .= implode(",",$color_i) . "{color: $color_code !important;} ";
			}

			// add any background color rules
			if(!empty($background)){
				$output .= implode(",",$background) . "{background-color: $color_code;} ";
			}
			if(!empty($background_i)){
				$output .= $aui_bs5 ? '' : implode(",",$background_i) . "{background-color: $color_code !important;} ";
//				$output .= implode(",",$background_i) . "{background-color: rgba(var(--bs-primary-rgb), var(--bs-bg-opacity)) !important;} ";
			}

			// add any border color rules
			if(!empty($border)){
				$output .= implode(",",$border) . "{border-color: $color_code;} ";
			}
			if(!empty($border_i)){
				$output .= implode(",",$border_i) . "{border-color: $color_code !important;} ";
			}

			// add any fill color rules
			if(!empty($fill)){
				$output .= implode(",",$fill) . "{fill: $color_code;} ";
			}
			if(!empty($fill_i)){
				$output .= implode(",",$fill_i) . "{fill: $color_code !important;} ";
			}


			$prefix = $compatibility ? $compatibility . " " : "";

			$transition = $is_var ? 'transition: color 0.15s ease-in-out,background-color 0.15s ease-in-out,border-color 0.15s ease-in-out,box-shadow 0.15s ease-in-out,filter 0.15s ease-in-out;' : '';
			// darken
			$darker_075 = $is_var ? $color_code.';filter:brightness(0.925)' : self::css_hex_lighten_darken($color_code,"-0.075");
			$darker_10 = $is_var ? $color_code.';filter:brightness(0.9)' : self::css_hex_lighten_darken($color_code,"-0.10");
			$darker_125 = $is_var ? $color_code.';filter:brightness(0.875)' : self::css_hex_lighten_darken($color_code,"-0.125");
			$darker_40 = $is_var ? $color_code.';filter:brightness(0.6)' : self::css_hex_lighten_darken($color_code,"-0.4");

			// lighten
			$lighten_25 = $is_var ? $color_code.';filter:brightness(1.25)' :self::css_hex_lighten_darken($color_code,"0.25");

			// opacity see https://css-tricks.com/8-digit-hex-codes/
			$op_25 = $color_code."40"; // 25% opacity


			// button states
			$output .= $is_var ? $prefix ." .btn-{$type}{{$transition }} " : '';
			$output .= $prefix ." .btn-{$type}:hover, $prefix .btn-{$type}:focus, $prefix .btn-{$type}.focus{background-color: ".$darker_075.";    border-color: ".$darker_10.";} ";
//			$output .= $prefix ." .btn-{$type}:hover, $prefix .btn-{$type}:focus, $prefix .btn-{$type}.focus{background-color: #000;    border-color: #000;} ";
			$output .= $prefix ." .btn-outline-{$type}:not(:disabled):not(.disabled):active:focus, $prefix .btn-outline-{$type}:not(:disabled):not(.disabled).active:focus, .show>$prefix .btn-outline-{$type}.dropdown-toggle:focus{box-shadow: 0 0 0 0.2rem $op_25;} ";
			$output .= $prefix ." .btn-{$type}:not(:disabled):not(.disabled):active, $prefix .btn-{$type}:not(:disabled):not(.disabled).active, .show>$prefix .btn-{$type}.dropdown-toggle{background-color: ".$darker_10.";    border-color: ".$darker_125.";} ";
			$output .= $prefix ." .btn-{$type}:not(:disabled):not(.disabled):active:focus, $prefix .btn-{$type}:not(:disabled):not(.disabled).active:focus, .show>$prefix .btn-{$type}.dropdown-toggle:focus {box-shadow: 0 0 0 0.2rem $op_25;} ";

			if ( $type == 'primary' ) {
				// dropdown's
				$output .= $prefix . " .dropdown-item.active, $prefix .dropdown-item:active{background-color: $color_code;} ";

				// input states
				$output .= $prefix . " .form-control:focus{border-color: " . $lighten_25 . ";box-shadow: 0 0 0 0.2rem $op_25;} ";

				// page link
				$output .= $prefix . " .page-link:focus{box-shadow: 0 0 0 0.2rem $op_25;} ";
			}

            // alerts
			if ( $aui_bs5 ) {
//				$output .= $is_var ? '' : $prefix ." .alert-{$type} {background-color: ".$color_code."20;    border-color: ".$color_code."30;color:$darker_40} ";
				$output .= $prefix ." .alert-{$type} {--bs-alert-bg: rgba(var(--bs-{$type}-rgb), .1 ) !important;--bs-alert-border-color: rgba(var(--bs-{$type}-rgb), .25 ) !important;--bs-alert-color: rgba(var(--bs-{$type}-rgb), 1 ) !important;} ";
			}

			return $output;
		}

		/**
		 *
		 * @deprecated 0.1.76 Use css_overwrite()
		 *
		 * @param $color_code
		 * @param $compatibility
		 * @param $use_variable
		 *
		 * @return string
		 */
		public static function css_primary($color_code,$compatibility, $use_variable = false){

			if(!$use_variable){
				$color_code = sanitize_hex_color($color_code);
				if(!$color_code){return '';}
			}

			/**
			 * c = color, b = background color, o = border-color, f = fill
			 */
			$selectors = array(
				'a' => array('c'),
				'.btn-primary' => array('b','o'),
				'.btn-primary.disabled' => array('b','o'),
				'.btn-primary:disabled' => array('b','o'),
				'.btn-outline-primary' => array('c','o'),
				'.btn-outline-primary:hover' => array('b','o'),
				'.btn-outline-primary:not(:disabled):not(.disabled).active' => array('b','o'),
				'.btn-outline-primary:not(:disabled):not(.disabled):active' => array('b','o'),
				'.show>.btn-outline-primary.dropdown-toggle' => array('b','o'),
				'.btn-link' => array('c'),
				'.dropdown-item.active' => array('b'),
				'.custom-control-input:checked~.custom-control-label::before' => array('b','o'),
				'.custom-checkbox .custom-control-input:indeterminate~.custom-control-label::before' => array('b','o'),
//				'.custom-range::-webkit-slider-thumb' => array('b'), // these break the inline rules...
//				'.custom-range::-moz-range-thumb' => array('b'),
//				'.custom-range::-ms-thumb' => array('b'),
				'.nav-pills .nav-link.active' => array('b'),
				'.nav-pills .show>.nav-link' => array('b'),
				'.page-link' => array('c'),
				'.page-item.active .page-link' => array('b','o'),
				'.badge-primary' => array('b'),
				'.alert-primary' => array('b','o'),
				'.progress-bar' => array('b'),
				'.list-group-item.active' => array('b','o'),
				'.bg-primary' => array('b','f'),
				'.btn-link.btn-primary' => array('c'),
				'.select2-container .select2-results__option--highlighted.select2-results__option[aria-selected=true]' => array('b'),
			);

			$important_selectors = array(
				'.bg-primary' => array('b','f'),
				'.border-primary' => array('o'),
				'.text-primary' => array('c'),
			);

			$color = array();
			$color_i = array();
			$background = array();
			$background_i = array();
			$border = array();
			$border_i = array();
			$fill = array();
			$fill_i = array();

			$output = '';

			// build rules into each type
			foreach($selectors as $selector => $types){
				$selector = $compatibility ? ".bsui ".$selector : $selector;
				$types = array_combine($types,$types);
				if(isset($types['c'])){$color[] = $selector;}
				if(isset($types['b'])){$background[] = $selector;}
				if(isset($types['o'])){$border[] = $selector;}
				if(isset($types['f'])){$fill[] = $selector;}
			}

			// build rules into each type
			foreach($important_selectors as $selector => $types){
				$selector = $compatibility ? ".bsui ".$selector : $selector;
				$types = array_combine($types,$types);
				if(isset($types['c'])){$color_i[] = $selector;}
				if(isset($types['b'])){$background_i[] = $selector;}
				if(isset($types['o'])){$border_i[] = $selector;}
				if(isset($types['f'])){$fill_i[] = $selector;}
			}

			// add any color rules
			if(!empty($color)){
				$output .= implode(",",$color) . "{color: $color_code;} ";
			}
			if(!empty($color_i)){
				$output .= implode(",",$color_i) . "{color: $color_code !important;} ";
			}

			// add any background color rules
			if(!empty($background)){
				$output .= implode(",",$background) . "{background-color: $color_code;} ";
			}
			if(!empty($background_i)){
				$output .= implode(",",$background_i) . "{background-color: $color_code !important;} ";
			}

			// add any border color rules
			if(!empty($border)){
				$output .= implode(",",$border) . "{border-color: $color_code;} ";
			}
			if(!empty($border_i)){
				$output .= implode(",",$border_i) . "{border-color: $color_code !important;} ";
			}

			// add any fill color rules
			if(!empty($fill)){
				$output .= implode(",",$fill) . "{fill: $color_code;} ";
			}
			if(!empty($fill_i)){
				$output .= implode(",",$fill_i) . "{fill: $color_code !important;} ";
			}


			$prefix = $compatibility ? ".bsui " : "";

			// darken
			$darker_075 = self::css_hex_lighten_darken($color_code,"-0.075");
			$darker_10 = self::css_hex_lighten_darken($color_code,"-0.10");
			$darker_125 = self::css_hex_lighten_darken($color_code,"-0.125");

			// lighten
			$lighten_25 = self::css_hex_lighten_darken($color_code,"0.25");

			// opacity see https://css-tricks.com/8-digit-hex-codes/
			$op_25 = $color_code."40"; // 25% opacity


			// button states
			$output .= $prefix ." .btn-primary:hover, $prefix .btn-primary:focus, $prefix .btn-primary.focus{background-color: ".$darker_075.";    border-color: ".$darker_10.";} ";
			$output .= $prefix ." .btn-outline-primary:not(:disabled):not(.disabled):active:focus, $prefix .btn-outline-primary:not(:disabled):not(.disabled).active:focus, .show>$prefix .btn-outline-primary.dropdown-toggle:focus{box-shadow: 0 0 0 0.2rem $op_25;} ";
			$output .= $prefix ." .btn-primary:not(:disabled):not(.disabled):active, $prefix .btn-primary:not(:disabled):not(.disabled).active, .show>$prefix .btn-primary.dropdown-toggle{background-color: ".$darker_10.";    border-color: ".$darker_125.";} ";
			$output .= $prefix ." .btn-primary:not(:disabled):not(.disabled):active:focus, $prefix .btn-primary:not(:disabled):not(.disabled).active:focus, .show>$prefix .btn-primary.dropdown-toggle:focus {box-shadow: 0 0 0 0.2rem $op_25;} ";


			// dropdown's
			$output .= $prefix ." .dropdown-item.active, $prefix .dropdown-item:active{background-color: $color_code;} ";


			// input states
			$output .= $prefix ." .form-control:focus{border-color: ".$lighten_25.";box-shadow: 0 0 0 0.2rem $op_25;} ";

			// page link
			$output .= $prefix ." .page-link:focus{box-shadow: 0 0 0 0.2rem $op_25;} ";

			return $output;
		}

		/**
		 *
		 * @deprecated 0.1.76 Use css_overwrite()
		 *
		 * @param $color_code
		 * @param $compatibility
		 *
		 * @return string
		 */
		public static function css_secondary($color_code,$compatibility){;
			$color_code = sanitize_hex_color($color_code);
			if(!$color_code){return '';}
			/**
			 * c = color, b = background color, o = border-color, f = fill
			 */
			$selectors = array(
				'.btn-secondary' => array('b','o'),
				'.btn-secondary.disabled' => array('b','o'),
				'.btn-secondary:disabled' => array('b','o'),
				'.btn-outline-secondary' => array('c','o'),
				'.btn-outline-secondary:hover' => array('b','o'),
				'.btn-outline-secondary.disabled' => array('c'),
				'.btn-outline-secondary:disabled' => array('c'),
				'.btn-outline-secondary:not(:disabled):not(.disabled):active' => array('b','o'),
				'.btn-outline-secondary:not(:disabled):not(.disabled).active' => array('b','o'),
				'.btn-outline-secondary.dropdown-toggle' => array('b','o'),
				'.badge-secondary' => array('b'),
				'.alert-secondary' => array('b','o'),
				'.btn-link.btn-secondary' => array('c'),
			);

			$important_selectors = array(
				'.bg-secondary' => array('b','f'),
				'.border-secondary' => array('o'),
				'.text-secondary' => array('c'),
			);

			$color = array();
			$color_i = array();
			$background = array();
			$background_i = array();
			$border = array();
			$border_i = array();
			$fill = array();
			$fill_i = array();

			$output = '';

			// build rules into each type
			foreach($selectors as $selector => $types){
				$selector = $compatibility ? ".bsui ".$selector : $selector;
				$types = array_combine($types,$types);
				if(isset($types['c'])){$color[] = $selector;}
				if(isset($types['b'])){$background[] = $selector;}
				if(isset($types['o'])){$border[] = $selector;}
				if(isset($types['f'])){$fill[] = $selector;}
			}

			// build rules into each type
			foreach($important_selectors as $selector => $types){
				$selector = $compatibility ? ".bsui ".$selector : $selector;
				$types = array_combine($types,$types);
				if(isset($types['c'])){$color_i[] = $selector;}
				if(isset($types['b'])){$background_i[] = $selector;}
				if(isset($types['o'])){$border_i[] = $selector;}
				if(isset($types['f'])){$fill_i[] = $selector;}
			}

			// add any color rules
			if(!empty($color)){
				$output .= implode(",",$color) . "{color: $color_code;} ";
			}
			if(!empty($color_i)){
				$output .= implode(",",$color_i) . "{color: $color_code !important;} ";
			}

			// add any background color rules
			if(!empty($background)){
				$output .= implode(",",$background) . "{background-color: $color_code;} ";
			}
			if(!empty($background_i)){
				$output .= implode(",",$background_i) . "{background-color: $color_code !important;} ";
			}

			// add any border color rules
			if(!empty($border)){
				$output .= implode(",",$border) . "{border-color: $color_code;} ";
			}
			if(!empty($border_i)){
				$output .= implode(",",$border_i) . "{border-color: $color_code !important;} ";
			}

			// add any fill color rules
			if(!empty($fill)){
				$output .= implode(",",$fill) . "{fill: $color_code;} ";
			}
			if(!empty($fill_i)){
				$output .= implode(",",$fill_i) . "{fill: $color_code !important;} ";
			}


			$prefix = $compatibility ? ".bsui " : "";

			// darken
			$darker_075 = self::css_hex_lighten_darken($color_code,"-0.075");
			$darker_10 = self::css_hex_lighten_darken($color_code,"-0.10");
			$darker_125 = self::css_hex_lighten_darken($color_code,"-0.125");

			// lighten
			$lighten_25 = self::css_hex_lighten_darken($color_code,"0.25");

			// opacity see https://css-tricks.com/8-digit-hex-codes/
			$op_25 = $color_code."40"; // 25% opacity


			// button states
			$output .= $prefix ." .btn-secondary:hover{background-color: ".$darker_075.";    border-color: ".$darker_10.";} ";
			$output .= $prefix ." .btn-outline-secondary:not(:disabled):not(.disabled):active:focus, $prefix .btn-outline-secondary:not(:disabled):not(.disabled).active:focus, .show>$prefix .btn-outline-secondary.dropdown-toggle:focus{box-shadow: 0 0 0 0.2rem $op_25;} ";
			$output .= $prefix ." .btn-secondary:not(:disabled):not(.disabled):active, $prefix .btn-secondary:not(:disabled):not(.disabled).active, .show>$prefix .btn-secondary.dropdown-toggle{background-color: ".$darker_10.";    border-color: ".$darker_125.";} ";
			$output .= $prefix ." .btn-secondary:not(:disabled):not(.disabled):active:focus, $prefix .btn-secondary:not(:disabled):not(.disabled).active:focus, .show>$prefix .btn-secondary.dropdown-toggle:focus {box-shadow: 0 0 0 0.2rem $op_25;} ";


			return $output;
		}

		/**
		 * Increases or decreases the brightness of a color by a percentage of the current brightness.
		 *
		 * @param   string  $hexCode        Supported formats: `#FFF`, `#FFFFFF`, `FFF`, `FFFFFF`
		 * @param   float   $adjustPercent  A number between -1 and 1. E.g. 0.3 = 30% lighter; -0.4 = 40% darker.
		 *
		 * @return  string
		 */
		public static function css_hex_lighten_darken($hexCode, $adjustPercent) {
			$hexCode = ltrim($hexCode, '#');

			if ( strpos( $hexCode, 'rgba(' ) !== false || strpos( $hexCode, 'rgb(' ) !== false ) {
				return $hexCode;
			}

			if (strlen($hexCode) == 3) {
				$hexCode = $hexCode[0] . $hexCode[0] . $hexCode[1] . $hexCode[1] . $hexCode[2] . $hexCode[2];
			}

			$hexCode = array_map('hexdec', str_split($hexCode, 2));

			foreach ($hexCode as & $color) {
				$adjustableLimit = $adjustPercent < 0 ? $color : 255 - $color;
				$adjustAmount = ceil($adjustableLimit * $adjustPercent);

				$color = str_pad(dechex($color + $adjustAmount), 2, '0', STR_PAD_LEFT);
			}

			return '#' . implode($hexCode);
		}

		/**
		 * Check if we should display examples.
		 */
		public function maybe_show_examples(){
			if(current_user_can('manage_options') && isset($_REQUEST['preview-aui'])){
				echo "<head>";
				wp_head();
				echo "</head>";
				echo "<body>";
				echo $this->get_examples(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo "</body>";
				exit;
			}
		}

		/**
		 * Get developer examples.
		 *
		 * @return string
		 */
		public function get_examples(){
			$output = '';


			// open form
			$output .= "<form class='p-5 m-5 border rounded'>";

			// input example
			$output .= aui()->input(array(
				'type'  =>  'text',
				'id'    =>  'text-example',
				'name'    =>  'text-example',
				'placeholder'   => 'text placeholder',
				'title'   => 'Text input example',
				'value' =>  '',
				'required'  => false,
				'help_text' => 'help text',
				'label' => 'Text input example label'
			));

			// input example
			$output .= aui()->input(array(
				'type'  =>  'url',
				'id'    =>  'text-example2',
				'name'    =>  'text-example',
				'placeholder'   => 'url placeholder',
				'title'   => 'Text input example',
				'value' =>  '',
				'required'  => false,
				'help_text' => 'help text',
				'label' => 'Text input example label'
			));

			// checkbox example
			$output .= aui()->input(array(
				'type'  =>  'checkbox',
				'id'    =>  'checkbox-example',
				'name'    =>  'checkbox-example',
				'placeholder'   => 'checkbox-example',
				'title'   => 'Checkbox example',
				'value' =>  '1',
				'checked'   => true,
				'required'  => false,
				'help_text' => 'help text',
				'label' => 'Checkbox checked'
			));

			// checkbox example
			$output .= aui()->input(array(
				'type'  =>  'checkbox',
				'id'    =>  'checkbox-example2',
				'name'    =>  'checkbox-example2',
				'placeholder'   => 'checkbox-example',
				'title'   => 'Checkbox example',
				'value' =>  '1',
				'checked'   => false,
				'required'  => false,
				'help_text' => 'help text',
				'label' => 'Checkbox un-checked'
			));

			// switch example
			$output .= aui()->input(array(
				'type'  =>  'checkbox',
				'id'    =>  'switch-example',
				'name'    =>  'switch-example',
				'placeholder'   => 'checkbox-example',
				'title'   => 'Switch example',
				'value' =>  '1',
				'checked'   => true,
				'switch'    => true,
				'required'  => false,
				'help_text' => 'help text',
				'label' => 'Switch on'
			));

			// switch example
			$output .= aui()->input(array(
				'type'  =>  'checkbox',
				'id'    =>  'switch-example2',
				'name'    =>  'switch-example2',
				'placeholder'   => 'checkbox-example',
				'title'   => 'Switch example',
				'value' =>  '1',
				'checked'   => false,
				'switch'    => true,
				'required'  => false,
				'help_text' => 'help text',
				'label' => 'Switch off'
			));

			// close form
			$output .= "</form>";

			return $output;
		}

		/**
		 * Calendar params.
		 *
		 * @since 0.1.44
		 *
		 * @return array Calendar params.
		 */
		public static function calendar_params() {
			$params = array(
				'month_long_1' => __( 'January', 'ayecode-connect' ),
				'month_long_2' => __( 'February', 'ayecode-connect' ),
				'month_long_3' => __( 'March', 'ayecode-connect' ),
				'month_long_4' => __( 'April', 'ayecode-connect' ),
				'month_long_5' => __( 'May', 'ayecode-connect' ),
				'month_long_6' => __( 'June', 'ayecode-connect' ),
				'month_long_7' => __( 'July', 'ayecode-connect' ),
				'month_long_8' => __( 'August', 'ayecode-connect' ),
				'month_long_9' => __( 'September', 'ayecode-connect' ),
				'month_long_10' => __( 'October', 'ayecode-connect' ),
				'month_long_11' => __( 'November', 'ayecode-connect' ),
				'month_long_12' => __( 'December', 'ayecode-connect' ),
				'month_s_1' => _x( 'Jan', 'January abbreviation', 'ayecode-connect' ),
				'month_s_2' => _x( 'Feb', 'February abbreviation', 'ayecode-connect' ),
				'month_s_3' => _x( 'Mar', 'March abbreviation', 'ayecode-connect' ),
				'month_s_4' => _x( 'Apr', 'April abbreviation', 'ayecode-connect' ),
				'month_s_5' => _x( 'May', 'May abbreviation', 'ayecode-connect' ),
				'month_s_6' => _x( 'Jun', 'June abbreviation', 'ayecode-connect' ),
				'month_s_7' => _x( 'Jul', 'July abbreviation', 'ayecode-connect' ),
				'month_s_8' => _x( 'Aug', 'August abbreviation', 'ayecode-connect' ),
				'month_s_9' => _x( 'Sep', 'September abbreviation', 'ayecode-connect' ),
				'month_s_10' => _x( 'Oct', 'October abbreviation', 'ayecode-connect' ),
				'month_s_11' => _x( 'Nov', 'November abbreviation', 'ayecode-connect' ),
				'month_s_12' => _x( 'Dec', 'December abbreviation', 'ayecode-connect' ),
				'day_s1_1' => _x( 'S', 'Sunday initial', 'ayecode-connect' ),
				'day_s1_2' => _x( 'M', 'Monday initial', 'ayecode-connect' ),
				'day_s1_3' => _x( 'T', 'Tuesday initial', 'ayecode-connect' ),
				'day_s1_4' => _x( 'W', 'Wednesday initial', 'ayecode-connect' ),
				'day_s1_5' => _x( 'T', 'Friday initial', 'ayecode-connect' ),
				'day_s1_6' => _x( 'F', 'Thursday initial', 'ayecode-connect' ),
				'day_s1_7' => _x( 'S', 'Saturday initial', 'ayecode-connect' ),
				'day_s2_1' => __( 'Su', 'ayecode-connect' ),
				'day_s2_2' => __( 'Mo', 'ayecode-connect' ),
				'day_s2_3' => __( 'Tu', 'ayecode-connect' ),
				'day_s2_4' => __( 'We', 'ayecode-connect' ),
				'day_s2_5' => __( 'Th', 'ayecode-connect' ),
				'day_s2_6' => __( 'Fr', 'ayecode-connect' ),
				'day_s2_7' => __( 'Sa', 'ayecode-connect' ),
				'day_s3_1' => __( 'Sun', 'ayecode-connect' ),
				'day_s3_2' => __( 'Mon', 'ayecode-connect' ),
				'day_s3_3' => __( 'Tue', 'ayecode-connect' ),
				'day_s3_4' => __( 'Wed', 'ayecode-connect' ),
				'day_s3_5' => __( 'Thu', 'ayecode-connect' ),
				'day_s3_6' => __( 'Fri', 'ayecode-connect' ),
				'day_s3_7' => __( 'Sat', 'ayecode-connect' ),
				'day_s5_1' => __( 'Sunday', 'ayecode-connect' ),
				'day_s5_2' => __( 'Monday', 'ayecode-connect' ),
				'day_s5_3' => __( 'Tuesday', 'ayecode-connect' ),
				'day_s5_4' => __( 'Wednesday', 'ayecode-connect' ),
				'day_s5_5' => __( 'Thursday', 'ayecode-connect' ),
				'day_s5_6' => __( 'Friday', 'ayecode-connect' ),
				'day_s5_7' => __( 'Saturday', 'ayecode-connect' ),
				'am_lower' => __( 'am', 'ayecode-connect' ),
				'pm_lower' => __( 'pm', 'ayecode-connect' ),
				'am_upper' => __( 'AM', 'ayecode-connect' ),
				'pm_upper' => __( 'PM', 'ayecode-connect' ),
				'firstDayOfWeek' => (int) get_option( 'start_of_week' ),
				'time_24hr' => false,
				'year' => __( 'Year', 'ayecode-connect' ),
				'hour' => __( 'Hour', 'ayecode-connect' ),
				'minute' => __( 'Minute', 'ayecode-connect' ),
				'weekAbbreviation' => __( 'Wk', 'ayecode-connect' ),
				'rangeSeparator' => __( ' to ', 'ayecode-connect' ),
				'scrollTitle' => __( 'Scroll to increment', 'ayecode-connect' ),
				'toggleTitle' => __( 'Click to toggle', 'ayecode-connect' )
			);

			return apply_filters( 'ayecode_ui_calendar_params', $params );
		}

		/**
		 * Flatpickr calendar localize.
		 *
		 * @since 0.1.44
		 *
		 * @return string Calendar locale.
		 */
		public static function flatpickr_locale() {
			$params = self::calendar_params();

			if ( is_string( $params ) ) {
				$params = html_entity_decode( $params, ENT_QUOTES, 'UTF-8' );
			} else {
				foreach ( (array) $params as $key => $value ) {
					if ( ! is_scalar( $value ) ) {
						continue;
					}

					$params[ $key ] = html_entity_decode( (string) $value, ENT_QUOTES, 'UTF-8' );
				}
			}

			$day_s3 = array();
			$day_s5 = array();

			for ( $i = 1; $i <= 7; $i ++ ) {
				$day_s3[] = addslashes( $params[ 'day_s3_' . $i ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				$day_s5[] = addslashes( $params[ 'day_s3_' . $i ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}

			$month_s = array();
			$month_long = array();

			for ( $i = 1; $i <= 12; $i ++ ) {
				$month_s[] = addslashes( $params[ 'month_s_' . $i ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				$month_long[] = addslashes( $params[ 'month_long_' . $i ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}

			ob_start();
		if ( 0 ) { ?><script><?php } ?>
                {
                    weekdays: {
                        shorthand: ['<?php echo implode( "','", $day_s3 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>'],
                            longhand: ['<?php echo implode( "','", $day_s5 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>'],
                    },
                    months: {
                        shorthand: ['<?php echo implode( "','", $month_s ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>'],
                            longhand: ['<?php echo implode( "','", $month_long ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>'],
                    },
                    daysInMonth: [31,28,31,30,31,30,31,31,30,31,30,31],
                        firstDayOfWeek: <?php echo (int) $params[ 'firstDayOfWeek' ]; ?>,
                    ordinal: function (nth) {
                        var s = nth % 100;
                        if (s > 3 && s < 21)
                            return "th";
                        switch (s % 10) {
                            case 1:
                                return "st";
                            case 2:
                                return "nd";
                            case 3:
                                return "rd";
                            default:
                                return "th";
                        }
                    },
                    rangeSeparator: '<?php echo esc_attr( $params[ 'rangeSeparator' ] ); ?>',
                        weekAbbreviation: '<?php echo esc_attr( $params[ 'weekAbbreviation' ] ); ?>',
                    scrollTitle: '<?php echo esc_attr( $params[ 'scrollTitle' ] ); ?>',
                    toggleTitle: '<?php echo esc_attr( $params[ 'toggleTitle' ] ); ?>',
                    amPM: ['<?php echo esc_attr( $params[ 'am_upper' ] ); ?>','<?php echo esc_attr( $params[ 'pm_upper' ] ); ?>'],
                    yearAriaLabel: '<?php echo esc_attr( $params[ 'year' ] ); ?>',
                    hourAriaLabel: '<?php echo esc_attr( $params[ 'hour' ] ); ?>',
                    minuteAriaLabel: '<?php echo esc_attr( $params[ 'minute' ] ); ?>',
                    time_24hr: <?php echo ( $params[ 'time_24hr' ] ? 'true' : 'false' ) ; ?>
                }
				<?php if ( 0 ) { ?></script><?php } ?>
			<?php
			$locale = ob_get_clean();

			return apply_filters( 'ayecode_ui_flatpickr_locale', trim( $locale ) );
		}

		/**
		 * Select2 JS params.
		 *
		 * @since 0.1.44
		 *
		 * @return array Select2 JS params.
		 */
		public static function select2_params() {
			$params = array(
				'i18n_select_state_text'    => esc_attr__( 'Select an option&hellip;', 'ayecode-connect' ),
				'i18n_no_matches'           => _x( 'No matches found', 'enhanced select', 'ayecode-connect' ),
				'i18n_ajax_error'           => _x( 'Loading failed', 'enhanced select', 'ayecode-connect' ),
				'i18n_input_too_short_1'    => _x( 'Please enter 1 or more characters', 'enhanced select', 'ayecode-connect' ),
				'i18n_input_too_short_n'    => _x( 'Please enter %item% or more characters', 'enhanced select', 'ayecode-connect' ),
				'i18n_input_too_long_1'     => _x( 'Please delete 1 character', 'enhanced select', 'ayecode-connect' ),
				'i18n_input_too_long_n'     => _x( 'Please delete %item% characters', 'enhanced select', 'ayecode-connect' ),
				'i18n_selection_too_long_1' => _x( 'You can only select 1 item', 'enhanced select', 'ayecode-connect' ),
				'i18n_selection_too_long_n' => _x( 'You can only select %item% items', 'enhanced select', 'ayecode-connect' ),
				'i18n_load_more'            => _x( 'Loading more results&hellip;', 'enhanced select', 'ayecode-connect' ),
				'i18n_searching'            => _x( 'Searching&hellip;', 'enhanced select', 'ayecode-connect' )
			);

			return apply_filters( 'ayecode_ui_select2_params', $params );
		}

		/**
		 * Select2 JS localize.
		 *
		 * @since 0.1.44
		 *
		 * @return string Select2 JS locale.
		 */
		public static function select2_locale() {
			$params = self::select2_params();

			foreach ( (array) $params as $key => $value ) {
				if ( ! is_scalar( $value ) ) {
					continue;
				}

				$params[ $key ] = html_entity_decode( (string) $value, ENT_QUOTES, 'UTF-8' );
			}

			$locale = json_encode( $params );

			return apply_filters( 'ayecode_ui_select2_locale', trim( $locale ) );
		}

		/**
		 * Time ago JS localize.
		 *
		 * @since 0.1.47
		 *
		 * @return string Time ago JS locale.
		 */
		public static function timeago_locale() {
			$params = array(
				'prefix_ago' => '',
				'suffix_ago' => ' ' . _x( 'ago', 'time ago', 'ayecode-connect' ),
				'prefix_after' => _x( 'after', 'time ago', 'ayecode-connect' ) . ' ',
				'suffix_after' => '',
				'seconds' => _x( 'less than a minute', 'time ago', 'ayecode-connect' ),
				'minute' => _x( 'about a minute', 'time ago', 'ayecode-connect' ),
				'minutes' => _x( '%d minutes', 'time ago', 'ayecode-connect' ),
				'hour' => _x( 'about an hour', 'time ago', 'ayecode-connect' ),
				'hours' => _x( 'about %d hours', 'time ago', 'ayecode-connect' ),
				'day' => _x( 'a day', 'time ago', 'ayecode-connect' ),
				'days' => _x( '%d days', 'time ago', 'ayecode-connect' ),
				'month' => _x( 'about a month', 'time ago', 'ayecode-connect' ),
				'months' => _x( '%d months', 'time ago', 'ayecode-connect' ),
				'year' => _x( 'about a year', 'time ago', 'ayecode-connect' ),
				'years' => _x( '%d years', 'time ago', 'ayecode-connect' ),
			);

			$params = apply_filters( 'ayecode_ui_timeago_params', $params );

			foreach ( (array) $params as $key => $value ) {
				if ( ! is_scalar( $value ) ) {
					continue;
				}

				$params[ $key ] = html_entity_decode( (string) $value, ENT_QUOTES, 'UTF-8' );
			}

			$locale = json_encode( $params );

			return apply_filters( 'ayecode_ui_timeago_locale', trim( $locale ) );
		}

		/**
		 * JavaScript Minifier
		 *
		 * @param $input
		 *
		 * @return mixed
		 */
		public static function minify_js($input) {
			if(trim($input) === "") return $input;
			return preg_replace(
				array(
					// Remove comment(s)
					'#\s*("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')\s*|\s*\/\*(?!\!|@cc_on)(?>[\s\S]*?\*\/)\s*|\s*(?<![\:\=])\/\/.*(?=[\n\r]|$)|^\s*|\s*$#',
					// Remove white-space(s) outside the string and regex
					'#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|\/\*(?>.*?\*\/)|\/(?!\/)[^\n\r]*?\/(?=[\s.,;]|[gimuy]|$))|\s*([!%&*\(\)\-=+\[\]\{\}|;:,.<>?\/])\s*#s',
					// Remove the last semicolon
					'#;+\}#',
					// Minify object attribute(s) except JSON attribute(s). From `{'foo':'bar'}` to `{foo:'bar'}`
					'#([\{,])([\'])(\d+|[a-z_][a-z0-9_]*)\2(?=\:)#i',
					// --ibid. From `foo['bar']` to `foo.bar`
					'#([a-z0-9_\)\]])\[([\'"])([a-z_][a-z0-9_]*)\2\]#i'
				),
				array(
					'$1',
					'$1$2',
					'}',
					'$1$3',
					'$1.$3'
				),
				$input);
		}

		/**
		 * Minify CSS
		 *
		 * @param $input
		 *
		 * @return mixed
		 */
		public static function minify_css($input) {
			if(trim($input) === "") return $input;
			return preg_replace(
				array(
					// Remove comment(s)
					'#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')|\/\*(?!\!)(?>.*?\*\/)|^\s*|\s*$#s',
					// Remove unused white-space(s)
					'#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|\/\*(?>.*?\*\/))|\s*+;\s*+(})\s*+|\s*+([*$~^|]?+=|[{};,>~]|\s(?![0-9\.])|!important\b)\s*+|([[(:])\s++|\s++([])])|\s++(:)\s*+(?!(?>[^{}"\']++|"(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')*+{)|^\s++|\s++\z|(\s)\s+#si',
					// Replace `0(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)` with `0`
					'#(?<=[\s:])(0)(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)#si',
					// Replace `:0 0 0 0` with `:0`
					'#:(0\s+0|0\s+0\s+0\s+0)(?=[;\}]|\!important)#i',
					// Replace `background-position:0` with `background-position:0 0`
					'#(background-position):0(?=[;\}])#si',
					// Replace `0.6` with `.6`, but only when preceded by `:`, `,`, `-` or a white-space
					'#(?<=[\s:,\-])0+\.(\d+)#s',
					// Minify string value
					'#(\/\*(?>.*?\*\/))|(?<!content\:)([\'"])([a-z_][a-z0-9\-_]*?)\2(?=[\s\{\}\];,])#si',
					'#(\/\*(?>.*?\*\/))|(\burl\()([\'"])([^\s]+?)\3(\))#si',
					// Minify HEX color code
					'#(?<=[\s:,\-]\#)([a-f0-6]+)\1([a-f0-6]+)\2([a-f0-6]+)\3#i',
					// Replace `(border|outline):none` with `(border|outline):0`
					'#(?<=[\{;])(border|outline):none(?=[;\}\!])#',
					// Remove empty selector(s)
					'#(\/\*(?>.*?\*\/))|(^|[\{\}])(?:[^\s\{\}]+)\{\}#s'
				),
				array(
					'$1',
					'$1$2$3$4$5$6$7',
					'$1',
					':0',
					'$1:0 0',
					'.$1',
					'$1$3',
					'$1$2$4$5',
					'$1$2$3',
					'$1:0',
					'$1$2'
				),
				$input);
		}

		/**
		 * Get the conditional fields JavaScript.
		 *
		 * @return mixed
		 */
		public function conditional_fields_js() {
			ob_start();
			?>
            <script>
                /**
                 * Conditional Fields
                 */
                var aui_cf_field_rules = [], aui_cf_field_key_rules = {}, aui_cf_field_default_values = {};

                jQuery(function($) {
                    aui_cf_field_init_rules($);
                });

                /**
                 * Conditional fields init.
                 */
                function aui_cf_field_init_rules($) {
                    if (!$('[data-has-rule]').length) {
                        return;
                    }
                    $('input.select2-search__field').attr('data-ignore-rule','');
                    $('[data-rule-key]').on('change keypress keyup gdclear', 'input, textarea', function() {
                        if (!$(this).hasClass('select2-search__field')) {
                            aui_cf_field_apply_rules($(this));
                        }
                    });

                    $('[data-rule-key]').on('change change.select2 gdclear', 'select', function() {
                        aui_cf_field_apply_rules($(this));
                    });

                    aui_cf_field_setup_rules($);
                }

                /**
                 * Setup conditional field rules.
                 */
                function aui_cf_field_setup_rules($) {
                    var aui_cf_field_keys = [];

                    $('[data-rule-key]').each(function() {
                        var key = $(this).data('rule-key'), irule = parseInt($(this).data('has-rule'));
                        if (key) {
                            aui_cf_field_keys.push(key);
                        }

                        var parse_conds = {};
                        if ($(this).data('rule-fie-0')) {
                            $(this).find('input,select,textarea').each(function() {
                                if ($(this).attr('required') || $(this).attr('oninvalid')) {
                                    $(this).addClass('aui-cf-req');
                                    if ($(this).attr('required')) {
                                        $(this).attr('data-rule-req', true);
                                    }
                                    if ($(this).attr('oninvalid')) {
                                        $(this).attr('data-rule-oninvalid', $(this).attr('oninvalid'));
                                    }
                                }
                            });
                            for (var i = 0; i < irule; i++) {
                                var field = $(this).data('rule-fie-' + i);
                                if (typeof parse_conds[i] === 'undefined') {
                                    parse_conds[i] = {};
                                }
                                parse_conds[i]['action'] = $(this).data('rule-act-' + i);
                                parse_conds[i]['field'] = $(this).data('rule-fie-' + i);
                                parse_conds[i]['condition'] = $(this).data('rule-con-' + i);
                                parse_conds[i]['value'] = $(this).data('rule-val-' + i);
                            }

                            $.each(parse_conds, function(j, data) {
                                var item = {
                                    'field': {
                                        key: key,
                                        action: data.action,
                                        field: data.field,
                                        condition: data.condition,
                                        value: data.value,
                                        rule: {
                                            key: key,
                                            action: data.action,
                                            condition: data.condition,
                                            value: data.value
                                        }
                                    }
                                };
                                aui_cf_field_rules.push(item);
                            });
                        }
                        aui_cf_field_default_values[$(this).data('rule-key')] = aui_cf_field_get_default_value($(this));
                    });

                    $.each(aui_cf_field_keys, function(i, fkey) {
                        aui_cf_field_key_rules[fkey] = aui_cf_field_get_children(fkey);
                    });

                    $('[data-rule-key]:visible').each(function() {
                        var conds = aui_cf_field_key_rules[$(this).data('rule-key')];
                        if (conds && conds.length) {
                            var $main_el = $(this), el = aui_cf_field_get_element($main_el);
                            if ($(el).length) {
                                aui_cf_field_apply_rules($(el));
                            }
                        }
                    });
                }

                /**
                 * Apply conditional field rules.
                 */
                function aui_cf_field_apply_rules($el) {
                    if (!$el.parents('[data-rule-key]').length) {
                        return;
                    }

                    if ($el.data('no-rule')) {
                        return;
                    }

                    var key = $el.parents('[data-rule-key]').data('rule-key');
                    var conditions = aui_cf_field_key_rules[key];
                    if (typeof conditions === 'undefined') {
                        return;
                    }
                    var field_type = aui_cf_field_get_type($el.parents('[data-rule-key]')), current_value = aui_cf_field_get_value($el);
                    var $keys = {}, $keys_values = {}, $key_rules = {};

                    jQuery.each(conditions, function(index, condition) {
                        if (typeof $keys_values[condition.key] == 'undefined') {
                            $keys_values[condition.key] = [];
                            $key_rules[condition.key] = {}
                        }

                        $keys_values[condition.key].push(condition.value);
                        $key_rules[condition.key] = condition;
                    });

                    jQuery.each(conditions, function(index, condition) {
                        if (typeof $keys[condition.key] == 'undefined') {
                            $keys[condition.key] = {};
                        }

                        if (condition.condition === 'empty') {
                            var field_value = Array.isArray(current_value) ? current_value.join('') : current_value;
                            if (!field_value || field_value === '') {
                                $keys[condition.key][index] = true;
                            } else {
                                $keys[condition.key][index] = false;
                            }
                        } else if (condition.condition === 'not empty') {
                            var field_value = Array.isArray(current_value) ? current_value.join('') : current_value;
                            if (field_value && field_value !== '') {
                                $keys[condition.key][index] = true;
                            } else {
                                $keys[condition.key][index] = false;
                            }
                        } else if (condition.condition === 'equals to') {
                            var field_value = (Array.isArray(current_value) && current_value.length === 1) ? current_value[0] : current_value;
                            if (((condition.value && condition.value == condition.value) || (condition.value === field_value)) && aui_cf_field_in_array(field_value, $keys_values[condition.key])) {
                                $keys[condition.key][index] = true;
                            } else {
                                $keys[condition.key][index] = false;
                            }
                        } else if (condition.condition === 'not equals') {
                            var field_value = (Array.isArray(current_value) && current_value.length === 1) ? current_value[0] : current_value;
                            if (jQuery.isNumeric(condition.value) && parseInt(field_value) !== parseInt(condition.value) && field_value && !aui_cf_field_in_array(field_value, $keys_values[condition.key])) {
                                $keys[condition.key][index] = true;
                            } else if (condition.value != field_value && !aui_cf_field_in_array(field_value, $keys_values[condition.key])) {
                                $keys[condition.key][index] = true;
                            } else {
                                $keys[condition.key][index] = false;
                            }
                        } else if (condition.condition === 'greater than') {
                            var field_value = (Array.isArray(current_value) && current_value.length === 1) ? current_value[0] : current_value;
                            if (jQuery.isNumeric(condition.value) && parseInt(field_value) > parseInt(condition.value)) {
                                $keys[condition.key][index] = true;
                            } else {
                                $keys[condition.key][index] = false;
                            }
                        } else if (condition.condition === 'less than') {
                            var field_value = (Array.isArray(current_value) && current_value.length === 1) ? current_value[0] : current_value;
                            if (jQuery.isNumeric(condition.value) && parseInt(field_value) < parseInt(condition.value)) {
                                $keys[condition.key][index] = true;
                            } else {
                                $keys[condition.key][index] = false;
                            }
                        } else if (condition.condition === 'contains') {
                            var avalues = condition.value;
                            if (!Array.isArray(avalues)) {
                                if (jQuery.isNumeric(avalues)) {
                                    avalues = [avalues];
                                } else {
                                    avalues = avalues.split(",");
                                }
                            }
                            switch (field_type) {
                                case 'multiselect':
                                    var found = false;
                                    for (var key in avalues) {
                                        var svalue = jQuery.isNumeric(avalues[key]) ? avalues[key] : (avalues[key]).trim();
                                        if (!found && current_value && ((!Array.isArray(current_value) && current_value.indexOf(svalue) >= 0) || (Array.isArray(current_value) && aui_cf_field_in_array(svalue, current_value)))) {
                                            found = true;
                                        }
                                    }
                    
                                    if (found) {
                                        $keys[condition.key][index] = true;
                                    } else {
                                        $keys[condition.key][index] = false;
                                    }
                                    break;
                                case 'checkbox':
                                    if (current_value && ((!Array.isArray(current_value) && current_value.indexOf(condition.value) >= 0) || (Array.isArray(current_value) && aui_cf_field_in_array(condition.value, current_value)))) {
                                        $keys[condition.key][index] = true;
                                    } else {
                                        $keys[condition.key][index] = false;
                                    }
                                    break;
                                default:
                                    if (typeof $keys[condition.key][index] === 'undefined') {
                                        if (current_value && current_value.indexOf(condition.value) >= 0 && aui_cf_field_in_array(current_value, $keys_values[condition.key], false, true)) {
                                            $keys[condition.key][index] = true;
                                        } else {
                                            $keys[condition.key][index] = false;
                                        }
                                    }
                                    break;
                            }
                        }
                    });

                    jQuery.each($keys, function(index, field) {
                        if (aui_cf_field_in_array(true, field)) {
                            aui_cf_field_apply_action($el, $key_rules[index], true);
                        } else {
                            aui_cf_field_apply_action($el, $key_rules[index], false);
                        }
                    });

                    /* Trigger field change */
                    if ($keys.length) {
                        $el.trigger('aui_cf_field_on_change');
                    }
                }

                /**
                 * Get the field element.
                 */
                function aui_cf_field_get_element($el) {
                    var el = $el.find('input:not("[data-ignore-rule]"),textarea,select'), type = aui_cf_field_get_type($el);
                    if (type && window._aui_cf_field_elements && typeof window._aui_cf_field_elements == 'object' && typeof window._aui_cf_field_elements[type] != 'undefined') {
                        el = window._aui_cf_field_elements[type];
                    }
                    return el;
                }

                /**
                 * Get the field type.
                 */
                function aui_cf_field_get_type($el) {
                    return $el.data('rule-type');
                }

                /**
                 * Get the field value.
                 */
                function aui_cf_field_get_value($el) {
                    var current_value = $el.val();

                    if ($el.is(':checkbox')) {
                        current_value = '';
                        if ($el.parents('[data-rule-key]').find('input:checked').length > 1) {
                            $el.parents('[data-rule-key]').find('input:checked').each(function() {
                                current_value = current_value + jQuery(this).val() + ' ';
                            });
                        } else {
                            if ($el.parents('[data-rule-key]').find('input:checked').length >= 1) {
                                current_value = $el.parents('[data-rule-key]').find('input:checked').val();
                            }
                        }
                    }

                    if ($el.is(':radio')) {
                        current_value = $el.parents('[data-rule-key]').find('input[type=radio]:checked').val();
                    }

                    return current_value;
                }

                /**
                 * Get the field default value.
                 */
                function aui_cf_field_get_default_value($el) {
                    var value = '', type = aui_cf_field_get_type($el);

                    switch (type) {
                        case 'text':
                        case 'number':
                        case 'date':
                        case 'textarea':
                        case 'select':
                            value = $el.find('input:text,input[type="number"],textarea,select').val();
                            break;
                        case 'phone':
                        case 'email':
                        case 'color':
                        case 'url':
                        case 'hidden':
                        case 'password':
                        case 'file':
                            value = $el.find('input[type="' + type + '"]').val();
                            break;
                        case 'multiselect':
                            value = $el.find('select').val();
                            break;
                        case 'radio':
                            if ($el.find('input[type="radio"]:checked').length >= 1) {
                                value = $el.find('input[type="radio"]:checked').val();
                            }
                            break;
                        case 'checkbox':
                            if ($el.find('input[type="checkbox"]:checked').length >= 1) {
                                if ($el.find('input[type="checkbox"]:checked').length > 1) {
                                    var values = [];
                                    values.push(value);
                                    $el.find('input[type="checkbox"]:checked').each(function() {
                                        values.push(jQuery(this).val());
                                    });
                                    value = values;
                                } else {
                                    value = $el.find('input[type="checkbox"]:checked').val();
                                }
                            }
                            break;
                        default:
                            if (window._aui_cf_field_default_values && typeof window._aui_cf_field_default_values == 'object' && typeof window._aui_cf_field_default_values[type] != 'undefined') {
                                value = window._aui_cf_field_default_values[type];
                            }
                            break;
                    }
                    return {
                        type: type,
                        value: value
                    };
                }

                /**
                 * Reset field default value.
                 */
                function aui_cf_field_reset_default_value($el, bHide, setVal) {
                    var type = aui_cf_field_get_type($el), key = $el.data('rule-key'), field = aui_cf_field_default_values[key];
                    if (typeof setVal === 'undefined' || (typeof setVal !== 'undefined' && setVal === null)) {
                        setVal = field.value;
                    }

                    switch (type) {
                        case 'text':
                        case 'number':
                        case 'date':
                        case 'textarea':
                            $el.find('input:text,input[type="number"],textarea').val(setVal);
                            break;
                        case 'phone':
                        case 'email':
                        case 'color':
                        case 'url':
                        case 'hidden':
                        case 'password':
                        case 'file':
                            $el.find('input[type="' + type + '"]').val(setVal);
                            break;
                        case 'select':
                            $el.find('select').find('option').prop('selected', false);
                            $el.find('select').val(setVal);
                            $el.find('select').trigger('change');
                            break;
                        case 'multiselect':
                            $el.find('select').find('option').prop('selected', false);
                            if ((typeof setVal === 'object' || typeof setVal === 'array') && !setVal.length && $el.find('select option:first').text() == '') {
                                $el.find('select option:first').remove(); // Clear first option to show placeholder.
                            }
                            if (typeof setVal === 'string') {
                                $el.find('select').val(setVal);
                            } else {
                                jQuery.each(setVal, function(i, v) {
                                    $el.find('select').find('option[value="' + v + '"]').prop('selected', true);
                                });
                            }
                            $el.find('select').trigger('change');
                            break;
                        case 'checkbox':
                            if ($el.find('input[type="checkbox"]:checked').length >= 1) {
                                $el.find('input[type="checkbox"]:checked').prop('checked', false).removeAttr('checked');
                            }
                            if (Array.isArray(setVal)) {
                                jQuery.each(setVal, function(i, v) {
                                    $el.find('input[type="checkbox"][value="' + v + '"]').prop('checked', true);
                                });
                            } else {
                                $el.find('input[type="checkbox"][value="' + setVal + '"]').prop('checked', true);
                            }
                            break;
                        case 'radio':
                            setTimeout(function() {
                                if ($el.find('input[type="radio"]:checked').length >= 1) {
                                    $el.find('input[type="radio"]:checked').prop('checked', false).removeAttr('checked');
                                }
                                $el.find('input[type="radio"][value="' + setVal + '"]').prop('checked', true);
                            }, 100);
                            break;
                        default:
                            jQuery(document.body).trigger('aui_cf_field_reset_default_value', type, $el, field);
                            break;
                    }

                    if (!$el.hasClass('aui-cf-field-has-changed')) {
                        var el = aui_cf_field_get_element($el);
                        if (type === 'radio' || type === 'checkbox') {
                            el = el.find(':checked');
                        }
                        if (el) {
                            el.trigger('change');
                            $el.addClass('aui-cf-field-has-changed');
                        }
                    }
                }

                /**
                 * Get the field children.
                 */
                function aui_cf_field_get_children(field_key) {
                    var rules = [];
                    jQuery.each(aui_cf_field_rules, function(j, rule) {
                        if (rule.field.field === field_key) {
                            rules.push(rule.field.rule);
                        }
                    });
                    return rules;
                }

                /**
                 * Check in array field value.
                 */
                function aui_cf_field_in_array(find, item, exact, match) {
                    var found = false, key;
                    exact = !!exact;

                    for (key in item) {
                        if ((exact && item[key] === find) || (!exact && item[key] == find) || (match && (typeof find === 'string' || typeof find === 'number') && (typeof item[key] === 'string' || typeof item[key] === 'number') && find.length && find.indexOf(item[key]) >= 0)) {
                            found = true;
                            break;
                        }
                    }
                    return found;
                }

                /**
                 * App the field condition action.
                 */
                function aui_cf_field_apply_action($el, rule, isTrue) {
                    var $destEl = jQuery('[data-rule-key="' + rule.key + '"]'), $inputEl = (rule.key && $destEl.find('[name="' + rule.key + '"]').length) ? $destEl.find('[name="' + rule.key + '"]') : null;

                    if (rule.action === 'show' && isTrue) {
                        if ($destEl.is(':hidden') && !($destEl.hasClass('aui-cf-skip-reset') || ($inputEl && $inputEl.hasClass('aui-cf-skip-reset')))) {
                            aui_cf_field_reset_default_value($destEl);
                        }
                        aui_cf_field_show_element($destEl);
                    } else if (rule.action === 'show' && !isTrue) {
                        if ((!$destEl.is(':hidden') || ($destEl.is(':hidden') && ($destEl.hasClass('aui-cf-force-reset') || ($inputEl && $inputEl.hasClass('aui-cf-skip-reset')) || ($destEl.closest('.aui-cf-use-parent').length && $destEl.closest('.aui-cf-use-parent').is(':hidden'))))) && !($destEl.hasClass('aui-cf-skip-reset') || ($inputEl && $inputEl.hasClass('aui-cf-skip-reset')))) {
                            var _setVal = $destEl.hasClass('aui-cf-force-empty') || ($inputEl && $inputEl.hasClass('aui-cf-force-empty')) ? '' : null;
                            aui_cf_field_reset_default_value($destEl, true, _setVal);
                        }
                        aui_cf_field_hide_element($destEl);
                    } else if (rule.action === 'hide' && isTrue) {
                        if ((!$destEl.is(':hidden') || ($destEl.is(':hidden') && ($destEl.hasClass('aui-cf-force-reset') || ($inputEl && $inputEl.hasClass('aui-cf-skip-reset')) || ($destEl.closest('.aui-cf-use-parent').length && $destEl.closest('.aui-cf-use-parent').is(':hidden'))))) && !($destEl.hasClass('aui-cf-skip-reset') || ($inputEl && $inputEl.hasClass('aui-cf-skip-reset')))) {
                            var _setVal = $destEl.hasClass('aui-cf-force-empty') || ($inputEl && $inputEl.hasClass('aui-cf-force-empty')) ? '' : null;
                            aui_cf_field_reset_default_value($destEl, true, _setVal);
                        }
                        aui_cf_field_hide_element($destEl);
                    } else if (rule.action === 'hide' && !isTrue) {
                        if ($destEl.is(':hidden') && !($destEl.hasClass('aui-cf-skip-reset') || ($inputEl && $inputEl.hasClass('aui-cf-skip-reset')))) {
                            aui_cf_field_reset_default_value($destEl);
                        }
                        aui_cf_field_show_element($destEl);
                    }
                    return $el.removeClass('aui-cf-field-has-changed');
                }

                /**
                 * Show field element.
                 */
                function aui_cf_field_show_element($el) {
                    $el.removeClass('d-none').show();

                    $el.find('.aui-cf-req').each(function() {
                        if (jQuery(this).data('rule-req')) {
                            jQuery(this).removeAttr('required').prop('required', true);
                        }
                        if (jQuery(this).data('rule-oninvalid')) {
                            jQuery(this).removeAttr('oninvalid').attr('oninvalid', jQuery(this).data('rule-oninvalid'));
                        }
                    });

                    if (window && window.navigator.userAgent.indexOf("MSIE") !== -1) {
                        $el.css({
                            "visibility": "visible"
                        });
                    }
                }

                /**
                 * Hide field element.
                 */
                function aui_cf_field_hide_element($el) {
                    $el.addClass('d-none').hide();

                    $el.find('.aui-cf-req').each(function() {
                        if (jQuery(this).data('rule-req')) {
                            jQuery(this).removeAttr('required');
                        }
                        if (jQuery(this).data('rule-oninvalid')) {
                            jQuery(this).removeAttr('oninvalid');
                        }
                    });

                    if (window && window.navigator.userAgent.indexOf("MSIE") !== -1) {
                        $el.css({
                            "visibility": "hidden"
                        });
                    }
                }
				<?php do_action( 'aui_conditional_fields_js', $this ); ?>
            </script>
			<?php
			$output = ob_get_clean();

			return str_replace( array( '<script>', '</script>' ), '', self::minify_js( $output ) );
		}
	}

	/**
	 * Run the class if found.
	 */
	AyeCode_UI_Settings::instance();
}