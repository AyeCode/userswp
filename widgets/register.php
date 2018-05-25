<?php
add_action('widgets_init', 'uwp_init_register_widget');

function uwp_init_register_widget() {

    register_widget("UWP_Register_Widget");

}

class UWP_Register_Widget extends WP_Super_Duper {

    public function __construct() {

        $options = array(
            'textdomain'    => 'userswp',
            'block-icon'    => 'admin-site',
            'block-category'=> 'widgets',
            'block-keywords'=> "['userswp','register']",
            'class_name'     => __CLASS__,
            'base_id'       => 'uwp_register',
            'name'          => __('UWP > Register','userswp'),
            'widget_ops'    => array(
                'classname'   => 'uwp-register-class',
                'description' => esc_html__('Displays register form.','userswp'),
            ),
            'arguments'     => array(
                'title'  => array(
                    'title'       => __( 'Register widget title', 'userswp' ),
                    'desc'        => __( 'Enter register widget title.', 'userswp' ),
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
            return;
        }

        ob_start();

        echo '<div class="uwp_widgets uwp_widget_register">';

        $temp_obj = new UsersWP_Templates();

        $template = $temp_obj->uwp_locate_template('register');

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