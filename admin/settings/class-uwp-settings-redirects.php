<?php
/**
 * UsersWP Redirects Settings
 *
 * @author      AyeCode
 * @category    Admin
 * @package     userswp/Admin
 * @version     1.2.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'UsersWP_Settings_Redirects', false ) ) :

/**
 * UsersWP_Settings_Redirects.
 */
class UsersWP_Settings_Redirects extends UsersWP_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {

		$this->id    = 'redirects';
		$this->label = __( 'Redirects', 'userswp' );

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
            '' 	    => __( 'Role based redirects', 'userswp' ),
        );

        return apply_filters( 'uwp_get_sections_' . $this->id, $sections );
    }

	/**
	 * Get redirects settings array.
	 *
	 * @return array
	 */
	public function get_settings( $current_section = '' ) {
		/**
		 * Filter pages settings array.
		 *
		 * @package userswp
		 */

		global $wp_roles;

		$pages = get_pages(); $desc = '';
		$pages_options = array(
			'-1' => __( 'Last User Page', 'userswp' ),
			'0' => __( 'Default Redirect', 'userswp'),
			'-2' => __( 'Custom Redirect', 'userswp'),
		);
		if ( $pages ) {
			foreach ( $pages as $page ) {
				$pages_options[ $page->ID ] = $page->post_title;
			}
		}

		if ( - 1 == uwp_get_option( 'login_redirect_to' ) ) {
			$desc = '<div class="bsui">' . aui()->alert( array(
					'type'    => 'error',
					'content' => __( 'Login redirect based on user role will not work if "Last User Page" is set in General->Login->Login Redirect Page.', 'userswp' )
				) ) . '</div>';
		}

		$settings_array = array(
			array(
				'title' => __( 'Role based redirect settings', 'userswp' ),
				'type'  => 'title',
				'desc' => $desc,
				'id'    => 'redirects_options',
			),
		);
		if(isset($wp_roles->roles) && !empty($wp_roles->roles)){
			foreach ($wp_roles->roles as $key => $value){
				$role_label = $value['name'];
				$settings_array[] = array(
					'title' => $role_label,
					'type'  => 'title',
					'id'    => 'role_name_'.$key,
				);
				$settings_array[] = array(
					'id' => 'login_redirect_to_'.$key,
					'name' => __( 'Login Redirect Page', 'userswp' ),
					'desc' => __( 'Set the page to redirect the user to after logging in. If default redirect has been set then WordPress default will be used.', 'userswp' ),
					'type' => 'select',
					'options' => $pages_options,
					'default'  => '-1',
					'desc_tip' => true,
				);

				$settings_array[] = array(
					'id' => 'login_redirect_custom_url_'.$key,
					'name' => __( 'Redirect Custom URL', 'userswp' ),
					'desc' => __( 'Set the custom url to redirect the user to after logging in.', 'userswp' ),
					'type' => 'text',
					'default'  => '',
					'desc_tip' => true,
					'class' => 'uwp-login-redirect-custom-url',
				);

				$settings_array[] = array(
					'id' => 'logout_redirect_to_'.$key,
					'name' => __( 'Logout Redirect Page', 'userswp' ),
					'desc' => __( 'Set the page to redirect the user to after logging out. If no page has been set WordPress default will be used.', 'userswp' ),
					'type' => 'single_select_page',
					'desc_tip' => true,
				);

				$settings_array[] = array(
					'id'   => 'hide_admin_bar_'.$key,
					'name' => __( 'Hide Adminbar', 'userswp' ),
					'desc' => __('Hide admin bar from frontend for this user role.','userswp' ),
					'type' => 'checkbox',
					'default'  => '0',
				);

				$settings_array[] = array( 'type' => 'sectionend', 'id' => 'role_name_'.$key );
			}
		}

		$settings_array[] = array( 'type' => 'sectionend', 'id' => 'redirects_options' );

		$settings = apply_filters( 'uwp_redirects_options', $settings_array);

		return apply_filters( 'uwp_get_settings_' . $this->id, $settings );
	}
}

endif;

return new UsersWP_Settings_Redirects();