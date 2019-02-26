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

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     */
    public function __construct() {

    }


    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     * @param $hook_suffix
     */
    public function enqueue_styles($hook_suffix) {

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

        if ($hook_suffix == 'toplevel_page_userswp') {
            wp_enqueue_style( 'wp-color-picker' );
        }

    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     * @param $hook_suffix
     */
    public function enqueue_scripts($hook_suffix) {

        $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

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
        if ($hook_suffix == 'userswp_page_uwp_status') {
            wp_enqueue_script( 'jquery-ui-progressbar', array( 'jquery' ) );
            wp_enqueue_script( "uwp_status", USERSWP_PLUGIN_URL . '/admin/assets/js/system-status.js', array( 'jquery' ), USERSWP_VERSION, true );
        }

        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script( "userswp_admin", plugin_dir_url( __FILE__ ) . 'assets/js/users-wp-admin'.$suffix.'.js', array( 'jquery' ), null, false );
        wp_enqueue_script( "uwp_chosen", plugin_dir_url(dirname(__FILE__)) . 'public/assets/js/chosen.jquery.js', array( 'jquery' ), USERSWP_VERSION, false );
        wp_enqueue_script( "uwp_chosen_order", plugin_dir_url( __FILE__ ) . 'assets/js/chosen.order.jquery.min.js', array( 'jquery' ), USERSWP_VERSION, false );

        if ($hook_suffix == 'toplevel_page_userswp') {
            wp_enqueue_script("jquery-ui-tooltip");
            wp_enqueue_script('wp-color-picker');
        }

        $ajax_cons_data = array(
            'url' => admin_url('admin-ajax.php'),
            'custom_field_not_blank_var' => __('Field key must not be blank', 'userswp'),
            'custom_field_options_not_blank_var' => __('Option Values must not be blank', 'userswp'),
            'custom_field_not_special_char' => __('Please do not use special character and spaces in field key.', 'userswp'),
            'custom_field_unique_name' => __('Field key should be a unique name.', 'userswp'),
            'custom_field_delete' => __('Are you sure you wish to delete this field?', 'userswp'),
            'custom_field_id_required' => __('This field is required.', 'userswp'),
            'img_spacer' => admin_url( 'images/media-button-image.gif' ),
            'txt_choose_image' => __( 'Choose an image', 'userswp' ),
            'txt_use_image' => __( 'Use image', 'userswp' ),
        );
        wp_localize_script("userswp_admin", 'uwp_admin_ajax', $ajax_cons_data);

        $country_data = uwp_get_country_data();
        wp_localize_script(USERSWP_NAME, 'uwp_country_data', $country_data);

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