<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * UsersWP users loop widget.
 *
 * @since 1.1.2
 */
class UWP_Users_Loop_Widget extends WP_Super_Duper {

    /**
     * Register the profile users loop widget with WordPress.
     *
     */
    public function __construct() {


        $options = array(
            'textdomain'    => 'userswp',
            'block-icon'    => 'admin-site',
            'block-category'=> 'widgets',
            'block-keywords'=> "['userswp','user', 'search']",
            'class_name'     => __CLASS__,
            'base_id'       => 'uwp_users_loop',
            'name'          => __('UWP > Users Loop','userswp'),
            'widget_ops'    => array(
                'classname'   => 'uwp-users-list',
                'description' => esc_html__('Displays users loop.','userswp'),
            ),
            'arguments'     => array(
                'layout'  => array(
                    'title' => __('Layout:', 'userswp'),
                    'desc' => __('How the users list should displayed by default.', 'userswp'),
                    'type' => 'select',
                    'options'   => uwp_get_layout_options(),
                    'default'  => 'list',
                    'desc_tip' => true,
                    'advanced' => true
                )
            )

        );


        parent::__construct( $options );
    }

    public function output( $args = array(), $widget_args = array(), $content = '' ) {

        ob_start();

        do_action('uwp_users_loop');

        $output = ob_get_clean();

        return $output;

    }

}