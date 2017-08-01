<?php
function uwp_wrap_notice($message, $type) {
    $notice = new Users_WP_Notices();
    return $notice->wrap_notice($message, $type);
}