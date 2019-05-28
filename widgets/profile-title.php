<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * UsersWP profile user title widget.
 *
 * @since 1.1.2
 */
class UWP_Profile_Title_Widget extends WP_Super_Duper {

    /**
     * Register the profile user title widget with WordPress.
     *
     */
    public function __construct() {


        $options = array(
            'textdomain'    => 'userswp',
            'block-icon'    => 'admin-site',
            'block-category'=> 'widgets',
            'block-keywords'=> "['userswp','profile']",
            'class_name'     => __CLASS__,
            'base_id'       => 'uwp_profile_title',
            'name'          => __('UWP > Profile Title','userswp'),
            'no_wrap'       => true,
            'widget_ops'    => array(
                'classname'   => 'uwp-profile-title',
                'description' => esc_html__('Displays user name.','userswp'),
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

        ob_start();

        do_action('uwp_profile_title', $user);

        $output = ob_get_clean();

        return $output;

    }

}