<?php
/**
 * Returns the register form fields.
 *
 * @return      array       Form fields.
 * @package     userswp
 *
 * @since       1.0.0
 */
function get_register_form_fields( $form_id = 1 ) {
	global $wpdb;
	$table_name        = uwp_get_table_prefix() . 'uwp_form_fields';
	$extras_table_name = uwp_get_table_prefix() . 'uwp_form_extras';
	$fields            = $wpdb->get_results( $wpdb->prepare( "SELECT fields.* FROM " . $table_name . " fields JOIN " . $extras_table_name . " extras ON extras.site_htmlvar_name = fields.htmlvar_name WHERE fields.form_type = %s AND fields.is_active = '1' AND fields.for_admin_use != '1' AND fields.is_register_field = '1' AND extras.form_type = 'register' AND extras.form_id=%d ORDER BY extras.sort_order ASC", array(
		'account',
		$form_id
	) ) );

	$fields = apply_filters( 'uwp_get_register_form_fields', $fields );

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

	$user_id = get_current_user_id();
	$form_id = get_user_meta( $user_id, '_uwp_register_form_id', true );

	$table_name        = uwp_get_table_prefix() . 'uwp_form_fields';
	$include_admin_use = apply_filters( 'uwp_account_include_admin_use_only_fields', false );
	if ( $include_admin_use ) {
		$fields = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $table_name . " WHERE form_type = %s AND is_active = '1' AND is_register_only_field = '0' AND htmlvar_name != 'password'" . $extra_where . " ORDER BY sort_order ASC", array( 'account' ) ) );
	} else {
		$fields = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $table_name . " WHERE form_type = %s AND is_active = '1' AND for_admin_use != '1' AND is_register_only_field = '0' AND htmlvar_name != 'password'" . $extra_where . " ORDER BY sort_order ASC", array( 'account' ) ) );
	}

	return uwp_get_account_fields_sorted( $fields, $form_id );
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

function uwp_get_register_forms() {

	$get_register_form = uwp_get_option( 'multiple_registration_forms' );

	$register_forms = array();

	if ( ! empty( $get_register_form ) && is_array( $get_register_form ) ) {
		foreach ( $get_register_form as $key => $register_form ) {
			$form_id = ! empty( $register_form['id'] ) ? $register_form['id'] : '';
			if ( ! empty( $form_id ) && $form_id > 0 ) {
				$register_forms[ $form_id ] = ! empty( $register_form['title'] ) ? $register_form['title'] : '';
			}
		}
	}

	return $register_forms;
}

function uwp_get_register_form_title( $form_id ) {

	$form_title = '';
	if ( ! empty( $form_id ) && $form_id > 0 ) {
		$get_register_form = uwp_get_option( 'multiple_registration_forms' );

		if ( ! empty( $get_register_form ) && is_array( $get_register_form ) ) {

			foreach ( $get_register_form as $key => $register_form ) {

				if ( ! empty( $register_form['id'] ) && $register_form['id'] == $form_id ) {

					$form_title = ! empty( $register_form['title'] ) ? $register_form['title'] : '';
				}
			}
		}
	}

	return $form_title;
}

function uwp_get_next_register_form_id() {

	global $wpdb;

	$extras_table_name = uwp_get_table_prefix() . 'uwp_form_extras';
	$fields            = $wpdb->get_results( "SELECT MAX(`form_id`) AS last_added FROM `$extras_table_name`" );
	$last_added        = ! empty( $fields['0']->last_added ) ? (int) $fields['0']->last_added : 1;

	$register_forms    = uwp_get_option( 'multiple_registration_forms' );
	$register_form_ids = array();
	if ( ! empty( $register_forms ) && is_array( $register_forms ) ) {
		foreach ( $register_forms as $key => $register_form ) {
			if ( ! empty( $register_form['id'] ) ) {
				$register_form_ids[] = $register_form['id'];
			}
		}
	}

	$form_max_id = ! empty( $register_form_ids ) ? max( $register_form_ids ) : 0;

	$next_form = max( $last_added, $form_max_id );
	$next_form = ! empty( $next_form ) ? $next_form + 1 : 0;

	return ! empty( $next_form ) ? $next_form : 1;
}

function uwp_get_register_form_id( $user_id ) {

	if ( empty( $user_id ) ) {
		return;
	}

	$form_id = get_user_meta( $user_id, '_uwp_register_form_id', true );

	return ! empty( $form_id ) ? (int) $form_id : 1;
}

function uwp_get_register_fields_htmlvar( $form_id = 1 ) {

	$fields = get_register_form_fields( $form_id );

	$html_var = array();

	if ( ! empty( $fields ) && is_array( $fields ) ) {

		foreach ( $fields as $key => $field ) {

			if ( ! empty( $field->htmlvar_name ) ) {

				$html_var[] = $field->htmlvar_name;
			}
		}
	}

	return $html_var;
}

function uwp_get_account_fields_sorted( $fields = array(), $form_id = 1 ) {

	if ( empty( $fields ) || ! is_array( $fields ) ) {
		return;
	}

	$accept_fields = apply_filters( 'uwp_accept_account_fields', array(
		'first_name',
		'last_name',
		'username',
		'email',
		'display_name',
		'bio'
	) );

	$excluded_fields = uwp_get_excluded_fields();

	$register_fields = uwp_get_register_fields_htmlvar( $form_id );

	$register_fields = array_merge( $register_fields, $accept_fields );
	$register_fields = array_unique( $register_fields );

	$register_fields = array_diff( $register_fields, $excluded_fields );

	foreach ( $fields as $key => $field ) {

		$htmlvar_name = ! empty( $field->htmlvar_name ) ? $field->htmlvar_name : '';

		if ( ! empty( $htmlvar_name ) && ! empty( $register_fields ) && ! in_array( $htmlvar_name, $register_fields ) ) {

			unset( $fields[ $key ] );
		}

		$fields = array_values( $fields );
	}

	return $fields;
}