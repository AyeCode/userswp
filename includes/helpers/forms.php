<?php
/**
 * Returns the register form fields.
 *
 * @return      array       Form fields.
 * @package     userswp
 *
 * @since       1.0.0
 */
function get_register_form_fields($form_id = 1) {
	global $wpdb;
	$table_name = uwp_get_table_prefix() . 'uwp_form_fields';
	$extras_table_name = uwp_get_table_prefix() . 'uwp_form_extras';
	$fields = $wpdb->get_results($wpdb->prepare("SELECT fields.* FROM " . $table_name . " fields JOIN " . $extras_table_name . " extras ON extras.site_htmlvar_name = fields.htmlvar_name WHERE fields.form_type = %s AND fields.is_active = '1' AND fields.for_admin_use != '1' AND fields.is_register_field = '1' AND extras.form_type = 'register' AND extras.form_id=%d AND fields.form_id=%d ORDER BY extras.sort_order ASC", array('account', $form_id, $form_id)));
	$fields = apply_filters('uwp_get_register_form_fields', $fields, $form_id);
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
 * @param int $form_id  Form ID Default 1.
 *
 * @return      array                   Validate-able fields.
 * @since       1.0.0
 * @package     userswp
 *
 */
function get_register_validate_form_fields( $form_id = 1 ) {
	global $wpdb;
	$table_name        = uwp_get_table_prefix() . 'uwp_form_fields';
	$extras_table_name = uwp_get_table_prefix() . 'uwp_form_extras';
	$fields = $wpdb->get_results( $wpdb->prepare( "SELECT fields.* FROM " . $table_name . " fields JOIN " . $extras_table_name . " extras ON extras.site_htmlvar_name = fields.htmlvar_name WHERE fields.form_type = %s AND fields.field_type != 'fieldset' AND fields.field_type != 'file' AND fields.is_active = '1' AND fields.for_admin_use != '1' AND fields.is_register_field = '1' AND fields.form_id = %s ORDER BY extras.sort_order ASC", array( 'account', $form_id ) ) );
	$fields = apply_filters( 'uwp_get_register_validate_form_fields', $fields, $form_id );

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
	$user_id = get_current_user_id();
	if($user_id && 1 == get_user_meta($user_id, 'is_uwp_social_login_no_password', true)){
		$enable_old_password = 0;
	}
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

	$form_id = uwp_get_register_form_id( get_current_user_id() );
	$table_name        = uwp_get_table_prefix() . 'uwp_form_fields';
	$include_admin_use = apply_filters( 'uwp_account_include_admin_use_only_fields', false );
	if ( $include_admin_use ) {
		$fields = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $table_name . " WHERE form_type = %s AND is_active = '1' AND is_register_only_field = '0' AND htmlvar_name != 'password' AND form_id = %s" . $extra_where . " ORDER BY sort_order ASC", array( 'account', $form_id ) ) );
	} else {
		$fields = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $table_name . " WHERE form_type = %s AND is_active = '1' AND for_admin_use != '1' AND is_register_only_field = '0' AND htmlvar_name != 'password' AND form_id = %s" . $extra_where . " ORDER BY sort_order ASC", array( 'account', $form_id ) ) );
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
	$user_id = get_current_user_id();
	if($user_id && 1 == get_user_meta($user_id, 'is_uwp_social_login_no_password', true)){
		$enable_old_password = 0;
	}
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

	return apply_filters( 'uwp_get_form_label', stripslashes( esc_attr( $label ) ), $field );
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
	global $wpdb, $custom_field_info;
	$table_name = uwp_get_table_prefix() . 'uwp_form_fields';
	$cache_key = $htmlvar_name . '_' . $form_type;

	// Return cached field data.
	if ( isset( $custom_field_info[ $cache_key ] ) ) {
		return $custom_field_info[ $cache_key ];
	}

	if ( $form_type ) {
		$field = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . $table_name . " WHERE htmlvar_name = %s AND form_type = %s", array(
			$htmlvar_name,
			$form_type
		) ) );
	} else {
		$field = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . $table_name . " WHERE htmlvar_name = %s", array( $htmlvar_name ) ) );
	}
	
	// Cache the field data.
	$custom_field_info[ $cache_key ] = $field;
	
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

/**
 * Check font awesome icon or not.
 *
 * @param string $icon Font awesome icon.
 * @return bool True if font awesome icon.
 */
function uwp_is_fa_icon( $icon ) {
	$return = false;
	if ( $icon != '' ) {
		$fa_icon = trim( $icon );
		if ( strpos( $fa_icon, 'fa fa-' ) === 0 || strpos( $fa_icon, 'fas fa-' ) === 0 || strpos( $fa_icon, 'far fa-' ) === 0 || strpos( $fa_icon, 'fab fa-' ) === 0 || strpos( $fa_icon, 'fa-' ) === 0 ) {
			$return = true;
		}
	}
	return apply_filters( 'uwp_is_fa_icon', $return, $icon  );
}

/**
 * Check icon url.
 *
 * @param string $icon Icon url.
 * @return bool True if icon url.
 */
function uwp_is_icon_url( $icon ) {
	$return = false;
	if ( $icon != '' ) {
		$icon = trim( $icon );
		if ( strpos( $icon, 'http://' ) === 0 || strpos( $icon, 'https://' ) === 0 ) {
			$return = true;
		}
	}
	return apply_filters( 'uwp_is_icon_url', $return, $icon  );
}

/**
 * Get the field icon.
 *
 * @since       1.0.12
 * @package     userswp
 *
 * @param       string   $value   Field icon value.
 * @return      string       Field icon element.
 */
