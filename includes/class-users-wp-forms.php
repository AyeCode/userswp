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


    public function __construct() {

    }

    public function handler()
    {
        global $uwp_notices;

        ob_start();

        $errors = null;
        $message = null;
        $redirect = false;
        $processed = false;

        $redirect_page_id = uwp_get_option('login_redirect_to', '');
        if (empty($redirect_page_id)) {
            $redirect_to = home_url('/');
        } else {
            $redirect_to = get_permalink($redirect_page_id);
        }
        $redirect_to = apply_filters('uwp_login_redirect', $redirect_to);

        if (isset($_POST['uwp_register_submit'])) {
            $_POST['auto_login'] = apply_filters('uwp_register_auto_login', true);
            $errors = $this->process_register($_POST, $_FILES);
            $message = __('Account registered successfully.', 'uwp');
            if ($_POST['auto_login']) {
                $redirect = $redirect_to;
            }
            $processed = true;
        } elseif (isset($_POST['uwp_login_submit'])) {
            $errors = $this->process_login($_POST);
            $redirect = $redirect_to;
            $processed = true;
        } elseif (isset($_POST['uwp_forgot_submit'])) {
            $errors = $this->process_forgot($_POST);
            $message = __('Please check your email.', 'uwp');
            $processed = true;
        } elseif (isset($_POST['uwp_reset_submit'])) {
            $errors = $this->process_reset($_POST);
            $message = __('Password updated successfully. Please login with your new password', 'uwp');
            $processed = true;
        } elseif (isset($_POST['uwp_account_submit'])) {
            $errors = $this->process_account($_POST, $_FILES);
            $message = __('Account updated successfully.', 'uwp');
            $processed = true;
        } elseif (isset($_POST['uwp_avatar_submit'])) {
            $errors = $this->process_upload_submit($_POST, $_FILES, 'avatar');
            if (!is_wp_error($errors)) {
                $redirect = $errors;
            }
            $message = __('Avatar cropped successfully.', 'uwp');
            $processed = true;
        } elseif (isset($_POST['uwp_banner_submit'])) {
            $errors = $this->process_upload_submit($_POST, $_FILES, 'banner');
            if (!is_wp_error($errors)) {
                $redirect = $errors;
            }
            $message = __('Banner cropped successfully.', 'uwp');
            $processed = true;
        } elseif (isset($_POST['uwp_avatar_crop'])) {
            $errors = $this->process_image_crop($_POST, 'avatar');
            if (!is_wp_error($errors)) {
                $redirect = $errors;
            }
            $message = __('Avatar cropped successfully.', 'uwp');
            $processed = true;
        } elseif (isset($_POST['uwp_banner_crop'])) {
            $errors = $this->process_image_crop($_POST, 'banner');
            if (!is_wp_error($errors)) {
                $redirect = $errors;
            }
            $message = __('Banner cropped successfully.', 'uwp');
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

        $uwp_notices = ob_get_contents();
        ob_end_clean();

    }

    public function display_notices() {
        global $uwp_notices;
        echo $uwp_notices;
    }

    public function process_register($data = array(), $files = array()) {

        $errors = new WP_Error();

        if( ! isset( $data['uwp_register_nonce'] ) || ! wp_verify_nonce( $data['uwp_register_nonce'], 'uwp-register-nonce' ) ) {
            return false;
        }

        if (!get_option('users_can_register')) {
            $errors->add('register_disabled', __('<strong>ERROR</strong>: User registration is currently not allowed.', 'uwp'));
            return $errors;
        }

        do_action('uwp_before_validate', 'register');

        $result = $this->validate_fields($data, 'register');

        $result = apply_filters('uwp_validate_result', $result, 'register');

        if (is_wp_error($result)) {
            return $result;
        }

        $uploads_result = $this->validate_uploads($files, 'register');

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
        if (isset($result['uwp_register_first_name']) && !empty($result['uwp_register_first_name'])) {
            $first_name = $result['uwp_register_first_name'];
        }

        $last_name = "";
        if (isset($result['uwp_register_last_name']) && !empty($result['uwp_register_last_name'])) {
            $last_name = $result['uwp_register_last_name'];
        }

        if (!empty($first_name) || !empty($last_name)) {
            $display_name = $first_name . ' ' . $last_name;
        } else {
            $display_name = $result['uwp_register_username'];
        }

        $args = array(
            'user_login'   => $result['uwp_register_username'],
            'user_email'   => $result['uwp_register_email'],
            'user_pass'    => $password,
            'display_name' => $display_name,
            'first_name'   => $first_name,
            'last_name'    => $last_name
        );

        $user_id = wp_insert_user( $args );

        if (!$user_id) {
            $errors->add('registerfail', sprintf(__('<strong>Error</strong>: Something went wrong.', 'uwp'), get_option('admin_email')));
            return $errors;
        }

        $save_result = $this->uwp_save_user_extra_fields($user_id, $result, 'register');

        if (!$save_result) {
            $errors->add('something_wrong', __('<strong>Error</strong>: Something went wrong. Please contact site admin.', 'uwp'));
        }

        if ($errors->get_error_code())
            return $errors;

        if ($generated_password) {
            $message_pass = $password;
        } else {
            $message_pass = __("Password you entered", 'uwp');
        }

        $login_details = __('<p><b>' . __('Your login Information :', 'uwp') . '</b></p>
        <p>' . __('Username:', 'uwp') . ' ' . $result['uwp_register_username'] . '</p>
        <p>' . __('Password:', 'uwp') . ' ' . $message_pass . '</p>');

        $send_result = $this->uwp_send_email( 'register', $user_id, $login_details );

        if (!$send_result) {
            $errors->add('something_wrong', __('<strong>Error</strong>: Something went wrong when sending email. Please contact site admin.', 'uwp'));
        }

        if ($errors->get_error_code())
            return $errors;




        if ($data['auto_login']) {
            $res = wp_signon(
                array(
                    'user_login' => $result['uwp_register_username'],
                    'user_password' => $password,
                    'remember' => false
                ),
                false
            );

            if (is_wp_error($res)) {
                $errors->add('invalid_userorpass', __('<strong>Error</strong>: Invalid username or Password.', 'uwp'));
                return $errors;
            } else {
                wp_redirect(home_url('/'));
                exit();
            }
        } else {
            return true;
        }


    }

    public function process_login($data) {

        $errors = new WP_Error();

        if( ! isset( $data['uwp_login_nonce'] ) || ! wp_verify_nonce( $data['uwp_login_nonce'], 'uwp-login-nonce' ) ) {
            return false;
        }

        do_action('uwp_before_validate', 'login');

        $result = $this->validate_fields($data, 'login');

        $result = apply_filters('uwp_validate_result', $result, 'login');

        if (is_wp_error($result)) {
            return $result;
        }

        do_action('uwp_after_validate', 'login');

        if ($data['remember_me'] == 'forever') {
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
            $errors->add('invalid_userorpass', __('<strong>Error</strong>: Invalid username or Password.', 'uwp'));
            return $errors;
        } else {
            $redirect_page_id = uwp_get_option('login_redirect_to', '');
            if (empty($redirect_page_id)) {
                $redirect_to = home_url('/');
            } else {
                $redirect_to = get_permalink($redirect_page_id);
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

        $result = $this->validate_fields($data, 'forgot');

        $result = apply_filters('uwp_validate_result', $result, 'forgot');

        if (is_wp_error($result)) {
            return $result;
        }

        do_action('uwp_after_validate', 'forgot');


        $user_data = get_user_by('email', $data['uwp_forgot_email']);

        $login_details = $this->generate_forgot_message($user_data);

        $res = $this->uwp_send_email( 'forgot', $user_data->ID, $login_details );

        if (!$res) {
            if (get_option('admin_email') == $data['uwp_forgot_email']) {
                $errors->add('something_wrong', __('<strong>Error</strong>: Something went wrong when sending email. Please check your site error log for more details.', 'uwp'));
            } else {
                $errors->add('something_wrong', __('<strong>Error</strong>: Something went wrong when sending email. Please contact site admin.', 'uwp'));
            }

        }

        if ($errors->get_error_code())
            return $errors;

        return true;
    }

    public function process_reset($data) {

        $errors = new WP_Error();

        if( ! isset( $data['uwp_reset_nonce'] ) || ! wp_verify_nonce( $data['uwp_reset_nonce'], 'uwp-reset-nonce' ) ) {
            return false;
        }

        do_action('uwp_before_validate', 'reset');

        $result = $this->validate_fields($data, 'reset');

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

        $login_details = "";

        $res = $this->uwp_send_email( 'reset', $user_data->ID, $login_details );

        if (!$res) {
            if (get_option('admin_email') == $user_data->user_email) {
                $errors->add('something_wrong', __('<strong>Error</strong>: Something went wrong when sending email. Please check your site error log for more details.', 'uwp'));
            } else {
                $errors->add('something_wrong', __('<strong>Error</strong>: Something went wrong when sending email. Please contact site admin.', 'uwp'));
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
            $message = '<p><b>' . __('Your login Information :', 'uwp') . '</b></p>';
            $message .= '<p>' . sprintf(__('Username: %s', 'uwp'), $user_data->user_login) . "</p>";
            $message .= '<p>' . sprintf(__('Password: %s', 'uwp'), $new_pass) . "</p>";

        } else {
            $key = wp_generate_password( 20, false );
            do_action( 'retrieve_password_key', $user_data->user_login, $key );

            if ( empty( $wp_hasher ) ) {
                require_once ABSPATH . 'wp-includes/class-phpass.php';
                $wp_hasher = new PasswordHash( 8, true );
            }
            $hashed = $wp_hasher->HashPassword( $key );
            $wpdb->update( $wpdb->users, array( 'user_activation_key' => time().":".$hashed ), array( 'user_login' => $user_data->user_login ) );
            $message = __('Someone requested that the password be reset for the following account:', 'uwp') . "\r\n\r\n";
            $message .= home_url( '/' ) . "\r\n\r\n";
            $message .= sprintf(__('Username: %s', 'uwp'), $user_data->user_login) . "\r\n\r\n";
            $message .= __('If this was a mistake, just ignore this email and nothing will happen.', 'uwp') . "\r\n\r\n";
            $message .= __('To reset your password, visit the following address:', 'uwp') . "\r\n\r\n";
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

        $result = $this->validate_fields($data, 'account');

        $result = apply_filters('uwp_validate_result', $result, 'account');

        if (is_wp_error($result)) {
            return $result;
        }

        $uploads_result = $this->validate_uploads($files, 'account');

        if (is_wp_error($uploads_result)) {
            return $uploads_result;
        }

        do_action('uwp_after_validate', 'account');

        $result = array_merge( $result, $uploads_result );

        $args = array(
            'ID' => $current_user_id,
            'user_email'   => $result['uwp_account_email'],
            'display_name' => $result['uwp_account_first_name'] . ' ' . $result['uwp_account_last_name'],
            'first_name'   => $result['uwp_account_first_name'],
            'last_name'    => $result['uwp_account_last_name']
        );

        if (isset($result['password'])) {
            $args['user_pass'] = $result['password'];
        }

        $user_id = wp_update_user( $args );

        if (!$user_id) {
            $errors->add('registerfail', sprintf(__('<strong>Error</strong>: Something went wrong.', 'uwp'), get_option('admin_email')));
            return $errors;
        }

        $res = $this->uwp_save_user_extra_fields($user_id, $result, 'account');

        if (!$res) {
            $errors->add('something_wrong', __('<strong>Error</strong>: Something went wrong. Please contact site admin.', 'uwp'));
        }

        if ($errors->get_error_code())
            return $errors;

        //todo: update account notification. some users maybe interested in that

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

        $result = $this->validate_uploads($files, $type);

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
        $user_id = get_current_user_id();
        $image_url = $data['uwp_crop'];
        
        $errors = new WP_Error();
        if (empty($image_url)) {
            $errors->add('something_wrong', __('<strong>Error</strong>: Something went wrong. Please contact site admin.', 'uwp'));
        }

        if ($errors->get_error_code())
            return $errors;
        
        if ($image_url) {
            if ($type == 'avatar') {
                $full_height = apply_filters('uwp_avatar_image_height', 128);
                $full_width  = apply_filters('uwp_avatar_image_width', 128);
            } else {
                $full_height = apply_filters('uwp_banner_image_height', 300);
                $full_width  = apply_filters('uwp_banner_image_width', 700);
            }

            $uploads = wp_upload_dir();
            $upload_url = $uploads['baseurl'];
            $upload_path = $uploads['basedir'];
            $image_url = str_replace($upload_url, $upload_path, $image_url);
            $ext = pathinfo($image_url, PATHINFO_EXTENSION); // to get extension
            $name =pathinfo($image_url, PATHINFO_FILENAME); //file name without extension
            $thumb_image_name = $name.'_uwp_thumb'.'.'.$ext;
            $thumb_image_location = str_replace($name.'.'.$ext, $thumb_image_name, $image_url);
            //Get the new coordinates to crop the image.
            $x = $data["x"];
            $y = $data["y"];
            $w = $data["w"];
            $h = $data["h"];
            //Scale the image based on cropped width setting
            $scale = $full_width/$w;
            $cropped = uwp_resizeThumbnailImage($thumb_image_location, $image_url,$w,$h,$x,$y,$scale);
            $cropped = str_replace($upload_path, $upload_url, $cropped);
            if ($type == 'avatar') {
                uwp_update_usermeta($user_id, 'uwp_account_avatar_thumb', $cropped);
            } else {
                uwp_update_usermeta($user_id, 'uwp_account_banner_thumb', $cropped);
            }
        }


        $profile_url = uwp_build_profile_tab_url($user_id);
        return $profile_url;

    }

    public function validate_fields($data, $type) {

        $errors = new WP_Error();

        global $wpdb;
        $table_name = $wpdb->prefix . 'uwp_custom_fields';
        $fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $table_name . " WHERE form_type = %s AND field_type != 'file' AND is_active = '1' ORDER BY sort_order ASC", array($type)));

        $validated_data = array();

        if (!empty($fields)) {
            foreach ($fields as $field) {

                $value = $data[$field->htmlvar_name];
                $sanitized_value = $value;

                if ($field->field_type == 'password') {
                    continue;
                }

                $sanitized = false;

                // sanitize our default fields
                switch($field->htmlvar_name) {

                    case 'uwp_register_username':
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

                        case 'email':
                            $sanitized_value = sanitize_email($value);
                            break;

                        case 'multiselect':
                            $sanitized_value = array_map( 'sanitize_text_field', $value );
                            break;

                        default:
                            $sanitized_value = sanitize_text_field($value);

                    }
                }



                if (($field->htmlvar_name == 'uwp_account_password' || $field->htmlvar_name == 'uwp_account_confirm_password') && empty($value)) {
                    $field->is_required = 0;
                }


                if ($field->is_required == 1 && $sanitized_value == '') {
                    $errors->add('empty_'.$field->htmlvar_name, __('<strong>Error</strong>: '.$field->site_title.' cannot be empty.', 'uwp'));
                }

                if ($field->field_type == 'email' && !is_email($sanitized_value)) {
                    $errors->add('invalid_email', __('<strong>Error</strong>: The email address isn&#8217;t correct.', 'uwp'));
                }

                //register email
                if ($field->htmlvar_name == 'uwp_register_email' && email_exists($sanitized_value)) {
                    $errors->add('email_exists', __('<strong>Error</strong>: This email is already registered, please choose another one.', 'uwp'));
                }

                //forgot email
                if ($field->htmlvar_name == 'uwp_forgot_email' && !email_exists($sanitized_value)) {
                    $errors->add('email_exists', __('<strong>Error</strong>: This email doesn\'t exists.', 'uwp'));
                }

                // Check the username for register
                if ($field->htmlvar_name == 'uwp_register_username') {
                    if (!validate_username($sanitized_value)) {
                        $errors->add('invalid_username', __('<strong>Error</strong>: This username is invalid because it uses illegal characters. Please enter a valid username.', 'uwp'));
                    }
                    if (username_exists($sanitized_value)) {
                        $errors->add('username_exists', __('<strong>Error</strong>: This username is already registered. Please choose another one.', 'uwp'));
                    }
                }

                // Check the username for login
                if ($field->htmlvar_name == 'uwp_login_username') {
                    if (!validate_username($sanitized_value)) {
                        $errors->add('invalid_username', __('<strong>Error</strong>: This username is invalid because it uses illegal characters. Please enter a valid username.', 'uwp'));
                    }
                }


                $validated_data[$field->htmlvar_name] = $sanitized_value;

            }
        }

        if ($type == 'login' || $type == 'register' || ($type == 'account' && !empty( $data['uwp_account_password']))) {
            //check password
            if( empty( $data['uwp_'.$type.'_password'] ) ) {
                $errors->add( 'empty_password', __( 'Please enter a password', 'uwp' ) );
            }

            if (strlen($data['uwp_'.$type.'_password']) < 7) {
                $errors->add('pass_match', __('ERROR: Password must be 7 characters or more.', 'uwp'));
            }

            $validated_data['password'] = $data['uwp_'.$type.'_password'];
        }

        if ($type == 'register' || $type == 'reset' || ($type == 'account' && !empty( $data['uwp_account_password']))) {
            //check password
            if ($data['uwp_'.$type.'_password'] != $data['uwp_'.$type.'_confirm_password']) {
                $errors->add('pass_match', __('ERROR: Passwords do not match.', 'uwp'));
            }

            $validated_data['password'] = $data['uwp_'.$type.'_password'];
        }


        if ($errors->get_error_code())
            return $errors;

        return $validated_data;
    }

    public function validate_uploads($files, $type) {

        $validated_data = array();

        if (empty($files)) {
            return $validated_data;
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'uwp_custom_fields';
        $fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $table_name . " WHERE form_type = %s AND field_type = 'file' AND is_active = '1' ORDER BY sort_order ASC", array($type)));



        if (!empty($fields)) {
            foreach ($fields as $field) {

                if(isset($files[$field->htmlvar_name])) {

                    $file_urls = $this->handle_file_upload($field, $files);

                    if (is_wp_error($file_urls)) {
                        return $file_urls;
                    }

                    $validated_data[$field->htmlvar_name] = $file_urls['url'];
                }

            }
        }

        return $validated_data;
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
        if ($type == 'register') {
            if (isset($data['uwp_register_username'])) {
                unset($data['uwp_register_username']);
            }
            if (isset($data['uwp_register_email'])) {
                unset($data['uwp_register_email']);
            }
            if (isset($data['password'])) {
                unset($data['password']);
            }
            if (isset($data['uwp_register_first_name'])) {
                unset($data['uwp_register_first_name']);
            }
            if (isset($data['uwp_register_last_name'])) {
                unset($data['uwp_register_last_name']);
            }
        }

        if ($type == 'account') {
            if (isset($data['uwp_account_email'])) {
                unset($data['uwp_account_email']);
            }
            if (isset($data['password'])) {
                unset($data['password']);
            }
            if (isset($data['uwp_account_first_name'])) {
                unset($data['uwp_account_first_name']);
            }
            if (isset($data['uwp_account_last_name'])) {
                unset($data['uwp_account_last_name']);
            }
        }

        if (empty($data)) {
            // no extra fields. so just return
            return true;
        } else {
            foreach($data as $key => $value) {
                // Register and Account form extra fields should be saved under common name
                // So it can be created and updated on the same meta.
                // For this reason, lets replace all register meta keys with account meta keys
                $key = str_replace('uwp_register_', 'uwp_account_', $key);
                uwp_update_usermeta($user_id, $key, $value);
            }
            return true;
        }
    }

    public function uwp_send_email( $message_type, $user_id, $login_details ) {

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
        } elseif ( $message_type == 'forgot' ) {
            $subject = uwp_get_option('forgot_password_email_subject', '');
            $message = uwp_get_option('forgot_password_email_content', '');
        } elseif ( $message_type == 'reset' ) {
            $subject = uwp_get_option('reset_password_email_subject', '');
            $message = uwp_get_option('reset_password_email_content', '');
        }

        if ( ! empty( $subject ) ) {
            $subject = __( stripslashes_deep( $subject ), 'uwp' );
        }

        if ( ! empty( $message ) ) {
            $message = __( stripslashes_deep( $message ), 'uwp' );
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
                __( "Email from UsersWP failed to send.\nMessage type: %s\nSend time: %s\nTo: %s\nSubject: %s\n\n", 'uwp' ),
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

    public function uwp_error_log($log){

        $should_log = apply_filters( 'uwp_log_errors', WP_DEBUG);
        if ( true === $should_log ) {
            if ( is_array( $log ) || is_object( $log ) ) {
                error_log( print_r( $log, true ) );
            } else {
                error_log( $log );
            }
        }
    }

    public function handle_file_upload( $field, $files ) {

        if ( isset( $files[ $field->htmlvar_name ] ) && ! empty( $files[ $field->htmlvar_name ] ) && ! empty( $files[ $field->htmlvar_name ]['name'] ) ) {

            $extra_fields = unserialize($field->extra_fields);

            $allowed_mime_types = array();
            if (isset($extra_fields['uwp_file_types'])) {
                $allowed_mime_types = $extra_fields['uwp_file_types'];
            }

            $allowed_mime_types = apply_filters('uwp_allowed_mime_types', $allowed_mime_types, $field->htmlvar_name);

            $file_urls       = array();
            $files_to_upload = $this->uwp_prepare_files( $files[ $field->htmlvar_name ] );

            foreach ( $files_to_upload as $file_key => $file_to_upload ) {

                if (!empty($allowed_mime_types)) {
                    $ext = uwp_get_file_type($file_to_upload['type']);

                    $allowed_error_text = implode(', ', $allowed_mime_types);
                    if ( !in_array( $ext , $allowed_mime_types ) )
                        return new WP_Error( 'validation-error', sprintf( __( 'Allowed files types are: %s', 'uwp' ),  $allowed_error_text) );
                }


                if ( $file_to_upload['size'] > uwp_get_option('profile_avatar_max_size', 1048576) )
                    return new WP_Error( 'avatar-too-big', __( 'The uploaded file is too big.', 'uwp' ) );


                $uploaded_file = $this->uwp_upload_file( $file_to_upload, array( 'file_key' => $file_key ) );

                if ( is_wp_error( $uploaded_file ) ) {

                    return new WP_Error( 'validation-error', $uploaded_file->get_error_message() );

                } else {

                    $file_urls[] = array(
                        'url'  => $uploaded_file->url,
                        'path' => $uploaded_file->path,
                        'size' => $uploaded_file->size
                    );

                }

            }

            return current( $file_urls );

        }
        return true;

    }

    public function uwp_prepare_files( $file_data ) {
        $files_to_upload = array();

        if ( is_array( $file_data['name'] ) ) {
            foreach ( $file_data['name'] as $file_data_key => $file_data_value ) {

                if ( $file_data['name'][ $file_data_key ] ) {
                    $files_to_upload[] = array(
                        'name'     => $file_data['name'][ $file_data_key ],
                        'type'     => $file_data['type'][ $file_data_key ],
                        'tmp_name' => $file_data['tmp_name'][ $file_data_key ],
                        'error'    => $file_data['error'][ $file_data_key ],
                        'size'     => $file_data['size'][ $file_data_key ]
                    );
                }
            }
        } else {
            $files_to_upload[] = $file_data;
        }

        return $files_to_upload;
    }

    public function uwp_upload_file( $file, $args = array() ) {

        include_once ABSPATH . 'wp-admin/includes/file.php';
        include_once ABSPATH . 'wp-admin/includes/media.php';

        $args = wp_parse_args( $args, array(
            'file_key'           => '',
            'file_label'         => '',
            'allowed_mime_types' => get_allowed_mime_types()
        ) );

        $uploaded_file              = new stdClass();

        if ( ! in_array( $file['type'], $args['allowed_mime_types'] ) ) {
            if ( $args['file_label'] ) {
                return new WP_Error( 'upload', sprintf( __( '"%s" (filetype %s) needs to be one of the following file types: %s', 'uwp' ), $args['file_label'], $file['type'], implode( ', ', array_keys( $args['allowed_mime_types'] ) ) ) );
            } else {
                return new WP_Error( 'upload', sprintf( __( 'Uploaded files need to be one of the following file types: %s', 'uwp' ), implode( ', ', array_keys( $args['allowed_mime_types'] ) ) ) );
            }
        } else {
            $upload = wp_handle_upload( $file, apply_filters( 'uwp_handle_upload_overrides', array( 'test_form' => false ) ) );
            if ( ! empty( $upload['error'] ) ) {
                return new WP_Error( 'upload', $upload['error'] );
            } else {
                $uploaded_file->url       = $upload['url'];
                $uploaded_file->name      = basename( $upload['file'] );
                $uploaded_file->path      = $upload['file'];
                $uploaded_file->type      = $upload['type'];
                $uploaded_file->size      = $file['size'];
                $uploaded_file->extension = substr( strrchr( $uploaded_file->name, '.' ), 1 );
            }
        }


        return $uploaded_file;
    }

    public function check_password_reset_key( $key, $login ) {
        global $wpdb, $wp_hasher;

        $key = preg_replace( '/[^a-z0-9]/i', '', $key );

        $errors = new WP_Error();

        if ( empty( $key ) || ! is_string( $key ) ) {
            $errors->add('invalid_key', __('<strong>Error</strong>: Invalid Username or Reset Key.', 'uwp'));
            return false;
        }

        if ( empty( $login ) || ! is_string( $login ) ) {
            $errors->add('invalid_key', __('<strong>Error</strong>: Invalid Username or Reset Key.', 'uwp'));
            return false;
        }

        if ($errors->get_error_code())
            return $errors;

        $user = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->users WHERE user_login = %s", $login ) );

        if ( ! empty( $user ) ) {
            if ( empty( $wp_hasher ) ) {
                require_once ABSPATH . 'wp-includes/class-phpass.php';
                $wp_hasher = new PasswordHash( 8, true );
            }

            $valid = $wp_hasher->CheckPassword( $key, $user->user_activation_key );
        }

        if ( empty( $user ) || empty( $valid ) ) {
            $errors->add('invalid_key', __('<strong>Error</strong>: Invalid Username or Reset Key.', 'uwp'));
            return false;
        }

        if ($errors->get_error_code())
            return $errors;

        return get_userdata( $user->ID );
    }


}