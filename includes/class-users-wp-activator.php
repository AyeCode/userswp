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

        $installed_ver = get_option( "uwp_db_version" );
        
        if (!get_option('uwp_default_data_installed')) {
            self::load_dependencies();
            self::generate_pages();
            self::add_default_options();
            self::uwp_create_tables();
            self::uwp101_create_tables();
            self::uwp_insert_usermeta();
            self::uwp_create_default_fields();
            self::uwp_insert_form_extras();
            self::uwp_flush_rewrite_rules();
            update_option('uwp_activation_redirect', 1);
            update_option('uwp_flush_rewrite', 1);
            update_option('uwp_db_version', USERSWP_VERSION);
            update_option('uwp_default_data_installed', 1);
        } else {
            // already installed
            if (!$installed_ver) {
                // Previous Version was beta
                self::uwp_create_tables();
                self::uwp101_create_tables();
                update_option('uwp_db_version', USERSWP_VERSION);
                update_option('uwp_default_data_installed', 1);
            }
        }


        
    }

    public static function load_dependencies() {
        require_once dirname(dirname( __FILE__ )) . '/admin/settings/class-users-wp-form-builder.php';
    }

    public static function generate_pages() {
        uwp_generate_default_pages();
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

        $settings['enable_profile_tabs'] = array('more_info', 'posts', 'comments');

        //notifications

        $register_success_subject = __('Your Log In Details', 'userswp');
        $register_success_content = __("<p>Dear [#user_name#],</p><p>You can log in  with the following information:</p>[#login_details#]<p>You can login here: [#login_url#]</p><p>Thank you,<br /><br />[#site_name_url#].</p>" ,'userswp');

        $register_activate_subject = __('Please activate your account', 'userswp');
        $register_activate_content = __("<p>Dear [#user_name#],</p><p>Thank you for signing up with [#site_name#]</p>[#login_details#]<p>Thank you,<br /><br />[#site_name_url#].</p>" ,'userswp');

        $forgot_password_subject = __('[#site_name#] - Your new password', 'userswp');
        $forgot_password_content = __("<p>Dear [#user_name#],<p>[#login_details#]<p>You can login here: [#login_url#]</p><p>Thank you,<br /><br />[#site_name_url#].</p>" ,'userswp');

        $change_password_subject = __('[#site_name#] - Password has been changed', 'userswp');
        $change_password_content = __("<p>Dear [#user_name#],<p><p>Your password has been changed successfully.</p><p>You can login here: [#login_url#]</p><p>Thank you,<br /><br />[#site_name_url#].</p>" ,'userswp');

        $reset_password_subject = __('[#site_name#] - Password has been reset', 'userswp');
        $reset_password_content = __("<p>Dear [#user_name#],<p><p>Your password has been reset</p><p>You can login here: [#login_url#]</p><p>Thank you,<br /><br />[#site_name_url#].</p>" ,'userswp');

        $account_update_subject = __('[#site_name#] - Account has been updated', 'userswp');
        $account_update_content = __("<p>Dear [#user_name#],<p><p>Your account has been updated successfully</p><p>Thank you,<br /><br />[#site_name_url#].</p>" ,'userswp');

        $settings['registration_success_email_subject'] = $register_success_subject;
        $settings['registration_success_email_content'] = $register_success_content;

        $settings['registration_activate_email_subject'] = $register_activate_subject;
        $settings['registration_activate_email_content'] = $register_activate_content;

        $settings['forgot_password_email_subject'] = $forgot_password_subject;
        $settings['forgot_password_email_content'] = $forgot_password_content;

        $settings['change_password_email_subject'] = $change_password_subject;
        $settings['change_password_email_content'] = $change_password_content;

        $settings['reset_password_email_subject'] = $reset_password_subject;
        $settings['reset_password_email_content'] = $reset_password_content;

        // $settings['enable_account_update_notification'] = '0'; // no need to set this

        $settings['account_update_email_subject'] = $account_update_subject;
        $settings['account_update_email_content'] = $account_update_content;

        update_option( 'uwp_settings', $settings );

    }
    
    public static function uwp_create_tables()
    {

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


        // Table for storing userswp usermeta
        $usermeta_table_name = uwp_get_table_prefix() . 'uwp_usermeta';
        $user_meta = "CREATE TABLE " . $usermeta_table_name . " (
						user_id int(20) NOT NULL,
						user_ip varchar(20) NULL DEFAULT NULL,
						uwp_account_username varchar(255) NULL DEFAULT NULL,
						uwp_account_email varchar(255) NULL DEFAULT NULL,
						uwp_account_first_name varchar(255) NULL DEFAULT NULL,
						uwp_account_last_name varchar(255) NULL DEFAULT NULL,
						uwp_account_bio varchar(255) NULL DEFAULT NULL,
						uwp_account_avatar_thumb varchar(255) NULL DEFAULT NULL,
						uwp_account_banner_thumb varchar(255) NULL DEFAULT NULL,
						PRIMARY KEY  (user_id)
						) $collate ";

        $user_meta = apply_filters('uwp_before_usermeta_table_create', $user_meta);

        dbDelta($user_meta);

    }

    public static function uwp101_create_tables() {
        global $wpdb;


        $wpdb->hide_errors();

        $collate = '';
        if ($wpdb->has_cap('collation')) {
            if (!empty($wpdb->charset)) $collate = "DEFAULT CHARACTER SET $wpdb->charset";
            if (!empty($wpdb->collate)) $collate .= " COLLATE $wpdb->collate";
        }

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');


        // Table for storing userswp usermeta
        $usermeta_table_name = uwp_get_table_prefix() . 'uwp_usermeta';
        $user_meta = "CREATE TABLE " . $usermeta_table_name . " (
						user_id int(20) NOT NULL,
						user_ip varchar(20) NULL DEFAULT NULL,
						uwp_account_username varchar(255) NULL DEFAULT NULL,
						uwp_account_email varchar(255) NULL DEFAULT NULL,
						uwp_account_first_name varchar(255) NULL DEFAULT NULL,
						uwp_account_last_name varchar(255) NULL DEFAULT NULL,
						uwp_account_bio varchar(255) NULL DEFAULT NULL,
						uwp_account_avatar_thumb varchar(255) NULL DEFAULT NULL,
						uwp_account_banner_thumb varchar(255) NULL DEFAULT NULL,
						PRIMARY KEY  (user_id)
						) $collate ";

        $user_meta = apply_filters('uwp_before_usermeta_table_create', $user_meta);

        dbDelta($user_meta);
    }

    public static function uwp_insert_usermeta()
    {
        global $wpdb;
        $sort= "user_registered";
        $all_users_id = $wpdb->get_col( $wpdb->prepare(
            "SELECT $wpdb->users.ID FROM $wpdb->users ORDER BY %s ASC"
            , $sort ));

        //we got all the IDs, now loop through them to get individual IDs
        foreach ( $all_users_id as $user_id ) {
            // get user info by calling get_userdata() on each id
            $user_data = get_userdata($user_id);
            $first_name = get_user_meta( $user_id, 'first_name', true );
            $last_name = get_user_meta( $user_id, 'last_name', true );
            $bio = get_user_meta( $user_id, 'description', true );
            uwp_update_usermeta($user_id, 'uwp_account_username', $user_data->user_login);
            uwp_update_usermeta($user_id, 'uwp_account_email', $user_data->user_email);
            uwp_update_usermeta($user_id, 'uwp_account_first_name', $first_name);
            uwp_update_usermeta($user_id, 'uwp_account_last_name', $last_name);
            uwp_update_usermeta($user_id, 'uwp_account_bio', $bio);
        }
    }

    public static function uwp_create_default_fields()
    {
        $form_builder = new Users_WP_Form_Builder();

        $fields = self::uwp_default_custom_fields();

        $fields = apply_filters('uwp_before_default_custom_fields_saved', $fields);

        foreach ($fields as $field_index => $field) {
            $form_builder->uwp_admin_form_field_save($field);
        }
    }

    public static function uwp_default_custom_fields(){

        $login = self::uwp_default_custom_fields_login();
        $forgot = self::uwp_default_custom_fields_forgot();
        $avatar = self::uwp_default_custom_fields_avatar();
        $banner = self::uwp_default_custom_fields_banner();
        $change = self::uwp_default_custom_fields_change();
        $reset = self::uwp_default_custom_fields_reset();
        $account = self::uwp_default_custom_fields_account();

        $fields = array_merge($login, $forgot, $account, $avatar, $banner, $change, $reset);

        $fields = apply_filters('uwp_default_custom_fields', $fields);

        return $fields;

    }

    public static function uwp_default_custom_fields_login(){

        $fields = array();

        $fields[] = array(
            'form_type' => 'login',
            'field_type' => 'text',
            'site_title' => __('Username', 'userswp'),
            'htmlvar_name' => 'username',
            'default_value' => '',
            'option_values' => '',
            'is_default' => '1',
            'is_active' => '1',
            'is_required' => '1'
        );

        $fields[] = array(
            'form_type' => 'login',
            'field_type' => 'password',
            'site_title' => __('Password', 'userswp'),
            'htmlvar_name' => 'password',
            'default_value' => '',
            'option_values' => '',
            'is_default' => '1',
            'is_active' => '1',
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
            'site_title' => __('Email', 'userswp'),
            'htmlvar_name' => 'email',
            'default_value' => '',
            'option_values' => '',
            'is_default' => '1',
            'is_active' => '1',
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
            'site_title' => __('Avatar', 'userswp'),
            'htmlvar_name' => 'file',
            'default_value' => '',
            'option_values' => '',
            'is_default' => '1',
            'is_active' => '1',
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
            'site_title' => __('Banner', 'userswp'),
            'htmlvar_name' => 'file',
            'default_value' => '',
            'option_values' => '',
            'is_default' => '1',
            'is_active' => '1',
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

    public static function uwp_default_custom_fields_change(){

        $fields = array();

        $fields[] = array(
            'form_type' => 'change',
            'field_type' => 'password',
            'site_title' => __('Old Password', 'userswp'),
            'htmlvar_name' => 'old_password',
            'default_value' => '',
            'option_values' => '',
            'is_default' => '1',
            'is_active' => '1',
            'is_required' => '1'
        );

        $fields[] = array(
            'form_type' => 'change',
            'field_type' => 'password',
            'site_title' => __('New Password', 'userswp'),
            'htmlvar_name' => 'password',
            'default_value' => '',
            'option_values' => '',
            'is_default' => '1',
            'is_active' => '1',
            'is_required' => '1'
        );

        $fields[] = array(
            'form_type' => 'change',
            'field_type' => 'password',
            'site_title' => __('Confirm Password', 'userswp'),
            'htmlvar_name' => 'confirm_password',
            'default_value' => '',
            'option_values' => '',
            'is_default' => '1',
            'is_active' => '1',
            'is_required' => '1'
        );

        $fields = apply_filters('uwp_default_custom_fields_change', $fields);

        return  $fields;
    }

    public static function uwp_default_custom_fields_reset(){

        $fields = array();

        $fields[] = array(
            'form_type' => 'reset',
            'field_type' => 'password',
            'site_title' => __('Password', 'userswp'),
            'htmlvar_name' => 'password',
            'default_value' => '',
            'option_values' => '',
            'is_default' => '1',
            'is_active' => '1',
            'is_required' => '1'
        );

        $fields[] = array(
            'form_type' => 'reset',
            'field_type' => 'password',
            'site_title' => __('Confirm Password', 'userswp'),
            'htmlvar_name' => 'confirm_password',
            'default_value' => '',
            'option_values' => '',
            'is_default' => '1',
            'is_active' => '1',
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
            'site_title' => __('First Name', 'userswp'),
            'htmlvar_name' => 'first_name',
            'default_value' => '',
            'option_values' => '',
            'is_default' => '1',
            'is_public' => '1',
            'is_active' => '1',
            'is_required' => '1',
            'is_register_field' => '1',
            'is_search_field' => '1',
            'css_class' => 'uwp-half uwp-half-left',
        );

        $fields[] = array(
            'form_type' => 'account',
            'field_type' => 'text',
            'site_title' => __('Last Name', 'userswp'),
            'htmlvar_name' => 'last_name',
            'default_value' => '',
            'option_values' => '',
            'is_default' => '1',
            'is_public' => '1',
            'is_active' => '1',
            'is_required' => '1',
            'is_register_field' => '1',
            'is_search_field' => '1',
            'css_class' => 'uwp-half uwp-half-right',
        );

        $fields[] = array(
            'form_type' => 'account',
            'field_type' => 'text',
            'site_title' => __('Username', 'userswp'),
            'htmlvar_name' => 'username',
            'default_value' => '',
            'option_values' => '',
            'is_default' => '1',
            'is_public' => '1',
            'is_active' => '1',
            'is_required' => '1',
            'is_register_field' => '1',
            'is_search_field' => '1',
            'is_register_only_field' => '1',
        );

        $fields[] = array(
            'form_type' => 'account',
            'field_type' => 'text',
            'site_title' => __('Display Name', 'userswp'),
            'htmlvar_name' => 'display_name',
            'default_value' => '',
            'option_values' => '',
            'is_default' => '1',
            'is_public' => '1',
            'is_active' => '1',
            'is_required' => '0',
            'is_register_field' => '0',
            'is_search_field' => '1',
        );

        $fields[] = array(
            'form_type' => 'account',
            'field_type' => 'email',
            'site_title' => __('Email', 'userswp'),
            'htmlvar_name' => 'email',
            'default_value' => '',
            'option_values' => '',
            'is_default' => '1',
            'is_active' => '1',
            'is_required' => '1',
            'is_register_field' => '1',
            'is_search_field' => '1',
        );

//        $fields[] = array(
//            'form_type' => 'account',
//            'field_type' => 'email',
//            'site_title' => __('Confirm Email', 'userswp'),
//            'htmlvar_name' => 'confirm_email',
//            'default_value' => '',
//            'option_values' => '',
//            'is_default' => '1',
//            'is_active' => '1',
//            'is_required' => '1',
//            'is_register_field' => '1',
//            'is_register_only_field' => '1',
//            'is_search_field' => '1',
//        );

        $fields[] = array(
            'form_type' => 'account',
            'field_type' => 'textarea',
            'site_title' => __('Bio', 'userswp'),
            'htmlvar_name' => 'bio',
            'default_value' => '',
            'option_values' => '',
            'is_default' => '1',
            'is_active' => '1',
            'is_public' => '1',
            'is_required' => '1',
            'is_search_field' => '1',
            'show_in' => array('[profile_side]', '[users]')
        );

        $fields[] = array(
            'form_type' => 'account',
            'field_type' => 'password',
            'site_title' => __('Password', 'userswp'),
            'htmlvar_name' => 'password',
            'default_value' => '',
            'option_values' => '',
            'is_default' => '1',
            'is_active' => '1',
            'is_required' => '1',
            'is_register_field' => '1',
            'is_register_only_field' => '1'
        );

        $fields[] = array(
            'form_type' => 'account',
            'field_type' => 'password',
            'site_title' => __('Confirm Password', 'userswp'),
            'htmlvar_name' => 'confirm_password',
            'default_value' => '',
            'option_values' => '',
            'is_default' => '1',
            'is_active' => '1',
            'is_required' => '1',
            'is_register_field' => '1',
            'is_register_only_field' => '1'
        );

        $fields = apply_filters('uwp_default_custom_fields_account', $fields);

        return  $fields;
    }

    public static function uwp_flush_rewrite_rules() {
        flush_rewrite_rules();
    }

    public static function uwp_insert_form_extras() {
        global $wpdb;
        $extras_table_name = uwp_get_table_prefix() . 'uwp_form_extras';

        $fields = array();

        $fields[] = array(
            'form_type' => 'register',
            'field_type' => 'text',
            'is_default' => '1',
            'htmlvar_name' => 'uwp_account_first_name'
        );

        $fields[] = array(
            'form_type' => 'register',
            'field_type' => 'text',
            'is_default' => '1',
            'htmlvar_name' => 'uwp_account_last_name'
        );

        $fields[] = array(
            'form_type' => 'register',
            'field_type' => 'text',
            'is_default' => '1',
            'htmlvar_name' => 'uwp_account_username'
        );

        $fields[] = array(
            'form_type' => 'register',
            'field_type' => 'email',
            'is_default' => '1',
            'htmlvar_name' => 'uwp_account_email'
        );

//        $fields[] = array(
//            'form_type' => 'register',
//            'field_type' => 'email',
//            'is_default' => '1',
//            'htmlvar_name' => 'uwp_account_confirm_email'
//        );

        $fields[] = array(
            'form_type' => 'register',
            'field_type' => 'password',
            'is_default' => '1',
            'htmlvar_name' => 'uwp_account_password'
        );

        $fields[] = array(
            'form_type' => 'register',
            'field_type' => 'password',
            'is_default' => '1',
            'htmlvar_name' => 'uwp_account_confirm_password'
        );

        foreach ($fields as $field) {
            $last_order = $wpdb->get_var("SELECT MAX(sort_order) as last_order FROM " . $extras_table_name);
            $sort_order = (int)$last_order + 1;
            $wpdb->query(
                $wpdb->prepare(

                    "insert into " . $extras_table_name . " set
                        form_type = %s,
                        field_type = %s,
                        is_default = %s,
                        site_htmlvar_name = %s,
                        sort_order = %s",
                    array(
                        $field['form_type'],
                        $field['field_type'],
                        $field['is_default'],
                        $field['htmlvar_name'],
                        $sort_order
                    )
                )
            );
        }
    }

}