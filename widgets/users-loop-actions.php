<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * UsersWP user's loop actions widget.
 *
 * @since 1.1.2
 */
class UWP_Users_Loop_Actions extends WP_Super_Duper {

    /**
     * Register the user's loop actions widget with WordPress.
     *
     */
    public function __construct() {


        $options = array(
            'textdomain'    => 'userswp',
            'block-icon'    => 'admin-site',
            'block-category'=> 'widgets',
            'block-keywords'=> "['userswp','user']",
            'class_name'     => __CLASS__,
            'base_id'       => 'uwp_users_loop_actions',
            'name'          => __('UWP > User Loop Action','userswp'),
            'widget_ops'    => array(
                'classname'   => 'uwp-user-loop-action bsui',
                'description' => esc_html__('Displays user loop actions.','userswp'),
            ),

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

        ob_start();

        $design_style = !empty($args['design_style']) ? esc_attr($args['design_style']) : uwp_get_option("design_style",'bootstrap');
        $template = $design_style ? $design_style."/users-actions.php" : "users-actions.php";

	    uwp_get_template($template, $args);

	    return ob_get_clean();

    }

}