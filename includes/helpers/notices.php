<?php
/**
 * This method gets fired during plugin activation.
 *
 * @since       1.0.0
 * @package     UsersWP
 * @return      void
 */
function uwp_wrap_notice($message, $type) {
    $notice = new Users_WP_Notices();
    return $notice->wrap_notice($message, $type);
}