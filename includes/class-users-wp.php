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
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->define_shortcodes();
        $this->init_form_builder();

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
         * The class responsible for defining all shortcodes
         */
        require_once dirname(dirname( __FILE__ )) . '/includes/class-users-wp-templates.php';

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
        $this->loader->add_action( 'admin_init', $plugin_admin_settings, 'users_wp_register_general_settings' );
        $this->loader->add_action( 'load-nav-menus.php', $plugin_admin_menus, 'users_wp_admin_menu_metabox' );

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

        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

        $this->loader->add_action( 'uwp_template_form_title_after', $forms, 'handler' );
        $this->loader->add_action( 'template_redirect', $templates, 'access_checks' );
        $this->loader->add_action( 'uwp_template_fields', $templates, 'uwp_template_fields', 10, 1 );

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

    public function init_form_builder() {
        $form_builder = new Users_WP_Form_Builder();

        $this->loader->add_action('uwp_manage_available_fields_predefined', $form_builder, 'uwp_manage_available_fields_predefined');
        $this->loader->add_action('uwp_manage_available_fields_custom', $form_builder, 'uwp_manage_available_fields_custom');
        $this->loader->add_action('uwp_manage_available_fields', $form_builder, 'uwp_manage_available_fields');
        $this->loader->add_action('uwp_manage_selected_fields', $form_builder, 'uwp_manage_selected_fields');
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