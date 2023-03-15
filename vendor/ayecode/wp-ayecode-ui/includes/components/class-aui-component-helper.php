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
	public static function name( $text, $multiple = false ) {
		$output = '';

		if ( $text ) {
			$is_multiple = strpos( $text, '[' ) === false && $multiple ? '[]' : '';
			$output      = ' name="' . esc_attr( $text ) . $is_multiple . '" ';
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
	public static function id( $text ) {
		$output = '';

		if ( $text ) {
			$output = ' id="' . sanitize_html_class( $text ) . '" ';
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
	public static function title( $text ) {
		$output = '';

		if ( $text ) {
			$output = ' title="' . esc_attr( $text ) . '" ';
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
	public static function class_attr( $text ) {
		$output = '';

		if ( $text ) {
			$classes = self::esc_classes( $text );
			if ( ! empty( $classes ) ) {
				$output = ' class="' . $classes . '" ';
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
	public static function esc_classes( $text ) {
		$output = '';

		if ( $text ) {
			$classes = explode( " ", $text );
			$classes = array_map( "trim", $classes );
			$classes = array_map( "sanitize_html_class", $classes );
			if ( ! empty( $classes ) ) {
				$output = implode( " ", $classes );
			}
		}

		return $output;

	}

	/**
	 * @param $args
	 *
	 * @return string
	 */
	public static function data_attributes( $args ) {
		$output = '';

		if ( ! empty( $args ) ) {

			foreach ( $args as $key => $val ) {
				if ( substr( $key, 0, 5 ) === "data-" ) {
					$output .= ' ' . sanitize_html_class( $key ) . '="' . esc_attr( $val ) . '" ';
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
	public static function aria_attributes( $args ) {
		$output = '';

		if ( ! empty( $args ) ) {

			foreach ( $args as $key => $val ) {
				if ( substr( $key, 0, 5 ) === "aria-" ) {
					$output .= ' ' . sanitize_html_class( $key ) . '="' . esc_attr( $val ) . '" ';
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
	public static function icon( $class, $space_after = false, $extra_attributes = array() ) {
		$output = '';

		if ( $class ) {
			$classes = self::esc_classes( $class );
			if ( ! empty( $classes ) ) {
				$output = '<i class="' . $classes . '" ';
				// extra attributes
				if ( ! empty( $extra_attributes ) ) {
					$output .= AUI_Component_Helper::extra_attributes( $extra_attributes );
				}
				$output .= '></i>';
				if ( $space_after ) {
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
	public static function extra_attributes( $args ) {
		$output = '';

		if ( ! empty( $args ) ) {

			if ( is_array( $args ) ) {
				foreach ( $args as $key => $val ) {
					$output .= ' ' . sanitize_html_class( $key ) . '="' . esc_attr( $val ) . '" ';
				}
			} else {
				$output .= ' ' . $args . ' ';
			}

		}

		return $output;
	}

	/**
	 * @param $args
	 *
	 * @return string
	 */
	public static function help_text( $text ) {
		$output = '';

		if ( $text ) {
			$output .= '<small class="form-text text-muted d-block">' . wp_kses_post( $text ) . '</small>';
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

		if ( $output ) {
			$output = ' data-element-require="' . $output . '" ';
		}

		return $output;
	}

	/**
	 * Navigates through an array, object, or scalar, and removes slashes from the values.
	 *
	 * @since 0.1.41
	 *
	 * @param mixed $value The value to be stripped.
	 * @param array $input Input Field.
	 *
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
		 * @param array $input Input Field.
		 */
		return apply_filters( 'ayecode_ui_sanitize_html_field', $value, $original, $input );
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
	 * @param array $input Input Field.
	 *
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
	 * Returns an array of allowed HTML tags and attributes for a given context.
	 *
	 * @since 0.1.41
	 *
	 * @param string|array $context The context for which to retrieve tags. Allowed values are 'post',
	 *                              'strip', 'data', 'entities', or the name of a field filter such as
	 *                              'pre_user_description'.
	 * @param array $input Input.
	 *
	 * @return array Array of allowed HTML tags and their allowed attributes.
	 */
	public static function kses_allowed_html( $context = 'post', $input = array() ) {
		$allowed_html = wp_kses_allowed_html( $context );

		if ( is_array( $allowed_html ) ) {
			// <iframe>
			if ( ! isset( $allowed_html['iframe'] ) && $context == 'post' ) {
				$allowed_html['iframe'] = array(
					'class'           => true,
					'id'              => true,
					'src'             => true,
					'width'           => true,
					'height'          => true,
					'frameborder'     => true,
					'marginwidth'     => true,
					'marginheight'    => true,
					'scrolling'       => true,
					'style'           => true,
					'title'           => true,
					'allow'           => true,
					'allowfullscreen' => true,
					'data-*'          => true,
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

	public static function get_column_class( $label_number = 2, $type = 'label' ) {

		$class = '';

		// set default if empty
		if( $label_number === '' ){
			$label_number = 2;
		}

		if ( $label_number && $label_number < 12 && $label_number > 0 ) {
			if ( $type == 'label' ) {
				$class = 'col-sm-' . absint( $label_number );
			} elseif ( $type == 'input' ) {
				$class = 'col-sm-' . ( 12 - absint( $label_number ) );
			}
		}

		return $class;
	}

	/**
	 * Sanitizes a multiline string from user input or from the database.
	 *
	 * Emulate the WP native sanitize_textarea_field function in a %%variable%% safe way.
	 *
	 * @see   https://core.trac.wordpress.org/browser/trunk/src/wp-includes/formatting.php for the original
	 *
	 * @since 0.1.66
	 *
	 * @param string $str String to sanitize.
	 * @return string Sanitized string.
	 */
	public static function sanitize_textarea_field( $str ) {
		$filtered = self::_sanitize_text_fields( $str, true );

		/**
		 * Filters a sanitized textarea field string.
		 *
		 * @see https://core.trac.wordpress.org/browser/trunk/src/wp-includes/formatting.php
		 *
		 * @param string $filtered The sanitized string.
		 * @param string $str      The string prior to being sanitized.
		 */
		return apply_filters( 'sanitize_textarea_field', $filtered, $str );
	}

	/**
	 * Internal helper function to sanitize a string from user input or from the db.
	 *
	 * @since 0.1.66
	 * @access private
	 *
	 * @param string $str           String to sanitize.
	 * @param bool   $keep_newlines Optional. Whether to keep newlines. Default: false.
	 * @return string Sanitized string.
	 */
	public static function _sanitize_text_fields( $str, $keep_newlines = false ) {
		if ( is_object( $str ) || is_array( $str ) ) {
			return '';
		}

		$str = (string) $str;

		$filtered = wp_check_invalid_utf8( $str );

		if ( strpos( $filtered, '<' ) !== false ) {
			$filtered = wp_pre_kses_less_than( $filtered );
			// This will strip extra whitespace for us.
			$filtered = wp_strip_all_tags( $filtered, false );

			// Use HTML entities in a special case to make sure no later
			// newline stripping stage could lead to a functional tag.
			$filtered = str_replace( "<\n", "&lt;\n", $filtered );
		}

		if ( ! $keep_newlines ) {
			$filtered = preg_replace( '/[\r\n\t ]+/', ' ', $filtered );
		}
		$filtered = trim( $filtered );

		$found = false;
		while ( preg_match( '`[^%](%[a-f0-9]{2})`i', $filtered, $match ) ) {
			$filtered = str_replace( $match[1], '', $filtered );
			$found = true;
		}
		unset( $match );

		if ( $found ) {
			// Strip out the whitespace that may now exist after removing the octets.
			$filtered = trim( preg_replace( '` +`', ' ', $filtered ) );
		}

		return $filtered;
	}
}