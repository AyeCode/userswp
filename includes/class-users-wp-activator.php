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
    }

    public static function generate_pages() {
        self::uwp_create_page(esc_sql(_x('register', 'page_slug', 'users-wp')), 'uwp_register_page', __('Register', 'users-wp'), '');
        self::uwp_create_page(esc_sql(_x('login', 'page_slug', 'users-wp')), 'uwp_login_page', __('Login', 'users-wp'), '');
        self::uwp_create_page(esc_sql(_x('account', 'page_slug', 'users-wp')), 'uwp_account_page', __('Account', 'users-wp'), '');
        self::uwp_create_page(esc_sql(_x('forgot', 'page_slug', 'users-wp')), 'uwp_forgot_pass_page', __('Forgot Password?', 'users-wp'), '');
        self::uwp_create_page(esc_sql(_x('profile', 'page_slug', 'users-wp')), 'uwp_user_profile_page', __('Profile', 'users-wp'), '');
        self::uwp_create_page(esc_sql(_x('users', 'page_slug', 'users-wp')), 'uwp_users_list_page', __('Users', 'users-wp'), '');
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