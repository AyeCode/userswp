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
	public static function input($args = array()){
		$defaults = array(
			'type'       => 'text',
			'name'       => '',
			'class'      => '',
			'id'         => '',
			'placeholder'=> '',
			'title'      => '',
			'value'      => '',
			'required'   => false,
			'label'      => '',
			'validation_text'   => '',
			'validation_pattern' => '',
			'no_wrap'    => false,
			'step'       => '',
		);

		/**
		 * Parse incoming $args into an array and merge it with $defaults
		 */
		$args   = wp_parse_args( $args, $defaults );
		$output = '';
		if ( ! empty( $args['type'] ) ) {
			$type = sanitize_html_class( $args['type'] );

			// label
			if(!empty($args['label']) && is_array($args['label'])){
			}elseif(!empty($args['label'])){
				$output .= self::label(array('title'=>$args['label'],'for'=>$args['id']));
			}

			// open/type
			$output .= '<input type="' . $type . '" ';

			// name
			if(!empty($args['name'])){
				$output .= ' name="'.sanitize_html_class($args['name']).'" ';
			}

			// id
			if(!empty($args['id'])){
				$output .= ' id="'.sanitize_html_class($args['id']).'" ';
			}

			// placeholder
			if(!empty($args['placeholder'])){
				$output .= ' placeholder="'.esc_attr($args['placeholder']).'" ';
			}

			// title
			if(!empty($args['title'])){
				$output .= ' title="'.esc_attr($args['title']).'" ';
			}

			// value
			if(!empty($args['value'])){
				$output .= ' value="'.sanitize_text_field($args['value']).'" ';
			}

			// validation text
			if(!empty($args['validation_text'])){
				$output .= ' oninvalid="setCustomValidity(\''.esc_attr($args['validation_text']).'\')" ';
				$output .= ' onchange="try{setCustomValidity(\'\')}catch(e){}" ';
			}

			// validation_pattern
			if(!empty($args['validation_pattern'])){
				$output .= ' pattern="'.$args['validation_pattern'].'" ';
			}

			// step (for numbers)
			if(!empty($args['step'])){
				$output .= ' step="'.$args['step'].'" ';
			}

			// required
			if(!empty($args['required'])){
				$output .= ' required ';
			}

			// class
			$class = !empty($args['class']) ? $args['class'] : '';
			$output .= ' class="form-control '.$class.'" ';


			// close
			$output .= ' >';




			// wrap
			if(!$args['no_wrap']){
				$output = self::wrap(array(
					'content' => $output,
				));
			}



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
	public static function textarea($args = array()){
		$defaults = array(
			'name'       => '',
			'class'      => '',
			'id'         => '',
			'placeholder'=> '',
			'title'      => '',
			'value'      => '',
			'required'   => false,
			'label'      => '',
			'validation_text'   => '',
			'validation_pattern' => '',
			'no_wrap'    => false,
			'rows'      => '',
		);

		/**
		 * Parse incoming $args into an array and merge it with $defaults
		 */
		$args   = wp_parse_args( $args, $defaults );
		$output = '';

		// label
		if(!empty($args['label']) && is_array($args['label'])){
		}elseif(!empty($args['label'])){
			$output .= self::label(array('title'=>$args['label'],'for'=>$args['id']));
		}

		// open
		$output .= '<textarea ';

		// name
		if(!empty($args['name'])){
			$output .= ' name="'.sanitize_html_class($args['name']).'" ';
		}

		// id
		if(!empty($args['id'])){
			$output .= ' id="'.sanitize_html_class($args['id']).'" ';
		}

		// placeholder
		if(!empty($args['placeholder'])){
			$output .= ' placeholder="'.esc_attr($args['placeholder']).'" ';
		}

		// title
		if(!empty($args['title'])){
			$output .= ' title="'.esc_attr($args['title']).'" ';
		}

		// validation text
		if(!empty($args['validation_text'])){
			$output .= ' oninvalid="setCustomValidity(\''.esc_attr($args['validation_text']).'\')" ';
			$output .= ' onchange="try{setCustomValidity(\'\')}catch(e){}" ';
		}

		// validation_pattern
		if(!empty($args['validation_pattern'])){
			$output .= ' pattern="'.$args['validation_pattern'].'" ';
		}

		// required
		if(!empty($args['required'])){
			$output .= ' required ';
		}

		// rows
		if(!empty($args['rows'])){
			$output .= ' rows="'.absint($args['rows']).'" ';
		}


		// class
		$class = !empty($args['class']) ? $args['class'] : '';
		$output .= ' class="form-control '.$class.'" ';


		// close tag
		$output .= ' >';

		// value
		if(!empty($args['value'])){
			$output .= sanitize_textarea_field($args['value']);
		}

		// closing tag
		$output .= '</textarea>';




		// wrap
		if(!$args['no_wrap']){
			$output = self::wrap(array(
				'content' => $output,
			));
		}





		return $output;
	}

	public static function label($args = array()){
		//<label for="exampleInputEmail1">Email address</label>
		$defaults = array(
			'title'       => 'div',
			'for'      => '',
			'class'      => '',
		);

		/**
		 * Parse incoming $args into an array and merge it with $defaults
		 */
		$args   = wp_parse_args( $args, $defaults );
		$output = '';

		if($args['title']){

			// open
			$output .= '<label ';

			// for
			if(!empty($args['for'])){
				$output .= ' for="'.sanitize_text_field($args['for']).'" ';
			}

			// class
			$class = !empty($args['class']) ? $args['class'] : '';
			$output .= ' class="sr-only '.$class.'" '; //@todo set a global option for visibility class

			// close
			$output .= '>';


			// title
			if(!empty($args['title'])){
				$output .= esc_attr($args['title']);
			}

			// close wrap
			$output .= '</label>';


		}


		return $output;
	}

	public static function wrap($args = array()){
		$defaults = array(
			'type'       => 'div',
			'class'      => '',
			'content'   => '',
		);

		/**
		 * Parse incoming $args into an array and merge it with $defaults
		 */
		$args   = wp_parse_args( $args, $defaults );
		$output = '';
		if($args['type']){

			// open
			$output .= '<'.sanitize_html_class($args['type']);

			// class
			$class = !empty($args['class']) ? $args['class'] : '';
			$output .= ' class="form-group '.$class.'" ';

			// close wrap
			$output .= ' >';

			// content
			$output .= $args['content'];



			// close wrap
			$output .= '</'.sanitize_html_class($args['type']).'>';


		}else{
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
	public static function select($args = array()){
		$defaults = array(
			'class'      => '',
			'id'         => '',
			'title'      => '',
			'value'      => '', // can be an array or a string
			'required'   => false,
			'label'      => '',
			'placeholder'=> '',
			'options'    => array(),
			'icon'       => '',
			'multiple'   => false,
			'select2'    => false,
			'no_wrap'    => false,
		);

		/**
		 * Parse incoming $args into an array and merge it with $defaults
		 */
		$args   = wp_parse_args( $args, $defaults );
		$output = '';

		// Maybe setup select2
		$is_select2 = false;
		if(!empty($args['select2'])){
			$args['class'] .= ' aui-select2';
			$is_select2 = true;
		}elseif( strpos($args['class'], 'aui-select2') !== false){
			$is_select2 = true;
		}

		// select2 tags
		if(!empty($args['select2']) && $args['select2'] == 'tags'){
			$args['data-tags'] = 'true';
			$args['data-token-separators'] = "[',']";

			$args['multiple'] = true;
		}

		// select2 placeholder
		if($is_select2 && !empty($args['placeholder']) && empty($args['data-placeholder'])){
			$args['data-placeholder'] = esc_attr($args['placeholder']);
		}

		// label
		if(!empty($args['label']) && is_array($args['label'])){
		}elseif(!empty($args['label'])){
			$output .= self::label(array('title'=>$args['label'],'for'=>$args['id']));
		}

		// open/type
		$output .= '<select ';

		// class
		$class = !empty($args['class']) ? $args['class'] : '';
		$output .= AUI_Component_Helper::class_attr('custom-select '.$class);

		// name
		if(!empty($args['name'])){
			$output .= AUI_Component_Helper::name($args['name'],$args['multiple']);
		}

		// id
		if(!empty($args['id'])){
			$output .= AUI_Component_Helper::id($args['id']);
		}

		// title
		if(!empty($args['title'])){
			$output .= AUI_Component_Helper::title($args['title']);
		}

		// data-attributes
		$output .= AUI_Component_Helper::data_attributes($args);

		// aria-attributes
		$output .= AUI_Component_Helper::aria_attributes($args);

		// required
		if(!empty($args['required'])){
			$output .= ' required ';
		}

		// multiple
		if(!empty($args['multiple'])){
			$output .= ' multiple ';
		}

		// close opening tag
		$output .= ' >';

		// placeholder
		if(!empty($args['placeholder']) && !$is_select2){
			$output .= '<option value="" disabled selected hidden>'.esc_attr($args['placeholder']).'</option>';
		}

		// Options
		if(!empty($args['options'])){
			foreach($args['options'] as $val => $name){
				$selected = '';
				if(!empty($args['value'])){
					if(is_array($args['value'])){
						$selected = in_array($val,$args['value']) ? 'selected="selected"' : '';
					}else{
						$selected = selected( $args['value'], $val, false);
					}
				}

				$output .= '<option value="'.esc_attr($val).'" '.$selected.'>'.esc_attr($name).'</option>';
			}

		}

		// closing tag
		$output .= '</select>';

		// wrap
		if(!$args['no_wrap']){
			$output = self::wrap(array(
				'content' => $output,
			));
		}


		return $output;
	}

}