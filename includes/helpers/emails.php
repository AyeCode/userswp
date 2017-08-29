<?php
/**
 * Modifies the mail extras based on the notification type.
 *
 * @since   1.0.0
 * @package    userswp
 * @subpackage userswp/includes
 * @param string $extras Unmodified mail extras.
 * @param string $type Notification type.
 * @return string Modified mail extras.
 */
function uwp_send_register_admin_mail_extras($extras, $type, $user_id) {
    switch ($type) {
        case "register_admin":
            $user_data = get_userdata($user_id);
            $extras = __('<p><b>' . __('User Information :', 'userswp') . '</b></p>
            <p>' . __('First Name:', 'userswp') . ' ' . $user_data->first_name . '</p>
            <p>' . __('Last Name:', 'userswp') . ' ' . $user_data->last_name . '</p>
            <p>' . __('Username:', 'userswp') . ' ' . $user_data->user_login . '</p>
            <p>' . __('Email:', 'userswp') . ' ' . $user_data->user_email . '</p>');
            break;
    }
    return $extras;
}
add_filter('uwp_send_admin_mail_extras', 'uwp_send_register_admin_mail_extras', 10, 3);

/**
 * Modifies the admin mail subject based on the admin notification type.
 *
 * @since   1.0.0
 * @package    userswp
 * @subpackage userswp/includes
 * @param string $subject Unmodified admin mail subject.
 * @param string $type Admin notification type.
 * @return string Modified admin mail subject.
 */
function uwp_send_register_admin_mail_subject($subject, $type) {
    switch ($type) {
        case "register_admin":
            $subject = uwp_get_option('registration_success_email_subject_admin', '');
            break;
    }
    return $subject;
}
add_filter('uwp_send_admin_mail_subject', 'uwp_send_register_admin_mail_subject', 10, 2);

/**
 * Modifies the admin mail content based on the admin notification type.
 *
 * @since   1.0.0
 * @package    userswp
 * @subpackage userswp/includes
 * @param string $content Unmodified admin mail content.
 * @param string $type Admin notification type.
 * @return string Modified admin mail content.
 */
function uwp_send_register_admin_mail_content($content, $type) {
    switch ($type) {
        case "register_admin":
            $content = uwp_get_option('registration_success_email_content_admin', '');
            break;
    }
    return $content;
}
add_filter('uwp_send_admin_mail_message', 'uwp_send_register_admin_mail_content', 10, 2);