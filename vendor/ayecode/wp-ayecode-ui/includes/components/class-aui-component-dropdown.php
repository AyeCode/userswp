<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * A component class for rendering a bootstrap dropdown.
 *
 * @since 1.0.0
 */
class AUI_Component_Dropdown {

	/**
	 * Build the component.
	 *
	 * @param array $args
	 *
	 * @return string The rendered component.
	 */
	public static function get($args = array()){
		$defaults = array(
			'type'       => 'button',
			'href'       => '#',
			'class'      => 'btn btn-primary dropdown-toggle',
			'wrapper_class' => '',
			'dropdown_menu_class' => '',
			'id'         => '',
			'title'      => '',
			'value'      => '',
			'content'    => '',
			'icon'       => '',
			'hover_content' => '',
			'hover_icon'    => '',
			'data-toggle'   => 'dropdown',
			'aria-haspopup' => 'true',
			'aria-expanded' => 'false',
			'dropdown_menu'          => '', // unescaped html menu (non-preferred way)
			'dropdown_items'          => array(), // array of AUI calls

		);

		/**
		 * Parse incoming $args into an array and merge it with $defaults
		 */
		$args   = wp_parse_args( $args, $defaults );
		$output = '';
		if ( ! empty( $args['type'] ) ) {
			// wrapper open
			$output .= '<div class="dropdown '.AUI_Component_Helper::esc_classes($args['wrapper_class']).'">';

			// button part
			$output .= aui()->button($args);

			// dropdown-menu
			if(!empty($args['dropdown_menu'])){
				$output .= $args['dropdown_menu'];
			}elseif(!empty($args['dropdown_items'])){
				$output .= '<div class="dropdown-menu '.AUI_Component_Helper::esc_classes($args['dropdown_menu_class']).'" aria-labelledby="'.sanitize_html_class($args['id']).'">';
				$output .= aui()->render($args['dropdown_items']);
				$output .= '</div>';
			}

			// wrapper close
			$output .= '</div>';

		}

		return $output;
	}

}