<?php
/**
 * Uninstall UsersWP
 *
 * Uninstalling UsersWP deletes tables and plugin options.
 *
 * @package UsersWP
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb;

if ( !class_exists( 'Users_WP' ) ) {
    // Load plugin file.
    include_once( 'users_wp.php' );
}

if ( uwp_get_option('uninstall_erase_data') == '1' ) {
    $wpdb->hide_errors();
    
    // Delete options
    delete_option('uwp_settings');
    delete_option('uwp_activation_redirect');
    delete_option('uwp_flush_rewrite');
    //delete_option('uwp_db_version');

    // Drop tables.
    // Drop form fields table
    $table_name = $wpdb->prefix . 'uwp_form_fields';
    $sql = "DROP TABLE IF EXISTS $table_name";
    $wpdb->query($sql);

    // Drop form extras table
    $extras_table_name = $wpdb->prefix . 'uwp_form_extras';
    $sql = "DROP TABLE IF EXISTS $extras_table_name";
    $wpdb->query($sql);
    
    // Delete user meta for all users
    $meta_type  = 'user';
    $user_id    = 0; // This will be ignored, since we are deleting for all users.
    $meta_key   = 'uwp_usermeta';
    $meta_value = ''; // Also ignored. The meta will be deleted regardless of value.
    $delete_all = true;

    delete_metadata( $meta_type, $user_id, $meta_key, $meta_value, $delete_all );
}