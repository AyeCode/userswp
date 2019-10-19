<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * UsersWP profile actions widget.
 *
 * @since 1.1.2
 */
class UWP_Profile_Actions_Widget extends WP_Super_Duper {

    /**
     * Register the profile actions widget with WordPress.
     *
     */
    public function __construct() {


        $options = array(
            'textdomain'    => 'userswp',
            'block-icon'    => 'admin-site',
            'block-category'=> 'widgets',
            'block-keywords'=> "['userswp','profile']",
            'class_name'     => __CLASS__,
            'base_id'       => 'uwp_profile_actions',
            'name'          => __('UWP > Profile Actions','userswp'),
            'widget_ops'    => array(
                'classname'   => 'uwp-profile-actions',
                'description' => esc_html__('Display profile actions.','userswp'),
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

        ob_start();

        do_action('uwp_profile_actions', $user);

        $output = ob_get_clean();

        return $output;

    }

}