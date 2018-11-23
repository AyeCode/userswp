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
    public function uwp_create_tables()
    {

        if ( get_option('uwp_db_version') == USERSWP_VERSION ) return;

        global $wpdb;

        $table_name = uwp_get_table_prefix() . 'uwp_form_fields';

        $wpdb->hide_errors();

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
									  first_search_value int(11) NULL DEFAULT NULL,
									  first_search_text varchar(255) CHARACTER SET utf8 NULL DEFAULT NULL,
									  last_search_text varchar(255) CHARACTER SET utf8 NULL DEFAULT NULL,
									  search_min_value int(11) NULL DEFAULT NULL,
									  search_max_value int(11) NULL DEFAULT NULL,
									  search_diff_value int(11) NULL DEFAULT NULL,
									  search_condition varchar(100) NULL DEFAULT NULL,
									  field_input_type varchar(255) NULL DEFAULT NULL,
									  field_data_type varchar(255) NULL DEFAULT NULL,
									  PRIMARY KEY  (id)
									) $collate AUTO_INCREMENT=1 ;";

        $form_extras = apply_filters('uwp_before_form_extras_table_create', $form_extras);

        dbDelta($form_extras);

        update_option('uwp_db_version', USERSWP_VERSION);

    }

    /**
     * Creates uwp_usermeta table which introduced in version 1.0.1
     *
     * @since       1.0.0
     * @package     userswp
     *
     * @return      void
     */
    public function uwp101_create_tables() {

        global $wpdb;

        $wpdb->hide_errors();

        $collate = '';
        if ($wpdb->has_cap('collation')) {
            if (!empty($wpdb->charset)) $collate = "DEFAULT CHARACTER SET $wpdb->charset";
            if (!empty($wpdb->collate)) $collate .= " COLLATE $wpdb->collate";
        }

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');


        // Table for storing userswp usermeta
        $usermeta_table_name = get_usermeta_table_prefix() . 'uwp_usermeta';
          $user_meta = "CREATE TABLE " . $usermeta_table_name . " (
						user_id int(20) NOT NULL,
						user_ip varchar(20) NULL DEFAULT NULL,
						user_privacy varchar(255) NULL DEFAULT NULL,
						uwp_account_username varchar(255) NULL DEFAULT NULL,
						uwp_account_email varchar(255) NULL DEFAULT NULL,
						uwp_account_first_name varchar(255) NULL DEFAULT NULL,
						uwp_account_last_name varchar(255) NULL DEFAULT NULL,
						uwp_account_avatar_thumb varchar(255) NULL DEFAULT NULL,
						uwp_account_banner_thumb varchar(255) NULL DEFAULT NULL,
                        uwp_account_display_name varchar(255) NULL DEFAULT NULL,
						PRIMARY KEY  (user_id)
						) $collate ";

        $user_meta = apply_filters('uwp_before_usermeta_table_create', $user_meta);

        dbDelta($user_meta);

        update_option('uwp_db_version', USERSWP_VERSION);
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
        if ( is_plugin_active_for_network( 'userswp/userswp.php' ) ) {
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

        $starts_with = "uwp_account_";

        if ((substr($column, 0, strlen($starts_with)) === $starts_with) && !in_array($column, $excluded)) {
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

}