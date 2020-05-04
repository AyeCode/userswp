<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * UsersWP user post counts widget.
 */
class UWP_User_Post_Counts_Widget extends WP_Super_Duper {

    /**
     * Register the user post counts widget with WordPress.
     *
     */
    public function __construct() {


        $options = array(
            'textdomain'    => 'userswp',
            'block-icon'    => 'admin-site',
            'block-category'=> 'widgets',
            'block-keywords'=> "['userswp','user']",
            'class_name'     => __CLASS__,
            'base_id'       => 'uwp_user_post_counts',
            'name'          => __('UWP > User Post Counts','userswp'),
            'no_wrap'       => true,
            'block-wrap'    => '',
            'widget_ops'    => array(
                'classname'   => 'uwp-user-post-counts bsui',
                'description' => esc_html__('Display user post, comments and other custom post type counts.','userswp'),
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

        if(!$user){
        	return '';
        }

        ob_start();

        do_action('uwp_user_post_counts', $user->ID);

        $output = ob_get_clean();

        return $output;

    }

}