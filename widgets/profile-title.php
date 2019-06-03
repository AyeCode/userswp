<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * UsersWP profile user title widget.
 *
 * @since 1.1.2
 */
class UWP_Profile_Title_Widget extends WP_Super_Duper {

    /**
     * Register the profile user title widget with WordPress.
     *
     */
    public function __construct() {


        $options = array(
            'textdomain'    => 'userswp',
            'block-icon'    => 'admin-site',
            'block-category'=> 'widgets',
            'block-keywords'=> "['userswp','profile']",
            'class_name'     => __CLASS__,
            'base_id'       => 'uwp_profile_title',
            'name'          => __('UWP > Profile Title','userswp'),
            'no_wrap'       => true,
            'widget_ops'    => array(
                'classname'   => 'uwp-profile-title',
                'description' => esc_html__('Displays user name.','userswp'),
            ),
            'arguments'     => array(
                'tag'  => array(
                    'title' => __('Header Tag:', 'userswp'),
                    'desc' => __('Header tag for the profile title.', 'userswp'),
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

        $title_tag = empty( $args['tag'] ) ? 'h2' : apply_filters( 'uwp_widget_profile_title_tag', $args['tag'], $args, $widget_args, $this );

        ob_start();

        do_action('uwp_profile_title', $user, $title_tag);

        $output = ob_get_clean();

        return $output;

    }

}