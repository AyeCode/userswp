<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @since      1.0.0
 * @author     GeoDirectory Team <info@wpgeodirectory.com>
 */
class UsersWP {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      UsersWP_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;


    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;
    
    protected $profile;
    protected $forms;
    protected $i18n;
    protected $notices;
    protected $templates;
    protected $meta;
    protected $pages;
    protected $files;
    protected $shortcodes;
    protected $assets;
    protected $admin;
    protected $admin_settings;
    protected $menus;
    protected $form_builder;
    protected $ajax;
    protected $tools;
    protected $tables;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct() {

        $this->plugin_name = USERSWP_NAME;
        $this->version = USERSWP_VERSION;


        $this->load_dependencies();
        $this->loader = new UsersWP_Loader();
        $this->meta = new UsersWP_Meta();
        $this->pages = new UsersWP_Pages();
        $this->profile = new UsersWP_Profile();
        $this->forms = new UsersWP_Forms();
        $this->templates = new UsersWP_Templates();
        $this->i18n = new UsersWP_i18n();
        $this->notices = new UsersWP_Notices();
        $this->assets = new UsersWP_Public();
        $this->form_builder = new UsersWP_Form_Builder();
        $this->menus = new UsersWP_Menus();
        $this->tools = new UsersWP_Tools();
        $this->tables = new UsersWP_Tables();

        $this->shortcodes = new UsersWP_Shortcodes($this->templates);
        $this->admin_settings = new UsersWP_Admin_Settings($this->form_builder);
        $this->admin = new UsersWP_Admin($this->admin_settings);
        $this->ajax = new UsersWP_Ajax($this->form_builder);
        $this->files = new UsersWP_Files();
        
        
        // actions and filters
        $this->load_assets_actions_and_filters($this->assets);
        $this->load_meta_actions_and_filters($this->meta);
        $this->load_ajax_actions_and_filters($this->ajax);
        $this->load_files_actions_and_filters($this->files);
        $this->load_forms_actions_and_filters($this->forms);
        $this->load_i18n_actions_and_filters($this->i18n);
        $this->load_notices_actions_and_filters($this->notices);
        $this->load_pages_actions_and_filters($this->pages);
        $this->load_profile_actions_and_filters($this->profile);
        $this->load_shortcodes_actions_and_filters($this->shortcodes);
        $this->load_tables_actions_and_filters($this->tables);
        $this->load_templates_actions_and_filters($this->templates);
        $this->load_tools_actions_and_filters($this->tools);

        //admin
        $this->load_form_builder_actions_and_filters($this->form_builder);
        $this->load_menus_actions_and_filters($this->menus);
        $this->load_admin_actions_and_filters($this->admin);
        $this->load_admin_settings_actions_and_filters($this->admin_settings);
        
    }




    public function load_assets_actions_and_filters($instance) {
        add_action( 'wp_enqueue_scripts', array($instance, 'enqueue_styles') );
        add_action( 'wp_enqueue_scripts', array($instance, 'enqueue_scripts') );
    }

    public function load_meta_actions_and_filters($instance) {
        add_action('user_register', array($instance, 'sync_usermeta'), 10, 1);
        add_action('delete_user', array($instance, 'delete_usermeta_for_user'), 10, 1);
        add_action('remove_user_from_blog', array($instance, 'remove_user_from_blog'), 10, 2);
        add_action('wp_login', array($instance, 'save_user_ip_on_login') ,10,2);
        add_filter('uwp_before_extra_fields_save', array($instance, 'save_user_ip_on_register'), 10, 3);
        add_filter('uwp_update_usermeta', array($instance, 'modify_privacy_value_on_update'), 10, 4);
        add_filter('uwp_get_usermeta', array($instance, 'modify_privacy_value_on_get'), 10, 5);
        add_filter('uwp_update_usermeta', array($instance, 'modify_datepicker_value_on_update'), 10, 3);
        add_filter('uwp_get_usermeta', array($instance, 'modify_datepicker_value_on_get'), 10, 5);
        add_filter('user_row_actions', array($instance, 'uwp_user_row_actions'), 10, 2);
        add_action('bulk_actions-users', array($instance, 'uwp_users_bulk_actions'));
        add_action('handle_bulk_actions-users', array($instance, 'uwp_handle_users_bulk_actions'), 10, 3);
        add_filter('init', array($instance, 'uwp_process_user_actions'));
        add_action('admin_notices', array($instance, 'uwp_show_update_messages'));
    }
    
