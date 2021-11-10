<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * UsersWP profile tabs widget.
 *
 * @since 1.1.2
 */
class UWP_Profile_Tabs_Widget extends WP_Super_Duper {

    /**
     * Register the profile tabs widget with WordPress.
     *
     */
    public function __construct() {


        $options = array(
            'textdomain'    => 'userswp',
            'block-icon'    => 'admin-site',
            'block-category'=> 'widgets',
            'block-keywords'=> "['userswp','profile']",
            'class_name'     => __CLASS__,
            'base_id'       => 'uwp_profile_tabs',
            'name'          => __('UWP > Profile Tabs','userswp'),
            //'no_wrap'       => true,
            'widget_ops'    => array(
                'classname'   => 'uwp-profile-tabs bsui',
                'description' => esc_html__('Displays profile tabs.','userswp'),
            ),
            'arguments'     => array(
                'title'  => array(
                    'title'       => __( 'Title', 'userswp' ),
                    'desc'        => __( 'Enter widget title.', 'userswp' ),
                    'type'        => 'text',
                    'desc_tip'    => true,
                    'default'     => '',
                    'advanced'    => false
                ),
                'output'  => array(
                    'title' => __('Output Type:', 'userswp'),
                    'desc' => __('What parts should be output.', 'userswp'),
                    'type' => 'select',
                    'options'   =>  array(
                        "" => __('Default', 'userswp'),
                        "head" => __('Head only', 'userswp'),
                        "body" => __('Body only', 'userswp'),
                        "json" => __('JSON Array (developer option)', 'userswp'),
                    ),
                    'default'  => '',
                    'desc_tip' => true,
                    'advanced' => true
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
	 * @param array $args
	 * @param array $widget_args
	 * @param string $content
	 *
	 * @return mixed|string|bool
	 */
    public function output( $args = array(), $widget_args = array(), $content = '' ) {
        global $userswp;

        $user = uwp_get_user_by_author_slug();

        if(!$user){
            return '';
        }

        $defaults = array(
            'output' => '',
        );

        $args = wp_parse_args( $args, $defaults );

        $tabs_array = $userswp->profile->get_tabs();
        $args['tabs_array'] = $tabs_array;
        $active_tab = '';
        if(!empty($tabs_array)){
            if(!empty(get_query_var('uwp_tab'))){
                foreach($tabs_array as $key => $tab){
                    if(get_query_var('uwp_tab')==$tab['tab_key']){
                        $active_tab = $tab['tab_key'];
                    }
                }
            }else{
                $active_tab = $tabs_array[0]['tab_key'];
            }

        }

        // error/warning messages
        if (!$active_tab && isset($tabs_array[0]['tab_key']) ) {

            $no_tab = $tabs_array[0];
            $no_tab['tab_key'] = 'not_found';
            $no_tab['tab_name'] = __("Not Found","userswp");
            $no_tab['tab_icon'] = 'fas fa-exclamation-triangle';
            $no_tab['tab_content'] = '';
            $no_tab['tab_content_rendered'] = aui()->alert(array(
                    'type'=>'error',
                    'content'=> __('<strong>ERROR</strong>: This tab does not exist.', 'userswp')
                )
            );
            $no_tab['tab_level'] = 0;
            $no_tab['tab_parent'] = 0;

            array_unshift( $tabs_array, $no_tab ); // push to start of array
            $active_tab = $tabs_array[0]['tab_key']; // set it as active
            $args['tabs_array'] = $tabs_array; //  update the array

        }elseif(!$active_tab){
            if(current_user_can('manage_options' )){
                $no_tab = array();
                $no_tab['tab_key'] = 'no_tabs';
                $no_tab['tab_name'] = __("No Tabs","userswp");
                $no_tab['tab_icon'] = 'fas fa-exclamation-triangle';
                $no_tab['tab_content'] = '';
                $no_tab['tab_content_rendered'] = aui()->alert(array(
                        'type'=>'warning',
                        'content'=> sprintf( __('No content, please make sure tabs have been added %shere.%s', 'userswp'), "<a href='".admin_url( 'admin.php?page=uwp_form_builder&tab=profile-tabs')."'>","</a>" )
                    )
                );
                $no_tab['tab_level'] = 0;
                $no_tab['tab_parent'] = 0;

                array_unshift( $tabs_array, $no_tab ); // push to start of array
                $active_tab = $tabs_array[0]['tab_key']; // set it as active
                $args['tabs_array'] = $tabs_array; //  update the array
            }else{
                $no_tab = array();
                $no_tab['tab_key'] = 'no_tabs';
                $no_tab['tab_name'] = __("No Tabs","userswp");
                $no_tab['tab_icon'] = 'fas fa-exclamation-triangle';
                $no_tab['tab_content'] = '';
                $no_tab['tab_content_rendered'] = aui()->alert(array(
                        'type'=>'warning',
                        'content'=> __('No content found.', 'userswp')
                    )
                );
                $no_tab['tab_level'] = 0;
                $no_tab['tab_parent'] = 0;

                array_unshift( $tabs_array, $no_tab ); // push to start of array
                $active_tab = $tabs_array[0]['tab_key']; // set it as active
                $args['tabs_array'] = $tabs_array; //  update the array
            }

        }

        $args['active_tab'] = $active_tab;

        // output JSON
        if($args['output']=='json'){
            return json_encode( $tabs_array );
        }

        ob_start();

        $design_style = !empty($args['design_style']) ? esc_attr($args['design_style']) : uwp_get_option("design_style",'bootstrap');
        $template = $design_style ? $design_style."/profile-tabs.php" : "profile-tabs.php";

	    uwp_get_template($template, $args);

	    return ob_get_clean();

    }

}