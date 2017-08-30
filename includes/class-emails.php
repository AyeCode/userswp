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
     * @param       bool        $login_details      Login detail info. Applicable only for register form emails
     * @return      bool                            True when success. False when error.
     */
    public function send( $message_type, $user_id, $login_details = false )
    {
        $user_data = get_userdata($user_id);

        if (!$login_details) {
            $login_details = "";
        }

        $login_page_id = uwp_get_option('login_page', false);
        if ($login_page_id) {
            $login_page_url = get_permalink($login_page_id);
        } else {
            $login_page_url = wp_login_url();
        }

        $subject = "";
        $message = "";

        if ($message_type == 'register') {
            $subject = uwp_get_option('registration_success_email_subject', '');
            $message = uwp_get_option('registration_success_email_content', '');
        } elseif ($message_type == 'activate') {
            $subject = uwp_get_option('registration_activate_email_subject', '');
            $message = uwp_get_option('registration_activate_email_content', '');
        } elseif ($message_type == 'forgot') {
            $subject = uwp_get_option('forgot_password_email_subject', '');
            $message = uwp_get_option('forgot_password_email_content', '');
        } elseif ($message_type == 'reset') {
            $subject = uwp_get_option('reset_password_email_subject', '');
            $message = uwp_get_option('reset_password_email_content', '');
        } elseif ($message_type == 'change') {
            $subject = uwp_get_option('change_password_email_subject', '');
            $message = uwp_get_option('change_password_email_content', '');
        } elseif ($message_type == 'account') {
            $subject = uwp_get_option('account_update_email_subject', '');
            $message = uwp_get_option('account_update_email_content', '');
        } else {
            $subject = apply_filters('uwp_send_mail_subject', $subject, $message_type);
            $message = apply_filters('uwp_send_mail_message', $message, $message_type);
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
        $loginurl_link = '<a href="' . $loginurl . '">login</a>';

        $current_date = date_i18n('Y-m-d H:i:s', current_time('timestamp'));

        $site_email = get_option('admin_email');

        $site_name = stripslashes(get_option('blogname'));

        //user
        $user_name = $user_data->display_name;
        $user_email = $user_data->user_email;

        $search_array = array(
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
        $message = str_replace($search_array, $replace_array, $message);

        $search_array = array(
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
        $subject = str_replace($search_array, $replace_array, $subject);

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
            $log_message = sprintf(
                __("Email from UsersWP failed to send.\nMessage type: %s\nSend time: %s\nTo: %s\nSubject: %s\n\n", 'userswp'),
                $message_type,
                date_i18n('F j Y H:i:s', current_time('timestamp')),
                $to,
                $subject
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

        $login_page_id = uwp_get_option('login_page', false);
        if ($login_page_id) {
            $login_page_url = get_permalink($login_page_id);
        } else {
            $login_page_url = wp_login_url();
        }

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
        $loginurl_link = '<a href="' . $loginurl . '">login</a>';

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
            $log_message = sprintf(
                __("Email from UsersWP failed to send.\nMessage type: %s\nSend time: %s\nTo: %s\nSubject: %s\n\n", 'userswp'),
                $message_type,
                date_i18n('F j Y H:i:s', current_time('timestamp')),
                $to,
                $subject
            );
            uwp_error_log($log_message);
            return false;
        } else {
            return true;
        }
    }

}