    public function load_ajax_actions_and_filters($instance) {
        add_action('wp_ajax_uwp_ajax_action', array($instance, 'handler'));
    }

    public function load_files_actions_and_filters($instance) {
        if($instance->uwp_doing_upload()){
            add_filter( 'wp_handle_upload_prefilter', array($instance, 'uwp_wp_media_restrict_file_types') );
        }
        add_filter('uwp_get_max_upload_size', array($instance, 'uwp_modify_get_max_upload_size'), 10, 2);
    }

    public function load_forms_actions_and_filters($instance) {
        // general
        add_action('init', array($instance, 'init_notices'), 1);
        add_action('uwp_loaded', array($instance, 'handler'));
        add_action('init', array($instance, 'uwp_privacy_submit_handler'));
        add_action('uwp_template_display_notices', array($instance, 'display_notices'), 10, 1);
        add_action('wp_ajax_uwp_upload_file_remove', array($instance, 'uwp_upload_file_remove'));
        //User search form
        add_action('uwp_users_page_search_form_inner', array($instance, 'uwp_users_search_form_text_field'), 10, 1);
        add_action('uwp_users_page_search_form_inner', array($instance, 'uwp_users_search_form_submit'), 50, 1);
        add_action('personal_options_update', array($instance, 'update_profile_extra_admin_edit'), 10, 1);
        add_action('edit_user_profile_update', array($instance, 'update_profile_extra_admin_edit'), 10, 1);
        add_action('user_edit_form_tag', array($instance, 'add_multipart_to_admin_edit_form'));
        add_action('uwp_template_form_title_after', array($instance, 'uwp_display_username_in_account'), 10, 1);


        // Forms
        add_filter('uwp_form_input_html_datepicker', array($instance, 'uwp_form_input_datepicker'), 10, 4);
        add_filter('uwp_form_input_html_time', array($instance, 'uwp_form_input_time'), 10, 4);
        add_filter('uwp_form_input_html_select', array($instance, 'uwp_form_input_select'), 10, 4);
        add_filter('uwp_form_input_html_multiselect', array($instance, 'uwp_form_input_multiselect'), 10, 4);
        add_filter('uwp_form_input_html_text', array($instance, 'uwp_form_input_text'), 10, 4);
        add_filter('uwp_form_input_html_textarea', array($instance, 'uwp_form_input_textarea'), 10, 4);
        add_filter('uwp_form_input_html_fieldset', array($instance, 'uwp_form_input_fieldset'), 10, 4);
        add_filter('uwp_form_input_html_file', array($instance, 'uwp_form_input_file'), 10, 4);
        add_filter('uwp_form_input_html_checkbox', array($instance, 'uwp_form_input_checkbox'), 10, 4);
        add_filter('uwp_form_input_html_radio', array($instance, 'uwp_form_input_radio'), 10, 4);
        add_filter('uwp_form_input_html_url', array($instance, 'uwp_form_input_url'), 10, 4);
        add_filter('uwp_form_input_html_email', array($instance, 'uwp_form_input_email'), 10, 4);
        add_filter('uwp_form_input_html_password', array($instance, 'uwp_form_input_password'), 10, 4);
        // Country select
        add_filter('uwp_form_input_html_select_country', array($instance, 'uwp_form_input_select_country'), 10, 4);
        add_filter('uwp_forms_check_for_send_mail_errors', array($instance, 'uwp_forms_check_for_send_mail_errors'), 10, 3);
        add_filter('uwp_form_input_email_uwp_account_email_after', array($instance, 'uwp_register_confirm_email_field'), 10, 4);
        add_filter('uwp_form_input_password_uwp_account_password_after', array($instance, 'uwp_register_confirm_password_field'), 10, 4);
        
        // Emails
        add_filter('uwp_send_mail_subject', array($instance, 'init_mail_subject'), 10, 2);
        add_filter('uwp_send_mail_message', array($instance, 'init_mail_content'), 10, 2);
        add_filter('uwp_send_mail_extras', array($instance, 'init_mail_extras'), 10, 3);

        add_filter('uwp_send_admin_mail_subject', array($instance, 'init_admin_mail_subject'), 10, 2);
        add_filter('uwp_send_admin_mail_message', array($instance, 'init_admin_mail_content'), 10, 2);
        add_filter('uwp_send_admin_mail_extras', array($instance, 'init_admin_mail_extras'), 10, 3);
        
    }

