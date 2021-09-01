<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * UsersWP user cover widget.
 *
 * @since 1.1.2
 */
class UWP_User_Cover_Widget extends WP_Super_Duper {

    /**
     * Register the user cover widget with WordPress.
     *
     */
    public function __construct() {


        $options = array(
            'textdomain'    => 'userswp',
            'block-icon'    => 'admin-site',
            'block-category'=> 'widgets',
            'block-keywords'=> "['userswp','profile']",
            'class_name'     => __CLASS__,
            'base_id'       => 'uwp_user_cover',
            'name'          => __('UWP > User Cover Image','userswp'),
            //'no_wrap'       => true,
            'block-wrap'    => '',
            'widget_ops'    => array(
                'classname'   => 'uwp-user-cover bsui',
                'description' => esc_html__('Displays user cover image for user.','userswp'),
            ),
            'arguments'     => array(
	            'link'  => array(
		            'title' => __("Link to profile?:", 'userswp'),
		            'desc' => __('Link cover image to user\'s profile page.', 'userswp'),
		            'type' => 'checkbox',
		            'desc_tip' => true,
		            'value'  => '1',
		            'default'  => '0',
		            'advanced' => true
	            ),
	            'allow_change'  => array(
		            'title' => __('Allow to change cover:', 'userswp'),
		            'desc' => __('Allow user to change cover image in profile page.', 'userswp'),
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

		global $post;

		$defaults = array(
			'link'      => 0,
			'allow_change' => 0,
		);

		$args = wp_parse_args( $args, $defaults );

		$args['link'] = 1 == $args['link'] ? 1 : 0;
		$args['allow_change'] = !empty($args['allow_change']) ? $args['allow_change'] : 0;

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

		wp_enqueue_script( 'jcrop', array( 'jquery' ) );
		wp_enqueue_script( 'jquery-ui-progressbar', array( 'jquery' ) );
		wp_enqueue_style( 'jcrop' );
		wp_enqueue_style( 'jquery-ui' );

		ob_start();

		add_filter( 'upload_dir', 'uwp_handle_multisite_profile_image', 10, 1 );
		$uploads = wp_upload_dir();
		remove_filter( 'upload_dir', 'uwp_handle_multisite_profile_image' );
		$upload_url = $uploads['baseurl'];

		$banner = uwp_get_usermeta($user->ID, 'banner_thumb', '');
		if (empty($banner)) {
			$banner = uwp_get_default_banner_uri();
		} else {
			$banner = $upload_url.$banner;
		}

		$args['banner_url'] = $banner;

		$design_style = !empty($args['design_style']) ? esc_attr($args['design_style']) : uwp_get_option("design_style",'bootstrap');
		$template = $design_style ? $design_style."/user-cover.php" : "user-cover.php";

		uwp_get_template($template, $args);

		return ob_get_clean();

	}

}