<?php
/**
 * UsersWP table related functions
 *
 * @since      1.0.0
 * @author     GeoDirectory Team <info@wpgeodirectory.com>
 */
class UsersWP_Tables {

	/**
	 * Creates UsersWP related tables.
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 * @return      void
	 */
	public function create_tables()
	{

		global $wpdb;

		$wpdb->hide_errors();

		// we may need to do some updates before dbDelta
		self::upgrade_1200();

		$collate = '';
		if ($wpdb->has_cap('collation')) {
			if (!empty($wpdb->charset)) $collate = "DEFAULT CHARACTER SET $wpdb->charset";
			if (!empty($wpdb->collate)) $collate .= " COLLATE $wpdb->collate";
		}

		/**
		 * Include any functions needed for upgrades.
		 *
		 * @since 1.0.0
		 */
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		$table_name = uwp_get_table_prefix() . 'uwp_form_fields';
		$form_fields = "CREATE TABLE " . $table_name . " (
							  id int(11) NOT NULL AUTO_INCREMENT,
							  form_type varchar(100) NULL,
							  data_type varchar(100) NULL,
							  field_type varchar(255) NOT NULL COMMENT 'text,checkbox,radio,select,textarea',
							  field_type_key varchar(255) NOT NULL,
							  site_title varchar(255) NULL DEFAULT NULL,
							  form_label varchar(255) NULL DEFAULT NULL,
							  help_text varchar(255) NULL DEFAULT NULL,
							  htmlvar_name varchar(255) NULL DEFAULT NULL,
							  default_value text NULL DEFAULT NULL,
							  sort_order int(11) NOT NULL,
							  option_values text NULL DEFAULT NULL,
							  is_active enum( '0', '1' ) NOT NULL DEFAULT '1',
							  placeholder_value varchar(255) NULL DEFAULT NULL,
							  for_admin_use enum( '0', '1' ) NOT NULL DEFAULT '0',
							  is_default enum( '0', '1' ) NOT NULL DEFAULT '0',
							  is_dummy enum( '0', '1' ) NOT NULL DEFAULT '0',
							  is_public enum( '0', '1', '2' ) NOT NULL DEFAULT '0',
							  is_required enum( '0', '1' ) NOT NULL DEFAULT '0',
							  is_register_field enum( '0', '1' ) NOT NULL DEFAULT '0',
							  is_search_field enum( '0', '1' ) NOT NULL DEFAULT '0',
							  is_register_only_field enum( '0', '1' ) NOT NULL DEFAULT '0',
							  required_msg varchar(255) NULL DEFAULT NULL,
							  show_in text NULL DEFAULT NULL,
							  user_roles text NULL DEFAULT NULL,
							  extra_fields text NULL DEFAULT NULL,
							  field_icon varchar(255) NULL DEFAULT NULL,
							  css_class varchar(255) NULL DEFAULT NULL,
							  decimal_point varchar( 10 ) NOT NULL,
							  validation_pattern varchar( 255 ) NOT NULL,
							  validation_msg text NULL DEFAULT NULL,
							  form_id int(11) NOT NULL DEFAULT 1,
							  user_sort enum( '0', '1' ) NOT NULL DEFAULT '0',
							  PRIMARY KEY  (id)
							  ) $collate";

		$form_fields = apply_filters('uwp_before_form_field_table_create', $form_fields);

		dbDelta($form_fields);

		$extras_table_name = uwp_get_table_prefix() . 'uwp_form_extras';

