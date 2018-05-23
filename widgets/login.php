<?php

add_action('widgets_init', 'uwp_init_login_widget');

function uwp_init_login_widget() {

    register_widget("UWP_Login_Widget");

}

class UWP_Login_Widget extends WP_Super_Duper {

    public function __construct() {

        $options = array(
            'textdomain'    => 'userswp',
            'block-icon'    => 'admin-site',
            'block-category'=> 'widgets',
            'block-keywords'=> "['userswp','login']",
            'class_name'     => __CLASS__,
            'base_id'       => 'uwp_login',
            'name'          => __('UWP > Login','userswp'),
            'widget_ops'    => array(
                'classname'   => 'uwp-login-class',
                'description' => esc_html__('Displays login form or current logged in user.','userswp'),
            ),
            'arguments'     => array(
                'title'  => array(
                    'title'       => __( 'Login widget title', 'userswp' ),
                    'desc'        => __( 'Enter login widget title', 'userswp' ),
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

        echo '<div class="uwp_widgets uwp_widget_login">';

        if(is_user_logged_in()) {

            global $current_user;

            $template = new UsersWP_Templates();

            $logout_url = $template->uwp_logout_url();

            echo '<div class="uwp-login-widget user-loggedin">';

            echo '<p>'.__( 'Logged in as ', 'userswp' );

            echo '<a href="'. apply_filters('uwp_profile_link', get_author_posts_url($current_user->ID), $current_user->ID).'">' . get_avatar( $current_user->ID, 35 ). '<strong>'. apply_filters('uwp_profile_display_name', $current_user->display_name).'</strong></a>';

            echo '<span>';

            printf(__( '<a href="%1$s">Log out</a>', 'userswp'), esc_url( $logout_url ));

            echo '</span>';

            echo '</p>';

            echo '</div>';

        } else {

            $temp_obj = new UsersWP_Templates();
            $template = $temp_obj->uwp_locate_template('login');

            echo '<div class="uwp_page">';

            if ($template) {
                include($template);
            }

            echo '</div>';

        }

        echo '</div>';

        $output = ob_get_contents();

        ob_end_clean();

        return trim($output);

    }
}