<?php
/**
 * Invite Codes admin list table.
 *
 * @since      1.2.66
 * @package    userswp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Class UsersWP_Invite_Codes_Table.
 *
 * @since 1.2.66
 */
class UsersWP_Invite_Codes_Table extends WP_List_Table {

	/**
	 * Constructor.
	 *
	 * @since 1.2.66
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => __( 'Invite Code', 'userswp' ),
				'plural'   => __( 'Invite Codes', 'userswp' ),
				'ajax'     => false,
			)
		);
	}

	/**
	 * Get columns.
	 *
	 * @since 1.2.66
	 * @return array
	 */
	public function get_columns() {
		return array(
			'cb'          => '<input type="checkbox" />',
			'code'        => __( 'Code', 'userswp' ),
			'created_by'  => __( 'Created By', 'userswp' ),
			'usage'       => __( 'Used', 'userswp' ),
			'usage_limit' => __( 'Limit', 'userswp' ),
			'expiry_date' => __( 'Expires', 'userswp' ),
			'is_active'   => __( 'Active', 'userswp' ),
			'created_at'  => __( 'Created', 'userswp' ),
		);
	}

	/**
	 * Get sortable columns.
	 *
	 * @since 1.2.66
	 * @return array
	 */
	public function get_sortable_columns() {
		return array(
			'code'        => array( 'code', false ),
			'usage'       => array( 'usage_count', false ),
			'usage_limit' => array( 'usage_limit', false ),
			'expiry_date' => array( 'expiry_date', false ),
			'created_at'  => array( 'created_at', false ),
		);
	}

	/**
	 * Get bulk actions.
	 *
	 * @since 1.2.66
	 * @return array
	 */
	public function get_bulk_actions() {
		return array(
			'activate'   => __( 'Activate', 'userswp' ),
			'deactivate' => __( 'Deactivate', 'userswp' ),
			'delete'     => __( 'Delete', 'userswp' ),
		);
	}

	/**
	 * Process bulk actions.
	 *
	 * @since 1.2.66
	 */
	public function process_bulk_action() {
		$action = $this->current_action();

		if ( ! $action ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- bulk action requires invite_code_id[] array.
		if ( empty( $_REQUEST['invite_code_id'] ) ) {
			return;
		}

		check_admin_referer( 'bulk-' . $this->_args['plural'] );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- nonce verified above.
		$code_ids = isset( $_REQUEST['invite_code_id'] ) ? array_map( 'absint', (array) $_REQUEST['invite_code_id'] ) : array();

		if ( empty( $code_ids ) ) {
			return;
		}

		global $wpdb;
		$table = uwp_invite_code_table_name();
		$count = 0;

		switch ( $action ) {
			case 'delete':
				foreach ( $code_ids as $id ) {
					$result = $wpdb->delete( $table, array( 'id' => $id ), array( '%d' ) );
					if ( $result ) {
						++$count;
					}
				}
				break;

			case 'deactivate':
				foreach ( $code_ids as $id ) {
					$result = $wpdb->update( $table, array( 'is_active' => 0 ), array( 'id' => $id ), array( '%d' ), array( '%d' ) );
					if ( false !== $result ) {
						++$count;
					}
				}
				break;

			case 'activate':
				foreach ( $code_ids as $id ) {
					$result = $wpdb->update( $table, array( 'is_active' => 1 ), array( 'id' => $id ), array( '%d' ), array( '%d' ) );
					if ( false !== $result ) {
						++$count;
					}
				}
				break;
		}

		if ( $count > 0 ) {
			if ( 'delete' === $action ) {
				/* translators: %d: number of codes deleted */
				$msg = sprintf( _n( '%d invite code deleted.', '%d invite codes deleted.', $count, 'userswp' ), $count );
			} elseif ( 'deactivate' === $action ) {
				/* translators: %d: number of codes deactivated */
				$msg = sprintf( _n( '%d invite code deactivated.', '%d invite codes deactivated.', $count, 'userswp' ), $count );
			} else {
				/* translators: %d: number of codes activated */
				$msg = sprintf( _n( '%d invite code activated.', '%d invite codes activated.', $count, 'userswp' ), $count );
			}
			add_action(
				'admin_notices',
				function () use ( $msg ) {
					printf(
						'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
						esc_html( $msg )
					);
				}
			);
		}
	}

	/**
	 * Column checkbox.
	 *
	 * @since 1.2.66
	 * @param object $item Row object.
	 * @return string
	 */
	public function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="invite_code_id[]" value="%d" />', (int) $item->id );
	}

