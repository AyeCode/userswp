<?php
add_action('widgets_init', 'uwp_init_login_widget');
function uwp_init_login_widget() {
    register_widget("UWP_Login_Widget");
}
class UWP_Login_Widget extends WP_Widget
{

    /**
     * Class constructor.
     */
    function __construct()
    {
        $widget_ops = array(
            'description' => __('Displays Login Form', 'userswp'),
            'classname' => 'uwp_progress_users',
        );
        parent::__construct(false, $name = _x('UWP > Login', 'widget name', 'userswp'), $widget_ops);

    }

    /**
     * Display the widget.
     *
     * @param array $args Widget arguments.
     * @param array $instance The widget settings, as saved by the user.
     */
    function widget($args, $instance)
    {
        extract($args, EXTR_SKIP);
        $title = empty($instance['title']) ? __('Login', 'userswp') : apply_filters('uwp_login_widget_title', $instance['title']);

        echo '<div class="uwp_widgets">';
        echo $before_widget;
        if ($title) {
            echo $before_title . $title . $after_title;
        }
        if(is_user_logged_in()){
            global $current_user;
            $template = new UsersWP_Templates();
            $logout_url = $template->uwp_logout_url();
            echo '<div class="uwp-login-widget user-loggedin">';
            echo '<p>'.__( 'Logged in as ', 'userswp' );
            echo '<a href="'. apply_filters('uwp_profile_link', get_author_posts_url($current_user->ID), $current_user->ID).'">' . get_avatar( $current_user->ID, 35, uwp_get_default_avatar_uri() ). '<strong>'. apply_filters('uwp_profile_display_name', $current_user->display_name).'</strong></a>';
            echo '<span>';
            printf(__( '<a href="%1$s">Log out</a>', 'userswp'), esc_url( $logout_url ));
            echo '</span></p>';
            echo '</div>';
        } else {
            echo do_shortcode('[uwp_login]');
        }
        echo $after_widget;
        echo '</div>';
    }

    function update($new_instance, $old_instance)
    {
        //save the widget
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        return $instance;
    }

    function form($instance)
    {
        //widgetform in backend
        $instance = wp_parse_args((array)$instance, array(
            'title' => __('Login', 'userswp'),
        ));
        $title = strip_tags($instance['title']);
        ?>
        <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php echo __("Widget Title:", 'userswp'); ?> <input class="widefat"
                                                                                                                     id="<?php echo $this->get_field_id('title'); ?>"
                                                                                                                     name="<?php echo $this->get_field_name('title'); ?>"
                                                                                                                     type="text"
                                                                                                                     value="<?php echo esc_attr($title); ?>"/></label>
        </p>
        <?php
    }

}