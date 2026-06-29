<?php
/**
 * Invite codes helper functions.
 *
 * @since      1.2.66
 * @package    userswp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get the invite codes table name.
 *
 * @since 1.2.66
 * @return string Table name with prefix.
 */
function uwp_invite_code_table_name() {
	global $wpdb;
	return $wpdb->prefix . 'uwp_invite_codes';
}

/**
 * Validate an invite code string.
 *
 * Checks that the code consists of valid characters and is 1-32 chars long.
 * Does NOT check DB existence or expiry — use uwp_validate_invite_code() for that.
 *
 * @since 1.2.66
 * @param string $code Raw code string.
 * @return string|false Sanitized uppercase code, or false if invalid format.
 */
function uwp_sanitize_invite_code( $code ) {
	$code = trim( (string) $code );
	if ( '' === $code || strlen( $code ) > 32 ) {
		return false;
	}
	$code = preg_replace( '/[^A-Za-z0-9]/', '', $code );
	if ( '' === $code ) {
		return false;
	}
	return strtoupper( $code );
}

/**
 * Validate an invite code against the database.
 *
 * @since 1.2.66
 * @param string $code    The invite code to validate.
 * @param int    $form_id Registration form ID (0 = any form).
 * @return array|WP_Error Array with code data on success, WP_Error on failure.
 */
function uwp_validate_invite_code( $code, $form_id = 0 ) {
	global $wpdb;

	$code = uwp_sanitize_invite_code( $code );
	if ( false === $code ) {
		return new WP_Error( 'uwp_invite_invalid_format', __( 'Invalid invite code format.', 'userswp' ) );
	}

	$table = uwp_invite_code_table_name();
	$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE code = %s", $code ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching

	if ( ! $row ) {
		return new WP_Error( 'uwp_invite_not_found', __( 'Invite code not found or invalid.', 'userswp' ) );
	}

	if ( ! $row->is_active ) {
		return new WP_Error( 'uwp_invite_inactive', __( 'This invite code is no longer active.', 'userswp' ) );
	}

	if ( $row->expiry_date && '0000-00-00 00:00:00' !== $row->expiry_date ) {
		$expiry = strtotime( $row->expiry_date );
		if ( $expiry && $expiry < time() ) {
			return new WP_Error( 'uwp_invite_expired', __( 'This invite code has expired.', 'userswp' ) );
		}
	}

	if ( $row->usage_limit > 0 && $row->usage_count >= $row->usage_limit ) {
		return new WP_Error( 'uwp_invite_exhausted', __( 'This invite code has reached its usage limit.', 'userswp' ) );
	}

	if ( $form_id > 0 && $row->form_id > 0 && (int) $row->form_id !== (int) $form_id ) {
		return new WP_Error( 'uwp_invite_wrong_form', __( 'This invite code is not valid for this registration form.', 'userswp' ) );
	}

	return (array) $row;
}

/**
 * Get a single invite code by its row ID.
 *
 * @since 1.2.66
 * @param int $id Row ID.
 * @return object|null Row object or null.
 */
function uwp_get_invite_code( $id ) {
	global $wpdb;
	$table = uwp_invite_code_table_name();
	return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", (int) $id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
}

/**
 * Generate a unique random invite code string.
 *
 * @since 1.2.66
 * @return string 10-char uppercase alphanumeric code.
 */
