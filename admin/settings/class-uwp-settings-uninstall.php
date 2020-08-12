<?php
/**
 * UsersWP Uninstall Settings
 *
 * @author      AyeCode
 * @category    Admin
 * @package     userswp/Admin
 * @version     1.0.24
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'UsersWP_Settings_Uninstall', false ) ) {

	/**
	 * UsersWP_Settings_Uninstall.
	 */
	class UsersWP_Settings_Uninstall extends UsersWP_Settings_Page {

		/**
		 * Constructor.
		 */
		public function __construct() {

			$this->id    = 'uninstall';
			$this->label = __( 'Uninstall', 'userswp' );

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
		public function get_sections() {

			$sections = array(
				'' => __( 'Uninstall Settings', 'userswp' ),
			);

			return apply_filters( 'uwp_get_sections_' . $this->id, $sections );
		}

		public function get_settings( $current_section = '' ) {

			/**
			 * Filter uninstall settings array.
			 *
			 * @package userswp
			 */
			$settings = apply_filters( 'uwp_uninstall_options', array(
				array(
					'title' => __( 'Uninstall Settings', 'userswp' ),
					'type'  => 'title',
					'desc'  => '<b style="color:#f00 ">' . __( 'NOTE: Add-ons should be deleted before core to ensure complete uninstall.', 'userswp' ) . '</b>',
					'id'    => 'uninstall_options',
				),

				array(
					'name' => __( 'Remove Data on Uninstall?', 'userswp' ),
					'desc' => __( 'Check this box if you would like UsersWP to completely remove all of its data when the plugin is deleted.', 'userswp' ),
					'id'   => 'uninstall_erase_data',
					'type' => 'checkbox',
				),

			) );

			$settings = apply_filters( 'uwp_get_settings_' . $this->id, $settings );

			$settings[] = array( 'type' => 'sectionend', 'id' => 'uninstall_options' );

			return $settings;
		}

	}

}

return new UsersWP_Settings_Uninstall();
