<?php
/**
 * Fired during plugin activation
 *
 * @link       http://wpgeodirectory.com
 * @since      1.0.0
 *
 * @package    Users_WP
 * @subpackage Users_WP/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Users_WP
 * @subpackage Users_WP/includes
 * @author     GeoDirectory Team <info@wpgeodirectory.com>
 */
class Users_WP_Activator {

    /**
     * @since    1.0.0
     */
    public static function activate() {
        self::generate_pages();
        self::add_default_options();
    }

    public static function generate_pages() {
        self::uwp_create_page(esc_sql(_x('register', 'page_slug', 'users-wp')), 'uwp_register_page', __('Register', 'users-wp'), '[uwp_register]');
        self::uwp_create_page(esc_sql(_x('login', 'page_slug', 'users-wp')), 'uwp_login_page', __('Login', 'users-wp'), '[uwp_login]');
        self::uwp_create_page(esc_sql(_x('account', 'page_slug', 'users-wp')), 'uwp_account_page', __('Account', 'users-wp'), '[uwp_account]');
        self::uwp_create_page(esc_sql(_x('forgot', 'page_slug', 'users-wp')), 'uwp_forgot_pass_page', __('Forgot Password?', 'users-wp'), '[uwp_forgot]');
        self::uwp_create_page(esc_sql(_x('profile', 'page_slug', 'users-wp')), 'uwp_user_profile_page', __('Profile', 'users-wp'), '[uwp_profile]');
        self::uwp_create_page(esc_sql(_x('users', 'page_slug', 'users-wp')), 'uwp_users_list_page', __('Users', 'users-wp'), '[uwp_users]');
    }

    public static function add_default_options() {
        $forgot_password_subject = __('[#site_name#] - Your new password', 'users-wp');
        $forgot_password_content = __("<p>Dear [#client_name#],<p><p>You requested a new password for [#site_name_url#]</p><p>[#login_details#]</p><p>You can login here: [#login_url#]</p><p>Thank you,<br /><br />[#site_name_url#].</p>",'users-wp');

        $register_success_subject = __('Your Log In Details', 'users-wp');
        $register_success_content = __("<p>Dear [#client_name#],</p><p>You can log in  with the following information:</p><p>[#login_details#]</p><p>You can login here: [#login_url#]</p><p>Thank you,<br /><br />[#site_name_url#].</p>",'users-wp');

        update_option('uwp_forgot_password_subject', $forgot_password_subject);
        update_option('uwp_forgot_password_content', $forgot_password_content);
        update_option('uwp_register_success_subject', $register_success_subject);
        update_option('uwp_register_success_content', $register_success_content);
    }

    public static function uwp_create_page($slug, $option, $page_title = '', $page_content = '', $post_parent = 0, $status = 'publish') {
        global $wpdb, $current_user;

        $option_value = get_option($option);

        if ($option_value > 0) :
            if (get_post($option_value)) :
                // Page exists
                return;
            endif;
        endif;

        $page_found = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT ID FROM " . $wpdb->posts . " WHERE post_name = %s LIMIT 1;",
                array($slug)
            )
        );

        if ($page_found) :
            // Page exists
            if (!$option_value) update_option($option, $page_found);
            return;
        endif;

        $page_data = array(
            'post_status' => $status,
            'post_type' => 'page',
            'post_author' => $current_user->ID,
            'post_name' => $slug,
            'post_title' => $page_title,
            'post_content' => $page_content,
            'post_parent' => $post_parent,
            'comment_status' => 'closed'
        );
        $page_id = wp_insert_post($page_data);

        add_option($option, $page_id);

    }

}