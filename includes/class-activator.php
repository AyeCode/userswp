<?php
/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @author     GeoDirectory Team <info@wpgeodirectory.com>
 */
class UsersWP_Activator {

    /** @var array DB updates and callbacks that need to be run per version */
    private static $db_updates = array(
        '1.2.0.0' => array(
            'uwp_upgrade_1200',
        ),
        '1.2.0.13' => array(
            'uwp_upgrade_12013',
        ),
        '1.2.2.5' => array(
	        'uwp_upgrade_1225',
        ),
        '1.2.3' => array(
	        'uwp_upgrade_1230',
        ),
    );


    /**
     * Background update class.
     *
     * @var object
     */
    private static $background_updater;

    /**
     * This method gets fired during plugin activation.
     *
     * @since       1.0.0
     * @package     userswp
     * @return      void
     */
    public static function activate($network_wide = false) {

        if (is_multisite()) {
            $main_site = get_network()->site_id;
            if($network_wide){

                update_network_option('', 'uwp_is_network_active', 1);
                switch_to_blog( $main_site );
                restore_current_blog();
                
                if (defined('UWP_ROOT_PAGES')) {
                    if (UWP_ROOT_PAGES == 'all') {
                        $blog_ids = self::get_blog_ids();

                        foreach ( $blog_ids as $blog_id ) {
                            switch_to_blog( $blog_id );
                            self::install();
                        }
                        restore_current_blog();
                    } else {
                        $blog_id = UWP_ROOT_PAGES;
                        switch_to_blog( $blog_id );
                        self::install();
                        restore_current_blog();
                    }
                } else {
                    switch_to_blog( $main_site );
                    self::install();
                    restore_current_blog();
                }

            } else {
                self::install();
            }
        } else {
            self::install();
        }

    }

    public static function install(){

	    uwp_generate_default_pages();
        self::add_default_options();
	    uwp_create_tables();

        // run update functions if needed
        if(self::needs_db_update()){
            self::update();
        }

        if (!get_option('uwp_default_data_installed')) {
            // new install
            self::create_default_fields();
            self::insert_form_extras();
            update_option('uwp_default_data_installed', 1);
            update_option('uwp_activation_redirect', 1);
        }else{
            // upgrade
            // if updating from < 1.2.0 then add the try bootstrap notice
            if(version_compare(get_option( 'uwp_db_version', null ),"1.2.0","<")){
                update_option("uwp_notice_try_bootstrap",true);
                uwp_update_option('design_style','');
            }
        }

        self::flush_rewrite_rules();
        update_option('uwp_flush_rewrite', 1);

        // update the version
        update_option('uwp_db_version', USERSWP_VERSION);

        $installed = get_option( 'uwp_installed_on' );

	    if ( empty( $installed ) ) {
		    update_option( 'uwp_installed_on', time() );
	    }

    }

    /**
     * Get all IDs of blogs that are not activated, not spam, and not deleted
     *
     * @global      object $wpdb
     * @return      array|false Array of IDs or false if none are found
     */
    public static function get_blog_ids() {
        global $wpdb;

        // Get an array of IDs
        $sql = "SELECT blog_id FROM $wpdb->blogs
                    WHERE archived = '0' AND spam = '0'
                    AND deleted = '0'";

        return $wpdb->get_col( $sql );
    }

