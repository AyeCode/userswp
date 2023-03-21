<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * A component class for rendering a bootstrap alert.
 *
 * @since 1.0.0
 */
class AUI_Component_Alert {

	/**
	 * Build the component.
	 * 
	 * @param array $args
	 *
	 * @return string The rendered component.
	 */
	public static function get($args = array()){
		global $aui_bs5;
		$defaults = array(
			'type'       => 'info',
			'class'      => '',
			'icon' => '',
			'heading'    => '',
			'content'    => '',
			'footer'     => '',
			'dismissible'=> false,
			'data'       => '',
		);

		/**
		 * Parse incoming $args into an array and merge it with $defaults
		 */
		$args   = wp_parse_args( $args, $defaults );
		$output = '';
		if ( ! empty( $args['content'] ) ) {
			$type = sanitize_html_class( $args['type'] );
			if($type=='error'){$type='danger';}
			$icon = !empty($args['icon']) ? "<i class='".esc_attr($args['icon'])."'></i>" : '';

			// set default icon
			if(!$icon && $args['icon']!==false && $type){
				if($type=='danger'){$icon = '<i class="fas fa-exclamation-circle"></i>';}
				elseif($type=='warning'){$icon = '<i class="fas fa-exclamation-triangle"></i>';}
				elseif($type=='success'){$icon = '<i class="fas fa-check-circle"></i>';}
				elseif($type=='info'){$icon = '<i class="fas fa-info-circle"></i>';}
			}

			$data = '';
			$class = !empty($args['class']) ? esc_attr($args['class']) : '';
			if($args['dismissible']){$class .= " alert-dismissible fade show";}

			// open
			$output .= '<div class="alert alert-' . $type . ' '.$class.'" role="alert" '.$data.'>';

			// heading
			if ( ! empty( $args['heading'] ) ) {
				$output .= '<h4 class="alert-heading">' . $args['heading'] . '</h4>';
			}

			// icon
			if ( ! empty( $icon) ) {
				$output .= $icon." ";
			}

			// content
			$output .= $args['content'];

			// dismissible
			if($args['dismissible']){

				if ( $aui_bs5 ) {
					$output .= '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
				}else{
					$output .= '<button type="button" class="close" data-dismiss="alert" aria-label="Close">';
					$output .= '<span aria-hidden="true">&times;</span>';
					$output .= '</button>';
				}
			}

			// footer
			if ( ! empty( $args['footer'] ) ) {
				$output .= '<hr>';
				$output .= '<p class="mb-0">' . $args['footer'] . '</p>';
			}

			// close
			$output .= '</div>';
		}

		return $output;
	}

}