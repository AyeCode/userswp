<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * A component class for rendering a bootstrap button.
 *
 * @since 1.0.0
 */
class AUI_Component_Button {

	/**
	 * Build the component.
	 *
	 * @param array $args
	 *
	 * @return string The rendered component.
	 */
	public static function get($args = array()){
		$defaults = array(
			'type'       => 'a',
			'href'       => '#',
			'class'      => 'btn btn-primary',
			'id'         => '',
			'title'      => '',
			'value'      => '',
			'content'    => '',
			'icon'       => '',
			'hover_content' => '',
			'hover_icon'    => '',
		);

		/**
		 * Parse incoming $args into an array and merge it with $defaults
		 */
		$args   = wp_parse_args( $args, $defaults );
		$output = '';
		if ( ! empty( $args['type'] ) ) {
			$type = $args['type'] == 'button' ? 'button' : 'a';

			// open/type
			if($type=='a'){
				$output .= '<a href="' . $args['href'] . '"';
			}else{
				$output .= '<button type="' . $type . '" ';
			}

			// name
			if(!empty($args['name'])){
				$output .= AUI_Component_Helper::name($args['name']);
			}

			// id
			if(!empty($args['id'])){
				$output .= AUI_Component_Helper::id($args['id']);
			}

			// title
			if(!empty($args['title'])){
				$output .= AUI_Component_Helper::title($args['title']);
			}

			// value
			if(!empty($args['value'])){
				$output .= AUI_Component_Helper::value($args['value']);
			}

			// class
			$class = !empty($args['class']) ? $args['class'] : '';
			$output .= AUI_Component_Helper::class_attr($class);
			
			// data-attributes
			$output .= AUI_Component_Helper::data_attributes($args);

			// aria-attributes
			$output .= AUI_Component_Helper::aria_attributes($args);
			

			// close opening tag
			$output .= ' >';


			// hover content
			$hover_content = false;
			if(!empty($args['hover_content']) || !empty($args['hover_icon'])){
				$output .= "<span class='hover-content'>".AUI_Component_Helper::icon($args['hover_icon'],$args['hover_content']).$args['hover_content']."</span>";
				$hover_content = true;
			}
			
			// content
			if($hover_content){$output .= "<span class='hover-content-original'>";}
			if(!empty($args['content']) || !empty($args['icon'])){
				$output .= AUI_Component_Helper::icon($args['icon'],$args['content']).$args['content'];
			}
			if($hover_content){$output .= "</span>";}
					


			// close
			if($type=='a'){
				$output .= '</a>';
			}else{
				$output .= '</button>';
			}

		}

		return $output;
	}

}