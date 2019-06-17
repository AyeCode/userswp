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
        );

        parent::__construct( $options );
    }

    public function output( $args = array(), $widget_args = array(), $content = '' ) {

        $users_list = get_uwp_users_list();
        $users = $users_list['users'];

        ob_start();

        $temp_obj = new UsersWP_Templates();

        $template_path = $temp_obj->uwp_locate_template('users');

        if (file_exists($template_path)) {
            include($template_path);
        }

        $number = uwp_get_option('profile_no_of_items', 10);
        $total_users = $users_list['total_users'];

        $total_pages = ceil($total_users/$number);

        do_action('uwp_profile_pagination', $total_pages);

        $output = ob_get_clean();

        return $output;

    }

}