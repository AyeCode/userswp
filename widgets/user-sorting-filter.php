<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * UsersWP user sorting filter widget.
 *
 * @since 1.1.2
 */
class UWP_User_Sorting_Filter_Widget extends WP_Super_Duper {

    /**
     * Register the profile user sorting filter widget with WordPress.
     *
     */
    public function __construct() {


        $options = array(
            'textdomain'    => 'userswp',
            'block-icon'    => 'admin-site',
            'block-category'=> 'widgets',
            'block-keywords'=> "['userswp','user', 'sorting']",
            'class_name'     => __CLASS__,
            'base_id'       => 'uwp_users_sorting',
            'name'          => __('UWP > User sorting filter','userswp'),
            'widget_ops'    => array(
                'classname'   => 'uwp-user-sort',
                'description' => esc_html__('Displays filter for sort user by. Example Newer, Older, A-Z, Z-A ','userswp'),
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

        do_action('uwp_users_sorting');

        $output = ob_get_clean();

        return $output;

    }

}