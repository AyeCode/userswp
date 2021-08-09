<?php

/**
 * Gets UsersWP user meta value using key.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @param       int|bool        $user_id        User ID.
 * @param       string          $key            User meta Key.
 * @param       bool|string     $default        Default value.
 *
 * @return      string                          User meta Value.
 */
function uwp_get_usermeta( $user_id = false, $key = '', $default = false ) {
    $meta = new UsersWP_Meta();
    return $meta->get_usermeta($user_id, $key, $default);
}

/**
 * Updates UsersWP user meta value using key.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @param       int|bool        $user_id        User ID.
 * @param       string|bool     $key            User meta Key.
 * @param       string          $value          User meta Value.
 *
 * @return      bool                            Update success or not?.
 */
function uwp_update_usermeta( $user_id, $key, $value ) {
    $meta = new UsersWP_Meta();
    return $meta->update_usermeta($user_id, $key, $value);
}

/**
 * Gets UsersWP user meta row using user ID.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @param       int|bool            $user_id    User ID.
 *
 * @return      object|bool                     User meta row object.
 */
function uwp_get_usermeta_row($user_id = false) {
    $meta = new UsersWP_Meta();
    return $meta->get_usermeta_row($user_id);
}

/**
 * Deletes a UsersWP meta row using the user ID.
 *
 * @since       1.0.5
 * @package     UsersWP
 *
 * @param       int|bool            $user_id        User ID.
 *
 * @return      void
 */
function uwp_delete_usermeta_row($user_id = false) {
    $meta = new UsersWP_Meta();
    $meta->delete_usermeta_row($user_id);
}