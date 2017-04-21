<?php
/**
 * Form related functions
 *
 * This class defines all code necessary to handle UsersWP forms like login. register etc.
 *
 * @link       http://wpgeodirectory.com
 * @since      1.0.0
 *
 * @package    Users_WP
 * @subpackage Users_WP/includes
 */

/**
 * Define the templates functionality.
 *
 * @since      1.0.0
 * @package    Users_WP
 * @subpackage Users_WP/includes
 * @author     GeoDirectory Team <info@wpgeodirectory.com>
 */
class Users_WP_Forms {

    public function init_notices() {
        global $uwp_notices;
        $uwp_notices = array();
    }
    
    public function handler()
    {
        global $uwp_notices;

        ob_start();

        $errors = null;
        $message = null;
        $redirect = false;
        $processed = false;

        $login_page = uwp_get_option('login_page', false);
        if ($login_page) {
            $login_page_url = get_permalink($login_page);
        } else {
            $login_page_url = wp_login_url();
        }

        $redirect_page_id = uwp_get_option('login_redirect_to', '');
        if (empty($redirect_page_id)) {
            $redirect_to = home_url('/');
        } else {
            $redirect_to = get_permalink($redirect_page_id);
        }
        $redirect_to = apply_filters('uwp_login_redirect', $redirect_to);

        if (isset($_POST['uwp_register_submit'])) {
            $auto_login = uwp_get_option('uwp_registration_action', false);
            $errors = $this->process_register($_POST, $_FILES);
            if (!is_wp_error($errors)) {
                $message = $errors;
            }
            if ($auto_login == 'auto_approve_login') {
                $reg_redirect_page_id = uwp_get_option('register_redirect_to', '');
                if (empty($reg_redirect_page_id)) {
                    $reg_redirect_to = $redirect_to;
                } else {
                    $reg_redirect_to = get_permalink($reg_redirect_page_id);
                }
                $redirect = apply_filters('uwp_register_redirect', $reg_redirect_to);
            }
            $processed = true;
        } elseif (isset($_POST['uwp_login_submit'])) {
            $errors = $this->process_login($_POST);
            if (isset($_POST['redirect_to'])) {
                $redirect_to = strip_tags(esc_sql($_POST['redirect_to']));
            }
            $redirect = $redirect_to;
            $processed = true;
        } elseif (isset($_POST['uwp_forgot_submit'])) {
            $errors = $this->process_forgot($_POST);
            $message = __('Please check your email.', 'userswp');
            $processed = true;
        } elseif (isset($_POST['uwp_change_submit'])) {
            $errors = $this->process_change($_POST);
            $message = __('Password changed successfully', 'userswp');
            $processed = true;
        } elseif (isset($_POST['uwp_reset_submit'])) {
            $errors = $this->process_reset($_POST);
            $message = sprintf(__('Password updated successfully. Please <a href="%s">login</a> with your new password', 'userswp'), $login_page_url);
            $processed = true;
        } elseif (isset($_POST['uwp_account_submit'])) {
            $errors = $this->process_account($_POST, $_FILES);
            $message = __('Account updated successfully.', 'userswp');
            $processed = true;
        } elseif (isset($_POST['uwp_avatar_submit'])) {
            $errors = $this->process_upload_submit($_POST, $_FILES, 'avatar');
            if (!is_wp_error($errors)) {
                $redirect = $errors;
            }
            $message = __('Avatar cropped successfully.', 'userswp');
            $processed = true;
        } elseif (isset($_POST['uwp_banner_submit'])) {
            $errors = $this->process_upload_submit($_POST, $_FILES, 'banner');
            if (!is_wp_error($errors)) {
                $redirect = $errors;
            }
            $message = __('Banner cropped successfully.', 'userswp');
            $processed = true;
        } elseif (isset($_POST['uwp_avatar_crop'])) {
            $errors = $this->process_image_crop($_POST, 'avatar');
            if (!is_wp_error($errors)) {
                $redirect = $errors;
            }
            $message = __('Avatar cropped successfully.', 'userswp');
            $processed = true;
        } elseif (isset($_POST['uwp_banner_crop'])) {
            $errors = $this->process_image_crop($_POST, 'banner');
            if (!is_wp_error($errors)) {
                $redirect = $errors;
            }
            $message = __('Banner cropped successfully.', 'userswp');
            $processed = true;
        }

        if ($processed) {
            if (is_wp_error($errors)) {
                echo '<div class="uwp-alert-error text-center">';
                echo $errors->get_error_message();
                echo '</div>';
            } else {
                if ($redirect) {
                    wp_redirect($redirect);
                    exit();
                } else {
                    echo '<div class="uwp-alert-success text-center">';
                    echo $message;
                    echo '</div>';
                }
            }
        }

        $uwp_notices[] = ob_get_contents();
        ob_end_clean();

    }

    public function display_notices($type) {
        global $uwp_notices;

        if (is_array($uwp_notices)) {
            foreach ($uwp_notices as $notice) {
                if (!empty($notice)) {
                    echo $notice;
                }
            }

        }

        if ($type == 'change') {
            $user_id = get_current_user_id();
            $password_nag = get_user_option('default_password_nag', $user_id);

            if ($password_nag) {
                $change_page = uwp_get_option('change_page', false);
                $remove_nag_url = add_query_arg('uwp_remove_nag', 'yes', get_permalink($change_page));

                if (isset($_GET['uwp_remove_nag']) && $_GET['uwp_remove_nag'] == 'yes') {
                    delete_user_meta( $user_id, 'default_password_nag' );
                    $message = sprintf(__('We have removed the system generated password warning for you. From this point forward you can continue to access our site as usual. To go to home page, <a href="%s">click here</a>.', 'userswp'), home_url('/'));
                    echo '<div class="uwp-alert-success text-center">';
                    echo $message;
                    echo '</div>';
                } else {
                    $message = sprintf(__('<strong>Warning</strong>: You seems like you are using a system generated password. Please change the password in this page. If this is not a problem for you, you can remove this warning by <a href="%s">clicking here</a>.', 'userswp'), $remove_nag_url);
                    echo '<div class="uwp-alert-warning text-center">';
                    echo $message;
                    echo '</div>';
                }
            }
        }
    }


