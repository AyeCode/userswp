<?php
add_action('widgets_init', create_function('', 'return register_widget("UWP_Login_Widget");'));
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
        if (is_user_logged_in()) {
            return;
        }
        extract($args, EXTR_SKIP);
        $title = empty($instance['title']) ? __('Login', 'userswp') : apply_filters('uwp_login_widget_title', $instance['title']);

        echo '<div class="uwp_widgets">';
        echo $before_widget;
        if ($title) {
            echo $before_title . $title . $after_title;
        }
        echo do_shortcode('[uwp_login]');
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