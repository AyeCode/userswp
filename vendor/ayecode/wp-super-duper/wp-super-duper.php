<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_Super_Duper' ) ) {


	/**
	 * A Class to be able to create a Widget, Shortcode or Block to be able to output content for WordPress.
	 *
	 * Should not be called direct but extended instead.
	 *
	 * Class WP_Super_Duper
	 * @since 1.0.16 change log moved to file change-log.txt - CHANGED
	 * @ver 1.0.19
	 */
	class WP_Super_Duper extends WP_Widget {

		public $version = "1.0.27";
		public $font_awesome_icon_version = "5.11.2";
		public $block_code;
		public $options;
		public $base_id;
		public $settings_hash;
		public $arguments = array();
		public $instance = array();
		private $class_name;

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
			$this->base_id                     = $options['base_id'];
			// lets filter the options before we do anything
			$options       = apply_filters( "wp_super_duper_options", $options );
			$options       = apply_filters( "wp_super_duper_options_{$this->base_id}", $options );
			$options       = $this->add_name_from_key( $options );
			$this->options = $options;

			$this->base_id   = $options['base_id'];
			$this->arguments = isset( $options['arguments'] ) ? $options['arguments'] : array();

			// init parent
			parent::__construct( $options['base_id'], $options['name'], $options['widget_ops'] );

			if ( isset( $options['class_name'] ) ) {
				// register widget
				$this->class_name = $options['class_name'];

				// register shortcode
				$this->register_shortcode();

				// Fusion Builder (avada) support
				if ( function_exists( 'fusion_builder_map' ) ) {
					add_action( 'init', array( $this, 'register_fusion_element' ) );
				}

				// register block
				add_action( 'admin_enqueue_scripts', array( $this, 'register_block' ) );
			}

			// add the CSS and JS we need ONCE
			global $sd_widget_scripts;

			if ( ! $sd_widget_scripts ) {
				wp_add_inline_script( 'admin-widgets', $this->widget_js() );
				wp_add_inline_script( 'customize-controls', $this->widget_js() );
				wp_add_inline_style( 'widgets', $this->widget_css() );

				// maybe add elementor editor styles
				add_action( 'elementor/editor/after_enqueue_styles', array( $this, 'elementor_editor_styles' ) );

				$sd_widget_scripts = true;

				// add shortcode insert button once
				add_action( 'media_buttons', array( $this, 'shortcode_insert_button' ) );
				// generatepress theme sections compatibility
				if ( function_exists( 'generate_sections_sections_metabox' ) ) {
					add_action( 'generate_sections_metabox', array( $this, 'shortcode_insert_button_script' ) );
				}
				if ( $this->is_preview() ) {
					add_action( 'wp_footer', array( $this, 'shortcode_insert_button_script' ) );
					// this makes the insert button work for elementor
					add_action( 'elementor/editor/after_enqueue_scripts', array(
						$this,
						'shortcode_insert_button_script'
					) ); // for elementor
				}
				// this makes the insert button work for cornerstone
				add_action( 'wp_print_footer_scripts', array( __CLASS__, 'maybe_cornerstone_builder' ) );

				add_action( 'wp_ajax_super_duper_get_widget_settings', array( __CLASS__, 'get_widget_settings' ) );
				add_action( 'wp_ajax_super_duper_get_picker', array( __CLASS__, 'get_picker' ) );

				// add generator text to admin head
				add_action( 'admin_head', array( $this, 'generator' ) );
			}

			do_action( 'wp_super_duper_widget_init', $options, $this );
		}

		/**
		 * Add our widget CSS to elementor editor.
		 */
		public function elementor_editor_styles() {
			wp_add_inline_style( 'elementor-editor', $this->widget_css( false ) );
		}

		public function register_fusion_element() {

			$options = $this->options;

			if ( $this->base_id ) {

				$params = $this->get_fusion_params();

				$args = array(
					'name'            => $options['name'],
					'shortcode'       => $this->base_id,
					'icon'            => $options['block-icon'] ? $options['block-icon'] : 'far fa-square',
					'allow_generator' => true,
				);

				if ( ! empty( $params ) ) {
					$args['params'] = $params;
				}

				fusion_builder_map( $args );
			}

		}

		public function get_fusion_params() {
			$params    = array();
			$arguments = $this->get_arguments();

			if ( ! empty( $arguments ) ) {
				foreach ( $arguments as $key => $val ) {
					$param = array();
					// type
					$param['type'] = str_replace(
						array(
							"text",
							"number",
							"email",
							"color",
							"checkbox"
						),
						array(
							"textfield",
							"textfield",
							"textfield",
							"colorpicker",
							"select",

						),
						$val['type'] );

					// multiselect
					if ( $val['type'] == 'multiselect' || ( ( $param['type'] == 'select' || $val['type'] == 'select' ) && ! empty( $val['multiple'] ) ) ) {
						$param['type']     = 'multiple_select';
						$param['multiple'] = true;
					}

					// heading
					$param['heading'] = $val['title'];

					// description
					$param['description'] = isset( $val['desc'] ) ? $val['desc'] : '';

					// param_name
					$param['param_name'] = $key;

					// Default
					$param['default'] = isset( $val['default'] ) ? $val['default'] : '';

					// Group
					if ( isset( $val['group'] ) ) {
						$param['group'] = $val['group'];
					}

					// value
					if ( $val['type'] == 'checkbox' ) {
						if ( isset( $val['default'] ) && $val['default'] == '0' ) {
							unset( $param['default'] );
						}
						$param['value'] = array( '' => __( "No" ), '1' => __( "Yes" ) );
					} elseif ( $param['type'] == 'select' || $param['type'] == 'multiple_select' ) {
						$param['value'] = isset( $val['options'] ) ? $val['options'] : array();
					} else {
						$param['value'] = isset( $val['default'] ) ? $val['default'] : '';
					}

					// setup the param
					$params[] = $param;

				}
			}


			return $params;
		}

		/**
		 * Maybe insert the shortcode inserter button in the footer if we are in the cornerstone builder
		 */
		public static function maybe_cornerstone_builder() {
			if ( did_action( 'cornerstone_before_boot_app' ) ) {
				self::shortcode_insert_button_script();
			}
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
		 * Output the version in the admin header.
		 */
		public function generator() {
			echo '<meta name="generator" content="WP Super Duper v' . $this->version . '" />';
		}

		/**
		 * Get widget settings.
		 *
		 * @since 1.0.0
		 */
		public static function get_widget_settings() {
			global $sd_widgets;

			$shortcode = isset( $_REQUEST['shortcode'] ) && $_REQUEST['shortcode'] ? sanitize_title_with_dashes( $_REQUEST['shortcode'] ) : '';
			if ( ! $shortcode ) {
				wp_die();
			}
			$widget_args = isset( $sd_widgets[ $shortcode ] ) ? $sd_widgets[ $shortcode ] : '';
			if ( ! $widget_args ) {
				wp_die();
			}
			$class_name = isset( $widget_args['class_name'] ) && $widget_args['class_name'] ? $widget_args['class_name'] : '';
			if ( ! $class_name ) {
				wp_die();
			}

			// invoke an instance method
			$widget = new $class_name;

			ob_start();
			$widget->form( array() );
			$form = ob_get_clean();
			echo "<form id='$shortcode'>" . $form . "<div class=\"widget-control-save\"></div></form>";
			echo "<style>" . $widget->widget_css() . "</style>";
			echo "<script>" . $widget->widget_js() . "</script>";
			?>
			<?php
			wp_die();
		}

		/**
		 * Insert shortcode builder button to classic editor (not inside Gutenberg, not needed).
		 *
		 * @since 1.0.0
		 *
		 * @param string $editor_id Optional. Shortcode editor id. Default null.
		 * @param string $insert_shortcode_function Optional. Insert shortcode function. Default null.
		 */
		public static function shortcode_insert_button( $editor_id = '', $insert_shortcode_function = '' ) {
			global $sd_widgets, $shortcode_insert_button_once;
			if ( $shortcode_insert_button_once ) {
				return;
			}
			add_thickbox();


			/**
			 * Cornerstone makes us play dirty tricks :/
			 * All media_buttons are removed via JS unless they are two specific id's so we wrap our content in this ID so it is not removed.
			 */
			if ( function_exists( 'cornerstone_plugin_init' ) && ! is_admin() ) {
				echo '<span id="insert-media-button">';
			}

			echo self::shortcode_button( 'this', 'true' );

			// see opening note
			if ( function_exists( 'cornerstone_plugin_init' ) && ! is_admin() ) {
				echo '</span>'; // end #insert-media-button
			}

			// Add separate script for generatepress theme sections
			if ( function_exists( 'generate_sections_sections_metabox' ) && did_action( 'generate_sections_metabox' ) ) {
			} else {
				self::shortcode_insert_button_script( $editor_id, $insert_shortcode_function );
			}

			$shortcode_insert_button_once = true;
		}

		/**
		 * Gets the shortcode insert button html.
		 *
		 * @param string $id
		 * @param string $search_for_id
		 *
		 * @return mixed
		 */
		public static function shortcode_button( $id = '', $search_for_id = '' ) {
			ob_start();
			?>
			<span class="sd-lable-shortcode-inserter">
				<a onclick="sd_ajax_get_picker(<?php echo $id;
				if ( $search_for_id ) {
					echo "," . $search_for_id;
				} ?>);" href="#TB_inline?width=100%&height=550&inlineId=super-duper-content-ajaxed"
				   class="thickbox button super-duper-content-open" title="Add Shortcode">
					<span style="vertical-align: middle;line-height: 18px;font-size: 20px;"
					      class="dashicons dashicons-screenoptions"></span>
				</a>
				<div id="super-duper-content-ajaxed" style="display:none;">
					<span>Loading</span>
				</div>
			</span>

			<?php
			$html = ob_get_clean();

			// remove line breaks so we can use it in js
			return preg_replace( "/\r|\n/", "", trim( $html ) );
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
		 * Output the JS and CSS for the shortcode insert button.
		 *
		 * @since 1.0.6
		 *
		 * @param string $editor_id
		 * @param string $insert_shortcode_function
		 */
		public static function shortcode_insert_button_script( $editor_id = '', $insert_shortcode_function = '' ) {
			?>
			<style>
				.sd-shortcode-left-wrap {
					float: left;
					width: 60%;
				}

				.sd-shortcode-left-wrap .gd-help-tip {
					float: none;
				}

				.sd-shortcode-left-wrap .widefat {
					border-spacing: 0;
					width: 100%;
					clear: both;
					margin: 0;
					border: 1px solid #ddd;
					box-shadow: inset 0 1px 2px rgba(0, 0, 0, .07);
					background-color: #fff;
					color: #32373c;
					outline: 0;
					transition: 50ms border-color ease-in-out;
					padding: 3px 5px;
				}

				.sd-shortcode-left-wrap input[type=checkbox].widefat {
					border: 1px solid #b4b9be;
					background: #fff;
					color: #555;
					clear: none;
					cursor: pointer;
					display: inline-block;
					line-height: 0;
					height: 16px;
					margin: -4px 4px 0 0;
					margin-top: 0;
					outline: 0;
					padding: 0 !important;
					text-align: center;
					vertical-align: middle;
					width: 16px;
					min-width: 16px;
					-webkit-appearance: none;
					box-shadow: inset 0 1px 2px rgba(0, 0, 0, .1);
					transition: .05s border-color ease-in-out;
				}

				.sd-shortcode-left-wrap input[type=checkbox]:checked:before {
					content: "\f147";
					margin: -3px 0 0 -4px;
					color: #1e8cbe;
					float: left;
					display: inline-block;
					vertical-align: middle;
					width: 16px;
					font: normal 21px/1 dashicons;
					speak: none;
					-webkit-font-smoothing: antialiased;
					-moz-osx-font-smoothing: grayscale;
				}

				#sd-shortcode-output-actions button,
				.sd-advanced-button {
					color: #555;
					border-color: #ccc;
					background: #f7f7f7;
					box-shadow: 0 1px 0 #ccc;
					vertical-align: top;
					display: inline-block;
					text-decoration: none;
					font-size: 13px;
					line-height: 26px;
					height: 28px;
					margin: 0;
					padding: 0 10px 1px;
					cursor: pointer;
					border-width: 1px;
					border-style: solid;
					-webkit-appearance: none;
					border-radius: 3px;
					white-space: nowrap;
					box-sizing: border-box;
				}

				button.sd-advanced-button {
					background: #0073aa;
					border-color: #006799;
					box-shadow: inset 0 2px 0 #006799;
					vertical-align: top;
					color: #fff;
					text-decoration: none;
					text-shadow: 0 -1px 1px #006799, 1px 0 1px #006799, 0 1px 1px #006799, -1px 0 1px #006799;
					float: right;
					margin-right: 3px !important;
					font-size: 20px !important;
				}

				.sd-shortcode-right-wrap {
					float: right;
					width: 35%;
				}

				#sd-shortcode-output {
					background: rgba(255, 255, 255, .5);
					border-color: rgba(222, 222, 222, .75);
					box-shadow: inset 0 1px 2px rgba(0, 0, 0, .04);
					color: rgba(51, 51, 51, .5);
					overflow: auto;
					padding: 2px 6px;
					line-height: 1.4;
					resize: vertical;
				}

				#sd-shortcode-output {
					height: 250px;
					width: 100%;
				}

				<?php if ( function_exists( 'generate_sections_sections_metabox' ) ) { ?>
				.generate-sections-modal #custom-media-buttons > .sd-lable-shortcode-inserter {
					display: inline;
				}

				<?php } ?>
			</style>
			<?php
			if ( class_exists( 'SiteOrigin_Panels' ) ) {
				echo "<script>" . self::siteorigin_js() . "</script>";
			}
			?>
			<script>
				<?php
				if(! empty( $insert_shortcode_function )){
					echo $insert_shortcode_function;
				}else{

				/**
				 * Function for super duper insert shortcode.
				 *
				 * @since 1.0.0
				 */
				?>
				function sd_insert_shortcode($editor_id) {
					$shortcode = jQuery('#TB_ajaxContent #sd-shortcode-output').val();
					if ($shortcode) {
						if (!$editor_id) {
							<?php
							if ( isset( $_REQUEST['et_fb'] ) ) {
								echo '$editor_id = "#main_content_content_vb_tiny_mce";';
							} elseif ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'elementor' ) {
								echo '$editor_id = "#elementor-controls .wp-editor-container textarea";';
							} else {
								echo '$editor_id = "#wp-content-editor-container textarea";';
							}
							?>
						} else {
							$editor_id = '#' + $editor_id;
						}
						tmceActive = jQuery($editor_id).attr("aria-hidden") == "true" ? true : false;
						/* GeneratePress */
						if (jQuery('#generate-sections-modal-dialog ' + $editor_id).length) {
							$editor_id = '#generate-sections-modal-dialog ' + $editor_id;
							tmceActive = jQuery($editor_id).closest('.wp-editor-wrap').hasClass('tmce-active') ? true : false;
						}
						if (tinyMCE && tinyMCE.activeEditor && tmceActive) {
							tinyMCE.execCommand('mceInsertContent', false, $shortcode);
						} else {
							var $txt = jQuery($editor_id);
							var caretPos = $txt[0].selectionStart;
							var textAreaTxt = $txt.val();
							var txtToAdd = $shortcode;
							var textareaValue = textAreaTxt.substring(0, caretPos) + txtToAdd + textAreaTxt.substring(caretPos);
							$txt.focus().val(textareaValue).change().keydown().blur().keyup().keypress().trigger('input').trigger('change');

							// set Divi react input value
							var input = document.getElementById("main_content_content_vb_tiny_mce");
							if (input) {
								sd_setNativeValue(input, textareaValue);
							}

						}
						tb_remove();
					}
				}

				/*
				 Set the value of elements controlled via react.
				 */
				function sd_setNativeValue(element, value) {
					let lastValue = element.value;
					element.value = value;
					let event = new Event("input", {target: element, bubbles: true});
					// React 15
					event.simulated = true;
					// React 16
					let tracker = element._valueTracker;
					if (tracker) {
						tracker.setValue(lastValue);
					}
					element.dispatchEvent(event);
				}
				<?php }?>

				/*
				 Copies the shortcode to the clipboard.
				 */
				function sd_copy_to_clipboard() {
					/* Get the text field */
					var copyText = document.querySelector("#TB_ajaxContent #sd-shortcode-output");
					//un-disable the field
					copyText.disabled = false;
					/* Select the text field */
					copyText.select();
					/* Copy the text inside the text field */
					document.execCommand("Copy");
					//re-disable the field
					copyText.disabled = true;
					/* Alert the copied text */
					alert("Copied the text: " + copyText.value);
				}

				/*
				 Gets the shortcode options.
				 */
				function sd_get_shortcode_options($this) {

					$short_code = jQuery($this).val();
					if ($short_code) {

						var data = {
							'action': 'super_duper_get_widget_settings',
							'shortcode': $short_code,
							'attributes': 123,
							'post_id': 321,
							'_ajax_nonce': '<?php echo wp_create_nonce( 'super_duper_output_shortcode' );?>'
						};

						if (typeof ajaxurl === 'undefined') {
							var ajaxurl = "<?php echo admin_url( 'admin-ajax.php' );?>";
						}

						jQuery.post(ajaxurl, data, function (response) {
							jQuery('#TB_ajaxContent .sd-shortcode-settings').html(response);

							jQuery('#' + $short_code).on('change', 'select', function () {
								sd_build_shortcode($short_code);
							}); // take care of select tags

							jQuery('#' + $short_code).on('change keypress keyup', 'input,textarea', function () {
								sd_build_shortcode($short_code);
							});

							sd_build_shortcode($short_code);

							// resize the window to fit
							setTimeout(function () {
								jQuery('#TB_ajaxContent').css('width', 'auto').css('height', '75vh');
							}, 200);


							return response;
						});
					}

				}

				/*
				 Builds and inserts the shortcode into the viewer.
				 */
				function sd_build_shortcode($id) {

					var multiSelects = {};
					var multiSelectsRemove = [];

					$output = "[" + $id;

					$form_data = jQuery("#" + $id).serializeArray();

					// run checks for multiselects
					jQuery.each($form_data, function (index, element) {
						if (element && element.value) {
							$field_name = element.name.substr(element.name.indexOf("][") + 2);
							$field_name = $field_name.replace("]", "");
							// check if its a multiple
							if ($field_name.includes("[]")) {
								multiSelectsRemove[multiSelectsRemove.length] = index;
								$field_name = $field_name.replace("[]", "");
								if ($field_name in multiSelects) {
									multiSelects[$field_name] = multiSelects[$field_name] + "," + element.value;
								} else {
									multiSelects[$field_name] = element.value;
								}
							}
						}
					});

					// fix multiselects if any are found
					if (multiSelectsRemove.length) {

						// remove all multiselects
						multiSelectsRemove.reverse();
						multiSelectsRemove.forEach(function (index) {
							$form_data.splice(index, 1);
						});

						$ms_arr = [];
						// add multiselets back
						jQuery.each(multiSelects, function (index, value) {
							$ms_arr[$ms_arr.length] = {"name": "[][" + index + "]", "value": value};
						});
						$form_data = $form_data.concat($ms_arr);
					}


					if ($form_data) {
						$content = '';
						$form_data.forEach(function (element) {

							if (element.value) {
								$field_name = element.name.substr(element.name.indexOf("][") + 2);
								$field_name = $field_name.replace("]", "");
								if ($field_name == 'html') {
									$content = element.value;
								} else {
									$output = $output + " " + $field_name + '="' + element.value + '"';
								}
							}

						});
					}
					$output = $output + "]";

					// check for content field
					if ($content) {
						$output = $output + $content + "[/" + $id + "]";
					}

					jQuery('#TB_ajaxContent #sd-shortcode-output').html($output);
				}


				/*
				 Delay the init of the textareas for 1 second.
				 */
				(function () {
					setTimeout(function () {
						sd_init_textareas();
					}, 1000);
				})();

				/*
				 Init the textareas to be able to show the shortcode builder button.
				 */
				function sd_init_textareas() {

					// General textareas
					jQuery(document).on('focus', 'textarea', function () {

						if (jQuery(this).hasClass('wp-editor-area')) {
							// insert the shortcode button to the textarea lable if not there already
							if (!jQuery(this).parent().find('.sd-lable-shortcode-inserter').length) {
								jQuery(this).parent().find('.quicktags-toolbar').append(sd_shortcode_button(jQuery(this).attr('id')));
							}
						} else {
							// insert the shortcode button to the textarea lable if not there already
							if (!jQuery("label[for='" + jQuery(this).attr('id') + "']").find('.sd-lable-shortcode-inserter').length) {
								jQuery("label[for='" + jQuery(this).attr('id') + "']").append(sd_shortcode_button(jQuery(this).attr('id')));
							}
						}
					});

					// The below tries to add the shortcode builder button to the builders own raw/shortcode sections.

					// DIVI
					jQuery(document).on('focusin', '.et-fb-codemirror', function () {
						// insert the shortcode button to the textarea lable if not there already
						if (!jQuery(this).closest('.et-fb-form__group').find('.sd-lable-shortcode-inserter').length) {
							jQuery(this).closest('.et-fb-form__group').find('.et-fb-form__label-text').append(sd_shortcode_button());
						}
					});

					// Beaver
					jQuery(document).on('focusin', '.fl-code-field', function () {
						// insert the shortcode button to the textarea lable if not there already
						if (!jQuery(this).closest('.fl-field-control-wrapper').find('.sd-lable-shortcode-inserter').length) {
							jQuery(this).closest('.fl-field-control-wrapper').prepend(sd_shortcode_button());
						}
					});

					// Fushion builder (avada)
					jQuery(document).on('focusin', '.CodeMirror.cm-s-default', function () {
						// insert the shortcode button to the textarea lable if not there already
						if (!jQuery(this).parent().find('.sd-lable-shortcode-inserter').length) {
							jQuery(sd_shortcode_button()).insertBefore(this);
						}
					});

					// Avia builder (enfold)
					jQuery(document).on('focusin', '#aviaTBcontent', function () {
						// insert the shortcode button to the textarea lable if not there already
						if (!jQuery(this).parent().parent().find('.avia-name-description ').find('.sd-lable-shortcode-inserter').length) {
							jQuery(this).parent().parent().find('.avia-name-description strong').append(sd_shortcode_button(jQuery(this).attr('id')));
						}
					});

					// Cornerstone textareas
					jQuery(document).on('focusin', '.cs-control.cs-control-textarea', function () {
						// insert the shortcode button to the textarea lable if not there already
						if (!jQuery(this).find('.cs-control-header label').find('.sd-lable-shortcode-inserter').length) {
							jQuery(this).find('.cs-control-header label').append(sd_shortcode_button());
						}
					});

					// Cornerstone main bar
					setTimeout(function () {
						// insert the shortcode button to the textarea lable if not there already
						if (!jQuery('.cs-bar-btns').find('.sd-lable-shortcode-inserter').length) {
							jQuery('<li style="text-align: center;padding: 5px;list-style: none;">' + sd_shortcode_button() + '</li>').insertBefore('.cs-action-toggle-custom-css');
						}
					}, 2000);


					// WP Bakery, code editor does not render shortcodes.
//					jQuery(document).on('focusin', '.wpb-textarea_raw_html', function () {
//						// insert the shortcode button to the textarea lable if not there already
//						if(!jQuery(this).parent().parent().find('.wpb_element_label').find('.sd-lable-shortcode-inserter').length){
//							jQuery(this).parent().parent().find('.wpb_element_label').append(sd_shortcode_button());
//						}
//					});

				}

				/**
				 * Gets the html for the picker via ajax and updates it on the fly.
				 *
				 * @param $id
				 * @param $search
				 */
				function sd_ajax_get_picker($id, $search) {
					if ($search) {
						$this = $id;
						$id = jQuery($this).closest('.wp-editor-wrap').find('.wp-editor-container textarea').attr('id');
					}

					var data = {
						'action': 'super_duper_get_picker',
						'editor_id': $id,
						'_ajax_nonce': '<?php echo wp_create_nonce( 'super_duper_picker' );?>'
					};

					if (!ajaxurl) {
						var ajaxurl = "<?php echo admin_url( 'admin-ajax.php' ); ?>";
					}

					jQuery.post(ajaxurl, data, function (response) {
						jQuery('#TB_ajaxContent').html(response);
						//return response;
					}).then(function (env) {
						jQuery('body').on('thickbox:removed', function () {
							jQuery('#super-duper-content-ajaxed').html('');
						});
					});
				}

				/**
				 * Get the html for the shortcode inserter button depending on if a textarea id is available.
				 *
				 * @param $id string The textarea id.
				 * @returns {string}
				 */
				function sd_shortcode_button($id) {
					if ($id) {
						return '<?php echo self::shortcode_button( "\\''+\$id+'\\'" );?>';
					} else {
						return '<?php echo self::shortcode_button();?>';
					}
				}

			</script>
			<?php
		}

		/**
		 * Gets some CSS for the widgets screen.
		 *
		 * @param bool $advanced If we should include advanced CSS.
		 *
		 * @return mixed
		 */
		public function widget_css( $advanced = true ) {
			ob_start();
			?>
			<style>
				<?php if( $advanced ){ ?>
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

				<?php } ?>

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
		 * Gets some JS for the widgets screen.
		 *
		 * @return mixed
		 */
		public function widget_js() {
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
				<?php do_action( 'wp_super_duper_widget_js', $this ); ?>
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
		 * Set the name from the argument key.
		 *
		 * @param $options
		 *
		 * @return mixed
		 */
		private function add_name_from_key( $options, $arguments = false ) {
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
		 * Register the parent shortcode.
		 *
		 * @since 1.0.0
		 */
		public function register_shortcode() {
			add_shortcode( $this->base_id, array( $this, 'shortcode_output' ) );
			add_action( 'wp_ajax_super_duper_output_shortcode', array( __CLASS__, 'render_shortcode' ) );
		}

		/**
		 * Render the shortcode via ajax so we can return it to Gutenberg.
		 *
		 * @since 1.0.0
		 */
		public static function render_shortcode() {

			check_ajax_referer( 'super_duper_output_shortcode', '_ajax_nonce', true );
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die();
			}

			// we might need the $post value here so lets set it.
			if ( isset( $_POST['post_id'] ) && $_POST['post_id'] ) {
				$post_obj = get_post( absint( $_POST['post_id'] ) );
				if ( ! empty( $post_obj ) && empty( $post ) ) {
					global $post;
					$post = $post_obj;
				}
			}

			if ( isset( $_POST['shortcode'] ) && $_POST['shortcode'] ) {
				$shortcode_name   = sanitize_title_with_dashes( $_POST['shortcode'] );
				$attributes_array = isset( $_POST['attributes'] ) && $_POST['attributes'] ? $_POST['attributes'] : array();
				$attributes       = '';
				if ( ! empty( $attributes_array ) ) {
					foreach ( $attributes_array as $key => $value ) {
						if ( is_array( $value ) ) {
							$value = implode( ",", $value );
						}
						$attributes .= " " . sanitize_title_with_dashes( $key ) . "='" . wp_slash( $value ) . "' ";
					}
				}

				$shortcode = "[" . $shortcode_name . " " . $attributes . "]";

				echo do_shortcode( $shortcode );

			}
			wp_die();
		}

		/**
		 * Output the shortcode.
		 *
		 * @param array $args
		 * @param string $content
		 *
		 * @return string
		 */
		public function shortcode_output( $args = array(), $content = '' ) {
			$args = $this->argument_values( $args );

			// add extra argument so we know its a output to gutenberg
			//$args
			$args = $this->string_to_bool( $args );

			// if we have a enclosed shortcode we add it to the special `html` argument
			if ( ! empty( $content ) ) {
				$args['html'] = $content;
			}

			$class = isset( $this->options['widget_ops']['classname'] ) ? esc_attr( $this->options['widget_ops']['classname'] ) : '';
			$class .= " sdel-".$this->get_instance_hash();

			$class = apply_filters( 'wp_super_duper_div_classname', $class, $args, $this );
			$class = apply_filters( 'wp_super_duper_div_classname_' . $this->base_id, $class, $args, $this );

			$attrs = apply_filters( 'wp_super_duper_div_attrs', '', $args, $this );
			$attrs = apply_filters( 'wp_super_duper_div_attrs_' . $this->base_id, '', $args, $this );

			$shortcode_args = array();
			$output         = '';
			$no_wrap        = isset( $this->options['no_wrap'] ) && $this->options['no_wrap'] ? true : false;
			if ( isset( $args['no_wrap'] ) && $args['no_wrap'] ) {
				$no_wrap = true;
			}
			$main_content = $this->output( $args, $shortcode_args, $content );
			if ( $main_content && ! $no_wrap ) {
				// wrap the shortcode in a div with the same class as the widget
				$output .= '<div class="' . $class . '" ' . $attrs . '>';
				if ( ! empty( $args['title'] ) ) {
					// if its a shortcode and there is a title try to grab the title wrappers
					$shortcode_args = array( 'before_title' => '', 'after_title' => '' );
					if ( empty( $instance ) ) {
						global $wp_registered_sidebars;
						if ( ! empty( $wp_registered_sidebars ) ) {
							foreach ( $wp_registered_sidebars as $sidebar ) {
								if ( ! empty( $sidebar['before_title'] ) ) {
									$shortcode_args['before_title'] = $sidebar['before_title'];
									$shortcode_args['after_title']  = $sidebar['after_title'];
									break;
								}
							}
						}
					}
					$output .= $this->output_title( $shortcode_args, $args );
				}
				$output .= $main_content;
				$output .= '</div>';
			} elseif ( $main_content && $no_wrap ) {
				$output .= $main_content;
			}

			// if preview show a placeholder if empty
			if ( $this->is_preview() && $output == '' ) {
				$output = $this->preview_placeholder_text( "{{" . $this->base_id . "}}" );
			}

			return apply_filters( 'wp_super_duper_widget_output', $output, $args, $shortcode_args, $this );
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
		 * This is the main output class for all 3 items, widget, shortcode and block, it is extended in the calling class.
		 *
		 * @param array $args
		 * @param array $widget_args
		 * @param string $content
		 */
		public function output( $args = array(), $widget_args = array(), $content = '' ) {

		}

		/**
		 * Add the dynamic block code inline when the wp-block in enqueued.
		 */
		public function register_block() {
			wp_add_inline_script( 'wp-blocks', $this->block() );
			if ( class_exists( 'SiteOrigin_Panels' ) ) {
				wp_add_inline_script( 'wp-blocks', $this->siteorigin_js() );
			}
		}

		/**
		 * Check if we need to show advanced options.
		 *
		 * @return bool
		 */
		public function block_show_advanced() {

			$show      = false;
			$arguments = $this->arguments;

			if ( empty( $arguments ) ) {
				$arguments = $this->get_arguments();
			}

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
		 * Generate the block icon.
		 *
		 * Enables the use of Font Awesome icons.
		 *
		 * @note xlink:href is actually deprecated but href is not supported by all so we use both.
		 *
		 * @param $icon
		 *
		 * @since 1.1.0
		 * @return string
		 */
		public function get_block_icon( $icon ) {

			// check if we have a Font Awesome icon
			$fa_type = '';
			if ( substr( $icon, 0, 7 ) === "fas fa-" ) {
				$fa_type = 'solid';
			} elseif ( substr( $icon, 0, 7 ) === "far fa-" ) {
				$fa_type = 'regular';
			} elseif ( substr( $icon, 0, 7 ) === "fab fa-" ) {
				$fa_type = 'brands';
			} else {
				$icon = "'" . $icon . "'";
			}

			// set the icon if we found one
			if ( $fa_type ) {
				$fa_icon = str_replace( array( "fas fa-", "far fa-", "fab fa-" ), "", $icon );
				$icon    = "el('svg',{width: 20, height: 20, viewBox: '0 0 20 20'},el('use', {'xlink:href': '" . $this->get_url() . "icons/" . $fa_type . ".svg#" . $fa_icon . "','href': '" . $this->get_url() . "icons/" . $fa_type . ".svg#" . $fa_icon . "'}))";
			}

			return $icon;
		}

		public function group_arguments( $arguments ) {
//			echo '###';print_r($arguments);
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

//			echo '###';print_r($arguments);
			return $arguments;
		}


		/**
		 * Output the JS for building the dynamic Guntenberg block.
		 *
		 * @since 1.0.4 Added block_wrap property which will set the block wrapping output element ie: div, span, p or empty for no wrap.
		 * @since 1.0.9 Save numbers as numbers and not strings.
		 * @since 1.1.0 Font Awesome classes can be used for icons.
		 * @return mixed
		 */
		public function block() {
			ob_start();

			$show_advanced = $this->block_show_advanced();
			?>
			<script>
				/**
				 * BLOCK: Basic
				 *
				 * Registering a basic block with Gutenberg.
				 * Simple block, renders and saves the same content without any interactivity.
				 *
				 * Styles:
				 *        editor.css — Editor styles for the block.
				 *        style.css  — Editor & Front end styles for the block.
				 */
				(function () {
					var __ = wp.i18n.__; // The __() for internationalization.
					var el = wp.element.createElement; // The wp.element.createElement() function to create elements.
					var editable = wp.blocks.Editable;
					var blocks = wp.blocks;
					var registerBlockType = wp.blocks.registerBlockType; // The registerBlockType() to register blocks.
					var is_fetching = false;
					var prev_attributes = [];

					var term_query_type = '';
					var post_type_rest_slugs = <?php if(! empty( $this->arguments ) && isset($this->arguments['post_type']['onchange_rest']['values'])){echo "[".json_encode($this->arguments['post_type']['onchange_rest']['values'])."]";}else{echo "[]";} ?>;
					const taxonomies_<?php echo str_replace("-","_", $this->id);?> = [{label: "Please wait", value: 0}];
					const sort_by_<?php echo str_replace("-","_", $this->id);?> = [{label: "Please wait", value: 0}];

					/**
					 * Register Basic Block.
					 *
					 * Registers a new block provided a unique name and an object defining its
					 * behavior. Once registered, the block is made available as an option to any
					 * editor interface where blocks are implemented.
					 *
					 * @param  {string}   name     Block name.
					 * @param  {Object}   settings Block settings.
					 * @return {?WPBlock}          The block, if it has been successfully
					 *                             registered; otherwise `undefined`.
					 */
					registerBlockType('<?php echo str_replace( "_", "-", sanitize_title_with_dashes( $this->options['textdomain'] ) . '/' . sanitize_title_with_dashes( $this->options['class_name'] ) );  ?>', { // Block name. Block names must be string that contains a namespace prefix. Example: my-plugin/my-custom-block.
						title: '<?php echo addslashes( $this->options['name'] ); ?>', // Block title.
						description: '<?php echo addslashes( $this->options['widget_ops']['description'] )?>', // Block title.
						icon: <?php echo $this->get_block_icon( $this->options['block-icon'] );?>,//'<?php echo isset( $this->options['block-icon'] ) ? esc_attr( $this->options['block-icon'] ) : 'shield-alt';?>', // Block icon from Dashicons → https://developer.wordpress.org/resource/dashicons/.
						supports: {
							<?php
							if ( isset( $this->options['block-supports'] ) ) {
								echo $this->array_to_attributes( $this->options['block-supports'] );
							}
							?>
						},
						category: '<?php echo isset( $this->options['block-category'] ) ? esc_attr( $this->options['block-category'] ) : 'common';?>', // Block category — Group blocks together based on common traits E.g. common, formatting, layout widgets, embed.
						<?php if ( isset( $this->options['block-keywords'] ) ) {
						echo "keywords : " . $this->options['block-keywords'] . ",";
					}?>

						<?php

						// maybe set no_wrap
						$no_wrap = isset( $this->options['no_wrap'] ) && $this->options['no_wrap'] ? true : false;
						if ( isset( $this->arguments['no_wrap'] ) && $this->arguments['no_wrap'] ) {
							$no_wrap = true;
						}
						if ( $no_wrap ) {
							$this->options['block-wrap'] = '';
						}



						$show_alignment = false;
						// align feature
						/*echo "supports: {";
						echo "	align: true,";
						echo "  html: false";
						echo "},";*/

						if ( ! empty( $this->arguments ) ) {
							echo "attributes : {";

							if ( $show_advanced ) {
								echo "show_advanced: {";
								echo "	type: 'boolean',";
								echo "  default: false,";
								echo "},";
							}

							// block wrap element
							if ( ! empty( $this->options['block-wrap'] ) ) { //@todo we should validate this?
								echo "block_wrap: {";
								echo "	type: 'string',";
								echo "  default: '" . esc_attr( $this->options['block-wrap'] ) . "',";
								echo "},";
							}

							foreach ( $this->arguments as $key => $args ) {

								// set if we should show alignment
								if ( $key == 'alignment' ) {
									$show_alignment = true;
								}

								$extra = '';

								if ( $args['type'] == 'checkbox' ) {
									$type    = 'boolean';
									$default = isset( $args['default'] ) && $args['default'] ? 'true' : 'false';
								} elseif ( $args['type'] == 'number' ) {
									$type    = 'number';
									$default = isset( $args['default'] ) ? "'" . $args['default'] . "'" : "''";
								} elseif ( $args['type'] == 'select' && ! empty( $args['multiple'] ) ) {
									$type = 'array';
									if ( isset( $args['default'] ) && is_array( $args['default'] ) ) {
										$default = ! empty( $args['default'] ) ? "['" . implode( "','", $args['default'] ) . "']" : "[]";
									} else {
										$default = isset( $args['default'] ) ? "'" . $args['default'] . "'" : "''";
									}
								} elseif ( $args['type'] == 'multiselect' ) {
									$type    = 'array';
									$default = isset( $args['default'] ) ? "'" . $args['default'] . "'" : "''";
								} else {
									$type    = 'string';
									$default = isset( $args['default'] ) ? "'" . $args['default'] . "'" : "''";
								}
								echo $key . " : {";
								echo "type : '$type',";
								echo "default : $default,";
								echo "},";
							}

							echo "content : {type : 'string',default: 'Please select the attributes in the block settings'},";
							echo "className: { type: 'string', default: '' },";

							echo "},";

						}

						?>

						// The "edit" property must be a valid function.
						edit: function (props) {


							var $value = '';
							<?php
							// if we have a post_type and a category then link them
							if( isset($this->arguments['post_type']) && isset($this->arguments['category']) && !empty($this->arguments['category']['post_type_linked']) ){
							?>
							if(typeof(prev_attributes[props.id]) != 'undefined' ){
								$pt = props.attributes.post_type;
								if(post_type_rest_slugs.length){
									$value = post_type_rest_slugs[0][$pt];
								}
								var run = false;

								if($pt != term_query_type){
									run = true;
									term_query_type = $pt;
								}

								// taxonomies
								if( $value && 'post_type' in prev_attributes[props.id] && 'category' in prev_attributes[props.id] && run ){
									wp.apiFetch({path: "<?php if(isset($this->arguments['post_type']['onchange_rest']['path'])){echo $this->arguments['post_type']['onchange_rest']['path'];}else{'/wp/v2/"+$value+"/categories/?per_page=100';} ?>"}).then(terms => {
										while (taxonomies_<?php echo str_replace("-","_", $this->id);?>.length) {
										taxonomies_<?php echo str_replace("-","_", $this->id);?>.pop();
									}
									taxonomies_<?php echo str_replace("-","_", $this->id);?>.push({label: "All", value: 0});
									jQuery.each( terms, function( key, val ) {
										taxonomies_<?php echo str_replace("-","_", $this->id);?>.push({label: val.name, value: val.id});
									});

									// setting the value back and fourth fixes the no update issue that sometimes happens where it won't update the options.
									var $old_cat_value = props.attributes.category
									props.setAttributes({category: [0] });
									props.setAttributes({category: $old_cat_value });

									return taxonomies_<?php echo str_replace("-","_", $this->id);?>;
								});
								}

								// sort_by
								if( $value && 'post_type' in prev_attributes[props.id] && 'sort_by' in prev_attributes[props.id] && run ){
									var data = {
										'action': 'geodir_get_sort_options',
										'post_type': $pt
									};
									jQuery.post(ajaxurl, data, function(response) {
										response = JSON.parse(response);
										while (sort_by_<?php echo str_replace("-","_", $this->id);?>.length) {
											sort_by_<?php echo str_replace("-","_", $this->id);?>.pop();
										}

										jQuery.each( response, function( key, val ) {
											sort_by_<?php echo str_replace("-","_", $this->id);?>.push({label: val, value: key});
										});

										// setting the value back and fourth fixes the no update issue that sometimes happens where it won't update the options.
										var $old_sort_by_value = props.attributes.sort_by
										props.setAttributes({sort_by: [0] });
										props.setAttributes({sort_by: $old_sort_by_value });

										return sort_by_<?php echo str_replace("-","_", $this->id);?>;
									});

								}
							}
							<?php }?>


							var content = props.attributes.content;

							function onChangeContent() {

								$refresh = false;

								// Set the old content the same as the new one so we only compare all other attributes
								if(typeof(prev_attributes[props.id]) != 'undefined'){
									prev_attributes[props.id].content = props.attributes.content;
								}else if(props.attributes.content === ""){
									// if first load and content empty then refresh
									$refresh = true;
								}

								if ( ( !is_fetching &&  JSON.stringify(prev_attributes[props.id]) != JSON.stringify(props.attributes) ) || $refresh  ) {

									is_fetching = true;
									var data = {
										'action': 'super_duper_output_shortcode',
										'shortcode': '<?php echo $this->options['base_id'];?>',
										'attributes': props.attributes,
										'post_id': <?php global $post; if ( isset( $post->ID ) ) {
										echo $post->ID;
									}else{echo '0';}?>,
										'_ajax_nonce': '<?php echo wp_create_nonce( 'super_duper_output_shortcode' );?>'
									};

									jQuery.post(ajaxurl, data, function (response) {
										return response;
									}).then(function (env) {

										// if the content is empty then we place some placeholder text
										if (env == '') {
											env = "<div style='background:#0185ba33;padding: 10px;border: 4px #ccc dashed;'>" + "<?php _e( 'Placeholder for: ' );?>" + props.name + "</div>";
										}

										props.setAttributes({content: env});
										is_fetching = false;
										prev_attributes[props.id] = props.attributes;

										// if AUI is active call the js init function
										if (typeof aui_init === "function") {
											aui_init();
										}
									});


								}

								return props.attributes.content;

							}

							return [

								el(wp.blockEditor.BlockControls, {key: 'controls'},

									<?php if($show_alignment){?>
									el(
										wp.blockEditor.AlignmentToolbar,
										{
											value: props.attributes.alignment,
											onChange: function (alignment) {
												props.setAttributes({alignment: alignment})
											}
										}
									)
									<?php }?>

								),

								el(wp.blockEditor.InspectorControls, {key: 'inspector'},

									<?php

									if(! empty( $this->arguments )){

									if ( $show_advanced ) {
									?>
									el('div', {
											style: {'padding-left': '16px','padding-right': '16px'}
										},
										el(
											wp.components.ToggleControl,
											{
												label: 'Show Advanced Settings?',
												checked: props.attributes.show_advanced,
												onChange: function (show_advanced) {
													props.setAttributes({show_advanced: !props.attributes.show_advanced})
												}
											}
										)
									)
									,
									<?php

									}

									$arguments = $this->group_arguments( $this->arguments );

									// Do we have sections?
									$has_sections = $arguments == $this->arguments ? false : true;


									if($has_sections){
									$panel_count = 0;
									foreach($arguments as $key => $args){
									?>
									el(wp.components.PanelBody, {
											title: '<?php esc_attr_e( $key ); ?>',
											initialOpen: <?php if ( $panel_count ) {
											echo "false";
										} else {
											echo "true";
										}?>
										},
										<?php



										foreach ( $args as $k => $a ) {

											$this->block_row_start( $k, $a );
											$this->build_block_arguments( $k, $a );
											$this->block_row_end( $k, $a );
										}
										?>
									),
									<?php
									$panel_count ++;

									}
									}else {
									?>
									el(wp.components.PanelBody, {
											title: '<?php esc_attr_e( "Settings" ); ?>',
											initialOpen: true
										},
										<?php
										foreach ( $this->arguments as $key => $args ) {
											$this->block_row_start( $key, $args );
											$this->build_block_arguments( $key, $args );
											$this->block_row_end( $key, $args );
										}
										?>
									),
									<?php
									}

									}
									?>

								),

								<?php
								// If the user sets block-output array then build it
								if ( ! empty( $this->options['block-output'] ) ) {
								$this->block_element( $this->options['block-output'] );
							}else{
								// if no block-output is set then we try and get the shortcode html output via ajax.
								?>
								el('div', {
									dangerouslySetInnerHTML: {__html: onChangeContent()},
									className: props.className,
									style: {'minHeight': '30px'}
								})
								<?php
								}
								?>
							]; // end return
						},

						// The "save" property must be specified and must be a valid function.
						save: function (props) {

							//console.log(props);


							var attr = props.attributes;
							var align = '';

							// build the shortcode.
							var content = "[<?php echo $this->options['base_id'];?>";
							$html = '';
							<?php

							if(! empty( $this->arguments )){

							foreach($this->arguments as $key => $args){
							?>
							if (attr.hasOwnProperty("<?php echo esc_attr( $key );?>")) {
								if ('<?php echo esc_attr( $key );?>' == 'html') {
									$html = attr.<?php echo esc_attr( $key );?>;
								} else {
									content += " <?php echo esc_attr( $key );?>='" + attr.<?php echo esc_attr( $key );?>+ "' ";
								}
							}
							<?php
							}
							}

							?>
							content += "]";

							// if has html element
							if ($html) {
								content += $html + "[/<?php echo $this->options['base_id'];?>]";
							}


							// @todo should we add inline style here or just css classes?
							if (attr.alignment) {
								if (attr.alignment == 'left') {
									align = 'alignleft';
								}
								if (attr.alignment == 'center') {
									align = 'aligncenter';
								}
								if (attr.alignment == 'right') {
									align = 'alignright';
								}
							}

							<?php
							if(isset( $this->options['block-wrap'] ) && $this->options['block-wrap'] == ''){
							?>
							return content;
							<?php
							}else{
							?>
							var block_wrap = 'div';
							if (attr.hasOwnProperty("block_wrap")) {
								block_wrap = attr.block_wrap;
							}
							return el(block_wrap, {dangerouslySetInnerHTML: {__html: content}, className: align});
							<?php
							}
							?>


						}
					});
				})();
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

		public function block_row_start($key, $args){

			// check for row
			if(!empty($args['row'])){

				if(!empty($args['row']['open'])){

				// element require
				$element_require = ! empty( $args['element_require'] ) ? $this->block_props_replace( $args['element_require'], true ) . " && " : "";
				echo $element_require;

					if(false){?><script><?php }?>
						el('div', {
								className: 'bsui components-base-control',
							},
							<?php if(!empty($args['row']['title'])){ ?>
							el('label', {
									className: 'components-base-control__label',
								},
								'<?php echo addslashes( $args['row']['title'] ); ?>'
							),
							<?php }?>
							<?php if(!empty($args['row']['desc'])){ ?>
							el('p', {
									className: 'components-base-control__help mb-0',
								},
								'<?php echo addslashes( $args['row']['desc'] ); ?>'
							),
							<?php }?>
							el(
								'div',
								{
									className: 'row mb-n2 <?php if(!empty($args['row']['class'])){ echo esc_attr($args['row']['class']);} ?>',
								},
								el(
									'div',
									{
										className: 'col pr-2',
									},

					<?php
					if(false){?></script><?php }
				}elseif(!empty($args['row']['close'])){
					if(false){?><script><?php }?>
						el(
							'div',
							{
								className: 'col pl-0',
							},
					<?php
					if(false){?></script><?php }
				}else{
					if(false){?><script><?php }?>
						el(
							'div',
							{
								className: 'col pl-0 pr-2',
							},
					<?php
					if(false){?></script><?php }
				}

			}

		}

		public function block_row_end($key, $args){

			if(!empty($args['row'])){
				// maybe close
				if(!empty($args['row']['close'])){
					echo "))";
				}

				echo "),";
			}
		}

		public function build_block_arguments( $key, $args ) {
			$custom_attributes = ! empty( $args['custom_attributes'] ) ? $this->array_to_attributes( $args['custom_attributes'] ) : '';
			$options           = '';
			$extra             = '';
			$require           = '';

			// `content` is a protected and special argument
			if ( $key == 'content' ) {
				return;
			}


			// icon
			$icon = '';
			if( !empty( $args['icon'] ) ){
				$icon .= "el('div', {";
									$icon .= "dangerouslySetInnerHTML: {__html: '".self::get_widget_icon( esc_attr($args['icon']))."'},";
									$icon .= "className: 'text-center',";
									$icon .= "title: '".addslashes( $args['title'] )."',";
								$icon .= "}),";

				// blank title as its added to the icon.
				$args['title'] = '';
			}

			// require advanced
			$require_advanced = ! empty( $args['advanced'] ) ? "props.attributes.show_advanced && " : "";

			// element require
			$element_require = ! empty( $args['element_require'] ) ? $this->block_props_replace( $args['element_require'], true ) . " && " : "";


			$onchange  = "props.setAttributes({ $key: $key } )";
			$onchangecomplete  = "";
			$value     = "props.attributes.$key";
			$text_type = array( 'text', 'password', 'number', 'email', 'tel', 'url', 'colorx' );
			if ( in_array( $args['type'], $text_type ) ) {
				$type = 'TextControl';
				// Save numbers as numbers and not strings
				if ( $args['type'] == 'number' ) {
					$onchange = "props.setAttributes({ $key: Number($key) } )";
				}
			}
			/*
			 * https://www.wptricks.com/question/set-current-tab-on-a-gutenberg-tabpanel-component-from-outside-that-component/ es5 layout
						elseif($args['type']=='tabs'){
							?>
							<script>
								el(
									wp.components.TabPanel,
									{
										tabs: [
											{
												name: 'show',
												title: __( 'Show', 'my-textdomain' ),
											},
											{
												name: 'edit',
												title: __( 'Edit', 'my-textdomain' ),
											},
										],
									},
									( tab ) => {

									if('show' === tab.name){
									return 123;
								}else if ( 'edit' === tab.name ) {
									return 321;
								}

								}
								),
							</script>
							<?php
							return;
						}
			*/
			elseif ( $args['type'] == 'color' ) {
				$type = 'ColorPicker';
				$onchange = "";
				$extra = "color: $value,";
				if(!empty($args['disable_alpha'])){
					$extra .= "disableAlpha: true,";
				}
				$onchangecomplete = "onChangeComplete: function($key) {
				value =  $key.rgb.a && $key.rgb.a < 1 ? \"rgba(\"+$key.rgb.r+\",\"+$key.rgb.g+\",\"+$key.rgb.b+\",\"+$key.rgb.a+\")\" : $key.hex;
                        props.setAttributes({
                            $key: value
                        });
                    },";
			}
			elseif ( $args['type'] == 'checkbox' ) {
				$type = 'CheckboxControl';
				$extra .= "checked: props.attributes.$key,";
				$onchange = "props.setAttributes({ $key: ! props.attributes.$key } )";
			} elseif ( $args['type'] == 'textarea' ) {
				$type = 'TextareaControl';
			} elseif ( $args['type'] == 'select' || $args['type'] == 'multiselect' ) {
				$type = 'SelectControl';

				if($args['name'] == 'category' && !empty($args['post_type_linked'])){
					$options .= "options: taxonomies_".str_replace("-","_", $this->id).",";
				}elseif($args['name'] == 'sort_by' && !empty($args['post_type_linked'])){
					$options .= "options: sort_by_".str_replace("-","_", $this->id).",";
				}else {

					if ( ! empty( $args['options'] ) ) {
						$options .= "options: [";
						foreach ( $args['options'] as $option_val => $option_label ) {
							$options .= "{ value: '" . esc_attr( $option_val ) . "', label: '" . addslashes( $option_label ) . "' },";
						}
						$options .= "],";
					}
				}
				if ( isset( $args['multiple'] ) && $args['multiple'] ) { //@todo multiselect does not work at the moment: https://github.com/WordPress/gutenberg/issues/5550
					$extra .= ' multiple: true, ';
				}
			} elseif ( $args['type'] == 'alignment' ) {
				$type = 'AlignmentToolbar'; // @todo this does not seem to work but cant find a example
			}elseif ( $args['type'] == 'margins' ) {

			} else {
				return;// if we have not implemented the control then don't break the JS.
			}



			// color input does not show the labels so we add them
			if($args['type']=='color'){
				// add show only if advanced
				echo $require_advanced;
				// add setting require if defined
				echo $element_require;
				echo "el('div', {style: {'marginBottom': '8px'}}, '".addslashes( $args['title'] )."'),";
			}

			// add show only if advanced
			echo $require_advanced;
			// add setting require if defined
			echo $element_require;

			// icon
			echo $icon;
			?>
			el( wp.components.<?php echo $type; ?>, {
			label: '<?php echo addslashes( $args['title'] ); ?>',
			help: '<?php if ( isset( $args['desc'] ) ) {
				echo addslashes( $args['desc'] );
			} ?>',
			value: <?php echo $value; ?>,
			<?php if ( $type == 'TextControl' && $args['type'] != 'text' ) {
				echo "type: '" . addslashes( $args['type'] ) . "',";
			} ?>
			<?php if ( ! empty( $args['placeholder'] ) ) {
				echo "placeholder: '" . addslashes( $args['placeholder'] ) . "',";
			} ?>
			<?php echo $options; ?>
			<?php echo $extra; ?>
			<?php echo $custom_attributes; ?>
			<?php echo $onchangecomplete;?>
			onChange: function ( <?php echo $key; ?> ) {
			<?php echo $onchange; ?>
			}
			} ),
			<?php


		}

		/**
		 * Convert an array of attributes to block string.
		 *
		 * @todo there is prob a faster way to do this, also we could add some validation here.
		 *
		 * @param $custom_attributes
		 *
		 * @return string
		 */
		public function array_to_attributes( $custom_attributes, $html = false ) {
			$attributes = '';
			if ( ! empty( $custom_attributes ) ) {

				if ( $html ) {
					foreach ( $custom_attributes as $key => $val ) {
						$attributes .= " $key='$val' ";
					}
				} else {
					foreach ( $custom_attributes as $key => $val ) {
						$attributes .= "'$key': '$val',";
					}
				}
			}

			return $attributes;
		}

		/**
		 * A self looping function to create the output for JS block elements.
		 *
		 * This is what is output in the WP Editor visual view.
		 *
		 * @param $args
		 */
		public function block_element( $args ) {


			if ( ! empty( $args ) ) {
				foreach ( $args as $element => $new_args ) {

					if ( is_array( $new_args ) ) { // its an element


						if ( isset( $new_args['element'] ) ) {

							if ( isset( $new_args['element_require'] ) ) {
								echo str_replace( array(
										"'+",
										"+'"
									), '', $this->block_props_replace( $new_args['element_require'] ) ) . " &&  ";
								unset( $new_args['element_require'] );
							}

							echo "\n el( '" . $new_args['element'] . "', {";

							// get the attributes
							foreach ( $new_args as $new_key => $new_value ) {


								if ( $new_key == 'element' || $new_key == 'content' || $new_key == 'element_require' || $new_key == 'element_repeat' || is_array( $new_value ) ) {
									// do nothing
								} else {
									echo $this->block_element( array( $new_key => $new_value ) );
								}
							}

							echo "},";// end attributes

							// get the content
							$first_item = 0;
							foreach ( $new_args as $new_key => $new_value ) {
								if ( $new_key === 'content' || is_array( $new_value ) ) {

									if ( $new_key === 'content' ) {
										echo "'" . $this->block_props_replace( wp_slash( $new_value ) ) . "'";
									}

									if ( is_array( $new_value ) ) {

										if ( isset( $new_value['element_require'] ) ) {
											echo str_replace( array(
													"'+",
													"+'"
												), '', $this->block_props_replace( $new_value['element_require'] ) ) . " &&  ";
											unset( $new_value['element_require'] );
										}

										if ( isset( $new_value['element_repeat'] ) ) {
											$x = 1;
											while ( $x <= absint( $new_value['element_repeat'] ) ) {
												$this->block_element( array( '' => $new_value ) );
												$x ++;
											}
										} else {
											$this->block_element( array( '' => $new_value ) );
										}
									}
									$first_item ++;
								}
							}

							echo ")";// end content

							echo ", \n";

						}
					} else {

						if ( substr( $element, 0, 3 ) === "if_" ) {
							echo str_replace( "if_", "", $element ) . ": " . $this->block_props_replace( $new_args, true ) . ",";
						} elseif ( $element == 'style' ) {
							echo $element . ": " . $this->block_props_replace( $new_args ) . ",";
						} else {
							echo $element . ": '" . $this->block_props_replace( $new_args ) . "',";
						}

					}
				}
			}
		}

		/**
		 * Replace block attributes placeholders with the proper naming.
		 *
		 * @param $string
		 *
		 * @return mixed
		 */
		public function block_props_replace( $string, $no_wrap = false ) {

			if ( $no_wrap ) {
				$string = str_replace( array( "[%", "%]" ), array( "props.attributes.", "" ), $string );
			} else {
				$string = str_replace( array( "[%", "%]" ), array( "'+props.attributes.", "+'" ), $string );
			}

			return $string;
		}

		/**
		 * Outputs the content of the widget
		 *
		 * @param array $args
		 * @param array $instance
		 */
		public function widget( $args, $instance ) {

			// get the filtered values
			$argument_values = $this->argument_values( $instance );
			$argument_values = $this->string_to_bool( $argument_values );
			$output          = $this->output( $argument_values, $args );

			$no_wrap = false;
			if ( isset( $argument_values['no_wrap'] ) && $argument_values['no_wrap'] ) {
				$no_wrap = true;
			}

			ob_start();
			if ( $output && ! $no_wrap ) {

				$class_original = $this->options['widget_ops']['classname'];
				$class = $this->options['widget_ops']['classname']." sdel-".$this->get_instance_hash();

				// Before widget
				$before_widget = $args['before_widget'];
				$before_widget = str_replace($class_original,$class,$before_widget);
				$before_widget = apply_filters( 'wp_super_duper_before_widget', $before_widget, $args, $instance, $this );
				$before_widget = apply_filters( 'wp_super_duper_before_widget_' . $this->base_id, $before_widget, $args, $instance, $this );

				// After widget
				$after_widget = $args['after_widget'];
				$after_widget = apply_filters( 'wp_super_duper_after_widget', $after_widget, $args, $instance, $this );
				$after_widget = apply_filters( 'wp_super_duper_after_widget_' . $this->base_id, $after_widget, $args, $instance, $this );

				echo $before_widget;
				// elementor strips the widget wrapping div so we check for and add it back if needed
				if ( $this->is_elementor_widget_output() ) {
					// Filter class & attrs for elementor widget output.
					$class = apply_filters( 'wp_super_duper_div_classname', $class, $args, $this );
					$class = apply_filters( 'wp_super_duper_div_classname_' . $this->base_id, $class, $args, $this );

					$attrs = apply_filters( 'wp_super_duper_div_attrs', '', $args, $this );
					$attrs = apply_filters( 'wp_super_duper_div_attrs_' . $this->base_id, '', $args, $this );

					echo "<span class='" . esc_attr( $class  ) . "' " . $attrs . ">";
				}
				echo $this->output_title( $args, $instance );
				echo $output;
				if ( $this->is_elementor_widget_output() ) {
					echo "</span>";
				}
				echo $after_widget;
			} elseif ( $this->is_preview() && $output == '' ) {// if preview show a placeholder if empty
				$output = $this->preview_placeholder_text( "{{" . $this->base_id . "}}" );
				echo $output;
			} elseif ( $output && $no_wrap ) {
				echo $output;
			}
			$output = ob_get_clean();

			$output = apply_filters( 'wp_super_duper_widget_output', $output, $instance, $args, $this );

			echo $output;
		}

		/**
		 * Tests if the current output is inside a elementor container.
		 *
		 * @since 1.0.4
		 * @return bool
		 */
		public function is_elementor_widget_output() {
			$result = false;
			if ( defined( 'ELEMENTOR_VERSION' ) && isset( $this->number ) && $this->number == 'REPLACE_TO_ID' ) {
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
		 * General function to check if we are in a preview situation.
		 *
		 * @since 1.0.6
		 * @return bool
		 */
		public function is_preview() {
			$preview = false;
			if ( $this->is_divi_preview() ) {
				$preview = true;
			} elseif ( $this->is_elementor_preview() ) {
				$preview = true;
			} elseif ( $this->is_beaver_preview() ) {
				$preview = true;
			} elseif ( $this->is_siteorigin_preview() ) {
				$preview = true;
			} elseif ( $this->is_cornerstone_preview() ) {
				$preview = true;
			} elseif ( $this->is_fusion_preview() ) {
				$preview = true;
			} elseif ( $this->is_oxygen_preview() ) {
				$preview = true;
			} elseif( $this->is_block_content_call() ) {
				$preview = true;
			}

			return $preview;
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
				$title  = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );

				if(empty($instance['widget_title_tag'])){
					$output = $args['before_title'] . $title . $args['after_title'];
				}else{
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

					$class = !empty( $title_classes ) ? implode(" ",$title_classes) : '';
					$output = "<$title_tag class='$class' >$title</$title_tag>";
				}

			}

			return $output;
		}

		/**
		 * Outputs the options form inputs for the widget.
		 *
		 * @param array $instance The widget options.
		 */
		public function form( $instance ) {

			// set widget instance
			$this->instance = $instance;

			// set it as a SD widget
			echo $this->widget_advanced_toggle();

			echo "<p>" . esc_attr( $this->options['widget_ops']['description'] ) . "</p>";
			$arguments_raw = $this->get_arguments();

			if ( is_array( $arguments_raw ) ) {

				$arguments = $this->group_arguments( $arguments_raw );

				// Do we have sections?
				$has_sections = $arguments == $arguments_raw ? false : true;


				if ( $has_sections ) {
					$panel_count = 0;
					foreach ( $arguments as $key => $args ) {

						?>
						<script>
							//							jQuery(this).find("i").toggleClass("fas fa-chevron-up fas fa-chevron-down");jQuery(this).next().toggle();
						</script>
						<?php

						$hide       = $panel_count ? ' style="display:none;" ' : '';
						$icon_class = $panel_count ? 'fas fa-chevron-up' : 'fas fa-chevron-down';
						echo "<button onclick='jQuery(this).find(\"i\").toggleClass(\"fas fa-chevron-up fas fa-chevron-down\");jQuery(this).next().slideToggle();' type='button' class='sd-toggle-group-button sd-input-group-toggle" . sanitize_title_with_dashes( $key ) . "'>" . esc_attr( $key ) . " <i style='float:right;' class='" . $icon_class . "'></i></button>";
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
		 * Get the hidden input that when added makes the advanced button show on widget settings.
		 *
		 * @return string
		 */
		public function widget_advanced_toggle() {

			$output = '';
			if ( $this->block_show_advanced() ) {
				$val = 1;
			} else {
				$val = 0;
			}

			$output .= "<input type='hidden'  class='sd-show-advanced' value='$val' />";

			return $output;
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

		public function get_widget_icon($icon = 'box-top', $title = ''){
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
					$title = self::get_widget_icon( $args['icon'], $args['title']  );
				} else {
					$title = esc_attr($args['title']);
				}
			}

			return $title;
		}

		/**
		 * Get the tool tip html.
		 *
		 * @param $tip
		 * @param bool $allow_html
		 *
		 * @return string
		 */
		function desc_tip( $tip, $allow_html = false ) {
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
		 * Processing widget options on save
		 *
		 * @param array $new_instance The new options
		 * @param array $old_instance The previous options
		 *
		 * @return array
		 * @todo we should add some sanitation here.
		 */
		public function update( $new_instance, $old_instance ) {

			//save the widget
			$instance = array_merge( (array) $old_instance, (array) $new_instance );

			// set widget instance
			$this->instance = $instance;

			if ( empty( $this->arguments ) ) {
				$this->get_arguments();
			}

			// check for checkboxes
			if ( ! empty( $this->arguments ) ) {
				foreach ( $this->arguments as $argument ) {
					if ( isset( $argument['type'] ) && $argument['type'] == 'checkbox' && ! isset( $new_instance[ $argument['name'] ] ) ) {
						$instance[ $argument['name'] ] = '0';
					}
				}
			}

			return $instance;
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
		 * Get an instance hash that will be unique to the type and settings.
		 *
		 * @since 1.0.20
		 * @return string
		 */
		public function get_instance_hash(){
			$instance_string = $this->base_id.serialize($this->instance);
			return hash('crc32b',$instance_string);
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
	}
}
