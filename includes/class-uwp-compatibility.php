<?php
/**
 * Compatibility functions for third party plugins.
 *
 * @since 1.2.2.32
 * @package userswp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


class UsersWP_Compatibility {

	public function __construct(){
		if( defined( 'ELEMENTOR_VERSION' ) ){
			add_action('uwp_bypass_users_list_item_template_content', array($this,'users_list_item_content_elementor'), 10, 3);
		}
	}

	/**
	 * Allow to filter the archive item template content if being edited by elementor.
	 *
	 * @param $content
	 * @param $original_content
	 * @param $page_id
	 *
	 * @return mixed
	 */
	public function users_list_item_content_elementor($content, $original_content, $page_id){
		if ( ! $original_content && $page_id && self::is_elementor( $page_id ) ) {
			$original_content = $content = \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $page_id );
		} else {
			$original_content = $content;
		}

		return $original_content;
	}

	/**
	 * Check if a page is being edited by elementor.
	 *
	 * @return bool
	 */
	public static function is_elementor( $post_id ) {
		$document = \Elementor\Plugin::$instance->documents->get( $post_id );
		return $document->is_built_with_elementor();
	}
}

new UsersWP_Compatibility();