<?php
/**
 * Contains the shortcode class.
 *
 * @since 2.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 *
 * The shortcode super duper class.
 *
 *
 * @since 2.0.0
 * @version 2.0.0
 */
class WP_Super_Duper_Shortcode {

	/**
	 * @var WP_Super_Duper
	 */
	protected $sd;

	/**
	 * Class constructor.
	 *
	 * @param WP_Super_Duper $super_duper
	 */
	public function __construct( $super_duper ) {

		$this->sd = $super_duper;

		// Registers the shortcode.
		add_shortcode( $this->sd->base_id, array( $this, 'shortcode_output' ) );

		// This makes the insert button work for cornerstone.
		add_action( 'wp_print_footer_scripts', array( $this, 'maybe_cornerstone_builder' ) );

		// Fusion Builder (avada) support
		if ( function_exists( 'fusion_builder_map' ) ) {
			add_action( 'init', array( $this, 'register_fusion_element' ) );
		}

		// Add shortcode insert button once
		add_action( 'media_buttons', array( $this, 'shortcode_insert_button' ) );
		add_action( 'wp_ajax_super_duper_get_widget_settings', array( $this, 'get_widget_settings' ) );

		// generatepress theme sections compatibility
		if ( function_exists( 'generate_sections_sections_metabox' ) ) {
			add_action( 'generate_sections_metabox', array( $this, 'shortcode_insert_button_script' ) );
		}

		if ( $this->sd->is_preview() ) {
			add_action( 'wp_footer', array( $this, 'shortcode_insert_button_script' ) );
			// this makes the insert button work for elementor
			add_action( 'elementor/editor/after_enqueue_scripts', array(
				$this,
				'shortcode_insert_button_script'
			) ); // for elementor
		}

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
		global $shortcode_insert_button_once;
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
	 * Output the shortcode.
	 *
	 * @param array $args
	 * @param string $content
	 *
	 * @return string
	 */
	public function shortcode_output( $args = array(), $content = '' ) {
		$args = $this->sd->argument_values( $args );

		// Clean booleans.
		$args = $this->sd->string_to_bool( $args );

		// if we have a enclosed shortcode we add it to the special `html` argument
		if ( ! empty( $content ) ) {
			$args['html'] = $content;
		}

		$class = isset( $this->sd->options['widget_ops']['classname'] ) ? esc_attr( $this->sd->options['widget_ops']['classname'] ) : '';
		$class .= " sdel-" . $this->sd->get_instance_hash();

		$class = apply_filters( 'wp_super_duper_div_classname', $class, $args, $this->sd, $this );
		$class = apply_filters( 'wp_super_duper_div_classname_' . $this->sd->base_id, $class, $args, $this->sd, $this );

		$attrs = apply_filters( 'wp_super_duper_div_attrs', '', $args, $this->sd, $this );
		$attrs = apply_filters( 'wp_super_duper_div_attrs_' . $this->sd->base_id, $attrs, $args, $this->sd, $this );

		$shortcode_args = array();
		$output         = '';
		$no_wrap        = ! empty( $this->sd->options['no_wrap'] ) || ! empty( $args['no_wrap'] );

		$main_content = $this->sd->output( $args, $shortcode_args, $content );

		if ( $main_content && ! $no_wrap ) {
			// wrap the shortcode in a div with the same class as the widget
			$output .= '<div class="' . esc_attr( $class ) . '" ' . $attrs . '>';
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
				$output .= $this->sd->output_title( $shortcode_args, $args );
			}
			$output .= $main_content;
			$output .= '</div>';
		} elseif ( $main_content && $no_wrap ) {
			$output .= $main_content;
		}

		// if preview, show a placeholder if empty
		if ( $this->sd->is_preview() && $output == '' ) {
			$output = $this->sd->preview_placeholder_text( "{{" . $this->sd->base_id . "}}" );
		}

		return apply_filters( 'wp_super_duper_widget_output', $output, $args, $shortcode_args, $this );
	}

	public function register_fusion_element() {
		$options = $this->sd->options;

		if ( $this->sd->base_id ) {

			$params = $this->get_fusion_params();

			$args = array(
				'name'            => $options['name'],
				'shortcode'       => $this->sd->base_id,
				'icon'            => $options['block-icon'] ? $options['block-icon'] : 'far fa-square',
				'allow_generator' => true,
			);

			if ( ! empty( $params ) ) {
				$args['params'] = $params;
			}

			fusion_builder_map( $args );
		}
	}

	protected function get_fusion_params() {
		$params    = array();
		$arguments = $this->sd->get_arguments();

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
	public function maybe_cornerstone_builder() {
		if ( did_action( 'cornerstone_before_boot_app' ) ) {
			self::shortcode_insert_button_script();
		}
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
			echo "<script>" . WP_Super_Duper::siteorigin_js() . "</script>";
		}
		?>
		<script>
			<?php
			if(! empty( $insert_shortcode_function )){
				echo $insert_shortcode_function;
			} else {

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
			 Set the value of elements controled via react.
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
	 * Get widget settings.
	 *
	 * @since 1.0.0
	 */
	public function get_widget_settings() {

		if ( isset( $_REQUEST['shortcode'] ) && $this->sd->base_id == sanitize_title_with_dashes( $_REQUEST['shortcode'] ) ) {

			$shortcode = sanitize_title_with_dashes( $_REQUEST['shortcode'] );

			ob_start();
			$this->sd->form( array() );
			$form = ob_get_clean();

			echo "<form id='$shortcode'>" . $form . "<div class='widget-control-save'></div></form>";
			echo "<style>" . WP_Super_Duper::widget_css() . "</style>";
			echo "<script>" . WP_Super_Duper::widget_js() . "</script>";
			exit;

		}

	}

}
