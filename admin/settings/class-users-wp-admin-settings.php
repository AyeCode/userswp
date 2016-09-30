<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://wpgeodirectory.com
 * @since      0.0.1
 *
 * @package    Users_WP
 * @subpackage Users_WP/admin/settings
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Users_WP
 * @subpackage Users_WP/admin/settings
 * @author     GeoDirectory Team <info@wpgeodirectory.com>
 */
class Users_WP_Admin_Settings {

    private $users_wp;

    protected $loader;

    public function __construct() {

    }

    public function users_wp_get_pages() {
        $pages_options = array( '' => 'Select a Page' ); // Blank option

        $pages = get_pages();
        if ( $pages ) {
            foreach ( $pages as $page ) {
                $pages_options[ $page->ID ] = $page->post_title;
            }
        }
        return $pages_options;
    }

    public function users_wp_get_pages_as_option($selected) {
        $page_options = $this->users_wp_get_pages();
        foreach ($page_options as $key => $page_title) {
            ?>
            <option value="<?php echo $key; ?>" <?php selected( $selected, $key ); ?>><?php echo $page_title; ?></option>
            <?php
        }
    }

    function users_wp_register_general_settings() {
        register_setting( 'users-wp', 'users_wp_user_profile_page' );
        register_setting( 'users-wp', 'users_wp_register_page' );
        register_setting( 'users-wp', 'users_wp_login_page' );
        register_setting( 'users-wp', 'users_wp_account_page' );
        register_setting( 'users-wp', 'users_wp_forgot_pass_page' );
        register_setting( 'users-wp', 'users_wp_users_list_page' );
    }

    function users_wp_general_settings_page() {
        $tab = 'general';

        if (isset($_GET['tab'])) {
            $tab = $_GET['tab'];
        }

        $current_page_url = $this->get_current_page_url();
        ?>
        <div class="wrap">
            <h2><?php echo __( 'Page Settings', 'users-wp' ); ?></h2>

            <div id="users-wp">
                <div class="item-list-tabs content-box">
                    <ul>

                        <li id="users-wp-general-li" class="<?php if ($tab == 'general') { echo "current selected"; } ?>">
                            <a id="users-wp-general" href="<?php echo add_query_arg('tab', 'general', $current_page_url); ?>">General</a>
                        </li>
                        <li id="users-wp-form-builder-li" class="<?php if ($tab == 'form_builder') { echo "current selected"; } ?>">
                            <a id="users-wp-form-builder" href="<?php echo add_query_arg('tab', 'form_builder', $current_page_url); ?>">Form Builder</a>
                        </li>
                        <li id="users-wp-recaptcha-li" class="<?php if ($tab == 'recaptcha') { echo "current selected"; } ?>">
                            <a id="users-wp-recaptcha" href="<?php echo add_query_arg('tab', 'recaptcha', $current_page_url); ?>">ReCaptcha</a>
                        </li>
                        <li id="users-wp-notifications-li" class="<?php if ($tab == 'notifications') { echo "current selected"; } ?>">
                            <a id="users-wp-notifications" href="<?php echo add_query_arg('tab', 'notifications', $current_page_url); ?>">Notifications</a>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <?php
                        if ($tab == 'general') {
                            $this->get_general_content();
                        } elseif ($tab == 'form_builder') {
                            $this->get_form_builder_content();
                        } elseif ($tab == 'recaptcha') {
                            $this->get_recaptcha_content();
                        } elseif ($tab == 'notifications') {
                            $this->get_notifications_content();
                        }
                        ?>
                    </div>
                </div>

            </div>

        </div>
    <?php }

