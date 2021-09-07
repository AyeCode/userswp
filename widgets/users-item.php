<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * UsersWP users widget.
 *
 * @since 1.0.22
 */
class UWP_Users_Item_Widget extends WP_Super_Duper {

	/**
	 * Register the users widget with WordPress.
	 *
	 */
	public function __construct() {


		$options = array(
			'textdomain'     => 'userswp',
			'block-icon'     => 'admin-site',
			'block-category' => 'widgets',
			'block-keywords' => "['userswp','users']",
			'class_name'     => __CLASS__,
			'base_id'        => 'uwp_users_item',
			'no_wrap'        => true,
			'block-wrap'     => '',
			'name'           => __( 'UWP > Users Item', 'userswp' ),
			'widget_ops'     => array(
				'classname'   => 'uwp-users-item-class',
				'description' => esc_html__( 'Template for displaying a single user item inside the users loop.', 'userswp' ),
			),
			'arguments'      => array()

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

		ob_start();

		$design_style = uwp_get_option( "design_style", 'bootstrap' );

		if ( $design_style == 'bootstrap' ) {

			echo do_shortcode( "[uwp_profile_header]" );

			?>

            <div class="card-body">
				<?php echo do_shortcode( "[uwp_output_location location='users']" ); ?>
            </div>
            <div class="card-footer">
				<?php echo do_shortcode( "[uwp_user_actions]" ); ?>
            </div>
			<?php
		} else {
			echo do_shortcode( "[uwp_profile_header][uwp_user_title tag= 'h4'][uwp_profile_social][uwp_output_location location='users'][uwp_user_actions]" );
		}

		return ob_get_clean();

	}

}