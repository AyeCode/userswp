<?php
add_action('widgets_init', 'uwp_init_register_widget');
function uwp_init_register_widget() {
    register_widget("UWP_Register_Widget");
}
class UWP_Register_Widget extends WP_Widget
{

    /**
     * Class constructor.
     */
    function __construct()
    {
        $widget_ops = array(
            'description' => __('Displays Register Form', 'userswp'),
            'classname' => 'uwp_progress_users',
        );
        parent::__construct(false, $name = _x('UWP > Register', 'widget name', 'userswp'), $widget_ops);

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
        $title = empty($instance['title']) ? __('Register', 'userswp') : apply_filters('uwp_register_widget_title', $instance['title']);

        echo '<div class="uwp_widgets">';
        echo $before_widget;
        if ($title) {
            echo $before_title . $title . $after_title;
        }
        echo do_shortcode('[uwp_register]');
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
            'title' => __('Register', 'userswp'),
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