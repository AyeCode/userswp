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
                ''              	 => __( 'User Emails', 'userswp' ),
                'admin_emails'       => __( 'Admin Emails', 'userswp' ),
            );

            return apply_filters( 'uwp_get_sections_' . $this->id, $sections );
        }

        public function get_settings( $current_section = '' ) {

            if($current_section == 'admin_emails'){
                $settings = apply_filters( 'uwp_admin_email_settings', array(

                    array('name' => __('New account registration', 'userswp'), 'type' => 'title', 'desc' => '', 'id' => 'registration_success_email_settings'),

                    array(
                        'name' => __('Enable email', 'userswp'),
                        'desc' => __('Send an email to admin when user has created account on site.', 'userswp'),
                        'id' => 'registration_success_email',
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
                        'placeholder' => UsersWP_Defaults::email_user_new_account_subject(),
                        'advanced' => true
                    ),
                    array(
                        'name' => __('Body', 'userswp'),
                        'desc' => __('The email body, this can be text or HTML.', 'userswp'),
                        'id' => 'registration_success_email_content_admin',
                        'type' => 'textarea',
                        'class' => 'code uwp-email-body',
                        'desc_tip' => true,
                        'placeholder' => UsersWP_Defaults::email_user_new_account_body(),
                        'advanced' => true,
                        'custom_desc' => __('Available template tags:', 'userswp') . ' ' . uwp_all_email_tags()
                    ),

                    array('type' => 'sectionend', 'id' => 'registration_success_email_settings'),

                ));
            }else{
                $settings = apply_filters( 'uwp_user_email_settings', array(


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
                        'placeholder' => UsersWP_Defaults::email_user_activation_subject(),
                    ),
                    array(
                        'name' => __('Body', 'userswp'),
                        'desc' => __('The email body, this can be text or HTML.', 'userswp'),
                        'id' => 'registration_activate_email_content',
                        'type' => 'textarea',
                        'class' => 'code uwp-email-body',
                        'desc_tip' => true,
                        'advanced' => true,
                        'placeholder' => UsersWP_Defaults::email_user_activation_body(),
                        'custom_desc' => __('Available template tags:', 'userswp') . ' ' . uwp_all_email_tags()
                    ),

                    array('type' => 'sectionend', 'id' => 'registration_activate_email_settings'),

                    array('name' => __('Registration success email', 'userswp'), 'type' => 'title', 'desc' => '', 'id' => 'registration_success_email_settings'),

                    array(
                        'name' => __('Enable email', 'userswp'),
                        'desc' => __('Send an email to user for when successfull registration.', 'userswp'),
                        'id' => 'registration_activate_email',
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
                        'placeholder' => UsersWP_Defaults::registration_success_email_body(),
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
                        'placeholder' => UsersWP_Defaults::forgot_password_email_body(),
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
                        'placeholder' => UsersWP_Defaults::change_password_email_body(),
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
                        'placeholder' => UsersWP_Defaults::reset_password_email_body(),
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
                        'placeholder' => UsersWP_Defaults::update_account_email_subject(),
                    ),
                    array(
                        'name' => __('Body', 'userswp'),
                        'desc' => __('The email body, this can be text or HTML.', 'userswp'),
                        'id' => 'account_update_email_content',
                        'type' => 'textarea',
                        'class' => 'code uwp-email-body',
                        'desc_tip' => true,
                        'advanced' => true,
                        'placeholder' => UsersWP_Defaults::update_account_email_body(),
                        'custom_desc' => __('Available template tags:', 'userswp') . ' ' . uwp_all_email_tags()
                    ),

                    array('type' => 'sectionend', 'id' => 'account_update_email_settings'),

                ));
            }

            return apply_filters( 'uwp_get_settings_' . $this->id, $settings );
        }

    }

endif;

return new UsersWP_Settings_Email();
