<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://wpgeodirectory.com
 * @since      1.0.0
 *
 * @package    userswp
 * @subpackage userswp/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    userswp
 * @subpackage userswp/admin
 * @author     GeoDirectory Team <info@wpgeodirectory.com>
 */
class UsersWP_Admin {

    protected $admin_settings;
    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     */
    public function __construct($admin_settings) {

        $this->admin_settings = $admin_settings;
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
         * defined in UsersWP_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The UsersWP_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        if ($hook_suffix == 'profile.php' || $hook_suffix == 'user-edit.php') {
            wp_register_style('jquery-ui', plugin_dir_url(dirname(__FILE__)) .  'public/assets/css/jquery-ui.css');
            wp_enqueue_style( 'jquery-ui' );
            wp_enqueue_style( 'jcrop' );
            wp_enqueue_style( "userswp", plugin_dir_url(dirname(__FILE__)) . 'public/assets/css/users-wp.css', array(), null, 'all' );
            wp_enqueue_style( "uwp_timepicker_css", plugin_dir_url( dirname(__FILE__) ) . 'public/assets/css/jquery.ui.timepicker.css', array(), null, 'all' );
        }
        if ($hook_suffix == 'userswp_page_uwp_tools') {
            wp_enqueue_style( "userswp", plugin_dir_url(dirname(__FILE__)) . 'public/assets/css/users-wp.css', array(), null, 'all' );
        }
        wp_enqueue_style( "userswp_admin_css", plugin_dir_url( __FILE__ ) . 'assets/css/users-wp-admin.css', array(), USERSWP_VERSION, 'all' );
        wp_enqueue_style( "uwp_chosen_css", plugin_dir_url(dirname(__FILE__)) . 'public/assets/css/chosen.css', array(), USERSWP_VERSION, 'all' );

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
         * defined in UsersWP_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The UsersWP_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        if ($hook_suffix == 'profile.php' || $hook_suffix == 'user-edit.php') {

            wp_enqueue_script( 'jquery-ui-datepicker', array( 'jquery' ) );
            wp_enqueue_script( "uwp_timepicker", plugin_dir_url( dirname(__FILE__) ) . 'public/assets/js/jquery.ui.timepicker.min.js', array( 'jquery', 'jquery-ui-datepicker', 'jquery-ui-core' ), null, false );
            wp_enqueue_script( "userswp", plugin_dir_url(dirname(__FILE__)) . 'public/assets/js/users-wp.min.js', array( 'jquery' ), null, false );
            $uwp_localize_data = uwp_get_localize_data();
            wp_localize_script('userswp', 'uwp_localize_data', $uwp_localize_data);
            wp_enqueue_script( 'jquery-ui-progressbar', array( 'jquery' ) );
            wp_enqueue_script( 'jcrop', array( 'jquery' ) );
            wp_enqueue_script( "country-select", plugin_dir_url(dirname(__FILE__)) . 'public/assets/js/countrySelect.min.js', array( 'jquery' ), null, false );


        }
        if ($hook_suffix == 'userswp_page_uwp_tools') {
            wp_enqueue_script( 'jquery-ui-progressbar', array( 'jquery' ) );
        }
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script( "userswp_admin", plugin_dir_url( __FILE__ ) . 'assets/js/users-wp-admin.min.js', array( 'jquery' ), null, false );
        wp_enqueue_script( "uwp_chosen", plugin_dir_url(dirname(__FILE__)) . 'public/assets/js/chosen.jquery.js', array( 'jquery' ), USERSWP_VERSION, false );
        wp_enqueue_script( "uwp_chosen_order", plugin_dir_url( __FILE__ ) . 'assets/js/chosen.order.jquery.min.js', array( 'jquery' ), USERSWP_VERSION, false );

        if ($hook_suffix == 'userswp_page_uwp_status') {
            wp_enqueue_script( "uwp_status", USERSWP_PLUGIN_URL . '/admin/assets/js/system-status.js', array( 'jquery' ), USERSWP_VERSION, true );
        }

        $ajax_cons_data = array(
            'url' => admin_url('admin-ajax.php'),
            'custom_field_not_blank_var' => __('HTML Variable Name must not be blank', 'userswp'),
            'custom_field_options_not_blank_var' => __('Option Values must not be blank', 'userswp'),
            'custom_field_not_special_char' => __('Please do not use special character and spaces in HTML Variable Name.', 'userswp'),
            'custom_field_unique_name' => __('HTML Variable Name should be a unique name.', 'userswp'),
            'custom_field_delete' => __('Are you sure you wish to delete this field?', 'userswp'),
            'custom_field_id_required' => __('This field is required.', 'userswp'),
        );
        wp_localize_script("userswp_admin", 'uwp_admin_ajax', $ajax_cons_data);

        $country_data = uwp_get_country_data();
        wp_localize_script(USERSWP_NAME, 'uwp_country_data', $country_data);

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
                $show_builder = true;
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


        add_menu_page(
            __( 'UsersWP Settings', 'userswp' ),
            __( 'UsersWP', 'userswp' ),
            'manage_options',
            'userswp',
            array( $this->admin_settings, 'uwp_settings_page' ),
            'dashicons-groups',
            70
        );

        if ($show_builder) {
            add_submenu_page(
                "userswp",
                __( 'Form Builder', 'userswp' ),
                __( 'Form Builder', 'userswp' ),
                'manage_options',
                'uwp_form_builder',
                array($this->admin_settings, 'uwp_settings_page')
            );

            add_submenu_page(
                "userswp",
                __( 'Notifications', 'userswp' ),
                __( 'Notifications', 'userswp' ),
                'manage_options',
                'uwp_notifications',
                array($this->admin_settings, 'uwp_settings_page')
            );

            $settings_page = array($this->admin_settings, 'uwp_settings_page');
            do_action('uwp_admin_sub_menus', $settings_page, $this->admin_settings);
        }
    }

    /**
     * Adds UsersWP css to admin area
     *
     * @since       1.0.0
     * @package     userswp
     *
     * @return      void
     */
    function uwp_admin_only_css() {
        ?>
        <style type="text/css">
            .uwp_page .uwp-bs-modal input[type="submit"].button,
            .uwp_page .uwp-bs-modal button.button {
                padding: 0 10px 1px;
            }
        </style>
        <?php
    }

}