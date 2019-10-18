<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * UsersWP user avatar widget.
 *
 * @since 1.1.2
 */
class UWP_User_Avatar_Widget extends WP_Super_Duper {

    /**
     * Register the user avatar widget with WordPress.
     *
     */
    public function __construct() {


        $options = array(
            'textdomain'    => 'userswp',
            'block-icon'    => 'admin-site',
            'block-category'=> 'widgets',
            'block-keywords'=> "['userswp','user']",
            'class_name'     => __CLASS__,
            'base_id'       => 'uwp_user_avatar',
            'name'          => __('UWP > User Avatar','userswp'),
            'no_wrap'       => true,
            'widget_ops'    => array(
                'classname'   => 'uwp-user-avatar',
                'description' => esc_html__('Displays user name.','userswp'),
            ),
            'arguments'     => array(
                'tag'  => array(
                    'title' => __('Header Tag:', 'userswp'),
                    'desc' => __('Header tag for the user name.', 'userswp'),
                    'type' => 'select',
                    'options'   =>  array(
                        "div"=> "div",
                        "p"  => "p",
                        "h1" => "h1",
                        "h2" => "h2",
                        "h3" => "h3",
                        "h4" => "h4",
                        "h5" => "h5",
                        "h6" => "h6",
                    ),
                    'default'  => 'p',
                    'desc_tip' => true,
                ),
                'size'  => array(
                    'title' => __('Avatar size:', 'userswp'),
                    'desc' => __('Avatar image size in px. Default is 50px.', 'userswp'),
                    'type' => 'number',
                    'desc_tip' => true,
                    'default'  => '50',
                    'advanced' => true
                ),
                'link'  => array(
                    'title' => __("Link to profile?:", 'geodirectory'),
                    'desc' => __('Link avatar image and name to user\'s profile page.', 'userswp'),
                    'type' => 'checkbox',
                    'desc_tip' => true,
                    'value'  => '1',
                    'default'  => '0',
                    'advanced' => true
                ),
                'user_id'  => array(
                    'title' => __('User ID:', 'userswp'),
                    'desc' => __('Leave blank to use current user ID.', 'userswp'),
                    'type' => 'number',
                    'desc_tip' => true,
                    'default'  => '',
                    'advanced' => true
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

        $defaults = array(
            'tag'      => 'p',
        );

        $args = wp_parse_args( $args, $defaults );

        $args = apply_filters( 'uwp_widget_user_avatar_args', $args, $widget_args, $this );

        $title_tag = empty( $args['tag'] ) ? 'p' : $args['tag'];
        $size = empty( $args['size'] ) ? 50 : $args['size'];
        $is_link = 1 == $args['link'] ? 1 : 0;

        if(isset($args['user_id']) && $args['user_id'] > 0){
            $user = get_userdata((int)$args['user_id']);
            $display_name = $user->display_name;
            $user_id = $user->ID;
            $link = apply_filters('uwp_profile_link', get_author_posts_url($user_id), $user_id);
        } elseif(is_user_logged_in()){
            $user = get_userdata(get_current_user_id());
            $display_name = $user->display_name;
            $user_id = $user->ID;
            $link = apply_filters('uwp_profile_link', get_author_posts_url($user_id), $user_id);
        } else {
            $display_name = __('Guest', 'userswp');
            $user_id = 0;
            $link = uwp_get_login_page_url();
        }

        $output = '';

        ob_start();

        $output .= '<div class="uwp-user-avatar-image">';

        if(1 == $is_link){
            $output .= '<a href="'.$link.'" class="uwp-user-avatar-link">';
        }

        $output .= get_avatar( $user_id, $size );

        if(1 == $is_link){
            $output .= '</a>';
        }

        $output .= '</div>';

        $output .= '<'.esc_attr($title_tag).' class="uwp-user-avatar-title" data-user="'.$user_id.'">';

        if(1 == $is_link){
            $output .= '<a href="'.$link.'" class="uwp-user-avatar-link">';
        }

        $output .= apply_filters('uwp_profile_display_name', $display_name, $args, $widget_args, $this);

        if(1 == $is_link){
            $output .= '</a>';
        }

        $output .= '</'.esc_attr($title_tag).'>';

        echo $output;

        $output = ob_get_clean();

        return apply_filters( 'uwp_widget_user_avatar_output', $output, $args, $widget_args, $this );

    }

}