		$form_extras = "CREATE TABLE " . $extras_table_name . " (
									  id int(11) NOT NULL AUTO_INCREMENT,
									  form_type varchar(255) NOT NULL,
									  field_type varchar(255) NOT NULL COMMENT 'text,checkbox,radio,select,textarea',
									  site_htmlvar_name varchar(255) NOT NULL,
									  sort_order int(11) NOT NULL,
									  is_default enum( '0', '1' ) NOT NULL DEFAULT '0',
									  is_dummy enum( '0', '1' ) NOT NULL DEFAULT '0',
									  expand_custom_value int(11) NULL DEFAULT NULL,
									  searching_range_mode int(11) NULL DEFAULT NULL,
									  expand_search int(11) NULL DEFAULT NULL,
									  front_search_title varchar(255) CHARACTER SET utf8 NULL DEFAULT NULL,
									  front_css_class varchar(255) NULL DEFAULT NULL,
									  first_search_value int(11) NULL DEFAULT NULL,
									  first_search_text varchar(255) CHARACTER SET utf8 NULL DEFAULT NULL,
									  last_search_text varchar(255) CHARACTER SET utf8 NULL DEFAULT NULL,
									  search_min_value int(11) NULL DEFAULT NULL,
									  search_max_value int(11) NULL DEFAULT NULL,
									  search_diff_value int(11) NULL DEFAULT NULL,
									  search_condition varchar(100) NULL DEFAULT NULL,
									  field_input_type varchar(255) NULL DEFAULT NULL,
									  field_data_type varchar(255) NULL DEFAULT NULL,
									  form_id int(11) NOT NULL DEFAULT 1,
									  PRIMARY KEY  (id)
									) $collate AUTO_INCREMENT=1 ;";

		$form_extras = apply_filters('uwp_before_form_extras_table_create', $form_extras);

		dbDelta($form_extras);

		// Table for storing userswp usermeta
		$usermeta_table_name = get_usermeta_table_prefix() . 'uwp_usermeta';
		$user_meta = "CREATE TABLE " . $usermeta_table_name . " (
						user_id int(20) NOT NULL,
						user_ip varchar(20) NULL DEFAULT NULL,
						user_privacy text NULL DEFAULT NULL,
						tabs_privacy text NULL DEFAULT NULL,
						username varchar(255) NULL DEFAULT NULL,
						email varchar(255) NULL DEFAULT NULL,
						first_name varchar(255) NULL DEFAULT NULL,
						last_name varchar(255) NULL DEFAULT NULL,
						avatar_thumb varchar(255) NULL DEFAULT NULL,
						banner_thumb varchar(255) NULL DEFAULT NULL,
                        display_name varchar(255) NULL DEFAULT NULL,
                        user_url text NULL DEFAULT NULL,
                        bio text NULL DEFAULT NULL,
						PRIMARY KEY  (user_id)
						) $collate ";

		$user_meta = apply_filters('uwp_before_usermeta_table_create', $user_meta);

		dbDelta($user_meta);

		// profile tabs layout table
		$profile_tabs_table_name = uwp_get_table_prefix() . 'uwp_profile_tabs';
		$tabs_tbl_query = " CREATE TABLE " . $profile_tabs_table_name . " (
							  id int(11) NOT NULL AUTO_INCREMENT,
							  form_type varchar(100) NULL,
							  sort_order int(11) NOT NULL,
							  tab_layout varchar(100) NOT NULL,
							  tab_type varchar(100) NOT NULL,
							  tab_level int(11) NOT NULL,
							  tab_parent int(11) NOT NULL,
							  tab_privacy int(11) NOT NULL DEFAULT '0',
							  user_decided int(11) NOT NULL DEFAULT '0',
							  tab_name varchar(255) NOT NULL,
							  tab_icon varchar(255) NOT NULL,
							  tab_key varchar(255) NOT NULL,
							  tab_content text NULL DEFAULT NULL,
							  form_id int(11) NOT NULL DEFAULT 1,
							  PRIMARY KEY  (id)
							  ) $collate; ";

		$tabs_tbl_query = apply_filters('uwp_profile_tabs_table_create_query', $tabs_tbl_query);

		dbDelta($tabs_tbl_query);

		// user sorting options table
		$user_sorting_table_name = uwp_get_table_prefix() . 'uwp_user_sorting';
		$tabs_tbl_query = " CREATE TABLE " . $user_sorting_table_name . " (
							  id int(11) NOT NULL AUTO_INCREMENT,
							  data_type varchar(255) NOT NULL,
							  field_type varchar(255) NOT NULL,
							  site_title varchar(255) NOT NULL,
							  htmlvar_name varchar(255) NOT NULL,
							  field_icon varchar(255) NULL DEFAULT NULL,
						      sort_order int(11) NOT NULL DEFAULT '0',
							  tab_parent varchar(100) NOT NULL DEFAULT '0',
							  tab_level int(11) NOT NULL DEFAULT '0',
							  is_active int(11) NOT NULL DEFAULT '0',
							  is_default int(11) NOT NULL DEFAULT '0',
							  sort varchar(5) DEFAULT 'asc',
							  PRIMARY KEY  (id)
							  ) $collate; ";

		$tabs_tbl_query = apply_filters('uwp_user_sorting_table_create_query', $tabs_tbl_query);

		dbDelta($tabs_tbl_query);

	}

	/**
	 * Deleting the table whenever a blog is deleted
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 * @param       array       $tables     Tables to delete.
	 *
	 * @return      array                   Modified table array to delete
	 */
	public function drop_tables_on_delete_blog( $tables ) {
		global $wpdb;
		$tables[] = $wpdb->prefix . 'uwp_form_fields';
		$tables[] = $wpdb->prefix . 'uwp_form_extras';
		$tables[] = $wpdb->prefix . 'uwp_usermeta';
		$tables[] = $wpdb->prefix . 'uwp_profile_tabs';
		return $tables;
	}

	/**
	 * Returns the table prefix based on the installation type.
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 * @return      string      Table prefix
	 */
	public function get_table_prefix() {
		global $wpdb;
		return $wpdb->prefix;
	}

	/**
	 * Returns the user meta table prefix based on the installation type.
	 *
	 * @since       1.0.16
	 * @package     userswp
	 *
	 * @return      string      Table prefix
	 */
	public function get_usermeta_table_prefix() {
		global $wpdb;

		if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
			require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
		}

		// Network active.
		if ( is_plugin_active_for_network( 'userswp/userswp.php' ) || 1 == get_network_option('', 'uwp_is_network_active') ) {
			return $wpdb->base_prefix;
		} else {
			return $wpdb->prefix;
		}
	}

	/**
	 * Checks whether the column exists in the table.
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 * @param       string      $db             Table name.
	 * @param       string      $column         Column name.
	 *
	 * @return      bool
	 */
	public function column_exists($db, $column)
	{
		global $wpdb;
		$exists = false;
		$columns = $wpdb->get_col("show columns from $db");
		foreach ($columns as $c) {
			if ($c == $column) {
				$exists = true;
				break;
			}
		}
		return $exists;
	}

	/**
	 * Adds column if not exist in the table.
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 * @param       string      $db             Table name.
	 * @param       string      $column         Column name.
	 * @param       string      $column_attr    Column attributes.
	 *
	 * @return      bool|int                    True when success.
	 */
	public function add_column_if_not_exist($db, $column, $column_attr = "VARCHAR( 255 ) NOT NULL")
	{
		$excluded = uwp_get_excluded_fields();

		if (!in_array($column, $excluded)) {
			global $wpdb;
			$result = 0;// no rows affected
			if (!$this->column_exists($db, $column)) {
				if (!empty($db) && !empty($column))
					$result = $wpdb->query("ALTER TABLE `$db` ADD `$column`  $column_attr");
			}
			return $result;
		} else {
			return true;
		}
	}

	/**
	 * In v1.2.0 we removed some prefixes so they must be updated before dbDelta runs so to not duplicate columns.
	 *
	 * @since 1.2.0
	 */
	public function upgrade_1200(){
		// Only run if its an upgrade, not an install
		if(get_option('uwp_db_version')){
			global $wpdb;
			$meta_table = get_usermeta_table_prefix() . 'uwp_usermeta';
			$fields_table = uwp_get_table_prefix() . 'uwp_form_fields';
			$extras_table = uwp_get_table_prefix() . 'uwp_form_extras';
			$cols = $wpdb->get_results( "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$meta_table' AND  TABLE_SCHEMA ='$wpdb->dbname'");

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

						// alter the usermeta column
						$wpdb->query( $sql);

						// Update the fields table keys
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

						// Update the fields extras table keys
						$wpdb->update(
							$extras_table,
							array(
								'site_htmlvar_name' => $new_col_name,
							),
							array( 'site_htmlvar_name' =>  $col_name),
							array(
								'%s',
							),
							array( '%s' )
						);

					}
				}
			}

			// now change all htmlvar_names
			$wpdb->query( "UPDATE $fields_table SET htmlvar_name = REPLACE(htmlvar_name, 'uwp_account_', '') WHERE htmlvar_name LIKE 'uwp_account_%'");
			$wpdb->query( "UPDATE $fields_table SET htmlvar_name = REPLACE(htmlvar_name, 'uwp_change_', '') WHERE htmlvar_name LIKE 'uwp_change_%'");
			$wpdb->query( "UPDATE $fields_table SET htmlvar_name = REPLACE(htmlvar_name, 'uwp_reset_', '') WHERE htmlvar_name LIKE 'uwp_reset_%'");
			$wpdb->query( "UPDATE $fields_table SET htmlvar_name = REPLACE(htmlvar_name, 'uwp_forgot_', '') WHERE htmlvar_name LIKE 'uwp_forgot_%'");
			$wpdb->query( "UPDATE $fields_table SET htmlvar_name = REPLACE(htmlvar_name, 'uwp_login_', '') WHERE htmlvar_name LIKE 'uwp_login_%'");

			$wpdb->query( "UPDATE $fields_table SET htmlvar_name = 'avatar' WHERE htmlvar_name = 'uwp_avatar_file'");
			$wpdb->query( "UPDATE $fields_table SET htmlvar_name = 'banner' WHERE htmlvar_name = 'uwp_banner_file'");

			$wpdb->query( "UPDATE $extras_table SET site_htmlvar_name = REPLACE(site_htmlvar_name, 'uwp_account_', '') WHERE site_htmlvar_name LIKE 'uwp_account_%'");

		}

	}

}