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
            )

        );


        parent::__construct( $options );
    }

    public function output( $args = array(), $widget_args = array(), $content = '' ) {
        global $userswp;

        $user = uwp_get_user_by_author_slug();

        if(!$user){
            return;
        }


        $defaults = array(
            'hide_cover'       => 0,
            'hide_avatar'      => 0,
            'allow_change'     => 1,
        );

        $args = wp_parse_args( $args, $defaults );


        $tabs_array = $userswp->profile->get_tabs();
        $args['tabs_array'] = $tabs_array;
//        $active_tab = get_query_var('uwp_tab');
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


        // bail if no active tab
        if (!$active_tab) {
            return;
        }

        $args['active_tab'] = $active_tab;


        $enable_profile_body = uwp_get_option('enable_profile_body');

        ob_start();

        if (1 == $enable_profile_body) {

            

            global $uwp_widget_args;
            $uwp_widget_args = $args;

            $design_style = !empty($args['design_style']) ? esc_attr($args['design_style']) : uwp_get_option("design_style",'bootstrap');
            $template = $design_style ? $design_style."/profile-tabs" : "profile-tabs";

            uwp_locate_template($template);

//            do_action('uwp_profile_content', $user);

        }

        $output = ob_get_clean();

        return $output;

    }

}