<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * UsersWP profile header widget.
 *
 * @since 1.1.2
 */
class UWP_Profile_Header_Widget extends WP_Super_Duper {

    /**
     * Register the profile header widget with WordPress.
     *
     */
    public function __construct() {


        $options = array(
            'textdomain'    => 'userswp',
            'block-icon'    => 'admin-site',
            'block-category'=> 'widgets',
            'block-keywords'=> "['userswp','profile']",
            'class_name'     => __CLASS__,
            'base_id'       => 'uwp_profile_header',
            'name'          => __('UWP > Profile Header','userswp'),
            'no_wrap'       => true,
            'widget_ops'    => array(
                'classname'   => 'uwp-profile-header',
                'description' => esc_html__('Displays user profile header.','userswp'),
            ),
            'arguments'     => array(
                'hide_cover'  => array(
                    'title' => __('Hide cover image:', 'userswp'),
                    'desc' => __('Hide cover image in user profile page.', 'userswp'),
                    'type' => 'checkbox',
                    'desc_tip' => true,
                    'value'  => '1',
                    'default'  => 0,
                    'advanced' => true
                ),
                'hide_avatar'  => array(
                    'title' => __('Hide avatar image:', 'userswp'),
                    'desc' => __('Hide avatar image in user profile page.', 'userswp'),
                    'type' => 'checkbox',
                    'desc_tip' => true,
                    'value'  => '1',
                    'default'  => 0,
                    'advanced' => true
                ),
                'allow_change'  => array(
                    'title' => __('Allow to change cover and avatar:', 'userswp'),
                    'desc' => __('Allow user to change cover and avatar image in profile page.', 'userswp'),
                    'type' => 'checkbox',
                    'desc_tip' => true,
                    'value'  => '1',
                    'default'  => 1,
                    'advanced' => true
                ),
            )

        );


        parent::__construct( $options );
    }

    public function output( $args = array(), $widget_args = array(), $content = '' ) {

        $user = uwp_get_displayed_user();

        $enable_profile_header = uwp_get_option('enable_profile_header');
        $defaults = array(
            'hide_cover'       => 0,
            'hide_avatar'      => 0,
            'allow_change'     => 1,
        );

        $args = wp_parse_args( $args, $defaults );

        $args['hide_cover'] = !empty($args['hide_cover']) ? $args['hide_cover'] : 0;
        $args['hide_avatar'] = !empty($args['hide_avatar']) ? $args['hide_avatar'] : 0;
        $args['allow_change'] = !empty($args['allow_change']) ? $args['allow_change'] : 1;

        $args = apply_filters( 'uwp_widget_profile_header_args', $args, $widget_args, $this );

        ob_start();

        if ($enable_profile_header == '1') {

            do_action('uwp_profile_header', $user, $args['hide_cover'], $args['hide_avatar'], $args['allow_change']);

        }

        $output = ob_get_clean();

        return $output;

    }

}