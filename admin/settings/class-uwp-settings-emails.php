<?php
/**
 * UsersWP Emails Settings
 *
 * @author      AyeCode
 * @category    Admin
 * @package     userswp/Admin
 * @version     1.0.24
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'UsersWP_Settings_Email', false ) ) :

	/**
	 * UsersWP_Settings_Email.
	 */
	class UsersWP_Settings_Email extends UsersWP_Settings_Page {

		/**
		 * Constructor.
		 */
		public function __construct() {

			$this->id    = 'emails';
			$this->label = __( 'Emails', 'userswp' );

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
				''              	 => __( 'Email Options', 'userswp' ),
				'user_emails'        => __( 'User Emails', 'userswp' ),
				'admin_emails'       => __( 'Admin Emails', 'userswp' ),
			);

			return apply_filters( 'uwp_get_sections_' . $this->id, $sections );
		}

		public function get_settings( $current_section = '' ) {

			if($current_section == 'admin_emails'){
				$settings = apply_filters( 'uwp_admin_email_settings', array(

					array('name' => __('WP new user notification email', 'userswp'), 'type' => 'title', 'desc' => '', 'id' => 'wp_new_user_notification_email_settings'),

					array(
						'name' => __('Enable email', 'userswp'),
						'desc' => __('This will replace the email sent by WordPress when new user registered.', 'userswp'),
						'id' => 'wp_new_user_notification_email_admin',
						'type' => 'checkbox',
						'default' => 1,
					),
					array(
						'name' => __('Subject', 'userswp'),
						'desc' => __('The email subject.', 'userswp'),
						'id' => 'wp_new_user_notification_email_subject_admin',
						'type' => 'text',
						'class' => 'large-text',
						'desc_tip' => true,
						'advanced' => true,
						'placeholder' => UsersWP_Defaults::wp_new_user_notification_email_subject_admin(),
					),
					array(
						'name' => __('Body', 'userswp'),
						'desc' => __('The email body, this can be text or HTML.', 'userswp'),
						'id' => 'wp_new_user_notification_email_content_admin',
						'type' => 'textarea',
						'class' => 'code uwp-email-body',
						'desc_tip' => true,
						'advanced' => true,
						'placeholder' => UsersWP_Defaults::wp_new_user_notification_email_content_admin(),
						'custom_desc' => __('Available template tags:', 'userswp') . ' ' . uwp_wp_new_user_notification_tags()
					),

					array('type' => 'sectionend', 'id' => 'wp_new_user_notification_email_settings'),

					array('name' => __('New account registration', 'userswp'), 'type' => 'title', 'desc' => '', 'id' => 'registration_success_admin_email_settings'),

					array(
						'name' => __('Enable email', 'userswp'),
						'desc' => __('Send an email to admin when user has created account on site.', 'userswp'),
						'id' => 'registration_success_email_admin',
						'type' => 'checkbox',
						'default' => 1,
					),
					array(
						'name' => __('Subject', 'userswp'),
						'desc' => __('The email subject.', 'userswp'),
						'id' => 'registration_success_email_subject_admin',
						'type' => 'text',
						'class' => 'large-text',
						'desc_tip' => true,
						'placeholder' => UsersWP_Defaults::registration_success_email_subject_admin(),
						'advanced' => true
					),
					array(
						'name' => __('Body', 'userswp'),
						'desc' => __('The email body, this can be text or HTML.', 'userswp'),
						'id' => 'registration_success_email_content_admin',
						'type' => 'textarea',
						'class' => 'code uwp-email-body',
						'desc_tip' => true,
						'placeholder' => UsersWP_Defaults::registration_success_email_content_admin(),
						'advanced' => true,
						'custom_desc' => __('Available template tags:', 'userswp') . ' ' . uwp_all_email_tags(true, array('[#form_fields#]'))
					),

					array('type' => 'sectionend', 'id' => 'registration_success_admin_email_settings'),

					array('name' => __('Account delete email', 'userswp'), 'type' => 'title', 'desc' => '', 'id' => 'account_delete_admin_email_settings'),

					array(
						'name' => __('Enable email', 'userswp'),
						'desc' => __('Send an email to admin after user account deleted from the site.', 'userswp'),
						'id' => 'account_delete_email_admin',
						'type' => 'checkbox',
						'default' => 1,
					),
					array(
						'name' => __('Subject', 'userswp'),
						'desc' => __('The email subject.', 'userswp'),
						'id' => 'account_delete_email_subject_admin',
						'type' => 'text',
						'class' => 'large-text',
						'desc_tip' => true,
						'advanced' => true,
						'placeholder' => UsersWP_Defaults::account_delete_email_subject_admin(),
					),
					array(
						'name' => __('Body', 'userswp'),
						'desc' => __('The email body, this can be text or HTML.', 'userswp'),
						'id' => 'account_delete_email_content_admin',
						'type' => 'textarea',
						'class' => 'code uwp-email-body',
						'desc_tip' => true,
						'advanced' => true,
						'placeholder' => UsersWP_Defaults::account_delete_email_content_admin(),
						'custom_desc' => __('Available template tags:', 'userswp') . ' ' . uwp_delete_account_email_tags()
					),

					array('type' => 'sectionend', 'id' => 'account_delete_admin_email_settings'),

				));
			}elseif($current_section == 'user_emails'){
				$settings = apply_filters( 'uwp_user_email_settings', array(

					array('name' => __('WP new user notification email', 'userswp'), 'type' => 'title', 'desc' => '', 'id' => 'wp_new_user_notification_email_settings'),

					array(
						'name' => __('Enable email', 'userswp'),
						'desc' => __('This will replace the email sent by WordPress when new user registered.', 'userswp'),
						'id' => 'wp_new_user_notification_email',
						'type' => 'checkbox',
						'default' => 1,
					),
					array(
						'name' => __('Subject', 'userswp'),
						'desc' => __('The email subject.', 'userswp'),
						'id' => 'wp_new_user_notification_email_subject',
						'type' => 'text',
						'class' => 'large-text',
						'desc_tip' => true,
						'advanced' => true,
						'placeholder' => UsersWP_Defaults::wp_new_user_notification_email_subject(),
					),
					array(
						'name' => __('Body', 'userswp'),
						'desc' => __('The email body, this can be text or HTML.', 'userswp'),
						'id' => 'wp_new_user_notification_email_content',
						'type' => 'textarea',
						'class' => 'code uwp-email-body',
						'desc_tip' => true,
						'advanced' => true,
						'placeholder' => UsersWP_Defaults::wp_new_user_notification_email_content(),
						'custom_desc' => __('Available template tags:', 'userswp') . ' ' . uwp_wp_new_user_notification_tags(true, array('[#reset_link#]'))
					),

					array('type' => 'sectionend', 'id' => 'wp_new_user_notification_email_settings'),

					array('name' => __('Registration activate email', 'userswp'), 'type' => 'title', 'desc' => '', 'id' => 'registration_activate_email_settings'),

					array(
						'name' => __('Enable email', 'userswp'),
						'desc' => __('Send an email to user for activation when registered.', 'userswp'),
						'id' => 'registration_activate_email',
						'type' => 'checkbox',
						'default' => 1,
					),
					array(
						'name' => __('Subject', 'userswp'),
						'desc' => __('The email subject.', 'userswp'),
						'id' => 'registration_activate_email_subject',
						'type' => 'text',
						'class' => 'large-text',
						'desc_tip' => true,
						'advanced' => true,
						'placeholder' => UsersWP_Defaults::registration_activate_email_subject(),
					),
					array(
						'name' => __('Body', 'userswp'),
						'desc' => __('The email body, this can be text or HTML.', 'userswp'),
						'id' => 'registration_activate_email_content',
						'type' => 'textarea',
						'class' => 'code uwp-email-body',
						'desc_tip' => true,
						'advanced' => true,
						'placeholder' => UsersWP_Defaults::registration_activate_email_content(),
						'custom_desc' => __('Available template tags:', 'userswp') . ' ' . uwp_all_email_tags(true, array('[#activation_link#]'))
					),

					array('type' => 'sectionend', 'id' => 'registration_activate_email_settings'),

					array('name' => __('Registration success email', 'userswp'), 'type' => 'title', 'desc' => '', 'id' => 'registration_success_email_settings'),

					array(
						'name' => __('Enable email', 'userswp'),
						'desc' => __('Send an email to user for when successfull registration.', 'userswp'),
						'id' => 'registration_success_email',
						'type' => 'checkbox',
						'default' => 1,
					),
					array(
						'name' => __('Subject', 'userswp'),
						'desc' => __('The email subject.', 'userswp'),
						'id' => 'registration_success_email_subject',
						'type' => 'text',
						'class' => 'large-text',
						'desc_tip' => true,
						'advanced' => true,
						'placeholder' => UsersWP_Defaults::registration_success_email_subject(),
					),
					array(
						'name' => __('Body', 'userswp'),
						'desc' => __('The email body, this can be text or HTML.', 'userswp'),
						'id' => 'registration_success_email_content',
						'type' => 'textarea',
						'class' => 'code uwp-email-body',
						'desc_tip' => true,
						'advanced' => true,
						'placeholder' => UsersWP_Defaults::registration_success_email_content(),
						'custom_desc' => __('Available template tags:', 'userswp') . ' ' . uwp_all_email_tags()
					),

					array('type' => 'sectionend', 'id' => 'registration_success_email_settings'),

					array('name' => __('Forgot password email', 'userswp'), 'type' => 'title', 'desc' => '', 'id' => 'forgot_password_email_settings'),

					array(
						'name' => __('Enable email', 'userswp'),
						'desc' => __('Send an email to user for forgot password.', 'userswp'),
						'id' => 'forgot_password_email',
						'type' => 'checkbox',
						'default' => 1,
					),
					array(
						'name' => __('Subject', 'userswp'),
						'desc' => __('The email subject.', 'userswp'),
						'id' => 'forgot_password_email_subject',
						'type' => 'text',
						'class' => 'large-text',
						'desc_tip' => true,
						'advanced' => true,
						'placeholder' => UsersWP_Defaults::forgot_password_email_subject(),
					),
					array(
						'name' => __('Body', 'userswp'),
						'desc' => __('The email body, this can be text or HTML.', 'userswp'),
						'id' => 'forgot_password_email_content',
						'type' => 'textarea',
						'class' => 'code uwp-email-body',
						'desc_tip' => true,
						'advanced' => true,
						'placeholder' => UsersWP_Defaults::forgot_password_email_content(),
						'custom_desc' => __('Available template tags:', 'userswp') . ' ' . uwp_all_email_tags()
					),

					array('type' => 'sectionend', 'id' => 'forgot_password_email_settings'),

					array('name' => __('Change password email', 'userswp'), 'type' => 'title', 'desc' => '', 'id' => 'change_password_email_settings'),

					array(
						'name' => __('Enable email', 'userswp'),
						'desc' => __('Send an email to user for change password.', 'userswp'),
						'id' => 'change_password_email',
						'type' => 'checkbox',
						'default' => 1,
					),
					array(
						'name' => __('Subject', 'userswp'),
						'desc' => __('The email subject.', 'userswp'),
						'id' => 'change_password_email_subject',
						'type' => 'text',
						'class' => 'large-text',
						'desc_tip' => true,
						'advanced' => true,
						'placeholder' => UsersWP_Defaults::change_password_email_subject(),
					),
					array(
						'name' => __('Body', 'userswp'),
						'desc' => __('The email body, this can be text or HTML.', 'userswp'),
						'id' => 'change_password_email_content',
						'type' => 'textarea',
						'class' => 'code uwp-email-body',
						'desc_tip' => true,
						'advanced' => true,
						'placeholder' => UsersWP_Defaults::change_password_email_content(),
						'custom_desc' => __('Available template tags:', 'userswp') . ' ' . uwp_all_email_tags()
					),

					array('type' => 'sectionend', 'id' => 'change_password_email_settings'),

					array('name' => __('Reset password email', 'userswp'), 'type' => 'title', 'desc' => '', 'id' => 'reset_password_email_settings'),

					array(
						'name' => __('Enable email', 'userswp'),
						'desc' => __('Send an email to user for reset password.', 'userswp'),
						'id' => 'reset_password_email',
						'type' => 'checkbox',
						'default' => 1,
					),
					array(
						'name' => __('Subject', 'userswp'),
						'desc' => __('The email subject.', 'userswp'),
						'id' => 'reset_password_email_subject',
						'type' => 'text',
						'class' => 'large-text',
						'desc_tip' => true,
						'advanced' => true,
						'placeholder' => UsersWP_Defaults::reset_password_email_subject(),
					),
					array(
						'name' => __('Body', 'userswp'),
						'desc' => __('The email body, this can be text or HTML.', 'userswp'),
						'id' => 'reset_password_email_content',
						'type' => 'textarea',
						'class' => 'code uwp-email-body',
						'desc_tip' => true,
						'advanced' => true,
						'placeholder' => UsersWP_Defaults::reset_password_email_content(),
						'custom_desc' => __('Available template tags:', 'userswp') . ' ' . uwp_all_email_tags()
					),

					array('type' => 'sectionend', 'id' => 'reset_password_email_settings'),

					array('name' => __('Account update email', 'userswp'), 'type' => 'title', 'desc' => '', 'id' => 'account_update_email_settings'),

					array(
						'name' => __('Enable email', 'userswp'),
						'desc' => __('Send an email to user when updating account details.', 'userswp'),
						'id' => 'account_update_email',
						'type' => 'checkbox',
						'default' => 1,
					),
					array(
						'name' => __('Subject', 'userswp'),
						'desc' => __('The email subject.', 'userswp'),
						'id' => 'account_update_email_subject',
						'type' => 'text',
						'class' => 'large-text',
						'desc_tip' => true,
						'advanced' => true,
						'placeholder' => UsersWP_Defaults::account_update_email_subject(),
					),
					array(
						'name' => __('Body', 'userswp'),
						'desc' => __('The email body, this can be text or HTML.', 'userswp'),
						'id' => 'account_update_email_content',
						'type' => 'textarea',
						'class' => 'code uwp-email-body',
						'desc_tip' => true,
						'advanced' => true,
						'placeholder' => UsersWP_Defaults::account_update_email_content(),
						'custom_desc' => __('Available template tags:', 'userswp') . ' ' . uwp_all_email_tags(true, array('[#form_fields#]'))
					),

					array('type' => 'sectionend', 'id' => 'account_update_email_settings'),

					array('name' => __('Account delete email', 'userswp'), 'type' => 'title', 'desc' => '', 'id' => 'account_delete_email_settings'),

					array(
						'name' => __('Enable email', 'userswp'),
						'desc' => __('Send an email to user after deleting account from the site.', 'userswp'),
						'id' => 'account_delete_email',
						'type' => 'checkbox',
						'default' => 1,
					),
					array(
						'name' => __('Subject', 'userswp'),
						'desc' => __('The email subject.', 'userswp'),
						'id' => 'account_delete_email_subject',
						'type' => 'text',
						'class' => 'large-text',
						'desc_tip' => true,
						'advanced' => true,
						'placeholder' => UsersWP_Defaults::account_delete_email_subject(),
					),
					array(
						'name' => __('Body', 'userswp'),
						'desc' => __('The email body, this can be text or HTML.', 'userswp'),
						'id' => 'account_delete_email_content',
						'type' => 'textarea',
						'class' => 'code uwp-email-body',
						'desc_tip' => true,
						'advanced' => true,
						'placeholder' => UsersWP_Defaults::account_delete_email_content(),
						'custom_desc' => __('Available template tags:', 'userswp') . ' ' . uwp_delete_account_email_tags()
					),

					array('type' => 'sectionend', 'id' => 'account_delete_email_settings'),

				));
			} else {
				$settings = apply_filters( 'uwp_email_settings', array(


					array('name' => __('Email sender options', 'userswp'), 'type' => 'title', 'desc' => '', 'id' => 'email_settings'),

					array(
						'name' => __('Sender name', 'userswp'),
						'desc' => __('How the sender name appears in outgoing UsersWP emails.', 'userswp'),
						'id' => 'email_name',
						'type' => 'text',
						'placeholder' => get_bloginfo('name'),
						'desc_tip' => true,
					),
					array(
						'name' => __('Email address', 'userswp'),
						'desc' => __('How the sender email appears in outgoing UsersWP emails.', 'userswp'),
						'id' => 'email_address',
						'type' => 'text',
						'placeholder' => UsersWP_Mails::get_mail_from(),
						'desc_tip' => true,
					),

					array('type' => 'sectionend', 'id' => 'email_settings'),

					array('name' => __('Email Template', 'userswp'), 'type' => 'title', 'desc' => sprintf( __( 'This section lets you customize the UsersWP email template. %sClick here to preview your email template%s.', 'userswp' ), '<a href="'.wp_nonce_url( admin_url( '?uwp_preview_mail=true' ), 'uwp-preview-mail' ).'" target="_blank">', '</a>' ), 'id' => 'email_template_settings'),

					array(
						'type' => 'select',
						'id' => 'email_type',
						'name' => __('Email type', 'userswp'),
						'desc' => __('Select format of the email to send.', 'userswp'),
						'options' => $this->get_email_type_options(),
						'default' => 'html',
						'desc_tip' => true,
						'advanced' => true,
					),
					array(
						'name' => __('Logo', 'userswp'),
						'desc' => __('Upload a logo to be displayed at the top of the emails. Displayed on HTML emails only.', 'userswp'),
						'id' => 'email_logo',
						'type' => 'image',
						'image_size' => 'full',
						'desc_tip' => true,
					),
					array(
						'name' => __('Footer Text', 'userswp'),
						'desc' => __('The text to appear in the footer of all UsersWP emails.', 'userswp'),
						'id' => 'email_footer_text',
						'type' => 'text',
						'class' => 'code',
						'desc_tip' => true,
						'placeholder' => $this->email_footer_text()
					),
					'email_base_color' => array(
						'id'   => 'email_base_color',
						'name' => __( 'Base Color', 'userswp' ),
						'desc' => __( 'The base color for UsersWP email template. Default <code>#557da2</code>.', 'userswp' ),
						'default' => '#557da2',
						'type' => 'color',
						'desc_tip' => true,
						'advanced' => true,
					),
					'email_background_color' => array(
						'id'   => 'email_background_color',
						'name' => __( 'Background Color', 'userswp' ),
						'desc' => __( 'The background color of email template. Default <code>#f5f5f5</code>.', 'userswp' ),
						'default' => '#f5f5f5',
						'type' => 'color',
						'desc_tip' => true,
						'advanced' => true,
					),
					'email_body_background_color' => array(
						'id'   => 'email_body_background_color',
						'name' => __( 'Body Background Color', 'userswp' ),
						'desc' => __( 'The main body background color of email template. Default <code>#fdfdfd</code>.', 'userswp' ),
						'default' => '#fdfdfd',
						'type' => 'color',
						'desc_tip' => true,
						'advanced' => true,
					),
					'email_text_color' => array(
						'id'   => 'email_text_color',
						'name' => __( 'Body Text Color', 'userswp' ),
						'desc' => __( 'The main body text color. Default <code>#505050</code>.', 'userswp' ),
						'default' => '#505050',
						'type' => 'color',
						'desc_tip' => true,
						'advanced' => true,
					),
					'email_header_background_color' => array(
						'id'   => 'email_header_background_color',
						'name' => __( 'Header Background Color', 'userswp' ),
						'desc' => __( 'The header background color of email template. Default <code>#555555</code>.', 'userswp' ),
						'default' => '#555555',
						'type' => 'color',
						'desc_tip' => true,
						'advanced' => true,
					),
					'email_header_text_color' => array(
						'id'   => 'email_header_text_color',
						'name' => __( 'Header Text Color', 'userswp' ),
						'desc' => __( 'The footer text color. Default <code>#ffffff</code>.', 'userswp' ),
						'default' => '#ffffff',
						'type' => 'color',
						'desc_tip' => true,
						'advanced' => true,
					),
					'email_footer_background_color' => array(
						'id'   => 'email_footer_background_color',
						'name' => __( 'Footer Background Color', 'userswp' ),
						'desc' => __( 'The footer background color of email template. Default <code>#666666</code>.', 'userswp' ),
						'default' => '#666666',
						'type' => 'color',
						'desc_tip' => true,
						'advanced' => true,
					),
					'email_footer_text_color' => array(
						'id'   => 'email_footer_text_color',
						'name' => __( 'Footer Text Color', 'userswp' ),
						'desc' => __( 'The footer text color. Default <code>#dddddd</code>.', 'userswp' ),
						'default' => '#dddddd',
						'type' => 'color',
						'desc_tip' => true,
						'advanced' => true,
					),

					array('type' => 'sectionend', 'id' => 'email_template_settings'),

				));
			}

			return apply_filters( 'uwp_get_settings_' . $this->id, $settings );
		}

		/**
		 * The default email footer text.
		 *
		 * @since 1.2.1.2
		 * @return string
		 */
		public function email_footer_text(){
			return apply_filters('uwp_email_footer_text',
				wp_sprintf( __( '%s - Powered by UsersWP', 'userswp' ), get_bloginfo( 'name', 'display' ) )
			);
		}

		/**
		 * Email type options.
		 *
		 * @since 1.2.1.2
		 * @return array
		 */
		public function get_email_type_options() {
			$types = array();
			if ( class_exists( 'DOMDocument' ) ) {
				$types['html'] = __( 'HTML', 'userswp' );
			}
			$types['plain'] = __( 'Plain text', 'userswp' );

			return $types;
		}

	}

endif;

return new UsersWP_Settings_Email();