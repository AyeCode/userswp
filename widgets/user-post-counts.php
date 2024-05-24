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
			'textdomain'     => 'userswp',
			'block-icon'     => 'admin-site',
			'block-category' => 'widgets',
			'block-keywords' => "['userswp','user']",
			'class_name'     => __CLASS__,
			'base_id'        => 'uwp_user_post_counts',
			'name'           => __( 'UWP > User Post Counts', 'userswp' ),
			'no_wrap'        => true,
			'block-wrap'     => '',
			'widget_ops'     => array(
				'classname'   => 'uwp-user-post-counts bsui',
				'description' => esc_html__( 'Display user post, comments and other custom post type counts.', 'userswp' ),
			),
			'arguments'      => array(
				'title' => array(
					'title'    => __( 'Title', 'userswp' ),
					'desc'     => __( 'Enter widget title.', 'userswp' ),
					'type'     => 'text',
					'desc_tip' => true,
					'default'  => '',
					'advanced' => false
				),
				'disable_greedy'  => array(
					'title' => __('Disable Greedy Menu', 'userswp'),
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
		global $_uwp_user_post_counts;

		$user = uwp_get_displayed_user();

		if ( ! $user ) {
			return '';
		}

		ob_start();

		$output  = "";
		$counts  = array();
		$user_id = $user->ID;

		if ( is_user_logged_in() && $user_id == get_current_user_id() ) {

			$post_types = uwp_get_option( 'login_user_post_counts_cpts' );
			$post_types = apply_filters( 'uwp_login_user_count_cpts', $post_types, $user_id );

		} else {

			$post_types = uwp_get_option( 'user_post_counts_cpts' );
			$post_types = apply_filters( 'uwp_user_count_cpts', $post_types, $user_id );

		}

		if ( ! empty( $post_types ) ) {
			$_uwp_user_post_counts = true;

			foreach ( $post_types as $cpt ) {
				$post_type = get_post_type_object( $cpt );
				$count     = count_user_posts( $user_id, $cpt );

				if ( $post_type && $count ) {
					$counts[ $cpt ] = array(
						'name'          => $post_type->labels->name,
						'singular_name' => $post_type->labels->singular_name,
						'count'         => $count
					);
				}
			}

			$_uwp_user_post_counts = false;
		}

		$counts = apply_filters( 'uwp_get_user_post_counts', $counts, $user_id );
		$greedy_menu_class = empty($args['disable_greedy']) ? 'greedy' : '';

		if ( is_uwp_profile_page() ) {

			if ( ! empty( $counts ) ) {
				$output .= '<nav class="navbar navbar-expand-xl navbar-light bg-white p-xl-0 '.esc_attr($greedy_menu_class).'">';
				$output .= '<div class="w-100 justify-content-center">';
				$output .= '<ul class="navbar-nav flex-wrap m-0">';
				$class = " pl-0";
				foreach ( $counts as $cpt => $post_type ) {
					$post_count_text = $post_type['count'] > 1 ? esc_attr( $post_type['name'] ) . '<span class="badge badge-dark ml-1">' . esc_attr( $post_type['count'] ) . '</span>' : esc_attr( $post_type['singular_name'] ) . '<span class="badge badge-dark ml-1">' . esc_attr( $post_type['count'] ) . '</span>';
					$output          .= '<li class="nav-item"><span class="nav-link pr-0'.$class.'"><span class="badge badge-white text-muted'.$class.'">' . $post_count_text . '</span></span></li>' . " \n";
					$class = '';
				}
				$output .= '</ul>';
				$output .= '</div>';
				$output .= '</nav>';
			}

		} else {
			if ( ! empty( $counts ) ) {
				$class = " pl-0";
				foreach ( $counts as $cpt => $post_type ) {
					$post_count_text = $post_type['count'] > 1 ? esc_attr( $post_type['name'] ) . '<span class="badge badge-dark ml-1">' . esc_attr( $post_type['count'] ) . '</span>' : esc_attr( $post_type['singular_name'] ) . '<span class="badge badge-dark ml-1">' . esc_attr( $post_type['count'] ) . '</span>';
					$output          .= '<span class="badge badge-white nav-link text-muted pr-0'.$class.'">' . $post_count_text . '</span>' . " \n";
					$class = '';
				}
			}
		}

		echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		return ob_get_clean();
	}
}