    public function process_register($data = array(), $files = array()) {

        $errors = new WP_Error();

        if( ! isset( $data['uwp_register_nonce'] ) || ! wp_verify_nonce( $data['uwp_register_nonce'], 'uwp-register-nonce' ) ) {
            return false;
        }

        if (!get_option('users_can_register')) {
            $errors->add('register_disabled', __('<strong>ERROR</strong>: User registration is currently not allowed.', 'userswp'));
            return $errors;
        }

        $reg_terms_page_id = uwp_get_option('register_terms_page', '');
        $reg_terms_page_id = apply_filters('uwp_reg_terms_page_id', $reg_terms_page_id);
        if (!empty($reg_terms_page_id)) {
            if (!isset($data['agree_terms']) || $data['agree_terms'] != 'yes') {
                $errors->add('accept_tos', __('<strong>ERROR</strong>: You must accept our terms and conditions.', 'userswp'));
                return $errors;
            }
        }

        do_action('uwp_before_validate', 'register');

        $result = uwp_validate_fields($data, 'register');
        
        $result = apply_filters('uwp_validate_result', $result, 'register');

        if (is_wp_error($result)) {
            return $result;
        }

        $uploads_result = uwp_validate_uploads($files, 'register');

        if (is_wp_error($uploads_result)) {
            return $uploads_result;
        }

        do_action('uwp_after_validate', 'register');

        $result = array_merge( $result, $uploads_result );

        if ($errors->get_error_code())
            return $errors;

        if (isset($result['password']) && !empty($result['password'])) {
            $password = $result['password'];
            $generated_password = false;
        } else {
            $password = wp_generate_password();
            $generated_password = true;
        }

        $first_name = "";
        if (isset($result['uwp_account_first_name']) && !empty($result['uwp_account_first_name'])) {
            $first_name = $result['uwp_account_first_name'];
        }

        $last_name = "";
        if (isset($result['uwp_account_last_name']) && !empty($result['uwp_account_last_name'])) {
            $last_name = $result['uwp_account_last_name'];
        }

        if (isset($result['uwp_account_display_name']) && !empty($result['uwp_account_display_name'])) {
            $display_name = $result['uwp_account_display_name'];
        } else {
            if (!empty($first_name) || !empty($last_name)) {
                $display_name = $first_name . ' ' . $last_name;
            } else {
                $display_name = $result['uwp_account_username'];
            }
        }

        $description = "";
        if (isset($result['uwp_account_bio']) && !empty($result['uwp_account_bio'])) {
            $description = $result['uwp_account_bio'];
        }


        $args = array(
            'user_login'   => $result['uwp_account_username'],
            'user_email'   => $result['uwp_account_email'],
            'user_pass'    => $password,
            'display_name' => $display_name,
            'first_name'   => $first_name,
            'last_name'    => $last_name,
            'description'  => $description
        );

        $user_id = wp_insert_user( $args );

        if (!$user_id) {
            $errors->add('registerfail', sprintf(__('<strong>Error</strong>: Something went wrong.', 'userswp'), get_option('admin_email')));
            return $errors;
        }

        $result = apply_filters('uwp_before_extra_fields_save', $result, 'register', $user_id);

        $save_result = $this->uwp_save_user_extra_fields($user_id, $result, 'register');

        $save_result = apply_filters('uwp_after_extra_fields_save', $save_result, $result, 'register', $user_id);

        if (is_wp_error($save_result)) {
            return $save_result;
        }

        if (!$save_result) {
            $errors->add('something_wrong', __('<strong>Error</strong>: Something went wrong. Please contact site admin.', 'userswp'));
        }

        if ($errors->get_error_code())
            return $errors;

        if ($generated_password) {
            update_user_meta($user_id, 'default_password_nag', true); //Set up the Password change nag.
            $message_pass = $password;
        } else {
            $message_pass = __("Password you entered", 'userswp');
        }


        $reg_action = uwp_get_option('uwp_registration_action', false);

        if ($reg_action == 'require_email_activation' && !$generated_password) {
            global $wpdb;
            $key = wp_generate_password( 20, false );
            $user_data = get_user_by('id', $user_id);
            do_action( 'uwp_activation_key', $user_data->user_login, $key );

            if ( empty( $wp_hasher ) ) {
                require_once ABSPATH . 'wp-includes/class-phpass.php';
                $wp_hasher = new PasswordHash( 8, true );
            }
            $hashed = $wp_hasher->HashPassword( $key );
            $wpdb->update( $wpdb->users, array( 'user_activation_key' => time().":".$hashed ), array( 'user_login' => $user_data->user_login ) );
            update_user_meta( $user_id, 'uwp_mod', 'email_unconfirmed' );
            $message = __('To activate your account, visit the following address:', 'userswp') . "\r\n\r\n";
            $act_url = add_query_arg(
                array(
                    'uwp_activate' => 'yes',
                    'key' => $key,
                    'login' => $user_data->user_login
                ),
                site_url()
            );
            
            $message .= $act_url . "\r\n";
            
            $login_details = __('<p><b>' . __('Please activate your account :', 'userswp') . '</b></p>
            <p>' . $message . '</p>');

            $send_result = $this->uwp_send_email( 'activate', $user_id, $login_details );
            if (!$send_result) {
                $errors->add('something_wrong', __('<strong>Error</strong>: Something went wrong when sending email. Please contact site admin.', 'userswp'));
            }
        } else {
            $login_details = __('<p><b>' . __('Your login Information :', 'userswp') . '</b></p>
            <p>' . __('Username:', 'userswp') . ' ' . $result['uwp_account_username'] . '</p>
            <p>' . __('Password:', 'userswp') . ' ' . $message_pass . '</p>');

            $send_result = $this->uwp_send_email( 'register', $user_id, $login_details );
            if (!$send_result) {
                $errors->add('something_wrong', __('<strong>Error</strong>: Something went wrong when sending email. Please contact site admin.', 'userswp'));
            }
        }


        if ($errors->get_error_code())
            return $errors;


        $reg_action = uwp_get_option('uwp_registration_action', false);

        if ($reg_action == 'auto_approve_login') {
            $res = wp_signon(
                array(
                    'user_login' => $result['uwp_account_username'],
                    'user_password' => $password,
                    'remember' => false
                ),
                false
            );

            if (is_wp_error($res)) {
                $errors->add('invalid_userorpass', __('<strong>Error</strong>: Invalid username or Password.', 'userswp'));
                return $errors;
            } else {
                $reg_redirect_page_id = uwp_get_option('register_redirect_to', '');
                if (empty($reg_redirect_page_id)) {
                    $reg_redirect_to = home_url('/');
                } else {
                    $reg_redirect_to = get_permalink($reg_redirect_page_id);
                }
                $redirect = apply_filters('uwp_register_redirect', $reg_redirect_to);
                wp_redirect($redirect);
                exit();
            }
        } else {
            if ($reg_action == 'require_email_activation') {
                return __('An email has been sent to your registered email address. Please click the activation link to proceed.', 'userswp');
            } else {
                $login_page = uwp_get_option('login_page', false);
                if ($login_page) {
                    $login_page_url = get_permalink($login_page);
                } else {
                    $login_page_url = wp_login_url();
                }
                if ($generated_password) {
                    return sprintf(__('Account registered successfully. A password has been generated and mailed to your registered Email ID. Please login <a href="%s">here</a>.', 'userswp'), $login_page_url);
                } else {
                    return sprintf(__('Account registered successfully. Please login <a href="%s">here</a>', 'userswp'), $login_page_url);
                }
            }
        }


    }

    public function process_login($data) {

        $errors = new WP_Error();

        if( ! isset( $data['uwp_login_nonce'] ) || ! wp_verify_nonce( $data['uwp_login_nonce'], 'uwp-login-nonce' ) ) {
            return false;
        }

        do_action('uwp_before_validate', 'login');

        $result = uwp_validate_fields($data, 'login');

        $result = apply_filters('uwp_validate_result', $result, 'login');

        if (is_wp_error($result)) {
            return $result;
        }

        do_action('uwp_after_validate', 'login');

        if (isset($data['remember_me']) && $data['remember_me'] == 'forever') {
            $remember_me = true;
        } else {
            $remember_me = false;
        }

        $res = wp_signon(
            array(
                'user_login' => $result['uwp_login_username'],
                'user_password' => $result['password'],
                'remember' => $remember_me
            ),
            false
        );

        if (is_wp_error($res)) {
            $errors->add('invalid_userorpass', __('<strong>Error</strong>: Invalid username or Password.', 'userswp'));
            return $errors;
        } else {
            $redirect_page_id = uwp_get_option('login_redirect_to', '');
            if (empty($redirect_page_id)) {
                $redirect_to = home_url('/');
            } else {
                $redirect_to = get_permalink($redirect_page_id);
            }

            if (isset($data['redirect_to'])) {
                $redirect_to = strip_tags(esc_sql($data['redirect_to']));
            }

            $redirect_to = apply_filters('uwp_login_redirect', $redirect_to);
            wp_redirect($redirect_to);
            exit();
        }
    }

    public function process_forgot($data) {

        $errors = new WP_Error();

        if( ! isset( $data['uwp_forgot_nonce'] ) || ! wp_verify_nonce( $data['uwp_forgot_nonce'], 'uwp-forgot-nonce' ) ) {
            return false;
        }

        do_action('uwp_before_validate', 'forgot');

        $result = uwp_validate_fields($data, 'forgot');

        $result = apply_filters('uwp_validate_result', $result, 'forgot');

        if (is_wp_error($result)) {
            return $result;
        }

        do_action('uwp_after_validate', 'forgot');


        $user_data = get_user_by('email', $data['uwp_forgot_email']);

        // make sure user account is active before account reset
        $mod_value = get_user_meta( $user_data->ID, 'uwp_mod', true );
        if ($mod_value == 'email_unconfirmed') {
            $errors->add('activate_account', __('<strong>Error</strong>: Your account is not activated yet. Please activate your account first.', 'userswp'));
        }

        if (is_wp_error($errors)) {
            return $errors;
        }

        $login_details = $this->generate_forgot_message($user_data);

        $res = $this->uwp_send_email( 'forgot', $user_data->ID, $login_details );

        if (!$res) {
            if (get_option('admin_email') == $data['uwp_forgot_email']) {
                $errors->add('something_wrong', __('<strong>Error</strong>: Something went wrong when sending email. Please check your site error log for more details.', 'userswp'));
            } else {
                $errors->add('something_wrong', __('<strong>Error</strong>: Something went wrong when sending email. Please contact site admin.', 'userswp'));
            }

        }

        if ($errors->get_error_code())
            return $errors;

        return true;
    }

    public function process_change($data) {

        $errors = new WP_Error();

        if( ! isset( $data['uwp_change_nonce'] ) || ! wp_verify_nonce( $data['uwp_change_nonce'], 'uwp-change-nonce' ) ) {
            return false;
        }

        do_action('uwp_before_validate', 'change');

        $result = uwp_validate_fields($data, 'change');

        $result = apply_filters('uwp_validate_result', $result, 'change');

        if (is_wp_error($result)) {
            return $result;
        }

        do_action('uwp_after_validate', 'change');

        $user_data = get_user_by('id', get_current_user_id());

        if (is_wp_error($user_data)) {
            return $user_data;
        }
        
        $res = $this->uwp_send_email( 'change', $user_data->ID );

        if (!$res) {
            if (get_option('admin_email') == $user_data->user_email) {
                $errors->add('something_wrong', __('<strong>Error</strong>: Something went wrong when sending email. Please check your site error log for more details.', 'userswp'));
            } else {
                $errors->add('something_wrong', __('<strong>Error</strong>: Something went wrong when sending email. Please contact site admin.', 'userswp'));
            }
        }

        if ($errors->get_error_code())
            return $errors;

        wp_set_password( $data['uwp_change_password'], $user_data->ID );
        wp_set_auth_cookie( $user_data->ID, false);

        return true;
    }
    
    public function process_reset($data) {

        $errors = new WP_Error();

        if( ! isset( $data['uwp_reset_nonce'] ) || ! wp_verify_nonce( $data['uwp_reset_nonce'], 'uwp-reset-nonce' ) ) {
            return false;
        }

        do_action('uwp_before_validate', 'reset');

        $result = uwp_validate_fields($data, 'reset');

        $result = apply_filters('uwp_validate_result', $result, 'reset');

        if (is_wp_error($result)) {
            return $result;
        }

        do_action('uwp_after_validate', 'reset');

        $login = $data['uwp_reset_username'];
        $key = $data['uwp_reset_key'];
        $user_data = check_password_reset_key( $key, $login );

        if (is_wp_error($user_data)) {
            return $user_data;
        }
        
        $res = $this->uwp_send_email( 'reset', $user_data->ID );

        if (!$res) {
            if (get_option('admin_email') == $user_data->user_email) {
                $errors->add('something_wrong', __('<strong>Error</strong>: Something went wrong when sending email. Please check your site error log for more details.', 'userswp'));
            } else {
                $errors->add('something_wrong', __('<strong>Error</strong>: Something went wrong when sending email. Please contact site admin.', 'userswp'));
            }

        }

        if ($errors->get_error_code())
            return $errors;

        wp_set_password( $data['uwp_reset_password'], $user_data->ID );

        return true;
    }

    public function generate_forgot_message($user_data) {

        global $wpdb, $wp_hasher;

        $allow = apply_filters('allow_password_reset', true, $user_data->ID);
        if ( ! $allow )
            return false;
        else if ( is_wp_error($allow) )
            return false;

        $as_password = apply_filters('uwp_forgot_message_as_password', false);

        if ($as_password) {
            $new_pass = wp_generate_password(12, false);
            wp_set_password($new_pass, $user_data->ID);
            update_user_meta($user_data->ID, 'default_password_nag', true); //Set up the Password change nag.
            $message = '<p><b>' . __('Your login Information :', 'userswp') . '</b></p>';
            $message .= '<p>' . sprintf(__('Username: %s', 'userswp'), $user_data->user_login) . "</p>";
            $message .= '<p>' . sprintf(__('Password: %s', 'userswp'), $new_pass) . "</p>";

        } else {
            $key = wp_generate_password( 20, false );
            do_action( 'retrieve_password_key', $user_data->user_login, $key );

            if ( empty( $wp_hasher ) ) {
                require_once ABSPATH . 'wp-includes/class-phpass.php';
                $wp_hasher = new PasswordHash( 8, true );
            }
            $hashed = $wp_hasher->HashPassword( $key );
            $wpdb->update( $wpdb->users, array( 'user_activation_key' => time().":".$hashed ), array( 'user_login' => $user_data->user_login ) );
            $message = __('Someone requested that the password be reset for the following account:', 'userswp') . "\r\n\r\n";
            $message .= home_url( '/' ) . "\r\n\r\n";
            $message .= sprintf(__('Username: %s', 'userswp'), $user_data->user_login) . "\r\n\r\n";
            $message .= __('If this was a mistake, just ignore this email and nothing will happen.', 'userswp') . "\r\n\r\n";
            $message .= __('To reset your password, visit the following address:', 'userswp') . "\r\n\r\n";
            $message .= site_url("reset?key=$key&login=" . rawurlencode($user_data->user_login), 'login') . "\r\n";

        }


        return $message;

    }

    public function process_account($data = array(), $files = array()) {

        $current_user_id = get_current_user_id();
        if (!$current_user_id) {
            return false;
        }

        $errors = new WP_Error();

        if( ! isset( $data['uwp_account_nonce'] ) || ! wp_verify_nonce( $data['uwp_account_nonce'], 'uwp-account-nonce' ) ) {
            return false;
        }

        do_action('uwp_before_validate', 'account');

        $result = uwp_validate_fields($data, 'account');

        $result = apply_filters('uwp_validate_result', $result, 'account');

        if (is_wp_error($result)) {
            return $result;
        }

        $uploads_result = uwp_validate_uploads($files, 'account');

        if (is_wp_error($uploads_result)) {
            return $uploads_result;
        }

        do_action('uwp_after_validate', 'account');

        $result = array_merge( $result, $uploads_result );


        $args = array(
            'ID' => $current_user_id
        );

        if (isset($result['uwp_account_email'])) {
            $args['user_email'] = $result['uwp_account_email'];
        }

        if (isset($result['uwp_account_first_name']) && isset($result['uwp_account_last_name'])) {
            $args['display_name'] = $result['uwp_account_first_name'] . ' ' . $result['uwp_account_last_name'];
        }

        if (isset($result['uwp_account_first_name'])) {
            $args['first_name'] = $result['uwp_account_first_name'];
        }

        if (isset($result['uwp_account_last_name'])) {
            $args['last_name'] = $result['uwp_account_last_name'];
        }

        if (isset($result['uwp_account_bio'])) {
            $args['description'] = $result['uwp_account_bio'];
        }

        if (isset($result['uwp_account_display_name']) && !empty($result['uwp_account_display_name'])) {
            $args['display_name'] = $result['uwp_account_display_name'];
        }

        if (isset($result['password'])) {
            $args['user_pass'] = $result['password'];
        }

        $user_id = wp_update_user( $args );

        if (!$user_id) {
            $errors->add('registerfail', sprintf(__('<strong>Error</strong>: Something went wrong.', 'userswp'), get_option('admin_email')));
            return $errors;
        }

        $res = $this->uwp_save_user_extra_fields($user_id, $result, 'account');

        if (!$res) {
            $errors->add('something_wrong', __('<strong>Error</strong>: Something went wrong. Please contact site admin.', 'userswp'));
        }

        if ($errors->get_error_code())
            return $errors;


        if (uwp_get_option('enable_account_update_notification') == '1') {
            $user_data = get_user_by('id', $user_id);
            
            $res = $this->uwp_send_email( 'account', $user_data->ID );

            if (!$res) {
                if (get_option('admin_email') == $user_data->user_email) {
                    $errors->add('something_wrong', __('<strong>Error</strong>: Something went wrong when sending email. Please check your site error log for more details.', 'userswp'));
                } else {
                    $errors->add('something_wrong', __('<strong>Error</strong>: Something went wrong when sending email. Please contact site admin.', 'userswp'));
                }

            }

            if ($errors->get_error_code())
                return $errors;
        }
        
        return true;

    }

    public function process_upload_submit($data = array(), $files = array(), $type = 'avatar') {

        $current_user_id = get_current_user_id();
        if (!$current_user_id) {
            return false;
        }

        if( ! isset( $data['uwp_upload_nonce'] ) || ! wp_verify_nonce( $data['uwp_upload_nonce'], 'uwp-upload-nonce' ) ) {
            return false;
        }

        do_action('uwp_before_validate', $type);

        $result = uwp_validate_uploads($files, $type);

        $result = apply_filters('uwp_validate_result', $result, $type);

        if (is_wp_error($result)) {
            return $result;
        }

        $profile_url = uwp_build_profile_tab_url($current_user_id);

        $url = add_query_arg(
            array(
                'uwp_crop' => $result['uwp_'.$type.'_file'],
                'type' => $type
            ),
            $profile_url);

        return $url;

    }

    public function process_image_crop($data = array(), $type = 'avatar') {
        
        if (!is_user_logged_in()) {
            return false;
        }

        // If is current user's profile (profile.php)
        if ( is_admin() && defined('IS_PROFILE_PAGE') && IS_PROFILE_PAGE ) {
            $user_id = get_current_user_id();
            // If is another user's profile page
        } elseif (is_admin() && ! empty($_GET['user_id']) && is_numeric($_GET['user_id']) ) {
            $user_id = $_GET['user_id'];
            // Otherwise something is wrong.
        } else {
            $user_id = get_current_user_id();
        }
        $image_url = $data['uwp_crop'];
        
        $errors = new WP_Error();
        if (empty($image_url)) {
            $errors->add('something_wrong', __('<strong>Error</strong>: Something went wrong. Please contact site admin.', 'userswp'));
        }

        if ($errors->get_error_code())
            return $errors;
        
        if ($image_url) {
            if ($type == 'avatar') {
                $full_width  = apply_filters('uwp_avatar_image_width', 150);
                $full_height = apply_filters('uwp_avatar_image_height', 150);
            } else {
                $full_width  = apply_filters('uwp_banner_image_width', uwp_get_option('profile_banner_width', 1000));
                $full_height = apply_filters('uwp_banner_image_height', 300);
            }

            $uploads = wp_upload_dir();
            $upload_url = $uploads['baseurl'];
            $upload_path = $uploads['basedir'];
            $image_url = str_replace($upload_url, $upload_path, $image_url);
            $ext = pathinfo($image_url, PATHINFO_EXTENSION); // to get extension
            $name =pathinfo($image_url, PATHINFO_FILENAME); //file name without extension
            $thumb_image_name = $name.'_uwp_'.$type.'_thumb'.'.'.$ext;
            $thumb_image_location = str_replace($name.'.'.$ext, $thumb_image_name, $image_url);
            //Get the new coordinates to crop the image.
            $x = $data["x"];
            $y = $data["y"];
            $w = $data["w"];
            $h = $data["h"];
            //Scale the image based on cropped width setting
            $scale = $full_width/$w;
            //$scale = 1; // no scaling
            $cropped = uwp_resizeThumbnailImage($thumb_image_location, $image_url,$x, $y, $w, $h,$scale);
            $cropped = str_replace($upload_path, $upload_url, $cropped);
            if ($type == 'avatar') {
                uwp_update_usermeta($user_id, 'uwp_account_avatar_thumb', $cropped);
            } else {
                uwp_update_usermeta($user_id, 'uwp_account_banner_thumb', $cropped);
            }
        }

        if (is_admin()) {
            if ($user_id == get_current_user_id()) {
                $profile_url = admin_url( 'profile.php' );
            } else {
                $profile_url = admin_url( 'user-edit.php?user_id='.$user_id );
            }
        } else {
            $profile_url = uwp_build_profile_tab_url($user_id);
        }
        return $profile_url;

    }

    public function uwp_save_user_extra_fields($user_id, $data, $type) {

        if (empty($user_id) || empty($data) || empty($type)) {
            return false;
        }

        // custom user fields not applicable for login and forgot
        if ($type == 'login' || $type == 'forgot') {
            return true;
        }

        //unset default fields
//        if ($type == 'register') {
//            if (isset($data['uwp_account_username'])) {
//                unset($data['uwp_account_username']);
//            }
//        }

        if ($type == 'account' || $type == 'register') {
            if (isset($data['password'])) {
                unset($data['password']);
            }
        }

        if (empty($data)) {
            // no extra fields. so just return
            return true;
        } else {
            foreach($data as $key => $value) {
//                if ($value == '0' || !empty($value)) {
                    uwp_update_usermeta($user_id, $key, $value);
//                }
            }
            return true;
        }
    }

    public function uwp_send_email( $message_type, $user_id, $login_details = "" ) {
        $user_data = get_userdata($user_id);

        $login_page_id = uwp_get_option('login_page', false);
        if ($login_page_id) {
            $login_page_url = get_permalink($login_page_id);
        } else {
            $login_page_url = wp_login_url();
        }

        $subject = "";
        $message = "";

        if ( $message_type == 'register' ) {
            $subject = uwp_get_option('registration_success_email_subject', '');
            $message = uwp_get_option('registration_success_email_content', '');
        } elseif ( $message_type == 'activate' ) {
            $subject = uwp_get_option('registration_activate_email_subject', '');
            $message = uwp_get_option('registration_activate_email_content', '');
        } elseif ( $message_type == 'forgot' ) {
            $subject = uwp_get_option('forgot_password_email_subject', '');
            $message = uwp_get_option('forgot_password_email_content', '');
        } elseif ( $message_type == 'reset' ) {
            $subject = uwp_get_option('reset_password_email_subject', '');
            $message = uwp_get_option('reset_password_email_content', '');
        } elseif ( $message_type == 'change' ) {
            $subject = uwp_get_option('change_password_email_subject', '');
            $message = uwp_get_option('change_password_email_content', '');
        } elseif ( $message_type == 'account' ) {
            $subject = uwp_get_option('account_update_email_subject', '');
            $message = uwp_get_option('account_update_email_content', '');
        }

        if ( ! empty( $subject ) ) {
            $subject = __( stripslashes_deep( $subject ), 'userswp' );
        }

        if ( ! empty( $message ) ) {
            $message = __( stripslashes_deep( $message ), 'userswp' );
        }

        $sitefromEmail     = get_option( 'admin_email' );
        $sitefromEmailName =  stripslashes(get_option('blogname'));


        $user_login = '';
        if ( $user_id > 0 && $user_info = get_userdata( $user_id ) ) {
            $user_login = $user_info->user_login;
        }

        $siteurl       = home_url();
        $siteurl_link  = '<a href="' . $siteurl . '">' . $siteurl . '</a>';
        $loginurl      = $login_page_url;
        $loginurl_link = '<a href="' . $loginurl . '">login</a>';

        $current_date     = date_i18n( 'Y-m-d H:i:s', current_time( 'timestamp' ) );

        $site_email = get_option( 'admin_email' );

        $site_name = stripslashes(get_option('blogname'));

        //user
        $user_name = $user_data->display_name;
        $user_email = $user_data->user_email;

        $search_array  = array(
            '[#site_name_url#]',
            '[#site_name#]',
            '[#to_name#]',
            '[#from_name#]',
            '[#login_url#]',
            '[#user_name#]',
            '[#from_email#]',
            '[#user_login#]',
            '[#username#]',
            '[#current_date#]',
            '[#login_details#]',
        );
        $replace_array = array(
            $siteurl_link,
            $sitefromEmailName,
            $user_name,
            $site_name,
            $loginurl_link,
            $user_name,
            $site_email,
            $user_login,
            $user_login,
            $current_date,
            $login_details
        );
        $message = str_replace( $search_array, $replace_array, $message );

        $search_array  = array(
            '[#site_name_url#]',
            '[#site_name#]',
            '[#to_name#]',
            '[#from_name#]',
            '[#user_name#]',
            '[#from_email#]',
            '[#user_login#]',
            '[#username#]',
            '[#current_date#]'
        );
        $replace_array = array(
            $siteurl_link,
            $sitefromEmailName,
            $user_name,
            $site_name,
            $user_name,
            $site_email,
            $user_login,
            $user_login,
            $current_date
        );
        $subject = str_replace( $search_array, $replace_array, $subject );

        $headers  = array();
        $headers[] = 'Content-type: text/html; charset=UTF-8';
        $headers[] = "Reply-To: " . $site_email;
        $headers[] = 'From: ' . $sitefromEmailName . ' <' . $sitefromEmail . '>';

        $to = $user_email;

        $to = apply_filters( 'uwp_send_email_to', $to, $message_type, $user_id );

        $subject = apply_filters( 'uwp_send_email_subject', $subject, $message_type, $user_id  );

        $message = apply_filters( 'uwp_send_email_message', $message, $message_type, $user_id  );

        $headers = apply_filters( 'uwp_send_email_headers', $headers, $message_type, $user_id  );

        $sent = wp_mail( $to, $subject, $message, $headers );

        if ( ! $sent ) {
            if ( is_array( $to ) ) {
                $to = implode( ',', $to );
            }
            $log_message = sprintf(
                __( "Email from UsersWP failed to send.\nMessage type: %s\nSend time: %s\nTo: %s\nSubject: %s\n\n", 'userswp' ),
                $message_type,
                date_i18n( 'F j Y H:i:s', current_time( 'timestamp' ) ),
                $to,
                $subject
            );
            $this->uwp_error_log( $log_message );
            return false;
        } else {
            return true;
        }

    }

    public static function uwp_error_log($log){

        $should_log = apply_filters( 'uwp_log_errors', WP_DEBUG);
        if ( true === $should_log ) {
            if ( is_array( $log ) || is_object( $log ) ) {
                error_log( print_r( $log, true ) );
            } else {
                error_log( $log );
            }
        }
    }
    
    
    public function uwp_upload_file_remove() {

        $htmlvar = strip_tags(esc_sql($_POST['htmlvar']));
        $user_id = (int) strip_tags(esc_sql($_POST['uid']));
        $permission = false;
        if ($user_id == get_current_user_id()) {
            $permission = true;
        } else {
            if (current_user_can('manage_options')) {
                $permission = true;
            }
        }
        if ($permission) {
            uwp_update_usermeta($user_id, $htmlvar, '');
        }
        die();
    }
    
    
    // form fields html generation

    public function uwp_form_input_datepicker($html, $field, $value, $form_type){

        // Check if there is a field specific filter.
        if(has_filter("uwp_form_input_html_datepicker_{$field->htmlvar_name}")){
            $html = apply_filters("uwp_form_input_html_datepicker_{$field->htmlvar_name}", $html, $field, $value, $form_type);
        }

        // If no html then we run the standard output.
        if(empty($html)) {

            ob_start(); // Start  buffering;

            $extra_fields = unserialize($field->extra_fields);

            if ($extra_fields['date_format'] == '')
                $extra_fields['date_format'] = 'yy-mm-dd';

            $date_format = $extra_fields['date_format'];
            $jquery_date_format  = $date_format;

            if (!empty($value) && !is_string($value)) {
                $value = date('Y-m-d', $value);
            }


            // check if we need to change the format or not
            $date_format_len = strlen(str_replace(' ', '', $date_format));
            if($date_format_len>5){// if greater then 5 then it's the old style format.

                $search = array('dd','d','DD','mm','m','MM','yy'); //jQuery UI datepicker format
                $replace = array('d','j','l','m','n','F','Y');//PHP date format

                $date_format = str_replace($search, $replace, $date_format);
            }else{
                $jquery_date_format = uwp_date_format_php_to_jqueryui( $jquery_date_format );
            }
            if($value=='0000-00-00'){$value='';}//if date not set, then mark it empty
            $value = uwp_date($value, 'Y-m-d', $date_format);
            ?>
            <script type="text/javascript">

                jQuery(function () {

                    jQuery("#<?php echo $field->htmlvar_name;?>").datepicker({changeMonth: true, changeYear: true
                        <?php if($field->htmlvar_name == 'uwp_account_dob'){ echo ", yearRange: '1900:+0'"; } else { echo ", yearRange: '1900:2050'"; }?>
                        <?php echo apply_filters("uwp_datepicker_extra_{$field->htmlvar_name}",'');?>});

                    jQuery("#<?php echo $field->htmlvar_name;?>").datepicker("option", "dateFormat", '<?php echo $jquery_date_format;?>');

                    <?php if(!empty($value)){?>
                    jQuery("#<?php echo $field->htmlvar_name;?>").datepicker("setDate", '<?php echo $value;?>');
                    <?php } ?>

                });

            </script>
            <div id="<?php echo $field->htmlvar_name;?>_row"
                 class="<?php if ($field->is_required) echo 'required_field';?> uwp_form_row clearfix uwp-fieldset-details">

                <?php
                $site_title = uwp_get_form_label($field);
                if (!is_admin()) { ?>
                    <label>
                        <?php echo (trim($site_title)) ? $site_title : '&nbsp;'; ?>
                        <?php if ($field->is_required) echo '<span>*</span>';?>
                    </label>
                <?php } ?>

                <input name="<?php echo $field->htmlvar_name;?>"
                       id="<?php echo $field->htmlvar_name;?>"
                       placeholder="<?php echo $site_title; ?>"
                       title="<?php echo $site_title; ?>"
                       type="text"
                    <?php if ($field->is_required == 1) { echo 'required="required"'; } ?>
                       value="<?php echo esc_attr($value);?>" class="uwp_textfield"/>

                <span class="uwp_message_note"><?php _e($field->help_text, 'userswp');?></span>
                <?php if ($field->is_required) { ?>
                    <span class="uwp_message_error"><?php _e($field->required_msg, 'userswp'); ?></span>
                <?php } ?>
            </div>

            <?php
            $html = ob_get_clean();
        }

        return $html;
    }

    public function uwp_form_input_time($html, $field, $value, $form_type){

        if(has_filter("uwp_form_input_html_time_{$field->htmlvar_name}")){

            $html = apply_filters("uwp_form_input_html_time_{$field->htmlvar_name}",$html, $field, $value, $form_type);
        }

        // If no html then we run the standard output.
        if(empty($html)) {

            ob_start(); // Start  buffering;

            if ($value != '')
                $value = date('H:i', strtotime($value));
            ?>
            <script type="text/javascript">
                jQuery(document).ready(function () {

                    jQuery('#<?php echo $field->htmlvar_name;?>').timepicker({
                        showPeriod: true,
                        showLeadingZero: true
                    });
                });
            </script>
            <div id="<?php echo $field->htmlvar_name;?>_row"
                 class="<?php if ($field->is_required) echo 'required_field';?> uwp_form_row clearfix uwp-fieldset-details">

                <?php
                $site_title = uwp_get_form_label($field);
                if (!is_admin()) { ?>
                    <label>
                        <?php echo (trim($site_title)) ? $site_title : '&nbsp;'; ?>
                        <?php if ($field->is_required) echo '<span>*</span>';?>
                    </label>
                <?php } ?>

                <input readonly="readonly" name="<?php echo $field->htmlvar_name;?>"
                       id="<?php echo $field->htmlvar_name;?>"
                       value="<?php echo esc_attr($value);?>"
                       placeholder="<?php echo $site_title; ?>"
                       type="text"
                       class="uwp_textfield"/>

                <span class="uwp_message_note"><?php _e($field->help_text, 'userswp');?></span>
                <?php if ($field->is_required) { ?>
                    <span class="uwp_message_error"><?php _e($field->required_msg, 'userswp'); ?></span>
                <?php } ?>
            </div>
            <?php
            $html = ob_get_clean();
        }

        return $html;
    }

    public function uwp_form_input_select($html, $field, $value, $form_type){

        // Check if there is a field specific filter.
        if(has_filter("uwp_form_input_html_select_{$field->htmlvar_name}")){
            $html = apply_filters("uwp_form_input_html_select_{$field->htmlvar_name}", $html, $field, $value, $form_type);
        }

        // If no html then we run the standard output.
        if(empty($html)) {

            ob_start(); // Start  buffering;

            ?>
            <div id="<?php echo $field->htmlvar_name;?>_row"
                 class="<?php if ($field->is_required) echo 'required_field';?> uwp_form_row">

                <?php
                $site_title = uwp_get_form_label($field);
                if (!is_admin()) { ?>
                    <label>
                        <?php echo (trim($site_title)) ? $site_title : '&nbsp;'; ?>
                        <?php if ($field->is_required) echo '<span>*</span>';?>
                    </label>
                <?php } ?>

                <?php
                $option_values_arr = uwp_string_values_to_options($field->option_values, true);
                $select_options = '';
                if (!empty($option_values_arr)) {
                    foreach ($option_values_arr as $option_row) {
                        if (isset($option_row['optgroup']) && ($option_row['optgroup'] == 'start' || $option_row['optgroup'] == 'end')) {
                            $option_label = isset($option_row['label']) ? $option_row['label'] : '';

                            $select_options .= $option_row['optgroup'] == 'start' ? '<optgroup label="' . esc_attr($option_label) . '">' : '</optgroup>';
                        } else {
                            $option_label = isset($option_row['label']) ? $option_row['label'] : '';
                            $option_value = isset($option_row['value']) ? $option_row['value'] : '';
                            $selected = $option_value == $value ? 'selected="selected"' : '';

                            $select_options .= '<option value="' . esc_attr($option_value) . '" ' . $selected . '>' . $option_label . '</option>';
                        }
                    }
                }
                ?>
                <select name="<?php echo $field->htmlvar_name;?>" id="<?php echo $field->htmlvar_name;?>"
                        class="uwp_textfield"
                        title="<?php echo $site_title; ?>"
                        data-placeholder="<?php echo __('Choose', 'userswp') . ' ' . $site_title . '&hellip;';?>"
                ><?php echo $select_options;?>
                </select>
                <span class="uwp_message_note"><?php _e($field->help_text, 'userswp');?></span>
                <?php if ($field->is_required) { ?>
                    <span class="uwp_message_error"><?php _e($field->required_msg, 'userswp'); ?></span>
                <?php } ?>
            </div>

            <?php
            $html = ob_get_clean();
        }

        return $html;
    }

    public function uwp_form_input_multiselect($html, $field, $value, $form_type){

        // Check if there is a field specific filter.
        if(has_filter("uwp_form_input_html_multiselect_{$field->htmlvar_name}")){
            $html = apply_filters("uwp_form_input_html_multiselect_{$field->htmlvar_name}", $html, $field, $value, $form_type);
        }


        if(empty($html)) {

            ob_start(); // Start  buffering;

            $multi_display = 'select';
            if (!empty($field->extra_fields)) {
                $multi_display = unserialize($field->extra_fields);
            }
            ?>
            <div id="<?php echo $field->htmlvar_name;?>_row"
                 class="<?php if ($field->is_required) echo 'required_field';?> uwp_form_row">

                <?php
                $site_title = uwp_get_form_label($field);
                if (!is_admin()) { ?>
                    <label>
                        <?php echo (trim($site_title)) ? $site_title : '&nbsp;'; ?>
                        <?php if ($field->is_required) echo '<span>*</span>';?>
                    </label>
                <?php } ?>

                <input type="hidden" name="<?php echo $field->htmlvar_name;?>" value=""/>
                <?php if ($multi_display == 'select') { ?>
                <div class="uwp_multiselect_list">
                    <select name="<?php echo $field->htmlvar_name;?>[]"
                            id="<?php echo $field->htmlvar_name;?>"
                            title="<?php echo $site_title; ?>"
                            multiple="multiple" class="uwp_chosen_select"
                            data-placeholder="<?php echo $site_title; ?>"
                    >
                        <?php
                        } else {
                            ?>
                            <ul class="uwp_multi_choice">
                            <?php
                        }

                        $option_values_arr = uwp_string_values_to_options($field->option_values, true);
                        $select_options = '';
                        if (!empty($option_values_arr)) {
                            foreach ($option_values_arr as $option_row) {
                                if (isset($option_row['optgroup']) && ($option_row['optgroup'] == 'start' || $option_row['optgroup'] == 'end')) {
                                    $option_label = isset($option_row['label']) ? $option_row['label'] : '';

                                    if ($multi_display == 'select') {
                                        $select_options .= $option_row['optgroup'] == 'start' ? '<optgroup label="' . esc_attr($option_label) . '">' : '</optgroup>';
                                    } else {
                                        $select_options .= $option_row['optgroup'] == 'start' ? '<li>' . $option_label . '</li>' : '';
                                    }
                                } else {
                                    $option_label = isset($option_row['label']) ? $option_row['label'] : '';
                                    $option_value = isset($option_row['value']) ? $option_row['value'] : '';
                                    $selected = $option_value == $value ? 'selected="selected"' : '';
                                    $selected = '';
                                    $checked = '';

                                    if ((!is_array($value) && trim($value) != '') || (is_array($value) && !empty($value))) {
                                        if (!is_array($value)) {
                                            $value_array = explode(',', $value);
                                        } else {
                                            $value_array = $value;
                                        }

                                        if (is_array($value_array)) {
                                            if (in_array($option_value, $value_array)) {
                                                $selected = 'selected="selected"';
                                                $checked = 'checked="checked"';
                                            }
                                        }
                                    }

                                    if ($multi_display == 'select') {
                                        $select_options .= '<option value="' . esc_attr($option_value) . '" ' . $selected . '>' . $option_label . '</option>';
                                    } else {
                                        $select_options .= '<li><input name="' . $field->name . '[]" ' . $checked . ' value="' . esc_attr($option_value) . '" class="uwp-' . $multi_display . '" type="' . $multi_display . '" />&nbsp;' . $option_label . ' </li>';
                                    }
                                }
                            }
                        }
                        echo $select_options;

                        if ($multi_display == 'select') { ?></select></div>
            <?php } else { ?>
                </ul>
            <?php } ?>
                <?php if ($field->is_required) { ?>
                    <span class="uwp_message_error"><?php _e($field->required_msg, 'userswp'); ?></span>
                <?php } ?>
            </div>
            <?php
            $html = ob_get_clean();
        }

        return $html;
    }

    public function uwp_form_input_file($html, $field, $value, $form_type){

        // Check if there is a field specific filter.
        if(has_filter("uwp_form_input_html_file_{$field->htmlvar_name}")){
            $html = apply_filters("uwp_form_input_html_file_{$field->htmlvar_name}", $html, $field, $value, $form_type);
        }

        // If no html then we run the standard output.
        if(empty($html)) {

            ob_start(); // Start  buffering;

            ?>
            <div id="<?php echo $field->htmlvar_name;?>_row"
                 class="<?php if ($field->is_required) echo 'required_field';?> uwp_form_<?php echo $field->field_type; ?>_row">

                <?php
                $site_title = uwp_get_form_label($field);
                if (!is_admin()) { ?>
                    <label>
                        <?php echo (trim($site_title)) ? $site_title : '&nbsp;'; ?>
                        <?php if ($field->is_required) echo '<span>*</span>';?>
                    </label>
                <?php } ?>

                <?php echo uwp_file_upload_preview($field, $value); ?>
                <input name="<?php echo $field->htmlvar_name; ?>"
                       class="<?php echo $field->css_class; ?>"
                       placeholder="<?php echo $site_title; ?>"
                       title="<?php echo $site_title; ?>"
                    <?php if ($field->is_required == 1) { echo 'required="required"'; } ?>
                       type="<?php echo $field->field_type; ?>">
                <span class="uwp_message_note"><?php _e($field->help_text, 'userswp');?></span>
                <?php if ($field->is_required) { ?>
                    <span class="uwp_message_error"><?php _e($field->required_msg, 'userswp'); ?></span>
                <?php } ?>
            </div>

            <?php
            $html = ob_get_clean();
        }

        return $html;
    }

    public function uwp_form_input_checkbox($html, $field, $value, $form_type){

        // Check if there is a field specific filter.
        if(has_filter("uwp_form_input_html_checkbox_{$field->htmlvar_name}")){
            $html = apply_filters("uwp_form_input_html_checkbox_{$field->htmlvar_name}", $html, $field, $value, $form_type);
        }

        // If no html then we run the standard output.
        if(empty($html)) {

            ob_start(); // Start  buffering;
            $site_title = uwp_get_form_label($field);
            ?>
            <div id="<?php echo $field->htmlvar_name;?>_row"
                 class="<?php if ($field->is_required) echo 'required_field';?> uwp_form_<?php echo $field->field_type; ?>_row">
                <input type="hidden" name="<?php echo $field->htmlvar_name; ?>" value="0" />
                <input name="<?php echo $field->htmlvar_name; ?>"
                       class="<?php echo $field->css_class; ?>"
                       placeholder="<?php echo $site_title; ?>"
                       title="<?php echo $site_title; ?>"
                    <?php if ($field->is_required == 1) { echo 'required="required"'; } ?>
                    <?php if ($value == '1') { echo 'checked="checked"'; } ?>
                       type="<?php echo $field->field_type; ?>"
                       value="1">
                <?php
                echo (trim($site_title)) ? $site_title : '&nbsp;';
                ?>
                <span class="uwp_message_note"><?php _e($field->help_text, 'userswp');?></span>
                <?php if ($field->is_required) { ?>
                    <span class="uwp_message_error"><?php _e($field->required_msg, 'userswp'); ?></span>
                <?php } ?>
            </div>

            <?php
            $html = ob_get_clean();
        }

        return $html;
    }

    public function uwp_form_input_radio($html, $field, $value, $form_type){

        // Check if there is a field specific filter.
        if(has_filter("uwp_form_input_html_radio_{$field->htmlvar_name}")){
            $html = apply_filters("uwp_form_input_html_radio_{$field->htmlvar_name}", $html, $field, $value, $form_type);
        }

        // If no html then we run the standard output.
        if(empty($html)) {

            ob_start(); // Start  buffering;

            ?>
            <div id="<?php echo $field->htmlvar_name;?>_row"
                 class="<?php if ($field->is_required) echo 'required_field';?> uwp_form_<?php echo $field->field_type; ?>_row">

                <?php
                $site_title = uwp_get_form_label($field);
                if (!is_admin()) { ?>
                    <label>
                        <?php echo (trim($site_title)) ? $site_title : '&nbsp;'; ?>
                        <?php if ($field->is_required) echo '<span>*</span>';?>
                    </label>
                <?php } ?>


                <?php if ($field->option_values) {
                    $option_values = uwp_string_values_to_options($field->option_values, true);

                    if (!empty($option_values)) {
                        $count = 0;
                        foreach ($option_values as $option_value) {
                            if (empty($option_value['optgroup'])) {
                                $count++;
                                if ($count == 1) {
                                    $class = "uwp-radio-first";
                                } else {
                                    $class = "";
                                }
                                ?>
                                <span class="uwp-radios <?php echo $class; ?>">
                                    <input name="<?php echo $field->htmlvar_name; ?>"
                                           id="<?php echo $field->htmlvar_name; ?>"
                                           title="<?php echo esc_attr($option_value['label']); ?>"
                                        <?php checked($value, $option_value['value']);?>
                                        <?php if ($field->is_required == 1) { echo 'required="required"'; } ?>
                                           value="<?php echo esc_attr($option_value['value']); ?>"
                                           class="uwp-radio" type="radio" />
                                    <?php echo $option_value['label']; ?>
                                </span>
                                <?php
                            }
                        }
                    }
                }
                ?>
                <span class="uwp_message_note"><?php _e($field->help_text, 'userswp');?></span>
                <?php if ($field->is_required) { ?>
                    <span class="uwp_message_error"><?php _e($field->required_msg, 'userswp'); ?></span>
                <?php } ?>
            </div>
            <?php
            $html = ob_get_clean();
        }

        return $html;
    }

    public function uwp_form_input_text($html, $field, $value, $form_type){

        // Check if there is a custom field specific filter.
        if(has_filter("uwp_form_input_text_{$field->htmlvar_name}")){
            $html = apply_filters("uwp_form_input_text_{$field->htmlvar_name}",$html, $field, $value, $form_type);
        }

        // If no html then we run the standard output.
        if(empty($html)) {

            ob_start(); // Start  buffering;

            $type = 'text';
            $step = false;
            //number and float validation $validation_pattern
            if(isset($field->data_type) && $field->data_type == 'INT'){
                $type = 'number';
            } elseif(isset($field->data_type) && $field->data_type == 'FLOAT'){
                $dp = $field->decimal_point;
                switch ($dp) {
                    case "1":
                        $step = "0.1";
                        break;
                    case "2":
                        $step = "0.01";
                        break;
                    case "3":
                        $step = "0.001";
                        break;
                    case "4":
                        $step = "0.0001";
                        break;
                    case "5":
                        $step = "0.00001";
                        break;
                    case "6":
                        $step = "0.000001";
                        break;
                    case "7":
                        $step = "0.0000001";
                        break;
                    case "8":
                        $step = "0.00000001";
                        break;
                    case "9":
                        $step = "0.000000001";
                        break;
                    case "10":
                        $step = "0.0000000001";
                        break;
                    default:
                        $step = "0.01";
                        break;
                }
                $type = 'number';
            }

            ?>

            <div id="<?php echo $field->htmlvar_name;?>_row"
                 class="<?php if ($field->is_required) echo 'required_field';?> uwp_form_<?php echo $field->field_type; ?>_row">

                <?php
                $site_title = uwp_get_form_label($field);
                if (!is_admin()) { ?>
                    <label>
                        <?php echo (trim($site_title)) ? $site_title : '&nbsp;'; ?>
                        <?php if ($field->is_required) echo '<span>*</span>';?>
                    </label>
                <?php } ?>

                <input name="<?php echo $field->htmlvar_name;?>"
                       class="<?php echo $field->css_class; ?> uwp_textfield"
                       id="<?php echo $field->htmlvar_name;?>"
                       placeholder="<?php echo $site_title; ?>"
                       value="<?php echo esc_attr(stripslashes($value));?>"
                       title="<?php echo $site_title; ?>"
                    <?php if ($field->is_required == 1) { echo 'required="required"'; } ?>
                       type="<?php echo $type; ?>"
                    <?php if ($step) { echo 'step="'.$step.'"'; } ?>
                />
                <span class="uwp_message_note"><?php _e($field->help_text, 'userswp');?></span>
                <?php if ($field->is_required) { ?>
                    <span class="uwp_message_error"><?php _e($field->required_msg, 'userswp'); ?></span>
                <?php } ?>
            </div>


            <?php
            $html = ob_get_clean();
        }

        return $html;
    }

    public function uwp_form_input_textarea($html, $field, $value, $form_type){

        // Check if there is a field specific filter.
        if(has_filter("uwp_form_input_textarea_{$field->htmlvar_name}")){
            $html = apply_filters("uwp_form_input_textarea_{$field->htmlvar_name}", $html, $field, $value, $form_type);
        }

        // If no html then we run the standard output.
        if(empty($html)) {

            ob_start(); // Start  buffering;

            ?>
            <div id="<?php echo $field->htmlvar_name;?>_row"
                 class="<?php if ($field->is_required) echo 'required_field';?> uwp_form_<?php echo $field->field_type; ?>_row">

                <?php
                $site_title = uwp_get_form_label($field);
                if (!is_admin()) { ?>
                    <label>
                        <?php echo (trim($site_title)) ? $site_title : '&nbsp;'; ?>
                        <?php if ($field->is_required) echo '<span>*</span>';?>
                    </label>
                <?php } ?>

                <textarea name="<?php echo $field->htmlvar_name; ?>"
                          class="<?php echo $field->css_class; ?>"
                          placeholder="<?php echo $site_title; ?>"
                          title="<?php echo $site_title; ?>"
                    <?php if ($field->is_required == 1) { echo 'required="required"'; } ?>
                          type="<?php echo $field->field_type; ?>"
                          rows="4"><?php echo $value; ?></textarea>
                <span class="uwp_message_note"><?php _e($field->help_text, 'userswp');?></span>
                <?php if ($field->is_required) { ?>
                    <span class="uwp_message_error"><?php _e($field->required_msg, 'userswp'); ?></span>
                <?php } ?>
            </div>

            <?php
            $html = ob_get_clean();
        }

        return $html;
    }

    public function uwp_form_input_fieldset($html, $field, $value, $form_type) {
        // Check if there is a custom field specific filter.
        if(has_filter("uwp_form_input_fieldset_{$field->htmlvar_name}")){
            $html = apply_filters("uwp_form_input_fieldset_{$field->htmlvar_name}",$html, $field, $value, $form_type);
        }

        // If no html then we run the standard output.
        if(empty($html)) {

            ob_start(); // Start  buffering;
            ?>
            <h3 class="uwp_input_fieldset <?php echo $field->css_class; ?>">
                <?php echo $field->site_title;; ?>
                <?php if ( $field->help_text != '' ) {
                    echo '<small>( ' . $field->help_text . ' )</small>';
                } ?></h3>
            <?php
            $html = ob_get_clean();
        }

        return $html;
    }

    public function uwp_form_input_url($html, $field, $value, $form_type){


        // Check if there is a custom field specific filter.
        if(has_filter("uwp_form_input_url_{$field->htmlvar_name}")){
            $html = apply_filters("uwp_form_input_url_{$field->htmlvar_name}",$html, $field, $value, $form_type);
        }

        // If no html then we run the standard output.
        if(empty($html)) {

            ob_start(); // Start  buffering;
            ?>
            <div id="<?php echo $field->htmlvar_name;?>_row"
                 class="<?php if ($field->is_required) echo 'required_field';?> uwp_form_<?php echo $field->field_type; ?>_row">

                <?php
                $site_title = uwp_get_form_label($field);
                if (!is_admin()) { ?>
                    <label>
                        <?php echo (trim($site_title)) ? $site_title : '&nbsp;'; ?>
                        <?php if ($field->is_required) echo '<span>*</span>';?>
                    </label>
                <?php } ?>

                <input name="<?php echo $field->htmlvar_name;?>"
                       class="<?php echo $field->css_class; ?> uwp_textfield"
                       id="<?php echo $field->htmlvar_name;?>"
                       placeholder="<?php echo $site_title; ?>"
                       value="<?php echo esc_attr(stripslashes($value));?>"
                       title="<?php echo $site_title; ?>"
                    <?php if ($field->is_required == 1) { echo 'required="required"'; } ?>
                       type="url"
                       oninvalid="setCustomValidity('<?php _e('Please enter a valid URL including http://', 'userswp'); ?>')"
                       onchange="try{setCustomValidity('')}catch(e){}"
                />
                <span class="uwp_message_note"><?php _e($field->help_text, 'userswp');?></span>
                <?php if ($field->is_required) { ?>
                    <span class="uwp_message_error"><?php _e($field->required_msg, 'userswp'); ?></span>
                <?php } ?>
            </div>

            <?php
            $html = ob_get_clean();
        }

        return $html;
    }

    public function uwp_form_input_email($html, $field, $value, $form_type){


        // Check if there is a custom field specific filter.
        if(has_filter("uwp_form_input_email_{$field->htmlvar_name}")){
            $html = apply_filters("uwp_form_input_email_{$field->htmlvar_name}",$html, $field, $value, $form_type);
        }

        // If no html then we run the standard output.
        if(empty($html)) {

            ob_start(); // Start  buffering;
            ?>
            <div id="<?php echo $field->htmlvar_name;?>_row"
                 class="<?php if ($field->is_required) echo 'required_field';?> uwp_form_<?php echo $field->field_type; ?>_row">

                <?php
                $site_title = uwp_get_form_label($field);
                if (!is_admin()) { ?>
                    <label>
                        <?php echo (trim($site_title)) ? $site_title : '&nbsp;'; ?>
                        <?php if ($field->is_required) echo '<span>*</span>';?>
                    </label>
                <?php } ?>

                <input name="<?php echo $field->htmlvar_name;?>"
                       class="<?php echo $field->css_class; ?> uwp_textfield"
                       id="<?php echo $field->htmlvar_name;?>"
                       placeholder="<?php echo $site_title; ?>"
                       value="<?php echo esc_attr(stripslashes($value));?>"
                       title="<?php echo $site_title; ?>"
                    <?php if ($field->is_required == 1) { echo 'required="required"'; } ?>
                       type="email"
                />
                <span class="uwp_message_note"><?php _e($field->help_text, 'userswp');?></span>
                <?php if ($field->is_required) { ?>
                    <span class="uwp_message_error"><?php _e($field->required_msg, 'userswp'); ?></span>
                <?php } ?>
            </div>

            <?php
            $html = ob_get_clean();
        }

        if(has_filter("uwp_form_input_email_{$field->htmlvar_name}_after")){
            $html = apply_filters("uwp_form_input_email_{$field->htmlvar_name}_after",$html, $field, $value, $form_type);
        }

        return $html;
    }

    // Add multipart/form-data to edit form
    function add_multipart_to_admin_edit_form() {
        global $wpdb;
        $table_name = $wpdb->base_prefix . 'uwp_form_fields';
        $fields = $wpdb->get_results("SELECT * FROM " . $table_name . " WHERE form_type = 'account' AND field_type = 'file' AND is_default = '0' ORDER BY sort_order ASC");
        if ($fields) {
            echo 'enctype="multipart/form-data"';
        }
    }

    // Update admin edit
    public function update_profile_extra_admin_edit($user_id) {
        ob_start();
        global $wpdb;
        $table_name = $wpdb->base_prefix . 'uwp_form_fields';
        //Normal fields
        $fields = $wpdb->get_results("SELECT * FROM " . $table_name . " WHERE form_type = 'account' AND field_type != 'file' AND field_type != 'fieldset' AND is_default = '0' ORDER BY sort_order ASC");
        if ($fields) {
            $result = uwp_validate_fields($_POST, 'account', $fields);
            if (!is_wp_error($result)) {
                foreach ($fields as $field) {
                    $value = $result[$field->htmlvar_name];
                    if ($value == '0' || !empty($value)) {
                        uwp_update_usermeta($user_id, $field->htmlvar_name, $value);
                    }
                }
            }
        }

        //File fields
        $fields = $wpdb->get_results("SELECT * FROM " . $table_name . " WHERE form_type = 'account' AND field_type = 'file' AND is_default = '0' ORDER BY sort_order ASC");
        if ($fields) {
            $result = uwp_validate_uploads($_FILES, 'account', true, $fields);
            if (!is_wp_error($result)) {
                foreach ($fields as $field) {
                    $value = $result[$field->htmlvar_name];
                    if ($value == '0' || !empty($value)) {
                        uwp_update_usermeta($user_id, $field->htmlvar_name, $value);
                    }
                }
            }
        }
    }
    
    
    // Search form
    public function uwp_users_search_form_text_field($keyword) {
        ?>
        <input placeholder="Search For" name="uwps" value="<?php echo $keyword; ?>" class="s search-input" type="text">
        <?php
    }

    public function uwp_users_search_form_submit($keyword) {
        ?>
        <input class="uwp-searchsubmit uwp-search-submit" value="Search" type="submit">
        <?php
    }
}