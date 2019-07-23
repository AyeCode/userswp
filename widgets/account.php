<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * UsersWP account widget.
 *
 * @since 1.0.22
 */
class UWP_Account_Widget extends WP_Super_Duper {

    /**
     * Register the account widget with WordPress.
     *
     */
    public function __construct() {


        $options = array(
            'textdomain'    => 'userswp',
            'block-icon'    => 'admin-site',
            'block-category'=> 'widgets',
            'block-keywords'=> "['userswp','account']",
            'class_name'     => __CLASS__,
            'base_id'       => 'uwp_account',
            'name'          => __('UWP > Account','userswp'),
            'widget_ops'    => array(
                'classname'   => 'uwp-account-class',
                'description' => esc_html__('Displays account form.','userswp'),
            ),
            'arguments'     => array(
                'title'  => array(
                    'title'       => __( 'Widget title', 'userswp' ),
                    'desc'        => __( 'Enter widget title.', 'userswp' ),
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
                    'placeholder' => __('Account','userswp'),
                    'advanced'    => true
                ),
            )

        );


        parent::__construct( $options );
    }

    public function output( $args = array(), $widget_args = array(), $content = '' ) {

        if (!is_user_logged_in()) {
            return;
        }

        $defaults = array(
            'form_title'      => __('Account','userswp'),
        );

        /**
         * Parse incoming $args into an array and merge it with $defaults
         */
        $args = wp_parse_args( $args, $defaults );

        global $uwp_account_widget_args;
        $uwp_account_widget_args = $args;

        ob_start();

        echo '<div class="uwp_widgets uwp_widget_account">';

        echo '<div class="uwp_page">';

        uwp_locate_template('account');

        echo '</div>';

        echo '</div>';

        $output = ob_get_contents();

        ob_end_clean();

        return trim($output);

    }

}