    //main tabs
    public function get_general_content() {
        $subtab = 'general';

        if (isset($_GET['subtab'])) {
            $subtab = $_GET['subtab'];
        }

        $current_page_url = $this->get_current_page_url();

        ?>
        <div class="item-list-sub-tabs">
            <ul>
                <li id="users-wp-general-general-li" class="<?php if ($subtab == 'general') { echo "current selected"; } ?>">
                    <a id="users-wp-general-general" href="<?php echo add_query_arg(array('tab' => 'general', 'subtab' => 'general'), $current_page_url); ?>">General Settings</a>
                </li>
                <li id="users-wp-general-shortcodes-li" class="<?php if ($subtab == 'shortcodes') { echo "current selected"; } ?>">
                    <a id="users-wp-general-shortcodes" href="<?php echo add_query_arg(array('tab' => 'general', 'subtab' => 'shortcodes'), $current_page_url); ?>">Shortcodes</a>
                </li>
                <li id="users-wp-general-info-li" class="<?php if ($subtab == 'info') { echo "current selected"; } ?>">
                    <a id="users-wp-general-info" href="<?php echo add_query_arg(array('tab' => 'general', 'subtab' => 'info'), $current_page_url); ?>">Info</a>
                </li>
            </ul>
        </div>
        <?php
        if ($subtab == 'general') {
            $this->get_general_general_content();
        } elseif ($subtab == 'shortcodes') {
            $this->get_general_shortcodes_content();
        } elseif ($subtab == 'info') {
            $this->get_general_info_content();
        }
    }

    public function get_form_builder_content() {
        ?>

        <?php
    }

    public function get_recaptcha_content() {
        ?>

        <?php
    }

    public function get_notifications_content() {
        ?>

        <?php
    }

    //subtabs
    public function get_general_general_content() {
        ?>
        <form method="post" action="options.php">
            <?php settings_fields( 'users-wp' ); ?>
            <?php do_settings_sections( 'users-wp' );

            $user_profile_page = esc_attr( get_option('users_wp_user_profile_page', ''));
            $register_page = esc_attr( get_option('users_wp_register_page', ''));
            $login_page = esc_attr( get_option('users_wp_login_page', ''));
            $account_page = esc_attr( get_option('users_wp_account_page', ''));
            $forgot_pass_page = esc_attr( get_option('users_wp_forgot_pass_page', ''));
            $users_list_page = esc_attr( get_option('users_wp_users_list_page', ''));

            ?>

            <table class="widefat fixed" style="padding:10px;margin-top: 10px;">

                <tr valign="top">
                    <th scope="row"><?php echo __( 'User Profile Page', 'users-wp' ); ?></th>
                    <td>
                        <select id="users_wp_user_profile_page" name="users_wp_user_profile_page">
                            <?php $this->users_wp_get_pages_as_option($user_profile_page); ?>
                        </select>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php echo __( 'Register Page', 'users-wp' ); ?></th>
                    <td>
                        <select id="users_wp_register_page" name="users_wp_register_page">
                            <?php $this->users_wp_get_pages_as_option($register_page); ?>
                        </select>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php echo __( 'Login Page', 'users-wp' ); ?></th>
                    <td>
                        <select id="users_wp_login_page" name="users_wp_login_page">
                            <?php $this->users_wp_get_pages_as_option($login_page); ?>
                        </select>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php echo __( 'Account Page', 'users-wp' ); ?></th>
                    <td>
                        <select id="users_wp_account_page" name="users_wp_account_page">
                            <?php $this->users_wp_get_pages_as_option($account_page); ?>
                        </select>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php echo __( 'Forgot Password Page', 'users-wp' ); ?></th>
                    <td>
                        <select id="users_wp_forgot_pass_page" name="users_wp_forgot_pass_page">
                            <?php $this->users_wp_get_pages_as_option($forgot_pass_page); ?>
                        </select>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php echo __( 'Users List Page', 'users-wp' ); ?></th>
                    <td>
                        <select id="users_wp_users_list_page" name="users_wp_users_list_page">
                            <?php $this->users_wp_get_pages_as_option($users_list_page); ?>
                        </select>
                    </td>
                </tr>

                <tr valign="top">
                    <th></th>
                    <td><?php submit_button(null, 'primary','submit',false); ?></td>
                </tr>
            </table>

        </form>
        <?php
    }