function uwp_generate_invite_code_string() {
	global $wpdb;
	$table = uwp_invite_code_table_name();

	$chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // no 0/O/1/I to avoid confusion.
	$max   = strlen( $chars ) - 1;

	do {
		$code = '';
		for ( $i = 0; $i < 10; $i++ ) {
			$code .= $chars[ wp_rand( 0, $max ) ];
		}
		$exists = $wpdb->get_var( $wpdb->prepare( "SELECT code FROM {$table} WHERE code = %s", $code ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
	} while ( $exists );

	return $code;
}

/**
 * Create an invite code in the database.
 *
 * @since 1.2.66
 * @param array $args {
 *     Arguments for creating a code.
 *     @type int    $created_by  User ID of creator (0 for admin).
 *     @type int    $form_id     Registration form ID (0 for any form).
 *     @type int    $usage_limit Max uses (0 = unlimited).
 *     @type string $expiry_date Expiry datetime (Y-m-d H:i:s) or empty.
 *     @type string $code        Predefined code string. If empty, auto-generates.
 *     @type int    $bulk_count  Generate this many codes at once. Default 1.
 * }
 * @return array|WP_Error Array of created code row IDs, or WP_Error.
 */
function uwp_create_invite_code( $args = array() ) {
	global $wpdb;

	$defaults = array(
		'created_by'  => 0,
		'form_id'     => 0,
		'usage_limit' => 1,
		'expiry_date' => '',
		'code'        => '',
		'bulk_count'  => 1,
	);

	$args = wp_parse_args( $args, $defaults );

	// Validate expiry date format before insertion.
	if ( ! empty( $args['expiry_date'] ) ) {
		$expiry_ts = strtotime( $args['expiry_date'] );
		if ( false === $expiry_ts ) {
			return new WP_Error( 'uwp_invite_bad_expiry', __( 'Invalid expiry date format.', 'userswp' ) );
		}
	}

	$table = uwp_invite_code_table_name();
	$ids   = array();

	$bulk_count = max( 1, min( (int) $args['bulk_count'], 500 ) );

	for ( $i = 0; $i < $bulk_count; $i++ ) {
		$code = ! empty( $args['code'] ) ? uwp_sanitize_invite_code( $args['code'] ) : '';
		if ( empty( $code ) ) {
			$code = uwp_generate_invite_code_string();
		}

		$data = array(
			'code'        => $code,
			'created_by'  => absint( $args['created_by'] ),
			'form_id'     => absint( $args['form_id'] ),
			'usage_limit' => absint( $args['usage_limit'] ),
			'usage_count' => 0,
			'expiry_date' => ! empty( $args['expiry_date'] ) ? sanitize_text_field( $args['expiry_date'] ) : null,
			'is_active'   => 1,
			'created_at'  => current_time( 'mysql' ),
			'updated_at'  => current_time( 'mysql' ),
		);

		$inserted = $wpdb->insert(
			$table,
			$data,
			array( '%s', '%d', '%d', '%d', '%d', '%s', '%d', '%s', '%s' )
		);

		if ( false === $inserted ) {
			return new WP_Error( 'uwp_invite_insert_failed', __( 'Failed to create invite code.', 'userswp' ) );
		}

		$ids[] = $wpdb->insert_id;
	}

	return $ids;
}

/**
 * Record invite code usage after successful registration.
 *
 * Uses an atomic conditional UPDATE to prevent TOCTOU race conditions
 * on concurrent registrations with the same code.
 *
 * @since 1.2.66
 * @param int $code_id The invite code row ID.
 * @param int $user_id The newly registered user ID.
 * @return bool True on success, false on failure or if code exhausted.
 */
function uwp_use_invite_code( $code_id, $user_id ) {
	global $wpdb;

	$table = uwp_invite_code_table_name();

	// Idempotency check: read used_by once.
	$used_by_raw = $wpdb->get_var( $wpdb->prepare( "SELECT used_by FROM {$table} WHERE id = %d", (int) $code_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching

	if ( null === $used_by_raw ) {
		return false; // Code row doesn't exist.
	}

	$used_by = array();
	if ( ! empty( $used_by_raw ) ) {
		$used_by = array_map( 'absint', explode( ',', $used_by_raw ) );
	}

	// Already used by this user — idempotent.
	if ( in_array( (int) $user_id, $used_by, true ) ) {
		return true;
	}

	$new_used_by = implode( ',', array_merge( $used_by, array( (int) $user_id ) ) );

	// Atomic conditional update — only increments if limit not reached and code is active.
	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
	$rows = $wpdb->query( $wpdb->prepare( "UPDATE {$table} SET usage_count = usage_count + 1, used_by = %s, updated_at = %s WHERE id = %d AND (usage_limit = 0 OR usage_count < usage_limit) AND is_active = 1", $new_used_by, current_time( 'mysql' ), (int) $code_id ) );

	if ( false === $rows ) {
		return false;
	}

	if ( 0 === $rows ) {
		// Code exhausted, deactivated, or deleted between validation and now.
		return false;
	}

	return true;
}

/**
 * Get the registration page URL with an invite code pre-applied.
 *
 * @since 1.2.66
 * @param string $code The invite code.
 * @return string URL with code parameter.
 */
function uwp_get_invite_code_register_url( $code ) {
	$page_id = uwp_get_page_id( 'register_page', false );
	if ( ! $page_id ) {
		return home_url();
	}

	$url = get_permalink( $page_id );
	return add_query_arg( 'uwp_invite_code', rawurlencode( $code ), $url );
}

/**
 * Check if invite codes are required for a specific registration form.
 *
 * @since 1.2.66
 * @param int $form_id Registration form ID.
 * @return bool True if invite code required.
 */
function uwp_is_invite_code_required( $form_id ) {
	$required_forms = uwp_get_option( 'uwp_require_invite_code_forms', array() );
	if ( ! is_array( $required_forms ) ) {
		$required_forms = array();
	}
	return in_array( (int) $form_id, $required_forms, true );
}

/**
 * Get the maximum number of active invite codes a user can create.
 *
 * @since 1.2.66
 * @return int Maximum active codes per user.
 */
function uwp_get_max_user_invite_codes() {
	$max = uwp_get_option( 'uwp_invite_max_per_user', 10 );
	return max( 1, absint( $max ) );
}

/**
 * Count a user's currently active invite codes.
 *
 * @since 1.2.66
 * @param int $user_id User ID.
 * @return int Number of active codes.
 */
function uwp_count_user_invite_codes( $user_id ) {
	global $wpdb;
	$table = uwp_invite_code_table_name();
	return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE created_by = %d AND is_active = 1", (int) $user_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
}

/**
 * Get paginated invite codes for a user or all (admin use).
 *
 * @since 1.2.66
 * @param array $args {
 *     @type int    $user_id  Filter by creator. 0 = all.
 *     @type int    $per_page Items per page.
 *     @type int    $page     Page number (1-based).
 *     @type string $search   Search term for code.
 * }
 * @return object {
 *     @type array  $items Matched code rows.
 *     @type int    $total Total matching items.
 * }
 */
function uwp_get_invite_codes( $args = array() ) {
	global $wpdb;

	$defaults = array(
		'user_id'  => 0,
		'per_page' => 20,
		'page'     => 1,
		'search'   => '',
	);

	$args  = wp_parse_args( $args, $defaults );
	$table = uwp_invite_code_table_name();

	$where = array( '1=1' );

	if ( $args['user_id'] > 0 ) {
		$where[] = $wpdb->prepare( 'created_by = %d', (int) $args['user_id'] );
	}

	if ( ! empty( $args['search'] ) ) {
		$search  = '%' . $wpdb->esc_like( sanitize_text_field( $args['search'] ) ) . '%';
		$where[] = $wpdb->prepare( 'code LIKE %s', $search );
	}

	$where_sql = implode( ' AND ', $where );

	$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE {$where_sql}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching

	$offset   = ( max( 1, (int) $args['page'] ) - 1 ) * max( 1, (int) $args['per_page'] );
	$per_page = max( 1, (int) $args['per_page'] );

	$items = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} WHERE {$where_sql} ORDER BY created_at DESC LIMIT %d OFFSET %d", $per_page, $offset ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching

	return (object) array(
		'items' => $items ? $items : array(),
		'total' => $total,
	);
}
