<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * UsersWP users search widget.
 *
 * @since 1.1.2
 */
class UWP_Users_Search_Widget extends WP_Super_Duper {

    /**
     * Register the profile users search widget with WordPress.
     *
     */
    public function __construct() {


        $options = array(
            'textdomain'    => 'userswp',
            'block-icon'    => 'admin-site',
            'block-category'=> 'widgets',
            'block-keywords'=> "['userswp','user', 'search']",
            'class_name'     => __CLASS__,
            'base_id'       => 'uwp_users_search',
            'name'          => __('UWP > Users Search Form','userswp'),
            //'no_wrap'       => true,
            'widget_ops'    => array(
                'classname'   => 'uwp-user-search bsui',
                'description' => esc_html__('Displays users search form.','userswp'),
            ),
            'arguments'     => array(
                'title'  => array(
                    'title'       => __( 'Title', 'userswp' ),
                    'desc'        => __( 'Enter widget title.', 'userswp' ),
                    'type'        => 'text',
                    'desc_tip'    => true,
                    'default'     => '',
                    'advanced'    => false
                ),
            )

        );


        parent::__construct( $options );
    }

	/**
	 * The Super block output function.
	 *
	 * @param array $args
	 * @param array $widget_args
	 * @param string $content
	 *
	 * @return mixed|string|bool
	 */
    public function output( $args = array(), $widget_args = array(), $content = '' ) {

        ob_start();

	    $design_style = !empty($args['design_style']) ? esc_attr($args['design_style']) : uwp_get_option("design_style",'bootstrap');
        $template = $design_style ? $design_style."/search-form.php" : "search-form.php";

	    uwp_get_template($template, $args);

	    return ob_get_clean();

    }

}