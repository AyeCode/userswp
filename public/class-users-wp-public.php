<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://wpgeodirectory.com
 * @since      1.0.0
 *
 * @package    Users_WP
 * @subpackage Users_WP/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Users_WP
 * @subpackage Users_WP/public
 * @author     GeoDirectory Team <info@wpgeodirectory.com>
 */
class Users_WP_Public {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $users_wp    The ID of this plugin.
     */
    private $users_wp;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $users_wp       The name of the plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct( $users_wp, $version ) {

        $this->plugin_name = $users_wp;
        $this->version = $version;

    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {

        /**
         * An instance of this class should be passed to the run() function
         * defined in Users_WP_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Users_WP_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_style( 'jcrop' );
        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'assets/css/users-wp.css', array(), $this->version, 'all' );

        if (is_page()) {
            global $post;
            $current_page_id = $post->ID;
            $register_page = uwp_get_option('register_page', false);
            $account_page = uwp_get_option('account_page', false);
            $profile_page = uwp_get_option('profile_page', false);
            
            if (( $register_page && ((int) $register_page ==  $current_page_id ) ) ||
                ( $account_page && ((int) $account_page ==  $current_page_id ) )) {
                wp_enqueue_style( "uwp_chosen_css", plugin_dir_url( __FILE__ ) . 'assets/css/chosen.css', array(), $this->version, 'all' );
            }

            if ($profile_page && is_user_logged_in() && ((int) $profile_page ==  $current_page_id )) {
                wp_enqueue_media();
            }
        }
        global $wp_styles;
        $srcs = array_map('basename', (array) wp_list_pluck($wp_styles->registered, 'src') );
        if ( in_array('font-awesome.css', $srcs) || in_array('font-awesome.min.css', $srcs)  ) {
            /* echo 'font-awesome.css registered'; */
        } else {
            wp_register_style('font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css', array(), $this->version);
            wp_enqueue_style('font-awesome');
        }

        wp_register_style('jquery-ui', '//ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css');
        wp_enqueue_style( 'jquery-ui' );


    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {

        /**
         * An instance of this class should be passed to the run() function
         * defined in Users_WP_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Users_WP_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_script( 'jquery-ui-core', array( 'jquery' ) );
        wp_enqueue_script( 'jcrop', array( 'jquery' ) );
        wp_enqueue_script( 'jquery-ui-datepicker', array( 'jquery' ) );
        wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'assets/js/users-wp.js', array( 'jquery' ), null, false );

        if (is_page()) {
            global $post;
            $current_page_id = $post->ID;
            $register_page = uwp_get_option('register_page', false);
            $account_page = uwp_get_option('account_page', false);


            if (( $register_page && ((int) $register_page ==  $current_page_id ) ) ||
                ( $account_page && ((int) $account_page ==  $current_page_id ) )) {
                wp_dequeue_script('chosen');
                wp_enqueue_script( "uwp_chosen", plugin_dir_url( __FILE__ ) . 'assets/js/chosen.jquery.js', array( 'jquery' ), $this->version, false );
            }
        }
        
    }

}