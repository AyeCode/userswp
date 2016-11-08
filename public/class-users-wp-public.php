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
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Users_WP_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Users_WP_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'assets/css/users-wp.css', array(), $this->version, 'all' );

    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Users_WP_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Users_WP_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_script( $this->plugin_name.'_imagecrop', plugin_dir_url( __FILE__ ) . 'assets/js/jquery.imgareaselect.min.js', array( 'jquery' ), $this->version, false );
        wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'assets/js/users-wp.js', array( 'jquery' ), $this->version, false );

    }

}