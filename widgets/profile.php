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
            'widget_ops'    => array(
                'classname'   => 'uwp-profile-class',
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
            )

        );


        parent::__construct( $options );
    }

    public function output( $args = array(), $widget_args = array(), $content = '' ) {

        ob_start();

        echo '<div class="uwp_widgets uwp_widget_profile">';

        $temp_obj = new UsersWP_Templates();

        $template = $temp_obj->uwp_locate_template('profile');

        echo '<div class="uwp_page">';

        if ($template) {
            include($template);
        }

        echo '</div>';

        echo '</div>';

        $output = ob_get_contents();

        ob_end_clean();

        return trim($output);

    }

}