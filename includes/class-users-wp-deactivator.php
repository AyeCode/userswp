<?php
/**
 * Fired during plugin deactivation
 *
 * @link       http://wpgeodirectory.com
 * @since      1.0.0
 *
 * @package    Users_WP
 * @subpackage Users_WP/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Users_WP
 * @subpackage Users_WP/includes
 * @author     GeoDirectory Team <info@wpgeodirectory.com>
 */
class Users_WP_Deactivator {

    /**
     * @since    1.0.0
     */
    public static function deactivate() {
        //todo: call the function here.
    }

    public function delete_settings() {
        delete_option('uwp_settings');
        delete_option('uwp_activation_redirect');
    }

    public function delete_uwp_tables() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'uwp_form_fields';
        $sql = "DROP TABLE IF EXISTS $table_name";
        $wpdb->query($sql);
        delete_option('uwp_db_version');
    }

    public function delete_uwp_user_meta() {
        $meta_type  = 'user';
        $user_id    = 0; // This will be ignored, since we are deleting for all users.
        $meta_key   = 'uwp_usermeta';
        $meta_value = ''; // Also ignored. The meta will be deleted regardless of value.
        $delete_all = true;

        delete_metadata( $meta_type, $user_id, $meta_key, $meta_value, $delete_all );
    }

}