    public function load_i18n_actions_and_filters($instance) {
        add_action( 'init', array($instance, 'load_plugin_textdomain'));
    }

    public function load_notices_actions_and_filters($instance) {
        add_action('uwp_template_display_notices', array($instance, 'display_registration_disabled_notice'));
        add_action('uwp_template_display_notices', array($instance, 'form_notice_by_key'));
        add_action( 'admin_notices', array( $instance, 'show_admin_notices' ) );
        add_action( 'admin_notices', array($instance, 'uwp_admin_notices') );
    }

    public function load_pages_actions_and_filters($instance) {
        add_action( 'wpmu_new_blog', array($instance, 'wpmu_generate_default_pages_on_new_site'), 10, 6 );
    }

    public function load_profile_actions_and_filters($instance) {
        add_action( 'template_redirect', array($instance, 'uwp_redirect_author_page') , 10 , 2 );
        //profile page
        add_filter('query_vars', array($instance, 'profile_query_vars'), 10, 1 );
        add_action('init', array($instance, 'rewrite_profile_link') , 10, 1 );
        add_filter( 'uwp_profile_link', array($instance, 'get_profile_link'), 10, 2 );
        add_filter( 'edit_profile_url', array($instance, 'uwp_modify_admin_bar_edit_profile_url'), 10, 3);
        add_filter( 'the_title', array($instance, 'modify_profile_page_title'), 10, 2 );
        add_filter( 'get_comment_author_link', array($instance, 'uwp_get_comment_author_link') , 10 , 2 );
        add_action( 'uwp_profile_header', array($instance, 'get_profile_header'), 10, 1 );
        add_action( 'uwp_users_profile_header', array($instance, 'get_profile_header'), 10, 1 );
        add_action( 'uwp_profile_title', array($instance, 'get_profile_title'), 10, 1 );
        //add_action( 'uwp_profile_bio', array($instance, 'get_profile_bio'), 10, 1 );
        add_action( 'uwp_profile_social', array($instance, 'get_profile_social'), 10, 1 );

        //Fields as tabs
        add_action( 'uwp_profile_tabs', array($instance, 'uwp_extra_fields_as_tabs'), 10, 2 );

        // Popup and crop functions
        add_filter( 'ajax_query_attachments_args', array($instance, 'uwp_restrict_attachment_display') );

        add_action( 'uwp_handle_file_upload_error_checks', array($instance, 'uwp_handle_file_upload_error_checks'), 10, 4 );
        add_action( 'wp_ajax_uwp_avatar_banner_upload', array($instance, 'uwp_ajax_avatar_banner_upload') );
        //add_action( 'wp_ajax_uwp_ajax_image_crop_popup', array($instance, 'uwp_ajax_image_crop_popup') );
        add_action( 'wp_ajax_uwp_ajax_image_crop_popup_form', array($instance, 'uwp_ajax_image_crop_popup_form') );
        add_action( 'wp_head', array($instance, 'uwp_define_ajaxurl') );
        add_action( 'uwp_profile_header', array($instance, 'uwp_image_crop_init'), 10, 1 );
        add_action( 'uwp_admin_profile_edit', array($instance, 'uwp_image_crop_init'), 10, 1 );

        // Profile Tabs
        add_action( 'uwp_profile_content', array($instance, 'get_profile_tabs_content'), 10, 1 );
        add_action( 'uwp_profile_more_info_tab_content', array($instance, 'get_profile_more_info'), 10, 1);
        add_action( 'uwp_profile_posts_tab_content', array($instance, 'get_profile_posts'), 10, 1);
        add_action( 'uwp_profile_comments_tab_content', array($instance, 'get_profile_comments'), 10, 1);
        add_action( 'uwp_profile_tab_content', array($instance, 'uwp_extra_fields_as_tab_values'), 10, 2 );

        // Profile Pagination
        add_action( 'uwp_profile_pagination', array($instance, 'get_profile_pagination'));

        // Users
        add_action( 'uwp_users_search', array($instance, 'uwp_users_search'));
        add_action( 'uwp_users_list', array($instance, 'uwp_users_list'));
        add_action( 'uwp_users_extra', array($instance, 'get_users_extra'));
        add_action( 'uwp_profile_bio', array($instance, 'get_profile_side_extra'));


        // User, allow subscribers to upload profile and banner pictures
        add_filter( 'plupload_default_params', array($instance, 'add_uwp_plupload_param'), 10, 1 );
        add_filter( 'user_has_cap', array($instance, 'allow_all_users_profile_uploads'), 10, 4 );
    }

