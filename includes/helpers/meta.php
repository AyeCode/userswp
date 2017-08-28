<?php
add_action('init', 'uwp_init_meta_filters');
function uwp_init_meta_filters() {
    $meta = new UsersWP_Meta();
    add_action('user_register', array($meta, 'sync_usermeta'), 10, 1);
    add_action('delete_user', array($meta, 'delete_usermeta_for_user'));

    add_action('wp_login', array($meta, 'save_user_ip_on_login') ,10,2);
    add_filter('uwp_before_extra_fields_save', array($meta, 'save_user_ip_on_register'), 10, 3);
    add_filter('uwp_update_usermeta', array($meta, 'modify_privacy_value_on_update'), 10, 4);
    add_filter('uwp_get_usermeta', array($meta, 'modify_privacy_value_on_get'), 10, 5);
    add_filter('uwp_update_usermeta', array($meta, 'modify_datepicker_value_on_update'), 10, 3);
    add_filter('uwp_get_usermeta', array($meta, 'modify_datepicker_value_on_get'), 10, 5);
}
/**
 * Gets UsersWP setting value using key.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @param       string          $key        Setting Key.
 * @param       bool|string     $default    Default value.
 * @param       bool            $cache      Use cache to retrieve the value?.
 *
 * @return      string                      Setting Value.
 */
function uwp_get_option( $key = '', $default = false, $cache = true ) {
    $meta = new UsersWP_Meta();
    return $meta->get_option($key, $default, $cache);
}

/**
 * Updates UsersWP setting value using key.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @param       string|bool     $key        Setting Key.
 * @param       string          $value      Setting Value.
 *
 * @return      bool                        Update success or not?.
 */
function uwp_update_option( $key = false, $value = '') {
    $meta = new UsersWP_Meta();
    return $meta->update_option($key, $value);
}

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
function uwp_update_usermeta( $user_id = false, $key, $value ) {
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