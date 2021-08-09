<?php
/**
 * Validation related functions
 *
 * All UsersWP form validation handled here.
 *
 * @since      1.0.0
 * @author     GeoDirectory Team <info@wpgeodirectory.com>
 */
class UsersWP_Validation {

    /**
     * Validates the submitted form data.
     *
     * @since       1.0.0
     * @package     userswp
     *
     * @param       array       $data           Submitted form data
     * @param       string      $type           Form type.
     * @param       array|bool  $fields         Fields applicable for validation.
     *
     * @return      array|mixed|WP_Error   Validated form data.
     */
    public function validate_fields($data, $type, $fields = false) {

        $errors = new WP_Error();

        $errors = apply_filters('uwp_validate_fields_before', $errors, $data, $type);

        $error_code = $errors->get_error_code();
        if (!empty($error_code)) {
            return $errors;
        }

        if (!$fields) {
            global $wpdb;
            $table_name = uwp_get_table_prefix() . 'uwp_form_fields';
            if ($type == 'register') {
	            if ( isset( $data['uwp_register_form_id'] ) && ! empty( $data['uwp_register_form_id'] ) ) {
		            $form_id = (int) $data['uwp_register_form_id'];
	            } else {
		            $form_id = 1;
	            }
                $fields = get_register_validate_form_fields($form_id);
            } elseif ($type == 'change') {
                $fields = get_change_validate_form_fields();
            } elseif ($type == 'account') {
                $fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $table_name . " WHERE form_type = %s AND field_type != 'fieldset' AND field_type != 'file' AND is_active = '1' AND is_register_only_field = '0' AND htmlvar_name != 'password' ORDER BY sort_order ASC", array('account')));
            } else {
                $fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $table_name . " WHERE form_type = %s AND field_type != 'fieldset' AND field_type != 'file' AND is_active = '1' ORDER BY sort_order ASC", array($type)));
            }
        }

        $validated_data = array();

        $email_field = uwp_get_custom_field_info('email','account');
        $email_extra = array();
        if (isset($email_field->extra_fields) && $email_field->extra_fields != '') {
            $email_extra = unserialize($email_field->extra_fields);
        }

        $enable_confirm_email_field = isset($email_extra['confirm_email']) ? $email_extra['confirm_email'] : '0';

        $password_field = uwp_get_custom_field_info('password','account');
        $enable_password = isset($data['password']) && $password_field->is_active ? 1 : 0;
        $password_extra = array();
        if (isset($password_field->extra_fields) && $password_field->extra_fields != '') {
            $password_extra = unserialize($password_field->extra_fields);
        }
        
        $enable_confirm_password_field = isset($password_extra['confirm_password']) ? $password_extra['confirm_password'] : '0';

        $enable_old_password = uwp_get_option('change_enable_old_password', false);

        if ($type == 'account' || $type == 'change') {
            if (!is_user_logged_in()) {
                $errors->add('not_logged_in', __('<strong>Error</strong>: Permission denied.', 'userswp'));
                return $errors;
            }
        }

        if (!empty($fields)) {
            foreach ($fields as $field) {

                if (!isset($data[$field->htmlvar_name]) && $field->is_required != 1) {
                    continue;
                }


                if ($type == 'register') {

                    if ($enable_password != '1') {
                        if ( ($field->htmlvar_name == 'password') OR ($field->htmlvar_name == 'confirm_password') ) {
                            continue;
                        }
                    }

                    if ($enable_confirm_email_field != '1') {
                        if ( $field->htmlvar_name == 'confirm_email' ) {
                            continue;
                        }
                    }
                }

                $value = isset($data[$field->htmlvar_name]) ? $data[$field->htmlvar_name] : '';
                $sanitized_value = $value;
                $sanitized = false;

                // sanitize our default fields
                switch($field->htmlvar_name) {

                    case 'uwp_register_username':
                    case 'username':
                    case 'uwp_login_username':
                    case 'uwp_reset_username':
                        $sanitized_value = sanitize_user($value);
                        $sanitized = true;
                        break;

                    case 'uwp_register_first_name':
                    case 'uwp_register_last_name':
                    case 'first_name':
                    case 'last_name':
                        $sanitized_value = sanitize_text_field($value);
                        $sanitized = true;
                        break;

                    case 'uwp_register_email':
                    case 'uwp_forgot_email':
                    case 'email':
                    case 'confirm_email':
                        $sanitized_value = sanitize_email($value);
                        $sanitized = true;
                        break;

                }

                if (!$sanitized && !empty($value)) {
                    // sanitize by field type
                    switch($field->field_type) {

                        case 'text':
                            $sanitized_value = sanitize_text_field($value);
                            break;

                        case 'checkbox':
                            $sanitized_value = sanitize_text_field($value);
                            break;

                        case 'textarea':
                            $sanitized_value = sanitize_textarea_field($value);
                            break;

                        case 'email':
                            $sanitized_value = sanitize_email($value);
                            break;

                        case 'multiselect':
                            $sanitized_value = array_map( 'sanitize_text_field', $value );
                            break;

                        case 'datepicker':
                            $sanitized_value = sanitize_text_field($value);
                            $extra_fields = unserialize($field->extra_fields);

                            if ($extra_fields['date_format'] == '')
                                $extra_fields['date_format'] = 'yy-mm-dd';

                            $date_format = $extra_fields['date_format'];

                            if (!empty($sanitized_value)) {
                                $date_value = uwp_date($sanitized_value, 'Y-m-d', $date_format);
                                $sanitized_value = strtotime($date_value);
                            }
                            break;

	                    case 'editor':
		                    $sanitized_value = wp_kses_post( strip_shortcodes( $value ) );
		                    break;

                        default:
                            $sanitized_value = sanitize_text_field($value);

                    }
                }

                if ($field->is_required == 1 && $sanitized_value == '') {
                    if (isset($GLOBALS['current_screen']) && !is_customize_preview()) {
                        //do nothing since admin edit fields can be empty
                    } else {
                        if ($field->required_msg) {
                            $errors->add('empty_'.$field->htmlvar_name,  sprintf(__('<strong>Error</strong>: %s %s', 'userswp'), $field->site_title, $field->required_msg));
                            return $errors;
                        } else {
                            $errors->add('empty_'.$field->htmlvar_name, sprintf(__('<strong>Error</strong>: %s cannot be empty.', 'userswp'), $field->site_title));
                            return $errors;
                        }
                    }
                }

                if ($field->field_type == 'email' && !empty($sanitized_value) && !is_email($sanitized_value)) {
                    $incorrect_email_error_msg = apply_filters('uwp_incorrect_email_error_msg', __('<strong>Error</strong>: The email address isn&#8217;t correct.', 'userswp'));
                    $errors->add('invalid_email', $incorrect_email_error_msg);
                    return $errors;
                }

                //register email
                if ($type == 'register' && $field->htmlvar_name == 'email' && email_exists($sanitized_value)) {
                    $errors->add('email_exists', __('<strong>Error</strong>: This email is already registered, please choose another one.', 'userswp'));
                    return $errors;
                }

                //forgot email
                if ($field->htmlvar_name == 'uwp_forgot_email' && !email_exists($sanitized_value)) {
                    $errors->add('email_exists', __('<strong>Error</strong>: This email doesn\'t exists.', 'userswp'));
                    return $errors;
                }

                $incorrect_username_error_msg = apply_filters('uwp_incorrect_username_error_msg', __('<strong>Error</strong>: This username is invalid because it uses illegal characters. Please enter a valid username.', 'userswp'));

                // Check the username for register
                if ('register' == $type && $field->htmlvar_name == 'username') {
                    if (!empty($sanitized_value) && !validate_username($sanitized_value)) {
                        $errors->add('invalid_username', $incorrect_username_error_msg);
                        return $errors;
                    }
                    if (username_exists($sanitized_value)) {
                        $errors->add('username_exists', __('<strong>Error</strong>: This username is already registered. Please choose another one.', 'userswp'));
                        return $errors;
                    }
                    $username_length = uwp_get_option( 'register_username_length');
                    $username_length = !empty($username_length) ? (int)$username_length : 4;

                    if(!empty($sanitized_value) && strlen($sanitized_value) < $username_length) {
	                    $errors->add('username_length', sprintf(__('<strong>Error</strong>: Username must be %s characters or more.', 'userswp'), $username_length));
	                    return $errors;
                    }
                }

                // check for the TOS and GDPR validation.
	            if ('register' == $type && ($field->htmlvar_name == 'register_gdpr' || $field->htmlvar_name == 'register_tos' )) {

		            if($field->htmlvar_name == 'register_gdpr'){
			            $msg = __('You must read and accept our GDPR policy.', 'userswp');
			            $is_page = uwp_get_option('register_gdpr_page', false);
		            } else {
			            $msg = __('You must accept our terms and conditions.', 'userswp');
			            $is_page = uwp_get_option('register_terms_page', false);
		            }

					if(isset($sanitized_value) && 1 != $sanitized_value && $is_page){

						if ($field->required_msg) {
							$errors->add('empty_'.$field->htmlvar_name,  __($field->required_msg, 'userswp'));
							return $errors;
						} else {
							$errors->add('empty_'.$field->htmlvar_name,  $msg);
							return $errors;
						}
					}
	            }

                // Check the username for login
                if ($type != 'account' && $field->htmlvar_name == 'username') {
                    if (!empty($sanitized_value) && !is_email($sanitized_value) && !validate_username($sanitized_value)) {
                        $errors->add('invalid_username', $incorrect_username_error_msg);
                        return $errors;
                    }
                }


                $validated_data[$field->htmlvar_name] = $sanitized_value;

            }
        }

        $error_code = $errors->get_error_code();
        if (!empty($error_code)) {
            return $errors;
        }

        if ( $type == 'change' && $enable_old_password == '1' ) {
	        $old_pass = isset($data['old_password']) ? $data['old_password'] : "";
        	//check old password
            if( empty( $old_pass ) ) {
                $errors->add( 'empty_password', __( '<strong>Error</strong>: Please enter your old password', 'userswp' ) );
                return $errors;
            }

            $user = get_user_by( 'id', get_current_user_id() );
            if ( !wp_check_password( $old_pass, $user->data->user_pass, $user->ID) ) {
                $errors->add( 'invalid_password', __( '<strong>Error</strong>: Incorrect old password', 'userswp' ) );
                return $errors;
            }

            if( $old_pass == $data['password'] ) {
                $errors->add( 'invalid_password', __( '<strong>Error</strong>: Old password and new password are same', 'userswp' ) );
                return $errors;
            }

        }

        if (($type == 'register' && $enable_confirm_email_field == '1')) {
            //check confirm email
            if( empty( $data['email'] ) ) {
                $errors->add( 'empty_email', __( '<strong>Error</strong>: Please enter your Email', 'userswp' ) );
                return $errors;
            }

            if( !isset($data['confirm_email']) || empty( $data['confirm_email'] ) ) {
                $errors->add( 'empty_confirm_email', __( '<strong>Error</strong>: Please fill Confirm Email field', 'userswp' ) );
                return $errors;
            }

            if( $data['email'] != $data['confirm_email'] ) {
                $errors->add( 'email_mismatch', __( '<strong>Error</strong>: Email and Confirm email not match', 'userswp' ) );
                return $errors;
            }

        }

        if ($type == 'change' || $type == 'reset' || $type == 'login' || ($type == 'register' && $enable_password == '1')) {
            //check password
            if( empty( $data['password'] ) ) {
                $errors->add( 'empty_password', __( 'Please enter a password', 'userswp' ) );
            }

            $password_min_length = uwp_get_option( 'register_password_min_length');
	        $password_min_length = !empty($password_min_length) ? (int)$password_min_length : 8;

	        $password_max_length = uwp_get_option( 'register_password_max_length');
	        $password_max_length = !empty($password_max_length) ? (int)$password_max_length : 15;

	        if ($type != 'login' && (strlen($data['password']) < $password_min_length || strlen($data['password']) > $password_max_length )) {
		        if(strlen($data['password']) > $password_max_length) {
			        $errors->add('pass_match', sprintf(__('<strong>Error</strong>: Password must be %s characters or less.', 'userswp'), $password_max_length));
		        } else{
			        $errors->add('pass_match', sprintf(__('<strong>Error</strong>: Password must be %s characters or more.', 'userswp'), $password_min_length));
		        }
	        }

            $validated_data['password'] = isset($data['password']) ? $data['password'] : '';
        }

        $error_code = $errors->get_error_code();
        if (!empty($error_code)) {
            return $errors;
        }

        if (($type == 'register' && $enable_password == '1') || $type == 'reset' || $type == 'change') {

            if (($type == 'register' && $enable_confirm_password_field != '1')) {
                $validated_data['password'] = $data['password'];
            } else {
                //check password
                if ($data['password'] != $data['confirm_password']) {
                    $errors->add('pass_match', __('<strong>Error</strong>: Passwords do not match.', 'userswp'));
                }

                $validated_data['password'] = isset($data['password']) ? $data['password'] : '';
            }
        }


        $error_code = $errors->get_error_code();
        if (!empty($error_code)) {
            return $errors;
        }

        return $validated_data;
    }

}