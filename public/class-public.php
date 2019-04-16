<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://wpgeodirectory.com
 * @since      1.0.0
 *
 * @package    userswp
 * @subpackage userswp/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    userswp
 * @subpackage userswp/public
 * @author     GeoDirectory Team <info@wpgeodirectory.com>
 */
class UsersWP_Public {

    
    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {

        if (is_uwp_page()) {
            // include only in uwp pages
            wp_register_style('jquery-ui', USERSWP_PLUGIN_URL .  'assets/css/jquery-ui.css');
            wp_enqueue_style( 'jquery-ui' );
        }

        if (is_uwp_current_user_profile_page()) {
            // include only profile pages
            wp_enqueue_style( 'jcrop' );

            if (is_user_logged_in()) {
                wp_enqueue_media();
            }
        }

        $enable_timepicker_in_register = false;

        if (is_uwp_register_page() || is_uwp_account_page()) {
            if (is_uwp_register_page()) {
                $fields = get_register_form_fields();
            } else {
                // account page
                $fields = get_account_form_fields();
            }
            if (!empty($fields)) {
                foreach ($fields as $field) {
                    if ($field->field_type == 'time') {
                        $enable_timepicker_in_register = true;
                    }
                }
            }
        }
        

        if ($enable_timepicker_in_register) {
            // time fields available only in register and account pages
            wp_enqueue_style( "uwp_timepicker_css", USERSWP_PLUGIN_URL . 'assets/css/jquery.ui.timepicker.css', array(), USERSWP_VERSION, 'all' );
        }

        //widget styles for all pages
        wp_enqueue_style( "uwp_widget_css", USERSWP_PLUGIN_URL . 'assets/css/widgets.css', array(), USERSWP_VERSION, 'all' );
        wp_enqueue_style( "select2", USERSWP_PLUGIN_URL . 'assets/css/select2/select2.css', array(), USERSWP_VERSION, 'all' );
        wp_enqueue_style( USERSWP_NAME, USERSWP_PLUGIN_URL . 'assets/css/users-wp.css', array(), USERSWP_VERSION, 'all' );
        wp_register_style( 'uwp-authorbox', USERSWP_PLUGIN_URL . 'assets/css/authorbox.css', array(), USERSWP_VERSION, 'all' );

    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {

        $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

        if (is_uwp_page()) {
            // include only in uwp pages
            wp_enqueue_script( 'jquery-ui-core', array( 'jquery' ) );    
        }

        if (is_uwp_current_user_profile_page()) {
            // include only profile pages
            wp_enqueue_script( 'jcrop', array( 'jquery' ) );
        }
        
        $enable_timepicker_in_register = false;
        $enable_timepicker_in_account = false;

        $enable_datepicker_in_register = false;
        $enable_datepicker_in_account = false;
        
        if (is_uwp_register_page() || is_uwp_account_page()) {
            if (is_uwp_register_page()) {
                $fields = get_register_form_fields();    
            } else {
                // account page
                $fields = get_account_form_fields();
            }
            
            if (!empty($fields)) {
                foreach ($fields as $field) {
                    if ($field->field_type == 'time') {
                        $enable_timepicker_in_register = true;
                    }

                    if ($field->field_type == 'datepicker') {
                        $enable_datepicker_in_register = true;
                    }
                }
            }
        }
        
        if ($enable_timepicker_in_register || $enable_timepicker_in_account) {
            // time fields available only in register and account pages
            wp_enqueue_script( "uwp_timepicker", USERSWP_PLUGIN_URL . 'assets/js/jquery.ui.timepicker' . $suffix . '.js', array( 'jquery', 'jquery-ui-datepicker', 'jquery-ui-core' ), null, false );
        }


        if ($enable_datepicker_in_register || $enable_datepicker_in_account) {
            // date fields available only in register and account pages
            wp_enqueue_script( 'jquery-ui-datepicker', array( 'jquery' ) );
        }

        if (is_uwp_profile_page() ) {
            wp_enqueue_script( 'jquery-ui-progressbar', array( 'jquery' ) );
        }

        wp_enqueue_script('select2', USERSWP_PLUGIN_URL . 'assets/js/select2/select2.full' . $suffix . '.js', array( 'jquery' ), USERSWP_VERSION );

        // include only in uwp pages
        wp_enqueue_script( USERSWP_NAME, USERSWP_PLUGIN_URL . 'assets/js/users-wp' . $suffix . '.js', array( 'jquery' ), USERSWP_VERSION, false );

        //load CountrySelect
        wp_enqueue_script( "country-select", USERSWP_PLUGIN_URL . 'assets/js/countrySelect' . $suffix . '.js', array( 'jquery' ), USERSWP_VERSION, false );

        $country_data = uwp_get_country_data();
        wp_localize_script(USERSWP_NAME, 'uwp_country_data', $country_data);

        $uwp_localize_data = uwp_get_localize_data();
        wp_localize_script(USERSWP_NAME, 'uwp_localize_data', $uwp_localize_data);
        
    }

}