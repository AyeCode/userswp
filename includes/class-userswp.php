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
final class UsersWP {

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    public $profile;
    public $forms;
    protected $i18n;
    protected $notices;
    protected $templates;
    protected $meta;
    protected $pages;
    protected $files;
    protected $shortcodes;
    protected $assets;
    protected $admin;
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
        $this->init_hooks();

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
        $this->admin = new UsersWP_Admin();
        $this->admin_menus = new UsersWP_Admin_Menus();
        $this->ajax = new UsersWP_Ajax($this->form_builder);
        $this->files = new UsersWP_Files();
        $this->notifications = new UsersWP_Notifications();

        
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
        $this->load_tables_actions_and_filters($this->tables);
        $this->load_templates_actions_and_filters($this->templates);
        $this->load_tools_actions_and_filters($this->tools);
        $this->load_notifications_actions_and_filters($this->notifications);

        //admin
        $this->load_form_builder_actions_and_filters($this->form_builder);
        $this->load_menus_actions_and_filters($this->menus);
        $this->load_admin_actions_and_filters($this->admin);
        
    }

    /**
     * Hook into actions and filters.
     */
    private function init_hooks() {
        register_activation_hook( USERSWP_PLUGIN_FILE, array( 'UsersWP_Activator', 'activate' ) );
        register_deactivation_hook( USERSWP_PLUGIN_FILE, array( 'UsersWP_Deactivator', 'deactivate' ) );
        add_action( 'admin_init', array('UsersWP_Activator', 'uwp_automatic_upgrade') );
        add_action( 'init', array( 'UsersWP_Activator', 'init_background_updater' ), 5 );
        add_action( 'widgets_init', array( $this, 'register_widgets' ) );
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
        add_filter('uwp_update_usermeta', array($instance, 'modify_datepicker_value_on_update'), 10, 3);
        add_filter('uwp_get_usermeta', array($instance, 'modify_datepicker_value_on_get'), 10, 4);
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
        
        // login
        add_action('wp_ajax_nopriv_uwp_ajax_login_form', array($instance, 'ajax_login_form'));
        add_action('wp_ajax_nopriv_uwp_ajax_login', array($instance, 'process_login'));
        
        // register
        add_action('wp_ajax_nopriv_uwp_ajax_register_form', array($instance, 'ajax_register_form'));
        add_action('wp_ajax_nopriv_uwp_ajax_register', array($instance, 'process_register'));

        // forgot
        add_action('wp_ajax_nopriv_uwp_ajax_forgot_password_form', array($instance, 'ajax_forgot_password_form'));
        add_action('wp_ajax_nopriv_uwp_ajax_forgot_password', array($instance, 'process_forgot'));

        // general
        add_action('init', array($instance, 'init_notices'), 1);
        add_action('uwp_loaded', array($instance, 'handler'));
        add_action('init', array($instance, 'uwp_privacy_submit_handler'));
        add_action('uwp_template_display_notices', array($instance, 'display_notices'), 10, 1);
        add_action('wp_ajax_uwp_upload_file_remove', array($instance, 'uwp_upload_file_remove'));
        //User search form
        add_action('personal_options_update', array($instance, 'update_profile_extra_admin_edit'), 10, 1);
        add_action('edit_user_profile_update', array($instance, 'update_profile_extra_admin_edit'), 10, 1);
        add_action('user_edit_form_tag', array($instance, 'add_multipart_to_admin_edit_form'));
        add_action('uwp_template_form_title_after', array($instance, 'uwp_display_username_in_account'), 10, 1);
        add_action('init', array($instance, 'process_login'));
        add_action('init', array($instance, 'process_register'));
        add_action('init', array($instance, 'process_account'));
        add_action('init', array($instance, 'process_forgot'));
        add_action('init', array($instance, 'process_change'));
        add_action('init', array($instance, 'process_reset'));

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
        add_filter('uwp_form_input_email_uwp_account_email_after', array($instance, 'uwp_register_confirm_email_field'), 10, 4);
        add_filter('uwp_form_input_password_uwp_account_password_after', array($instance, 'uwp_register_confirm_password_field'), 10, 4);
        
        // Emails
        add_filter('uwp_send_mail_extras', array($instance, 'init_mail_extras'), 10, 3);
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
        add_action( 'admin_notices', array($instance, 'try_bootstrap') );
    }

    public function load_pages_actions_and_filters($instance) {
        add_action( 'wpmu_new_blog', array($instance, 'wpmu_generate_default_pages_on_new_site'), 10, 6 );
        add_filter( 'display_post_states', array( $instance, 'add_display_post_states' ), 10, 2 );
    }

    public function load_profile_actions_and_filters($instance) {
        add_action( 'template_redirect', array($instance, 'uwp_redirect_author_page') , 10 , 2 );
        //profile page
        add_filter('query_vars', array($instance, 'profile_query_vars'), 10, 1 );
        add_action('init', array($instance, 'rewrite_profile_link') , 10, 1 );
        add_filter( 'author_link', array($instance, 'get_profile_link'), 10, 2 );
        add_filter( 'edit_profile_url', array($instance, 'uwp_modify_admin_bar_edit_profile_url'), 10, 3);
        add_filter( 'the_title', array($instance, 'modify_profile_page_title'), 10, 2 );
        add_filter( 'get_comment_author_link', array($instance, 'uwp_get_comment_author_link') , 10 , 2 );
//        add_action( 'uwp_profile_header', array($instance, 'get_profile_header'), 10, 4 );
        add_action( 'uwp_users_profile_header', array($instance, 'get_profile_header'), 10, 1 );
        add_action( 'uwp_user_title', array($instance, 'get_profile_title'), 10, 2 );
        add_action( 'uwp_profile_social', array($instance, 'get_profile_social'), 10, 2 );
        add_action( 'get_avatar_url', array($instance, 'get_avatar_url'), 99, 3 );
        add_action( 'uwp_profile_pagination' ,array($instance,'list_view_js'));


        add_action( 'uwp_after_users_list' ,array($instance,'list_view_js'));




        //Fields as tabs
        add_action( 'uwp_available_tab_items', array($instance, 'uwp_extra_fields_available_tab_items'), 10, 1 );
        add_action( 'uwp_profile_tabs', array($instance, 'uwp_extra_fields_as_tabs'), 10, 3 );

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
        add_action( 'uwp_profile_body', array($instance, 'get_profile_body'), 10, 1 );
        add_action( 'uwp_profile_content', array($instance, 'get_profile_tabs_content'), 10, 1 );
        add_action( 'uwp_profile_more_info_tab_content', array($instance, 'get_profile_more_info'), 10, 1);
        add_action( 'uwp_profile_posts_tab_content', array($instance, 'get_profile_posts'), 10, 1);
        add_action( 'uwp_profile_tab_icon', array($instance, 'get_profile_tab_icon'), 10, 3);
        add_action( 'uwp_profile_comments_tab_content', array($instance, 'get_profile_comments'), 10, 1);
        //add_action( 'uwp_profile_tab_content', array($instance, 'uwp_extra_fields_as_tab_values'), 10, 2 );

        // Profile Pagination
        add_action( 'uwp_profile_pagination', array($instance, 'get_profile_pagination'));
        
        // Profile title
        add_action( 'uwp_profile_after_title',array($instance, 'edit_profile_button'));

        // Users
        add_action( 'uwp_output_location', array($instance, 'show_output_location_data'), 10, 2);
        add_action( 'wpdiscuz_profile_url', array($instance, 'uwp_wpdiscuz_profile_url'), 10, 2);

        // User, allow subscribers to upload profile and banner pictures
        add_filter( 'plupload_default_params', array($instance, 'add_uwp_plupload_param'), 10, 1 );
        add_filter( 'user_has_cap', array($instance, 'allow_all_users_profile_uploads'), 10, 4 );
    }

    public function load_tables_actions_and_filters($instance) {
        add_filter( 'wpmu_drop_tables', array($instance, 'drop_tables_on_delete_blog'));
    }
    
    public function load_templates_actions_and_filters($instance) {

        add_action( 'template_redirect', array($instance, 'change_default_password_redirect') );
        add_action( 'uwp_template_fields', array($instance, 'uwp_template_fields'), 10, 1 );
        add_action( 'uwp_template_fields', array($instance, 'uwp_template_extra_fields'), 10, 1 );
        add_action( 'uwp_account_form_display', array($instance, 'uwp_account_edit_form_display'), 10, 1 );
        add_action( 'wp_logout', array($instance, 'logout_redirect'));
        add_action( 'init', array($instance, 'wp_login_redirect'));
        add_action( 'init', array($instance, 'wp_register_redirect'));
        add_action( 'admin_init', array($instance, 'uwp_activation_redirect'));
        // Redirect functions
        add_action( 'template_redirect', array($instance, 'profile_redirect'), 10);
        add_action( 'template_redirect', array($instance, 'access_checks'), 20);
        // Admin user edit page
        add_action( 'edit_user_profile', array($instance, 'get_profile_extra_admin_edit'), 10, 1 );
        add_action( 'show_user_profile', array($instance, 'get_profile_extra_admin_edit'), 10, 1 );


        add_filter( 'wp_setup_nav_menu_item', array($instance, 'uwp_setup_nav_menu_item'), 10, 1 );
        add_filter( 'the_content', array($instance, 'uwp_author_page_content'), 10, 1 );
        add_filter( 'the_content', array($instance, 'uwp_author_box_page_content'), 10, 1 );
        add_filter( 'the_content', array($instance, 'setup_singular_page_content'), 10, 1 );
        add_filter( 'body_class', array($instance, 'uwp_add_body_class'), 10, 1 );

        // filter the login and register url
        add_filter( 'login_url', array($instance, 'wp_login_url'), 10, 3 );
        add_filter( 'register_url', array($instance, 'wp_register_url'), 10, 1 );
        add_filter( 'lostpassword_url', array($instance, 'wp_lostpassword_url'), 10, 1 );
        
    }
    
    public function load_tools_actions_and_filters($instance) {
        add_action('uwp_admin_sub_menus', array($instance, 'uwp_add_admin_tools_sub_menu'), 100, 1);
        add_action('uwp_tools_settings_main_tab_content', array($instance, 'uwp_tools_main_tab_content'));
        add_action('wp_ajax_uwp_process_diagnosis', array($instance, 'uwp_process_diagnosis_ajax'));
    }

    public function load_notifications_actions_and_filters($instance){
        add_action('uwp_account_form_display', array($instance, 'uwp_user_notifications_form_front'), 10, 1);
        add_action('init', array($instance, 'uwp_notification_submit_handler'));
    }

    public function load_form_builder_actions_and_filters($instance) {
        // Actions
        add_action('admin_init', array($instance, 'uwp_form_builder_dummy_fields'));
        add_action('uwp_manage_available_fields_predefined', array($instance, 'uwp_manage_available_fields_predefined'));
        add_action('uwp_manage_available_fields_custom', array($instance, 'uwp_manage_available_fields_custom'));
        add_action('uwp_manage_available_fields', array($instance, 'uwp_manage_available_fields'));
        add_action('uwp_manage_selected_fields', array($instance, 'uwp_manage_selected_fields'));
        add_action('uwp_admin_extra_custom_fields', array($instance, 'uwp_advance_admin_custom_fields'), 10, 2);
        add_action('wp_ajax_uwp_ajax_register_action', array($instance, 'uwp_register_ajax_handler'));
        add_action('uwp_form_builder_tabs_content', array($instance, 'uwp_form_builder'));

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
        add_action( 'admin_bar_menu', array($instance, 'admin_bar_menu'), 51 );
    }

    public function load_admin_actions_and_filters($instance) {
        add_action( 'admin_enqueue_scripts', array($instance, 'enqueue_styles') );
        add_action( 'admin_enqueue_scripts', array($instance, 'enqueue_scripts') );
        add_action('admin_head', array($instance, 'uwp_admin_only_css'));
    }

    public function register_widgets(){
        register_widget("UWP_Register_Widget");
        register_widget("UWP_Forgot_Widget");
        register_widget("UWP_Login_Widget");
        register_widget("UWP_Change_Widget");
        register_widget("UWP_Reset_Widget");
        register_widget("UWP_Users_Widget");
        register_widget("UWP_Account_Widget");
        register_widget("UWP_Profile_Widget");

        register_widget("UWP_Profile_Header_Widget");
        register_widget("UWP_Profile_Social_Widget");
        register_widget("UWP_Profile_Tabs_Widget");
        register_widget("UWP_Profile_Actions_Widget");
        register_widget("UWP_Profile_Section_Widget");

        register_widget("UWP_User_Title_Widget");
        register_widget("UWP_User_Avatar_Widget");
        register_widget("UWP_User_Meta_Widget");
        register_widget("UWP_Users_Search_Widget");
        register_widget("UWP_Users_Loop_Actions");
        register_widget("UWP_Users_Loop_Widget");
        register_widget("UWP_User_Actions_Widget");
        register_widget("UWP_Output_Location_Widget");
        register_widget("UWP_Author_Box_Widget");
        register_widget("UWP_Button_Group_Widget");
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
        global $uwp_options;

        if ( ! function_exists( 'is_plugin_active' ) ) {
            /**
             * Load all plugin functions from WordPress.
             */
            require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
        }

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once dirname(dirname( __FILE__ )) . '/includes/class-i18n.php';

        require_once dirname(dirname( __FILE__ )) . '/admin/settings/functions.php';

        $uwp_options = uwp_get_settings();


        require_once( dirname(dirname( __FILE__ )) . '/upgrade.php' );

        /**
         * The class responsible for activation functionality
         * of the plugin.
         */
        require_once dirname(dirname( __FILE__ )) . '/includes/class-activator.php';

        /**
         * The class responsible for deactivation functionality
         * of the plugin.
         */
        require_once dirname(dirname( __FILE__ )) . '/includes/class-deactivator.php';

        /**
         * The libraries required.
         */
        require_once dirname(dirname( __FILE__ )) . '/vendor/autoload.php';

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
         * The class responsible for defining all menus in the admin area.
         */
        require_once dirname(dirname( __FILE__ )) . '/admin/class-uwp-admin-menus.php';

        /**
         * The class responsible for defining all admin area settings.
         */
        require_once dirname(dirname( __FILE__ )) . '/admin/settings/class-settings.php';

        /**
         * The class responsible for default content.
         */
        require_once dirname(dirname( __FILE__ )) . '/includes/class-uwp-defaults.php';

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
         * The class responsible for admin settings functions
         */
        include_once dirname(dirname( __FILE__ )) . '/admin/settings/class-uwp-settings-page.php';

        /**
         * The class responsible for adding fields in forms
         */
        require_once dirname(dirname( __FILE__ )) . '/admin/settings/class-formbuilder.php';

	    /**
	     * The class responsible for defining all admin area settings.
	     */
	    require_once dirname(dirname( __FILE__ )) . '/admin/settings/class-uwp-settings-profile-tabs.php';

        /**
         * The class responsible for adding tools functions
         */
        require_once dirname(dirname( __FILE__ )) . '/includes/class-tools.php';

        /**
         * The class responsible for displaying notices
         */
        require_once dirname(dirname( __FILE__ )) . '/includes/class-notices.php';

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
         * The class for forgot password widget.
         */
        require_once( dirname(dirname( __FILE__ )) .'/widgets/forgot.php' );

        /**
         * The class for reset password widget.
         */
        require_once( dirname(dirname( __FILE__ )) .'/widgets/reset.php' );

        /**
         * The class for change password widget.
         */
        require_once( dirname(dirname( __FILE__ )) .'/widgets/change.php' );

        /**
         * The class for users widget.
         */
        require_once( dirname(dirname( __FILE__ )) .'/widgets/users.php' );

        /**
         * The class for account widget.
         */
        require_once( dirname(dirname( __FILE__ )) .'/widgets/account.php' );

        /**
         * The class for profile widget.
         */
        require_once( dirname(dirname( __FILE__ )) .'/widgets/profile.php' );

        /**
         * The class for profile sections widget.
         */
        require_once( dirname(dirname( __FILE__ )) .'/widgets/profile-section.php' );

        /**
         * The class profile header widget
         */
        require_once dirname(dirname( __FILE__ )) . '/widgets/profile-header.php';

        /**
         * The class for user title widget.
         */
        require_once( dirname(dirname( __FILE__ )) .'/widgets/user-title.php' );

        /**
         * The class for user avatar widget.
         */
        require_once( dirname(dirname( __FILE__ )) .'/widgets/user-avatar.php' );

        /**
         * The class for profile social fields widget.
         */
        require_once( dirname(dirname( __FILE__ )) .'/widgets/profile-social.php' );

        /**
         * The class for profile action buttons fields widget.
         */
        require_once( dirname(dirname( __FILE__ )) .'/widgets/profile-actions.php' );

        /**
         * The class for profile buttons fields widget.
         */
        require_once( dirname(dirname( __FILE__ )) .'/widgets/user-actions.php' );

        /**
         * The class for profile content widget.
         */
        require_once( dirname(dirname( __FILE__ )) .'/widgets/profile-tabs.php' );

        /**
         * The class for user meta widget.
         */
        require_once( dirname(dirname( __FILE__ )) .'/widgets/user-meta.php' );

        /**
         * The class for users search widget.
         */
        require_once( dirname(dirname( __FILE__ )) .'/widgets/users-search.php' );

        /**
         * The class for user list sorting widget.
         */
        require_once( dirname(dirname( __FILE__ )) .'/widgets/users-loop-actions.php' );

        /**
         * The class for users list widget.
         */
        require_once( dirname(dirname( __FILE__ )) .'/widgets/users-loop.php' );

        /**
         * The class for output location widget.
         */
        require_once( dirname(dirname( __FILE__ )) .'/widgets/output-location.php' );

        /**
         * The class for author box widget.
         */
        require_once( dirname(dirname( __FILE__ )) .'/widgets/authorbox.php' );

        /**
         * The class for button group widget.
         */
        require_once( dirname(dirname( __FILE__ )) .'/widgets/button-group.php' );

        /**
         * The class responsible for displaying notices
         */
        require_once dirname(dirname( __FILE__ )) . '/includes/class-import-export.php';

        /**
         * The class responsible for displaying notices
         */
        require_once dirname(dirname( __FILE__ )) . '/includes/class-user-notifications.php';

        /**
         * The class responsible for adding tools functions
         */
        require_once dirname(dirname( __FILE__ )) . '/includes/class-status.php';

        /**
         * The class responsible for extensions screen functions on admin side
         */
        require_once dirname(dirname( __FILE__ )) . '/includes/libraries/class-ayecode-addons.php';

        /**
         * The class responsible for extensions screen functions on admin side
         */
        require_once dirname(dirname( __FILE__ )) . '/includes/class-addons.php';

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

        if ( class_exists('WPInv_Invoice') ) {
            /**
             * The class responsible for displaying notices
             *
             * @since 1.0.12
             */
            require_once dirname(dirname( __FILE__ )) . '/includes/libraries/class-invoicing-plugin.php';
        }
    }

}