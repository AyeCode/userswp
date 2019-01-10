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
                'description' => esc_html__('Displays users form.','userswp'),
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

        echo '<div class="uwp_widgets uwp_widget_users">';

        $temp_obj = new UsersWP_Templates();

        $template = $temp_obj->uwp_locate_template('users');

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