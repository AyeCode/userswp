<?php
/**
 * Template related functions
 *
 * This class defines all code necessary for UsersWP templates like login. register etc.
 *
 * @link       http://wpgeodirectory.com
 * @since      1.0.0
 *
 * @package    Users_WP
 * @subpackage Users_WP/includes
 */

/**
 * Define the templates functionality.
 *
 * @since      1.0.0
 * @package    Users_WP
 * @subpackage Users_WP/includes
 * @author     GeoDirectory Team <info@wpgeodirectory.com>
 */
class Users_WP_Templates {


    public function __construct() {

    }

    public function uwp_locate_template( $template ) {

//        global $wp_query;
//
//        if (!is_page()) {
//            return $template;
//        }
//
//        $current_page_id = $wp_query->query_vars['page_id'];
//
//        $register_page = esc_attr( get_option('uwp_register_page', false));
//        if ( $register_page && ((int) $register_page ==  $current_page_id ) ) {
//            $template = "register";
//        }
//
//        $login_page = esc_attr( get_option('uwp_login_page', false));
//        if ( $login_page && ((int) $login_page ==  $current_page_id ) ) {
//            $template = "login";
//        }
//
//        $forgot_pass_page = esc_attr( get_option('uwp_forgot_pass_page', false));
//        if ( $forgot_pass_page && ((int) $forgot_pass_page ==  $current_page_id ) ) {
//            $template = "forgot";
//        }
//
//        $account_page = esc_attr( get_option('uwp_account_page', false));
//        if ( $account_page && ((int) $account_page ==  $current_page_id ) ) {
//            $template = "account";
//        }
//
//        $user_profile_page = esc_attr( get_option('uwp_user_profile_page', false));
//        if ( $user_profile_page && ((int) $user_profile_page ==  $current_page_id ) ) {
//            $template = "profile";
//        }
//
//        $users_list_page = esc_attr( get_option('uwp_users_list_page', false));
//        if ( $users_list_page && ((int) $users_list_page ==  $current_page_id ) ) {
//            $template = "users";
//        }

        $plugin_path = dirname( dirname( __FILE__ ) );

        switch ($template) {
            case 'register':
                $template = locate_template(array("userswp/register.php"));
                if (!$template) {
                    $template = $plugin_path . '/templates/register.php';
                }
                $template = apply_filters('uwp_template_register', $template);
                return $template;
                break;

            case 'login':
                $template = locate_template(array("userswp/login.php"));
                if (!$template) {
                    $template = $plugin_path . '/templates/login.php';
                }
                $template = apply_filters('uwp_template_login', $template);
                return $template;
                break;

            case 'forgot':
                $template = locate_template(array("userswp/forgot.php"));
                if (!$template) {
                    $template = $plugin_path . '/templates/forgot.php';
                }
                $template = apply_filters('uwp_template_forgot', $template);
                return $template;
                break;

            case 'account':
                $template = locate_template(array("userswp/account.php"));
                if (!$template) {
                    $template = $plugin_path . '/templates/account.php';
                }
                $template = apply_filters('uwp_template_account', $template);
                return $template;
                break;

            case 'profile':
                $template = locate_template(array("userswp/profile.php"));
                if (!$template) {
                    $template = $plugin_path . '/templates/profile.php';
                }
                $template = apply_filters('uwp_template_profile', $template);
                return $template;
                break;

            case 'users':
                $template = locate_template(array("userswp/users.php"));
                if (!$template) {
                    $template = $plugin_path . '/templates/users.php';
                }
                $template = apply_filters('uwp_template_users', $template);
                return $template;
                break;
        }

        return false;
    }



}