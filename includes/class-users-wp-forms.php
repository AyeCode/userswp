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

        if (isset($_POST['uwp_register_submit'])) {
            $this->register();
        } elseif (isset($_POST['uwp_login_submit'])) {
            $this->login();
        } elseif (isset($_POST['uwp_forgot_submit'])) {
            $this->forgot();
        } elseif (isset($_POST['uwp_account_submit'])) {
            $this->account();
        }

        $uwp_notices = ob_get_contents();
        ob_end_clean();

    }

    public function display_notices() {
        global $uwp_notices;
        echo $uwp_notices;
    }

    public function register() {

        $register_page = esc_attr( get_option('uwp_register_page', false));

        if (!get_option('users_can_register')) {
            wp_redirect(get_permalink($register_page));
            exit();
        }

        $_POST['auto_login'] = false;

        $errors = $this->process_register($_POST);


        /* display error in registration form */
        if (is_wp_error($errors)) {
            echo '<div class="alert alert-error text-center">';
            echo $errors->get_error_message();
            echo '</div>';
        } else {
            //todo: redirect not working. fix this
            if ($_POST['auto_login']) {
                wp_redirect(home_url('/'));
                exit();
            } else {
                echo '<div class="alert alert-success text-center">';
                echo __('Account registered successfully.', 'uwp');
                echo '</div>';
            }
        }

    }

    public function process_register($data = array()) {

        $errors = new WP_Error();

        if( ! isset( $data['uwp_register_nonce'] ) || ! wp_verify_nonce( $data['uwp_register_nonce'], 'uwp-register-nonce' ) ) {
            return false;
        }

        if (!get_option('users_can_register')) {
            $errors->add('register_disabled', __('<strong>ERROR</strong>: User registration is currently not allowed.', 'uwp'));
            return $errors;
        }

        //todo: recaptcha check
        do_action('uwp_before_validate', 'register');

        $result = $this->validate_fields($data, 'register');

        $result = apply_filters('uwp_validate_result', $result, 'register');

        if (is_wp_error($result)) {
            return $result;
        }

        do_action('uwp_after_validate', 'register');

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

        $result = $this->uwp_save_user_extra_fields($user_id, $result, 'register');

        if (!$result) {
            $errors->add('something_wrong', __('<strong>Error</strong>: Something went wrong. Please contact site admin.', 'uwp'));
        }

        if ($errors->get_error_code())
            return $errors;

        if ($generated_password) {
            $message_pass = $password;
        } else {
            $message_pass = "Password you entered";
        }

        $login_details = __('<p><b>' . __('Your login Information :', 'uwp') . '</b></p>
        <p>' . __('Username:', 'uwp') . ' ' . $result['uwp_register_username'] . '</p>
        <p>' . __('Password:', 'uwp') . ' ' . $message_pass . '</p>');

        $result = $this->uwp_send_email( 'register', $user_id, $login_details );

        if (!$result) {
            $errors->add('something_wrong', __('<strong>Error</strong>: Something went wrong when sending email. Please contact site admin.', 'uwp'));
        }

        if ($errors->get_error_code())
            return $errors;


        if ($data['auto_login']) {
            $login_data = array(
                'username' => $result['uwp_register_username'],
                'password' => $result['password'],
            );

            return $this->process_login($login_data);

        } else {
            return true;
        }


    }

    public function login() {


        $errors = $this->process_login($_POST);


        /* display error in registration form */
        if (is_wp_error($errors)) {
            echo '<div class="alert alert-error text-center">';
            echo $errors->get_error_message();
            echo '</div>';
        } else {
            wp_redirect(home_url('/'));
            exit();
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
            //todo: fix redirect
            wp_redirect(home_url('/'));
            exit();
        }
    }

    public function forgot() {

        $errors = $this->process_forgot($_POST);

        /* display error in registration form */
        if (is_wp_error($errors)) {
            echo '<div class="alert alert-error text-center">';
            echo $errors->get_error_message();
            echo '</div>';
        } else {
            echo '<div class="alert alert-success text-center">';
            echo __('Please check your email.', 'uwp');
            echo '</div>';
        }

    }

    public function process_forgot($data) {

        $errors = new WP_Error();

        if( ! isset( $data['uwp_forgot_nonce'] ) || ! wp_verify_nonce( $data['uwp_forgot_nonce'], 'uwp-forgot-nonce' ) ) {
            return false;
        }

        $email = sanitize_email($data['uwp_forgot_email']);

        // Check the e-mail address
        if ($email == '') {
            $errors->add('empty_email', __('<strong>Error</strong>: Please type your e-mail address.', 'uwp'));
        }
        if (!is_email($email)) {
            $errors->add('invalid_email', __('<strong>Error</strong>: The email address isn&#8217;t correct.', 'uwp'));
        }
        if (!email_exists($email)) {
            $errors->add('email_exists', __('<strong>Error</strong>: This email doesn\'t exists.', 'uwp'));
        }

        if ($errors->get_error_code())
            return $errors;

        $user_data = get_user_by('email', $email);

        $login_details = $this->generate_forgot_message($user_data);

        $result = $this->uwp_send_email( 'forgot', $user_data->ID, $login_details );

        if (!$result) {
            if (get_option('admin_email') == $email) {
                $errors->add('something_wrong', __('<strong>Error</strong>: Something went wrong when sending email. Please check your site error log for more details.', 'uwp'));
            } else {
                $errors->add('something_wrong', __('<strong>Error</strong>: Something went wrong when sending email. Please contact site admin.', 'uwp'));
            }

        }

        if ($errors->get_error_code())
            return $errors;

        return true;
    }

    public function generate_forgot_message($user_data) {

        $new_pass = wp_generate_password(12, false);

        wp_set_password($new_pass, $user_data->ID);
        update_user_meta($user_data->ID, 'default_password_nag', true); //Set up the Password change nag.
        $login_details = '<p><b>' . __('Your login Information :', 'uwp') . '</b></p>';
        $login_details .= '<p>' . sprintf(__('Username: %s', 'uwp'), $user_data->user_login) . "</p>";
        $login_details .= '<p>' . sprintf(__('Password: %s', 'uwp'), $new_pass) . "</p>";

        return $login_details;

    }

    public function account() {

        $errors = $this->process_account($_POST);


        /* display error in registration form */
        if (is_wp_error($errors)) {
            echo '<div class="alert alert-error text-center">';
            echo $errors->get_error_message();
            echo '</div>';
        } else {
            echo '<div class="alert alert-success text-center">';
            echo __('Account updated successfully.', 'uwp');
            echo '</div>';
        }
    }

    public function process_account($data = array()) {

        $current_user_id = get_current_user_id();
        if (!$current_user_id) {
            return false;
        }

        $errors = new WP_Error();

        if( ! isset( $data['uwp_account_nonce'] ) || ! wp_verify_nonce( $data['uwp_account_nonce'], 'uwp-account-nonce' ) ) {
            return false;
        }

        do_action('uwp_before_validate_account');

        $result = $this->validate_fields($data, 'account');

        do_action('uwp_after_validate_account', $result);

        if (is_wp_error($result)) {
            return $result;
        }

        if ($errors->get_error_code())
            return $errors;


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

        //todo: find a way to fix auth cookie headers already sent by problem
        $user_id = wp_update_user( $args );

        if (!$user_id) {
            $errors->add('registerfail', sprintf(__('<strong>Error</strong>: Something went wrong.', 'uwp'), get_option('admin_email')));
            return $errors;
        }

        $result = $this->uwp_save_user_extra_fields($user_id, $result, 'account');

        if (!$result) {
            $errors->add('something_wrong', __('<strong>Error</strong>: Something went wrong. Please contact site admin.', 'uwp'));
        }

        if ($errors->get_error_code())
            return $errors;

        //todo: update account notification. some users maybe interested in that

        return true;

    }

    public function validate_fields($data, $type) {

        $errors = new WP_Error();

        global $wpdb;
        $table_name = $wpdb->prefix . 'uwp_custom_fields';
        $fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $table_name . " WHERE form_type = %s AND is_active = '1' ORDER BY sort_order ASC", array($type)));

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

                if (!$sanitized) {
                    // sanitize by field type
                    switch($field->field_type) {

                        case 'text':
                            $sanitized_value = sanitize_text_field($value);
                            break;

                        case 'email':
                            $sanitized_value = sanitize_email($value);
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

                //todo: move forgot form validation here


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

        if ($type == 'register' || ($type == 'account' && !empty( $data['uwp_account_password']))) {
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
            return true;
        } else {
            foreach($data as $key => $value) {
                update_user_meta($user_id, $key, $value);
            }
            return true;
        }
    }

    public function uwp_send_email( $message_type, $user_id, $login_details ) {

        global $uwp_options;
        $user_data = get_userdata($user_id);

        $login_page_id = $uwp_options['login_page'];
        if ($login_page_id) {
            $login_page_url = get_permalink($login_page_id);
        } else {
            $login_page_url = wp_login_url();
        }

        $subject = "";
        $message = "";

        if ( $message_type == 'register' ) {
            $subject = $uwp_options['registration_success_email_subject'];
            $message = $uwp_options['registration_success_email_content'];
        } elseif ( $message_type == 'forgot' ) {
            $subject = $uwp_options['forgot_password_email_subject'];
            $message = $uwp_options['forgot_password_email_content'];
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

        $headers = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
        $headers .= "Reply-To: " . $site_email . "\r\n";
        $headers .= 'From: ' . $sitefromEmailName . ' <' . $sitefromEmail . '>' . "\r\n";

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

}