    public function get_general_shortcodes_content() {
        ?>
        <table class="widefat fixed" style="padding:10px;margin-top: 10px;">

            <tr valign="top">
                <th scope="row"><?php echo __( 'User Profile Shortcode', 'users-wp' ); ?></th>
                <td>
                    [uwp_profile]
                </td>
            </tr>

            <tr valign="top">
                <th scope="row"><?php echo __( 'Register Form Shortcode', 'users-wp' ); ?></th>
                <td>
                    [uwp_register]
                </td>
            </tr>

            <tr valign="top">
                <th scope="row"><?php echo __( 'Login Form Shortcode', 'users-wp' ); ?></th>
                <td>
                    [uwp_login]
                </td>
            </tr>

            <tr valign="top">
                <th scope="row"><?php echo __( 'Account Form Shortcode', 'users-wp' ); ?></th>
                <td>
                    [uwp_account]
                </td>
            </tr>

            <tr valign="top">
                <th scope="row"><?php echo __( 'Forgot Password Form Shortcode', 'users-wp' ); ?></th>
                <td>
                    [uwp_forgot]
                </td>
            </tr>

            <tr valign="top">
                <th scope="row"><?php echo __( 'Users List Shortcode', 'users-wp' ); ?></th>
                <td>
                    [uwp_users]
                </td>
            </tr>

        </table>
        <?php
    }

    public function get_general_info_content() {
        ?>

        <?php
    }

    public function get_settings_tabs() {

        $tabs = array();

        $tabs['general']  = __( 'General', 'users-wp' );
        $tabs['form_builder'] = __( 'Form Builder', 'users-wp' );
        $tabs['recaptcha']   = __( 'reCaptcha', 'users-wp' );
        $tabs['notifications']   = __( 'Notifications', 'users-wp' );

        return apply_filters( 'users_wp_settings_tabs', $tabs );
    }

    function get_registered_settings_sections() {

        static $sections = false;
        if ( false !== $sections ) {
            return $sections;
        }

        $sections = array(
            'general'    => apply_filters( 'users_wp_settings_sections_general', array(
                'main'               => __( 'General Settings', 'users-wp' ),
                'shortcodes'           => __( 'Shortcodes List', 'users-wp' ),
                'info'                => __( 'Info', 'users-wp' ),
            ) ),
            'form_builder'    => apply_filters( 'users_wp_settings_sections_form_builder', array() ),
            'recaptcha'    => apply_filters( 'users_wp_settings_sections_recaptcha', array() ),
            'notifications'    => apply_filters( 'users_wp_settings_sections_notifications', array() ),

        );
        $sections = apply_filters( 'users_wp_settings_sections', $sections );
        return $sections;
    }

    function users_wp_get_settings() {
        $settings = get_option( 'users_wp_settings' );
        if( empty( $settings ) ) {
            // Update old settings with new single option
            $general_settings = is_array( get_option( 'users_wp_settings_general' ) )    ? get_option( 'users_wp_settings_general' )    : array();
            $form_builder_settings = is_array( get_option( 'users_wp_settings_form_builder' ) )    ? get_option( 'users_wp_settings_form_builder' )    : array();
            $recaptcha_settings = is_array( get_option( 'users_wp_settings_recaptcha' ) )    ? get_option( 'users_wp_settings_recaptcha' )    : array();
            $notifications_settings = is_array( get_option( 'users_wp_settings_notifications' ) )    ? get_option( 'users_wp_settings_notifications' )    : array();

            $settings = array_merge( $general_settings, $form_builder_settings, $recaptcha_settings, $notifications_settings );
            update_option( 'users_wp_settings', $settings );
        }
        return apply_filters( 'users_wp_get_settings', $settings );
    }

    /**
     * Get the current page url.
     *
     * @since     0.0.1
     * @return    string    current page url.
     */
    public function get_current_page_url() {
        $pageURL = 'http';
        if ( isset( $_SERVER["HTTPS"] ) && $_SERVER["HTTPS"] == "on" ) {
            $pageURL .= "s";
        }
        $pageURL .= "://";
        $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];

        /**
         * Filter the current page URL returned by function get_current_page_url().
         *
         * @since 0.0.1
         *
         * @param string $pageURL The URL of the current page.
         */
        return apply_filters( 'users_wp_get_current_page_url', $pageURL );
    }



}