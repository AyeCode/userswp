<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * UsersWP users widget.
 *
 * @since 1.0.22
 */
class UWP_Users_Widget extends WP_Super_Duper {

    /**
     * Register the users widget with WordPress.
     *
     */
    public function __construct() {


        $options = array(
            'textdomain'    => 'userswp',
            'block-icon'    => 'admin-site',
            'block-category'=> 'widgets',
            'block-keywords'=> "['userswp','users']",
            'class_name'     => __CLASS__,
            'base_id'       => 'uwp_users',
            'name'          => __('UWP > Users','userswp'),
            'widget_ops'    => array(
                'classname'   => 'uwp-users-class',
                'description' => esc_html__('Displays users list.','userswp'),
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
	 * @return string
	 */
    public function output( $args = array(), $widget_args = array(), $content = '' ) {

        ob_start();

        $design_style = uwp_get_option("design_style",'bootstrap');

        echo '<div class="uwp_page">';

        if($design_style=='bootstrap'){
            echo do_shortcode("[uwp_users_loop_actions]\n[uwp_users_loop]");
        }else{
            echo do_shortcode("[uwp_users_search]\n[uwp_users_loop_actions]\n[uwp_users_loop]");
        }

        echo '</div>';

        $output = ob_get_clean();

        return trim($output);

    }

}