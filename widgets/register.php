<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * UsersWP register widget.
 *
 * @since 1.0.0
 */
class UWP_Register_Widget extends WP_Super_Duper {

	/**
	 * Register the registration widget with WordPress.
	 *
	 */
	public function __construct() {

		$options = array(
			'textdomain'     => 'userswp',
			'block-icon'     => 'admin-site',
			'block-category' => 'widgets',
			'block-keywords' => "['userswp','register']",
			'class_name'     => __CLASS__,
			'base_id'        => 'uwp_register',
			'name'           => __( 'UWP > Register', 'userswp' ),
			'widget_ops'     => array(
				'classname'   => 'uwp-register-class bsui',
				'description' => esc_html__( 'Displays register form.', 'userswp' ),
			),
			'arguments'      => array(
				'id'           => array(
					'title'    => __( 'Form', 'userswp' ),
					'desc'     => __( 'Select form.', 'userswp' ),
					'type'     => 'select',
					'options'  => uwp_get_register_forms_dropdown_options(),
					'default'  => 1,
					'desc_tip' => true,
					'advanced' => false
				),
				'limit'        => array(
					'title'    => __( 'Display form having IDs', 'userswp' ),
					'desc'     => __( 'Enter comma separeted IDs to show limited forms', 'userswp' ),
					'type'     => 'text',
					'desc_tip' => true,
					'default'  => '',
					'advanced' => true
				),
				'title'        => array(
					'title'    => __( 'Widget title', 'userswp' ),
					'desc'     => __( 'Enter widget title.', 'userswp' ),
					'type'     => 'text',
					'desc_tip' => true,
					'default'  => '',
					'advanced' => true
				),
				'form_title'   => array(
					'title'       => __( 'Form Title', 'userswp' ),
					'desc'        => __( 'Enter the form title (or "0" for no title)', 'userswp' ),
					'type'        => 'text',
					'desc_tip'    => true,
					'default'     => '',
					'placeholder' => __( 'Register', 'userswp' ),
					'advanced'    => true
				),
				'redirect_to'  => array(
					'type'        => 'text',
					'title'       => __( 'Redirect to:', 'userswp' ),
					'desc'        => __( 'Enter the url you want to redirect after register.', 'userswp' ),
					'placeholder' => '',
					'default'     => '',
					'desc_tip'    => true,
					'advanced'    => true
				),
				'design_style' => array(
					'title'    => __( 'Design Style', 'userswp' ),
					'desc'     => __( 'The design style to use.', 'userswp' ),
					'type'     => 'select',
					'options'  => array(
						""          => __( 'default', 'userswp' ),
						"bootstrap" => __( 'Style 1', 'userswp' ),
					),
					'default'  => '',
					'desc_tip' => true,
					'advanced' => true
				),
				'css_class'    => array(
					'type'        => 'text',
					'title'       => __( 'Extra class:', 'userswp' ),
					'desc'        => __( 'Give the wrapper an extra class so you can style things as you want.', 'userswp' ),
					'placeholder' => '',
					'default'     => '',
					'desc_tip'    => true,
					'advanced'    => true,
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

		if ( is_user_logged_in() && ! is_admin() ) {
			return false;
		}

		$defaults = array(
			'form_title'  => __( 'Register', 'userswp' ),
			'css_class'   => 'border-0',
			'redirect_to' => '',
		);

		/**
		 * Parse incoming $args into an array and merge it with $defaults
		 */
		$args = wp_parse_args( $args, $defaults );

		if(isset($_REQUEST['user_type']) && !empty($_REQUEST['user_type'])){
			$args['id'] = uwp_get_form_id_by_type(sanitize_text_field($_REQUEST['user_type']));
		}

		if(isset($_REQUEST['uwp_form_id']) && !empty($_REQUEST['uwp_form_id'])){
			$args['id'] = absint($_REQUEST['uwp_form_id']);
		}

		ob_start();

		echo '<div class="uwp_widgets uwp_widget_register">';

		$design_style = ! empty( $args['design_style'] ) ? esc_attr( $args['design_style'] ) : uwp_get_option( "design_style", 'bootstrap' );
		$template     = $design_style ? $design_style . "/register.php" : "register.php";

		echo '<div class="uwp_page wpbs">';

		uwp_get_template( $template, $args );

		echo '</div>';

		echo '</div>';

		if(!wp_doing_ajax()){
			uwp_password_strength_inline_js();
		}

		return ob_get_clean();

	}
}