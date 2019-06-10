<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * UsersWP users loop widget.
 *
 * @since 1.1.2
 */
class UWP_Users_Loop_Widget extends WP_Super_Duper {

    /**
     * Register the profile users loop widget with WordPress.
     *
     */
    public function __construct() {


        $options = array(
            'textdomain'    => 'userswp',
            'block-icon'    => 'admin-site',
            'block-category'=> 'widgets',
            'block-keywords'=> "['userswp','user', 'search']",
            'class_name'     => __CLASS__,
            'base_id'       => 'uwp_users_loop',
            'name'          => __('UWP > Users Loop','userswp'),
            'widget_ops'    => array(
                'classname'   => 'uwp-users-list',
                'description' => esc_html__('Displays users loop.','userswp'),
            ),
            'arguments'     => array(
                'layout'  => array(
                    'title' => __('Layout:', 'userswp'),
                    'desc' => __('How the users list should displayed by default.', 'userswp'),
                    'type' => 'select',
                    'options'   => uwp_get_layout_options(),
                    'default'  => 'list',
                    'desc_tip' => true,
                    'advanced' => true
                )
            )

        );


        parent::__construct( $options );
    }

    public function output( $args = array(), $widget_args = array(), $content = '' ) {

        global $layout_class;

        $defaults = array(
            'layout' => 'list',
        );

        $args = wp_parse_args( $args, $defaults );

        $args = apply_filters( 'uwp_widget_users_loop_args', $args, $widget_args, $this );

        $layout_class = uwp_get_layout_class( $args['layout'] );

        ob_start();

        ?>

        <div class="uwp-content-wrap">
            <div class="uwp-users-list">

                <?php do_action('uwp_users_search'); ?>

                <ul class="uwp-users-list-wrap <?php echo $layout_class; ?>" id="uwp_user_items_layout">
                    <?php
                    global $uwp_user;
                    $users = get_uwp_users_list();

                    if($users){
                        do_action( 'uwp_before_user_list_item' );

                        foreach ($users as $uwp_user){
                            $template = uwp_locate_template( 'users-list' );
                            if ($template) {
                                include($template);
                            }
                        }

                        do_action( 'uwp_after_user_list_item' );
                    } else {
                        uwp_no_users_found();
                    }
                    ?>
                </ul>
            </div>
        </div>

        <?php

        $output = ob_get_clean();

        return $output;

    }

}