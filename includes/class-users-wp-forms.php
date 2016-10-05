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

        $data = array(
            'username' => $_POST['username'],
            'first_name' => $_POST['first_name'],
            'last_name' => $_POST['last_name'],
            'email' => $_POST['email'],
            'password' => $_POST['password'],
            'confirm_password' => $_POST['confirm_password'],
            'auto_login' => false
        );


        $errors = $this->process_register($data);


        /* display error in registration form */
        if (is_wp_error($errors)) {
            echo '<div class="alert alert-error text-center">';
            echo $errors->get_error_message();
            echo '</div>';
        } else {
            if ($data['auto_login']) {
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

        $user_first = sanitize_text_field($data['first_name']);
        $user_last  = sanitize_text_field($data['last_name']);
        $username = sanitize_text_field($data['username']);
        $email = sanitize_email($data['email']);

        //check the name
        if ($user_first == '') {
            $errors->add('empty_fname', __('<strong>Error</strong>: Please enter your first name.', 'users-wp'));
        }

        if ($user_last == '') {
            $errors->add('empty_lname', __('<strong>Error</strong>: Please enter your last name.', 'users-wp'));
        }
        // Check the username
        if ($username == '') {
            $errors->add('empty_username', __('<strong>Error</strong>: Please enter a username.', 'users-wp'));
        } elseif (!validate_username($username)) {
            $errors->add('invalid_username', __('<strong>Error</strong>: This username is invalid because it uses illegal characters. Please enter a valid username.', 'users-wp'));
            $username = '';
        } elseif (username_exists($username)) {
            $errors->add('username_exists', __('<strong>Error</strong>: This username is already registered. Please choose another one.', 'users-wp'));
        }

        // Check the e-mail address
        if ($data['email'] == '') {
            $errors->add('empty_email', __('<strong>Error</strong>: Please type your e-mail address.', 'users-wp'));
        } elseif (!is_email($email)) {
            $errors->add('invalid_email', __('<strong>Error</strong>: The email address isn&#8217;t correct.', 'users-wp'));
            $user_email = '';
        } elseif (email_exists($email)) {
            $errors->add('email_exists', __('<strong>Error</strong>: This email is already registered, please choose another one.', 'users-wp'));
        }

        //check password
        if( empty( $data['password'] ) ) {
            $errors->add( 'empty_password', __( 'Please enter a password', 'users-wp' ) );
        }

        if ($data['password'] != $data['confirm_password']) {
            $errors->add('pass_match', __('ERROR: Passwords do not match.', 'users-wp'));
        } elseif (strlen($data['password']) < 7) {
            $errors->add('pass_match', __('ERROR: Password must be 7 characters or more.', 'users-wp'));
        }

        if ($errors->get_error_code())
            return $errors;

        //todo: recaptcha check

        if ($errors->get_error_code())
            return $errors;

        $args = array(
            'user_login'   => $username,
            'user_email'   => $email,
            'user_pass'    => $data['password'],
            'display_name' => $user_first . ' ' . $user_last,
            'first_name'   => $user_first,
            'last_name'    => $user_last
        );

        $user_id = wp_insert_user( $args );

        if (!$user_id) {
            $errors->add('registerfail', sprintf(__('<strong>Error</strong>: Something went wrong.', 'users-wp'), get_option('admin_email')));
            return $errors;
        }

        wp_new_user_notification($user_id, $data['user_pass']);


        if ($data['auto_login']) {
            $login_data = array(
                'username' => $username,
                'password' => $data['password'],
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
            'rememberme' => $_POST['rememberme']
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

        if ($data['rememberme'] == 'forever') {
            $rememberme = true;
        } else {
            $rememberme = false;
        }
        $result = wp_signon(
            array(
                'user_login' => $user_login,
                'user_password' => $data['password'],
                'remember' => $rememberme
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


        $user_first = sanitize_text_field($data['first_name']);
        $user_last  = sanitize_text_field($data['last_name']);
        $email = sanitize_email($data['email']);

        //check the name
        if ($user_first == '') {
            $errors->add('empty_fname', __('<strong>Error</strong>: Please enter your first name.', 'users-wp'));
        }

        if ($user_last == '') {
            $errors->add('empty_lname', __('<strong>Error</strong>: Please enter your last name.', 'users-wp'));
        }

        // Check the e-mail address
        if ($data['email'] == '') {
            $errors->add('empty_email', __('<strong>Error</strong>: Please type your e-mail address.', 'users-wp'));
        } elseif (!is_email($email)) {
            $errors->add('invalid_email', __('<strong>Error</strong>: The email address isn&#8217;t correct.', 'users-wp'));
            $user_email = '';
        } elseif (email_exists($email)) {
            $errors->add('email_exists', __('<strong>Error</strong>: This email is already registered, please choose another one.', 'users-wp'));
        }

        //check password
        if( empty( $data['password'] ) ) {
            $password_change = false;
            // no password change
        } else {
            $password_change = true;
            if ($data['password'] != $data['confirm_password']) {
                $errors->add('pass_match', __('ERROR: Passwords do not match.', 'users-wp'));
            } elseif (strlen($data['password']) < 7) {
                $errors->add('pass_match', __('ERROR: Password must be 7 characters or more.', 'users-wp'));
            }
        }


        if ($errors->get_error_code())
            return $errors;

        //todo: recaptcha check

        if ($errors->get_error_code())
            return $errors;

        $args = array(
            'ID' => $current_user_id,
            'user_email'   => $email,
            'display_name' => $user_first . ' ' . $user_last,
            'first_name'   => $user_first,
            'last_name'    => $user_last
        );

        if ($password_change) {
            $args['user_pass'] = $data['password'];
        }

        $user_id = wp_update_user( $args );

        if (!$user_id) {
            $errors->add('registerfail', sprintf(__('<strong>Error</strong>: Something went wrong.', 'users-wp'), get_option('admin_email')));
            return $errors;
        }

        //todo: update account notification

        return true;

    }

}