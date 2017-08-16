<?php
/**
 * Wrap notice with a div.
 *
 * @since       1.0.0
 * @package     userswp
 * @return      string      Html string.
 */
function uwp_wrap_notice($message, $type) {
    $notice = new UsersWP_Notices();
    return $notice->wrap_notice($message, $type);
}