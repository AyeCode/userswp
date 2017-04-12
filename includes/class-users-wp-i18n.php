<?php
/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       http://wpgeodirectory.com
 * @since      1.0.0
 *
 * @package    Users_WP
 * @subpackage Users_WP/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Users_WP
 * @subpackage Users_WP/includes
 * @author     GeoDirectory Team <info@wpgeodirectory.com>
 */
class Users_WP_i18n {


    /**
     * Load the plugin text domain for translation.
     *
     * @since    1.0.0
     */
    public function load_plugin_textdomain() {

        // Set filter for plugin's languages directory
        $lang_dir = dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/';
        $lang_dir = apply_filters( 'userswp_languages_directory', $lang_dir );

        // Traditional WordPress plugin locale filter
        $locale        = apply_filters( 'plugin_locale',  get_locale(), 'userswp' );
        $mofile        = sprintf( '%1$s-%2$s.mo', 'userswp', $locale );

        // Setup paths to current locale file
        $mofile_local  = $lang_dir . $mofile;
        $mofile_global = WP_LANG_DIR . '/userswp/' . $mofile;

        if ( file_exists( $mofile_global ) ) {
            // Look in global /wp-content/languages/userswp/ folder
            load_textdomain( 'userswp', $mofile_global );
        } elseif ( file_exists( $mofile_local ) ) {
            // Look in local /wp-content/plugins/userswp/languages/ folder
            load_textdomain( 'userswp', $mofile_local );
        } else {
            // Load the default language files
            load_plugin_textdomain( 'userswp', false, $lang_dir );
        }
        

    }



}