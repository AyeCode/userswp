<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://wpgeodirectory.com
 * @since      1.0.0
 *
 * @package    Users_WP
 * @subpackage Users_WP/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Users_WP
 * @subpackage Users_WP/admin
 * @author     GeoDirectory Team <info@wpgeodirectory.com>
 */
class Users_WP_Admin {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $users_wp    The ID of this plugin.
     */
    private $users_wp;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    protected $loader;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $users_wp       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct( $users_wp, $version ) {

        $this->plugin_name = $users_wp;
        $this->version = $version;

        $this->load_dependencies();


    }

    private function load_dependencies() {

        require_once dirname(dirname( __FILE__ )) . '/admin/settings/class-users-wp-admin-settings.php';

        require_once dirname(dirname( __FILE__ )) . '/admin/menus/class-users-wp-menus.php';

    }


    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Users_WP_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Users_WP_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'assets/css/users-wp-admin.css', array(), $this->version, 'all' );

    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Users_WP_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Users_WP_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'assets/js/users-wp-admin.js', array( 'jquery' ), $this->version, false );

        $ajax_cons_data = array(
            'url' => admin_url('admin-ajax.php'),
            'custom_field_not_blank_var' => __('HTML Variable Name must not be blank', 'uwp'),
            'custom_field_not_special_char' => __('Please do not use special character and spaces in HTML Variable Name.', 'uwp'),
            'custom_field_unique_name' => __('HTML Variable Name should be a unique name.', 'uwp'),
            'custom_field_delete' => __('Are you wish to delete this field?', 'uwp'),
            'custom_field_id_required' => __('This field is required.', 'uwp'),
        );
        wp_localize_script($this->plugin_name, 'uwp_admin_ajax', $ajax_cons_data);

    }

    public function setup_admin_menus() {
        $plugin_admin_settings = new Users_WP_Admin_Settings();
        add_submenu_page(
            "users.php",
            "UsersWP Settings",
            "UsersWP Settings",
            'manage_options',
            'users-wp',
            array( $plugin_admin_settings, 'users_wp_general_settings_page' )
        );
    }

}