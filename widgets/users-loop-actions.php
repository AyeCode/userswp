<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * UsersWP user's loop actions widget.
 *
 * @since 1.1.2
 */
class UWP_Users_Loop_Actions extends WP_Super_Duper {

    /**
     * Register the user's loop actions widget with WordPress.
     *
     */
    public function __construct() {


        $options = array(
            'textdomain'    => 'userswp',
            'block-icon'    => 'admin-site',
            'block-category'=> 'widgets',
            'block-keywords'=> "['userswp','user']",
            'class_name'     => __CLASS__,
            'base_id'       => 'uwp_users_loop_actions',
            'name'          => __('UWP > User Loop Action','userswp'),
            'widget_ops'    => array(
                'classname'   => 'uwp-user-loop-action',
                'description' => esc_html__('Displays user loop actions.','userswp'),
            ),

        );


        parent::__construct( $options );
    }

    public function output( $args = array(), $widget_args = array(), $content = '' ) {

        ob_start();

        do_action('uwp_users_loop_actions');

        $output = ob_get_clean();

        return $output;

    }

}