    public function load_shortcodes_actions_and_filters($instance) {
        add_shortcode( 'uwp_forgot',    array($instance, 'forgot'));
        add_shortcode( 'uwp_change',    array($instance, 'change'));
        add_shortcode( 'uwp_reset',     array($instance, 'reset'));
        add_shortcode( 'uwp_account',   array($instance, 'account'));
        add_shortcode( 'uwp_profile',   array($instance, 'profile'));
        add_shortcode( 'uwp_users',     array($instance, 'users'));
    }
    
    public function load_tables_actions_and_filters($instance) {
        add_filter( 'wpmu_drop_tables', array($instance, 'drop_tables_on_delete_blog'));
    }
    
    public function load_templates_actions_and_filters($instance) {



        add_action( 'template_redirect', array($instance, 'change_default_password_redirect') );
        add_action( 'uwp_template_fields', array($instance, 'uwp_template_fields'), 10, 1 );
        add_action( 'uwp_account_form_display', array($instance, 'uwp_account_edit_form_display'), 10, 1 );
        add_action( 'wp_logout', array($instance, 'logout_redirect'));
        add_action( 'init', array($instance, 'wp_login_redirect'));
        add_action( 'admin_init', array($instance, 'uwp_activation_redirect'));
        // Redirect functions
        add_action( 'template_redirect', array($instance, 'profile_redirect'), 10);
        add_action( 'template_redirect', array($instance, 'access_checks'), 20);
        // Admin user edit page
        add_action( 'edit_user_profile', array($instance, 'get_profile_extra_admin_edit'), 10, 1 );
        add_action( 'show_user_profile', array($instance, 'get_profile_extra_admin_edit'), 10, 1 );


        add_filter( 'wp_setup_nav_menu_item', array($instance, 'uwp_setup_nav_menu_item'), 10, 1 );
        add_filter( 'the_content', array($instance, 'uwp_author_page_content'), 10, 1 );
        add_filter( 'body_class', array($instance, 'uwp_add_body_class'), 10, 1 );

        // filter the login url
        add_filter( 'login_url', array($instance, 'wp_login_url'), 10, 3 );
    }
    
