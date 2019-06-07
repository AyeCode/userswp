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
            'widget_ops'    => array(
                'classname'   => 'uwp-user-search',
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

    public function output( $args = array(), $widget_args = array(), $content = '' ) {

        ob_start();

        do_action('uwp_users_search');

        $output = ob_get_clean();

        return $output;

    }

}