<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * UsersWP account widget.
 *
 * @since 1.0.22
 */
class UWP_Account_Widget extends WP_Super_Duper {

    /**
     * Register the account widget with WordPress.
     *
     */
    public function __construct() {


        $options = array(
            'textdomain'    => 'userswp',
            'block-icon'    => 'admin-site',
            'block-category'=> 'widgets',
            'block-keywords'=> "['userswp','account']",
            'class_name'     => __CLASS__,
            'base_id'       => 'uwp_account',
            'name'          => __('UWP > Account','userswp'),
            'widget_ops'    => array(
                'classname'   => 'uwp-account-class bsui',
                'description' => esc_html__('Displays account form.','userswp'),
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
                    'placeholder' => __('Edit Account','userswp'),
                    'advanced'    => true
                ),
	            'design_style'  => array(
		            'title' => __('Design Style', 'userswp'),
		            'desc' => __('The design style to use.', 'userswp'),
		            'type' => 'select',
		            'options'   =>  array(
			            ""        =>  __('default', 'userswp'),
			            "bootstrap" =>  __('Style 1', 'userswp'),
		            ),
		            'default'  => '',
		            'desc_tip' => true,
		            'advanced' => true
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

        if (!is_user_logged_in()) {
            return '';
        }

        $defaults = array(
            'form_title'      => __('Account','userswp'),
            'css_class'     => ''
        );

        /**
         * Parse incoming $args into an array and merge it with $defaults
         */
        $args = wp_parse_args( $args, $defaults );

        ob_start();

        echo '<div class="uwp_widgets uwp_widget_account">';

        echo '<div class="uwp_page">';

	    $design_style = !empty($args['design_style']) ? esc_attr($args['design_style']) : uwp_get_option("design_style",'bootstrap');
	    $template = $design_style ? $design_style."/account.php" : "account.php";

	    uwp_get_template($template, $args);

        echo '</div>';

        echo '</div>';

        $output = ob_get_clean();

        return trim($output);

    }

}