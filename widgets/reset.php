<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * UsersWP reset password widget.
 *
 * @since 1.0.22
 */
class UWP_Reset_Widget extends WP_Super_Duper {

    /**
     * Register the reset password widget with WordPress.
     *
     */
    public function __construct() {


        $options = array(
            'textdomain'    => 'userswp',
            'block-icon'    => 'admin-site',
            'block-category'=> 'widgets',
            'block-keywords'=> "['userswp','reset']",
            'class_name'     => __CLASS__,
            'base_id'       => 'uwp_reset',
            'name'          => __('UWP > Reset','userswp'),
            'widget_ops'    => array(
                'classname'   => 'uwp-reset-class bsui',
                'description' => esc_html__('Displays reset password form.','userswp'),
            ),
            'arguments'     => array(
                'title'  => array(
                    'title'       => __( 'Widget title', 'userswp' ),
                    'desc'        => __( 'Enter widget title.', 'userswp' ),
                    'type'        => 'text',
                    'desc_tip'    => true,
                    'default'     => '',
                    'advanced'    => false
                ),
                'form_title'  => array(
                    'title'       => __( 'Form title', 'userswp' ),
                    'desc'        => __( 'Enter the form title (or "0" for no title)', 'userswp' ),
                    'type'        => 'text',
                    'desc_tip'    => true,
                    'default'     => '',
                    'placeholder' => __('Reset Password','userswp'),
                    'advanced'    => true
                ),
                'css_class'  => array(
	                'type' => 'text',
	                'title' => __('Extra class:', 'userswp'),
	                'desc' => __('Give the wrapper an extra class so you can style things as you want.', 'userswp'),
	                'placeholder' => '',
	                'default' => '',
	                'desc_tip' => true,
	                'advanced' => true,
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

        if (is_user_logged_in() && !is_admin()) {
            return false;
        }

        $defaults = array(
            'form_title'      => __('Reset Password','userswp'),
            'css_class'     => ''
        );

        /**
         * Parse incoming $args into an array and merge it with $defaults
         */
        $args = wp_parse_args( $args, $defaults );

        ob_start();

        echo '<div class="uwp_widgets uwp_widget_reset">';

	    $design_style = !empty($args['design_style']) ? esc_attr($args['design_style']) : uwp_get_option("design_style",'bootstrap');
	    $template = $design_style ? $design_style."/reset.php" : "reset.php";

        echo '<div class="uwp_page">';

	    uwp_get_template($template, $args);

        echo '</div>';

        echo '</div>';

	    uwp_password_strength_inline_js();

	    $output = ob_get_clean();

        return trim($output);

    }

}