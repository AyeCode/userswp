<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * UsersWP UsersWP_Defaults.
 *
 * A place to store default values used in many places.
 *
 * @class    UsersWP_Defaults
 * @package  UsersWP/Classes
 * @category Class
 * @author   AyeCode
 */
class UsersWP_Defaults {

	/**
	 * The new user account registration email subject default.
	 *
	 * @return string
	 */
	public static function wp_new_user_notification_email_subject() {
		return apply_filters( 'wp_new_user_notification_email_subject', __( "[[#site_name#]] Login Details", "userswp" ) );
	}

	/**
	 * The new user account registration email body default.
	 *
	 * @return string
	 */
	public static function wp_new_user_notification_email_content() {
		return apply_filters( 'wp_new_user_notification_email_content',
			__( "Dear [#user_name#],

To set your password, visit the following address:

[#reset_link#]

You can login here: [#login_url#]

Thank you,
[#site_name_url#]", "userswp"
			)
		);
	}

	/**
	 * The new user account registration email subject default.
	 *
	 * @return string
	 */
	public static function wp_new_user_notification_email_subject_admin() {
		return apply_filters( 'wp_new_user_notification_email_subject', __( "[[#site_name#]] New User Registration", "userswp" ) );
	}

	/**
	 * The new user account registration email body default.
	 *
	 * @return string
	 */
	public static function wp_new_user_notification_email_content_admin() {
		return apply_filters( 'wp_new_user_notification_email_content',
			__( "Dear Admin,

New user registration on your site: [#site_name#]

Username: [#username#]

Email: [#user_email#]

Thank you,
[#site_name_url#]", "userswp"
			)
		);
	}

	/**
	 * The new user account registration email subject default.
	 *
	 * @return string
	 */
	public static function registration_activate_email_subject() {
		return apply_filters( 'uwp_registration_activate_email_subject', __( "[[#site_name#]] Please activate your account", "userswp" ) );
	}

	/**
	 * The new user account registration email body default.
	 *
	 * @return string
	 */
	public static function registration_activate_email_content() {
		return apply_filters( 'uwp_registration_activate_email_content',
			__( "Dear [#user_name#],

Thank you for signing up with [#site_name#]

[#login_details#]

Thank you,
[#site_name_url#]", "userswp"
			)
		);
	}

	/**
	 * The registration success email subject default.
	 *
	 * @return string
	 */
	public static function registration_success_email_subject() {
		return apply_filters( 'uwp_registration_success_email_subject', __( "[[#site_name#]] Your Log In Details", "userswp" ) );
	}

	/**
	 * The registration success email body default.
	 *
	 * @return string
	 */
	public static function registration_success_email_content() {
		return apply_filters( 'uwp_registration_success_email_content',
			__( "Dear [#user_name#],

You can log in with the following information:

[#login_details#]

You can login here: [#login_url#]

Thank you,
[#site_name_url#]", "userswp"
			)
		);
	}

	/**
	 * The forgot password email subject default.
	 *
	 * @return string
	 */
	public static function forgot_password_email_subject() {
		return apply_filters( 'uwp_forgot_password_email_subject', __( "[#site_name#] - Your new password", "userswp" ) );
	}

	/**
	 * The forgot password email body default.
	 *
	 * @return string
	 */
	public static function forgot_password_email_content() {
		return apply_filters( 'uwp_forgot_password_email_content',
			__( "Dear [#user_name#],

[#login_details#]

You can login here: [#login_url#]

Thank you,
[#site_name_url#]", "userswp"
			)
		);
	}

	/**
	 * The change password email subject default.
	 *
	 * @return string
	 */
	public static function change_password_email_subject() {
		return apply_filters( 'uwp_change_password_email_subject', __( "[#site_name#] - Password has been changed", "userswp" ) );
	}

	/**
	 * The change password email body default.
	 *
	 * @return string
	 */
	public static function change_password_email_content() {
		return apply_filters( 'uwp_change_password_email_content',
			__( "Dear [#user_name#],

Your password has been changed successfully.

You can login here: [#login_url#]

Thank you,
[#site_name_url#]", "userswp"
			)
		);
	}

	/**
	 * The reset password email subject default.
	 *
	 * @return string
	 */
	public static function reset_password_email_subject() {
		return apply_filters( 'uwp_reset_password_email_subject', __( "[#site_name#] - Password has been reset", "userswp" ) );
	}

	/**
	 * The reset password email body default.
	 *
	 * @return string
	 */
	public static function reset_password_email_content() {
		return apply_filters( 'uwp_reset_password_email_content',
			__( "Dear [#user_name#],

Your password has been reset.

You can login here: [#login_url#]

Thank you,
[#site_name_url#]", "userswp"
			)
		);
	}

	/**
	 * The update account email subject default.
	 *
	 * @return string
	 */
	public static function account_update_email_subject() {
		return apply_filters( 'uwp_account_update_email_subject', __( "[#site_name#] - Account has been updated", "userswp" ) );
	}

	/**
	 * The update account email body default.
	 *
	 * @return string
	 */
	public static function account_update_email_content() {
		return apply_filters( 'uwp_account_update_email_content',
			__( "Dear [#user_name#],

Your account has been updated successfully.

Thank you,
[#site_name_url#]", "userswp"
			)
		);
	}

	/**
	 * The update account email subject default.
	 *
	 * @return string
	 */
	public static function account_delete_email_subject() {
		return apply_filters( 'uwp_account_delete_email_subject', __( "[#site_name#] - Your account has been deleted.", "userswp" ) );
	}

	/**
	 * The delete account email body default.
	 *
	 * @return string
	 */
	public static function account_delete_email_content() {
		return apply_filters( 'uwp_account_delete_email_content',
			__( "Dear [#user_name#],

Your account has been deleted successfully.

Thank you,
[#site_name_url#]", "userswp"
			)
		);
	}

	/**
	 * The update account email subject default.
	 *
	 * @return string
	 */
	public static function account_delete_email_subject_admin() {
		return apply_filters( 'uwp_account_delete_email_subject_admin', __( "[#site_name#] - Account has been deleted by a user.", "userswp" ) );
	}

	/**
	 * The delete account email body default.
	 *
	 * @return string
	 */
	public static function account_delete_email_content_admin() {
		return apply_filters( 'uwp_account_delete_email_content_admin',
			__( "Dear Admin,

User has deleted own account from the site.

[#login_details#]

Thank you,
[#site_name_url#]", "userswp"
			)
		);
	}

	/**
	 * The new user account registration email subject default.
	 *
	 * @return string
	 */
	public static function registration_success_email_subject_admin() {
		return apply_filters( 'uwp_registration_success_email_subject_admin', __( "[[#site_name#]] New account registration", "userswp" ) );
	}

	/**
	 * The new user account registration email body default.
	 *
	 * @return string
	 */
	public static function registration_success_email_content_admin() {
		return apply_filters( 'uwp_registration_success_email_content_admin',
			__( "Dear Admin,

A user has been registered recently on your website.

[#extras#]", "userswp"
			)
		);
	}

	/**
	 * Returns default author box content
	 *
	 * @return string
	 */
	public static function author_box_content() {
		return apply_filters( 'uwp_author_box_content',
			'<div class="uwp-author-box">
                <div class="media-figure">
                    <a href="[#author_link#]">[#author_image#]</a>
                </div>
                <div class="media-body">
                    <h3>Author: <a href="[#author_link#]">[#author_name#]</a></h3>
                    <p>[#author_bio#]</p>
                </div>
            </div>'
		);
	}

	/**
	 * Returns default author box content for bootstrap
	 *
	 * @return string
	 */
	public static function author_box_content_bootstrap() {
		return apply_filters( 'uwp_author_box_content_bootstrap',
			'<div class="d-block text-center text-md-left d-md-flex p-3 bg-light">
  <a href="[#author_link#]"><img src="[#author_image_url#]" class="rounded-circle shadow border border-white border-width-4 mr-3" width="60" height="60" alt="[#author_name#]"></a>
  <div class="media-body">
    <h5 class="mt-0">Author: <a href="[#author_link#]">[#author_name#]</a></h5>
    [uwp_button_group user_id="post_author"]
    <p>[#author_bio#]</p>
  </div>
</div>'
		);
	}

	/**
	 * Returns default user list item content
	 * @param bool $no_filter
	 *
	 * @return string
	 */
	public static function page_user_list_item_content( $no_filter = false ) {
		$content = "[uwp_profile_header][uwp_output_location location='users'][uwp_user_actions]";

		if ( $no_filter ) {
			return $content;
		} else {
			return apply_filters( "uwp_page_user_list_item_default_content", $content );
		}
	}

}