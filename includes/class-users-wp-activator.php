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
        self::load_dependencies();
        self::generate_pages();
        self::add_default_options();
        self::uwp_create_tables();
        self::uwp_create_default_fields();
        self::uwp_flush_rewrite_rules();

        add_option('uwp_activation_redirect', 1);
        add_option('uwp_flush_rewrite', 1);
    }

    public static function load_dependencies() {
        require_once dirname(dirname( __FILE__ )) . '/admin/settings/class-users-wp-form-builder.php';
    }

    public static function generate_pages() {
        self::uwp_create_page(esc_sql(_x('register', 'page_slug', 'uwp')), 'register_page', __('Register', 'uwp'), '[uwp_register]');
        self::uwp_create_page(esc_sql(_x('login', 'page_slug', 'uwp')), 'login_page', __('Login', 'uwp'), '[uwp_login]');
        self::uwp_create_page(esc_sql(_x('account', 'page_slug', 'uwp')), 'account_page', __('Account', 'uwp'), '[uwp_account]');
        self::uwp_create_page(esc_sql(_x('forgot', 'page_slug', 'uwp')), 'forgot_page', __('Forgot Password?', 'uwp'), '[uwp_forgot]');
        self::uwp_create_page(esc_sql(_x('reset', 'page_slug', 'uwp')), 'reset_page', __('Reset Password', 'uwp'), '[uwp_reset]');
        self::uwp_create_page(esc_sql(_x('profile', 'page_slug', 'uwp')), 'profile_page', __('Profile', 'uwp'), '[uwp_profile]');
        self::uwp_create_page(esc_sql(_x('users', 'page_slug', 'uwp')), 'users_page', __('Users', 'uwp'), '[uwp_users]');
    }

    public static function add_default_options() {

        $settings = get_option( 'uwp_settings', array());

        //general
        $settings['profile_no_of_items'] = '10';

        //register
        $settings['enable_register_password'] = '1';

        //login
        $settings['login_redirect_to'] = '';

        //profile
        $settings['enable_profile_header'] = '1';
        $settings['enable_profile_body'] = '1';
        $settings['enable_profile_posts_tab'] = '1';
        $settings['enable_profile_comments_tab'] = '1';
        $settings['profile_avatar_max_size'] = '5';

        //notifications

        $register_success_subject = __('Your Log In Details', 'uwp');
        $register_success_content = __("<p>Dear [#user_name#],</p><p>You can log in  with the following information:</p>[#login_details#]<p>You can login here: [#login_url#]</p><p>Thank you,<br /><br />[#site_name_url#].</p>" ,'uwp');

        $forgot_password_subject = __('[#site_name#] - Your new password', 'uwp');
        $forgot_password_content = __("<p>Dear [#user_name#],<p><p>You requested a new password for [#site_name_url#]</p>[#login_details#]<p>You can login here: [#login_url#]</p><p>Thank you,<br /><br />[#site_name_url#].</p>" ,'uwp');

        $reset_password_subject = __('[#site_name#] - Password has been reset', 'uwp');
        $reset_password_content = __("<p>Dear [#user_name#],<p><p>Your password has been reset</p><p>You can login here: [#login_url#]</p><p>Thank you,<br /><br />[#site_name_url#].</p>" ,'uwp');

        $settings['registration_success_email_subject'] = $register_success_subject;
        $settings['registration_success_email_content'] = $register_success_content;

        $settings['forgot_password_email_subject'] = $forgot_password_subject;
        $settings['forgot_password_email_content'] = $forgot_password_content;

        $settings['reset_password_email_subject'] = $reset_password_subject;
        $settings['reset_password_email_content'] = $reset_password_content;

        update_option( 'uwp_settings', $settings );

    }

    public static function uwp_create_page($slug, $option, $page_title = '', $page_content = '', $post_parent = 0, $status = 'publish') {
        global $wpdb, $current_user;

        $settings = get_option( 'uwp_settings', array());
        if (isset($settings[$option])) {
            $option_value = $settings[$option];
        } else {
            $option_value = false;
        }

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
            if (!$option_value) {
                $settings[$option] = $page_found;
                update_option( 'uwp_settings', $settings );
            }
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

        $settings[$option] = $page_id;
        update_option( 'uwp_settings', $settings );

    }

    public static function uwp_create_tables()
    {

        global $wpdb;

        $table_name = $wpdb->prefix . 'uwp_custom_fields';

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

        $custom_fields = "CREATE TABLE " . $table_name . " (
							  id int(11) NOT NULL AUTO_INCREMENT,
							  form_type varchar(100) NULL,
							  field_type varchar(255) NOT NULL COMMENT 'text,checkbox,radio,select,textarea',
							  field_type_key varchar(255) NOT NULL,
							  site_title varchar(255) NULL DEFAULT NULL,
							  help_text varchar(255) NULL DEFAULT NULL,
							  htmlvar_name varchar(255) NULL DEFAULT NULL,
							  default_value text NULL DEFAULT NULL,
							  sort_order int(11) NOT NULL,
							  option_values text NULL DEFAULT NULL,
							  is_active enum( '0', '1' ) NOT NULL DEFAULT '1',
							  is_default enum( '0', '1' ) NOT NULL DEFAULT '0',
							  is_required enum( '0', '1' ) NOT NULL DEFAULT '0',
							  is_register_field enum( '0', '1' ) NOT NULL DEFAULT '0',
							  required_msg varchar(255) NULL DEFAULT NULL,
							  show_in text NULL DEFAULT NULL,
							  extra_fields text NULL DEFAULT NULL,
							  field_icon varchar(255) NULL DEFAULT NULL,
							  css_class varchar(255) NULL DEFAULT NULL,
							  decimal_point varchar( 10 ) NOT NULL,
							  validation_pattern varchar( 255 ) NOT NULL,
							  validation_msg text NULL DEFAULT NULL,
							  PRIMARY KEY  (id)
							  ) $collate";

        $custom_fields = apply_filters('uwp_before_custom_field_table_create', $custom_fields);

        dbDelta($custom_fields);

    }

    public static function uwp_create_default_fields()
    {
        $form_builder = new Users_WP_Form_Builder();

        $fields = self::uwp_default_custom_fields();

        $fields = apply_filters('uwp_before_default_custom_fields_saved', $fields);

        foreach ($fields as $field_index => $field) {
            $form_builder->uwp_custom_field_save($field);
        }
    }

    public static function uwp_default_custom_fields(){

        $login = self::uwp_default_custom_fields_login();
        $forgot = self::uwp_default_custom_fields_forgot();
        $avatar = self::uwp_default_custom_fields_avatar();
        $banner = self::uwp_default_custom_fields_banner();
        $reset = self::uwp_default_custom_fields_reset();
        $account = self::uwp_default_custom_fields_account();

        $fields = array_merge($login, $forgot, $account, $avatar, $banner, $reset);

        $fields = apply_filters('uwp_default_custom_fields', $fields);

        return $fields;

    }

    public static function uwp_default_custom_fields_login(){

        $fields = array();

        $fields[] = array(
            'form_type' => 'login',
            'field_type' => 'text',
            'site_title' => __('Username', 'uwp'),
            'htmlvar_name' => 'username',
            'default_value' => '',
            'option_values' => '',
            'is_default' => '1',
            'is_required' => '1'
        );

        $fields[] = array(
            'form_type' => 'login',
            'field_type' => 'password',
            'site_title' => __('Password', 'uwp'),
            'htmlvar_name' => 'password',
            'default_value' => '',
            'option_values' => '',
            'is_default' => '1',
            'is_required' => '1'
        );

        $fields = apply_filters('uwp_default_custom_fields_login', $fields);

        return  $fields;
    }

    public static function uwp_default_custom_fields_forgot(){

        $fields = array();

        $fields[] = array(
            'form_type' => 'forgot',
            'field_type' => 'email',
            'site_title' => __('Email', 'uwp'),
            'htmlvar_name' => 'email',
            'default_value' => '',
            'option_values' => '',
            'is_default' => '1',
            'is_required' => '1'
        );

        $fields = apply_filters('uwp_default_custom_fields_forgot', $fields);

        return  $fields;
    }

    public static function uwp_default_custom_fields_avatar(){

        $fields = array();

        $fields[] = array(
            'form_type' => 'avatar',
            'field_type' => 'file',
            'site_title' => __('Avatar', 'uwp'),
            'htmlvar_name' => 'file',
            'default_value' => '',
            'option_values' => '',
            'is_default' => '1',
            'is_required' => '1',
            'extra_fields'        =>  array(
                'uwp_file_types'  =>  array(
                    'jpg',
                    'jpe',
                    'jpeg',
                    'gif',
                    'png'
                ),
            )
        );

        $fields = apply_filters('uwp_default_custom_fields_avatar', $fields);

        return  $fields;
    }

    public static function uwp_default_custom_fields_banner(){

        $fields = array();

        $fields[] = array(
            'form_type' => 'banner',
            'field_type' => 'file',
            'site_title' => __('Banner', 'uwp'),
            'htmlvar_name' => 'file',
            'default_value' => '',
            'option_values' => '',
            'is_default' => '1',
            'is_required' => '1',
            'extra_fields'        =>  array(
                'uwp_file_types'  =>  array(
                    'jpg',
                    'jpe',
                    'jpeg',
                    'gif',
                    'png'
                ),
            )
        );

        $fields = apply_filters('uwp_default_custom_fields_banner', $fields);

        return  $fields;
    }

    public static function uwp_default_custom_fields_reset(){

        $fields = array();

        $fields[] = array(
            'form_type' => 'reset',
            'field_type' => 'password',
            'site_title' => __('Password', 'uwp'),
            'htmlvar_name' => 'password',
            'default_value' => '',
            'option_values' => '',
            'is_default' => '1',
            'is_required' => '1'
        );

        $fields[] = array(
            'form_type' => 'reset',
            'field_type' => 'password',
            'site_title' => __('Confirm Password', 'uwp'),
            'htmlvar_name' => 'confirm_password',
            'default_value' => '',
            'option_values' => '',
            'is_default' => '1',
            'is_required' => '1'
        );

        $fields = apply_filters('uwp_default_custom_fields_reset', $fields);

        return  $fields;
    }

    public static function uwp_default_custom_fields_account(){

        $fields = array();

        $fields[] = array(
            'form_type' => 'account',
            'field_type' => 'text',
            'site_title' => __('First Name', 'uwp'),
            'htmlvar_name' => 'first_name',
            'default_value' => '',
            'option_values' => '',
            'is_default' => '1',
            'is_required' => '1',
            'is_register_field' => '1',
            'css_class' => 'uwp-half uwp-half-left',
        );

        $fields[] = array(
            'form_type' => 'account',
            'field_type' => 'text',
            'site_title' => __('Last Name', 'uwp'),
            'htmlvar_name' => 'last_name',
            'default_value' => '',
            'option_values' => '',
            'is_default' => '1',
            'is_required' => '1',
            'is_register_field' => '1',
            'css_class' => 'uwp-half uwp-half-right',
        );

        $fields[] = array(
            'form_type' => 'account',
            'field_type' => 'email',
            'site_title' => __('Email', 'uwp'),
            'htmlvar_name' => 'email',
            'default_value' => '',
            'option_values' => '',
            'is_default' => '1',
            'is_required' => '1',
            'is_register_field' => '1',
        );

        $fields[] = array(
            'form_type' => 'account',
            'field_type' => 'textarea',
            'site_title' => __('Bio', 'uwp'),
            'htmlvar_name' => 'bio',
            'default_value' => '',
            'option_values' => '',
            'is_default' => '1',
            'is_required' => '1'
        );

        $fields[] = array(
            'form_type' => 'account',
            'field_type' => 'text',
            'site_title' => __('Password', 'uwp'),
            'htmlvar_name' => 'password',
            'default_value' => '',
            'option_values' => '',
            'is_default' => '1',
            'is_required' => '1',
            'is_register_field' => '1'
        );

        $fields[] = array(
            'form_type' => 'account',
            'field_type' => 'text',
            'site_title' => __('Confirm Password', 'uwp'),
            'htmlvar_name' => 'confirm_password',
            'default_value' => '',
            'option_values' => '',
            'is_default' => '1',
            'is_required' => '1',
            'is_register_field' => '1'
        );

        $fields = apply_filters('uwp_default_custom_fields_account', $fields);

        return  $fields;
    }

    public static function uwp_flush_rewrite_rules() {
        flush_rewrite_rules();
    }

}