<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * UsersWP profile social widget.
 *
 * @since 1.1.2
 */
class UWP_Profile_Social_Widget extends WP_Super_Duper {

    /**
     * Register the profile social widget with WordPress.
     *
     */
    public function __construct() {


        $options = array(
            'textdomain'    => 'userswp',
            'block-icon'    => 'admin-site',
            'block-category'=> 'widgets',
            'block-keywords'=> "['userswp','profile']",
            'class_name'     => __CLASS__,
            'base_id'       => 'uwp_profile_social',
            'name'          => __('UWP > Profile Social','userswp'),
            'widget_ops'    => array(
                'classname'   => 'uwp-users-list-user-social',
                'description' => esc_html__('Displays fields which are selected to display in social location from form builder.','userswp'),
            ),
            'arguments'     => array(
                'exclude'  => array(
                    'title'       => __( 'Exclude Fields', 'userswp' ),
                    'desc'        => __( 'Enter comma separated field keys to exclude from displaying in social location.', 'userswp' ),
                    'type'        => 'text',
                    'desc_tip'    => true,
                    'default'     => '',
                    'advanced'    => false
                ),
            )

        );


        parent::__construct( $options );
    }

	/**
	 * The Super block output function.
	 *
	 * @param array $args
	 * @param array $widget_args
	 * @param string $content
	 *
	 * @return mixed|string|bool
	 */
    public function output( $args = array(), $widget_args = array(), $content = '' ) {

        $user = uwp_get_displayed_user();

        $defaults = array(
            'exclude' => '',
        );

        $args = wp_parse_args( $args, $defaults );

        $args = apply_filters( 'uwp_widget_profile_social_args', $args, $widget_args, $this );

        ob_start();

        do_action('uwp_profile_social', $user, $args['exclude']);

	    return ob_get_clean();

    }

}