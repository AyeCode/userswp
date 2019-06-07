<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * UsersWP user view filter widget.
 *
 * @since 1.1.2
 */
class UWP_User_Views_Filter_Widget extends WP_Super_Duper {

    /**
     * Register the profile user view filter widget with WordPress.
     *
     */
    public function __construct() {


        $options = array(
            'textdomain'    => 'userswp',
            'block-icon'    => 'admin-site',
            'block-category'=> 'widgets',
            'block-keywords'=> "['userswp','user', 'views']",
            'class_name'     => __CLASS__,
            'base_id'       => 'uwp_user_views',
            'name'          => __('UWP > User Views filter','userswp'),
            'widget_ops'    => array(
                'classname'   => 'uwp-user-views',
                'description' => esc_html__('Displays filter for user views. Example List view, grid view','userswp'),
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

        do_action('uwp_users_views');

        $output = ob_get_clean();

        return $output;

    }

}