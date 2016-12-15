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
        if (is_uwp_page()) {
            // include only in uwp pages
            wp_register_style('jquery-ui', '//ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css');
            wp_enqueue_style( 'jquery-ui' );
        }

        if (is_uwp_profile_page()) {
            // include only profile pages
            wp_enqueue_style( 'jcrop' );

            if (is_user_logged_in()) {
                wp_enqueue_media();
            }
        }

        $enable_timepicker_in_register = false;
        $enable_timepicker_in_account = false;

        $enable_chosen_in_register = false;
        $enable_chosen_in_account = false;

        if (is_uwp_register_page() ) {
            $fields = get_register_form_fields();
            if (!empty($fields)) {
                foreach ($fields as $field) {
                    if ($field->field_type == 'time') {
                        $enable_timepicker_in_register = true;
                    }

                    if ($field->field_type == 'multiselect') {
                        $enable_chosen_in_register = true;
                    }
                }
            }
        }

        if (is_uwp_account_page() ) {
            $fields = get_account_form_fields();
            if (!empty($fields)) {
                foreach ($fields as $field) {
                    if ($field->field_type == 'time') {
                        $enable_timepicker_in_account = true;
                    }

                    if ($field->field_type == 'multiselect') {
                        $enable_chosen_in_account = true;
                    }
                }
            }
        }

        if ($enable_timepicker_in_register || $enable_timepicker_in_account) {
            // time fields available only in register and account pages
            wp_enqueue_style( "uwp_timepicker_css", plugin_dir_url( __FILE__ ) . 'assets/css/jquery.ui.timepicker.css', array(), null, 'all' );
        }


        if ($enable_chosen_in_register || $enable_chosen_in_account) {
            // chosen fields (multiselect) available only in register and account pages
            wp_enqueue_style( "uwp_chosen_css", plugin_dir_url( __FILE__ ) . 'assets/css/chosen.css', array(), null, 'all' );
        }

        if (is_uwp_page()) {
            // include only in uwp pages
            global $wp_styles;
            $srcs = array_map('basename', (array) wp_list_pluck($wp_styles->registered, 'src') );
            if ( in_array('font-awesome.css', $srcs) || in_array('font-awesome.min.css', $srcs)  ) {
                /* echo 'font-awesome.css registered'; */
            } else {
                wp_register_style('font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css', array(), $this->version);
                wp_enqueue_style('font-awesome');
            }
            wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'assets/css/users-wp.css', array(), null, 'all' );
        }

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
        if (is_uwp_page()) {
            // include only in uwp pages
            wp_enqueue_script( 'jquery-ui-core', array( 'jquery' ) );    
        }

        if (is_uwp_profile_page()) {
            // include only profile pages
            wp_enqueue_script( 'jcrop', array( 'jquery' ) );
        }
        
        $enable_timepicker_in_register = false;
        $enable_timepicker_in_account = false;

        $enable_datepicker_in_register = false;
        $enable_datepicker_in_account = false;

        $enable_chosen_in_register = false;
        $enable_chosen_in_account = false;
        
        if (is_uwp_register_page() ) {
            $fields = get_register_form_fields();
            if (!empty($fields)) {
                foreach ($fields as $field) {
                    if ($field->field_type == 'time') {
                        $enable_timepicker_in_register = true;
                    }

                    if ($field->field_type == 'datepicker') {
                        $enable_datepicker_in_register = true;
                    }

                    if ($field->field_type == 'multiselect') {
                        $enable_chosen_in_register = true;
                    }
                }
            }
        }

        if (is_uwp_account_page() ) {
            $fields = get_account_form_fields();
            if (!empty($fields)) {
                foreach ($fields as $field) {
                    if ($field->field_type == 'time') {
                        $enable_timepicker_in_account = true;
                    }

                    if ($field->field_type == 'datepicker') {
                        $enable_datepicker_in_account = true;
                    }

                    if ($field->field_type == 'multiselect') {
                        $enable_chosen_in_account = true;
                    }
                }
            }
        }

        if ($enable_timepicker_in_register || $enable_timepicker_in_account) {
            // time fields available only in register and account pages
            wp_enqueue_script( "uwp_timepicker", plugin_dir_url( __FILE__ ) . 'assets/js/jquery.ui.timepicker.min.js', array( 'jquery', 'jquery-ui-datepicker', 'jquery-ui-core' ), null, false );
        }


        if ($enable_datepicker_in_register || $enable_datepicker_in_account) {
            // date fields available only in register and account pages
            wp_enqueue_script( 'jquery-ui-datepicker', array( 'jquery' ) );
        }


        if ($enable_chosen_in_register || $enable_chosen_in_account) {
            // chosen fields (multiselect) available only in register and account pages
            wp_dequeue_script('chosen');
            wp_enqueue_script( "uwp_chosen", plugin_dir_url( __FILE__ ) . 'assets/js/chosen.jquery.js', array( 'jquery' ), null, false );
        }

        if (is_uwp_page()) {
            // include only in uwp pages
            wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'assets/js/users-wp.js', array( 'jquery' ), null, false );
        }
        
    }

}