    /**
     * Adds default settings during plugin activation.
     *
     * @since       1.0.0
     * @package     userswp
     * @return      void
     */
    public static function add_default_options() {

        $settings = get_option( 'uwp_settings', array());

        $options = array(
            'register_modal' => 1,
            'uwp_registration_action' => 'auto_approve',
            'wp_register_redirect' => 1,
            'login_modal' => 1,
            'login_redirect_to' => -1,
            'block_wp_login' => 0,
            'disable_wp_2fa' => 0,
	        'forgot_modal' => 1,
            'change_enable_old_password' => 1,
            'change_disable_password_nag' => 0,
            'enable_profile_header' => 1,
            'enable_profile_body' => 1,
            'profile_avatar_size' => '',
            'profile_banner_size' => '',
            'profile_banner_width' => 1000,
            'profile_no_of_items' => 10,
            'users_no_of_items' => 10,
            'uwp_disable_author_link' => 0,
            'users_default_layout' => '3col',
            'author_box_enable_disable' => 1,
            'author_box_display_content' => 'below_content',
            'author_box_display_post_types' => array('post'),
            'author_box_content' => '',
            'author_box_bio_limit' => 200,
            'registration_success_email_admin' => 1,
            'registration_success_email_subject_admin' => UsersWP_Defaults::registration_success_email_subject_admin(),
            'registration_success_email_content_admin' => UsersWP_Defaults::registration_success_email_content_admin(),
            'registration_activate_email' => 1,
            'registration_activate_email_subject' => UsersWP_Defaults::registration_activate_email_subject(),
            'registration_activate_email_content' => UsersWP_Defaults::registration_activate_email_content(),
            'registration_success_email' => 1,
            'registration_success_email_subject' => UsersWP_Defaults::registration_success_email_subject(),
            'registration_success_email_content' => UsersWP_Defaults::registration_success_email_content(),
            'forgot_password_email' => 1,
            'forgot_password_email_subject' => UsersWP_Defaults::forgot_password_email_subject(),
            'forgot_password_email_content' => UsersWP_Defaults::forgot_password_email_content(),
            'change_password_email' => 1,
            'change_password_email_subject' => UsersWP_Defaults::change_password_email_subject(),
            'change_password_email_content' => UsersWP_Defaults::change_password_email_content(),
            'reset_password_email' => 1,
            'reset_password_email_subject' => UsersWP_Defaults::reset_password_email_subject(),
            'reset_password_email_content' => UsersWP_Defaults::reset_password_email_content(),
            'account_update_email' => 1,
            'account_update_email_subject' => UsersWP_Defaults::account_update_email_subject(),
            'account_update_email_content' => UsersWP_Defaults::account_update_email_content(),
            'account_delete_email' => 1,
            'account_delete_email_subject' => UsersWP_Defaults::account_delete_email_subject(),
            'account_delete_email_content' => UsersWP_Defaults::account_delete_email_content(),
            'account_delete_email_admin' => 1,
            'account_delete_email_subject_admin' => UsersWP_Defaults::account_delete_email_subject_admin(),
            'account_delete_email_content_admin' => UsersWP_Defaults::account_delete_email_content_admin(),
            'wp_new_user_notification_email' => 1,
            'wp_new_user_notification_email_subject' => UsersWP_Defaults::wp_new_user_notification_email_subject(),
            'wp_new_user_notification_email_content' => UsersWP_Defaults::wp_new_user_notification_email_content(),
            'account_new_email_activation_email' => 1,
            'account_new_email_activation_email_subject' => UsersWP_Defaults::account_new_email_activation_email_subject(),
            'account_new_email_activation_email_content' => UsersWP_Defaults::account_new_email_activation_email_content(),
            'wp_new_user_notification_email_admin' => 1,
            'wp_new_user_notification_email_subject_admin' => UsersWP_Defaults::wp_new_user_notification_email_subject_admin(),
            'wp_new_user_notification_email_content_admin' => UsersWP_Defaults::wp_new_user_notification_email_content_admin(),
            'user_post_counts_cpts' => array('post'),
            'login_user_post_counts_cpts' => array('post'),
            'multiple_registration_forms' => uwp_get_default_form_data(),
            'profile_seo_meta_description_length' => 150,
        );

        foreach ($options as $option => $value){
            if (!isset($settings[$option])) {
                $settings[$option] = $value;
            }
        }

        update_option( 'uwp_settings', $settings );

    }

    public static function init_background_updater(){
        if(empty(self::$background_updater)){
            include_once dirname( __FILE__ ) . '/class-uwp-background-updater.php';
            self::$background_updater = new UsersWP_Background_Updater();
        }
    }

    /**
     * Syncs WP usermeta with UsersWP usermeta during plugin activation.
     *
     * @since       1.0.0
     * @package     userswp
     * @return      void
     */
    public static function uwp_update_usermeta($dispatch = false) {
        $update_callback = 'uwp_insert_usermeta';
        self::init_background_updater();

        uwp_error_log( sprintf( 'Queuing %s - %s', USERSWP_VERSION, $update_callback ) );
        self::$background_updater->push_to_queue( $update_callback );
        if($dispatch){
            self::$background_updater->save()->dispatch();
        }
    }

    /**
     * Get list of DB update callbacks.
     *
     * @since  3.0.0
     * @return array
     */
    public static function get_db_update_callbacks() {
        return self::$db_updates;
    }

    /**
     * Is a DB update needed?
     *
     * @since 2.0.0
     * @return boolean
     */
    private static function needs_db_update() {
        $current_db_version = get_option( 'uwp_db_version', null );
        $updates            = self::get_db_update_callbacks();

        return ! empty( $updates ) && version_compare( $current_db_version, max( array_keys( $updates ) ), '<' );
    }

