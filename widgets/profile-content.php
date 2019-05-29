<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * UsersWP profile user title widget.
 *
 * @since 1.1.2
 */
class UWP_Profile_Content_Widget extends WP_Super_Duper {

    /**
     * Register the profile content widget with WordPress.
     *
     */
    public function __construct() {


        $options = array(
            'textdomain'    => 'userswp',
            'block-icon'    => 'admin-site',
            'block-category'=> 'widgets',
            'block-keywords'=> "['userswp','profile']",
            'class_name'     => __CLASS__,
            'base_id'       => 'uwp_profile_content',
            'name'          => __('UWP > Profile Content','userswp'),
            'no_wrap'       => true,
            'widget_ops'    => array(
                'classname'   => 'uwp-profile-content',
                'description' => esc_html__('Displays profile tabs and content.','userswp'),
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

        $user = uwp_get_user_by_author_slug();

        if(!$user && is_user_logged_in()){
            $user = get_userdata(get_current_user_id());
        }

        $enable_profile_content = uwp_get_option('enable_profile_body');

        ob_start();

        if ($enable_profile_content == '1') {

            do_action('uwp_profile_content', $user);

        }

        $output = ob_get_clean();

        return $output;

    }

}