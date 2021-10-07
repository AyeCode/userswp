<?php
/**
 * Contains the main class.
 *
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

// Ensure the class is only loaded once.
if ( ! class_exists( 'WP_Super_Duper' ) ) {

	/**
	 *
	 * A Class to be able to create a Widget, Shortcode or Block to be able to output content for WordPress.
	 *
	 * View hello-world.php for example usage
	 *
	 * @since 1.0.0
	 * @since 1.0.16 change log moved to file change-log.txt - CHANGED
	 * @since 2.0.0 shortcode, widget and blocks moved into separate files - CHANGED
	 * @version 2.0.0
	 */
	abstract class WP_Super_Duper {
	
		public $version = "2.0.1";
		public $font_awesome_icon_version = "5.11.2";
		public $block_code;
		public $options;
		public $base_id;
		public $number;
		public $settings_hash;
		public $arguments = array();
		public $instance = array();
	
		// prevent SDv1 errors if register_widget() function used
		public $id_base;
	
		/**
		 * @var array Contains an array of output types instances.
		 */
		public $output_types = array();
	
		/**
		 * The relative url to the current folder.
		 *
		 * @var string
		 */
		public $url = '';
	
		/**
		 * Take the array options and use them to build.
		 */
		public function __construct( $options ) {
			global $sd_widgets;
	
			$sd_widgets[ $options['base_id'] ] = array(
				'name'       => $options['name'],
				'class_name' => $options['class_name']
			);
			$this->base_id   = $options['base_id'];
	
			// Lets filter the options before we do anything.
			$options = apply_filters( 'wp_super_duper_options', $options, $this );
			$options = apply_filters( "wp_super_duper_options_{$this->base_id}", $options, $this );
			$options = $this->add_name_from_key( $options );
	
			// Set args.
			$this->options   = $options;
			$this->base_id   = $options['base_id'];
			$this->arguments = isset( $options['arguments'] ) ? $options['arguments'] : array();
	
			// Load output types.
			$this->load_output_types();
	
			// add generator text to admin head
			add_action( 'admin_head', array( $this, 'generator' ) );
	
			add_action( 'admin_init', array( __CLASS__, 'load_widgets_setting' ) );
	
			add_action( 'wp_ajax_super_duper_get_picker', array( __CLASS__, 'get_picker' ) );
	
			do_action( 'wp_super_duper_widget_init', $options, $this );
	
		}
	
		/**
		 * Set the name from the argument key.
		 *
		 * @param array $options
		 * @param bool $arguments
		 *
		 * @return mixed
		 */
		protected function add_name_from_key( $options, $arguments = false ) {
			if ( ! empty( $options['arguments'] ) ) {
				foreach ( $options['arguments'] as $key => $val ) {
					$options['arguments'][ $key ]['name'] = $key;
				}
			} elseif ( $arguments && is_array( $options ) && ! empty( $options ) ) {
				foreach ( $options as $key => $val ) {
					$options[ $key ]['name'] = $key;
				}
			}
	
			return $options;
		}
	
		/**
		 * Output the version in the admin header.
		 */
		public function load_output_types() {
	
			$allowed_types = $this->get_output_types();
			$output_types  = array( 'block', 'shortcode', 'widget' );
	
			// Check if this is being overidden by the widget.
			$args = $this->get_arguments();
			if ( isset( $args['output_types'] ) && is_array( $args['output_types'] ) ) {
				$output_types  = $args['output_types'] ;
			}
	
			if ( isset( $this->options['output_types'] ) && is_array( $this->options['output_types'] ) ) {
				$output_types  = $this->options['output_types'] ;
			}
	
			// Load each output type.
			foreach ( $output_types as $output_type ) {
	
				// Ensure this is an allowed type.
				if ( ! isset( $allowed_types[ $output_type ] ) ) {
					continue;
				}
	
				// If the class does not exist, try loading it.
				if ( ! class_exists( $allowed_types[ $output_type ] ) ) {
	
					if ( file_exists( plugin_dir_path( __FILE__ ) . "type/$output_type.php" ) ) {
						require_once( plugin_dir_path( __FILE__ ) . "type/$output_type.php" );
					} else {
						continue;
					}
	
				}
	
				$output_class                       = $allowed_types[ $output_type ];
				$this->output_types[ $output_type ] = new $output_class( $this );
			}
	
		}
	
		/**
		 * Retrieves an array of available SD types.
		 *
		 * @return array
		 */
		protected function get_output_types() {
	
			// Output type id and class.
			$types = array(
				'block'     => 'WP_Super_Duper_Block',
				'shortcode' => 'WP_Super_Duper_Shortcode',
				'widget'    => 'WP_Super_Duper_Widget',
			);
	
			// Maybe disable widgets.
			$disable_widget   = get_option( 'sd_load_widgets', 'auto' );
	
			if ( 'auto' === $disable_widget ) {
				if ( !$this->widgets_required() ) {
					unset( $types['widget'] );
				}
			}
	
			if ( 'no' === $disable_widget ) {
				unset( $types['widget'] );
			}
	
			return apply_filters( 'super_duper_types', $types, $this );
		}
	
		/**
		 * Check if we are required to load widgets.
		 *
		 * @return mixed|void
		 */
		protected function widgets_required(){
			global $wp_version;
	
			$required = false;
	
	
			// check wp version
			if( version_compare( $wp_version, '5.8', '<' ) ){
				$required = true;
			}
	
			// Page builders that require widgets
			if(
			!$required && (
			defined( 'ELEMENTOR_VERSION' ) // elementor
			|| class_exists( 'Fusion_Element' ) // Fusion Builder (avada)
			|| class_exists( 'SiteOrigin_Panels' ) // SiteOrigin Page builder
			|| defined( 'WPB_VC_VERSION' ) // WPBakery page builder
			|| defined( 'CT_VERSION' ) // Oxygen Builder
			|| defined( 'FL_BUILDER_VERSION' ) // Beaver Builder
			|| defined( 'FL_THEME_BUILDER_VERSION' ) // Beaver Themer
			)
			){
				$required = true;
			}
	
			// Theme has active widgets
			if( !$required && !empty( $this->has_active_widgets() )  ){
				$required = true;
			}
	
	
			return apply_filters( 'sd_widgets_required' , $required );
		}
	
		/**
		 * Check if the current site has any active old style widgets.
		 *
		 * @return bool
		 */
		protected function has_active_widgets(){
			global $sd_has_active_widgets;
	
			// have we already done this?
			if(!is_null($sd_has_active_widgets)){
				return $sd_has_active_widgets;
			}
	
			$result = false;
			$sidebars_widgets = get_option('sidebars_widgets');
	
			if(is_array($sidebars_widgets)){
	
				foreach ($sidebars_widgets as $key => $value) {
	
	
	
					if( $key != 'wp_inactive_widgets' ) {
	
						if(!empty($value) && is_array($value)){
							foreach($value as $widget){
								if($widget && substr( $widget, 0, 6 ) !== "block-"){
									$result = true;
								}
							}
						}
	
					}
				}
			}
	
			$sd_has_active_widgets = $result;
	
			return $result;
		}
	
		/**
		 * Get arguments in super duper.
		 *
		 * @since 1.0.0
		 *
		 * @return array Get arguments.
		 */
		public function get_arguments() {
			if ( empty( $this->arguments ) ) {
				$this->arguments = $this->set_arguments();
			}
	
			$this->arguments = apply_filters( 'wp_super_duper_arguments', $this->arguments, $this->options, $this->instance );
			$this->arguments = $this->add_name_from_key( $this->arguments, true );
	
			return $this->arguments;
		}
	
		/**
		 * Set arguments in super duper.
		 *
		 * @since 1.0.0
		 *
		 * @return array Set arguments.
		 */
		public function set_arguments() {
			return $this->arguments;
		}
	
		/**
		 * Makes SD work with the siteOrigin page builder.
		 *
		 * @since 1.0.6
		 * @return mixed
		 */
		public static function siteorigin_js() {
			ob_start();
			?>
			<script>
				/**
				 * Check a form to see what items should be shown or hidden.
				 */
				function sd_so_show_hide(form) {
					jQuery(form).find(".sd-argument").each(function () {
	
						var $element_require = jQuery(this).data('element_require');
	
						if ($element_require) {
	
							$element_require = $element_require.replace("&#039;", "'"); // replace single quotes
							$element_require = $element_require.replace("&quot;", '"'); // replace double quotes
	
							if (eval($element_require)) {
								jQuery(this).removeClass('sd-require-hide');
							} else {
								jQuery(this).addClass('sd-require-hide');
							}
						}
					});
				}
	
				/**
				 * Toggle advanced settings visibility.
				 */
				function sd_so_toggle_advanced($this) {
					var form = jQuery($this).parents('form,.form,.so-content');
					form.find('.sd-advanced-setting').toggleClass('sd-adv-show');
					return false;// prevent form submit
				}
	
				/**
				 * Initialise a individual widget.
				 */
				function sd_so_init_widget($this, $selector) {
					if (!$selector) {
						$selector = 'form';
					}
					// only run once.
					if (jQuery($this).data('sd-widget-enabled')) {
						return;
					} else {
						jQuery($this).data('sd-widget-enabled', true);
					}
	
					var $button = '<button title="<?php _e( 'Advanced Settings' );?>" class="button button-primary right sd-advanced-button" onclick="sd_so_toggle_advanced(this);return false;"><i class="fas fa-sliders-h" aria-hidden="true"></i></button>';
					var form = jQuery($this).parents('' + $selector + '');
	
					if (jQuery($this).val() == '1' && jQuery(form).find('.sd-advanced-button').length == 0) {
						jQuery(form).append($button);
					}
	
					// show hide on form change
					jQuery(form).on("change", function () {
						sd_so_show_hide(form);
					});
	
					// show hide on load
					sd_so_show_hide(form);
				}
	
				jQuery(function () {
					jQuery(document).on('open_dialog', function (w, e) {
						setTimeout(function () {
							if (jQuery('.so-panels-dialog-wrapper:visible .so-content.panel-dialog .sd-show-advanced').length) {
								console.log('exists');
								if (jQuery('.so-panels-dialog-wrapper:visible .so-content.panel-dialog .sd-show-advanced').val() == '1') {
									console.log('true');
									sd_so_init_widget('.so-panels-dialog-wrapper:visible .so-content.panel-dialog .sd-show-advanced', 'div');
								}
							}
						}, 200);
					});
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
		 * A function to ge the shortcode builder picker html.
		 *
		 * @param string $editor_id
		 *
		 * @return string
		 */
		public static function get_picker( $editor_id = '' ) {
	
			ob_start();
			if ( isset( $_POST['editor_id'] ) ) {
				$editor_id = esc_attr( $_POST['editor_id'] );
			} elseif ( isset( $_REQUEST['et_fb'] ) ) {
				$editor_id = 'main_content_content_vb_tiny_mce';
			}
	
			global $sd_widgets;
			?>
	
			<div class="sd-shortcode-left-wrap">
				<?php
				ksort( $sd_widgets );
				//				print_r($sd_widgets);exit;
				if ( ! empty( $sd_widgets ) ) {
					echo '<select class="widefat" onchange="sd_get_shortcode_options(this);">';
					echo "<option>" . __( 'Select shortcode' ) . "</option>";
					foreach ( $sd_widgets as $shortcode => $class ) {
						echo "<option value='" . esc_attr( $shortcode ) . "'>" . esc_attr( $shortcode ) . " (" . esc_attr( $class['name'] ) . ")</option>";
					}
					echo "</select>";
	
				}
				?>
				<div class="sd-shortcode-settings"></div>
	
			</div>
	
			<div class="sd-shortcode-right-wrap">
				<textarea id='sd-shortcode-output' disabled></textarea>
				<div id='sd-shortcode-output-actions'>
					<?php if ( $editor_id != '' ) { ?>
						<button class="button sd-insert-shortcode-button"
								onclick="sd_insert_shortcode(<?php if ( ! empty( $editor_id ) ) {
									echo "'" . $editor_id . "'";
								} ?>)"><?php _e( 'Insert shortcode' ); ?></button>
					<?php } ?>
					<button class="button"
							onclick="sd_copy_to_clipboard()"><?php _e( 'Copy shortcode' ); ?></button>
				</div>
			</div>
			<?php
	
			$html = ob_get_clean();
	
			if ( wp_doing_ajax() ) {
				echo $html;
				$should_die = true;
	
				// some builder get the editor via ajax so we should not die on those occasions
				$dont_die = array(
					'parent_tag',// WP Bakery
					'avia_request' // enfold
				);
	
				foreach ( $dont_die as $request ) {
					if ( isset( $_REQUEST[ $request ] ) ) {
						$should_die = false;
					}
				}
	
				if ( $should_die ) {
					wp_die();
				}
	
			} else {
				return $html;
			}
	
			return '';
	
		}
	
		/**
		 * Returns the JS used to render the widget/shortcode settings form.
		 *
		 * @return string
		 */
		public static function widget_js() {
			ob_start();
			?>
			<script>
	
				/**
				 * Toggle advanced settings visibility.
				 */
				function sd_toggle_advanced($this) {
					var form = jQuery($this).parents('form,.form');
						form.find('.sd-advanced-setting').toggleClass('sd-adv-show');
						return false;// prevent form submit
				}
	
				/**
				 * Check a form to see what items should be shown or hidden.
				 */
				function sd_show_hide(form) {
					console.log('show/hide');
					jQuery(form).find(".sd-argument").each(function () {
	
						var $element_require = jQuery(this).data('element_require');
	
						if ($element_require) {
	
							$element_require = $element_require.replace("&#039;", "'"); // replace single quotes
							$element_require = $element_require.replace("&quot;", '"'); // replace double quotes
	
							if (eval($element_require)) {
								jQuery(this).removeClass('sd-require-hide');
							} else {
								jQuery(this).addClass('sd-require-hide');
							}
						}
					});
				}
	
				/**
				 * Initialise widgets from the widgets screen.
				 */
				function sd_init_widgets($selector) {
					jQuery(".sd-show-advanced").each(function (index) {
						sd_init_widget(this, $selector);
					});
				}
	
				/**
				 * Initialise a individual widget.
				 */
				function sd_init_widget($this, $selector) {
					console.log($selector);
	
					if (!$selector) {
						$selector = 'form';
					}
					// only run once.
					if (jQuery($this).data('sd-widget-enabled')) {
						return;
					} else {
						jQuery($this).data('sd-widget-enabled', true);
					}
	
					var $button = '<button title="<?php _e( 'Advanced Settings' );?>" style="line-height: 28px;" class="button button-primary right sd-advanced-button" onclick="sd_toggle_advanced(this);return false;"><span class="dashicons dashicons-admin-settings" style="width: 28px;font-size: 28px;"></span></button>';
					var form = jQuery($this).parents('' + $selector + '');
	
					if (jQuery($this).val() == '1' && jQuery(form).find('.sd-advanced-button').length == 0) {
						console.log('add advanced button');
						if(jQuery(form).find('.widget-control-save').length > 0){
							jQuery(form).find('.widget-control-save').after($button);
						}else{
							jQuery(form).find('.sd-show-advanced').after($button);
						}
					} else {
						console.log('no advanced button');
						console.log(jQuery($this).val());
						console.log(jQuery(form).find('.sd-advanced-button').length);
					}
	
					// show hide on form change
					jQuery(form).on("change", function () {
						sd_show_hide(form);
					});
	
					// show hide on load
					sd_show_hide(form);
					}
	
					/**
					 * Init a customizer widget.
					 */
					function sd_init_customizer_widget(section) {
						if (section.expanded) {
							section.expanded.bind(function (isExpanding) {
								if (isExpanding) {
									// is it a SD widget?
									if (jQuery(section.container).find('.sd-show-advanced').length) {
										// init the widget
										sd_init_widget(jQuery(section.container).find('.sd-show-advanced'), ".form");
									}
								}
							});
						}
					}
	
					/**
					 * If on widgets screen.
					 */
					jQuery(function () {
						// if not in customizer.
						if (!wp.customize) {
							sd_init_widgets("form");
						}
	
						// init on widget added
						jQuery(document).on('widget-added', function (e, widget) {
							console.log('widget added');
							// is it a SD widget?
							if (jQuery(widget).find('.sd-show-advanced').length) {
								// init the widget
								sd_init_widget(jQuery(widget).find('.sd-show-advanced'), "form");
							}
						});
	
						// init on widget updated
						jQuery(document).on('widget-updated', function (e, widget) {
							console.log('widget updated');
	
							// is it a SD widget?
							if (jQuery(widget).find('.sd-show-advanced').length) {
								// init the widget
								sd_init_widget(jQuery(widget).find('.sd-show-advanced'), "form");
							}
						});
	
					});
	
	
					/**
					 * We need to run this before jQuery is ready
					 */
					if (wp.customize) {
						wp.customize.bind('ready', function () {
	
							// init widgets on load
							wp.customize.control.each(function (section) {
								sd_init_customizer_widget(section);
							});
	
							// init widgets on add
							wp.customize.control.bind('add', function (section) {
								sd_init_customizer_widget(section);
							});
	
						});
	
					}
					<?php do_action( 'wp_super_duper_widget_js' ); ?>
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
		 * Returns the CSS used to render the widget/shortcode settings form.
		 *
		 * @param bool $advanced If we should include advanced CSS.
		 *
		 * @return mixed
		 */
		public static function widget_css( $advanced = true ) {
			ob_start();
			?>
	
			<style>
				<?php if ( $advanced ) : ?>
					.sd-advanced-setting {
						display: none;
					}
	
					.sd-advanced-setting.sd-adv-show {
						display: block;
					}
	
					.sd-argument.sd-require-hide,
					.sd-advanced-setting.sd-require-hide {
						display: none;
					}
	
					button.sd-advanced-button {
						margin-right: 3px !important;
						font-size: 20px !important;
					}
	
				<?php endif; ?>
	
				button.sd-toggle-group-button {
					background-color: #f3f3f3;
					color: #23282d;
					cursor: pointer;
					padding: 10px;
					width: 100%;
					border: none;
					text-align: left;
					outline: none;
					font-size: 13px;
					font-weight: bold;
					margin-bottom: 1px;
				}
	
			</style>
	
			<?php
				$output = ob_get_clean();
	
				/*
				* We only add the <script> tags for code highlighting, so we strip them from the output.
				*/
	
				return str_replace( array(
					'<style>',
					'</style>'
				), '', $output );
		}
	
		/**
		 * Registers the widgets loading settings.
		 */
		public static function load_widgets_setting() {
			register_setting( 'general', 'sd_load_widgets', 'esc_attr' );
	
			add_settings_field(
				'sd_load_widgets',
				'<label for="sd_load_widgets">' . __( 'Load Super Duper Widgets' ) . '</label>',
				'WP_Super_Duper::load_widgets_setting_html',
				'general'
			);
	
		}
	
		/**
		 * Displays the widgets settings HTML.
		 */
		public static function load_widgets_setting_html() {
			$available_options = array(
				'yes'  => __( 'Yes' ),
				'no'   => __( 'No' ),
				'auto' => __( 'Auto' ),
			);
			$selected_option   = get_option( 'sd_load_widgets', 'auto' );
	
			?>
				<select name="sd_load_widgets" id="sd_load_widgets">
					<?php foreach ( $available_options as $key => $label ) : ?>
						<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $selected_option ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
				<p class="description"><?php _e( 'This option allows you to disable Super Duper widgets and instead only load the blocks and shortcodes.' ); ?></p>
			<?php
		}
	
		/**
		 * prevent SDv1 errors if register_widget() function used
		 */
		public function _register(){
			// backwards compatibility
		}
	
		/**
		 * Output the version in the admin header.
		 */
		public function generator() {
	
			// We want to set this once.
			if ( empty( $GLOBALS['SD_SET_GENERATOR'] ) ) {
				echo '<meta name="generator" content="WP Super Duper v' . $this->version . '" />';
				$GLOBALS['SD_SET_GENERATOR'] = 1;
			}
	
		}
	
		/**
		 * This is the main output class for all 3 items, widget, shortcode and block, it is extended in the calling class.
		 *
		 * @param array $args
		 * @param array $widget_args
		 * @param string $content
		 */
		public function output( $args = array(), $widget_args = array(), $content = '' ) {
			echo call_user_func( $this->options['widget_ops']['output'], $args, $widget_args, $content );
		}
	
		/**
		 * Placeholder text to show if output is empty and we are on a preview/builder page.
		 *
		 * @param string $name
		 *
		 * @return string
		 */
		public function preview_placeholder_text( $name = '' ) {
			return "<div style='background:#0185ba33;padding: 10px;border: 4px #ccc dashed;'>" . sprintf( __( 'Placeholder for: %s' ), $name ) . "</div>";
		}
	
		/**
		 * Sometimes booleans values can be turned to strings, so we fix that.
		 *
		 * @param $options
		 *
		 * @return mixed
		 */
		public function string_to_bool( $options ) {
			// convert bool strings to booleans
			foreach ( $options as $key => $val ) {
				if ( $val == 'false' ) {
					$options[ $key ] = false;
				} elseif ( $val == 'true' ) {
					$options[ $key ] = true;
				}
			}
	
			return $options;
		}
	
		/**
		 * Get the argument values that are also filterable.
		 *
		 * @param $instance
		 *
		 * @since 1.0.12 Don't set checkbox default value if the value is empty.
		 *
		 * @return array
		 */
		public function argument_values( $instance ) {
			$argument_values = array();
	
			// set widget instance
			$this->instance = $instance;
	
			if ( empty( $this->arguments ) ) {
				$this->arguments = $this->get_arguments();
			}
	
			if ( ! empty( $this->arguments ) ) {
				foreach ( $this->arguments as $key => $args ) {
					// set the input name from the key
					$args['name'] = $key;
					//
					$argument_values[ $key ] = isset( $instance[ $key ] ) ? $instance[ $key ] : '';
					if ( $args['type'] == 'checkbox' && $argument_values[ $key ] == '' ) {
						// don't set default for an empty checkbox
					} elseif ( $argument_values[ $key ] == '' && isset( $args['default'] ) ) {
						$argument_values[ $key ] = $args['default'];
					}
				}
			}
	
			return $argument_values;
		}
	
		/**
		 * Get the url path to the current folder.
		 *
		 * @return string
		 */
		public function get_url() {
			$url = $this->url;
	
			if ( ! $url ) {
				// check if we are inside a plugin
				$file_dir = str_replace( "/includes", "", dirname( __FILE__ ) );
	
				$dir_parts = explode( "/wp-content/", $file_dir );
				$url_parts = explode( "/wp-content/", plugins_url() );
	
				if ( ! empty( $url_parts[0] ) && ! empty( $dir_parts[1] ) ) {
					$url       = trailingslashit( $url_parts[0] . "/wp-content/" . $dir_parts[1] );
					$this->url = $url;
				}
			}
	
			return $url;
		}
	
		/**
		 * General function to check if we are in a preview situation.
		 *
		 * @since 1.0.6
		 * @return bool
		 */
		public function is_preview() {
			return $this->is_divi_preview() || $this->is_elementor_preview() || $this->is_beaver_preview() || $this->is_siteorigin_preview() || $this->is_cornerstone_preview() || $this->is_fusion_preview() || $this->is_oxygen_preview() || $this->is_block_content_call();
		}
	
		/**
		 * Tests if the current output is inside a Divi preview.
		 *
		 * @since 1.0.6
		 * @return bool
		 */
		public function is_divi_preview() {
			$result = false;
			if ( isset( $_REQUEST['et_fb'] ) || isset( $_REQUEST['et_pb_preview'] ) || ( is_admin() && isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'elementor' ) ) {
				$result = true;
			}
	
			return $result;
		}
	
		/**
		 * Tests if the current output is inside a elementor preview.
		 *
		 * @since 1.0.4
		 * @return bool
		 */
		public function is_elementor_preview() {
			$result = false;
			if ( isset( $_REQUEST['elementor-preview'] ) || ( is_admin() && isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'elementor' ) || ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'elementor_ajax' ) ) {
				$result = true;
			}
	
			return $result;
		}
	
		/**
		 * Tests if the current output is inside a Beaver builder preview.
		 *
		 * @since 1.0.6
		 * @return bool
		 */
		public function is_beaver_preview() {
			$result = false;
			if ( isset( $_REQUEST['fl_builder'] ) ) {
				$result = true;
			}
	
			return $result;
		}
	
		/**
		 * Tests if the current output is inside a siteorigin builder preview.
		 *
		 * @since 1.0.6
		 * @return bool
		 */
		public function is_siteorigin_preview() {
			$result = false;
			if ( ! empty( $_REQUEST['siteorigin_panels_live_editor'] ) ) {
				$result = true;
			}
	
			return $result;
		}
	
		/**
		 * Tests if the current output is inside a cornerstone builder preview.
		 *
		 * @since 1.0.8
		 * @return bool
		 */
		public function is_cornerstone_preview() {
			$result = false;
			if ( ! empty( $_REQUEST['cornerstone_preview'] ) || basename( $_SERVER['REQUEST_URI'] ) == 'cornerstone-endpoint' ) {
				$result = true;
			}
	
			return $result;
		}
	
		/**
		 * Tests if the current output is inside a fusion builder preview.
		 *
		 * @since 1.1.0
		 * @return bool
		 */
		public function is_fusion_preview() {
			$result = false;
			if ( ! empty( $_REQUEST['fb-edit'] ) || ! empty( $_REQUEST['fusion_load_nonce'] ) ) {
				$result = true;
			}
	
			return $result;
		}
	
		/**
		 * Tests if the current output is inside a Oxygen builder preview.
		 *
		 * @since 1.0.18
		 * @return bool
		 */
		public function is_oxygen_preview() {
			$result = false;
			if ( ! empty( $_REQUEST['ct_builder'] ) || ( ! empty( $_REQUEST['action'] ) && ( substr( $_REQUEST['action'], 0, 11 ) === "oxy_render_" || substr( $_REQUEST['action'], 0, 10 ) === "ct_render_" ) ) ) {
				$result = true;
			}
	
			return $result;
		}
	
		/**
		* Checks if the current call is a ajax call to get the block content.
		*
		* This can be used in your widget to return different content as the block content.
		*
		* @since 1.0.3
		* @return bool
		*/
		public function is_block_content_call() {
			$result = false;
			if ( wp_doing_ajax() && isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'super_duper_output_shortcode' ) {
				$result = true;
			}
	
			return $result;
		}
	
		/**
		 * Outputs the options form inputs for the widget/shortcode.
		 *
		 * @param array $instance The widget options.
		 */
		public function form( $instance ) {
	
			// Set widget instance.
			$this->instance = $instance;
	
			// Set it as a SD widget.
			echo $this->widget_advanced_toggle();
	
			// Display description.
			printf( '<p>%s</p>', esc_html( $this->options['widget_ops']['description'] ) );
	
			// Prepare arguments.
			$arguments_raw = $this->get_arguments();
	
		if ( is_array( $arguments_raw ) ) {
	
			$arguments = $this->group_arguments( $arguments_raw );
	
			// Do we have sections?
			if ( $arguments != $arguments_raw ) {
	
					$panel_count = 0;
					foreach ( $arguments as $key => $args ) {
	
						$hide       = $panel_count ? ' style="display:none;" ' : '';
						$icon_class = $panel_count ? 'fas fa-chevron-up' : 'fas fa-chevron-down';
						echo "<button onclick='jQuery(this).find(\"i\").toggleClass(\"fas fa-chevron-up fas fa-chevron-down\");jQuery(this).next().slideToggle();' type='button' class='sd-toggle-group-button sd-input-group-toggle" . sanitize_title_with_dashes( $key ) . "'>" . esc_attr( $key ) . " <i style='float:right;' class='" . esc_attr( $icon_class ) . "'></i></button>";
						echo "<div class='sd-toggle-group sd-input-group-" . sanitize_title_with_dashes( $key ) . "' $hide>";
	
						foreach ( $args as $k => $a ) {
	
							$this->widget_inputs_row_start($k, $a);
							$this->widget_inputs( $a, $instance );
							$this->widget_inputs_row_end($k, $a);
	
						}
	
						echo "</div>";
	
						$panel_count ++;
	
					}
	
				} else {
	
					foreach ( $arguments as $key => $args ) {
						$this->widget_inputs_row_start($key, $args);
						$this->widget_inputs( $args, $instance );
						$this->widget_inputs_row_end($key, $args);
					}
	
				}
	
			}
		}
	
		/**
		 * Get the hidden input that when added makes the advanced button show on widget settings.
		 *
		 * @return string
		 */
		public function widget_advanced_toggle() {
	
			return sprintf(
				'<input type="hidden"  class="sd-show-advanced" value="%s" />',
				(int) $this->block_show_advanced()
			);
	
		}
	
		/**
		 * Check if we need to show advanced options.
		 *
		 * @return bool
		 */
		public function block_show_advanced() {
			$show      = false;
			$arguments = $this->get_arguments();
	
			if ( ! empty( $arguments ) ) {
				foreach ( $arguments as $argument ) {
					if ( isset( $argument['advanced'] ) && $argument['advanced'] ) {
						$show = true;
						break; // no need to continue if we know we have it
					}
				}
			}
	
			return $show;
		}
	
		/**
		 * Groups widget arguments.
		 *
		 * @param array $arguments
		 *
		 * @return array
		 */
		public function group_arguments( $arguments ) {
	
			if ( ! empty( $arguments ) ) {
				$temp_arguments = array();
				$general        = __( "General" );
				$add_sections   = false;
				foreach ( $arguments as $key => $args ) {
					if ( isset( $args['group'] ) ) {
						$temp_arguments[ $args['group'] ][ $key ] = $args;
						$add_sections                             = true;
					} else {
						$temp_arguments[ $general ][ $key ] = $args;
					}
				}
	
				// only add sections if more than one
				if ( $add_sections ) {
					$arguments = $temp_arguments;
				}
			}
	
			return $arguments;
		}
	
		public function widget_inputs_row_start($key, $args){
			if(!empty($args['row'])){
				// maybe open
				if(!empty($args['row']['open'])){
					?>
					<div class='bsui sd-argument ' data-argument='<?php echo esc_attr( $args['row']['key'] ); ?>' data-element_require='<?php if ( !empty($args['row']['element_require'])) {
						echo $this->convert_element_require( $args['row']['element_require'] );
					} ?>'>
					<?php if(!empty($args['row']['title'])){ ?>
					<label class="mb-0 "><?php echo esc_attr( $args['row']['title'] ); ?><?php echo $this->widget_field_desc( $args['row'] ); ?></label>
					<?php }?>
					<div class='row <?php if(!empty($args['row']['class'])){ echo esc_attr($args['row']['class']);} ?>'>
					<div class='col pr-2'>
					<?php
				}elseif(!empty($args['row']['close'])){
					echo "<div class='col pl-0'>";
				}else{
					echo "<div class='col pl-0 pr-2'>";
				}
			}
		}
	
		/**
		 * Convert require element.
		 *
		 * @since 1.0.0
		 *
		 * @param string $input Input element.
		 *
		 * @return string $output
		 */
		public function convert_element_require( $input ) {
	
			$input = str_replace( "'", '"', $input );// we only want double quotes
	
			$output = esc_attr( str_replace( array( "[%", "%]" ), array(
				"jQuery(form).find('[data-argument=\"",
				"\"]').find('input,select,textarea').val()"
			), $input ) );
	
			return $output;
		}
	
		/**
		 * Get the widget input description html.
		 *
		 * @param $args
		 *
		 * @return string
		 * @todo, need to make its own tooltip script
		 */
		public function widget_field_desc( $args ) {
	
			$description = '';
			if ( isset( $args['desc'] ) && $args['desc'] ) {
				if ( isset( $args['desc_tip'] ) && $args['desc_tip'] ) {
					$description = $this->desc_tip( $args['desc'] );
				} else {
					$description = '<span class="description">' . wp_kses_post( $args['desc'] ) . '</span>';
				}
			}
	
			return $description;
		}
	
		/**
		 * Get the tool tip html.
		 *
		 * @param $tip
		 * @param bool $allow_html
		 *
		 * @return string
		 */
		public function desc_tip( $tip, $allow_html = false ) {
			if ( $allow_html ) {
				$tip = $this->sanitize_tooltip( $tip );
			} else {
				$tip = esc_attr( $tip );
			}
	
			return '<span class="gd-help-tip dashicons dashicons-editor-help" title="' . $tip . '"></span>';
		}
	
		/**
		 * Sanitize a string destined to be a tooltip.
		 *
		 * @param string $var
		 *
		 * @return string
		 */
		public function sanitize_tooltip( $var ) {
			return htmlspecialchars( wp_kses( html_entity_decode( $var ), array(
				'br'     => array(),
				'em'     => array(),
				'strong' => array(),
				'small'  => array(),
				'span'   => array(),
				'ul'     => array(),
				'li'     => array(),
				'ol'     => array(),
				'p'      => array(),
			) ) );
		}
	
		/**
		 * Builds the inputs for the widget options.
		 *
		 * @param $args
		 * @param $instance
		 */
		public function widget_inputs( $args, $instance ) {
	
			$class             = "";
			$element_require   = "";
			$custom_attributes = "";
	
			// get value
			if ( isset( $instance[ $args['name'] ] ) ) {
				$value = $instance[ $args['name'] ];
			} elseif ( ! isset( $instance[ $args['name'] ] ) && ! empty( $args['default'] ) ) {
				$value = is_array( $args['default'] ) ? array_map( "esc_html", $args['default'] ) : esc_html( $args['default'] );
			} else {
				$value = '';
			}
	
			// get placeholder
			if ( ! empty( $args['placeholder'] ) ) {
				$placeholder = "placeholder='" . esc_html( $args['placeholder'] ) . "'";
			} else {
				$placeholder = '';
			}
	
			// get if advanced
			if ( isset( $args['advanced'] ) && $args['advanced'] ) {
				$class .= " sd-advanced-setting ";
			}
	
			// element_require
			if ( isset( $args['element_require'] ) && $args['element_require'] ) {
				$element_require = $args['element_require'];
			}
	
			// custom_attributes
			if ( isset( $args['custom_attributes'] ) && $args['custom_attributes'] ) {
				$custom_attributes = $this->array_to_attributes( $args['custom_attributes'], true );
			}
	
	
			// before wrapper
			?>
			<p class="sd-argument <?php echo esc_attr( $class ); ?>"
			data-argument='<?php echo esc_attr( $args['name'] ); ?>'
			data-element_require='<?php if ( $element_require ) {
				echo $this->convert_element_require( $element_require );
			} ?>'
			>
			<?php
	
	
			switch ( $args['type'] ) {
				//array('text','password','number','email','tel','url','color')
				case "text":
				case "password":
				case "number":
				case "email":
				case "tel":
				case "url":
				case "color":
					?>
					<label
						for="<?php echo esc_attr( $this->get_field_id( $args['name'] ) ); ?>"><?php echo $this->widget_field_title( $args );?><?php echo $this->widget_field_desc( $args ); ?></label>
					<input <?php echo $placeholder; ?> class="widefat"
						<?php echo $custom_attributes; ?>
													   id="<?php echo esc_attr( $this->get_field_id( $args['name'] ) ); ?>"
													   name="<?php echo esc_attr( $this->get_field_name( $args['name'] ) ); ?>"
													   type="<?php echo esc_attr( $args['type'] ); ?>"
													   value="<?php echo esc_attr( $value ); ?>">
					<?php
	
					break;
				case "select":
					$multiple = isset( $args['multiple'] ) && $args['multiple'] ? true : false;
					if ( $multiple ) {
						if ( empty( $value ) ) {
							$value = array();
						}
					}
					?>
					<label
						for="<?php echo esc_attr( $this->get_field_id( $args['name'] ) ); ?>"><?php echo $this->widget_field_title( $args ); ?><?php echo $this->widget_field_desc( $args ); ?></label>
					<select <?php echo $placeholder; ?> class="widefat"
						<?php echo $custom_attributes; ?>
														id="<?php echo esc_attr( $this->get_field_id( $args['name'] ) ); ?>"
														name="<?php echo esc_attr( $this->get_field_name( $args['name'] ) );
														if ( $multiple ) {
															echo "[]";
														} ?>"
						<?php if ( $multiple ) {
							echo "multiple";
						} //@todo not implemented yet due to gutenberg not supporting it
						?>
					>
						<?php
	
						if ( ! empty( $args['options'] ) ) {
							foreach ( $args['options'] as $val => $label ) {
								if ( $multiple ) {
									$selected = in_array( $val, $value ) ? 'selected="selected"' : '';
								} else {
									$selected = selected( $value, $val, false );
								}
								echo "<option value='$val' " . $selected . ">$label</option>";
							}
						}
						?>
					</select>
					<?php
					break;
				case "checkbox":
					?>
					<input <?php echo $placeholder; ?>
						<?php checked( 1, $value, true ) ?>
						<?php echo $custom_attributes; ?>
						class="widefat" id="<?php echo esc_attr( $this->get_field_id( $args['name'] ) ); ?>"
						name="<?php echo esc_attr( $this->get_field_name( $args['name'] ) ); ?>" type="checkbox"
						value="1">
					<label
						for="<?php echo esc_attr( $this->get_field_id( $args['name'] ) ); ?>"><?php echo $this->widget_field_title( $args );?><?php echo $this->widget_field_desc( $args ); ?></label>
					<?php
					break;
				case "textarea":
					?>
					<label
						for="<?php echo esc_attr( $this->get_field_id( $args['name'] ) ); ?>"><?php echo $this->widget_field_title( $args ); ?><?php echo $this->widget_field_desc( $args ); ?></label>
					<textarea <?php echo $placeholder; ?> class="widefat"
						<?php echo $custom_attributes; ?>
														  id="<?php echo esc_attr( $this->get_field_id( $args['name'] ) ); ?>"
														  name="<?php echo esc_attr( $this->get_field_name( $args['name'] ) ); ?>"
					><?php echo esc_attr( $value ); ?></textarea>
					<?php
	
					break;
				case "hidden":
					?>
					<input id="<?php echo esc_attr( $this->get_field_id( $args['name'] ) ); ?>"
						   name="<?php echo esc_attr( $this->get_field_name( $args['name'] ) ); ?>" type="hidden"
						   value="<?php echo esc_attr( $value ); ?>">
					<?php
					break;
				default:
					echo "No input type found!"; // @todo we need to add more input types.
			}
	
			// after wrapper
			?>
			</p>
			<?php
		}
	
		/**
		 * Convert an array of attributes to JS object or HTML attributes string.
		 *
		 * @todo there is prob a faster way to do this, also we could add some validation here.
		 *
		 * @param $attributes
		 *
		 * @return string
		 */
		public function array_to_attributes( $attributes, $html = false ) {
	
			if ( ! is_array( $attributes ) ) {
				return '';
			}
	
			$output = '';
			foreach ( $attributes as $name => $value ) {
	
				if ( $html ) {
	
					if ( true === $value ) {
						$output .= esc_html( $name ) . ' ';
					} else if ( false !== $value ) {
						$output .= sprintf( '%s="%s" ', esc_html( $name ), trim( esc_attr( $value ) ) );
					}
	
				} else {
					$output .= sprintf( "'%s': '%s',", esc_js( $name ), is_bool( $value ) ? $value : trim( esc_js( $value ) ) );
				}
	
			}
	
			return $output;
		}
	
		/**
		 * Constructs id attributes for use in WP_Widget::form() fields.
		 *
		 * This function should be used in form() methods to create id attributes
		 * for fields to be saved by WP_Widget::update().
		 *
		 * @since 2.8.0
		 * @since 4.4.0 Array format field IDs are now accepted.
		 *
		 * @param string $field_name Field name.
		 *
		 * @return string ID attribute for `$field_name`.
		 */
		public function get_field_id( $field_name ) {
	
			$field_name = str_replace( array( '[]', '[', ']' ), array( '', '-', '' ), $field_name );
			$field_name = trim( $field_name, '-' );
	
			return 'widget-' . $this->base_id . '-' . $this->get_number() . '-' . $field_name;
		}
	
		/**
		 * Returns the instance number.
		 *
		 * @return int
		 */
		public function get_number() {
			static $number = 1;
	
			if ( isset( $this->output_types['widget'] ) ) {
				return $this->output_types['widget']->number;
			}
	
			if ( empty( $this->number ) ) {
				$this->number = $number;
				$number ++;
			}
	
			return $this->number;
		}
	
		/**
		 * Get the widget input title html.
		 *
		 * @param $args
		 *
		 * @return string
		 */
		public function widget_field_title( $args ) {
	
			$title = '';
			if ( isset( $args['title'] ) && $args['title'] ) {
				if ( isset( $args['icon'] ) && $args['icon'] ) {
					$title = $this->get_widget_icon( $args['icon'], $args['title']  );
				} else {
					$title = esc_attr($args['title']);
				}
			}
	
			return $title;
		}
	
		/**
		 * Retrieves the icon to use for widgets / blocks.
		 *
		 * @return array
		 */
		public function get_widget_icon( $icon = 'box-top', $title = '' ) {
			if($icon=='box-top'){
				return '<svg title="'.esc_attr($title).'" width="20px" height="20px" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" stroke-linejoin="round" stroke-miterlimit="1.414" role="img" aria-hidden="true" focusable="false"><rect x="2.714" y="5.492" width="1.048" height="9.017" fill="#555D66"></rect><rect x="16.265" y="5.498" width="1.023" height="9.003" fill="#555D66"></rect><rect x="5.518" y="2.186" width="8.964" height="2.482" fill="#272B2F"></rect><rect x="5.487" y="16.261" width="9.026" height="1.037" fill="#555D66"></rect></svg>';
			}elseif($icon=='box-right'){
				return '<svg title="'.esc_attr($title).'" width="20px" height="20px" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" stroke-linejoin="round" stroke-miterlimit="1.414" role="img" aria-hidden="true" focusable="false"><rect x="2.714" y="5.492" width="1.046" height="9.017" fill="#555D66"></rect><rect x="15.244" y="5.498" width="2.518" height="9.003" fill="#272B2F"></rect><rect x="5.518" y="2.719" width="8.964" height="0.954" fill="#555D66"></rect><rect x="5.487" y="16.308" width="9.026" height="0.99" fill="#555D66"></rect></svg>';
			}elseif($icon=='box-bottom'){
				return '<svg title="'.esc_attr($title).'" width="20px" height="20px" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" stroke-linejoin="round" stroke-miterlimit="1.414" role="img" aria-hidden="true" focusable="false"><rect x="2.714" y="5.492" width="1" height="9.017" fill="#555D66"></rect><rect x="16.261" y="5.498" width="1.027" height="9.003" fill="#555D66"></rect><rect x="5.518" y="2.719" width="8.964" height="0.968" fill="#555D66"></rect><rect x="5.487" y="15.28" width="9.026" height="2.499" fill="#272B2F"></rect></svg>';
			}elseif($icon=='box-left'){
				return '<svg title="'.esc_attr($title).'" width="20px" height="20px" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" stroke-linejoin="round" stroke-miterlimit="1.414" role="img" aria-hidden="true" focusable="false"><rect x="2.202" y="5.492" width="2.503" height="9.017" fill="#272B2F"></rect><rect x="16.276" y="5.498" width="1.012" height="9.003" fill="#555D66"></rect><rect x="5.518" y="2.719" width="8.964" height="0.966" fill="#555D66"></rect><rect x="5.487" y="16.303" width="9.026" height="0.995" fill="#555D66"></rect></svg>';
			}
		}
	
		/**
		 * Constructs name attributes for use in form() fields
		 *
		 * This function should be used in form() methods to create name attributes for fields
		 * to be saved by update()
		 *
		 * @since 2.8.0
		 * @since 4.4.0 Array format field names are now accepted.
		 *
		 * @param string $field_name Field name.
		 *
		 * @return string Name attribute for `$field_name`.
		 */
		public function get_field_name( $field_name ) {
			$pos = strpos( $field_name, '[' );
	
			if ( false !== $pos ) {
				// Replace the first occurrence of '[' with ']['.
				$field_name = '[' . substr_replace( $field_name, '][', $pos, strlen( '[' ) );
			} else {
				$field_name = '[' . $field_name . ']';
			}
	
			return 'widget-' . $this->base_id . '[' . $this->get_number() . ']' . $field_name;
		}
	
		public function widget_inputs_row_end($key, $args){
			if(!empty($args['row'])){
				// maybe close
				if(!empty($args['row']['close'])){
					echo "</div></div>";
				}
	
				echo "</div>";
			}
		}
	
		/**
		 * Generate and return inline styles from CSS rules that will match the unique class of the instance.
		 *
		 * @param array $rules
		 *
		 * @since 1.0.20
		 * @return string
		 */
		public function get_instance_style($rules = array()){
			$css = '';
	
			if(!empty($rules)){
				$rules = array_unique($rules);
				$instance_hash = $this->get_instance_hash();
				$css .= "<style>";
				foreach($rules as $rule){
					$css .= ".sdel-$instance_hash $rule";
				}
				$css .= "</style>";
			}
	
			return $css;
		}
	
		/**
		 * Get an instance hash that will be unique to the type and settings.
		 *
		 * @since 1.0.20
		 * @return string
		 */
		public function get_instance_hash(){
			$instance_string = $this->base_id . serialize( $this->instance );
			return hash( 'crc32b', $instance_string );
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
			var sd_cf_field_rules = [], sd_cf_field_key_rules = {}, sd_cf_field_default_values = {};
	
			jQuery(function($) {
				/* Init conditional fields */
				sd_cf_field_init_rules($);
			});
	
			/**
			* Conditional fields init.
			*/
			function sd_cf_field_init_rules($) {
				if (!$('[data-has-rule]').length) {
					return;
				}
	
				$('[data-rule-key]').on('change keypress keyup', 'input, textarea', function() {
					sd_cf_field_apply_rules($(this));
				});
	
				$('[data-rule-key]').on('change', 'select', function() {
					sd_cf_field_apply_rules($(this));
				});
	
				$('[data-rule-key]').on('change.select2', 'select', function() {
					sd_cf_field_apply_rules($(this));
				});
	
				/*jQuery(document).on('sd_cf_field_on_change', function() {
					sd_cf_field_hide_child_elements();
				});*/
	
				sd_cf_field_setup_rules($);
			}
	
			/**
			* Setup conditional field rules.
			*/
			function sd_cf_field_setup_rules($) {
				var sd_cf_field_keys = [];
	
				$('[data-rule-key]').each(function() {
					var key = jQuery(this).data('rule-key'),
						irule = parseInt(jQuery(this).data('has-rule'));
					if (key) {
						sd_cf_field_keys.push(key);
					}
	
					var parse_conds = {};
					if ($(this).data('rule-fie-0')) {
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
	
						jQuery.each(parse_conds, function(j, data) {
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
							sd_cf_field_rules.push(item);
						});
					}
					sd_cf_field_default_values[jQuery(this).data('rule-key')] = sd_cf_field_get_default_value(jQuery(this));
				});
	
				jQuery.each(sd_cf_field_keys, function(i, fkey) {
					sd_cf_field_key_rules[fkey] = sd_cf_field_get_children(fkey);
				});
	
				jQuery('[data-rule-key]:visible').each(function() {
					var conds = sd_cf_field_key_rules[jQuery(this).data('rule-key')];
					if (conds && conds.length) {
						var $main_el = jQuery(this), el = sd_cf_field_get_element($main_el);
						if (jQuery(el).length) {
							sd_cf_field_apply_rules(jQuery(el));
						}
					}
				});
			}
	
			/**
			* Apply conditional field rules.
			*/
			function sd_cf_field_apply_rules($el) {
				if (!$el.parents('[data-rule-key]').length) {
					return;
				}
	
				if ($el.data('no-rule')) {
					return;
				}
	
				var key = $el.parents('[data-rule-key]').data('rule-key');
				var conditions = sd_cf_field_key_rules[key];
				if (typeof conditions === 'undefined') {
					return;
				}
				var field_type = sd_cf_field_get_type($el.parents('[data-rule-key]')),
					current_value = sd_cf_field_get_value($el);
	
				var $keys = {},
					$keys_values = {},
					$key_rules = {};
	
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
						if (((condition.value && condition.value == condition.value) || (condition.value === field_value)) && sd_cf_field_in_array(field_value, $keys_values[condition.key])) {
							$keys[condition.key][index] = true;
						} else {
							$keys[condition.key][index] = false;
						}
					} else if (condition.condition === 'not equals') {
						var field_value = (Array.isArray(current_value) && current_value.length === 1) ? current_value[0] : current_value;
						if (jQuery.isNumeric(condition.value) && parseInt(field_value) !== parseInt(condition.value) && field_value && !sd_cf_field_in_array(field_value, $keys_values[condition.key])) {
							$keys[condition.key][index] = true;
						} else if (condition.value != field_value && !sd_cf_field_in_array(field_value, $keys_values[condition.key])) {
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
						switch (field_type) {
							case 'multiselect':
								if (current_value && ((!Array.isArray(current_value) && current_value.indexOf(condition.value) >= 0) || (Array.isArray(current_value) && sd_cf_field_in_array(condition.value, current_value)))) { //
									$keys[condition.key][index] = true;
								} else {
									$keys[condition.key][index] = false;
								}
								break;
							case 'checkbox':
								if (current_value && ((!Array.isArray(current_value) && current_value.indexOf(condition.value) >= 0) || (Array.isArray(current_value) && sd_cf_field_in_array(condition.value, current_value)))) { //
									$keys[condition.key][index] = true;
								} else {
									$keys[condition.key][index] = false;
								}
								break;
							default:
								if (typeof $keys[condition.key][index] === 'undefined') {
									if (current_value && current_value.indexOf(condition.value) >= 0 && sd_cf_field_in_array(current_value, $keys_values[condition.key])) {
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
					if (sd_cf_field_in_array(true, field)) {
						sd_cf_field_apply_action($el, $key_rules[index], true);
					} else {
						sd_cf_field_apply_action($el, $key_rules[index], false);
					}
				});
	
				/* Trigger field change */
				if ($keys.length) {
					$el.trigger('sd_cf_field_on_change');
				}
			}
	
			/**
			* Get the field element.
			*/
			function sd_cf_field_get_element($el) {
				var el = $el.find('input,textarea,select'),
					type = sd_cf_field_get_type($el);
				if (type && window._sd_cf_field_elements && typeof window._sd_cf_field_elements == 'object' && typeof window._sd_cf_field_elements[type] != 'undefined') {
					el = window._sd_cf_field_elements[type];
				}
				return el;
			}
	
			/**
			* Get the field type.
			*/
			function sd_cf_field_get_type($el) {
				return $el.data('rule-type');
			}
	
			/**
			* Get the field value.
			*/
			function sd_cf_field_get_value($el) {
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
			function sd_cf_field_get_default_value($el) {
				var value = '',
					type = sd_cf_field_get_type($el);
	
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
						if (window._sd_cf_field_default_values && typeof window._sd_cf_field_default_values == 'object' && typeof window._sd_cf_field_default_values[type] != 'undefined') {
							value = window._sd_cf_field_default_values[type];
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
			function sd_cf_field_reset_default_value($el) {
				var type = sd_cf_field_get_type($el),
					key = $el.data('rule-key'),
					field = sd_cf_field_default_values[key];
	
				switch (type) {
					case 'text':
					case 'number':
					case 'date':
					case 'textarea':
						$el.find('input:text,input[type="number"],textarea').val(field.value);
						break;
					case 'phone':
					case 'email':
					case 'color':
					case 'url':
					case 'hidden':
					case 'password':
					case 'file':
						$el.find('input[type="' + type + '"]').val(field.value);
						break;
					case 'select':
						$el.find('select').find('option').prop('selected', false);
						$el.find('select').val(field.value);
						$el.find('select').trigger('change');
						break;
					case 'multiselect':
						$el.find('select').find('option').prop('selected', false);
						if ((typeof field.value === 'object' || typeof field.value === 'array') && !field.value.length && $el.find('select option:first').text() == '') {
							$el.find('select option:first').remove(); // Clear first option to show placeholder.
						}
						jQuery.each(field.value, function(i, v) {
							$el.find('select').find('option[value="' + v + '"]').attr('selected', true);
						});
						$el.find('select').trigger('change');
						break;
					case 'checkbox':
						if ($el.find('input[type="checkbox"]:checked').length >= 1) {
							$el.find('input[type="checkbox"]:checked').prop('checked', false);
							if (Array.isArray(field.value)) {
								jQuery.each(field.value, function(i, v) {
									$el.find('input[type="checkbox"][value="' + v + '"]').attr('checked', true);
								});
							} else {
								$el.find('input[type="checkbox"][value="' + field.value + '"]').attr('checked', true);
							}
						}
						break;
					case 'radio':
						if ($el.find('input[type="radio"]:checked').length >= 1) {
							setTimeout(function() {
								$el.find('input[type="radio"]:checked').prop('checked', false);
								$el.find('input[type="radio"][value="' + field.value + '"]').attr('checked', true);
							}, 100);
						}
						break;
					default:
						jQuery(document.body).trigger('sd_cf_field_reset_default_value', type, $el, field);
						break;
				}
	
				if (!$el.hasClass('sd-cf-field-has-changed')) {
					var el = sd_cf_field_get_element($el);
					if (type === 'radio' || type === 'checkbox') {
						el = el.find(':checked');
					}
					if (el) {
						el.trigger('change');
						$el.addClass('sd-cf-field-has-changed');
					}
				}
			}
	
			/**
			* Get the field children.
			*/
			function sd_cf_field_get_children(field_key) {
				var rules = [];
				jQuery.each(sd_cf_field_rules, function(j, rule) {
					if (rule.field.field === field_key) {
						rules.push(rule.field.rule);
					}
				});
				return rules;
			}
	
			/**
			* Check in array field value.
			*/
			function sd_cf_field_in_array(find, item, match) {
				var found = false,
					key;
				match = !!match;
	
				for (key in item) {
					if ((match && item[key] === find) || (!match && item[key] == find)) {
						found = true;
						break;
					}
				}
				return found;
			}
	
			/**
			* App the field condition action.
			*/
			function sd_cf_field_apply_action($el, rule, isTrue) {
				var $destEl = jQuery('[data-rule-key="' + rule.key + '"]');
	
				if (rule.action === 'show' && isTrue) {
					if ($destEl.is(':hidden')) {
						sd_cf_field_reset_default_value($destEl);
					}
					sd_cf_field_show_element($destEl);
				} else if (rule.action === 'show' && !isTrue) {
					sd_cf_field_hide_element($destEl);
				} else if (rule.action === 'hide' && isTrue) {
					sd_cf_field_hide_element($destEl);
				} else if (rule.action === 'hide' && !isTrue) {
					if ($destEl.is(':hidden')) {
						sd_cf_field_reset_default_value($destEl);
					}
					sd_cf_field_show_element($destEl);
				}
				return $el.removeClass('sd-cf-field-has-changed');
			}
	
			/**
			* Show field element.
			*/
			function sd_cf_field_show_element($el) {
				$el.removeClass('d-none').show();
	
				if (window && window.navigator.userAgent.indexOf("MSIE") !== -1) {
					$el.css({
						"visibility": "visible"
					});
				}
			}
	
			/**
			* Hide field element.
			*/
			function sd_cf_field_hide_element($el) {
				$el.addClass('d-none').hide();
	
				if (window && window.navigator.userAgent.indexOf("MSIE") !== -1) {
					$el.css({
						"visibility": "hidden"
					});
				}
			}
	
			/**
			* Show field child elements.
			*/
			function sd_cf_field_hide_child_elements() {
				jQuery.each(sd_cf_field_key_rules, function(i, conds) {
					if (i && conds && conds.length && (jQuery('[data-rule-key="' + i + '"]:hidden').length >= 1 || jQuery('[data-rule-key="' + i + '"]').css('display') === 'none')) {
						jQuery.each(conds, function(key, cond) {
							jQuery('[data-rule-key="' + cond.key + '"]').addClass('d-none').hide();
						});
					}
				});
			}
			<?php do_action( 'wp_super_duper_conditional_fields_js', $this ); ?>
			</script>
						<?php
						$output = ob_get_clean();
	
						return str_replace( array( '<script>', '</script>' ), '', trim( $output ) );
			}
	
		/**
		 * Output the super title.
		 *
		 * @param $args
		 * @param array $instance
		 *
		 * @return string
		 */
		public function output_title( $args, $instance = array() ) {
			$output = '';
	
			if ( ! empty( $instance['title'] ) ) {
				/** This filter is documented in wp-includes/widgets/class-wp-widget-pages.php */
				$title  = apply_filters( 'widget_title', $instance['title'], $instance, $this->base_id );
	
				if ( empty( $instance['widget_title_tag'] ) ) {
					$output = $args['before_title'] . $title . $args['after_title'];
				} else {
					$title_tag = esc_attr( $instance['widget_title_tag'] );
	
					// classes
					$title_classes = array();
					$title_classes[] = !empty( $instance['widget_title_size_class'] ) ? sanitize_html_class( $instance['widget_title_size_class'] ) : '';
					$title_classes[] = !empty( $instance['widget_title_align_class'] ) ? sanitize_html_class( $instance['widget_title_align_class'] ) : '';
					$title_classes[] = !empty( $instance['widget_title_color_class'] ) ? "text-".sanitize_html_class( $instance['widget_title_color_class'] ) : '';
					$title_classes[] = !empty( $instance['widget_title_border_class'] ) ? sanitize_html_class( $instance['widget_title_border_class'] ) : '';
					$title_classes[] = !empty( $instance['widget_title_border_color_class'] ) ? "border-".sanitize_html_class( $instance['widget_title_border_color_class'] ) : '';
					$title_classes[] = !empty( $instance['widget_title_mt_class'] ) ? "mt-".absint( $instance['widget_title_mt_class'] ) : '';
					$title_classes[] = !empty( $instance['widget_title_mr_class'] ) ? "mr-".absint( $instance['widget_title_mr_class'] ) : '';
					$title_classes[] = !empty( $instance['widget_title_mb_class'] ) ? "mb-".absint( $instance['widget_title_mb_class'] ) : '';
					$title_classes[] = !empty( $instance['widget_title_ml_class'] ) ? "ml-".absint( $instance['widget_title_ml_class'] ) : '';
					$title_classes[] = !empty( $instance['widget_title_pt_class'] ) ? "pt-".absint( $instance['widget_title_pt_class'] ) : '';
					$title_classes[] = !empty( $instance['widget_title_pr_class'] ) ? "pr-".absint( $instance['widget_title_pr_class'] ) : '';
					$title_classes[] = !empty( $instance['widget_title_pb_class'] ) ? "pb-".absint( $instance['widget_title_pb_class'] ) : '';
					$title_classes[] = !empty( $instance['widget_title_pl_class'] ) ? "pl-".absint( $instance['widget_title_pl_class'] ) : '';
					$title_classes   = array_filter( $title_classes );
	
					$class  = ! empty( $title_classes ) ? implode( ' ', $title_classes ) : '';
					$output = "<$title_tag class='$class' >$title</$title_tag>";
				}
	
			}
	
			return $output;
		}

		/**
		 * Backwards compatibility for SDv1
		 * 
		 * @param string $editor_id
		 * @param string $insert_shortcode_function
		 * 
		 * @return string|void
		 */
		public static function shortcode_insert_button( $editor_id = '', $insert_shortcode_function = '' ) {
			return class_exists('WP_Super_Duper_Shortcode') ? WP_Super_Duper_Shortcode::shortcode_insert_button( $editor_id, $insert_shortcode_function ) : '';
		}

		/**
		 * Backwards compatibility for SDv1
		 * 
		 * @param string $id
		 * @param string $search_for_id
		 * 
		 * @return mixed|string
		 */
		public static function shortcode_button( $id = '', $search_for_id = '') {
			return class_exists('WP_Super_Duper_Shortcode') ? WP_Super_Duper_Shortcode::shortcode_button( $id, $search_for_id ) : '';
		}
	
	}

}
