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
        if (isset($_POST['uwp_register_submit'])) {
            $this->register();
        } elseif (isset($_POST['uwp_login_submit'])) {
            $this->login();
        } elseif (isset($_POST['uwp_forgot_submit'])) {
            $this->forgot();
        } elseif (isset($_POST['uwp_account_submit'])) {
            $this->account();
        }
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
            if ($_POST['auto_login']) {
                wp_redirect(home_url('/'));
                exit();
            } else {
                echo '<div class="alert alert-success text-center">';
                echo __('Account registered successfully.', 'users-wp');
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
            $errors->add('register_disabled', __('<strong>ERROR</strong>: User registration is currently not allowed.', 'users-wp'));
            return $errors;
        }

        $result = $this->validate_fields($data, 'register');

        if (is_wp_error($result)) {
            return $result;
        }

        if ($errors->get_error_code())
            return $errors;

        //todo: recaptcha check

        if ($errors->get_error_code())
            return $errors;

        $args = array(
            'user_login'   => $result['uwp_register_username'],
            'user_email'   => $result['uwp_register_email'],
            'user_pass'    => $result['password'],
            'display_name' => $result['uwp_register_first_name'] . ' ' . $result['uwp_register_last_name'],
            'first_name'   => $result['uwp_register_first_name'],
            'last_name'    => $result['uwp_register_last_name']
        );

        $user_id = wp_insert_user( $args );

        if (!$user_id) {
            $errors->add('registerfail', sprintf(__('<strong>Error</strong>: Something went wrong.', 'users-wp'), get_option('admin_email')));
            return $errors;
        }

        wp_new_user_notification($user_id, $data['user_pass']);


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

        //$login_page = esc_attr( get_option('uwp_login_page', false));

        $data = array(
            'username' => $_POST['username'],
            'password' => $_POST['password'],
            'remember_me' => $_POST['remember_me']
        );


        $errors = $this->process_login($data);


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

        $user_login = sanitize_user($data['username']);
        // Check the username
        if ($user_login == '') {
            $errors->add('empty_username', __('<strong>Error</strong>: Please enter a username.', 'users-wp'));
        } elseif (!validate_username($user_login)) {
            $errors->add('invalid_username', __('<strong>Error</strong>: This username is invalid because it uses illegal characters. Please enter a valid username.', 'users-wp'));
            $user_login = '';
        }

        if ($data['password'] == '') {
            $errors->add('empty_password', __('<strong>Error</strong>: Please enter your password.', 'users-wp'));
        }
        if ($errors->get_error_code())
            return $errors;

        if ($data['remember_me'] == 'forever') {
            $remember_me = true;
        } else {
            $remember_me = false;
        }
        $result = wp_signon(
            array(
                'user_login' => $user_login,
                'user_password' => $data['password'],
                'remember' => $remember_me
            ),
            false
        );

        if (is_wp_error($result)) {
            $errors->add('invalid_userorpass', __('<strong>Error</strong>: Invalid username or Password.', 'users-wp'));
            return $errors;
        } else {
            return true;
        }
    }

    public function forgot() {

        //$forgot_pass_page = esc_attr( get_option('uwp_forgot_pass_page', ''));

        $data = array(
            'email' => $_POST['email'],
        );

        $errors = $this->process_forgot($data);

        /* display error in registration form */
        if (is_wp_error($errors)) {
            echo '<div class="alert alert-error text-center">';
            echo $errors->get_error_message();
            echo '</div>';
        } else {
            echo '<div class="alert alert-success text-center">';
            echo __('Please check your email.', 'users-wp');
            echo '</div>';
        }

    }

    public function process_forgot($data) {

        $errors = new WP_Error();

        $email = sanitize_email($data['email']);

        // Check the e-mail address
        if ($data['email'] == '') {
            $errors->add('empty_email', __('<strong>Error</strong>: Please type your e-mail address.', 'users-wp'));
        }
        if (!is_email($email)) {
            $errors->add('invalid_email', __('<strong>Error</strong>: The email address isn&#8217;t correct.', 'users-wp'));
        }
        if (!email_exists($email)) {
            $errors->add('email_exists', __('<strong>Error</strong>: This email doesn\'t exists.', 'users-wp'));
        }

        if ($errors->get_error_code())
            return $errors;

        return true;
    }

    public function account() {
        $account_page = esc_attr( get_option('uwp_account_page', false));

        $data = array(
            'first_name' => $_POST['first_name'],
            'last_name' => $_POST['last_name'],
            'email' => $_POST['email'],
            'password' => $_POST['password'],
            'confirm_password' => $_POST['confirm_password']
        );


        $errors = $this->process_account($data);


        /* display error in registration form */
        if (is_wp_error($errors)) {
            echo '<div class="alert alert-error text-center">';
            echo $errors->get_error_message();
            echo '</div>';
        } else {
            echo '<div class="alert alert-success text-center">';
            echo __('Account updated successfully.', 'users-wp');
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


        $result = $this->validate_fields($data, 'register');

        if (is_wp_error($result)) {
            return $result;
        }


        if ($errors->get_error_code())
            return $errors;

        //todo: recaptcha check

        if ($errors->get_error_code())
            return $errors;

        $args = array(
            'ID' => $current_user_id,
            'user_email'   => $result['uwp_account_email'],
            'display_name' => $result['uwp_account_first_name'] . ' ' . $result['uwp_account_last_name'],
            'first_name'   => $result['uwp_account_first_name'],
            'last_name'    => $result['uwp_account_last_name']
        );

        if ($result['password']) {
            $args['user_pass'] = $result['password'];
        }

        $user_id = wp_update_user( $args );

        if (!$user_id) {
            $errors->add('registerfail', sprintf(__('<strong>Error</strong>: Something went wrong.', 'users-wp'), get_option('admin_email')));
            return $errors;
        }

        //todo: update account notification

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


                if ($field->is_required == 1 && $sanitized_value == '') {
                    $errors->add('empty_'.$field->htmlvar_name, __('<strong>Error</strong>: '.$field->site_title.' cannot be empty.', 'users-wp'));
                }

                if ($field->field_type == 'email' && !is_email($sanitized_value)) {
                    $errors->add('invalid_email', __('<strong>Error</strong>: The email address isn&#8217;t correct.', 'users-wp'));
                }

                //register email
                if ($field->htmlvar_name == 'uwp_register_email' && email_exists($sanitized_value)) {
                    $errors->add('email_exists', __('<strong>Error</strong>: This email is already registered, please choose another one.', 'users-wp'));
                }

                // Check the username
                if ($field->htmlvar_name == 'uwp_register_username') {
                    if (!validate_username($sanitized_value)) {
                        $errors->add('invalid_username', __('<strong>Error</strong>: This username is invalid because it uses illegal characters. Please enter a valid username.', 'users-wp'));
                    }
                    if (username_exists($sanitized_value)) {
                        $errors->add('username_exists', __('<strong>Error</strong>: This username is already registered. Please choose another one.', 'users-wp'));
                    }
                }

                $validated_data[$field->htmlvar_name] = $sanitized_value;

            }
        }

        if ($type == 'register' || ($type == 'account' && empty( $data['password']))) {
            //check password
            if( empty( $data['password'] ) ) {
                $errors->add( 'empty_password', __( 'Please enter a password', 'users-wp' ) );
            }

            if ($data['password'] != $data['confirm_password']) {
                $errors->add('pass_match', __('ERROR: Passwords do not match.', 'users-wp'));
            }
            if (strlen($data['password']) < 7) {
                $errors->add('pass_match', __('ERROR: Password must be 7 characters or more.', 'users-wp'));
            }

            $validated_data['password'] = $data['password'];
        }


        if ($errors->get_error_code())
            return $errors;

        return $validated_data;
    }

}