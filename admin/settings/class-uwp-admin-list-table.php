<?php
/**
 * The custom admin ajaxified tabling listing functionality of the plugin.
 *
 * @link       http://wpgeodirectory.com
 * @since      1.2.3.11
 *
 * @package    userswp
 * @subpackage userswp/admin/settings
 */

/**
 * The form builder functionality of the plugin.
 *
 * @package    userswp
 * @subpackage userswp/admin/settings
 * @author     GeoDirectory Team <info@wpgeodirectory.com>
 */

// WP_List_Table is not loaded automatically so we need to load it in our application
if ( ! class_exists ( 'WP_List_Table' ) ) {
	require_once ( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Create a new table class that will extend the WP_List_Table
 */
class UWP_Admin_List_Table extends WP_List_Table {
	/**
	 * Prepare the items for the table to process
	 *
	 * @return Void
	 */
	public function prepare_items() {
		$columns  = $this -> get_columns ();
		$hidden   = $this -> get_hidden_columns ();
		$sortable = $this -> get_sortable_columns ();

		$data = $this -> table_data ();
		usort ( $data , array ( &$this , 'sort_data' ) );

		$perPage     = 10;
		$currentPage = $this -> get_pagenum ();
		$totalItems  = count ( $data );

		$this -> set_pagination_args ( array (
			'total_items' => $totalItems ,
			'per_page'    => $perPage
		) );

		$data = array_slice ( $data , ( ( $currentPage - 1 ) * $perPage ) , $perPage );

		$this -> _column_headers = array ( $columns , $hidden , $sortable );
		$this -> items           = $data;
	}

	/**
	 * Override the parent columns method. Defines the columns to use in your listing table
	 *
	 * @return Array
	 */
	public function get_columns() {
		$columns = array (
			'title'      => __ ( 'Title' , 'userwp' ) ,
			'id'         => __ ( 'ID' , 'userswp' ) ,
			'user_role'  => __ ( 'User Role' , 'userwp' ) ,
			'reg_action' => __ ( 'Registration Action' , 'userwp' ) ,
		);

		return $columns;
	}

	/**
	 * Define which columns are hidden
	 *
	 * @return Array
	 */
	public function get_hidden_columns() {
		return array ();
	}

	/**
	 * Define the sortable columns
	 *
	 * @return Array
	 */
	public function get_sortable_columns() {
		return array ( 'title' => array ( 'title' , false ) , 'id' => array ( 'id' , false ) );
	}

	/**
	 * Get the table data
	 *
	 * @return Array
	 */
	private function table_data() {
		$data           = array ();
		$register_forms = uwp_get_option ( 'multiple_registration_forms' );
		foreach ( $register_forms as $register_form ) {
			$data[] = array (
				'title'      => $register_form[ 'title' ] ,
				'id'         => $register_form[ 'id' ] ,
				'user_role'  => isset( $register_form[ 'user_role' ] ) ? $register_form[ 'user_role' ] : '-' ,
				'reg_action' => isset( $register_form[ 'reg_action' ] ) ? $register_form[ 'reg_action' ] : '-' ,
			);
		}

		return $data;
	}

	/**
	 * Define what data to show on each column of the table
	 *
	 * @param   Array   $item         Data
	 * @param   String  $column_name  - Current column name
	 *
	 * @return Mixed
	 */
	public function column_default( $item , $column_name ) {
		switch ( $column_name ) {
			case 'title':
			case 'id':
			case 'user_role':
			case 'reg_action':
				return $item[ $column_name ];

			default:
				return print_r ( $item , true );
		}
	}

	public function column_title( $item ) {

		$edit_link = admin_url ( 'admin.php?page=uwp_user_types&form=' . $item[ 'id' ] );
		$view_link = get_permalink ( $item[ 'id' ] );
		$output    = '';

		// Title.
		$output .= '<strong><a href="' . esc_url ( $edit_link ) . '" class="row-title">' . esc_html ( $item[ 'title' ] ) . '</a></strong>';

		// Get actions.
		$actions = array (
			'edit' => '<a class="" href="' . $edit_link . '">' . esc_html__ ( 'Edit' , 'my_plugin' ) . '</a>' ,
			'view' => '<a
				class="register-form-remove"
				data-id="' . $item[ "id" ] . '"
				data-nonce="' . wp_create_nonce ( 'uwp-delete-register-form-nonce' ) . '"
				href="#">' . esc_html__ ( 'Delete' , 'my_plugin' ) . '</a>' ,
		);

		$row_actions = array ();

		foreach ( $actions as $action => $link ) {
			$row_actions[] = '<span class="' . esc_attr ( $action ) . '">' . $link . '</span>';
		}

		$output .= '<div class="row-actions">' . implode ( ' | ' , $row_actions ) . '</div>';

		return $output;
	}

	/**
	 * Allows you to sort the data by the variables set in the $_GET
	 *
	 * @return Mixed
	 */
	private function sort_data( $a , $b ) {
// Set defaults
		$orderby = 'title';
		$order   = 'asc';

// If orderby is set, use this as the sort column
		if ( ! empty( $_GET[ 'orderby' ] ) ) {
			$orderby = $_GET[ 'orderby' ];
		}

// If order is set use this as the order
		if ( ! empty( $_GET[ 'order' ] ) ) {
			$order = $_GET[ 'order' ];
		}


		$result = strcmp ( $a[ $orderby ] , $b[ $orderby ] );

		if ( $order === 'asc' ) {
			return $result;
		}

		return - $result;
	}
}
