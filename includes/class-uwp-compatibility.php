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

			// Elementor 3
			if ( version_compare( ELEMENTOR_VERSION, '3.0.0', '>=' ) ) {
				add_filter( 'elementor/elements/categories_registered', array( __CLASS__,'add_elementor_widget_categories' ), 1, 1  );
				add_filter( 'elementor/editor/localize_settings', array( __CLASS__,'alter_widget_config' ), 5, 1  );
			}
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
	 * Get the elementor document by page ID.
	 *
	 * @since 1.2.20
	 *
	 * @param int $post_id The current post ID.
	 * @return object Elementor document.
	 */
	public static function get_elementor_document( $post_id ) {
		if ( defined( 'ELEMENTOR_VERSION' ) && class_exists( '\Elementor\Plugin' ) ) {
			$document = \Elementor\Plugin::$instance->documents->get( (int) $post_id );
		} else {
			$document = null;
		}

		return $document;
	}

	/**
	 * Check if a page is being edited by elementor.
	 *
	 * @return bool
	 */
	public static function is_elementor( $post_id ) {
		$document = self::get_elementor_document( $post_id );

		if ( empty( $document ) ) {
			return false;
		}

		return $document->is_built_with_elementor();
	}

	/**
	 * Add our own Category.
	 *
	 * @param $elements_manager
	 */
	public static function add_elementor_widget_categories( $elements_manager ) {

		$elements_manager->add_category(
			'userswp',
			[
				'title' => esc_html__( 'UsersWP', 'userswp' ),
				'icon' => 'fa fa-plug',
			]
		);

	}

	/**
	 * Force our widget to show for search and to be in our own category.
	 *
	 * @param $config
	 *
	 * @return mixed
	 */
	public static function alter_widget_config( $config ){

		if ( ! empty( $config['initial_document']['widgets'] ) ) {
			foreach( $config['initial_document']['widgets'] as $key => $widget){
				if(substr( $key, 0, 14 ) === "wp-widget-uwp_"){
					$config['initial_document']['widgets'][$key]['categories'][] = 'userswp';
					$config['initial_document']['widgets'][$key]['hide_on_search'] = false;
					$config['initial_document']['widgets'][$key]['icon'] = 'eicon-user-circle-o'; //@todo if no icons use on page then font-awesome is not loaded, wif we can fifure out how to force load we can use icons. <i class="fas fa-globe-americas"></i><i class="fa-solid fa-earth-americas"></i>
				}
			}
		}



		return $config;
	}
}

new UsersWP_Compatibility();