<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * UsersWP profile widget.
 *
 * @since 1.0.22
 */
class UWP_Profile_Widget extends WP_Super_Duper {

    /**
     * Register the profile widget with WordPress.
     *
     */
    public function __construct() {


        $options = array(
            'textdomain'    => 'userswp',
            'block-icon'    => 'admin-site',
            'block-category'=> 'widgets',
            'block-keywords'=> "['userswp','profile']",
            'class_name'     => __CLASS__,
            'base_id'       => 'uwp_profile',
            'name'          => __('UWP > Profile','userswp'),
            'no_wrap'       => true,
            'widget_ops'    => array(
                'classname'   => 'uwp-profile-class bsui',
                'description' => esc_html__('Displays user profile.','userswp'),
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

    public function output( $args = array(), $widget_args = array(), $content = '' ) {

        ob_start();

        echo '<div class="uwp_widgets uwp_widget_profile">';

        echo '<div class="uwp_page">';

        $design_style = !empty($args['design_style']) ? esc_attr($args['design_style']) : uwp_get_option("design_style",'bootstrap');
        $template = $design_style ? $design_style."/profile.php" : "profile.php";

        uwp_get_template($template, $args);
        
        echo '</div>';

        echo '</div>';

	    return ob_get_clean();

    }

}