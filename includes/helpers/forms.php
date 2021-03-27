<?php
/**
 * Returns the register form fields.
 *
 * @return      array       Form fields.
 * @package     userswp
 *
 * @since       1.0.0
 */
function get_register_form_fields() {
	global $wpdb;
	$table_name = uwp_get_table_prefix() . 'uwp_form_fields';
	$extras_table_name = uwp_get_table_prefix() . 'uwp_form_extras';
	$fields = $wpdb->get_results($wpdb->prepare("SELECT fields.* FROM " . $table_name . " fields JOIN " . $extras_table_name . " extras ON extras.site_htmlvar_name = fields.htmlvar_name WHERE fields.form_type = %s AND fields.is_active = '1' AND fields.for_admin_use != '1' AND fields.is_register_field = '1' AND extras.form_type = 'register' ORDER BY extras.sort_order ASC", array('account')));
	$fields = apply_filters('uwp_get_register_form_fields', $fields);
	return $fields;
}

function check_register_form_field( $var ) {
	if ( ! $var ) {
		return false;
	}

	global $wpdb;
	$extras_table_name = uwp_get_table_prefix() . 'uwp_form_extras';

	$fields = $wpdb->get_results(
		$wpdb->prepare(
			"select * from  " . $extras_table_name . " where form_type = %s AND site_htmlvar_name = %s order by sort_order asc",
			array( 'register', $var )
		)
	);

	$fields = apply_filters( 'uwp_check_register_form_field', $fields );

	return $fields;
}

/**
 * Returns the register form validate-able fields.
 *
 * @param int $role_id Role ID from role addons. Default 0.
 *
 * @return      array                   Validate-able fields.
 * @since       1.0.0
 * @package     userswp
 *
 */
function get_register_validate_form_fields( $role_id ) {
	global $wpdb;
	$table_name        = uwp_get_table_prefix() . 'uwp_form_fields';
	$extras_table_name = uwp_get_table_prefix() . 'uwp_form_extras';
	if ( $role_id == 0 ) {
		$fields = $wpdb->get_results( $wpdb->prepare( "SELECT fields.* FROM " . $table_name . " fields JOIN " . $extras_table_name . " extras ON extras.site_htmlvar_name = fields.htmlvar_name WHERE fields.form_type = %s AND fields.field_type != 'fieldset' AND fields.field_type != 'file' AND fields.is_active = '1' AND fields.for_admin_use != '1' AND fields.is_register_field = '1' ORDER BY extras.sort_order ASC", array( 'account' ) ) );
	} else {
		$slug   = get_post_field( 'post_name', $role_id );
		$fields = $wpdb->get_results( $wpdb->prepare( "SELECT fields.* FROM " . $table_name . " fields JOIN " . $extras_table_name . " extras ON extras.site_htmlvar_name = fields.htmlvar_name WHERE fields.form_type = %s AND fields.field_type != 'fieldset' AND fields.field_type != 'file' AND fields.is_active = '1' AND fields.for_admin_use != '1' AND fields.is_register_field = '1' AND FIND_IN_SET(%s, fields.user_roles) ORDER BY extras.sort_order ASC", array(
			'account',
			$slug
		) ) );
	}

	$fields = apply_filters( 'uwp_get_register_validate_form_fields', $fields, $role_id );

	return $fields;
}

/**
 * Returns the change password form validate-able fields.
 *
 * @return      array       Validate-able fields
 * @package     userswp
 *
 * @since       1.0.0
 */
function get_change_validate_form_fields() {
	global $wpdb;
	$table_name          = uwp_get_table_prefix() . 'uwp_form_fields';
	$enable_old_password = uwp_get_option( 'change_enable_old_password', false );
	if ( $enable_old_password == '1' ) {
		$fields = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $table_name . " WHERE form_type = %s AND field_type != 'fieldset' AND field_type != 'file' AND is_active = '1' AND for_admin_use != '1' ORDER BY sort_order ASC", array( 'change' ) ) );
	} else {
		$fields = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $table_name . " WHERE form_type = %s AND field_type != 'fieldset' AND field_type != 'file' AND is_active = '1' AND for_admin_use != '1' AND htmlvar_name != 'old_password' ORDER BY sort_order ASC", array( 'change' ) ) );
	}

	return $fields;
}

/**
 * Returns the account form fields.
 *
 * @param string $extra_where Extra where query.
 *
 * @return      array                       Form fields.
 * @since       1.0.0
 * @package     userswp
 *
 */
function get_account_form_fields( $extra_where = '' ) {
	global $wpdb;

	$table_name        = uwp_get_table_prefix() . 'uwp_form_fields';
	$include_admin_use = apply_filters( 'uwp_account_include_admin_use_only_fields', false );
	if ( $include_admin_use ) {
		$fields = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $table_name . " WHERE form_type = %s AND is_active = '1' AND is_register_only_field = '0' AND htmlvar_name != 'password'" . $extra_where . " ORDER BY sort_order ASC", array( 'account' ) ) );
	} else {
		$fields = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $table_name . " WHERE form_type = %s AND is_active = '1' AND for_admin_use != '1' AND is_register_only_field = '0' AND htmlvar_name != 'password'" . $extra_where . " ORDER BY sort_order ASC", array( 'account' ) ) );
	}

	return $fields;
}

