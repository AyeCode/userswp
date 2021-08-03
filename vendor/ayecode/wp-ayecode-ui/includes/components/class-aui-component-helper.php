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
	 * @param $multiple bool If the name is set to be multiple but no brackets found then we add some.
	 *
	 * @return string
	 */
	public static function name($text,$multiple = false){
		$output = '';

		if($text){
			$is_multiple = strpos($text, '[') === false && $multiple  ? '[]' : '';
			$output = ' name="'.esc_attr($text).$is_multiple.'" ';
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
	public static function value( $text ) {
		$output = '';

		if ( $text !== null && $text !== false ) {
			$output = ' value="' . esc_attr( wp_unslash( $text ) ) . '" ';
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

	/**
	 * Returns an array of allowed HTML tags and attributes for a given context.
	 *
	 * @since 0.1.41
	 *
	 * @param string|array $context The context for which to retrieve tags. Allowed values are 'post',
	 *                              'strip', 'data', 'entities', or the name of a field filter such as
	 *                              'pre_user_description'.
	 * @param array $input Input.
	 * @return array Array of allowed HTML tags and their allowed attributes.
	 */
	public static function kses_allowed_html( $context = 'post', $input = array() ) {
		$allowed_html = wp_kses_allowed_html( $context );

		if ( is_array( $allowed_html ) ) {
			// <iframe>
			if ( ! isset( $allowed_html['iframe'] ) && $context == 'post' ) {
				$allowed_html['iframe']     = array(
					'class'        => true,
					'id'           => true,
					'src'          => true,
					'width'        => true,
					'height'       => true,
					'frameborder'  => true,
					'marginwidth'  => true,
					'marginheight' => true,
					'scrolling'    => true,
					'style'        => true,
					'title'        => true,
					'allow'        => true,
					'allowfullscreen' => true,
					'data-*'       => true,
				);
			}
		}

		/**
		 * Filters the allowed html tags.
		 *
		 * @since 0.1.41
		 *
		 * @param array[]|string $allowed_html Allowed html tags.
		 * @param @param string|array $context The context for which to retrieve tags.
		 * @param array $input Input field.
		 */
		return apply_filters( 'ayecode_ui_kses_allowed_html', $allowed_html, $context, $input );
	}

	/**
	 * Filters content and keeps only allowable HTML elements.
	 *
	 * This function makes sure that only the allowed HTML element names, attribute
	 * names and attribute values plus only sane HTML entities will occur in
	 * $string. You have to remove any slashes from PHP's magic quotes before you
	 * call this function.
	 *
	 * The default allowed protocols are 'http', 'https', 'ftp', 'mailto', 'news',
	 * 'irc', 'gopher', 'nntp', 'feed', 'telnet, 'mms', 'rtsp' and 'svn'. This
	 * covers all common link protocols, except for 'javascript' which should not
	 * be allowed for untrusted users.
	 *
	 * @since 0.1.41
	 *
	 * @param string|array $value Content to filter through kses.
	 * @param array  $input       Input Field.
	 * @return string Filtered content with only allowed HTML elements.
	 */
	public static function _sanitize_html_field( $value, $input = array() ) {
		if ( $value === '' ) {
			return $value;
		}

		$allowed_html = self::kses_allowed_html( 'post', $input );

		if ( ! is_array( $allowed_html ) ) {
			$allowed_html = wp_kses_allowed_html( 'post' );
		}

		$filtered = trim( wp_unslash( $value ) );
		$filtered = wp_kses( $filtered, $allowed_html );
		$filtered = balanceTags( $filtered ); // Balances tags

		return $filtered;
	}

	/**
	 * Navigates through an array, object, or scalar, and removes slashes from the values.
	 *
	 * @since 0.1.41
	 *
	 * @param mixed $value The value to be stripped.
	 * @param array  $input Input Field.
	 * @return mixed Stripped value.
	 */
	public static function sanitize_html_field( $value, $input = array() ) {
		$original = $value;

		if ( is_array( $value ) ) {
			foreach ( $value as $index => $item ) {
				$value[ $index ] = self::_sanitize_html_field( $value, $input );
			}
		} elseif ( is_object( $value ) ) {
			$object_vars = get_object_vars( $value );

			foreach ( $object_vars as $property_name => $property_value ) {
				$value->$property_name = self::_sanitize_html_field( $property_value, $input );
			}
		} else {
			$value = self::_sanitize_html_field( $value, $input );
		}

		/**
		 * Filters content and keeps only allowable HTML elements.
		 *
		 * @since 0.1.41
		 *
		 * @param string|array $value Content to filter through kses.
		 * @param string|array $value Original content without filter.
		 * @param array  $input       Input Field.
		 */
		return apply_filters( 'ayecode_ui_sanitize_html_field', $value, $original, $input );
	}
}