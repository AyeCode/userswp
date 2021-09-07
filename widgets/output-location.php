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
            'no_wrap'       => true,
            'widget_ops'    => array(
                'description' => esc_html__('Displays fields which are selected to display in selected output location from form builder.','userswp'),
            ),
            'arguments'     => array(
                'location'  => array(
                    'title' => __('Location:', 'userswp'),
                    'desc' => __('The location type to output.', 'userswp'),
                    'type' => 'select',
                    'options' => $this->show_in_locations(),
                    'desc_tip' => true,
                    'default'  => '',
                    'advanced' => false
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

        $user = uwp_get_displayed_user();

        $defaults = array(
            'location' => '',
        );

        $args = wp_parse_args( $args, $defaults );

        $args = apply_filters( 'uwp_widget_output_location_args', $args, $widget_args, $this );

        ob_start();

        echo '<div class="uwp-output-location uwp-output-location-'.esc_attr($args['location']).'">';

        do_action('uwp_output_location', $user, $args['location']);

        echo '</div>';

	    return ob_get_clean();

    }

	/**
	 * Returns locations array
	 *
	 * @return array
	 */
    public function show_in_locations() {
        $show_in_locations = array(
            "users" => __("Users Page", 'userswp'),
            "more_info" => __("More info tab", 'userswp'),
            "profile_side" => __("Profile side", 'userswp'),
        );

        $show_in_locations = apply_filters('uwp_output_show_in_locations', $show_in_locations);

        return $show_in_locations;
    }

}