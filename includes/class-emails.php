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
        
        $login_page_url = wp_login_url();

        $subject = "";
        $message = "";
        
        $extras = apply_filters('uwp_send_mail_extras', "", $message_type, $user_id);
        $subject = apply_filters('uwp_send_mail_subject', $subject, $message_type);
        $message = apply_filters('uwp_send_mail_message', $message, $message_type);

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

        $sitefromEmail = get_option('admin_email');
        $sitefromEmailName = stripslashes(get_option('blogname'));


        $user_login = '';
        if ($user_id > 0 && $user_info = get_userdata($user_id)) {
            $user_login = $user_info->user_login;
        }

        $siteurl = home_url();
        $siteurl_link = '<a href="' . $siteurl . '">' . $siteurl . '</a>';
        $loginurl = $login_page_url;
        $loginurl_link = '<a href="' . $loginurl . '">'.__('login', 'userswp').'</a>';

        $current_date = date_i18n('Y-m-d H:i:s', current_time('timestamp'));

        $site_email = get_option('admin_email');

        $site_name = stripslashes(get_option('blogname'));

        //user
        $user_name = $user_data->display_name;
        $user_email = $user_data->user_email;

        $message_search_array = array(
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
        $message_replace_array = array(
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
            $extras
        );
        $message = str_replace($message_search_array, $message_replace_array, $message);


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
                $activation_link
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

        $subject_search_array = array(
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
        $subject_replace_array = array(
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
        $subject = str_replace($subject_search_array, $subject_replace_array, $subject);

        $headers = array();
        $headers[] = 'Content-type: text/html; charset=UTF-8';
        $headers[] = "Reply-To: " . $site_email;
        $headers[] = 'From: ' . $sitefromEmailName . ' <' . $sitefromEmail . '>';

        $to = $user_email;

        $to = apply_filters('uwp_send_email_to', $to, $message_type, $user_id);

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

        $login_page_url = wp_login_url();

        $extras = apply_filters('uwp_send_admin_mail_extras', "", $message_type, $user_id);
        $subject = apply_filters('uwp_send_admin_mail_subject', "", $message_type);
        $message = apply_filters('uwp_send_admin_mail_message', "", $message_type);

        if (!empty($subject)) {
            $subject = __(stripslashes_deep($subject), 'userswp');
        }

        if (!empty($message)) {
            $message = __(stripslashes_deep($message), 'userswp');
        }

        $sitefromEmail = apply_filters('uwp_send_mail_admin_email', get_option('admin_email'));
        $sitefromEmailName = apply_filters('uwp_send_mail_admin_blogname', stripslashes(get_option('blogname')));

        $siteurl = home_url();
        $siteurl_link = '<a href="' . $siteurl . '">' . $siteurl . '</a>';
        $loginurl = $login_page_url;
        $loginurl_link = '<a href="' . $loginurl . '">'.__('login', 'userswp').'</a>';

        $current_date = date_i18n('Y-m-d H:i:s', current_time('timestamp'));

        $site_email = get_option('admin_email');

        $site_name = stripslashes(get_option('blogname'));

        $search_array = array(
            '[#site_name_url#]',
            '[#site_name#]',
            '[#from_name#]',
            '[#login_url#]',
            '[#from_email#]',
            '[#current_date#]',
            '[#extras#]',
        );
        $replace_array = array(
            $siteurl_link,
            $sitefromEmailName,
            $site_name,
            $loginurl_link,
            $site_email,
            $current_date,
            $extras
        );
        $message = str_replace($search_array, $replace_array, $message);

        $search_array = array(
            '[#site_name_url#]',
            '[#site_name#]',
            '[#from_name#]',
            '[#from_email#]',
            '[#current_date#]'
        );
        $replace_array = array(
            $siteurl_link,
            $sitefromEmailName,
            $site_name,
            $site_email,
            $current_date
        );
        $subject = str_replace($search_array, $replace_array, $subject);

        $headers = array();
        $headers[] = 'Content-type: text/html; charset=UTF-8';
        $headers[] = "Reply-To: " . $site_email;
        $headers[] = 'From: ' . $sitefromEmailName . ' <' . $sitefromEmail . '>';

        $to = $site_email;

        $to = apply_filters('uwp_send_admin_email_to', $to, $message_type, $user_id);

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

}