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
            'block-wrap'    => '',
            'widget_ops'    => array(
                'classname'   => 'uwp-profile-header bsui',
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
	            'user_id'  => array(
		            'title' => __('User ID:', 'userswp'),
		            'desc' => __('Leave blank to use current user ID or use post_author for current post author ID. For profile page it will take displayed user ID. Input specific user ID for other pages.', 'userswp'),
		            'type' => 'text',
		            'desc_tip' => true,
		            'default'  => '',
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
                'disable_greedy'  => array(
	                'title' => __('Disable Greedy Menu for post counts', 'userswp'),
	                'desc' => __('Greedy menu prevents a large menu falling onto another line by adding a dropdown select.', 'userswp'),
	                'type' => 'checkbox',
	                'desc_tip' => true,
	                'value'  => '1',
	                'default'  => '',
	                'advanced' => true,
                ),
            )

        );


        parent::__construct( $options );
    }

    public function output( $args = array(), $widget_args = array(), $content = '' ) {

	    global $post;

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

	    if('post_author' == $args['user_id'] && $post instanceof WP_Post){
		    $user = get_userdata($post->post_author);
		    $args['user_id'] = $post->post_author;
	    } else if(isset($args['user_id']) && (int)$args['user_id'] > 0){
		    $user = get_userdata($args['user_id']);
	    } else {
		    $user = uwp_get_displayed_user();
	    }

	    if(empty($args['user_id']) && !empty($user->ID)){
		    $args['user_id'] = $user->ID;
	    }

	    if(!$user){
			return '';
	    }
        
        if ($enable_profile_header != '1') {
        	return '';
        }

        $args = apply_filters( 'uwp_widget_profile_header_args', $args, $widget_args, $this );

        ob_start();

        wp_enqueue_script( 'jcrop', array( 'jquery' ) );
        wp_enqueue_script( 'jquery-ui-progressbar', array( 'jquery' ) );
        wp_enqueue_style( 'jcrop' );
        wp_enqueue_style( 'jquery-ui' );

        // setup some args
        add_filter( 'upload_dir', 'uwp_handle_multisite_profile_image', 10, 1 );
        $uploads = wp_upload_dir();
        remove_filter( 'upload_dir', 'uwp_handle_multisite_profile_image' );
        $upload_url = $uploads['baseurl'];
        $banner = uwp_get_usermeta( $user->ID, 'banner_thumb', '' );
        if ( empty( $banner ) ) {
            $banner = uwp_get_default_banner_uri();
        } else {
            $banner = $upload_url . $banner;
        }

        $avatar = uwp_get_usermeta( $user->ID, 'avatar_thumb', '' );
        if ( empty( $avatar ) ) {
            $avatar = get_avatar_url( $user->user_email, array( 'size' => 150 ) );
        } else {
            if ( strpos( $avatar, 'http:' ) === false && strpos( $avatar, 'https:' ) === false ) {
                $avatar = $upload_url . $avatar;
            }
        }

        $args['avatar_url'] = $avatar;
        $args['banner_url'] = $banner;


        $design_style = uwp_get_option("design_style",'bootstrap');
        $template = $design_style ? $design_style."/profile-header.php" : "profile-header.php";

        uwp_get_template($template, $args);

        do_action('uwp_profile_header', $user, $args['hide_cover'], $args['hide_avatar'], $args['allow_change']);

        $output = ob_get_clean();

        return $output;

    }

}