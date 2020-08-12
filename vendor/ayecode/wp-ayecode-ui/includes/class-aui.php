<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * A singleton class to output AyeCode UI Components.
 *
 * @since 1.0.0
 */
class AUI {

	/**
	 * Holds the class instance.
	 *
	 * @since 1.0.0
	 * @var null
	 */
	private static $instance = null;

	/**
	 * Holds the current AUI version number.
	 *
	 * @var string $ver The current version number.
	 */
	public static $ver = '0.1.20';

	/**
	 * There can be only one.
	 *
	 * @since 1.0.0
	 * @return AUI|null
	 */
	public static function instance() {
		if ( self::$instance == null ) {
			self::$instance = new AUI();
		}

		return self::$instance;
	}

	/**
	 * AUI constructor.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		if ( function_exists( "__autoload" ) ) {
			spl_autoload_register( "__autoload" );
		}
		spl_autoload_register( array( $this, 'autoload' ) );
	}

	/**
	 * Autoload any components on the fly.
	 *
	 * @since 1.0.0
	 *
	 * @param $classname
	 */
	private function autoload( $classname ) {
		$class     = str_replace( '_', '-', strtolower( $classname ) );
		$file_path = trailingslashit( dirname( __FILE__ ) ) . "components/class-" . $class . '.php';
		if ( $file_path && is_readable( $file_path ) ) {
			include_once( $file_path );
		}
	}

	public function render( $items = array() ) {
		$output = '';

		if ( ! empty( $items ) ) {
			foreach ( $items as $args ) {
				$render = isset( $args['render'] ) ? $args['render'] : '';
				if ( $render && method_exists( __CLASS__, $render ) ) {
					$output .= $this->$render( $args );
				}
			}
		}

		return $output;
	}

	/**
	 * Render and return a bootstrap alert component.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args
	 *
	 * @return string The rendered component.
	 */
	public function alert( $args = array() ) {
		return AUI_Component_Alert::get( $args );
	}

	/**
	 * Render and return a bootstrap input component.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args
	 *
	 * @return string The rendered component.
	 */
	public function input( $args = array() ) {
		return AUI_Component_Input::input( $args );
	}

	/**
	 * Render and return a bootstrap textarea component.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args
	 *
	 * @return string The rendered component.
	 */
	public function textarea( $args = array() ) {
		return AUI_Component_Input::textarea( $args );
	}

	/**
	 * Render and return a bootstrap button component.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args
	 *
	 * @return string The rendered component.
	 */
	public function button( $args = array() ) {
		return AUI_Component_Button::get( $args );
	}

	/**
	 * Render and return a bootstrap button component.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args
	 *
	 * @return string The rendered component.
	 */
	public function badge( $args = array() ) {
		$defaults = array(
			'class' => 'badge badge-primary align-middle',
		);

		// maybe set type
		if ( empty( $args['href'] ) ) {
			$defaults['type'] = 'badge';
		}

		/**
		 * Parse incoming $args into an array and merge it with $defaults
		 */
		$args = wp_parse_args( $args, $defaults );

		return AUI_Component_Button::get( $args );
	}

	/**
	 * Render and return a bootstrap dropdown component.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args
	 *
	 * @return string The rendered component.
	 */
	public function dropdown( $args = array() ) {
		return AUI_Component_Dropdown::get( $args );
	}

	/**
	 * Render and return a bootstrap select component.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args
	 *
	 * @return string The rendered component.
	 */
	public function select( $args = array() ) {
		return AUI_Component_Input::select( $args );
	}

	/**
	 * Render and return a bootstrap radio component.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args
	 *
	 * @return string The rendered component.
	 */
	public function radio( $args = array() ) {
		return AUI_Component_Input::radio( $args );
	}

	/**
	 * Render and return a bootstrap pagination component.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args
	 *
	 * @return string The rendered component.
	 */
	public function pagination( $args = array() ) {
		return AUI_Component_Pagination::get( $args );
	}

}