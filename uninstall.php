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

$wpdb->hide_errors();

if ( is_multisite() ) {
	$main_site = get_network()->site_id;
	$sql       = "SELECT blog_id FROM $wpdb->blogs
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

function uwp_uninstall() {
	$uwp_options = get_option( 'uwp_settings' );
	if ( isset($uwp_options['uninstall_erase_data']) && $uwp_options['uninstall_erase_data'] == '1' ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'uwp_form_fields';
		$rows       = $wpdb->get_results( "select * from " . $table_name . "" );

		// Delete user meta for all users
		$meta_type  = 'user';
		$user_id    = 0; // This will be ignored, since we are deleting for all users.
		$meta_value = ''; // Also ignored. The meta will be deleted regardless of value.
		$delete_all = true;

		foreach ( $rows as $row ) {
			delete_metadata( $meta_type, $user_id, $row->htmlvar_name, $meta_value, $delete_all );
		}

		// Drop form fields table
		$table_name = $wpdb->prefix . 'uwp_form_fields';
		$sql        = "DROP TABLE IF EXISTS $table_name";
		$wpdb->query( $sql );

		// Drop form extras table
		$extras_table_name = $wpdb->prefix . 'uwp_form_extras';
		$sql               = "DROP TABLE IF EXISTS $extras_table_name";
		$wpdb->query( $sql );

		// Drop form profile tabs table
		$profile_table_name = $wpdb->prefix . 'uwp_profile_tabs';
		$sql                = "DROP TABLE IF EXISTS $profile_table_name";
		$wpdb->query( $sql );

		// Drop user sorting table
		$sorting_table_name = $wpdb->prefix . 'uwp_user_sorting';
		$sql                = "DROP TABLE IF EXISTS $sorting_table_name";
		$wpdb->query( $sql );

		// Delete pages
		$pages = array( 'register_page', 'login_page', 'profile_page', 'account_page', 'change_page', 'forgot_page', 'reset_page', 'users_page', 'user_list_item_page');

		foreach ($pages as $page){
			if(isset($uwp_options[$page]) && !empty($uwp_options[$page])){
				wp_delete_post( $uwp_options[$page], true );
			}
		}

		// Delete options
		delete_option( 'uwp_settings' );
		delete_option( 'uwp_activation_redirect' );
		delete_option( 'uwp_flush_rewrite' );
		delete_option( 'uwp_default_data_installed' );
		delete_option( 'uwp_db_version' );
	}
}

function uwp_drop_usermeta_table() {
	global $wpdb;
	// Drop usermeta table
	$meta_table_name = $wpdb->prefix . 'uwp_usermeta';
	$sql             = "DROP TABLE IF EXISTS $meta_table_name";
	$wpdb->query( $sql );
}