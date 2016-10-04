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

    }

    public function delete_settings() {
        delete_option('uwp_user_profile_page');
        delete_option('uwp_register_page');
        delete_option('uwp_login_page');
        delete_option('uwp_account_page');
        delete_option('uwp_forgot_pass_page');
        delete_option('uwp_users_list_page');
    }

}