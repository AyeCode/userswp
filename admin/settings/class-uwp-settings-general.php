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
            ''          	=> __( 'General', 'userswp' ),
            'pages' 	    => __( 'Pages', 'userswp' ),
            'register' 	    => __( 'Register', 'userswp' ),
            'login' 	    => __( 'Login', 'userswp' ),
            'change-password' => __( 'Change Password', 'userswp' ),
            'profile' => __( 'Profile', 'userswp' ),
            'users' => __( 'Users', 'userswp' ),
            'authorbox' => __( 'Author box', 'userswp' ),
        );

        return apply_filters( 'uwp_get_sections_' . $this->id, $sections );
    }

	/**
	 * Get general settings array.
	 *
	 * @return array
	 */
	public function get_settings( $current_section = '' ) {

		if ( 'pages' == $current_section ) {
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
					'class'      => 'uwp-select',
					'desc_tip' => true,
				),
                array(
                    'name'     => __( 'Register Page', 'userswp' ),
                    'desc'     => __( 'This is the front end register page. This is where users create their account.', 'userswp' ),
                    'id'       => 'register_page',
                    'type'     => 'single_select_page',
                    'class'      => 'uwp-select',
                    'desc_tip' => true,
                ),
                array(
                    'name'     => __( 'Login Page', 'userswp' ),
                    'desc'     => __( 'This is the front end login page. This is where users will login after creating their account.', 'userswp' ),
                    'id'       => 'login_page',
                    'type'     => 'single_select_page',
                    'class'      => 'uwp-select',
                    'desc_tip' => true,
                ),
                array(
                    'name'     => __( 'Account Page', 'userswp' ),
                    'desc'     => __( 'This is the front end account page. This is where users can edit their account.', 'userswp' ),
                    'id'       => 'account_page',
                    'type'     => 'single_select_page',
                    'class'      => 'uwp-select',
                    'desc_tip' => true,
                ),
                array(
                    'name'     => __( 'Change Password Page', 'userswp' ),
                    'desc'     => __( 'This is the front end Change Password page.', 'userswp' ),
                    'id'       => 'change_page',
                    'type'     => 'single_select_page',
                    'class'      => 'uwp-select',
                    'desc_tip' => true,
                ),
                array(
                    'name'     => __( 'Forgot Password Page', 'userswp' ),
                    'desc'     => __( 'This is the front end Forgot Password page. This is the page where users are sent to reset their password when they lose it.', 'userswp' ),
                    'id'       => 'forgot_page',
                    'type'     => 'single_select_page',
                    'class'      => 'uwp-select',
                    'desc_tip' => true,
                ),
                array(
                    'name'     => __( 'Reset Password Page', 'userswp' ),
                    'desc'     => __( 'This is the front end Reset Password page. This is the page where users can reset their password when they lose it.', 'userswp' ),
                    'id'       => 'reset_page',
                    'type'     => 'single_select_page',
                    'class'      => 'uwp-select',
                    'desc_tip' => true,
                ),
                array(
                    'name'     => __( 'Users List Page', 'userswp' ),
                    'desc'     => __( 'This is the front end Users List page. This is the page where all registered users of the websites are listed.', 'userswp' ),
                    'id'       => 'users_page',
                    'type'     => 'single_select_page',
                    'class'      => 'uwp-select',
                    'desc_tip' => true,
                ),
				array( 'type' => 'sectionend', 'id' => 'page_options' ),
			));
        } else if ( 'register' == $current_section ) {
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
                    'id' => 'uwp_registration_action',
                    'name' => __('Registration Action', 'userswp'),
                    'desc' => __('Select how registration should be handled.', 'userswp'),
                    'type' => 'select',
                    'options' => uwp_registration_status_options(),
                    'class' => 'uwp-select',
                    'desc_tip' => true,
                ),
                array(
                    'id' => 'register_redirect_to',
                    'name' => __( 'Register Redirect Page', 'userswp' ),
                    'desc' => __( 'Set the page to redirect the user to after signing up. If no page has been set WordPress default will be used.', 'userswp' ),
                    'type' => 'single_select_page',
                    'class' => 'uwp-select',
                    'desc_tip' => true,
                ),
                array(
                    'id' => 'register_terms_page',
                    'name' => __( 'Register TOS Page', 'userswp' ),
                    'desc' => __( 'Terms of Service page. When set "Accept terms and Conditions" checkbox will appear on the register form.', 'userswp' ),
                    'type' => 'single_select_page',
                    'class' => 'uwp-select',
                    'desc_tip' => true,
                ),
                array(
                    'id'   => 'wp_register_redirect',
                    'name' => __( 'Redirect admin default register page', 'userswp' ),
                    'desc' => __( 'When enabled /wp-login.php?action=register page will be redirected to UsersWP register page.', 'userswp' ),
                    'type' => 'checkbox',
                    'default'  => '1',
                ),
                array(
                    'id'   => 'register_admin_notify',
                    'name' => __( 'Enable admin email notification?', 'userswp' ),
                    'desc' => __( 'When enabled an email will be sent to the admin for every user registration.', 'userswp' ),
                    'type' => 'checkbox',
                    'default'  => '0',
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
            $pages_options = array( '-1' => __( 'Last User Page', 'userswp' ) );
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
                    'id' => 'login_redirect_to',
                    'name' => __( 'Login Redirect Page', 'userswp' ),
                    'desc' => __( 'Set the page to redirect the user to after logging in. If no page has been set WordPress default will be used.', 'userswp' ),
                    'type' => 'select',
                    'options' => $pages_options,
                    'class' => 'uwp-select',
                    'default'  => '-1',
                    'desc_tip' => true,
                ),
                array(
                    'id'   => 'block_wp_login',
                    'name' => __( 'Redirect wp-login.php?', 'userswp' ),
                    'desc' => 'When enabled /wp-login.php page will be redirected to UsersWP login page.',
                    'type' => 'checkbox',
                    'default'  => '0',
                    'class' => 'uwp_label_inline',
                ),
                array(
                    'id' => 'logout_redirect_to',
                    'name' => __( 'Logout Redirect Page', 'userswp' ),
                    'desc' => __( 'Set the page to redirect the user to after logging out. If no page has been set WordPress default will be used.', 'userswp' ),
                    'type' => 'single_select_page',
                    'class' => 'uwp-select',
                    'desc_tip' => true,
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
                    'id' => 'enable_profile_tabs',
                    'name' => __( 'Choose the tabs to display in Profile', 'userswp' ),
                    'desc' => __( 'Choose the tabs to display in UsersWP Profile', 'userswp' ),
                    'type'  => 'multiselect',
                    'sortable' => true,
                    'class'   => 'uwp_select2 uwp-select',
                    'options' =>   $this->uwp_available_tab_items(),
                    'desc_tip' => true,
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
                    'desc' => __( 'Recommended image size: 150x150', 'userwp'),
                    'type' => 'image',
                    'default' => '',
                    'desc_tip' => true,
                ),
                array(
                    'id'   => 'enable_profile_header',
                    'name' => __( 'Display Header in Profile', 'userswp' ),
                    'type' => 'checkbox',
                    'default'  => '1',
                    'advanced'  => true,
                ),
                array(
                    'id'   => 'enable_profile_body',
                    'name' => __( 'Display Body in Profile', 'userswp' ),
                    'type' => 'checkbox',
                    'default'  => '1',
                    'advanced'  => true,
                ),
                array(
                    'id'   => 'profile_avatar_size',
                    'name' => __( 'Profile Avatar max file size', 'userswp' ),
                    'desc' => sprintf(__( 'Enter Profile Avatar max file size in Kb. e.g. 512 for 512 kb, 1024 for 1 Mb, 2048 for 2 Mb etc. If empty WordPress default (%s) will be used.', 'userswp' ), '<b>'.$file_obj->uwp_formatSizeinKb($file_obj->uwp_get_max_upload_size()).'</b>'),
                    'type' => 'number',
                    'default'  => '',
                    'desc_tip' => true,
                    'advanced'  => true,
                ),
                array(
                    'id'   => 'profile_banner_size',
                    'name' => __( 'Profile Banner max file size', 'userswp' ),
                    'desc' => sprintf(__( 'Enter Profile Banner max file size in Kb. e.g. 512 for 512 kb, 1024 for 1 Mb, 2048 for 2 Mb etc. If empty WordPress default (%s) will be used.', 'userswp' ), '<b>'.$file_obj->uwp_formatSizeinKb($file_obj->uwp_get_max_upload_size()).'</b>'),
                    'type' => 'number',
                    'default'  => '',
                    'desc_tip' => true,
                    'advanced'  => true,
                ),
                array(
                    'id'   => 'profile_banner_width',
                    'name' => __( 'Profile banner width', 'userswp' ),
                    'type' => 'number',
                    'default'  => '1000',
                    'advanced'  => true,
                ),
                array(
                    'id' => 'profile_no_of_items',
                    'name' => __( 'Number of Items', 'userswp' ),
                    'type' => 'text',
                    'default' => '',
                    'desc' 	=> __( 'Enter number of items to display in profile tabs.', 'userswp' ),
                    'desc_tip' => true,
                    'advanced'  => true,
                ),
                array(
                    'id'   => 'uwp_disable_author_link',
                    'name' => __( 'Disable author link redirect to UsersWP profile page.', 'userswp' ),
                    'type' => 'checkbox',
                    'default'  => '0',
                    'advanced'  => true,
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
                    'desc' => __( 'Choose the default layout for Users Page - Users List', 'userswp' ),
                    'type'    => 'select',
                    'options' =>   $this->uwp_available_users_layout(),
                    'class'    => 'uwp-select',
                    'desc_tip' => true,
                ),
                array(
                    'id' => 'users_excluded_from_list',
                    'name' => __( 'Users to exclude', 'userswp' ),
                    'type' => 'text',
                    'desc' 	=> __( 'Enter comma separated ids of users to exclude from users listing.', 'userswp' ),
                    'desc_tip' => true,
                ),

                array( 'type' => 'sectionend', 'id' => 'users_options' ),
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
                    'class' => 'uwp-select',
                    'desc_tip' => true,
                    'default' => 'below_content',
                ),
                array(
                    'id' => 'author_box_display_post_types',
                    'name' => __( 'Post types', 'userswp' ),
                    'desc' => __( 'Choose post types to display author box', 'userswp' ),
                    'type'  => 'multiselect',
                    'sortable' => true,
                    'class'   => 'uwp_select2 uwp-select',
                    'options' =>  uwp_get_posttypes(),
                    'desc_tip' => true,
                    'default' => 'post',
                ),
                array(
                    'name' => __('Author box Content', 'userswp'),
                    'desc' => __('The author box body, this can be text or HTML.', 'userswp'),
                    'id' => 'author_box_content',
                    'type' => 'textarea',
                    'class' => 'code uwp-authorbox-body',
                    'desc_tip' => true,
                    'placeholder' => UsersWP_Defaults::author_box_content(),
                    'custom_desc' => __('Available template tags:', 'userswp') . ' ' . uwp_authbox_tags()
                ),

                array( 'type' => 'sectionend', 'id' => 'authorbox_options' ),
            ));
		} else {
			/**
			 * Filter general settings array.
			 *
			 * @package userswp
			 */
			$settings = apply_filters( 'uwp_settings_general_main', array(
				array(
					'title' => __( 'Welcome to UsersWP', 'userswp' ),
					'type'  => 'title',
					'desc'  => '',
					'id'    => 'general_options'
				),

                array( 'type' => 'sectionend', 'id' => 'general_options' ),

				array(
					'type' => 'general_shortcodes',
				),

				array( 'type' => 'sectionend', 'id' => 'general_options_add' ),

			) );
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
		echo '<div class="color_box">' . uwp_help_tip( $desc ) . '
			<input name="' . esc_attr( $id ) . '" id="' . esc_attr( $id ) . '" type="text" value="' . esc_attr( $value ) . '" class="colorpick" /> <div id="colorPickerDiv_' . esc_attr( $id ) . '" class="colorpickdiv"></div>
		</div>';
	}

    public function uwp_available_tab_items() {
        $tabs_arr = array(
            'more_info' => __( 'More Info', 'userswp' ),
            'posts' => __( 'Posts', 'userswp' ),
            'comments' => __( 'Comments', 'userswp' ),
        );

        $tabs_arr = apply_filters('uwp_available_tab_items', $tabs_arr);

        return $tabs_arr;
    }

    public function uwp_available_users_layout() {
        $tabs_arr = array(
            'list' => __( 'List View', 'userswp' ),
            '2col' => __( 'Grid View - 2 Column', 'userswp' ),
            '3col' => __( 'Grid View - 3 Column', 'userswp' ),
            '4col' => __( 'Grid View - 4 Column', 'userswp' ),
            '5col' => __( 'Grid View - 5 Column', 'userswp' ),
        );

        $tabs_arr = apply_filters('uwp_available_users_layout', $tabs_arr);

        return $tabs_arr;
    }

}

endif;

return new UsersWP_Settings_General();
