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
	if ( $uwp_options['uninstall_erase_data'] == '1' ) {
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

		// Delete pages
		wp_delete_post( $uwp_options['register_page'], true );
		wp_delete_post( $uwp_options['login_page'], true );
		wp_delete_post( $uwp_options['profile_page'], true );
		wp_delete_post( $uwp_options['account_page'], true );
		wp_delete_post( $uwp_options['change_page'], true );
		wp_delete_post( $uwp_options['forgot_page'], true );
		wp_delete_post( $uwp_options['reset_page'], true );
		wp_delete_post( $uwp_options['users_page'], true );

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