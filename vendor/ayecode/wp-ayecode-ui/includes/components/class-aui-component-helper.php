<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * A class for helping render common component items.
 *
 * @since 1.0.0
 */
class AUI_Component_Helper {

	/**
	 * A component helper for generating a input name.
	 *
	 * @param $text
	 *
	 * @return string
	 */
	public static function name($text,$multiple = false){
		$output = '';

		if($text){
			$is_multiple = strpos($text, '[]') !== false || (strpos($text, '[]') === false && $multiple ) ? '[]' : '';
			$output = ' name="'.sanitize_html_class($text).$is_multiple.'" ';
		}

		return $output;
	}

	/**
	 * A component helper for generating a item id.
	 *
	 * @param $text string The text to be used as the value.
	 *
	 * @return string The sanitized item.
	 */
	public static function id($text){
		$output = '';

		if($text){
			$output = ' id="'.sanitize_html_class($text).'" ';
		}

		return $output;
	}

	/**
	 * A component helper for generating a item title.
	 *
	 * @param $text string The text to be used as the value.
	 *
	 * @return string The sanitized item.
	 */
	public static function title($text){
		$output = '';

		if($text){
			$output = ' title="'.esc_attr($text).'" ';
		}

		return $output;
	}

	/**
	 * A component helper for generating a item value.
	 *
	 * @param $text string The text to be used as the value.
	 *
	 * @return string The sanitized item.
	 */
	public static function value($text){
		$output = '';

		if($text){
			$output = ' value="'.sanitize_text_field($text).'" ';
		}

		return $output;
	}

	/**
	 * A component helper for generating a item class attribute.
	 *
	 * @param $text string The text to be used as the value.
	 *
	 * @return string The sanitized item.
	 */
	public static function class_attr($text){
		$output = '';

		if($text){
			$classes = self::esc_classes($text);
			if(!empty($classes)){
				$output = ' class="'.$classes.'" ';
			}
		}

		return $output;
	}

	/**
	 * Escape a string of classes.
	 *
	 * @param $text
	 *
	 * @return string
	 */
	public static function esc_classes($text){
		$output = '';

		if($text){
			$classes = explode(" ",$text);
			$classes = array_map("trim",$classes);
			$classes = array_map("sanitize_html_class",$classes);
			if(!empty($classes)){
				$output = implode(" ",$classes);
			}
		}

		return $output;

	}

	/**
	 * @param $args
	 *
	 * @return string
	 */
	public static function data_attributes($args){
		$output = '';

		if(!empty($args)){

			foreach($args as $key => $val){
				if(substr( $key, 0, 5 ) === "data-"){
					$output .= ' '.sanitize_html_class($key).'="'.esc_attr($val).'" ';
				}
			}
		}

		return $output;
	}

	/**
	 * @param $args
	 *
	 * @return string
	 */
	public static function aria_attributes($args){
		$output = '';

		if(!empty($args)){

			foreach($args as $key => $val){
				if(substr( $key, 0, 5 ) === "aria-"){
					$output .= ' '.sanitize_html_class($key).'="'.esc_attr($val).'" ';
				}
			}
		}

		return $output;
	}

	/**
	 * Build a font awesome icon from a class.
	 *
	 * @param $class
	 * @param bool $space_after
	 * @param array $extra_attributes An array of extra attributes.
	 *
	 * @return string
	 */
	public static function icon($class,$space_after = false, $extra_attributes = array()){
		$output = '';

		if($class){
			$classes = self::esc_classes($class);
			if(!empty($classes)){
				$output = '<i class="'.$classes.'" ';
				// extra attributes
				if(!empty($extra_attributes)){
					$output .= AUI_Component_Helper::extra_attributes($extra_attributes);
				}
				$output .= '></i>';
				if($space_after){
					$output .= " ";
				}
			}
		}

		return $output;
	}

	/**
	 * @param $args
	 *
	 * @return string
	 */
	public static function extra_attributes($args){
		$output = '';

		if(!empty($args)){

			if( is_array($args) ){
				foreach($args as $key => $val){
					$output .= ' '.sanitize_html_class($key).'="'.esc_attr($val).'" ';
				}
			}else{
				$output .= ' '.$args.' ';
			}

		}

		return $output;
	}

	/**
	 * @param $args
	 *
	 * @return string
	 */
	public static function help_text($text){
		$output = '';

		if($text){
			$output .= '<small class="form-text text-muted">'.wp_kses_post($text).'</small>';
		}


		return $output;
	}

	/**
	 * Replace element require context with JS.
	 *
	 * @param $input
	 *
	 * @return string|void
	 */
	public static function element_require( $input ) {

		$input = str_replace( "'", '"', $input );// we only want double quotes

		$output = esc_attr( str_replace( array( "[%", "%]", "%:checked]" ), array(
			"jQuery(form).find('[data-argument=\"",
			"\"]').find('input,select,textarea').val()",
			"\"]').find('input:checked').val()",
		), $input ) );

		if($output){
			$output = ' data-element-require="'.$output.'" ';
		}

		return $output;
	}

}