	/**
	 * Column: code.
	 *
	 * @since 1.2.66
	 * @param object $item Row object.
	 * @return string
	 */
	public function column_code( $item ) {
		$delete_url = wp_nonce_url(
			add_query_arg(
				array(
					'action'  => 'confirm_delete',
					'code_id' => (int) $item->id,
				),
				admin_url( 'admin.php?page=uwp_invite_codes' )
			),
			'uwp_confirm_delete_invite_code_' . (int) $item->id
		);

		$actions = array(
			'delete' => sprintf(
				'<a href="%s">%s</a>',
				esc_url( $delete_url ),
				esc_html__( 'Delete', 'userswp' )
			),
		);

		$copy_link = uwp_get_invite_code_register_url( $item->code );

		$code_html  = sprintf( '<strong>%s</strong>', esc_html( $item->code ) );
		$code_html .= sprintf(
			'&nbsp;<a href="#" class="uwp-copy-invite-link" data-link="%s" title="%s"><i class="fas fa-link"></i></a>',
			esc_attr( $copy_link ),
			esc_attr__( 'Copy registration link', 'userswp' )
		);

		return $code_html . $this->row_actions( $actions );
	}

	/**
	 * Column: created_by.
	 *
	 * @since 1.2.66
	 * @param object $item Row object.
	 * @return string
	 */
	public function column_created_by( $item ) {
		if ( empty( $item->created_by ) ) {
			return '<em>' . esc_html__( 'Admin', 'userswp' ) . '</em>';
		}

		$user = get_userdata( (int) $item->created_by );
		if ( $user ) {
			$name = $user->display_name;
			if ( empty( $name ) || $name === $user->user_login ) {
				$name = ! empty( $user->nickname ) && $user->nickname !== $user->user_login ? $user->nickname : '';
			}
			if ( empty( $name ) ) {
				$first_name = ! empty( $user->first_name ) ? trim( $user->first_name ) : '';
				$last_name  = ! empty( $user->last_name ) ? trim( $user->last_name ) : '';
				$name       = trim( $first_name . ' ' . $last_name );
			}
			if ( empty( $name ) ) {
				$name = $user->user_login;
			}

			return esc_html( $name );
		}

		return esc_html__( 'Unknown', 'userswp' );
	}

	/**
	 * Column: usage (usage_count / usage_limit).
	 *
	 * @since 1.2.66
	 * @param object $item Row object.
	 * @return string
	 */
	public function column_usage( $item ) {
		return (int) $item->usage_count;
	}

	/**
	 * Column: usage_limit.
	 *
	 * @since 1.2.66
	 * @param object $item Row object.
	 * @return string
	 */
	public function column_usage_limit( $item ) {
		if ( (int) $item->usage_limit <= 0 ) {
			return '&infin;'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		return (int) $item->usage_limit;
	}

	/**
	 * Column: expiry_date.
	 *
	 * @since 1.2.66
	 * @param object $item Row object.
	 * @return string
	 */
	public function column_expiry_date( $item ) {
		if ( empty( $item->expiry_date ) || '0000-00-00 00:00:00' === $item->expiry_date ) {
			return '<em>' . esc_html__( 'Never', 'userswp' ) . '</em>';
		}

		$timestamp = strtotime( $item->expiry_date );
		if ( $timestamp && $timestamp < time() ) {
			return '<span style="color:#d63638;">' . esc_html( mysql2date( get_option( 'date_format' ), $item->expiry_date ) ) . '</span>';
		}

		return esc_html( mysql2date( get_option( 'date_format' ), $item->expiry_date ) );
	}

	/**
	 * Column: is_active.
	 *
	 * @since 1.2.66
	 * @param object $item Row object.
	 * @return string
	 */
	public function column_is_active( $item ) {
		if ( $item->is_active ) {
			return '<span style="color:#00a32a;">' . esc_html__( 'Yes', 'userswp' ) . '</span>';
		}
		return '<span style="color:#d63638;">' . esc_html__( 'No', 'userswp' ) . '</span>';
	}

	/**
	 * Column: created_at.
	 *
	 * @since 1.2.66
	 * @param object $item Row object.
	 * @return string
	 */
	public function column_created_at( $item ) {
		$format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
		return esc_html( mysql2date( $format, $item->created_at ) );
	}

	/**
	 * Prepare table items.
	 *
	 * @since 1.2.66
	 */
	public function prepare_items() {
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable, 'code' );

		$this->process_bulk_action();

		$per_page     = $this->get_items_per_page( 'invite_codes_per_page', 20 );
		$current_page = $this->get_pagenum();
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- search is a read operation in WP_List_Table.
		$search = isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : '';

		$result = uwp_get_invite_codes(
			array(
				'per_page' => $per_page,
				'page'     => $current_page,
				'search'   => $search,
			)
		);

		$this->items = $result->items;

		$this->set_pagination_args(
			array(
				'total_items' => $result->total,
				'per_page'    => $per_page,
				'total_pages' => ceil( $result->total / $per_page ),
			)
		);
	}
}