    public function load_tools_actions_and_filters($instance) {
        add_action('admin_init', array($instance, 'uwp_tools_process_dummy_users'));
        add_action('uwp_admin_sub_menus', array($instance, 'uwp_add_admin_tools_sub_menu'), 100, 1);
        add_action('uwp_tools_settings_main_tab_content', array($instance, 'uwp_tools_main_tab_content'));
        add_action('wp_ajax_uwp_process_diagnosis', array($instance, 'uwp_process_diagnosis_ajax'));
    }

    public function load_form_builder_actions_and_filters($instance) {
        // Actions
        add_action('admin_init', array($instance, 'uwp_form_builder_dummy_fields'));
        add_action('uwp_manage_available_fields_predefined', array($instance, 'uwp_manage_available_fields_predefined'));
        add_action('uwp_manage_available_fields_custom', array($instance, 'uwp_manage_available_fields_custom'));
        add_action('uwp_manage_available_fields', array($instance, 'uwp_manage_available_fields'));
        add_action('uwp_manage_selected_fields', array($instance, 'uwp_manage_selected_fields'));
        add_action('uwp_admin_extra_custom_fields', array($instance, 'uwp_advance_admin_custom_fields'), 10, 2);
        add_action('uwp_manage_available_fields', array($instance, 'uwp_manage_register_available_fields'), 10, 1);
        add_action('uwp_manage_selected_fields', array($instance, 'uwp_manage_register_selected_fields'), 10, 1);
        add_action('wp_ajax_uwp_ajax_register_action', array($instance, 'uwp_register_ajax_handler'));


        // Filters
        add_filter('uwp_builder_extra_fields_multiselect', array($instance, 'uwp_builder_extra_fields_smr'), 10, 4);
        add_filter('uwp_builder_extra_fields_select', array($instance, 'uwp_builder_extra_fields_smr'), 10, 4);
        add_filter('uwp_builder_extra_fields_radio', array($instance, 'uwp_builder_extra_fields_smr'), 10, 4);
        add_filter('uwp_builder_extra_fields_datepicker', array($instance, 'uwp_builder_extra_fields_datepicker'), 10, 4);
        add_filter('uwp_builder_extra_fields_password', array($instance, 'uwp_builder_extra_fields_password'), 10, 4);
        add_filter('uwp_builder_extra_fields_email', array($instance, 'uwp_builder_extra_fields_email'), 10, 4);
        add_filter('uwp_builder_extra_fields_file', array($instance, 'uwp_builder_extra_fields_file'), 10, 4);
        add_filter('uwp_builder_data_type_text', array($instance, 'uwp_builder_data_type_text'), 10, 4);
        add_filter('uwp_form_builder_available_fields_head', array($instance, 'uwp_register_available_fields_head'), 10, 2);
        add_filter('uwp_form_builder_available_fields_note', array($instance, 'uwp_register_available_fields_note'), 10, 2);
        add_filter('uwp_form_builder_selected_fields_head', array($instance, 'uwp_register_selected_fields_head'), 10, 2);
        add_filter('uwp_form_builder_selected_fields_note', array($instance, 'uwp_register_selected_fields_note'), 10, 2);
        add_filter('uwp_register_fields', array($instance, 'uwp_register_extra_fields'), 10, 2);
        // htmlvar not needed for taxonomy
        add_filter('uwp_builder_htmlvar_name_taxonomy',array($instance, 'uwp_return_empty_string'),10,4);
        // default_value not needed for textarea, html, file, fieldset
        add_filter('uwp_builder_default_value_textarea',array($instance, 'uwp_return_empty_string'),10,4);
        add_filter('uwp_builder_default_value_html',array($instance, 'uwp_return_empty_string'),10,4);
        add_filter('uwp_builder_default_value_file',array($instance, 'uwp_return_empty_string'),10,4);
        add_filter('uwp_builder_default_value_fieldset',array($instance, 'uwp_return_empty_string'),10,4);
        // is_required not needed for fieldset
        add_filter('uwp_builder_is_required_fieldset',array($instance, 'uwp_return_empty_string'),10,4);
        add_filter('uwp_builder_required_msg_fieldset',array($instance, 'uwp_return_empty_string'),10,4);
        // field_icon not needed for fieldset
        add_filter('uwp_builder_css_class_fieldset',array($instance, 'uwp_return_empty_string'),10,4);
        // filters for which is_public not required
        add_filter('uwp_builder_is_public_password',array($instance, 'uwp_return_empty_string'),10,4);
    }