/**
 * Returns the change password form fields.
 *
 * @return      array       Form fields.
 * @package     userswp
 *
 * @since       1.0.0
 */
function get_change_form_fields() {
	global $wpdb;
	$table_name          = uwp_get_table_prefix() . 'uwp_form_fields';
	$enable_old_password = uwp_get_option( 'change_enable_old_password', false );
	if ( $enable_old_password == '1' ) {
		$fields = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $table_name . " WHERE form_type = %s AND is_active = '1' AND for_admin_use != '1' ORDER BY sort_order ASC", array( 'change' ) ) );
	} else {
		$fields = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $table_name . " WHERE form_type = %s AND is_active = '1' AND for_admin_use != '1' AND htmlvar_name != 'old_password' ORDER BY sort_order ASC", array( 'change' ) ) );
	}

	return $fields;
}

/**
 * Validates the submitted form data.
 *
 * @param array      $data   Submitted form data
 * @param string     $type   Form type.
 * @param array|bool $fields Fields applicable for validation.
 *
 * @return      array|mixed|WP_Error   Validated form data.
 * @since       1.0.0
 * @package     userswp
 *
 */
function uwp_validate_fields( $data, $type, $fields = false ) {
	$validation = new UsersWP_Validation();

	return $validation->validate_fields( $data, $type, $fields );
}

/**
 * Returns form field label. If empty returns the field title.
 *
 * @param object $field Field info.
 *
 * @return      string                  Label.
 * @since       1.0.0
 * @package     userswp
 *
 */
function uwp_get_form_label( $field ) {
	if ( isset( $field->form_label ) && ! empty( $field->form_label ) ) {
		$label = __( $field->form_label, 'userswp' );
	} else {
		$label = __( $field->site_title, 'userswp' );
	}

	return apply_filters( 'uwp_get_form_label', stripslashes( $label ), $field );
}

/**
 * Returns placeholder for field.
 *
 * @param object $field Field info.
 *
 * @return      string                  Label.
 */
function uwp_get_field_placeholder( $field ) {

	if ( isset( $field->field_type ) && in_array( $field->field_type, array( 'select', 'multiselect' ) ) ) {
		if ( isset( $field->placeholder_value ) && ! empty( $field->placeholder_value ) ) {
			$placeholder = __( $field->placeholder_value, 'userswp' );
		} else {
			$placeholder = wp_sprintf( __( 'Choose %s&hellip;', 'userswp' ), uwp_get_form_label( $field ) );
		}
	} else {
		if ( isset( $field->placeholder_value ) && ! empty( $field->placeholder_value ) ) {
			$placeholder = __( $field->placeholder_value, 'userswp' );
		} else {
			$placeholder = uwp_get_form_label( $field );
		}
	}

	if ( isset( $field->is_required ) && ! empty( $field->is_required ) ) {
		$placeholder .= ' *';
	}

	return apply_filters( 'uwp_get_field_placeholder', stripslashes( $placeholder ), $field );
}

/**
 * Returns description for field.
 *
 * @param object $field   Field info.
 * @param string $default Default value.
 *
 * @return      string                  Label.
 */
function uwp_get_field_description( $field, $default = '' ) {

	if ( isset( $field->help_text ) && ! empty( $field->help_text ) ) {
		$desc = __( $field->help_text, 'userswp' );
	} else {
		$desc = $default;
	}

	return apply_filters( 'uwp_get_field_description', stripslashes( $desc ), $field );
}

/**
 * Gets the custom field info for given key.
 *
 * @param string $htmlvar_name Custom field key.
 * @param string $form_type    Form type.
 *
 * @return      object                          Field info.
 * @package     userswp
 *
 * @since       1.0.0
 */
function uwp_get_custom_field_info( $htmlvar_name, $form_type = 'account' ) {
	global $wpdb;
	$table_name = uwp_get_table_prefix() . 'uwp_form_fields';
	if ( $form_type ) {
		$field = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . $table_name . " WHERE htmlvar_name = %s AND form_type = %s", array(
			$htmlvar_name,
			$form_type
		) ) );
	} else {
		$field = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . $table_name . " WHERE htmlvar_name = %s", array( $htmlvar_name ) ) );
	}

	return $field;
}

function uwp_resend_activation_mail($user_id) {

	if ( ! $user_id ) {
		return false;
	}

	if ( 'email_unconfirmed' == get_user_meta( $user_id, 'uwp_mod', true ) ) {
		$user_data = get_userdata( $user_id );

		$activation_link = uwp_get_activation_link( $user_id );

		if ( $activation_link ) {

			$message = __( 'To activate your account, visit the following address:', 'userswp' ) . "\r\n\r\n";

			$message .= "<a href='" . esc_url( $activation_link ) . "' target='_blank'>" . esc_url( $activation_link ) . "</a>" . "\r\n";

			$activate_message = '<p><b>' . __( 'Please activate your account :', 'userswp' ) . '</b></p><p>' . $message . '</p>';

			$activate_message = apply_filters( 'uwp_activation_mail_message', $activate_message, $user_id );

			$email_vars = array(
				'user_id'         => $user_id,
				'login_details'   => $activate_message,
				'activation_link' => $activation_link,
			);

			$send_result = UsersWP_Mails::send( $user_data->user_email, 'registration_activate', $email_vars );

			return $send_result;
		}
	}

	return true;
}