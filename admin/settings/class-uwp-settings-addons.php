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

if ( ! class_exists( 'UsersWP_Settings_Addons', false ) ) :

    /**
     * UsersWP_Settings_Email.
     */
    class UsersWP_Settings_Addons extends UsersWP_Settings_Page {

        public function __construct() {

            $this->id    = 'uwp-addons';
            $this->label = __( 'Addons', 'userswp' );

            add_filter( 'uwp_settings_tabs_array', array( $this, 'add_settings_page' ), 99 );
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
        public function get_sections()
        {

            $sections = array(
                '' => __('Addons', 'userswp'),
            );

            return apply_filters('uwp_get_sections_' . $this->id, $sections);
        }

        public function get_settings($current_section = '')
        {
            $settings = array();

            if ('' == $current_section) {

                global $uwp_hide_save_button;
                $uwp_hide_save_button = true;

                $settings = array(
                    array(
                        'title' => __('UsersWP Addons', 'userswp'),
                        'type' => 'title',
                        'desc' => sprintf(__('Please select an addon to view its settings. Check our list of addons available for UsersWP %s here %s','userswp'), '<a href="https://userswp.io/downloads/category/addons/" target="_blank">', '</a>'),
                        'id' => 'addons_general_settings_options',
                        'desc_tip' => false,
                    ),
                    array('type' => 'sectionend', 'id' => 'addons_general_settings_options'),
                );

            }

            return apply_filters('uwp_get_settings_' . $this->id, $settings, $current_section);
        }

    }

endif;


return new UsersWP_Settings_Addons();