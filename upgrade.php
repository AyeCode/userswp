<?php
/**
 * Upgrade related functions.
 *
 * @since 1.0.0
 * @package UsersWP
 */



// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Change account fields to not have uwp_account_ prefix.
 */
function uwp_upgrade_1200(){
	global $wpdb;
	$meta_table = get_usermeta_table_prefix() . 'uwp_usermeta';
	$fields_table = uwp_get_table_prefix() . 'uwp_form_fields';
	$cols = $wpdb->get_results( "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$meta_table'");

	if(!empty($cols)){
		$current_cols = array();
		foreach($cols as $col){
			$current_cols[] = $col->COLUMN_NAME;
		}
		foreach($cols as $col){
			if ( strpos( $col->COLUMN_NAME, 'uwp_account_' ) === 0 ) {
				$col_name  = sanitize_sql_orderby($col->COLUMN_NAME);
				$col_type  = $col->COLUMN_TYPE;
				$new_col_name  = in_array($col_name,$current_cols) ? str_ireplace("uwp_account_","",$col_name) : str_ireplace("uwp_account_","_",$col_name);

				$sql = "ALTER TABLE `{$meta_table}` CHANGE `$col_name` `$new_col_name` $col_type";

				$wpdb->query( $sql);
				$wpdb->update(
					$fields_table,
					array(
						'htmlvar_name' => $new_col_name,
					),
					array( 'htmlvar_name' =>  $col_name),
					array(
						'%s',
					),
					array( '%s' )
				);

			}
		}
	}

}
//uwp_upgrade_1200();