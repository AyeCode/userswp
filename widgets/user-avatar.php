<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * UsersWP user avatar widget.
 *
 * @since 1.1.2
 */
class UWP_User_Avatar_Widget extends WP_Super_Duper {

	/**
	 * Register the user avatar widget with WordPress.
	 *
	 */
	public function __construct() {


		$options = array(
			'textdomain'     => 'userswp',
			'block-icon'     => 'admin-site',
			'block-category' => 'widgets',
			'block-keywords' => "['userswp','user']",
			'class_name'     => __CLASS__,
			'base_id'        => 'uwp_user_avatar',
			'name'           => __( 'UWP > User Avatar', 'userswp' ),
			'no_wrap'        => true,
			'widget_ops'     => array(
				'classname'   => 'uwp-user-avatar bsui',
				'description' => esc_html__( 'Displays user avatar image for user.', 'userswp' ),
			),
			'arguments'      => array(
				'size'         => array(
					'title'    => __( 'Avatar size:', 'userswp' ),
					'desc'     => __( 'Avatar image size in px. Default is 50px.', 'userswp' ),
					'type'     => 'number',
					'desc_tip' => true,
					'default'  => '50',
					'group'    => __( "Design", "userswp" ),
				),
				'link'         => array(
					'title'    => __( "Link to profile?:", 'userswp' ),
					'desc'     => __( 'Link avatar image to user\'s profile page.', 'userswp' ),
					'type'     => 'checkbox',
					'desc_tip' => true,
					'value'    => '1',
					'default'  => '0',
					'advanced' => true
				),
				'allow_change' => array(
					'title'    => __( 'Allow to change avatar:', 'userswp' ),
					'desc'     => __( 'Allow user to change avatar image in profile page.', 'userswp' ),
					'type'     => 'checkbox',
					'desc_tip' => true,
					'value'    => '1',
					'default'  => 1,
					'advanced' => true
				),
				'user_id'      => array(
					'title'    => __( 'User ID:', 'userswp' ),
					'desc'     => __( 'Leave blank to use current user ID or use post_author for current post author ID. For profile page use displayed_user. Input specific user ID for other pages.', 'userswp' ),
					'type'     => 'text',
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
	 * @param array  $args
	 * @param array  $widget_args
	 * @param string $content
	 *
	 * @return mixed|string|bool
	 */
	public function output( $args = array(), $widget_args = array(), $content = '' ) {

		global $post;

		$defaults = array(
			'size'         => 50,
			'link'         => 0,
			'allow_change' => 0,
		);

		$args = wp_parse_args( $args, $defaults );

		$args['size']         = empty( $args['size'] ) ? 50 : $args['size'];
		$args['link']         = 1 == $args['link'] ? 1 : 0;
		$args['allow_change'] = ! empty( $args['allow_change'] ) ? $args['allow_change'] : 0;

		if ( isset( $args['user_id'] ) && (int) $args['user_id'] > 0 ) {
			$user = get_userdata( $args['user_id'] );
		} else if ( isset( $args['user_id'] ) && 'post_author' == $args['user_id'] && $post instanceof WP_Post ) {
			$user = get_userdata( $post->post_author );
		} else if ( isset( $args['user_id'] ) && 'displayed_user' == $args['user_id'] ) {
			$user = uwp_get_displayed_user();
		} else {
			$user = get_userdata( get_current_user_id() );
		}

		if ( ! empty( $user->ID ) ) {
			$args['user_id'] = $user->ID;
		}

		if ( ! $args['user_id'] ) {
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

		$avatar = uwp_get_usermeta( $user->ID, 'avatar_thumb', '' );
		if ( empty( $avatar ) ) {
			$avatar = get_avatar_url( $user->user_email, array( 'size' => $args['size'] ) );
		} else {
			if ( strpos( $avatar, 'http:' ) === false && strpos( $avatar, 'https:' ) === false ) {
				$avatar = $upload_url . $avatar;
			}
		}

		$args['avatar_url'] = $avatar;

		$design_style = ! empty( $args['design_style'] ) ? esc_attr( $args['design_style'] ) : uwp_get_option( "design_style", 'bootstrap' );
		$template     = $design_style ? $design_style . "/user-avatar.php" : "user-avatar.php";

		uwp_get_template( $template, $args );

		return ob_get_clean();
	}

}