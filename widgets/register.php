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
            'textdomain'    => 'userswp',
            'block-icon'    => 'admin-site',
            'block-category'=> 'widgets',
            'block-keywords'=> "['userswp','register']",
            'class_name'     => __CLASS__,
            'base_id'       => 'uwp_register',
            'name'          => __('UWP > Register','userswp'),
            'widget_ops'    => array(
                'classname'   => 'uwp-register-class bsui',
                'description' => esc_html__('Displays register form.','userswp'),
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
                    'title'       => __( 'Form Title', 'userswp' ),
                    'desc'        => __( 'Enter the form title (or "0" for no title)', 'userswp' ),
                    'type'        => 'text',
                    'desc_tip'    => true,
                    'default'     => '',
                    'placeholder' => __('Register','userswp'),
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

        if (is_user_logged_in() && !is_admin()) {
            return false;
        }

        $defaults = array(
            'form_title'      => __('Register','userswp'),
            'css_class'       => 'border-0'
        );

        /**
         * Parse incoming $args into an array and merge it with $defaults
         */
        $args = wp_parse_args( $args, $defaults );

        ob_start();

        echo '<div class="uwp_widgets uwp_widget_register">';

	    $design_style = !empty($args['design_style']) ? esc_attr($args['design_style']) : uwp_get_option("design_style",'bootstrap');
	    $template = $design_style ? $design_style."/register.php" : "register.php";

        echo '<div class="uwp_page wpbs ">';

	    uwp_get_template($template, $args);

        echo '</div>';

        echo '</div>';

	    // scripts
	    wp_enqueue_script( 'password-strength-meter' ); // add scripts
	    ?>
	    <script>
		    jQuery( document ).ready( function( $ ) {
			    // Binding to trigger uwp_checkPasswordStrength
			    $( 'body' ).on( 'keyup', 'input[name=password], input[name=confirm_password]',
				    function( event ) {
					    uwp_checkPasswordStrength(
						    $('input[name=password]'),         // First password field
						    $('input[name=confirm_password]'), // Second password field
						    $('#uwp-password-strength'),           // Strength meter
						    $('input[type=submit]'),           // Submit button
						    ['black', 'listed', 'word']        // Blacklisted words
					    );
				    }
			    );
		    });
	    </script>
	    <?php

        $output = ob_get_contents();

        ob_end_clean();

        return trim($output);

    }
}