function uwp_get_field_icon( $value ) {
	$field_icon = $value;

	if ( ! empty( $value ) ) {
		if (strpos($value, 'http') === 0) {
			$field_icon = '<span class="uwp_field_icon" style="background: url(' . esc_url( $value ) . ') no-repeat left center;padding-left:14px;background-size:100% auto;margin-right:5px"></span>';
		} else {
			$field_icon = '<i class="uwp_field_icon ' . esc_attr( $value ) . '"></i>';
		}
	}

	return apply_filters( 'uwp_get_field_icon', $field_icon, $value );
}

function uwp_get_registration_form_actions(){
	$registration_options = array(
		'auto_approve' =>  __('Auto Approve', 'userswp'),
		'auto_approve_login' =>  __('Auto Approve + Auto Login', 'userswp'),
		'require_email_activation' =>  __('Require Email Activation', 'userswp'),
	);

	return apply_filters('uwp_registration_status_options', $registration_options);
}

function uwp_get_register_forms_dropdown_options($include_forms = array()) {

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

	if(!empty($register_forms) && isset($include_forms) && count($include_forms) > 0){
		foreach($register_forms as $form_id => $title){
			if(!in_array($form_id, $include_forms)){
				unset($register_forms[$form_id]);
			}
		}
	}

	return $register_forms;
}

function uwp_get_register_form_by( $form_id, $type = 'title' ) {

	$form_title = '';
	if ( ! empty( $form_id ) && $form_id > 0 ) {
		$get_register_form = uwp_get_option( 'multiple_registration_forms' );

		if ( ! empty( $get_register_form ) && is_array( $get_register_form ) ) {

			foreach ( $get_register_form as $key => $register_form ) {

				if ( ! empty( $register_form['id'] ) && $register_form['id'] == $form_id ) {

					$form_title = ! empty( $register_form[$type] ) ? $register_form[$type] : '';
				}
			}
		}
	}

	return $form_title;
}

function uwp_get_form_id_by_type($form_type){
	$form_id = 1;
	if ( isset($form_type) && ! empty( $form_type ) ) {
		$get_register_form = uwp_get_option( 'multiple_registration_forms' );

		if ( ! empty( $get_register_form ) && is_array( $get_register_form ) ) {

			foreach ( $get_register_form as $key => $register_form ) {

				if ( ! empty( $register_form['title'] ) && $form_type == sanitize_title_with_dashes($register_form['title']) ) {

					$form_id = $register_form['id'];
				}
			}
		}
	}

	return $form_id;
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
		return 1;
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

function uwp_get_unique_custom_fields($fields, $key = 'htmlvar_name'){
	if(empty($fields)){
		return array();
	}

	$temp_array = array();
	$i = 0;
	$key_array = array();

	foreach($fields as $field) {
		if(is_object($field)){
			$val = $field->$key;
		} else {
			$val = $field[$key];
		}

		if (!in_array($val, $key_array)) {
			$key_array[$i] = $val;
			$temp_array[$val] = $field;
		}
		$i++;
	}

	return $temp_array;
}

function uwp_get_default_form_data(){
	$fields = array();$i = 1;
	$user_role = get_option('default_role');

	$account_fields = UsersWP_Activator::uwp_default_custom_fields_account();
	if(!empty($account_fields)){
		foreach ($account_fields as $account_field){
			$fields[$account_field['htmlvar_name']] = $i;
			$i++;
		}
	}

	$reg_fields = get_register_form_fields();

	if ( ! empty( $reg_fields ) && is_array( $reg_fields ) ) {
		foreach ( $reg_fields as $key => $field ) {
			if ( isset( $field->htmlvar_name ) && ! empty( $field->htmlvar_name ) && $field->htmlvar_name == 'user_role' && ! empty( $field->option_values ) ) {
				$user_role = $field->option_values;
				$obj = new UsersWP_Form_Builder();
				$obj->admin_form_field_delete( $field->id, true, 1 );
			}
		}
	}

	$data = array(
		array(
			'id' => 1,
			'title' => __('Default','userswp'),
			'user_role' => $user_role,
			'reg_action' => uwp_get_option( 'uwp_registration_action', 'auto_approve' ),
			'redirect_to' => (int) uwp_get_option( 'register_redirect_to', - 1 ),
			'custom_url' => uwp_get_option( 'register_redirect_custom_url', home_url() ),
			'gdpr_page' => (int) uwp_get_option('register_gdpr_page', false),
			'tos_page' => (int) uwp_get_option('register_terms_page', false),
			'fields' => $fields
		)
	);

	return $data;
}

function uwp_get_upload_image_size($type = "avatar"){

	if ( $type == 'avatar' ) {
		$width = uwp_get_option( 'profile_avatar_width', 150 );
		$width = empty($width) ? 150 : $width;
		$value['width'] = apply_filters( 'uwp_avatar_image_width', $width );

		$height = uwp_get_option( 'profile_avatar_height', 150 );
		$height = empty($height) ? 150 : $height;
		$value['height'] = apply_filters( 'uwp_avatar_image_height', $height );
	} else {
		$width = uwp_get_option( 'profile_banner_width', 1000 );
		$width = empty($width) ? 1000 : $width;
		$value['width']  = apply_filters( 'uwp_banner_image_width', $width );

		$height = uwp_get_option( 'profile_banner_height', 300 );
		$height = empty($height) ? 300 : $height;
		$value['height'] = apply_filters( 'uwp_banner_image_height', $height );
	}

	return $value;
}