    /**
     * Push all needed DB updates to the queue for processing.
     *
     * @since 2.0.0
     */
    private static function update() {
        $current_db_version = get_option( 'uwp_db_version' );
        $update_queued      = false;
        self::init_background_updater();
        foreach ( self::get_db_update_callbacks() as $version => $update_callbacks ) {
            if ( version_compare( $current_db_version, $version, '<' ) ) {
                foreach ( $update_callbacks as $update_callback ) {
                    uwp_error_log( sprintf( 'Queuing %s - %s', $version, $update_callback ) );
                    self::$background_updater->push_to_queue( $update_callback );
                    $update_queued = true;
                }
            }
        }

        
        if ( $update_queued ) {
            self::uwp_update_usermeta();// make sure to sync user meta
            self::$background_updater->save()->dispatch();
        }else{
            self::uwp_update_usermeta(true);// make sure to sync user meta
        }
    }

    /**
     * Saves default custom fields in the database.
     *
     * @since       1.0.0
     * @package     userswp
     * @return      void
     */
    public static function create_default_fields()
    {
        $form_builder = new UsersWP_Form_Builder();

        $fields = self::uwp_default_custom_fields();

        $fields = apply_filters('uwp_before_default_custom_fields_saved', $fields);

        foreach ($fields as $field_index => $field) {
            $form_builder->admin_form_field_save($field);
        }
    }

    /**
     * Returns merged default custom fields.
     *
     * @since       1.0.0
     * @package     userswp
     * @return      array       Merged custom fields.
     */
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

