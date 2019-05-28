<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * UsersWP profile section widget.
 *
 * @since 1.1.2
 */
class UWP_Profile_Section_Widget extends WP_Super_Duper {

    /**
     * Register the profile section widget with WordPress.
     *
     */
    public function __construct() {


        $options = array(
            'textdomain'    => 'userswp',
            'block-icon'    => 'admin-site',
            'block-category'=> 'widgets',
            'block-keywords'=> "['userswp','profile']",
            'class_name'     => __CLASS__,
            'base_id'       => 'uwp_profile_section',
            'name'          => __('UWP > Profile Section','userswp'),
            'no_wrap'       => true,
            'widget_ops'    => array(
                'classname'   => 'uwp-profile-section',
                'description' => esc_html__('Display section to contain other elements.','userswp'),
            ),
            'arguments'     => array(
                'type'  => array(
                    'title' => __('Type:', 'userswp'),
                    'desc' => __('This is the opening or closing section.', 'userswp'),
                    'type' => 'select',
                    'options'   =>  array(
                        "open" => __('Open', 'userswp'),
                        "close" => __('Close', 'userswp'),
                    ),
                    'default'  => 'open',
                    'desc_tip' => true,
                    'advanced' => false
                ),
                'position'  => array(
                    'title' => __('Position:', 'userswp'),
                    'desc' => __('This is position of the section.', 'userswp'),
                    'type' => 'select',
                    'options'   =>  array(
                        "left" => __('Left', 'userswp'),
                        "right" => __('Right', 'userswp'),
                        "full" => __('Full', 'userswp'),
                    ),
                    'default'  => 'full',
                    'desc_tip' => true,
                    'advanced' => false
                ),
            )

        );


        parent::__construct( $options );
    }

    public function output( $args = array(), $widget_args = array(), $content = '' ) {

        $defaults = array(
            'type' => 'open',
            'position' => 'full',
        );

        $args = wp_parse_args( $args, $defaults );
        $output = '';

        if(isset($args['type']) && $args['type']=='open'){
            $class = !empty($args['class']) ? esc_attr($args['class']) : '';
            $position = isset($args['position']) ? $args['position'] : 'full';
            $output = '<div class="uwp_page uwp-section-'.$position.' '.$class.'">';
        }elseif(isset($args['type']) && $args['type']=='close'){
            $output = "</div>";
        }

        return $output;

    }

}