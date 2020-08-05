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


        // Scripts if user on own profile page.
        if (is_uwp_current_user_profile_page()) {
            // include only profile pages
            wp_enqueue_style( 'jcrop' );

            if (is_user_logged_in()) {
                wp_enqueue_media();
            }
        }


	    wp_register_style( 'jquery-ui', USERSWP_PLUGIN_URL .  'assets/css/jquery-ui.css' );

        // maybe add bootstrap
        if(empty(uwp_get_option("design_style","bootstrap"))){
            wp_enqueue_style( USERSWP_NAME, USERSWP_PLUGIN_URL . 'assets/css/users-wp.css', array(), USERSWP_VERSION, 'all' );
            wp_register_style( 'uwp-authorbox', USERSWP_PLUGIN_URL . 'assets/css/authorbox.css', array(), USERSWP_VERSION, 'all' );
        }else{
	        //@todo this is not actually being used yet, enable when if it is.
        	//wp_enqueue_style( "uwp", USERSWP_PLUGIN_URL . 'assets/css/bootstrap/uwp.css', array(), USERSWP_VERSION, 'all' );
        }

    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {

        $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

        // Core UWP JS
        wp_enqueue_script( USERSWP_NAME, USERSWP_PLUGIN_URL . 'assets/js/users-wp' . $suffix . '.js', array( 'jquery' ), USERSWP_VERSION, false );

        // localize
        $uwp_localize_data = uwp_get_localize_data();
        wp_localize_script(USERSWP_NAME, 'uwp_localize_data', $uwp_localize_data);

	    wp_register_script( "uwp_timepicker", USERSWP_PLUGIN_URL . 'assets/js/jquery.ui.timepicker.min.js', array( 'jquery', 'jquery-ui-datepicker', 'jquery-ui-core' ), USERSWP_VERSION, true );

	    $enable_timepicker_fields = false;
	    $enable_country_fields = false;

	    $register_fields = get_register_form_fields();
	    $account_fields = get_account_form_fields();
	    $fields = array_merge($register_fields,$account_fields);
	    if (!empty($fields)) {
		    foreach ($fields as $field) {
			    if ($field->field_type == 'time' || $field->field_type == 'datepicker') {
				    $enable_timepicker_fields = true;
			    }
			    if ($field->field_type_key == 'uwp_country' || $field->field_type_key == 'country') {
				    $enable_country_fields = true;
			    }
		    }
	    }

	    if($enable_timepicker_fields) {
		    wp_enqueue_style( 'jquery-ui' );
		    wp_enqueue_script( "uwp_timepicker" );
	    }

	    if($enable_country_fields) {
		    //@todo lets find a better solution for this and put it in AUI, maybe SVG files?
		    wp_enqueue_style( "uwp-country-select", USERSWP_PLUGIN_URL . 'assets/css/countryselect.css', array(), USERSWP_VERSION, 'all' );
		    wp_enqueue_script( "country-select", USERSWP_PLUGIN_URL . 'assets/js/countrySelect' . $suffix . '.js', array( 'jquery' ), USERSWP_VERSION, false );
		    $country_data = uwp_get_country_data();
		    wp_localize_script( 'country-select', 'uwp_country_data', $country_data );
	    }
        
    }

}