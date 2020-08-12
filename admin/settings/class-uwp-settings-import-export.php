<?php
/**
 * UsersWP import export Settings
 *
 * @author      AyeCode
 * @category    Admin
 * @package     userswp/Admin
 * @version     1.0.24
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'UsersWP_Settings_Import_Export', false ) ) :

    /**
     * UsersWP_Settings_Email.
     */
    class UsersWP_Settings_Import_Export extends UsersWP_Settings_Page {

        public function __construct() {

            $this->id    = 'import-export';
            $this->label = __( 'Import/Export', 'userswp' );

            add_filter( 'uwp_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
            add_action( 'uwp_settings_' . $this->id, array( $this, 'output' ) );
            add_action( 'uwp_sections_' . $this->id, array( $this, 'output_toggle_advanced' ) );
            add_action( 'uwp_settings_save_' . $this->id, array( $this, 'save' ) );
            add_action( 'uwp_sections_' . $this->id, array( $this, 'output_sections' ) );

        }

        /**
         * Output the settings.
         */
        public function output() {
            global $current_section;

            $settings = $this->get_settings( $current_section );

            UsersWP_Admin_Settings::output_fields( $settings );
        }

        /**
         * Save settings.
         */
        public function save() {
            global $current_section;

            $settings = $this->get_settings( $current_section );
            UsersWP_Admin_Settings::save_fields( $settings );
        }

        /**
         * Get sections.
         *
         * @return array
         */
        public function get_sections() {

            $sections = array(
                ''          	=> __( 'Users', 'userswp' ),
                'settings' 	    => __( 'Settings', 'userswp' ),
            );

            return apply_filters( 'uwp_get_sections_' . $this->id, $sections );
        }

        public function get_settings( $current_section = '' ) {
            global $uwp_hide_save_button;

            if ( 'settings' == $current_section ) {

                /**
                 * Filter import export settings array.
                 *
                 * @package userswp
                 */
                $uwp_hide_save_button = true;
                $settings = apply_filters('uwp_settings_ie_main', array(

                    array(
                        'type' => 'import_export_settings',
                    ),

                    array('type' => 'sectionend', 'id' => 'ie_users_options'),

                ));

            } else {

                /**
                 * Filter import export users array.
                 *
                 * @package userswp
                 */
                $uwp_hide_save_button = true;
                $settings = apply_filters('uwp_settings_ie_main', array(

                    array(
                        'type' => 'import_export_users',
                    ),

                    array('type' => 'sectionend', 'id' => 'ie_settings_options'),

                ));

            }

            return apply_filters( 'uwp_get_settings_' . $this->id, $settings );
        }

    }

endif;


return new UsersWP_Settings_Import_Export();