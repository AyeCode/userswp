<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://wpgeodirectory.com
 * @since      1.0.0
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
        $this->load_dependencies();
    }

    private function load_dependencies() {

        require_once dirname(dirname( __FILE__ )) . '/settings/class-users-wp-form-builder.php';

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
        //pages
        register_setting( 'users-wp', 'uwp_user_profile_page' );
        register_setting( 'users-wp', 'uwp_register_page' );
        register_setting( 'users-wp', 'uwp_login_page' );
        register_setting( 'users-wp', 'uwp_account_page' );
        register_setting( 'users-wp', 'uwp_forgot_pass_page' );
        register_setting( 'users-wp', 'uwp_users_list_page' );

        //recapcha
        register_setting( 'users-wp', 'uwp_recaptcha_api_key' );
        register_setting( 'users-wp', 'uwp_recaptcha_api_secret' );
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
                    <ul class="item-list-tabs-ul">

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
        $form_builder = new Users_WP_Form_Builder();
        ?>
        <h3 class="users_wp_section_heading">Manage Form Fields</h3>
        <?php
        $form_builder->uwp_form_builder();
    }

    public function get_recaptcha_content() {
        ?>
        <form method="post" action="options.php">
            <?php settings_fields( 'users-wp' ); ?>
            <?php do_settings_sections( 'users-wp' );

            $uwp_recaptcha_api_key = esc_attr( get_option('uwp_recaptcha_api_key', ''));
            $uwp_recaptcha_api_secret = esc_attr( get_option('uwp_recaptcha_api_secret', ''));

            ?>

        <table class="uwp-form-table">

            <tr valign="top">
                <th scope="row"><?php echo __( 'Google ReCaptcha API Key:', 'users-wp' ); ?></th>
                <td>
                    <input type="text" name="uwp_recaptcha_api_key" value="<?php echo esc_attr( $uwp_recaptcha_api_key ); ?>" id="uwp_recaptcha_api_key" />
                    <span class="description">*Required - Enter Re-Captcha site key that you get after site registration at <a target="_blank" href="https://www.google.com/recaptcha/admin#list">here</a>.</span>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row"><?php echo __( 'Google ReCaptcha API Secret:', 'users-wp' ); ?></th>
                <td>
                    <input type="text" name="uwp_recaptcha_api_secret" value="<?php echo esc_attr( $uwp_recaptcha_api_secret ); ?>" id="uwp_recaptcha_api_secret" />
                    <span class="description">*Required - Enter Re-Captcha secret key that you get after site registration at <a target="_blank" href="https://www.google.com/recaptcha/admin#list">here</a>.</span>
                </td>
            </tr>

            <tr valign="top">
                <th></th>
                <td><?php submit_button(null, 'primary','submit',false); ?></td>
            </tr>
        </table>
        <?php
    }

    public function get_notifications_content() {
        ?>
        <form method="post" action="options.php">

        <?php settings_fields( 'users-wp' ); ?>
        <?php do_settings_sections( 'users-wp' );

        $uwp_register_success_subject = esc_attr( get_option('uwp_register_success_subject', ''));
        $uwp_register_success_content = esc_attr( get_option('uwp_register_success_content', ''));

        $uwp_forgot_password_subject = esc_attr( get_option('uwp_forgot_password_subject', ''));
        $uwp_forgot_password_content = esc_attr( get_option('uwp_forgot_password_content', ''));

        ?>

        <h3 class="users_wp_section_heading">Email Notifications</h3>

            <table class="uwp-form-table">
               <tbody>

               <tr valign="top">
                    <th scope="row" class="titledesc">List of usable shortcodes</th>
                    <td class="forminp">
                        <span class="description">[#client_name#],[#login_url#],[#username#],[#user_email#],[#site_name_url#],[#site_name#],[#from_email#](the admin email) </span>
                    </td>
               </tr>

               <tr valign="top">
                    <th scope="row" class="titledesc">Registration success email</th>
                    <td class="forminp">
                        <input name="uwp_registration_success_email_subject" id="uwp_registration_success_email_subject" type="text" style=" min-width:300px;" value="<?php echo esc_attr( $uwp_register_success_subject ); ?>" />
                        <span class="description"></span>
                    </td>
               </tr>

               <tr valign="top">
                    <th scope="row" class="titledesc"></th>
                    <td class="forminp">
                        <textarea name="uwp_registration_success_email_content" id="uwp_registration_success_email_content" style="width:500px; height: 150px;"><?php echo esc_attr( $uwp_register_success_content ); ?></textarea>
                        <span class="description"></span>

                    </td>
               </tr>

                <tr valign="top">
                    <th scope="row" class="titledesc">User forgot password email</th>
                    <td class="forminp">
                        <input name="uwp_forgot_password_subject" id="uwp_forgot_password_subject" type="text" style=" min-width:300px;" value="<?php echo esc_attr( $uwp_forgot_password_subject ); ?>" />
                        <span class="description"></span>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row" class="titledesc"></th>
                    <td class="forminp">
                        <textarea name="uwp_forgot_password_content" id="uwp_forgot_password_content" style="width:500px; height: 150px;"><?php echo esc_attr( $uwp_forgot_password_content ); ?></textarea>
                        <span class="description"></span>
                    </td>
                </tr>

                <tr valign="top">
                    <th></th>
                    <td><?php submit_button(null, 'primary','submit',false); ?></td>
                </tr>
               </tbody>
            </table>
        </form>
        <?php
    }

    //subtabs
    public function get_general_general_content() {
        ?>
        <form method="post" action="options.php">
            <?php settings_fields( 'users-wp' ); ?>
            <?php do_settings_sections( 'users-wp' );


            $user_profile_page = esc_attr( get_option('uwp_user_profile_page', ''));
            $register_page = esc_attr( get_option('uwp_register_page', ''));
            $login_page = esc_attr( get_option('uwp_login_page', ''));
            $account_page = esc_attr( get_option('uwp_account_page', ''));
            $forgot_pass_page = esc_attr( get_option('uwp_forgot_pass_page', ''));
            $users_list_page = esc_attr( get_option('uwp_users_list_page', ''));

            ?>

            <h3 class="users_wp_section_heading">General Options</h3>

            <table class="uwp-form-table">

                <tr valign="top">
                    <th scope="row"><?php echo __( 'User Profile Page', 'users-wp' ); ?></th>
                    <td>
                        <select id="uwp_user_profile_page" name="uwp_user_profile_page">
                            <?php $this->users_wp_get_pages_as_option($user_profile_page); ?>
                        </select>
                        <span class="description">This is the front end user's profile page. This page automatically override the default WordPress author page.</span>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php echo __( 'Register Page', 'users-wp' ); ?></th>
                    <td>
                        <select id="uwp_register_page" name="uwp_register_page">
                            <?php $this->users_wp_get_pages_as_option($register_page); ?>
                        </select>
                        <span class="description">This is the front end register page. This is where users creates their account.</span>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php echo __( 'Login Page', 'users-wp' ); ?></th>
                    <td>
                        <select id="uwp_login_page" name="uwp_login_page">
                            <?php $this->users_wp_get_pages_as_option($login_page); ?>
                        </select>
                        <span class="description">This is the front end login page. This is where users will login after creating their account.</span>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php echo __( 'Account Page', 'users-wp' ); ?></th>
                    <td>
                        <select id="uwp_account_page" name="uwp_account_page">
                            <?php $this->users_wp_get_pages_as_option($account_page); ?>
                        </select>
                        <span class="description">This is the front end account page. This is where users can edit their account.</span>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php echo __( 'Forgot Password Page', 'users-wp' ); ?></th>
                    <td>
                        <select id="uwp_forgot_pass_page" name="uwp_forgot_pass_page">
                            <?php $this->users_wp_get_pages_as_option($forgot_pass_page); ?>
                        </select>
                        <span class="description">This is the front end Forgot Password page. This is the page where users are sent to reset their password when they lose it.</span>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php echo __( 'Users List Page', 'users-wp' ); ?></th>
                    <td>
                        <select id="uwp_users_list_page" name="uwp_users_list_page">
                            <?php $this->users_wp_get_pages_as_option($users_list_page); ?>
                        </select>
                        <span class="description">This is the front end Users List page. This is the page where all registered users of the websites are listed.</span>
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
        <table class="uwp-form-table">

            <tr valign="top">
                <th scope="row"><?php echo __( 'User Profile Shortcode', 'users-wp' ); ?></th>
                <td>
                    <span class="short_code">[uwp_profile]</span>
                    <span class="description">This is the shortcode for the front end user's profile.</span>
                    <span class="description">Parameters: header=yes or no (default yes) body=yes or no (default yes) posts=yes or no (default yes) comments=yes or no (default yes)</span>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row"><?php echo __( 'Register Form Shortcode', 'users-wp' ); ?></th>
                <td>
                    <span class="short_code">[uwp_register]</span>
                    <span class="description">This is the shortcode for the front end register form.</span>
                    <span class="description">Parameters: set_password=yes or no (default yes) captcha=yes or no (default yes)</span>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row"><?php echo __( 'Login Form Shortcode', 'users-wp' ); ?></th>
                <td>
                    <span class="short_code">[uwp_login]</span>
                    <span class="description">This is the shortcode for the front end login form.</span>
                    <span class="description">Parameters: captcha=yes or no (default yes) redirect_to=home or profile or page id (default home)</span>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row"><?php echo __( 'Account Form Shortcode', 'users-wp' ); ?></th>
                <td>
                    <span class="short_code">[uwp_account]</span>
                    <span class="description">This is the shortcode for the front end account form.</span>
                    <span class="description">Parameters: none</span>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row"><?php echo __( 'Forgot Password Form Shortcode', 'users-wp' ); ?></th>
                <td>
                    <span class="short_code">[uwp_forgot]</span>
                    <span class="description">This is the shortcode for the front end reset password form.</span>
                    <span class="description">Parameters: none</span>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row"><?php echo __( 'Users List Shortcode', 'users-wp' ); ?></th>
                <td>
                    <span class="short_code">[uwp_users]</span>
                    <span class="description">This is the shortcode for the front end users list.</span>
                    <span class="description">Parameters: none</span>
                </td>
            </tr>

        </table>
        <?php
    }

    public function get_general_info_content() {
        ?>
        <h3>Welcome to UsersWP</h3>
        <h4>version 1.0.0</h4>

        <h3>Flexible, Lightweight and Fast</h3>
        <p>UsersWP allows you to add a customizable register and login form to your website.
        It also adds an extended profile page that override the default author page.
        UsersWP has been built to be lightweight and fast</p>

        <h3>Less options, more hooks</h3>
        <p>We cut down the options to the bare minimum and you will not find any fancy
        styling options in this plugin as we believe they belong in your theme.
        This doesn't mean that you cannot customize the plugin behaviour.
        To do this we provided a long list of Filters and Actions for any developer
        to extend UsersWP to fit their needs. <a href="">Click here for the list of available hooks</a></p>

        <h3>Override Templates</h3>
        <p>If you need to change the look and feel of any UsersWP templates,
        simply create a folder named userswp inside your active child theme
        and copy the template you wish to modify in it. You can now modify the template.
        The plugin will use your modified version and you don't hve to worry about plugin or theme updates.
        <a href="">Click here for examples</a></p>

        <h3>Add-ons</h3>
        <p>We have a long list of free and premium add-ons that will help you extend users management on your wesbsite.
        <a href="">Click here for our official free and premium add-ons</a></p>
        <?php
    }

    public function get_settings_tabs() {

        $tabs = array();

        $tabs['general']  = __( 'General', 'users-wp' );
        $tabs['form_builder'] = __( 'Form Builder', 'users-wp' );
        $tabs['recaptcha']   = __( 'reCaptcha', 'users-wp' );
        $tabs['notifications']   = __( 'Notifications', 'users-wp' );

        return apply_filters( 'uwp_settings_tabs', $tabs );
    }


    /**
     * Get the current page url.
     *
     * @since     1.0.0
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
         * @since 1.0.0
         *
         * @param string $pageURL The URL of the current page.
         */
        return apply_filters( 'uwp_get_current_page_url', $pageURL );
    }



}