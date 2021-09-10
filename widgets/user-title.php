<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * UsersWP user title widget.
 *
 * @since 1.1.2
 */
class UWP_User_Title_Widget extends WP_Super_Duper {

    /**
     * Register the user title widget with WordPress.
     *
     */
    public function __construct() {


        $options = array(
            'textdomain'    => 'userswp',
            'block-icon'    => 'admin-site',
            'block-category'=> 'widgets',
            'block-keywords'=> "['userswp','user']",
            'class_name'     => __CLASS__,
            'base_id'       => 'uwp_user_title',
            'name'          => __('UWP > User Title','userswp'),
            'no_wrap'       => true,
            'widget_ops'    => array(
                'classname'   => 'uwp-user-title',
                'description' => esc_html__('Displays user name.','userswp'),
            ),
            'arguments'     => array(
                'tag'  => array(
                    'title' => __('Header Tag:', 'userswp'),
                    'desc' => __('Header tag for the user title.', 'userswp'),
                    'type' => 'select',
                    'options'   =>  array(
                        "h1" => "h1",
                        "h2" => "h2",
                        "h3" => "h3",
                        "h4" => "h4",
                        "h5" => "h5",
                        "h6" => "h6",
                    ),
                    'default'  => 'h2',
                    'desc_tip' => true,
                ),
                'user_id'  => array(
                    'title' => __('User ID:', 'userswp'),
                    'desc' => __('Leave blank to use current user ID or use post_author for current post author ID. For profile page it will take displayed user ID. Input specific user ID for other pages.', 'userswp'),
                    'type' => 'text',
                    'desc_tip' => true,
                    'default'  => '',
                    'advanced' => true
                ),
            )

        );


        parent::__construct( $options );
    }

    public function output( $args = array(), $widget_args = array(), $content = '' ) {

    	global $post;

        $defaults = array(
            'tag'      => 'h2',
        );

        $args = wp_parse_args( $args, $defaults );

        $args = apply_filters( 'uwp_widget_user_title_args', $args, $widget_args, $this );

        $title_tag = empty( $args['tag'] ) ? 'h2' : apply_filters( 'uwp_widget_user_title_tag', $args['tag'], $args, $widget_args, $this );

	    if('post_author' == $args['user_id'] && $post instanceof WP_Post){
		    $user = get_userdata($post->post_author);
		    $args['user_id'] = $post->post_author;
	    } else if(isset($args['user_id']) && (int)$args['user_id'] > 0){
		    $user = get_userdata((int)$args['user_id']);
	    } else {
		    $user = uwp_get_displayed_user();
	    }

	    if(empty($args['user_id']) && !empty($user->ID)){
		    $args['user_id'] = $user->ID;
	    }

	    if(!$user){
		    return '';
	    }

        ob_start();

        do_action('uwp_user_title', $user, $title_tag);

	    return ob_get_clean();

    }

}