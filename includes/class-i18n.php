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
class Users_WP_i18n {

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Users_WP_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    public function __construct() {
        add_action( 'init', array($this, 'load_plugin_textdomain'));
    }
    
    
    /**
     * Load the plugin text domain for translation.
     *
     * @since       1.0.0
     * @package     UsersWP
     * @return      void
     */
    public function load_plugin_textdomain() {

        load_plugin_textdomain( 'userswp', false, basename( dirname (dirname( __FILE__ ) ) ) . '/languages' );

    }

}