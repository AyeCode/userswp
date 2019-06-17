<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * UsersWP reset password widget.
 *
 * @since 1.0.22
 */
class UWP_Reset_Widget extends WP_Super_Duper {

    /**
     * Register the reset password widget with WordPress.
     *
     */
    public function __construct() {


        $options = array(
            'textdomain'    => 'userswp',
            'block-icon'    => 'admin-site',
            'block-category'=> 'widgets',
            'block-keywords'=> "['userswp','reset']",
            'class_name'     => __CLASS__,
            'base_id'       => 'uwp_reset',
            'name'          => __('UWP > Reset','userswp'),
            'widget_ops'    => array(
                'classname'   => 'uwp-reset-class',
                'description' => esc_html__('Displays reset password form.','userswp'),
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

        if (is_user_logged_in()) {
            return false;
        }

        ob_start();

        echo '<div class="uwp_widgets uwp_widget_reset">';

        echo '<div class="uwp_page">';

        uwp_locate_template('reset');

        echo '</div>';

        echo '</div>';

        $output = ob_get_contents();

        ob_end_clean();

        return trim($output);

    }

}