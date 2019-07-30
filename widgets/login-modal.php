<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * UsersWP forgot password widget.
 *
 * @since 1.0.22
 */

class UWP_Login_Modal_Widget extends WP_Super_Duper {

    public function __construct() {

        $options = array(
            'textdomain'    => 'userswp',
            'block-icon'    => 'admin-site',
            'block-category'=> 'widgets',
            'block-keywords'=> "['userswp','login']",
            'class_name'     => __CLASS__,
            'base_id'       => 'uwp_login_modal',
            'name'          => __('UWP > Login Modal','userswp'),
            'widget_ops'    => array(
                'classname'   => 'uwp-login-modal-class',
                'description' => esc_html__('Displays login form in bootstrap modal.','userswp'),
            ),
            'arguments'     => array(
                'title'  => array(
                    'title'       => __( 'Widget title', 'userswp' ),
                    'desc'        => __( 'Enter widget title', 'userswp' ),
                    'type'        => 'text',
                    'desc_tip'    => true,
                    'default'     => '',
                    'advanced'    => false
                ),
                'form_title'  => array(
                    'title'       => __( 'Form title', 'userswp' ),
                    'desc'        => __( 'Enter the form title', 'userswp' ),
                    'type'        => 'text',
                    'desc_tip'    => true,
                    'default'     => '',
                    'placeholder' => __('Login','userswp'),
                    'advanced'    => true
                ),
                'logged_in_show'  => array(
                    'title' => __('Logged in show', 'userswp'),
                    'desc' => __('What to show when logged in.', 'userswp'),
                    'type' => 'select',
                    'options'   =>  array(
                        ""        =>  __('User Dashboard (default)', 'userswp'),
                        "simple"        =>  __('Simple username and logout link', 'userswp'),
                        "empty"        =>  __('Nothing', 'userswp'),
                    ),
                    'default'  => '',
                    'desc_tip' => true,
                    'advanced' => true
                ),
                'design_style'  => array(
                    'title' => __('Design Style', 'userswp'),
                    'desc' => __('The design style to use.', 'userswp'),
                    'type' => 'select',
                    'options'   =>  array(
                        ""        =>  __('default', 'userswp'),
                        "bs1"        =>  __('Style 1', 'userswp'),
                        "bs2"        =>  __('Style 2', 'userswp'),
                        "bs3"        =>  __('Style 3', 'userswp'),
                        "bs4"        =>  __('Style 4', 'userswp'),
                    ),
                    'default'  => '',
                    'desc_tip' => true,
                    'advanced' => true
                )

            )

        );

        // GD
        if(class_exists( 'GeoDirectory' )){
            $options['arguments']['disable_gd'] = array(
                'title' => __("Disable GeoDirectory links from the user dashboard.", 'userswp'),
                'type' => 'checkbox',
                'desc_tip' => true,
                'value'  => '1',
                'default'  => '',
                'advanced' => true,
                'element_require' => '[%logged_in_show%]==""',
            );
        }

        // WPI
        if(class_exists( 'WPInv_Plugin' )){
            $options['arguments']['disable_wpi'] = array(
                'title' => __("Disable WP Invoicing links from the user dashboard.", 'userswp'),
                'type' => 'checkbox',
                'desc_tip' => true,
                'value'  => '1',
                'default'  => '',
                'advanced' => true,
                'element_require' => '[%logged_in_show%]==""',
            );
        }

        parent::__construct( $options );
    }

    public function output( $args = array(), $widget_args = array(), $content = '' ) {

        $defaults = array(
            'form_title'      => __('Login','userswp'),
            'logged_in_show'     => '',
        );

        /**
         * Parse incoming $args into an array and merge it with $defaults
         */
        $args = wp_parse_args( $args, $defaults );

        // if logged in and set to show nothing then bail.
        if(is_user_logged_in() && $args['logged_in_show']=='empty'){
            return '';
        }

        ob_start();

        echo '<div class="uwp_widgets uwp_widget_login_modal">';

        if(is_user_logged_in()) {

            if($args['logged_in_show']=='simple'){
                self::simple_output($args);
            }else{
                self::advanced_output($args);
            }

        } else {
            
            global $uwp_login_widget_args;
            $uwp_login_widget_args = $args;

            $design_style = !empty($args['design_style']) ? esc_attr($args['design_style']) : uwp_get_option("design_style",'bootstrap');
            $template = $design_style ? $design_style."/login" : "login";

            $login_text = apply_filters('uwp_ajax_login_button_text', __('Login', 'userswp'));
	        $login_modal_title = apply_filters('uwp_ajax_login_modal_title', __('Login to your account', 'userswp'));
	        ?>
            <ul class="navbar-nav ml-auto d-flex">
                <li class="nav-item">
	                <a href="#" class="nav-link" data-toggle="modal" data-target="#uwp_login_modal"><?php echo $login_text; ?></a>
                </li>
            </ul>

	        <div class="modal fade" id="uwp_login_modal" tabindex="-1" role="dialog" aria-hidden="true">
		        <div class="modal-dialog" role="document">
			        <div class="modal-content">
				        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel"><?php echo $login_modal_title; ?></h5>
					        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
						        <span aria-hidden="true">&times;</span>
					        </button>
				        </div>
				        <div class="modal-body">
					        <?php uwp_locate_template($template); ?>
				        </div>
			        </div>
		        </div>
	        </div>
			<?php
        }

        echo '</div>';

        $output = ob_get_clean();

        return trim($output);

    }

    public static function advanced_output($args){
        global $uwp_login_widget_args;
        $uwp_login_widget_args = $args;

        echo '<div class="uwp_page">';

        uwp_locate_template('dashboard');

        echo '</div>';
    }

    public static function simple_output($args){
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
    }
}