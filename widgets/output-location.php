<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * UsersWP output location widget.
 *
 * @since 1.1.2
 */
class UWP_Output_Location_Widget extends WP_Super_Duper {

    /**
     * Register the output location widget with WordPress.
     *
     */
    public function __construct() {


        $options = array(
            'textdomain'    => 'userswp',
            'block-icon'    => 'admin-site',
            'block-category'=> 'widgets',
            'block-keywords'=> "['userswp','output']",
            'class_name'     => __CLASS__,
            'base_id'       => 'uwp_output_location',
            'name'          => __('UWP > Output Location','userswp'),
            'widget_ops'    => array(
                'classname'   => 'uwp-output-location',
                'description' => esc_html__('Displays fields which are selected to display in selected location from form builder.','userswp'),
            ),
            'arguments'     => array(
                'location'  => array(
                    'title' => __('Location:', 'userswp'),
                    'desc' => __('The location type to output.', 'userswp'),
                    'type' => 'select',
                    'options' => $this->show_in_locations(),
                    'desc_tip' => true,
                    'advanced' => false
                )
            )

        );


        parent::__construct( $options );
    }

    public function output( $args = array(), $widget_args = array(), $content = '' ) {

        $user = uwp_get_user_by_author_slug();

        if(!$user && is_user_logged_in()){
            $user = get_userdata(get_current_user_id());
        }

        $defaults = array(
            'location' => '',
        );

        $args = wp_parse_args( $args, $defaults );

        $args = apply_filters( 'uwp_widget_output_location_args', $args, $widget_args, $this );

        ob_start();

        do_action('uwp_output_location', $user, $args['location']);

        $output = ob_get_clean();

        return $output;

    }

    public function show_in_locations() {
        $show_in_locations = array(
            "users" => __("Users Page", 'userswp'),
            "more_info" => __("More info tab", 'userswp'),
            "profile_side" => __("Profile side", 'userswp'),
        );

        $show_in_locations = apply_filters('uwp_ouptut_show_in_locations', $show_in_locations);

        return $show_in_locations;
    }

}