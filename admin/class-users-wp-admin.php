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
     * @param $hook_suffix
     */
    public function enqueue_styles($hook_suffix) {

        /**
         * An instance of this class should be passed to the run() function
         * defined in Users_WP_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Users_WP_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        if ($hook_suffix == 'profile.php' || $hook_suffix == 'user-edit.php') {
            wp_register_style('jquery-ui', '//ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css');
            wp_enqueue_style( 'jquery-ui' );
            wp_enqueue_style( 'jcrop' );
            wp_enqueue_style( "userswp", plugin_dir_url(dirname(__FILE__)) . 'public/assets/css/users-wp.css', array(), null, 'all' );
            wp_enqueue_style( "uwp_timepicker_css", plugin_dir_url( dirname(__FILE__) ) . 'public/assets/css/jquery.ui.timepicker.css', array(), null, 'all' );
        }
        if ($hook_suffix == 'userswp_page_uwp_tools') {
            wp_enqueue_style( "userswp", plugin_dir_url(dirname(__FILE__)) . 'public/assets/css/users-wp.css', array(), null, 'all' );
        }
        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'assets/css/users-wp-admin.css', array(), $this->version, 'all' );
        wp_enqueue_style( "uwp_chosen_css", plugin_dir_url(dirname(__FILE__)) . 'public/assets/css/chosen.css', array(), $this->version, 'all' );
        uwp_load_font_awesome();

    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     * @param $hook_suffix
     */
    public function enqueue_scripts($hook_suffix) {

        /**
         * An instance of this class should be passed to the run() function
         * defined in Users_WP_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Users_WP_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        if ($hook_suffix == 'profile.php' || $hook_suffix == 'user-edit.php') {
            wp_enqueue_script( 'jquery-ui-datepicker', array( 'jquery' ) );
            wp_enqueue_script( "uwp_timepicker", plugin_dir_url( dirname(__FILE__) ) . 'public/assets/js/jquery.ui.timepicker.min.js', array( 'jquery', 'jquery-ui-datepicker', 'jquery-ui-core' ), null, false );
            wp_enqueue_script( "userswp", plugin_dir_url(dirname(__FILE__)) . 'public/assets/js/users-wp.js', array( 'jquery' ), null, false );
            wp_enqueue_script( 'jquery-ui-progressbar', array( 'jquery' ) );
            wp_enqueue_script( 'jcrop', array( 'jquery' ) );
        }
        if ($hook_suffix == 'userswp_page_uwp_tools') {
            wp_enqueue_script( 'jquery-ui-progressbar', array( 'jquery' ) );
        }
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'assets/js/users-wp-admin.js', array( 'jquery' ), null, false );
        wp_enqueue_script( "uwp_chosen", plugin_dir_url(dirname(__FILE__)) . 'public/assets/js/chosen.jquery.js', array( 'jquery' ), $this->version, false );

        $ajax_cons_data = array(
            'url' => admin_url('admin-ajax.php'),
            'custom_field_not_blank_var' => __('HTML Variable Name must not be blank', 'userswp'),
            'custom_field_options_not_blank_var' => __('Option Values must not be blank', 'userswp'),
            'custom_field_not_special_char' => __('Please do not use special character and spaces in HTML Variable Name.', 'userswp'),
            'custom_field_unique_name' => __('HTML Variable Name should be a unique name.', 'userswp'),
            'custom_field_delete' => __('Are you wish to delete this field?', 'userswp'),
            'custom_field_id_required' => __('This field is required.', 'userswp'),
        );
        wp_localize_script($this->plugin_name, 'uwp_admin_ajax', $ajax_cons_data);

    }

    public function setup_admin_menus() {

        $install_type = uwp_get_installation_type();

        // Proceed if main site or pages on all sites or specific blog id
        $proceed = false;
        $show_builder = false;
        switch ($install_type) {
            case "single":
                $proceed = true;
                $show_builder = true;
                break;
            case "multi_na_all":
                $proceed = true;
                break;
            case "multi_na_site_id":
                if (defined('UWP_ROOT_PAGES')) {
                    $blog_id = UWP_ROOT_PAGES;
                } else {
                    $blog_id = null;
                }
                $current_blog_id = get_current_blog_id();
                if (!is_int($blog_id)) {
                    $proceed = false;
                } else {
                    if ($blog_id == $current_blog_id) {
                        $proceed = true;
                        $show_builder = true;
                    } else {
                        $proceed = false;
                    }
                }
                break;
            case "multi_na_default":
                $is_main_site = is_main_site();
                if ($is_main_site) {
                    $proceed = true;
                    $show_builder = true;
                }
                break;
            case "multi_not_na":
                $proceed = true;
                $show_builder = true;
                break;
            default:
                $proceed = false;

        }

        if (!$proceed) {
            return;
        }


        $plugin_admin_settings = new Users_WP_Admin_Settings();

        add_menu_page(
            'UsersWP Settings',
            'UsersWP',
            'manage_options',
            'userswp',
            array( $plugin_admin_settings, 'uwp_settings_page' ),
            'dashicons-admin-users',
            70
        );

        if ($show_builder) {
            add_submenu_page(
                "userswp",
                "Form Builder",
                "Form Builder",
                'manage_options',
                'uwp_form_builder',
                array($plugin_admin_settings, 'uwp_settings_page')
            );

            add_submenu_page(
                "userswp",
                "Notifications",
                "Notifications",
                'manage_options',
                'uwp_notifications',
                array($plugin_admin_settings, 'uwp_settings_page')
            );

            $settings_page = array($plugin_admin_settings, 'uwp_settings_page');
            do_action('uwp_admin_sub_menus', $settings_page, $plugin_admin_settings);
        }
    }

}