    /**
     * Returns Login form default custom fields.
     *
     * @since       1.0.0
     * @package     userswp
     * @return      array       Login form default custom fields.
     */
    public static function uwp_default_custom_fields_login(){

        $fields = array();

        $fields[] = array(
            'form_type' => 'login',
            'field_type' => 'text',
            'data_type' => 'XVARCHAR',
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
            'data_type' => 'XVARCHAR',
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

    /**
     * Returns Forgot form default custom fields.
     *
     * @since       1.0.0
     * @package     userswp
     * @return      array       Forgot form default custom fields.
     */
    public static function uwp_default_custom_fields_forgot(){

        $fields = array();

        $fields[] = array(
            'form_type' => 'forgot',
            'field_type' => 'email',
            'data_type' => 'XVARCHAR',
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

    /**
     * Returns Avatar form default custom fields.
     *
     * @since       1.0.0
     * @package     userswp
     * @return      array       Avatar form default custom fields.
     */
    public static function uwp_default_custom_fields_avatar(){

        $fields = array();

        $fields[] = array(
            'form_type' => 'avatar',
            'field_type' => 'file',
            'data_type' => 'TEXT',
            'site_title' => __('Avatar', 'userswp'),
            'htmlvar_name' => 'avatar',
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

    /**
     * Returns Banner form default custom fields.
     *
     * @since       1.0.0
     * @package     userswp
     * @return      array       Banner form default custom fields.
     */
    public static function uwp_default_custom_fields_banner(){

        $fields = array();

        $fields[] = array(
            'form_type' => 'banner',
            'field_type' => 'file',
            'data_type' => 'TEXT',
            'site_title' => __('Banner', 'userswp'),
            'htmlvar_name' => 'banner',
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

    /**
     * Returns Change Password form default custom fields.
     *
     * @since       1.0.0
     * @package     userswp
     * @return      array       Change Password form default custom fields.
     */
    public static function uwp_default_custom_fields_change(){

        $fields = array();

        $fields[] = array(
            'form_type' => 'change',
            'field_type' => 'password',
            'data_type' => 'XVARCHAR',
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
            'data_type' => 'XVARCHAR',
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
            'data_type' => 'XVARCHAR',
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

    /**
     * Returns Reset Password form default custom fields.
     *
     * @since       1.0.0
     * @package     userswp
     * @return      array       Reset Password form default custom fields.
     */
    public static function uwp_default_custom_fields_reset(){

        $fields = array();

        $fields[] = array(
            'form_type' => 'reset',
            'field_type' => 'password',
            'data_type' => 'XVARCHAR',
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
            'data_type' => 'XVARCHAR',
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

    /**
     * Returns Account form default custom fields.
     *
     * @since       1.0.0
     * @package     userswp
     * @return      array       Account form default custom fields.
     */
    public static function uwp_default_custom_fields_account(){

        $fields = array();

        $fields[] = array(
            'form_type' => 'account',
            'field_type' => 'text',
            'data_type' => 'XVARCHAR',
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
        );

        $fields[] = array(
            'form_type' => 'account',
            'field_type' => 'text',
            'data_type' => 'XVARCHAR',
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
        );

        $fields[] = array(
            'form_type' => 'account',
            'field_type' => 'text',
            'data_type' => 'XVARCHAR',
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
            'data_type' => 'XVARCHAR',
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
            'data_type' => 'XVARCHAR',
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


        $fields[] = array(
            'form_type' => 'account',
            'field_type' => 'textarea',
            'data_type' => 'TEXT',
            'site_title' => __('Bio', 'userswp'),
            'htmlvar_name' => 'bio',
            'default_value' => '',
            'option_values' => '',
            'is_default' => '0',
            'is_active' => '1',
            'is_public' => '1',
            'is_required' => '1',
            'is_search_field' => '1',
            'show_in' => array('[users]')
        );

        $fields[] = array(
            'form_type' => 'account',
            'field_type' => 'password',
            'data_type' => 'XVARCHAR',
            'site_title' => __('Password', 'userswp'),
            'htmlvar_name' => 'password',
            'default_value' => '',
            'option_values' => '',
            'is_default' => '1',
            'is_active' => '1',
            'is_required' => '1',
            'is_register_field' => '1',
            'is_register_only_field' => '1',
            'extra'        =>  array(
                'confirm_password'  =>  '1'
            )
        );


        $fields = apply_filters('uwp_default_custom_fields_account', $fields);

        return  $fields;
    }

    /**
     * Flushes rewrite rules.
     *
     * @since       1.0.0
     * @package     userswp
     * @return      void
     */
    public static function flush_rewrite_rules() {
        flush_rewrite_rules();
    }

    /**
     * Inserts register form custom fields in form extras table.
     *
     * @since       1.0.0
     * @package     userswp
     * @return      void
     */
    public static function insert_form_extras($form_id = 1) {
        global $wpdb;
        $extras_table_name = uwp_get_table_prefix() . 'uwp_form_extras';

        $fields = array();

        $fields[] = array(
            'form_type' => 'register',
            'field_type' => 'text',
            'is_default' => '0',
            'htmlvar_name' => 'first_name'
        );

        $fields[] = array(
            'form_type' => 'register',
            'field_type' => 'text',
            'is_default' => '0',
            'htmlvar_name' => 'last_name'
        );

        $fields[] = array(
            'form_type' => 'register',
            'field_type' => 'text',
            'is_default' => '1',
            'htmlvar_name' => 'username'
        );

        $fields[] = array(
            'form_type' => 'register',
            'field_type' => 'email',
            'is_default' => '1',
            'htmlvar_name' => 'email'
        );

        $fields[] = array(
            'form_type' => 'register',
            'field_type' => 'password',
            'is_default' => '0',
            'htmlvar_name' => 'password'
        );


        foreach ($fields as $field) {
            $last_order = $wpdb->get_var("SELECT MAX(sort_order) as last_order FROM " . $extras_table_name . " where form_id = ". $form_id);
            $sort_order = (int)$last_order + 1;
            $wpdb->query(
                $wpdb->prepare(

                    "insert into " . $extras_table_name . " set
                        form_type = %s,
                        field_type = %s,
                        is_default = %s,
                        site_htmlvar_name = %s,
                        sort_order = %s,
                        form_id = %s",
                    array(
                        $field['form_type'],
                        $field['field_type'],
                        $field['is_default'],
                        $field['htmlvar_name'],
                        $sort_order,
	                    $form_id
                    )
                )
            );
        }
    }

	/**
	 * Performs automatic upgrade
	 *
	 * @since       2.0.0
	 * @package     userswp
	 * @return      void
	 */
    public static function automatic_upgrade(){
        $uwp_db_version = get_option('uwp_db_version');

        if ( $uwp_db_version != USERSWP_VERSION ) {
            self::activate(is_plugin_active_for_network( 'userswp/userswp.php' ));
	        $settings = get_option( 'uwp_settings', array());
	        $needs_update = false;
	        if(isset($settings['design_style']) && 'bootstrap' == $settings['design_style'] ){
		        $settings['users_default_layout'] = '3col';
		        $needs_update = true;
	        }

	        if(isset($settings['uwp_registration_action']) && $settings['uwp_registration_action'] == 'force_redirect'){
		        $settings['uwp_registration_action'] = 'auto_approve_login';
		        $needs_update = true;
	        }

	        $get_register_form = isset( $settings['multiple_registration_forms'] ) ? $settings['multiple_registration_forms'] : false;

	        if ( ! empty( $get_register_form ) && is_array( $get_register_form ) ) {

		        foreach ( $get_register_form as $key => $register_form ) {

			        if ( ! empty( $register_form['id'] )) {

				        $reg_action = isset($register_form['reg_action']) ? $register_form['reg_action'] : '';

				        if(isset($reg_action) && $reg_action == 'force_redirect'){
					        $settings['multiple_registration_forms'][$key]['reg_action'] = 'auto_approve_login';
					        $needs_update = true;
				        }
			        }
		        }
	        }

	        if($needs_update){
		        update_option( 'uwp_settings', $settings );
	        }

            // Run this function once.
            update_option( 'uwp_db_version', USERSWP_VERSION );

        }
    }



}