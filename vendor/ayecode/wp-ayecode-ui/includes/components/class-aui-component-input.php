<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * A component class for rendering a bootstrap alert.
 *
 * @since 1.0.0
 */
class AUI_Component_Input {

	/**
	 * Build the component.
	 *
	 * @param array $args
	 *
	 * @return string The rendered component.
	 */
	public static function input( $args = array() ) {
		global $aui_bs5;

		$defaults = array(
			'type'                     => 'text',
			'name'                     => '',
			'class'                    => '',
			'wrap_class'               => '',
			'id'                       => '',
			'placeholder'              => '',
			'title'                    => '',
			'value'                    => '',
			'required'                 => false,
			'size'                     => '', // sm, lg, small, large
			'clear_icon'               => '', // true will show a clear icon, can't be used with input_group_right
			'with_hidden'              => false, // Append hidden field for single checkbox.
			'label'                    => '',
			'label_after'              => false,
			'label_class'              => '',
			'label_col'                => '2',
			'label_type'               => '', // top, horizontal, empty = hidden
			'label_force_left'         => false, // used to force checkbox label left when using horizontal
			// sets the label type, default: hidden. Options: hidden, top, horizontal, floating
			'help_text'                => '',
			'validation_text'          => '',
			'validation_pattern'       => '',
			'no_wrap'                  => false,
			'input_group_right'        => '',
			'input_group_left'         => '',
			'input_group_right_inside' => false,
			// forces the input group inside the input
			'input_group_left_inside'  => false,
			// forces the input group inside the input
			'form_group_class'         => '',
			'step'                     => '',
			'switch'                   => false,
			// to show checkbox as a switch
			'checked'                  => false,
			// set a checkbox or radio as selected
			'password_toggle'          => true,
			// toggle view/hide password
			'element_require'          => '',
			// [%element_id%] == "1"
			'extra_attributes'         => array(),
			// an array of extra attributes
			'wrap_attributes'          => array()
		);

		/**
		 * Parse incoming $args into an array and merge it with $defaults
		 */
		$args   = wp_parse_args( $args, $defaults );
		$output = '';
		if ( ! empty( $args['type'] ) ) {
			// hidden label option needs to be empty
			$args['label_type'] = $args['label_type'] == 'hidden' ? '' : $args['label_type'];

			$type = sanitize_html_class( $args['type'] );

			$help_text   = '';
			$label       = '';
			$label_after = $args['label_after'];
			$label_args  = array(
				'title'      => $args['label'],
				'for'        => $args['id'],
				'class'      => $args['label_class'] . " ",
				'label_type' => $args['label_type'],
				'label_col'  => $args['label_col']
			);

			// floating labels need label after
			if ( $args['label_type'] == 'floating' && $type != 'checkbox' ) {
				$label_after         = true;
				$args['placeholder'] = ' '; // set the placeholder not empty so the floating label works.
			}

			// size
			$size = '';
			if ( $args['size'] == 'lg' || $args['size'] == 'large' ) {
				$size = 'lg';
				$args['class'] .= ' form-control-lg';
			}elseif ( $args['size'] == 'sm' || $args['size'] == 'small' ) {
				$size = 'sm';
				$args['class'] .= ' form-control-sm';
			}

			// clear function
			$clear_function = 'jQuery(this).parent().parent().find(\'input\').val(\'\');';

			// Some special sauce for files
			if ( $type == 'file' ) {
				$label_after = true; // if type file we need the label after
				$args['class'] .= ' custom-file-input ';
			} elseif ( $type == 'checkbox' ) {
				$label_after = true; // if type file we need the label after
				$args['class'] .= $aui_bs5 ? ' form-check-input' : ' custom-control-input ';
			} elseif ( $type == 'datepicker' || $type == 'timepicker' ) {
				$orig_type = $type;
				$type = 'text';
				$args['class'] .= ' bg-initial '; // @todo not sure why we have this?
				$clear_function .= "jQuery(this).parent().parent().find('input[name=\'" . esc_attr( $args['name'] ) . "\']').trigger('change');";

				$args['extra_attributes']['data-aui-init'] = 'flatpickr';

				// Disable native datetime inputs.
				$disable_mobile_attr = isset( $args['extra_attributes']['data-disable-mobile'] ) ? $args['extra_attributes']['data-disable-mobile'] : 'true';
				$disable_mobile_attr = apply_filters( 'aui_flatpickr_disable_disable_mobile_attr', $disable_mobile_attr, $args );

				$args['extra_attributes']['data-disable-mobile'] = $disable_mobile_attr;

				// set a way to clear field if empty
				if ( $args['input_group_right'] === '' && $args['clear_icon'] !== false ) {
					$args['input_group_right_inside'] = true;
					$args['clear_icon'] = true;
				}

				// enqueue the script
				$aui_settings = AyeCode_UI_Settings::instance();
				$aui_settings->enqueue_flatpickr();
			} elseif ( $type == 'iconpicker' ) {
				$type = 'text';
				//$args['class'] .= ' aui-flatpickr bg-initial ';
//				$args['class'] .= ' bg-initial ';

				$args['extra_attributes']['data-aui-init'] = 'iconpicker';
				$args['extra_attributes']['data-placement'] = 'bottomRight';

				$args['input_group_right'] = '<span class="input-group-addon input-group-text c-pointer"></span>';
//				$args['input_group_right_inside'] = true;
				// enqueue the script
				$aui_settings = AyeCode_UI_Settings::instance();
				$aui_settings->enqueue_iconpicker();
			}

			if ( $type == 'checkbox' && ( ( ! empty( $args['name'] ) && strpos( $args['name'], '[' ) === false ) || ! empty( $args['with_hidden'] ) ) ) {
				$output .= '<input type="hidden" name="' . esc_attr( $args['name'] ) . '" value="0" />';
			}

			// allow clear icon
			if ( $args['input_group_right'] === '' && $args['clear_icon'] ) {
				$font_size = $size == 'sm' ? '1.3' : ( $size == 'lg' ? '1.65' : '1.5' );
				$args['input_group_right_inside'] = true;
				$align_class = $aui_bs5 ? ' h-100 py-0' : '';
				$args['input_group_right'] = '<span class="input-group-text aui-clear-input c-pointer bg-initial border-0 px-2 d-none ' . $align_class . '" onclick="' . $clear_function . '"><span style="font-size: ' . $font_size . 'rem" aria-hidden="true" class="' . ( $aui_bs5 ? 'btn-close' : 'close' ) . '">' . ( $aui_bs5 ? '' : '&times;' ) . '</span></span>';
			}

			// open/type
			$output .= '<input type="' . $type . '" ';

			// name
			if ( ! empty( $args['name'] ) ) {
				$output .= ' name="' . esc_attr( $args['name'] ) . '" ';
			}

			// id
			if ( ! empty( $args['id'] ) ) {
				$output .= ' id="' . sanitize_html_class( $args['id'] ) . '" ';
			}

			// placeholder
			if ( isset( $args['placeholder'] ) && '' != $args['placeholder'] ) {
				$output .= ' placeholder="' . esc_attr( $args['placeholder'] ) . '" ';
			}

			// title
			if ( ! empty( $args['title'] ) ) {
				$output .= ' title="' . esc_attr( $args['title'] ) . '" ';
			}

			// value
			if ( ! empty( $args['value'] ) ) {
				$output .= AUI_Component_Helper::value( $args['value'] );
			}

			// checked, for radio and checkboxes
			if ( ( $type == 'checkbox' || $type == 'radio' ) && $args['checked'] ) {
				$output .= ' checked ';
			}

			// validation text
			if ( ! empty( $args['validation_text'] ) ) {
				$output .= ' oninvalid="setCustomValidity(\'' . esc_attr( addslashes( $args['validation_text'] ) ) . '\')" ';
				$output .= ' onchange="try{setCustomValidity(\'\')}catch(e){}" ';
			}

			// validation_pattern
			if ( ! empty( $args['validation_pattern'] ) ) {
				$output .= ' pattern="' . esc_attr( $args['validation_pattern'] ) . '" ';
			}

			// step (for numbers)
			if ( ! empty( $args['step'] ) ) {
				$output .= ' step="' . $args['step'] . '" ';
			}

			// required
			if ( ! empty( $args['required'] ) ) {
				$output .= ' required ';
			}

			// class
			$class = ! empty( $args['class'] ) ? AUI_Component_Helper::esc_classes( $args['class'] ) : '';
			$output .= $aui_bs5 &&  $type == 'checkbox' ? ' class="' . $class . '" ' : ' class="form-control ' . $class . '" ';

			// data-attributes
			$output .= AUI_Component_Helper::data_attributes( $args );

			// extra attributes
			if ( ! empty( $args['extra_attributes'] ) ) {
				$output .= AUI_Component_Helper::extra_attributes( $args['extra_attributes'] );
			}

			// close
			$output .= ' >';

			// help text
			if ( ! empty( $args['help_text'] ) ) {
				$help_text = AUI_Component_Helper::help_text( $args['help_text'] );
			}

			// label
			if ( ! empty( $args['label'] ) ) {
				$label_base_class = '';
				if ( $type == 'file' ) {
					$label_base_class = ' custom-file-label';
				} elseif ( $type == 'checkbox' ) {
					if ( ! empty( $args['label_force_left'] ) ) {
						$label_args['title'] = wp_kses_post( $args['help_text'] );
						$help_text = '';
						//$label_args['class'] .= ' d-inline ';
						$args['wrap_class'] .= ' align-items-center ';
					}else{

					}

					$label_base_class = $aui_bs5 ? ' form-check-label' : ' custom-control-label';
				}
				$label_args['class'] .= $label_base_class;
				$temp_label_args = $label_args;
				if(! empty( $args['label_force_left'] )){$temp_label_args['class'] = $label_base_class." text-muted";}
				$label = self::label( $temp_label_args, $type );
			}




			// set help text in the correct position
			if ( $label_after ) {
				$output .= $label . $help_text;
			}

			// some input types need a separate wrap
			if ( $type == 'file' ) {
				$output = self::wrap( array(
					'content' => $output,
					'class'   => $aui_bs5 ? 'mb-3 custom-file' : 'form-group custom-file'
				) );
			} elseif ( $type == 'checkbox' ) {

				$label_args['title'] = $args['label'];
				$label_col = AUI_Component_Helper::get_column_class( $args['label_col'], 'label' );
				$label = !empty( $args['label_force_left'] ) ? self::label( $label_args, 'cb' ) : '<div class="' . $label_col . ' col-form-label"></div>';
				$switch_size_class = $args['switch'] && !is_bool( $args['switch'] ) ? ' custom-switch-'.esc_attr( $args['switch'] ) : '';
				if ( $aui_bs5 ) {
					$wrap_class = $args['switch'] ? 'form-check form-switch' . $switch_size_class : 'form-check';
				}else{
					$wrap_class = $args['switch'] ? 'custom-switch' . $switch_size_class :  'custom-checkbox' ;
				}
				if ( ! empty( $args['label_force_left'] ) ) {
					$wrap_class .= $aui_bs5 ? '' : ' d-flex align-content-center';
					$label = str_replace(array("form-check-label","custom-control-label"),"", self::label( $label_args, 'cb' ) );
				}
				$output     = self::wrap( array(
					'content' => $output,
					'class'   => $aui_bs5 ? $wrap_class : 'custom-control ' . $wrap_class
				) );

				if ( $args['label_type'] == 'horizontal' ) {
					$input_col = AUI_Component_Helper::get_column_class( $args['label_col'], 'input' );
					$output    = $label . '<div class="' . $input_col . '">' . $output . '</div>';
				}
			} elseif ( $type == 'password' && $args['password_toggle'] && ! $args['input_group_right'] ) {


				// allow password field to toggle view
				$args['input_group_right'] = '<span class="input-group-text c-pointer px-3" 
onclick="var $el = jQuery(this).find(\'i\');$el.toggleClass(\'fa-eye fa-eye-slash\');
var $eli = jQuery(this).parent().parent().find(\'input\');
if($el.hasClass(\'fa-eye\'))
{$eli.attr(\'type\',\'text\');}
else{$eli.attr(\'type\',\'password\');}"
><i class="far fa-fw fa-eye-slash"></i></span>';
			}

			// input group wraps
			if ( $args['input_group_left'] || $args['input_group_right'] ) {
				$w100 = strpos( $args['class'], 'w-100' ) !== false ? ' w-100' : '';
				$group_size = $size == 'lg' ? ' input-group-lg' : '';
				$group_size = !$group_size && $size == 'sm' ? ' input-group-sm' : $group_size;

				if ( $args['input_group_left'] ) {
					$output = self::wrap( array(
						'content'                 => $output,
						'class'                   => $args['input_group_left_inside'] ? 'input-group-inside position-relative' . $w100 . $group_size : 'input-group' . $group_size,
						'input_group_left'        => $args['input_group_left'],
						'input_group_left_inside' => $args['input_group_left_inside']
					) );
				}
				if ( $args['input_group_right'] ) {
					$output = self::wrap( array(
						'content'                  => $output,
						'class'                    => $args['input_group_right_inside'] ? 'input-group-inside position-relative' . $w100 . $group_size : 'input-group' . $group_size,
						'input_group_right'        => $args['input_group_right'],
						'input_group_right_inside' => $args['input_group_right_inside']
					) );
				}

			}

			if ( ! $label_after ) {
				$output .= $help_text;
			}


			if ( $args['label_type'] == 'horizontal' && $type != 'checkbox' ) {
				$output = self::wrap( array(
					'content' => $output,
					'class'   => AUI_Component_Helper::get_column_class( $args['label_col'], 'input' )
				) );
			}

			if ( ! $label_after ) {
				$output = $label . $output;
			}

			// wrap
			if ( ! $args['no_wrap'] ) {
				if ( ! empty( $args['form_group_class'] ) ) {
					$fg_class = esc_attr( $args['form_group_class'] );
				}else{
					$fg_class = $aui_bs5 ? 'mb-3' : 'form-group';
				}
				$form_group_class = $args['label_type'] == 'floating' && $type != 'checkbox' ? 'form-label-group' : $fg_class;
				$wrap_class       = $args['label_type'] == 'horizontal' ? $form_group_class . ' row' : $form_group_class;
				$wrap_class       = ! empty( $args['wrap_class'] ) ? $wrap_class . " " . $args['wrap_class'] : $wrap_class;
				$output           = self::wrap( array(
					'content'         => $output,
					'class'           => $wrap_class,
					'element_require' => $args['element_require'],
					'argument_id'     => $args['id'],
					'wrap_attributes' => $args['wrap_attributes'],
				) );
			}
		}

		return $output;
	}

	public static function label( $args = array(), $type = '' ) {
		global $aui_bs5;
		//<label for="exampleInputEmail1">Email address</label>
		$defaults = array(
			'title'      => 'div',
			'for'        => '',
			'class'      => '',
			'label_type' => '', // empty = hidden, top, horizontal
			'label_col'  => '',
		);

		/**
		 * Parse incoming $args into an array and merge it with $defaults
		 */
		$args   = wp_parse_args( $args, $defaults );
		$output = '';

		if ( $args['title'] ) {

			// maybe hide labels //@todo set a global option for visibility class
			if ( $type == 'file' || $type == 'checkbox' || $type == 'radio' || ! empty( $args['label_type'] ) ) {
				$class = $args['class'];
			} else {
				$class = 'sr-only ' . $args['class'];
			}

			// maybe horizontal
			if ( $args['label_type'] == 'horizontal' && $type != 'checkbox' ) {
				$class .= ' ' . AUI_Component_Helper::get_column_class( $args['label_col'], 'label' ) . ' col-form-label '.$type;
			}

			if( $aui_bs5 ){ $class .= ' form-label'; }

			// open
			$output .= '<label ';

			// for
			if ( ! empty( $args['for'] ) ) {
				$output .= ' for="' . esc_attr( $args['for'] ) . '" ';
			}

			// class
			$class = $class ? AUI_Component_Helper::esc_classes( $class ) : '';
			$output .= ' class="' . $class . '" ';

			// close
			$output .= '>';


			// title, don't escape fully as can contain html
			if ( ! empty( $args['title'] ) ) {
				$output .= wp_kses_post( $args['title'] );
			}

			// close wrap
			$output .= '</label>';


		}


		return $output;
	}

	/**
	 * Wrap some content in a HTML wrapper.
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	public static function wrap( $args = array() ) {
		global $aui_bs5;
		$defaults = array(
			'type'                     => 'div',
			'class'                    => $aui_bs5 ? 'mb-3' : 'form-group',
			'content'                  => '',
			'input_group_left'         => '',
			'input_group_right'        => '',
			'input_group_left_inside'  => false,
			'input_group_right_inside' => false,
			'element_require'          => '',
			'argument_id'              => '',
			'wrap_attributes'          => array()
		);

		/**
		 * Parse incoming $args into an array and merge it with $defaults
		 */
		$args   = wp_parse_args( $args, $defaults );
		$output = '';
		if ( $args['type'] ) {

			// open
			$output .= '<' . sanitize_html_class( $args['type'] );

			// element require
			if ( ! empty( $args['element_require'] ) ) {
				$output .= AUI_Component_Helper::element_require( $args['element_require'] );
				$args['class'] .= " aui-conditional-field";
			}

			// argument_id
			if ( ! empty( $args['argument_id'] ) ) {
				$output .= ' data-argument="' . esc_attr( $args['argument_id'] ) . '"';
			}

			// class
			$class = ! empty( $args['class'] ) ? AUI_Component_Helper::esc_classes( $args['class'] ) : '';
			$output .= ' class="' . $class . '" ';

			// Attributes
			if ( ! empty( $args['wrap_attributes'] ) ) {
				$output .= AUI_Component_Helper::extra_attributes( $args['wrap_attributes'] );
			}

			// close wrap
			$output .= ' >';


			// Input group left
			if ( ! empty( $args['input_group_left'] ) ) {
				$position_class   = ! empty( $args['input_group_left_inside'] ) ? 'position-absolute h-100' : '';
				$input_group_left = strpos( $args['input_group_left'], '<' ) !== false ? $args['input_group_left'] : '<span class="input-group-text">' . $args['input_group_left'] . '</span>';
				$output .= $aui_bs5 ? $input_group_left : '<div class="input-group-prepend ' . $position_class . '">' . $input_group_left . '</div>';
//				$output .= '<div class="input-group-prepend ' . $position_class . '">' . $input_group_left . '</div>';
			}

			// content
			$output .= $args['content'];

			// Input group right
			if ( ! empty( $args['input_group_right'] ) ) {
				$position_class    = ! empty( $args['input_group_right_inside'] ) ? 'position-absolute h-100' : '';
				$input_group_right = strpos( $args['input_group_right'], '<' ) !== false ? $args['input_group_right'] : '<span class="input-group-text">' . $args['input_group_right'] . '</span>';
				$output .= $aui_bs5 ? str_replace( 'input-group-text','input-group-text top-0 end-0', $input_group_right ) : '<div class="input-group-append ' . $position_class . '" style="top:0;right:0;">' . $input_group_right . '</div>';
//				$output .= '<div class="input-group-append ' . $position_class . '" style="top:0;right:0;">' . $input_group_right . '</div>';
			}


			// close wrap
			$output .= '</' . sanitize_html_class( $args['type'] ) . '>';


		} else {
			$output = $args['content'];
		}

		return $output;
	}

	/**
	 * Build the component.
	 *
	 * @param array $args
	 *
	 * @return string The rendered component.
	 */
	public static function textarea( $args = array() ) {
		global $aui_bs5;

		$defaults = array(
			'name'               => '',
			'class'              => '',
			'wrap_class'         => '',
			'id'                 => '',
			'placeholder'        => '',
			'title'              => '',
			'value'              => '',
			'required'           => false,
			'label'              => '',
			'label_after'        => false,
			'label_class'        => '',
			'label_type'         => '',
			'label_col'          => '',
			// sets the label type, default: hidden. Options: hidden, top, horizontal, floating
			'input_group_right'        => '',
			'input_group_left'         => '',
			'input_group_right_inside' => false,
			'form_group_class'      => '',
			'help_text'          => '',
			'validation_text'    => '',
			'validation_pattern' => '',
			'no_wrap'            => false,
			'rows'               => '',
			'wysiwyg'            => false,
			'allow_tags'         => false,
			// Allow HTML tags
			'element_require'    => '',
			// [%element_id%] == "1"
			'extra_attributes'   => array(),
			// an array of extra attributes
			'wrap_attributes'    => array(),
		);

		/**
		 * Parse incoming $args into an array and merge it with $defaults
		 */
		$args   = wp_parse_args( $args, $defaults );
		$output = '';
		$label = '';

		// hidden label option needs to be empty
		$args['label_type'] = $args['label_type'] == 'hidden' ? '' : $args['label_type'];

		// floating labels don't work with wysiwyg so set it as top
		if ( $args['label_type'] == 'floating' && ! empty( $args['wysiwyg'] ) ) {
			$args['label_type'] = 'top';
		}

		$label_after = $args['label_after'];

		// floating labels need label after
		if ( $args['label_type'] == 'floating' && empty( $args['wysiwyg'] ) ) {
			$label_after         = true;
			$args['placeholder'] = ' '; // set the placeholder not empty so the floating label works.
		}

		// label
		if ( ! empty( $args['label'] ) && is_array( $args['label'] ) ) {
		} elseif ( ! empty( $args['label'] ) && ! $label_after ) {
			$label_args = array(
				'title'      => $args['label'],
				'for'        => $args['id'],
				'class'      => $args['label_class'] . " ",
				'label_type' => $args['label_type'],
				'label_col'  => $args['label_col']
			);
			$label .= self::label( $label_args );
		}

		// maybe horizontal label
		if ( $args['label_type'] == 'horizontal' ) {
			$input_col = AUI_Component_Helper::get_column_class( $args['label_col'], 'input' );
			$label .= '<div class="' . $input_col . '">';
		}

		if ( ! empty( $args['wysiwyg'] ) ) {
			ob_start();
			$content   = $args['value'];
			$editor_id = ! empty( $args['id'] ) ? sanitize_html_class( $args['id'] ) : 'wp_editor';
			$settings  = array(
				'textarea_rows' => ! empty( absint( $args['rows'] ) ) ? absint( $args['rows'] ) : 4,
				'quicktags'     => false,
				'media_buttons' => false,
				'editor_class'  => 'form-control',
				'textarea_name' => ! empty( $args['name'] ) ? sanitize_html_class( $args['name'] ) : sanitize_html_class( $args['id'] ),
				'teeny'         => true,
			);

			// maybe set settings if array
			if ( is_array( $args['wysiwyg'] ) ) {
				$settings = wp_parse_args( $args['wysiwyg'], $settings );
			}

			wp_editor( $content, $editor_id, $settings );
			$output .= ob_get_clean();
		} else {

			// open
			$output .= '<textarea ';

			// name
			if ( ! empty( $args['name'] ) ) {
				$output .= ' name="' . esc_attr( $args['name'] ) . '" ';
			}

			// id
			if ( ! empty( $args['id'] ) ) {
				$output .= ' id="' . sanitize_html_class( $args['id'] ) . '" ';
			}

			// placeholder
			if ( isset( $args['placeholder'] ) && '' != $args['placeholder'] ) {
				$output .= ' placeholder="' . esc_attr( $args['placeholder'] ) . '" ';
			}

			// title
			if ( ! empty( $args['title'] ) ) {
				$output .= ' title="' . esc_attr( $args['title'] ) . '" ';
			}

			// validation text
			if ( ! empty( $args['validation_text'] ) ) {
				$output .= ' oninvalid="setCustomValidity(\'' . esc_attr( addslashes( $args['validation_text'] ) ) . '\')" ';
				$output .= ' onchange="try{setCustomValidity(\'\')}catch(e){}" ';
			}

			// validation_pattern
			if ( ! empty( $args['validation_pattern'] ) ) {
				$output .= ' pattern="' . esc_attr( $args['validation_pattern'] ) . '" ';
			}

			// required
			if ( ! empty( $args['required'] ) ) {
				$output .= ' required ';
			}

			// rows
			if ( ! empty( $args['rows'] ) ) {
				$output .= ' rows="' . absint( $args['rows'] ) . '" ';
			}


			// class
			$class = ! empty( $args['class'] ) ? $args['class'] : '';
			$output .= ' class="form-control ' . $class . '" ';

			// extra attributes
			if ( ! empty( $args['extra_attributes'] ) ) {
				$output .= AUI_Component_Helper::extra_attributes( $args['extra_attributes'] );
			}

			// close tag
			$output .= ' >';

			// value
			if ( ! empty( $args['value'] ) ) {
				if ( ! empty( $args['allow_tags'] ) ) {
					$output .= AUI_Component_Helper::sanitize_html_field( $args['value'], $args ); // Sanitize HTML.
				} else {
					$output .= AUI_Component_Helper::sanitize_textarea_field( $args['value'] );
				}
			}

			// closing tag
			$output .= '</textarea>';


			// input group wraps
			if ( $args['input_group_left'] || $args['input_group_right'] ) {
				$w100 = strpos( $args['class'], 'w-100' ) !== false ? ' w-100' : '';
				if ( $args['input_group_left'] ) {
					$output = self::wrap( array(
						'content'                 => $output,
						'class'                   => $args['input_group_left_inside'] ? 'input-group-inside position-relative' . $w100 : 'input-group',
						'input_group_left'        => $args['input_group_left'],
						'input_group_left_inside' => $args['input_group_left_inside']
					) );
				}
				if ( $args['input_group_right'] ) {
					$output = self::wrap( array(
						'content'                  => $output,
						'class'                    => $args['input_group_right_inside'] ? 'input-group-inside position-relative' . $w100 : 'input-group',
						'input_group_right'        => $args['input_group_right'],
						'input_group_right_inside' => $args['input_group_right_inside']
					) );
				}

			}


		}

		if ( ! empty( $args['label'] ) && $label_after ) {
			$label_args = array(
				'title'      => $args['label'],
				'for'        => $args['id'],
				'class'      => $args['label_class'] . " ",
				'label_type' => $args['label_type'],
				'label_col'  => $args['label_col']
			);
			$output .= self::label( $label_args );
		}

		// help text
		if ( ! empty( $args['help_text'] ) ) {
			$output .= AUI_Component_Helper::help_text( $args['help_text'] );
		}

		if ( ! $label_after ) {
			$output = $label . $output;
		}

		// maybe horizontal label
		if ( $args['label_type'] == 'horizontal' ) {
			$output .= '</div>';
		}


		// wrap
		if ( ! $args['no_wrap'] ) {
			if ( ! empty( $args['form_group_class'] ) ) {
				$fg_class = esc_attr( $args['form_group_class'] );
			}else{
				$fg_class = $aui_bs5 ? 'mb-3' : 'form-group';
			}
			$form_group_class = $args['label_type'] == 'floating' ? 'form-label-group' : $fg_class;
			$wrap_class       = $args['label_type'] == 'horizontal' ? $form_group_class . ' row' : $form_group_class;
			$wrap_class       = ! empty( $args['wrap_class'] ) ? $wrap_class . " " . $args['wrap_class'] : $wrap_class;
			$output           = self::wrap( array(
				'content'         => $output,
				'class'           => $wrap_class,
				'element_require' => $args['element_require'],
				'argument_id'     => $args['id'],
				'wrap_attributes' => $args['wrap_attributes'],
			) );
		}


		return $output;
	}

	/**
	 * Build the component.
	 *
	 * @param array $args
	 *
	 * @return string The rendered component.
	 */
	public static function select( $args = array() ) {
		global $aui_bs5;
		$defaults = array(
			'class'            => '',
			'wrap_class'       => '',
			'id'               => '',
			'title'            => '',
			'value'            => '',
			// can be an array or a string
			'required'         => false,
			'label'            => '',
			'label_after'      => false,
			'label_type'       => '',
			'label_col'        => '',
			// sets the label type, default: hidden. Options: hidden, top, horizontal, floating
			'label_class'      => '',
			'help_text'        => '',
			'placeholder'      => '',
			'options'          => array(),
			// array or string
			'icon'             => '',
			'multiple'         => false,
			'select2'          => false,
			'no_wrap'          => false,
			'input_group_right' => '',
			'input_group_left' => '',
			'input_group_right_inside' => false, // forces the input group inside the input
			'input_group_left_inside' => false, // forces the input group inside the input
			'form_group_class'  => '',
			'element_require'  => '',
			// [%element_id%] == "1"
			'extra_attributes' => array(),
			// an array of extra attributes
			'wrap_attributes'  => array(),
		);

		/**
		 * Parse incoming $args into an array and merge it with $defaults
		 */
		$args   = wp_parse_args( $args, $defaults );
		$output = '';

		// for now lets hide floating labels
		if ( $args['label_type'] == 'floating' ) {
			$args['label_type'] = 'hidden';
		}

		// hidden label option needs to be empty
		$args['label_type'] = $args['label_type'] == 'hidden' ? '' : $args['label_type'];


		$label_after = $args['label_after'];

		// floating labels need label after
		if ( $args['label_type'] == 'floating' ) {
			$label_after         = true;
			$args['placeholder'] = ' '; // set the placeholder not empty so the floating label works.
		}

		// Maybe setup select2
		$is_select2 = false;
		if ( ! empty( $args['select2'] ) ) {
			$args['class'] .= ' aui-select2';
			$is_select2 = true;
		} elseif ( strpos( $args['class'], 'aui-select2' ) !== false ) {
			$is_select2 = true;
		}

		// select2 tags
		if ( ! empty( $args['select2'] ) && $args['select2'] === 'tags' ) { // triple equals needed here for some reason
			$args['data-tags']             = 'true';
			$args['data-token-separators'] = "[',']";
			$args['multiple']              = true;
		}

		// select2 placeholder
		if ( $is_select2 && isset( $args['placeholder'] ) && '' != $args['placeholder'] && empty( $args['data-placeholder'] ) ) {
			$args['data-placeholder'] = esc_attr( $args['placeholder'] );
			$args['data-allow-clear'] = isset( $args['data-allow-clear'] ) ? (bool) $args['data-allow-clear'] : true;
		}

		// Set hidden input to save empty value for multiselect.
		if ( ! empty( $args['multiple'] ) && ! empty( $args['name'] ) ) {
			$output .= '<input type="hidden" ' . AUI_Component_Helper::name( $args['name'] ) . ' value="" data-ignore-rule/>';
		}

		// open/type
		$output .= '<select ';

		// style
		if ( $is_select2 && !($args['input_group_left'] || $args['input_group_right'])) {
			$output .= " style='width:100%;' ";
		}

		// element require
		if ( ! empty( $args['element_require'] ) ) {
			$output .= AUI_Component_Helper::element_require( $args['element_require'] );
			$args['class'] .= " aui-conditional-field";
		}

		// class
		$class = ! empty( $args['class'] ) ? $args['class'] : '';
		$select_class = $aui_bs5 ? 'form-select ' : 'custom-select ';
		$output .= AUI_Component_Helper::class_attr( $select_class . $class );

		// name
		if ( ! empty( $args['name'] ) ) {
			$output .= AUI_Component_Helper::name( $args['name'], $args['multiple'] );
		}

		// id
		if ( ! empty( $args['id'] ) ) {
			$output .= AUI_Component_Helper::id( $args['id'] );
		}

		// title
		if ( ! empty( $args['title'] ) ) {
			$output .= AUI_Component_Helper::title( $args['title'] );
		}

		// data-attributes
		$output .= AUI_Component_Helper::data_attributes( $args );

		// aria-attributes
		$output .= AUI_Component_Helper::aria_attributes( $args );

		// extra attributes
		if ( ! empty( $args['extra_attributes'] ) ) {
			$output .= AUI_Component_Helper::extra_attributes( $args['extra_attributes'] );
		}

		// required
		if ( ! empty( $args['required'] ) ) {
			$output .= ' required ';
		}

		// multiple
		if ( ! empty( $args['multiple'] ) ) {
			$output .= ' multiple ';
		}

		// close opening tag
		$output .= ' >';

		// placeholder
		if ( isset( $args['placeholder'] ) && '' != $args['placeholder'] && ! $is_select2 ) {
			$output .= '<option value="" disabled selected hidden>' . esc_attr( $args['placeholder'] ) . '</option>';
		} elseif ( $is_select2 && ! empty( $args['placeholder'] ) ) {
			$output .= "<option></option>"; // select2 needs an empty select to fill the placeholder
		}

		// Options
		if ( ! empty( $args['options'] ) ) {

			if ( ! is_array( $args['options'] ) ) {
				$output .= $args['options']; // not the preferred way but an option
			} else {
				foreach ( $args['options'] as $val => $name ) {
					$selected = '';
					if ( is_array( $name ) ) {
						if ( isset( $name['optgroup'] ) && ( $name['optgroup'] == 'start' || $name['optgroup'] == 'end' ) ) {
							$option_label = isset( $name['label'] ) ? $name['label'] : '';

							$output .= $name['optgroup'] == 'start' ? '<optgroup label="' . esc_attr( $option_label ) . '">' : '</optgroup>';
						} else {
							$option_label = isset( $name['label'] ) ? $name['label'] : '';
							$option_value = isset( $name['value'] ) ? $name['value'] : '';
							$extra_attributes = !empty($name['extra_attributes']) ? AUI_Component_Helper::extra_attributes( $name['extra_attributes'] ) : '';
							if ( ! empty( $args['multiple'] ) && ! empty( $args['value'] ) && is_array( $args['value'] ) ) {
								$selected = in_array( $option_value, stripslashes_deep( $args['value'] ) ) ? "selected" : "";
							} elseif ( ! empty( $args['value'] ) ) {
								$selected = selected( $option_value, stripslashes_deep( $args['value'] ), false );
							} elseif ( empty( $args['value'] ) && $args['value'] === $option_value ) {
								$selected = selected( $option_value, $args['value'], false );
							}

							$output .= '<option value="' . esc_attr( $option_value ) . '" ' . $selected . ' '.$extra_attributes .'>' . $option_label . '</option>';
						}
					} else {
						if ( ! empty( $args['value'] ) ) {
							if ( is_array( $args['value'] ) ) {
								$selected = in_array( $val, $args['value'] ) ? 'selected="selected"' : '';
							} elseif ( ! empty( $args['value'] ) ) {
								$selected = selected( $args['value'], $val, false );
							}
						} elseif ( $args['value'] === $val ) {
							$selected = selected( $args['value'], $val, false );
						}
						$output .= '<option value="' . esc_attr( $val ) . '" ' . $selected . '>' . esc_attr( $name ) . '</option>';
					}
				}
			}

		}

		// closing tag
		$output .= '</select>';

		$label = '';
		$help_text = '';
		// label
		if ( ! empty( $args['label'] ) && is_array( $args['label'] ) ) {
		} elseif ( ! empty( $args['label'] ) && ! $label_after ) {
			$label_args = array(
				'title'      => $args['label'],
				'for'        => $args['id'],
				'class'      => $args['label_class'] . " ",
				'label_type' => $args['label_type'],
				'label_col'  => $args['label_col']
			);
			$label = self::label( $label_args );
		}

		// help text
		if ( ! empty( $args['help_text'] ) ) {
			$help_text = AUI_Component_Helper::help_text( $args['help_text'] );
		}

		// input group wraps
		if ( $args['input_group_left'] || $args['input_group_right'] ) {
			$w100 = strpos( $args['class'], 'w-100' ) !== false ? ' w-100' : '';
			if ( $args['input_group_left'] ) {
				$output = self::wrap( array(
					'content'                 => $output,
					'class'                   => $args['input_group_left_inside'] ? 'input-group-inside position-relative' . $w100 : 'input-group',
					'input_group_left'        => $args['input_group_left'],
					'input_group_left_inside' => $args['input_group_left_inside']
				) );
			}
			if ( $args['input_group_right'] ) {
				$output = self::wrap( array(
					'content'                  => $output,
					'class'                    => $args['input_group_right_inside'] ? 'input-group-inside position-relative' . $w100 : 'input-group',
					'input_group_right'        => $args['input_group_right'],
					'input_group_right_inside' => $args['input_group_right_inside']
				) );
			}

		}

		if ( ! $label_after ) {
			$output .= $help_text;
		}


		if ( $args['label_type'] == 'horizontal' ) {
			$output = self::wrap( array(
				'content' => $output,
				'class'   => AUI_Component_Helper::get_column_class( $args['label_col'], 'input' )
			) );
		}

		if ( ! $label_after ) {
			$output = $label . $output;
		}

		// maybe horizontal label
//		if ( $args['label_type'] == 'horizontal' ) {
//			$output .= '</div>';
//		}


		// wrap
		if ( ! $args['no_wrap'] ) {
			if ( ! empty( $args['form_group_class'] ) ) {
				$fg_class = esc_attr( $args['form_group_class'] );
			}else{
				$fg_class = $aui_bs5 ? 'mb-3' : 'form-group';
			}
			$wrap_class = $args['label_type'] == 'horizontal' ? $fg_class . ' row' : $fg_class;
			$wrap_class = ! empty( $args['wrap_class'] ) ? $wrap_class . " " . $args['wrap_class'] : $wrap_class;
			$output     = self::wrap( array(
				'content'         => $output,
				'class'           => $wrap_class,
				'element_require' => $args['element_require'],
				'argument_id'     => $args['id'],
				'wrap_attributes' => $args['wrap_attributes'],
			) );
		}


		return $output;
	}

	/**
	 * Build the component.
	 *
	 * @param array $args
	 *
	 * @return string The rendered component.
	 */
	public static function radio( $args = array() ) {
		global $aui_bs5;

		$defaults = array(
			'class'            => '',
			'wrap_class'       => '',
			'id'               => '',
			'title'            => '',
			'horizontal'       => false,
			// sets the lable horizontal
			'value'            => '',
			'label'            => '',
			'label_class'      => '',
			'label_type'       => '',
			'label_col'        => '',
			// sets the label type, default: hidden. Options: hidden, top, horizontal, floating
			'help_text'        => '',
			'inline'           => true,
			'required'         => false,
			'options'          => array(),
			'icon'             => '',
			'no_wrap'          => false,
			'element_require'  => '',
			// [%element_id%] == "1"
			'extra_attributes' => array(),
			// an array of extra attributes
			'wrap_attributes'  => array()
		);

		/**
		 * Parse incoming $args into an array and merge it with $defaults
		 */
		$args = wp_parse_args( $args, $defaults );

		// for now lets use horizontal for floating
		if ( $args['label_type'] == 'floating' ) {
			$args['label_type'] = 'horizontal';
		}

		$label_args = array(
			'title'      => $args['label'],
			'class'      => $args['label_class'] . " pt-0 ",
			'label_type' => $args['label_type'],
			'label_col'  => $args['label_col']
		);

		if ( $args['label_type'] == 'top' || $args['label_type'] == 'hidden' ) {
			$label_args['class'] .= 'd-block ';

			if ( $args['label_type'] == 'hidden' ) {
				$label_args['class'] .= 'sr-only ';
			}
		}

		$output = '';

		// label before
		if ( ! empty( $args['label'] ) ) {
			$output .= self::label( $label_args, 'radio' );
		}

		// maybe horizontal label
		if ( $args['label_type'] == 'horizontal' ) {
			$input_col = AUI_Component_Helper::get_column_class( $args['label_col'], 'input' );
			$output .= '<div class="' . $input_col . '">';
		}

		if ( ! empty( $args['options'] ) ) {
			$count = 0;
			foreach ( $args['options'] as $value => $label ) {
				$option_args            = $args;
				$option_args['value']   = $value;
				$option_args['label']   = $label;
				$option_args['checked'] = $value == $args['value'] ? true : false;
				$output .= self::radio_option( $option_args, $count );
				$count ++;
			}
		}

		// help text
		$help_text = ! empty( $args['help_text'] ) ? AUI_Component_Helper::help_text( $args['help_text'] ) : '';
		$output .= $help_text;

		// maybe horizontal label
		if ( $args['label_type'] == 'horizontal' ) {
			$output .= '</div>';
		}

		// wrap
		$fg_class = $aui_bs5 ? 'mb-3' : 'form-group';
		$wrap_class = $args['label_type'] == 'horizontal' ? $fg_class . ' row' : $fg_class;
		$wrap_class = ! empty( $args['wrap_class'] ) ? $wrap_class . " " . $args['wrap_class'] : $wrap_class;
		$output     = self::wrap( array(
			'content'         => $output,
			'class'           => $wrap_class,
			'element_require' => $args['element_require'],
			'argument_id'     => $args['id'],
			'wrap_attributes' => $args['wrap_attributes'],
		) );


		return $output;
	}

	/**
	 * Build the component.
	 *
	 * @param array $args
	 *
	 * @return string The rendered component.
	 */
	public static function radio_option( $args = array(), $count = '' ) {
		$defaults = array(
			'class'            => '',
			'id'               => '',
			'title'            => '',
			'value'            => '',
			'required'         => false,
			'inline'           => true,
			'label'            => '',
			'options'          => array(),
			'icon'             => '',
			'no_wrap'          => false,
			'extra_attributes' => array() // an array of extra attributes
		);

		/**
		 * Parse incoming $args into an array and merge it with $defaults
		 */
		$args = wp_parse_args( $args, $defaults );

		$output = '';

		// open/type
		$output .= '<input type="radio"';

		// class
		$output .= ' class="form-check-input" ';

		// name
		if ( ! empty( $args['name'] ) ) {
			$output .= AUI_Component_Helper::name( $args['name'] );
		}

		// id
		if ( ! empty( $args['id'] ) ) {
			$output .= AUI_Component_Helper::id( $args['id'] . $count );
		}

		// title
		if ( ! empty( $args['title'] ) ) {
			$output .= AUI_Component_Helper::title( $args['title'] );
		}

		// value
		if ( isset( $args['value'] ) ) {
			$output .= AUI_Component_Helper::value( $args['value'] );
		}

		// checked, for radio and checkboxes
		if ( $args['checked'] ) {
			$output .= ' checked ';
		}

		// data-attributes
		$output .= AUI_Component_Helper::data_attributes( $args );

		// aria-attributes
		$output .= AUI_Component_Helper::aria_attributes( $args );

		// extra attributes
		if ( ! empty( $args['extra_attributes'] ) ) {
			$output .= AUI_Component_Helper::extra_attributes( $args['extra_attributes'] );
		}

		// required
		if ( ! empty( $args['required'] ) ) {
			$output .= ' required ';
		}

		// close opening tag
		$output .= ' >';

		// label
		if ( ! empty( $args['label'] ) && is_array( $args['label'] ) ) {
		} elseif ( ! empty( $args['label'] ) ) {
			$output .= self::label( array(
				'title' => $args['label'],
				'for'   => $args['id'] . $count,
				'class' => 'form-check-label'
			), 'radio' );
		}

		// wrap
		if ( ! $args['no_wrap'] ) {
			$wrap_class = $args['inline'] ? 'form-check form-check-inline' : 'form-check';

			// Unique wrap class
			$uniq_class = 'fwrap';
			if ( ! empty( $args['name'] ) ) {
				$uniq_class .= '-' . $args['name'];
			} else if ( ! empty( $args['id'] ) ) {
				$uniq_class .= '-' . $args['id'];
			}

			if ( isset( $args['value'] ) || $args['value'] !== "" ) {
				$uniq_class .= '-' . $args['value'];
			} else {
				$uniq_class .= '-' . $count;
			}
			$wrap_class .= ' ' . sanitize_html_class( $uniq_class );

			$output = self::wrap( array(
				'content' => $output,
				'class'   => $wrap_class
			) );
		}

		return $output;
	}

}