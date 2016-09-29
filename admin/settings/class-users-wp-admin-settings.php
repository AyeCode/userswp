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
        ?>
        <div class="wrap">
            <h2><?php echo __( 'Page Settings', 'users-wp' ); ?></h2>
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
        </div>
    <?php }

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



}