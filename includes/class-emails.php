<?php
/**
 * Mails related functions
 *
 * All UsersWP related mails are sent via this class.
 *
 * @since      1.0.0
 * @author     GeoDirectory Team <info@wpgeodirectory.com>
 */
class UsersWP_Mails {

    /**
     * All UsersWP user mails happen via this method.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       string      $message_type       Message type.
     * @param       int         $user_id            User ID.
     * @return      bool                            True when success. False when error.
     */
    public function send( $message_type, $user_id)
    {
        $user_data = get_userdata($user_id);
        
        $extras = apply_filters('uwp_send_mail_extras', "", $message_type, $user_id);
        $subject = $this->uwp_get_mail_subject($message_type);
        $message = $this->uwp_get_mail_content($message_type);

        $contains_login_details_tag = false;
        if (strpos($message, '[#login_details#]') !== false) {
            $contains_login_details_tag = true;
        }
        
        if (!empty($subject)) {
            $subject = __(stripslashes_deep($subject), 'userswp');
        }

        if (!empty($message)) {
            $message = __(stripslashes_deep($message), 'userswp');
        }

        $user_email = $user_data->user_email;

        $message_search_array = array(
            '[#login_details#]',
        );
        $message_replace_array = array(
            $extras
        );
        $message = str_replace($message_search_array, $message_replace_array, $message);

        $message = $this->uwp_email_format_text($message, $user_id);

        // Applicable only for activate mails
        if ($message_type == 'activate' && !$contains_login_details_tag) {
            $user_data = get_userdata($user_id);
            global $wpdb;
            $key = wp_generate_password( 20, false );
            do_action( 'uwp_activation_key', $user_data->user_login, $key );

            global $wp_hasher;
            if ( empty( $wp_hasher ) ) {
                require_once ABSPATH . 'wp-includes/class-phpass.php';
                $wp_hasher = new PasswordHash( 8, true );
            }
            $hashed = $wp_hasher->HashPassword( $key );
            $wpdb->update( $wpdb->users, array( 'user_activation_key' => time().":".$hashed ), array( 'user_login' => $user_data->user_login ) );
            update_user_meta( $user_id, 'uwp_mod', 'email_unconfirmed' );

            $activation_link = add_query_arg(
                array(
                    'uwp_activate' => 'yes',
                    'key' => $key,
                    'login' => $user_data->user_login
                ),
                site_url()
            );
            
            $activate_message_search_array = array(
                '[#activation_link#]',
            );
            $activate_message_replace_array = array(
                esc_url($activation_link)
            );
            $message = str_replace($activate_message_search_array, $activate_message_replace_array, $message);
        }

        // Applicable only for forgot mails
        if ($message_type == 'forgot' && !$contains_login_details_tag) {

            $new_pass = "";
            $reset_link = "";

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
                if(!uwp_get_option('change_disable_password_nag')) {
                    update_user_meta($user_data->ID, 'default_password_nag', true); //Set up the Password change nag.
                }

            } else {
                $key = wp_generate_password(20, false);
                do_action('retrieve_password_key', $user_data->user_login, $key);

                if (empty($wp_hasher)) {
                    require_once ABSPATH . 'wp-includes/class-phpass.php';
                    $wp_hasher = new PasswordHash(8, true);
                }
                $hashed = $wp_hasher->HashPassword($key);
                $wpdb->update($wpdb->users, array('user_activation_key' => time() . ":" . $hashed), array('user_login' => $user_data->user_login));
                $reset_page = uwp_get_page_id('reset_page', false);
                if ($reset_page) {
                    $reset_link = add_query_arg(array(
                        'key' => $key,
                        'login' => rawurlencode($user_data->user_login),
                    ), get_permalink($reset_page));
                } else {
                    $reset_link = site_url("reset?key=$key&login=" . rawurlencode($user_data->user_login), 'login');
                }
            }

            $reset_message_search_array = array(
                '[#new_password#]',
                '[#reset_link#]',
            );
            $reset_message_replace_array = array(
                $new_pass,
                $reset_link
            );
            $message = str_replace($reset_message_search_array, $reset_message_replace_array, $message);
        }

        $subject = $this->uwp_email_format_text($subject, $user_id);

        $headers = $this->uwp_get_mail_headers($message_type, $user_id);

        $to = apply_filters('uwp_send_email_to', $user_email, $message_type, $user_id);

        $subject = apply_filters('uwp_send_email_subject', $subject, $message_type, $user_id);

        $message = apply_filters('uwp_send_email_message', $message, $message_type, $user_id);

        $headers = apply_filters('uwp_send_email_headers', $headers, $message_type, $user_id);

        $sent = wp_mail($to, $subject, $message, $headers);


