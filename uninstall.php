<?php
/**
 * Uninstall UsersWP
 *
 * Uninstalling UsersWP deletes tables and plugin options.
 *
 * @package userswp
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb;

if ( !class_exists( 'UsersWP' ) ) {
    // Load plugin file.
    include_once( 'userswp.php' );
}

$wpdb->hide_errors();

if (is_multisite()) {
    $main_site = get_network()->site_id;
    $sql = "SELECT blog_id FROM $wpdb->blogs
                WHERE archived = '0' AND spam = '0'
                AND deleted = '0'";

    $blog_ids = $wpdb->get_col( $sql );

    foreach ( $blog_ids as $blog_id ) {
        switch_to_blog( $blog_id );
        uwp_uninstall();
    }
    switch_to_blog( $main_site );
    uwp_drop_usermeta_table();
    restore_current_blog();
} else {
    uwp_uninstall();
    uwp_drop_usermeta_table();
}

function uwp_uninstall(){
    if ( uwp_get_option('uninstall_erase_data') == '1' ) {
        global $wpdb;

        // Delete options
        delete_option('uwp_settings');
        delete_option('uwp_activation_redirect');
        delete_option('uwp_flush_rewrite');
        delete_option('uwp_default_data_installed');
        delete_option('uwp_db_version');


        $table_name = uwp_get_table_prefix() . 'uwp_form_fields';
        $rows = $wpdb->get_results("select * from " . $table_name . "");

        // Delete user meta for all users
        $meta_type  = 'user';
        $user_id    = 0; // This will be ignored, since we are deleting for all users.
        $meta_key   = 'uwp_usermeta';
        $meta_value = ''; // Also ignored. The meta will be deleted regardless of value.
        $delete_all = true;

        foreach ($rows as $row) {
            delete_metadata( $meta_type, $user_id, $row->htmlvar_name, $meta_value, $delete_all );
        }

        // Drop form fields table
        $table_name = uwp_get_table_prefix() . 'uwp_form_fields';
        $sql = "DROP TABLE IF EXISTS $table_name";
        $wpdb->query($sql);

        // Drop form extras table
        $extras_table_name = uwp_get_table_prefix() . 'uwp_form_extras';
        $sql = "DROP TABLE IF EXISTS $extras_table_name";
        $wpdb->query($sql);
    }
}

function uwp_drop_usermeta_table(){
    global $wpdb;
    if ( uwp_get_option('uninstall_erase_data') == '1' ) {
        // Drop usermeta table
        $meta_table_name = uwp_get_table_prefix() . 'uwp_usermeta';
        $sql = "DROP TABLE IF EXISTS $meta_table_name";
        $wpdb->query($sql);
    }
}