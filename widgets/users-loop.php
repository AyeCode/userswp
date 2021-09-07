<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * UsersWP users loop widget.
 *
 * @since 1.1.2
 */
class UWP_Users_Loop_Widget extends WP_Super_Duper {

    /**
     * Register the profile users loop widget with WordPress.
     *
     */
    public function __construct() {

        $options = array(
            'textdomain'    => 'userswp',
            'block-icon'    => 'admin-site',
            'block-category'=> 'widgets',
            'block-keywords'=> "['userswp','user', 'search']",
            'class_name'     => __CLASS__,
            'base_id'       => 'uwp_users_loop',
            'name'          => __('UWP > Users Loop','userswp'),

            'widget_ops'    => array(
                'classname'   => 'uwp-users-list bsui',
                'description' => esc_html__('Displays users loop.','userswp'),
            ),
            'arguments'     => array(
	            'roles'  => array(
		            'title' => __('Roles:', 'userswp'),
		            'desc' => __('Choose user roles to show in list. All users will display if no role selected.', 'userswp'),
		            'type' => 'select',
		            'options' => uwp_get_user_roles(),
		            'default'  => '',
		            'desc_tip' => true,
		            'advanced' => false,
		            'multiple' => true
	            )
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
        
        ob_start();

	    $roles = isset($args['roles']) ? $args['roles'] : array();
	    if(!empty($roles) && !is_array($roles)){
		    $roles = explode(',', $roles);
	    }

        // get users
        $users_list = get_uwp_users_list($roles);
        $args['template_args']['users'] = $users_list['users'];
        $args['template_args']['total_users'] = $users_list['total_users'];
        
        $design_style = !empty($args['design_style']) ? esc_attr($args['design_style']) : uwp_get_option("design_style",'bootstrap');
        $template = $design_style ? $design_style."/loop-users.php" : "loop-users.php";
	    uwp_get_template($template, $args);

        // @todo maybe move paging to template?
        $number = uwp_get_option('users_no_of_items', 10);
	    $number = empty($number) ? 10 : $number;
        $total_users = $users_list['total_users'];
        $total_pages = ceil($total_users/$number);
        do_action('uwp_profile_pagination', $total_pages);

	    return ob_get_clean();

    }

}