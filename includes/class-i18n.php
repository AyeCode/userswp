<?php
/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @author     GeoDirectory Team <info@wpgeodirectory.com>
 */
class UsersWP_i18n {
    
    /**
     * Load the plugin text domain for translation.
     *
     * @since       1.0.0
     * @package     userswp
     * @return      void
     */
    public function load_plugin_textdomain() {

        load_plugin_textdomain( 'userswp', false, basename( dirname (dirname( __FILE__ ) ) ) . '/languages' );

    }

}