        if (!$sent) {
            if (is_array($to)) {
                $to = implode(',', $to);
            }
            $err = $this->get_mail_errors();
            $log_message = sprintf(
                __("Email from UsersWP failed to send.\nMessage type: %s\nSend time: %s\nTo: %s\nSubject: %s\nError: %s\n\n", 'userswp'),
                $message_type,
                date_i18n('F j Y H:i:s', current_time('timestamp')),
                $to,
                $subject,
                $err
            );
            uwp_error_log($log_message);
            return false;
        } else {
            return true;
        }
    }

    /**
     * All UsersWP admin mails happen via this method.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       string      $message_type       Message type.
     * @param       int         $user_id            User ID. Not admin user ID.
     * @return      bool                            True when success. False when error.
     */
    public function send_admin_email( $message_type, $user_id)
    {

        $extras = apply_filters('uwp_send_admin_mail_extras', "", $message_type, $user_id);
        $subject = $this->uwp_get_mail_subject($message_type, true);
        $message = $this->uwp_get_mail_content($message_type, true);

        if (!empty($subject)) {
            $subject = __(stripslashes_deep($subject), 'userswp');
        }

        if (!empty($message)) {
            $message = __(stripslashes_deep($message), 'userswp');
        }

        $site_email = get_option('admin_email');

        $search_array = array(
            '[#extras#]',
        );
        $replace_array = array(
            $extras
        );
        $message = str_replace($search_array, $replace_array, $message);

        $message = $this->uwp_email_format_text($message, $user_id);

        $subject = $this->uwp_email_format_text($subject, $user_id);

        $headers = $this->uwp_get_mail_headers($message_type, $user_id);

        $to = apply_filters('uwp_send_admin_email_to', $site_email, $message_type, $user_id);

        $subject = apply_filters('uwp_send_admin_email_subject', $subject, $message_type, $user_id);

        $message = apply_filters('uwp_send_admin_email_message', $message, $message_type, $user_id);

        $headers = apply_filters('uwp_send_admin_email_headers', $headers, $message_type, $user_id);

        $sent = wp_mail($to, $subject, $message, $headers);

        if (!$sent) {
            if (is_array($to)) {
                $to = implode(',', $to);
            }
            $err = $this->get_mail_errors();
            $log_message = sprintf(
                __("Email from UsersWP failed to send.\nMessage type: %s\nSend time: %s\nTo: %s\nSubject: %s\nError: %s\n\n", 'userswp'),
                $message_type,
                date_i18n('F j Y H:i:s', current_time('timestamp')),
                $to,
                $subject,
                $err
            );
            uwp_error_log($log_message);
            return false;
        } else {
            return true;
        }
    }


    /**
     * Gets the original error message from phpmailer.
     *
     * @since       1.0.7
     * @package     userswp
     * @return      string                            Error messages.
     */
    public function get_mail_errors() {
        // wp mail debugging
        global $ts_mail_errors;
        global $phpmailer;

        if (!isset($ts_mail_errors)) $ts_mail_errors = array();

        if (isset($phpmailer)) {
            $ts_mail_errors[] = $phpmailer->ErrorInfo;
        }

        $out = json_encode($ts_mail_errors);

        return $out;
    }

    public static function uwp_email_format_text( $content, $user_id ) {

        $site_url = '<a href="' . home_url() . '">' . home_url() . '</a>';
        $site_name = html_entity_decode(stripslashes(get_option('blogname')) ,ENT_QUOTES);
        $login_url = '<a href="' . wp_login_url() . '">'.__('login', 'userswp').'</a>';

        $replace_array = array(
            '[#site_name_url#]' => $site_url,
            '[#site_name#]'     => $site_name,
            '[#from_name#]'     => $site_name,
            '[#login_url#]'     => $login_url,
            '[#from_email#]'    => get_option('admin_email'),
            '[#current_date#]'  => date_i18n('Y-m-d H:i:s', current_time('timestamp')),
        );

        if ( !empty( $user_id ) && $user_id > 0 ) {
            $user_data = get_userdata($user_id);
            $profile_link = apply_filters('uwp_profile_link', get_author_posts_url($user_id), $user_id);
            $replace_array = array_merge(
                $replace_array,
                array(
                    '[#to_name#]'         => $user_data->display_name,
                    '[#user_login#]'      => $user_data->user_login,
                    '[#user_name#]'       => $user_data->display_name,
                    '[#username#]'        => $user_data->user_login,
                    '[#profile_link#]'    => $profile_link,
                )
            );
        }

        $replace_array = apply_filters( 'uwp_email_format_text', $replace_array, $content, $user_id );

        foreach ( $replace_array as $key => $value ) {
            $content = str_replace( $key, $value, $content );
        }

        return apply_filters( 'uwp_email_content_replace', $content );
    }

    /**
     * Modifies the mail subject based on the admin notification type.
     *
     * @since   1.0.0
     * @package    userswp
     * @subpackage userswp/includes
     * @param string $type Notification type.
     * @param bool $is_admin Admin notification.
     * @return string Modified mail subject.
     */
    public function uwp_get_mail_subject($type, $is_admin = false) {
        $subject = '';
        switch ($type) {
            case "register":
                $default = UsersWP_Defaults::registration_success_email_subject();
                $subject = uwp_get_option('registration_success_email_subject');
                $subject = isset($subject) && !empty($subject) ? $subject : $default;
                break;
            case "activate":
                $default = UsersWP_Defaults::email_user_activation_subject();
                $subject = uwp_get_option('registration_activate_email_subject');
                $subject = isset($subject) && !empty($subject) ? $subject : $default;
                break;
            case "forgot":
                $default = UsersWP_Defaults::forgot_password_email_subject();
                $subject = uwp_get_option('forgot_password_email_subject');
                $subject = isset($subject) && !empty($subject) ? $subject : $default;
                break;
            case "reset":
                $default = UsersWP_Defaults::reset_password_email_subject();
                $subject = uwp_get_option('reset_password_email_subject');
                $subject = isset($subject) && !empty($subject) ? $subject : $default;
                break;
            case "change":
                $default = UsersWP_Defaults::change_password_email_subject();
                $subject = uwp_get_option('change_password_email_subject');
                $subject = isset($subject) && !empty($subject) ? $subject : $default;
                break;
            case "account":
                $default = UsersWP_Defaults::update_account_email_subject();
                $subject = uwp_get_option('account_update_email_subject');
                $subject = isset($subject) && !empty($subject) ? $subject : $default;
                break;
            case "register_admin":
                $default = UsersWP_Defaults::email_user_new_account_subject();
                $subject = uwp_get_option('registration_success_email_subject_admin');
                $subject = isset($subject) && !empty($subject) ? $subject : $default;
                break;
        }

        if($is_admin){
            return apply_filters('uwp_send_admin_mail_subject', $subject, $type);
        }

        return apply_filters('uwp_send_mail_subject', $subject, $type);
    }

    /**
     * Modifies the mail content based on the admin notification type.
     *
     * @since   1.0.0
     *
     * @package    userswp
     *
     * @subpackage userswp/includes
     *
     * @param bool $is_admin Admin notification.
     * @param string $type Notification type.
     *
     * @return string Modified mail content.
     */
    public function uwp_get_mail_content($type, $is_admin = false) {
        $content = '';
        switch ($type) {
            case "register":
                $default = UsersWP_Defaults::registration_success_email_body();
                $content = uwp_get_option('registration_success_email_content');
                $content = isset($content) && !empty($content) ? $content : $default;
                break;
            case "activate":
                $default = UsersWP_Defaults::email_user_activation_body();
                $content = uwp_get_option('registration_activate_email_content');
                $content = isset($content) && !empty($content) ? $content : $default;
                break;
            case "forgot":
                $default = UsersWP_Defaults::forgot_password_email_body();
                $content = uwp_get_option('forgot_password_email_content');
                $content = isset($content) && !empty($content) ? $content : $default;
                break;
            case "reset":
                $default = UsersWP_Defaults::reset_password_email_body();
                $content = uwp_get_option('reset_password_email_content');
                $content = isset($content) && !empty($content) ? $content : $default;
                break;
            case "change":
                $default = UsersWP_Defaults::change_password_email_body();
                $content = uwp_get_option('change_password_email_content');
                $content = isset($content) && !empty($content) ? $content : $default;
                break;
            case "account":
                $default = UsersWP_Defaults::update_account_email_body();
                $content = uwp_get_option('account_update_email_content');
                $content = isset($content) && !empty($content) ? $content : $default;
                break;
            case "register_admin":
                $default = UsersWP_Defaults::email_user_new_account_body();
                $content = uwp_get_option('registration_success_email_content_admin');
                $content = isset($content) && !empty($content) ? $content : $default;
                break;
        }

        if($is_admin){
            return apply_filters('uwp_send_admin_mail_message', $content, $type);
        }

        return apply_filters('uwp_send_mail_message', $content, $type);
    }

    public static function uwp_get_mail_headers( $email_type = '', $user_id ) {
        $sitefromEmail = get_option('admin_email');
        $sitefromEmailName = html_entity_decode(stripslashes(get_option('blogname')) ,ENT_QUOTES);
        $site_email = get_option('admin_email');

        $headers = array();
        $headers[] = 'Content-type: text/html; charset=UTF-8';
        $headers[] = "Reply-To: " . $site_email;
        $headers[] = 'From: ' . $sitefromEmailName . ' <' . $sitefromEmail . '>';

        return apply_filters( 'uwp_email_headers', $headers, $email_type, $user_id );
    }

}