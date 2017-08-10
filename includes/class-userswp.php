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
//    protected $templates;
    protected $assets;
    protected $admin;
    protected $admin_settings;

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
        $this->loader = new Users_WP_Loader();
        $this->profile = new Users_WP_Profile();
        $this->forms = new Users_WP_Forms();
        $this->i18n = new Users_WP_i18n();
        $this->notices = new Users_WP_Notices();
//        $this->templates = new Users_WP_Templates();
        $this->assets = new Users_WP_Public();
        $this->admin_settings = new Users_WP_Admin_Settings();
        $this->admin = new Users_WP_Admin($this->admin_settings);

    }

    /**
     * Load the required dependencies for this plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {
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