    public function load_menus_actions_and_filters($instance) {
        add_action( 'load-nav-menus.php', array($instance, 'users_wp_admin_menu_metabox') );
    }

    public function load_admin_actions_and_filters($instance) {
        add_action( 'admin_enqueue_scripts', array($instance, 'enqueue_styles') );
        add_action( 'admin_enqueue_scripts', array($instance, 'enqueue_scripts') );
        add_action( 'admin_menu', array($instance, 'setup_admin_menus') );
        add_action('admin_head', array($instance, 'uwp_admin_only_css'));
    }

    public function load_admin_settings_actions_and_filters($instance) {
        $instance->init_settings();

        add_action( 'admin_init', array($instance, 'uwp_register_settings') );
        //register settings
        add_action( 'userswp_settings_main_tab_content', array($instance, 'get_general_content') );
        add_action( 'userswp_settings_register_tab_content', array($instance, 'generic_display_form') );
        add_action( 'userswp_settings_login_tab_content', array($instance, 'generic_display_form') );
        add_action( 'userswp_settings_account_tab_content', array($instance, 'generic_display_form') );
        add_action( 'userswp_settings_profile_tab_content', array($instance, 'generic_display_form') );
        add_action( 'userswp_settings_users_tab_content', array($instance, 'generic_display_form') );
        add_action( 'userswp_settings_change_tab_content', array($instance, 'generic_display_form') );
        add_action( 'userswp_settings_uninstall_tab_content', array($instance, 'generic_display_form') );

        add_action( 'uwp_form_builder_settings_main_tab_content_before', array($instance, 'get_form_builder_tabs') );
        add_action( 'uwp_form_builder_settings_main_tab_content', array($instance, 'get_form_builder_content') );
        add_filter( 'uwp_display_form_title', array($instance, 'display_form_title'), 10, 3 );
        add_action( 'uwp_notifications_settings_main_tab_content', array($instance, 'get_notifications_content') );
        add_action( 'uwp_notifications_settings_admin_tab_content', array($instance, 'generic_display_form') );
    }


    
    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    UsersWP_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {
        if ( ! function_exists( 'is_plugin_active' ) ) {
            /**
             * Load all plugin functions from WordPress.
             */
            require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
        }

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once dirname(dirname( __FILE__ )) . '/includes/class-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once dirname(dirname( __FILE__ )) . '/includes/class-i18n.php';

        /**
         * The class responsible for sending emails
         * of the plugin.
         */
        require_once dirname(dirname( __FILE__ )) . '/includes/class-emails.php';

        /**
         * The class responsible for reading and updating meta
         * of the plugin.
         */
        require_once dirname(dirname( __FILE__ )) . '/includes/class-meta.php';

        /**
         * The class responsible for userswp dates
         * of the plugin.
         */
        require_once dirname(dirname( __FILE__ )) . '/includes/class-date.php';

        /**
         * The class responsible for userswp pages
         * of the plugin.
         */
        require_once dirname(dirname( __FILE__ )) . '/includes/class-pages.php';

        /**
         * The class responsible for uploading files
         * of the plugin.
         */
        require_once dirname(dirname( __FILE__ )) . '/includes/class-files.php';

        /**
         * The class responsible for defining form handler functionality
         * of the plugin.
         */
        require_once dirname(dirname( __FILE__ )) . '/includes/class-forms.php';

        /**
         * The class responsible for form validation
         * of the plugin.
         */
        require_once dirname(dirname( __FILE__ )) . '/includes/class-validation.php';

        /**
         * Country helpers
         */
        require_once dirname(dirname( __FILE__ )) . '/includes/class-countries.php';

        /**
         * The class responsible for defining ajax handler functionality
         * of the plugin.
         */
        require_once dirname(dirname( __FILE__ )) . '/includes/class-ajax.php';

        /**
         * The class responsible for defining all shortcodes
         */
        require_once dirname(dirname( __FILE__ )) . '/includes/class-templates.php';

        /**
         * The class responsible for defining profile content
         */
        require_once dirname(dirname( __FILE__ )) . '/includes/class-profile.php';

        /**
         * The class responsible for defining all shortcodes
         */
        require_once dirname(dirname( __FILE__ )) . '/includes/class-shortcodes.php';

        /**
         * The class responsible for defining all menus items.
         */
        require_once dirname(dirname( __FILE__ )) . '/admin/menus/class-checklist.php';

        /**
         * The class responsible for defining all menus in the admin area.
         */
        require_once dirname(dirname( __FILE__ )) . '/admin/menus/class-menus.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once dirname(dirname( __FILE__ )) . '/admin/class-admin.php';

        /**
         * The class responsible for defining all admin area settings.
         */
        require_once dirname(dirname( __FILE__ )) . '/admin/settings/class-settings.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once dirname(dirname( __FILE__ )) . '/public/class-public.php';

        /**
         * The class responsible for table functions
         */
        require_once dirname(dirname( __FILE__ )) . '/includes/class-tables.php';

        /**
         * The class responsible for adding fields in forms
         */
        require_once dirname(dirname( __FILE__ )) . '/admin/settings/class-formbuilder.php';

        /**
         * The class responsible for setting field callbacks
         */
        require_once dirname(dirname( __FILE__ )) . '/includes/class-callback.php';

        /**
         * The class responsible for adding tools functions
         */
        require_once dirname(dirname( __FILE__ )) . '/includes/class-tools.php';

        /**
         * The class responsible for displaying notices
         */
        require_once dirname(dirname( __FILE__ )) . '/includes/class-notices.php';

        /**
         * The class WP_Super_Duper for widgets.
         */
        require_once dirname(dirname( __FILE__ )) . '/includes/libraries/wp-super-duper.php';

        /**
         * contents helpers files and functions.
         */
        require_once( dirname(dirname( __FILE__ )) .'/includes/helpers.php' );

        /**
         * The class for login widget.
         */
        require_once( dirname(dirname( __FILE__ )) .'/widgets/login.php' );

        /**
         * The class for register widget.
         */
        require_once( dirname(dirname( __FILE__ )) .'/widgets/register.php' );

        /**
         * The class responsible for displaying notices
         */
        require_once dirname(dirname( __FILE__ )) . '/includes/class-import-export.php';

        /**
         * The class responsible for privacy policy functions
         */
        require_once dirname(dirname( __FILE__ )) . '/includes/abstract-uwp-privacy.php';
        require_once dirname(dirname( __FILE__ )) . '/includes/class-uwp-privacy.php';

        if ( is_plugin_active( 'uwp_geodirectory/uwp_geodirectory.php' ) ) {
            deactivate_plugins( 'uwp_geodirectory/uwp_geodirectory.php' );
        }

        if ( is_plugin_active( 'geodirectory/geodirectory.php' ) || class_exists('GeoDirectory') ) {
            /**
             * The class responsible for displaying notices
             *
             * @since 1.0.12
             */
            require_once dirname(dirname( __FILE__ )) . '/includes/libraries/class-geodirectory-plugin.php';
        }
    }

}