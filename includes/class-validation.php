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
     * @return      array|mixed|void|WP_Error   Validated form data.
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
                if (isset($data["uwp_role_id"])) {
                    $role_id = (int) strip_tags(esc_sql($data["uwp_role_id"]));
                } else {
                    $role_id = 0;
                }
                $fields = get_register_validate_form_fields($role_id);
            } elseif ($type == 'change') {
                $fields = get_change_validate_form_fields();
            } elseif ($type == 'account') {
                $fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $table_name . " WHERE form_type = %s AND field_type != 'fieldset' AND field_type != 'file' AND is_active = '1' AND is_register_only_field = '0' AND htmlvar_name != 'uwp_account_password' ORDER BY sort_order ASC", array('account')));
            } else {
                $fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $table_name . " WHERE form_type = %s AND field_type != 'fieldset' AND field_type != 'file' AND is_active = '1' ORDER BY sort_order ASC", array($type)));
            }
        }

        $validated_data = array();

        $email_field = uwp_get_custom_field_info('uwp_account_email');
        $email_extra = array();
        if (isset($email_field->extra_fields) && $email_field->extra_fields != '') {
            $email_extra = unserialize($email_field->extra_fields);
        }
        $enable_confirm_email_field = isset($email_extra['confirm_email']) ? $email_extra['confirm_email'] : '0';

        $password_field = uwp_get_custom_field_info('uwp_account_password');
        $enable_password = $password_field->is_active;
        $password_extra = array();
        if (isset($password_field->extra_fields) && $password_field->extra_fields != '') {
            $password_extra = unserialize($password_field->extra_fields);
        }
        $enable_confirm_password_field = isset($password_extra['confirm_password']) ? $password_extra['confirm_password'] : '0';

        $enable_old_password = uwp_get_option('change_enable_old_password', false);

        if ($type == 'account' || $type == 'change') {
            if (!is_user_logged_in()) {
                $errors->add('not_logged_in', __('<strong>Error</strong>: Permission denied.', 'userswp'));
            }
        }

        if (!empty($fields)) {
            foreach ($fields as $field) {

                if (!isset($data[$field->htmlvar_name]) && $field->is_required != 1) {
                    continue;
                }


                if ($type == 'register') {

                    if ($enable_password != '1') {
                        if ( ($field->htmlvar_name == 'uwp_account_password') OR ($field->htmlvar_name == 'uwp_account_confirm_password') ) {
                            continue;
                        }
                    }

                    if ($enable_confirm_email_field != '1') {
                        if ( $field->htmlvar_name == 'uwp_account_confirm_email' ) {
                            continue;
                        }
                    }
                }


                if (!isset($data[$field->htmlvar_name]) && $field->is_required == 1) {
                    if (is_admin()) {
                        //do nothing since admin edit fields can be empty
                    } else {
                        if ($field->required_msg) {
                            $errors->add('empty_'.$field->htmlvar_name,  __('<strong>Error</strong>: '.$field->site_title.' '.$field->required_msg, 'userswp'));
                        } else {
                            $errors->add('empty_'.$field->htmlvar_name, __('<strong>Error</strong>: '.$field->site_title.' cannot be empty.', 'userswp'));
                        }
                    }
                }

                $error_code = $errors->get_error_code();
                if (!empty($error_code)) {
                    return $errors;
                }


                $value = isset($data[$field->htmlvar_name]) ? $data[$field->htmlvar_name] : '';
                $sanitized_value = $value;

                if ($field->field_type == 'password') {
                    continue;
                }

                $sanitized = false;

                // sanitize our default fields
                switch($field->htmlvar_name) {

                    case 'uwp_register_username':
                    case 'uwp_account_username':
                    case 'uwp_login_username':
                    case 'uwp_reset_username':
                        $sanitized_value = sanitize_user($value);
                        $sanitized = true;
                        break;

                    case 'uwp_register_first_name':
                    case 'uwp_register_last_name':
                    case 'uwp_account_first_name':
                    case 'uwp_account_last_name':
                        $sanitized_value = sanitize_text_field($value);
                        $sanitized = true;
                        break;

                    case 'uwp_register_email':
                    case 'uwp_forgot_email':
                    case 'uwp_account_email':
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
                            
                        case 'textarea':
                            $sanitized_value = sanitize_text_field($value);
                            break;

                        case 'checkbox':
                            $sanitized_value = sanitize_text_field($value);
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

                        default:
                            $sanitized_value = sanitize_text_field($value);

                    }
                }


                if ($field->is_required == 1 && $sanitized_value == '') {
                    if (is_admin()) {
                        //do nothing since admin edit fields can be empty
                    } else {
                        if ($field->required_msg) {
                            $errors->add('empty_'.$field->htmlvar_name,  __('<strong>Error</strong>: '.$field->site_title.' '.$field->required_msg, 'userswp'));
                        } else {
                            $errors->add('empty_'.$field->htmlvar_name, __('<strong>Error</strong>: '.$field->site_title.' cannot be empty.', 'userswp'));
                        }
                    }
                }

                if ($field->field_type == 'email' && !empty($sanitized_value) && !is_email($sanitized_value)) {
                    $incorrect_email_error_msg = apply_filters('uwp_incorrect_email_error_msg', __('<strong>Error</strong>: The email address isn&#8217;t correct.', 'userswp'));
                    $errors->add('invalid_email', $incorrect_email_error_msg);
                }

                //register email
                if ($type == 'register' && $field->htmlvar_name == 'uwp_account_email' && email_exists($sanitized_value)) {
                    $errors->add('email_exists', __('<strong>Error</strong>: This email is already registered, please choose another one.', 'userswp'));
                }

                //forgot email
                if ($field->htmlvar_name == 'uwp_forgot_email' && !email_exists($sanitized_value)) {
                    $errors->add('email_exists', __('<strong>Error</strong>: This email doesn\'t exists.', 'userswp'));
                }

                $incorrect_username_error_msg = apply_filters('uwp_incorrect_username_error_msg', __('<strong>Error</strong>: This username is invalid because it uses illegal characters. Please enter a valid username.', 'userswp'));

                // Check the username for register
                if ($field->htmlvar_name == 'uwp_account_username') {
                    if (!is_admin()) {
                        if (!validate_username($sanitized_value)) {
                            $errors->add('invalid_username', $incorrect_username_error_msg);
                        }
                        if (username_exists($sanitized_value)) {
                            $errors->add('username_exists', __('<strong>Error</strong>: This username is already registered. Please choose another one.', 'userswp'));
                        }
                    }
                }

                // Check the username for login
                if ($field->htmlvar_name == 'uwp_login_username') {
                    if (!validate_username($sanitized_value)) {
                        $errors->add('invalid_username', $incorrect_username_error_msg);
                    }
                }


                $validated_data[$field->htmlvar_name] = $sanitized_value;

            }
        }

        $error_code = $errors->get_error_code();
        if (!empty($error_code)) {
            return $errors;
        }

        if ($type == 'login') {
            $password_type = 'login';
        } elseif ($type == 'reset') {
            $password_type = 'reset';
        } elseif ($type == 'change') {
            $password_type = 'change';
        } else {
            $password_type = 'account';
        }

        if (($type == 'change' && $enable_old_password == '1')) {
            //check old password
            if( empty( $data['uwp_'.$password_type.'_old_password'] ) ) {
                $errors->add( 'empty_password', __( '<strong>Error</strong>: Please enter your old password', 'userswp' ) );
            }

            $error_code = $errors->get_error_code();
            if (!empty($error_code)) {
                return $errors;
            }

            $pass = $data['uwp_'.$password_type.'_old_password'];
            $user = get_user_by( 'id', get_current_user_id() );
            if ( !wp_check_password( $pass, $user->data->user_pass, $user->ID) ) {
                $errors->add( 'invalid_password', __( '<strong>Error</strong>: Incorrect old password', 'userswp' ) );
            }

            $error_code = $errors->get_error_code();
            if (!empty($error_code)) {
                return $errors;
            }

            if( $data['uwp_'.$password_type.'_old_password'] == $data['uwp_'.$password_type.'_password'] ) {
                $errors->add( 'invalid_password', __( '<strong>Error</strong>: Old password and new password are same', 'userswp' ) );
            }

            $error_code = $errors->get_error_code();
            if (!empty($error_code)) {
                return $errors;
            }

        }

        if (($type == 'register' && $enable_confirm_email_field == '1')) {
            //check confirm email
            if( empty( $data['uwp_account_email'] ) ) {
                $errors->add( 'empty_email', __( '<strong>Error</strong>: Please enter your Email', 'userswp' ) );
            }

            $error_code = $errors->get_error_code();
            if (!empty($error_code)) {
                return $errors;
            }

            if( !isset($data['uwp_account_confirm_email']) || empty( $data['uwp_account_confirm_email'] ) ) {
                $errors->add( 'empty_confirm_email', __( '<strong>Error</strong>: Please fill Confirm Email field', 'userswp' ) );
            }

            $error_code = $errors->get_error_code();
            if (!empty($error_code)) {
                return $errors;
            }

            if( $data['uwp_account_email'] != $data['uwp_account_confirm_email'] ) {
                $errors->add( 'email_mismatch', __( '<strong>Error</strong>: Email and Confirm email not match', 'userswp' ) );
            }

            $error_code = $errors->get_error_code();
            if (!empty($error_code)) {
                return $errors;
            }

        }

        if ($type == 'change' || $type == 'reset' || $type == 'login' || ($type == 'register' && $enable_password == '1')) {
            //check password
            if( empty( $data['uwp_'.$password_type.'_password'] ) ) {
                $errors->add( 'empty_password', __( 'Please enter a password', 'userswp' ) );
            }

            if ($type != 'login' && strlen($data['uwp_'.$password_type.'_password']) < 7) {
                $errors->add('pass_match', __('ERROR: Password must be 7 characters or more.', 'userswp'));
            }

            $validated_data['password'] = $data['uwp_'.$password_type.'_password'];
        }

        $error_code = $errors->get_error_code();
        if (!empty($error_code)) {
            return $errors;
        }

        if (($type == 'register' && $enable_password == '1') || $type == 'reset' || $type == 'change') {

            if (($type == 'register' && $enable_confirm_password_field != '1')) {
                $validated_data['password'] = $data['uwp_'.$password_type.'_password'];
            } else {
                //check password
                if ($data['uwp_'.$password_type.'_password'] != $data['uwp_'.$password_type.'_confirm_password']) {
                    $errors->add('pass_match', __('ERROR: Passwords do not match.', 'userswp'));
                }

                $validated_data['password'] = $data['uwp_'.$password_type.'_password'];
            }
        }


        $error_code = $errors->get_error_code();
        if (!empty($error_code)) {
            return $errors;
        }

        return $validated_data;
    }

}
