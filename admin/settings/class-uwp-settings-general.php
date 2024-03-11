<?php
/**
 * UsersWP General Settings
 *
 * @author      AyeCode
 * @category    Admin
 * @package     userswp/Admin
 * @version     1.0.24
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'UsersWP_Settings_General', false ) ) :

/**
 * UsersWP_Settings_General.
 */
class UsersWP_Settings_General extends UsersWP_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {

		$this->id    = 'general';
		$this->label = __( 'General', 'userswp' );

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
            '' 	    => __( 'Pages', 'userswp' ),
            'register' 	    => __( 'Register', 'userswp' ),
            'login' 	    => __( 'Login', 'userswp' ),
            'change-password' => __( 'Change Password', 'userswp' ),
            'profile' => __( 'Profile', 'userswp' ),
            'users' => __( 'Users', 'userswp' ),
            'account' => __( 'Account', 'userswp' ),
            'authorbox' => __( 'Author box', 'userswp' ),
            'developer' => __( 'Developer', 'userswp' ),
        );

        return apply_filters( 'uwp_get_sections_' . $this->id, $sections );
    }

	/**
	 * Get general settings array.
	 *
	 * @return array
	 */
	public function get_settings( $current_section = '' ) {

		if ( 'register' == $current_section ) {
            /**
             * Filter registration settings array.
             *
             * @package userswp
             */

            $settings = apply_filters( 'uwp_register_options', array(
                array(
                    'title' => __( 'Register Settings', 'userswp' ),
                    'type'  => 'title',
                    'id'    => 'register_options',
                ),
	            array(
		            'id'   => 'register_modal',
		            'name' => __( 'Register Lightbox', 'userswp' ),
		            'desc' => __( 'When enabled some register links will open in a lightbox instead of changing page.','userswp'),
		            'type' => 'checkbox',
		            'default'  => '1',
	            ),
	            array(
		            'id'   => 'register_modal_form',
		            'name' => __( 'Lightbox Form(s)', 'userswp' ),
		            'desc' => __( 'Choose form(s) to display in lighbox. You can select multiple forms.','userswp'),
		            'type' => 'multiselect',
		            'options'  => uwp_get_register_forms_dropdown_options(),
		            'default'  => '1',
		            'desc_tip' => true,
	            ),
                array(
                    'id'   => 'wp_register_redirect',
                    'name' => __( 'Redirect Admin Default Register Page', 'userswp' ),
                    'desc' => __( 'When enabled /wp-login.php?action=register page will be redirected to UsersWP register page.', 'userswp' ),
                    'type' => 'checkbox',
                    'default'  => '1',
                ),
	            array(
		            'id'   => 'register_min_password_strength',
		            'name' => __( 'Minimum password strength', 'userswp' ),
		            'desc' => __( 'Forces users to use strong password on registration if set.', 'userswp' ),
		            'type' => 'select',
		            'options' => array(''=>'None','3'=>'Medium','4'=>'Strong'),
		            'default'  => '',
		            'desc_tip' => true,
	            ),
	            array(
		            'id' => 'register_uwp_strong_pass_msg',
		            'name' => __( 'Error message for entering strong password', 'userswp' ),
		            'desc' => __( 'Enter the message to show when user enters weak password in registration form.', 'userswp' ),
		            'type' => 'text',
		            'default'  => '',
		            'placeholder'  => __( 'Please enter valid strong password.', 'userswp' ),
		            'desc_tip' => true,
		            'advanced' => true,
	            ),
	            array(
		            'id'   => 'register_username_length',
		            'name' => __( 'Username Minimum Length', 'userswp' ),
		            'desc' => __( 'Minimum username character limit required for registration.', 'userswp' ),
		            'desc_tip' => true,
		            'type' => 'number',
		            'default'  => '4',
		            'advanced' => true,
	            ),
	            array(
		            'id'   => 'register_password_min_length',
		            'name' => __( 'Minimum Password Limit', 'userswp' ),
		            'desc' => __( 'Minimum Password limit user should enter in the form.', 'userswp' ),
		            'desc_tip' => true,
		            'type' => 'number',
		            'default'  => '8',
		            'advanced' => true,
	            ),
	            array(
		            'id'   => 'register_password_max_length',
		            'name' => __( 'Maximum Password Limit', 'userswp' ),
		            'desc' => __( 'Maximum Password limit user should enter in the form.', 'userswp' ),
		            'desc_tip' => true,
		            'type' => 'number',
		            'default'  => '15',
		            'advanced' => true,
	            ),

                array( 'type' => 'sectionend', 'id' => 'register_options' ),
            ));
        } else if ( 'login' == $current_section ) {
            /**
             * Filter login settings array.
             *
             * @package userswp
             */
            $pages = get_pages();
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

            $settings = apply_filters( 'uwp_login_options', array(
                array(
                    'title' => __( 'Login Settings', 'userswp' ),
                    'type'  => 'title',
                    'id'    => 'login_options',
                ),
	            array(
		            'id'   => 'login_modal',
		            'name' => __( 'Login Lightbox', 'userswp' ),
		            'desc' => __( 'When enabled some login links will open in a lightbox instead of changing page. The page will be reloaded after successful login instead of a redirect.','userswp'),
		            'type' => 'checkbox',
		            'default'  => '1',
	            ),
	            array(
		            'id'   => 'login_modal_enable_redirect',
		            'name' => __( 'Enable Login Lightbox Redirect', 'userswp' ),
		            'desc' => __( 'When enabled the login in lightbox will follow login redirect settings.','userswp'),
		            'type' => 'checkbox',
		            'default'  => '0',
	            ),
                array(
                    'id' => 'login_redirect_to',
                    'name' => __( 'Login Redirect Page', 'userswp' ),
                    'desc' => __( 'Set the page to redirect the user to after logging in. If default redirect has been set then WordPress default will be used.', 'userswp' ),
                    'type' => 'select',
                    'options' => $pages_options,
                    'default'  => '-1',
                    'desc_tip' => true,
                ),
	            array(
		            'id' => 'login_redirect_custom_url',
		            'name' => __( 'Redirect Custom URL', 'userswp' ),
		            'desc' => __( 'Set the custom url to redirect the user to after logging in.', 'userswp' ),
		            'type' => 'text',
		            'default'  => '',
		            'desc_tip' => true,
		            'class' => 'uwp-login-redirect-custom-url',
	            ),
	            array(
		            'id' => 'register_link_title',
		            'name' => __( 'Register Link Title', 'userswp' ),
		            'desc' => __( 'Enter the register link title.', 'userswp' ),
		            'type' => 'text',
		            'default'  => '',
		            'placeholder'  => __( 'Create account', 'userswp' ),
		            'desc_tip' => true,
		            'advanced' => true,
	            ),
	            array(
		            'id' => 'forgot_link_title',
		            'name' => __( 'Forgot Password Link Title', 'userswp' ),
		            'desc' => __( 'Enter the forgot password title.', 'userswp' ),
		            'type' => 'text',
		            'default'  => '',
		            'placeholder'  => __( 'Forgot password?', 'userswp' ),
		            'desc_tip' => true,
		            'advanced' => true,
	            ),
                array(
                    'id'   => 'block_wp_login',
                    'name' => __( 'Redirect wp-login.php?', 'userswp' ),
                    'desc' => __('When enabled /wp-login.php page will be redirected to UsersWP login page.','userswp' ),
                    'type' => 'checkbox',
                    'default'  => '0',
                ),
	            array(
		            'name'       => __( 'Restrict wp-admin', 'userswp' ),
		            'desc'       => __( 'The user roles that should be restricted from accessing the wp-admin area.', 'userswp' ),
		            'id'         => 'admin_blocked_roles',
		            'default'    => array('subscriber'),
		            'type'       => 'multiselect',
		            'class'      => 'uwp-select',
		            'options'    => uwp_get_user_roles(array('administrator')),
		            'desc_tip'   => true,
	            ),
                array(
                    'id' => 'logout_redirect_to',
                    'name' => __( 'Logout Redirect Page', 'userswp' ),
                    'desc' => __( 'Set the page to redirect the user to after logging out. If no page has been set WordPress default will be used.', 'userswp' ),
                    'type' => 'single_select_page',
                    'desc_tip' => true,
                ),
	            array(
		            'id'   => 'disable_wp_2fa',
		            'name' => __( 'Disable WP-2FA', 'userswp' ),
		            'desc' => __('This will disable integration with WP 2FA - Two-factor authentication for WordPress plugin.','userswp' ),
		            'type' => 'checkbox',
		            'default'  => '0',
	            ),
                array( 'type' => 'sectionend', 'id' => 'login_options' ),
            ));
        } else if ( 'change-password' == $current_section ) {
            /**
             * Filter change password settings array.
             *
             * @package userswp
             */
            $settings = apply_filters( 'uwp_change_password_options', array(
                array(
                    'title' => __( 'Change Password Settings', 'userswp' ),
                    'type'  => 'title',
                    'id'    => 'change_password_options',
                ),
	            array(
		            'id'   => 'forgot_modal',
		            'name' => __( 'Forgot password lightbox', 'userswp' ),
		            'desc' => __( 'When enabled some forgot password links will open in a lightbox instead of changing page.','userswp'),
		            'type' => 'checkbox',
		            'default'  => '1',
	            ),
	            array(
		            'id' => 'login_link_title',
		            'name' => __( 'Login Link Title', 'userswp' ),
		            'desc' => __( 'Enter the login link title.', 'userswp' ),
		            'type' => 'text',
		            'default'  => '',
		            'placeholder'  => __( 'Login', 'userswp' ),
		            'desc_tip' => true,
		            'advanced' => true,
	            ),
	            array(
		            'id' => 'profile_link_title',
		            'name' => __( 'Profile Link Title', 'userswp' ),
		            'desc' => __( 'Enter the profile link title.', 'userswp' ),
		            'type' => 'text',
		            'default'  => '',
		            'placeholder'  => __( 'Profile', 'userswp' ),
		            'desc_tip' => true,
		            'advanced' => true,
	            ),
	            array(
		            'id' => 'account_link_title',
		            'name' => __( 'Account Link Title', 'userswp' ),
		            'desc' => __( 'Enter the account link title.', 'userswp' ),
		            'type' => 'text',
		            'default'  => '',
		            'placeholder'  => __( 'Account', 'userswp' ),
		            'desc_tip' => true,
		            'advanced' => true,
	            ),
                array(
                    'id'   => 'change_enable_old_password',
                    'name' => __( 'Enabled Old Password?', 'userswp' ),
                    'desc' => __( 'Ask user to enter their old password before changing the password to add an extra layer of security.', 'userswp' ),
                    'type' => 'checkbox',
                    'default'  => '1',
                ),
                array(
                    'id'   => 'change_disable_password_nag',
                    'name' => __( 'Disable system generated password notice.', 'userswp' ),
                    'desc' => __( 'This option will disable system generated password notice if user has not changed default password after registration.', 'userswp' ),
                    'type' => 'checkbox',
                    'default'  => '0',
                ),

                array( 'type' => 'sectionend', 'id' => 'change_password_options' ),
            ));
        } else if ( 'profile' == $current_section ) {
            /**
             * Filter profile settings array.
             *
             * @package userswp
             */
            $file_obj = new UsersWP_Files();

            $settings = apply_filters( 'uwp_profile_options', array(
                array(
                    'title' => __( 'Profile Settings', 'userswp' ),
                    'type'  => 'title',
                    'id'    => 'profile_options',
                ),
                array(
                    'id' => 'profile_default_banner',
                    'name' => __( 'Default banner image', 'userswp' ),
                    'desc' => __( 'Recommended image size: 1000x300', 'userswp'),
                    'type' => 'image',
                    'default' => '',
                    'desc_tip' => true,
                ),
                array(
                    'id' => 'profile_default_profile',
                    'name' => __( 'Default profile image', 'userswp' ),
                    'desc' => __( 'Recommended image size: 150x150', 'userswp'),
                    'type' => 'image',
                    'default' => '',
                    'desc_tip' => true,
                ),
                array(
                    'id'   => 'enable_profile_header',
                    'name' => __( 'Display header in profile', 'userswp' ),
                    'type' => 'checkbox',
                    'default'  => '1',
                    'advanced'  => true,
                ),
                array(
                    'id'   => 'enable_profile_body',
                    'name' => __( 'Display body in profile', 'userswp' ),
                    'type' => 'checkbox',
                    'default'  => '1',
                    'advanced'  => true,
                ),
                array(
                    'id'   => 'profile_avatar_size',
                    'name' => __( 'Profile avatar max file size', 'userswp' ),
                    'desc' => sprintf(__( 'Enter Profile Avatar max file size in Kb. e.g. 512 for 512 kb, 1024 for 1 Mb, 2048 for 2 Mb etc. If empty WordPress default (%s) will be used.', 'userswp' ), '<b>'.$file_obj->uwp_formatSizeinKb($file_obj->uwp_get_max_upload_size()).'</b>'),
                    'type' => 'number',
                    'default'  => '',
                    'desc_tip' => true,
                    'advanced'  => true,
                ),
                array(
                    'id'   => 'profile_banner_size',
                    'name' => __( 'Profile banner max file size', 'userswp' ),
                    'desc' => sprintf(__( 'Enter Profile Banner max file size in Kb. e.g. 512 for 512 kb, 1024 for 1 Mb, 2048 for 2 Mb etc. If empty WordPress default (%s) will be used.', 'userswp' ), '<b>'.$file_obj->uwp_formatSizeinKb($file_obj->uwp_get_max_upload_size()).'</b>'),
                    'type' => 'number',
                    'default'  => '',
                    'desc_tip' => true,
                    'advanced'  => true,
                ),
	            array(
		            'id'   => 'profile_avatar_width',
		            'name' => __( 'Profile avatar width', 'userswp' ),
		            'type' => 'number',
		            'default'  => 150,
		            'advanced'  => true,
	            ),
	            array(
		            'id'   => 'profile_avatar_height',
		            'name' => __( 'Profile avatar height', 'userswp' ),
		            'type' => 'number',
		            'default'  => 150,
		            'advanced'  => true,
	            ),
                array(
                    'id'   => 'profile_banner_width',
                    'name' => __( 'Profile banner width', 'userswp' ),
                    'type' => 'number',
                    'default'  => 1000,
                    'advanced'  => true,
                ),
	            array(
		            'id'   => 'profile_banner_height',
		            'name' => __( 'Profile banner height', 'userswp' ),
		            'type' => 'number',
		            'default'  => 300,
		            'advanced'  => true,
	            ),
                array(
                    'id' => 'profile_no_of_items',
                    'name' => __( 'Number of items', 'userswp' ),
                    'type' => 'number',
                    'default' => 10,
                    'desc' 	=> __( 'Enter number of items to display in profile tabs.', 'userswp' ),
                    'desc_tip' => true,
                    'advanced'  => true,
                ),
                array(
                    'id'   => 'uwp_disable_author_link',
                    'name' => __( 'Disable author redirect to UsersWP profile page.', 'userswp' ),
                    'type' => 'checkbox',
                    'default'  => '0',
                    'advanced'  => true,
                ),
	            array(
		            'id' => 'user_post_counts_cpts',
		            'name' => __( 'User Counts CPTs', 'userswp' ),
		            'desc' => __( 'Choose post types to display user counts in profile page for all users.', 'userswp' ),
		            'type'  => 'multiselect',
		            'sortable' => true,
		            'options' =>  uwp_get_posttypes(),
		            'desc_tip' => true,
	            ),
	            array(
		            'id' => 'login_user_post_counts_cpts',
		            'name' => __( 'User Counts CPTs for profile owner', 'userswp' ),
		            'desc' => __( 'Choose post types to display user counts in profile page for profile owners.', 'userswp' ),
		            'type'  => 'multiselect',
		            'sortable' => true,
		            'options' =>  uwp_get_posttypes(),
		            'desc_tip' => true,
	            ),
                array( 'type' => 'sectionend', 'id' => 'profile_options' ),
            ));
        } else if ( 'users' == $current_section ) {
            /**
             * Filter general settings array.
             *
             * @package userswp
             */
            $settings = apply_filters( 'uwp_users_options', array(
                array(
                    'title' => __( 'Users Settings', 'userswp' ),
                    'type'  => 'title',
                    'id'    => 'users_options',
                ),

                array(
                    'id' => 'users_default_layout',
                    'name' => __( 'Users default layout', 'userswp' ),
                    'desc' => __( 'Choose the default layout for users listing page.', 'userswp' ),
                    'type'    => 'select',
                    'options' =>   $this->uwp_available_users_layout(),
                    'desc_tip' => true,
                ),
	            array(
		            'id' => 'users_no_of_items',
		            'name' => __( 'Number of items', 'userswp' ),
		            'type' => 'number',
		            'default' => 10,
		            'desc' 	=> __( 'Enter number of items to display in users list page.', 'userswp' ),
		            'desc_tip' => true,
		            'advanced'  => true,
	            ),
                array(
                    'id'   => 'users_excluded_from_list',
                    'name' => __( 'Users to exclude', 'uwp-messaging' ),
                    'desc' => __( 'Select users to exclude from the users listing.', 'userswp' ),
                    'desc_tip' => true,
                    'type' => 'multiselect',
                    'size' => 'regular',
                    'options' => $this->get_users(),
                ),

                array( 'type' => 'sectionend', 'id' => 'users_options' ),
            ));
		}else if ( 'account' == $current_section ) {
			/**
			 * Filter general settings array.
			 *
			 * @package userswp
			 */
			$settings = apply_filters( 'uwp_users_options', array(
				array(
					'title' => __( 'User Account Settings', 'userswp' ),
					'type'  => 'title',
					'id'    => 'accounts_options',
				),

				array(
					'id'   => 'disable_account_delete',
					'name' => __( 'Disable Account Delete', 'userswp' ),
					'desc' => __( 'Don\'t allow users to delete their own account. Default enabled.' , 'userswp' ),
					'type' => 'checkbox',
					'default'  => 0,
				),

				array( 'type' => 'sectionend', 'id' => 'accounts_options' ),
			));
		}else if ( 'authorbox' == $current_section ) {
            /**
             * Filter general settings array.
             *
             * @package userswp
             */
            $settings = apply_filters( 'uwp_authorbox_options', array(
                array(
                    'title' => __( 'Author Box Settings', 'userswp' ),
                    'type'  => 'title',
                    'id'    => 'authorbox_options',
                ),
                array(
                    'id'   => 'author_box_enable_disable',
                    'name' => __( 'Enable Author Box', 'userswp' ),
                    'desc' => __( 'Displays author box based on settings', 'userswp' ),
                    'type' => 'checkbox',
                    'default'  => '1',
                ),
                array(
                    'id' => 'author_box_display_content',
                    'name' => __('Where to display?', 'userswp'),
                    'desc' => __('Displays the author box above or below post content.', 'userswp'),
                    'type' => 'select',
                    'options' => array(
                        'above_content' =>  __('Above content', 'userswp'),
                        'below_content' =>  __('Below content', 'userswp'),
                    ),
                    'desc_tip' => true,
                    'default' => 'below_content',
                ),
                array(
                    'id' => 'author_box_display_post_types',
                    'name' => __( 'Post types', 'userswp' ),
                    'desc' => __( 'Choose post types to display author box', 'userswp' ),
                    'type'  => 'multiselect',
                    'sortable' => true,
                    'options' =>  uwp_get_posttypes(),
                    'desc_tip' => true,
                    'default' => 'post',
                ),
	            array(
		            'id'   => 'author_box_bio_limit',
		            'name' => __( 'Bio Length', 'userswp' ),
		            'desc' => __( 'Author bio word limit in Author box.', 'userswp' ),
		            'desc_tip' => true,
		            'type' => 'number',
		            'default'  => '200',
		            'advanced' => true,
	            ),
                array(
                    'name' => __('Author box Content', 'userswp'),
                    'desc' => __('The author box body, this can be text or HTML.', 'userswp'),
                    'id' => 'author_box_content',
                    'type' => 'textarea',
                    'class' => 'code uwp-authorbox-body',
                    'desc_tip' => true,
                    'placeholder' => UsersWP_Defaults::author_box_content(),
                    'custom_desc' => __('Available template tags:', 'userswp') . ' ' . uwp_authbox_tags(),
	                'advanced' => true,
                ),
	            array(
		            'name' => __('Author box Content (bootstrap)', 'userswp'),
		            'desc' => __('The author box body, this can be text or HTML.', 'userswp'),
		            'id' => 'author_box_content_bootstrap',
		            'type' => 'textarea',
		            'class' => 'code uwp-authorbox-body',
		            'desc_tip' => true,
		            'placeholder' => UsersWP_Defaults::author_box_content_bootstrap(),
		            'custom_desc' => __('Available template tags:', 'userswp') . ' ' . uwp_authbox_tags(),
		            'advanced' => true,
	            ),

                array( 'type' => 'sectionend', 'id' => 'authorbox_options' ),
            ));
        }else if ( 'developer' == $current_section ) {
            /**
             * Filter general settings array.
             *
             * @package userswp
             */
            $settings = apply_filters( 'uwp_developer_options', array(
                array(
                    'title' => __( 'Developer Settings', 'userswp' ),
                    'type'  => 'title',
                    'id'    => 'developer_options',
                ),

                array(
                    'id'   => 'disable_avatar_override',
                    'name' => __( 'Disable Avatar Override?', 'userswp' ),
                    'desc' => __( 'By default UsersWP will change the avatar to profile image. You can disable this using this option.', 'userswp' ),
                    'type' => 'checkbox',
                ),

	            array(
		            'name'     => __( 'Advanced settings', 'userswp' ),
		            'desc'     => __( 'Disable advanced toggle, show advanced settings at all times (not recommended).', 'userswp' ),
		            'id'       => 'admin_disable_advanced',
		            'type'     => 'checkbox',
	            ),

	            array(
		            'id'   => 'enable_uwp_error_log',
		            'name' => __( 'Debugging', 'userswp' ),
		            'desc' => __( 'Show debugging info in the error logs.', 'userswp' ),
		            'type' => 'checkbox',
	            ),

	            // @todo to be move to own design section
	            array(
		            'id' => 'design_style',
		            'name' => __('Default Design Style', 'userswp'),
		            'desc' => __('The default design style to use.', 'userswp'),
		            'type' => 'select',
		            'options' => array(
			            'bootstrap' =>  __('Bootstrap', 'userswp'),
			            '' =>  __('Legacy (non-bootstrap)', 'userswp'),
		            ),
		            'desc_tip' => true,
		            'default' => 'bootstrap',
	            ),

                array( 'type' => 'sectionend', 'id' => 'developer_options' ),
            ));
        } else {
			/**
			 * Filter pages settings array.
			 *
			 * @package userswp
			 */
			$settings = apply_filters( 'uwp_page_options', array(
				array(
					'title' => __( 'Page Settings', 'userswp' ),
					'type'  => 'title',
					'desc'  => 'These are essential pages used by UWP, you can set the pages here and edit the title/slug of the page via WP page settings.',
					'id'    => 'page_options',
					'desc_tip' => true,
				),
				array(
					'name'     => __( 'User Profile Page', 'userswp' ),
					'desc'     => __( 'This is the front end user\'s profile page. This page automatically overrides the default WordPress author page.', 'userswp' ),
					'id'       => 'profile_page',
					'type'     => 'single_select_page',
					'desc_tip' => true,
				),
				array(
					'name'     => __( 'Register Page', 'userswp' ),
					'desc'     => __( 'This is the front end register page. This is where users create their account.', 'userswp' ),
					'id'       => 'register_page',
					'type'     => 'single_select_page',
					'desc_tip' => true,
				),
				array(
					'name'     => __( 'Login Page', 'userswp' ),
					'desc'     => __( 'This is the front end login page. This is where users will login after creating their account.', 'userswp' ),
					'id'       => 'login_page',
					'type'     => 'single_select_page',
					'desc_tip' => true,
				),
				array(
					'name'     => __( 'Account Page', 'userswp' ),
					'desc'     => __( 'This is the front end account page. This is where users can edit their account.', 'userswp' ),
					'id'       => 'account_page',
					'type'     => 'single_select_page',
					'desc_tip' => true,
				),
				array(
					'name'     => __( 'Change Password Page', 'userswp' ),
					'desc'     => __( 'This is the front end Change Password page.', 'userswp' ),
					'id'       => 'change_page',
					'type'     => 'single_select_page',
					'desc_tip' => true,
				),
				array(
					'name'     => __( 'Forgot Password Page', 'userswp' ),
					'desc'     => __( 'This is the front end Forgot Password page. This is the page where users are sent to reset their password when they lose it.', 'userswp' ),
					'id'       => 'forgot_page',
					'type'     => 'single_select_page',
					'desc_tip' => true,
				),
				array(
					'name'     => __( 'Reset Password Page', 'userswp' ),
					'desc'     => __( 'This is the front end Reset Password page. This is the page where users can reset their password when they lose it.', 'userswp' ),
					'id'       => 'reset_page',
					'type'     => 'single_select_page',
					'desc_tip' => true,
				),
				array(
					'name'     => __( 'Users List Page', 'userswp' ),
					'desc'     => __( 'This is the front end Users List page. This is the page where all registered users of the websites are listed.', 'userswp' ),
					'id'       => 'users_page',
					'type'     => 'single_select_page',
					'desc_tip' => true,
				),
				array(
					'name'     => __( 'Users List Item Page', 'userswp' ),
					'desc'     => __( 'This is the page/template for displaying user item in users list page. You can change the template as you want which will apply to each user item in users list page.', 'userswp' ),
					'id'       => 'user_list_item_page',
					'type'     => 'single_select_page',
					'desc_tip' => true,
				),
				array( 'type' => 'sectionend', 'id' => 'page_options' ),
			));
		}


		return apply_filters( 'uwp_get_settings_' . $this->id, $settings );
	}

	/**
	 * Output a color picker input box.
	 *
	 * @param mixed $name
	 * @param string $id
	 * @param mixed $value
	 * @param string $desc (default: '')
	 */
	public function color_picker( $name, $id, $value, $desc = '' ) {
		echo '<div class="color_box">' . uwp_help_tip( $desc ) . '<input name="' . esc_attr( $id ) . '" id="' . esc_attr( $id ) . '" type="text" value="' . esc_attr( $value ) . '" class="colorpick" /> <div id="colorPickerDiv_' . esc_attr( $id ) . '" class="colorpickdiv"></div></div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

    public function uwp_available_users_layout() {
        $tabs_arr = array(
            'list' => __( 'Grid View - 1 Column (List View)', 'userswp' ),
            '2col' => __( 'Grid View - 2 Column', 'userswp' ),
            '3col' => __( 'Grid View - 3 Column', 'userswp' ),
            '4col' => __( 'Grid View - 4 Column', 'userswp' ),
            '5col' => __( 'Grid View - 5 Column', 'userswp' ),
        );

        $tabs_arr = apply_filters('uwp_available_users_layout', $tabs_arr);

        return $tabs_arr;
    }

	/**
	 * Get users lists.
	 *
	 * @return array $users
	 */
	public function get_users() {
		$get_users = get_users();

		$users = array();
		if(!empty($get_users) ) {
			foreach ($get_users as $key => $user) {
				$users[$user->ID] = esc_attr( $user->display_name );
			}
		}

		return $users;
	}
}

endif;

return new UsersWP_Settings_General();