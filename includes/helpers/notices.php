<?php
/**
 * Wrap notice with a div.
 *
 * @since       1.0.0
 * @package     UsersWP
 * @return      string      Html string.
 */
function uwp_wrap_notice($message, $type) {
    $notice = new Users_WP_Notices();
    return $notice->wrap_notice($message, $type);
}