<?php
function uwp_get_option( $key = '', $default = false, $cache = true ) {
    $meta = new Users_WP_Meta();
    return $meta->get_option($key, $default, $cache);
}

function uwp_update_option( $key = false, $value = '') {
    $meta = new Users_WP_Meta();
    return $meta->update_option($key, $value);
}

function uwp_get_usermeta( $user_id = false, $key = '', $default = false ) {
    $meta = new Users_WP_Meta();
    return $meta->get_usermeta($user_id, $key, $default);
}

function uwp_update_usermeta( $user_id = false, $key, $value ) {
    $meta = new Users_WP_Meta();
    return $meta->update_usermeta($user_id, $key, $value);
}

function uwp_get_usermeta_row($user_id = false) {
    $meta = new Users_WP_Meta();
    return $meta->get_usermeta_row($user_id);
}

/**
 * Deletes a UsersWP meta row using the user ID.
 *
 * @since   1.0.5
 * @package Users_WP
 * @param int|bool $user_id The User ID.
 * @return void.
 */
function uwp_delete_usermeta_row($user_id = false) {
    $meta = new Users_WP_Meta();
    $meta->delete_usermeta_row($user_id);
}