<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * UsersWP user user title widget.
 *
 * @since 1.1.2
 */
class UWP_User_Title_Widget extends WP_Super_Duper {

    /**
     * Register the user user title widget with WordPress.
     *
     */
    public function __construct() {


        $options = array(
            'textdomain'    => 'userswp',
            'block-icon'    => 'admin-site',
            'block-category'=> 'widgets',
            'block-keywords'=> "['userswp','user']",
            'class_name'     => __CLASS__,
            'base_id'       => 'uwp_user_title',
            'name'          => __('UWP > User Title','userswp'),
            'no_wrap'       => true,
            'widget_ops'    => array(
                'classname'   => 'uwp-user-title',
                'description' => esc_html__('Displays user name.','userswp'),
            ),
            'arguments'     => array(
                'tag'  => array(
                    'title' => __('Header Tag:', 'userswp'),
                    'desc' => __('Header tag for the user title.', 'userswp'),
                    'type' => 'select',
                    'options'   =>  array(
                        "h1" => "h1",
                        "h2" => "h2",
                        "h3" => "h3",
                        "h4" => "h4",
                        "h5" => "h5",
                        "h6" => "h6",
                    ),
                    'default'  => 'h2',
                    'desc_tip' => true,
                )
            )

        );


        parent::__construct( $options );
    }

    public function output( $args = array(), $widget_args = array(), $content = '' ) {

        $user = uwp_get_user_by_author_slug();

        if(!$user && is_user_logged_in()){
            $user = get_userdata(get_current_user_id());
        }

        $defaults = array(
            'tag'      => 'h2',
        );

        $args = wp_parse_args( $args, $defaults );

        $title_tag = empty( $args['tag'] ) ? 'h2' : apply_filters( 'uwp_widget_user_title_tag', $args['tag'], $args, $widget_args, $this );

        ob_start();

        do_action('uwp_user_title', $user, $title_tag);

        $output = ob_get_clean();

        return $output;

    }

}