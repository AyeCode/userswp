<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://wpgeodirectory.com
 * @since      1.0.0
 *
 * @package    Users_WP
 * @subpackage Users_WP/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Users_WP
 * @subpackage Users_WP/includes
 * @author     GeoDirectory Team <info@wpgeodirectory.com>
 */
class Users_WP {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Users_WP_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $users_wp    The string used to uniquely identify this plugin.
     */
    protected $users_wp;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

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

        $this->plugin_name = 'users-wp';
        $this->version = '1.0.0';

        $this->load_dependencies();
        $this->init_settings();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->define_shortcodes();
        $this->init_form_builder();
        $this->init_ajax();

    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Users_WP_Loader. Orchestrates the hooks of the plugin.
     * - Users_WP_i18n. Defines internationalization functionality.
     * - Users_WP_Admin. Defines all hooks for the admin area.
     * - Users_WP_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {
        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once dirname(dirname( __FILE__ )) . '/includes/class-users-wp-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once dirname(dirname( __FILE__ )) . '/includes/class-users-wp-i18n.php';

        /**
         * The class responsible for defining form handler functionality
         * of the plugin.
         */
        require_once dirname(dirname( __FILE__ )) . '/includes/class-users-wp-forms.php';

        /**
         * The class responsible for defining ajax handler functionality
         * of the plugin.
         */
        require_once dirname(dirname( __FILE__ )) . '/includes/class-users-wp-ajax.php';

        /**
         * The class responsible for defining all shortcodes
         */
        require_once dirname(dirname( __FILE__ )) . '/includes/class-users-wp-templates.php';

        /**
         * The class responsible for defining profile content
         */
        require_once dirname(dirname( __FILE__ )) . '/includes/class-users-wp-profile.php';

        /**
         * The class responsible for defining all shortcodes
         */
        require_once dirname(dirname( __FILE__ )) . '/includes/class-users-wp-shortcodes.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once dirname(dirname( __FILE__ )) . '/admin/class-users-wp-admin.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once dirname(dirname( __FILE__ )) . '/public/class-users-wp-public.php';

        require_once dirname(dirname( __FILE__ )) . '/admin/settings/class-users-wp-form-builder.php';


        $this->loader = new Users_WP_Loader();

    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Users_WP_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale() {

        $plugin_i18n = new Users_WP_i18n();

        $this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {

        $plugin_admin = new Users_WP_Admin( $this->get_plugin_name(), $this->get_version());
        $plugin_admin_settings = new Users_WP_Admin_Settings();
        $plugin_admin_menus = new Users_WP_Menus();

        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
        $this->loader->add_action( 'admin_menu', $plugin_admin, 'setup_admin_menus' );
        $this->loader->add_action( 'admin_init', $plugin_admin_settings, 'uwp_register_settings' );
        $this->loader->add_action( 'load-nav-menus.php', $plugin_admin_menus, 'users_wp_admin_menu_metabox' );

        //register settings
        $this->loader->add_action( 'uwp_settings_general_tab_content', $plugin_admin_settings, 'get_general_content' );
        $this->loader->add_action( 'uwp_settings_form_builder_tab_content', $plugin_admin_settings, 'get_form_builder_content' );
        $this->loader->add_action( 'uwp_settings_recaptcha_tab_content', $plugin_admin_settings, 'get_recaptcha_content' );
        $this->loader->add_action( 'uwp_settings_geodirectory_tab_content', $plugin_admin_settings, 'get_geodirectory_content' );
        $this->loader->add_action( 'uwp_settings_notifications_tab_content', $plugin_admin_settings, 'get_notifications_content' );

    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks() {

        $plugin_public = new Users_WP_Public( $this->get_plugin_name(), $this->get_version() );

        $forms = new Users_WP_Forms();
        $templates = new Users_WP_Templates($this->loader);

        $profile = new Users_WP_Profile($this->loader);

        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

        $this->loader->add_action( 'init', $forms, 'handler' );
        $this->loader->add_action( 'uwp_template_form_title_after', $forms, 'display_notices' );
        $this->loader->add_action( 'template_redirect', $templates, 'access_checks' );
        $this->loader->add_action( 'uwp_template_fields', $templates, 'uwp_template_fields', 10, 1 );

        $this->loader->add_filter( 'the_content', $templates, 'uwp_author_page_content', 10, 1 );

        //profile page
        $this->loader->add_filter('query_vars', $profile, 'profile_query_vars', 10, 1 );
        $this->loader->add_action('init', $profile, 'rewrite_profile_link' , 10, 1 );
        $this->loader->add_filter( 'uwp_profile_link', $profile, 'get_profile_link', 10, 2 );
        $this->loader->add_filter( 'the_title', $profile, 'modify_profile_page_title', 10, 2 );

        $this->loader->add_action( 'uwp_profile_header', $profile, 'get_profile_header', 10, 1 );
        $this->loader->add_action( 'uwp_profile_title', $profile, 'get_profile_title', 10, 1 );
        $this->loader->add_action( 'uwp_profile_bio', $profile, 'get_profile_bio', 10, 1 );
        $this->loader->add_action( 'uwp_profile_social', $profile, 'get_profile_social', 10, 1 );

        $this->loader->add_action( 'uwp_profile_content', $profile, 'get_profile_tabs_content', 10, 1 );
        $this->loader->add_action( 'uwp_profile_pagination', $profile, 'get_profile_pagination');
        $this->loader->add_action( 'uwp_profile_posts_tab_content', $profile, 'get_profile_posts', 10, 1);
        $this->loader->add_action( 'uwp_profile_comments_tab_content', $profile, 'get_profile_comments', 10, 1);

    }

    private function define_shortcodes() {

        $shortcodes = new Users_WP_Shortcodes($this->loader);

        add_shortcode( 'uwp_register', array($shortcodes,'register'));
        add_shortcode( 'uwp_login', array($shortcodes,'login'));
        add_shortcode( 'uwp_forgot', array($shortcodes,'forgot'));
        add_shortcode( 'uwp_account', array($shortcodes,'account'));
        add_shortcode( 'uwp_profile', array($shortcodes,'profile'));
        add_shortcode( 'uwp_users', array($shortcodes,'users'));


    }

    private function init_settings() {

        global $uwp_options;
        $plugin_admin = new Users_WP_Admin( $this->get_plugin_name(), $this->get_version()); //required to load dependencies
        $plugin_admin_settings = new Users_WP_Admin_Settings();
        $uwp_options = $plugin_admin_settings->uwp_get_settings();

    }

    public function init_form_builder() {
        $form_builder = new Users_WP_Form_Builder();

        $this->loader->add_action('uwp_manage_available_fields_predefined', $form_builder, 'uwp_manage_available_fields_predefined');
        $this->loader->add_action('uwp_manage_available_fields_custom', $form_builder, 'uwp_manage_available_fields_custom');
        $this->loader->add_action('uwp_manage_available_fields', $form_builder, 'uwp_manage_available_fields');
        $this->loader->add_action('uwp_manage_selected_fields', $form_builder, 'uwp_manage_selected_fields');

        $this->loader->add_filter('uwp_cfa_extra_fields_multiselect', $form_builder, 'uwp_cfa_extra_fields_smr', 10, 4);
        $this->loader->add_filter('uwp_cfa_extra_fields_select', $form_builder, 'uwp_cfa_extra_fields_smr', 10, 4);
        $this->loader->add_filter('uwp_cfa_extra_fields_radio', $form_builder, 'uwp_cfa_extra_fields_smr', 10, 4);

        $this->loader->add_filter('uwp_cfa_extra_fields_datepicker', $form_builder, 'uwp_cfa_extra_fields_datepicker', 10, 4);

        // htmlvar not needed for fieldset and taxonomy
        $this->loader->add_filter('uwp_cfa_htmlvar_name_fieldset',$form_builder, 'return_empty_string',10,4);
        $this->loader->add_filter('uwp_cfa_htmlvar_name_taxonomy',$form_builder, 'return_empty_string',10,4);


        // default_value not needed for textarea, html, file, fieldset
        $this->loader->add_filter('uwp_cfa_default_value_textarea',$form_builder, 'return_empty_string',10,4);
        $this->loader->add_filter('uwp_cfa_default_value_html',$form_builder, 'return_empty_string',10,4);
        $this->loader->add_filter('uwp_cfa_default_value_file',$form_builder, 'return_empty_string',10,4);
        $this->loader->add_filter('uwp_cfa_default_value_fieldset',$form_builder, 'return_empty_string',10,4);

        // is_required not needed for fieldset
        $this->loader->add_filter('uwp_cfa_is_required_fieldset',$form_builder, 'return_empty_string',10,4);
        $this->loader->add_filter('uwp_cfa_required_msg_fieldset',$form_builder, 'return_empty_string',10,4);

        // field_icon not needed for fieldset
        $this->loader->add_filter('uwp_cfa_field_icon_fieldset',$form_builder, 'return_empty_string',10,4);
        $this->loader->add_filter('uwp_cfa_css_class_fieldset',$form_builder, 'return_empty_string',10,4);

    }

    public function init_ajax() {
        $form_builder = new Users_WP_Form_Builder();
        $ajax = new Users_WP_Ajax($form_builder);

        $this->loader->add_action('wp_ajax_uwp_ajax_action', $ajax, 'handler');
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
     * @return    Users_WP_Loader    Orchestrates the hooks of the plugin.
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


}