<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * UsersWP users loop pagination widget.
 *
 * @since 1.1.2
 */
class UWP_Users_Loop_Pagination_Widget extends WP_Super_Duper {

    /**
     * Register the profile users loop pagination widget with WordPress.
     *
     */
    public function __construct() {


        $options = array(
            'textdomain'    => 'userswp',
            'block-icon'    => 'admin-site',
            'block-category'=> 'widgets',
            'block-keywords'=> "['userswp','user', 'search']",
            'class_name'     => __CLASS__,
            'base_id'       => 'uwp_loop_pagination',
            'name'          => __('UWP > Users Loop Pagination','userswp'),
            'widget_ops'    => array(
                'classname'   => 'uwp-users-loop-pagination',
                'description' => esc_html__('Displays pagination for users loop.','userswp'),
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

        global $uwp_users_query;
        ob_start();

        if (isset($uwp_users_query) && $uwp_users_query instanceof WP_User_Query && $uwp_users_query->get_total() > 1) {
            do_action('uwp_users_loop_pagination', $uwp_users_query->get_total());
        }

        $output = ob_get_clean();

        return $output;

    }

}