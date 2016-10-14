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


    protected $loader;

    public function __construct() {
        $this->load_dependencies();
    }

    private function load_dependencies() {

        require_once dirname(dirname( __FILE__ )) . '/settings/class-users-wp-form-builder.php';

    }

    function users_wp_register_settings() {
        //pages
        register_setting( 'users-wp', 'uwp_user_profile_page' );
        register_setting( 'users-wp', 'uwp_register_page' );
        register_setting( 'users-wp', 'uwp_login_page' );
        register_setting( 'users-wp', 'uwp_account_page' );
        register_setting( 'users-wp', 'uwp_forgot_pass_page' );
        register_setting( 'users-wp', 'uwp_users_list_page' );

        do_action('users_wp_register_settings');

        //recapcha
        register_setting( 'users-wp', 'uwp_recaptcha_api_key' );
        register_setting( 'users-wp', 'uwp_recaptcha_api_secret' );
    }

    function users_wp_general_settings_page() {

        $active_tab = isset( $_GET['tab'] ) && array_key_exists( $_GET['tab'], $this->get_settings_tabs() ) ? $_GET['tab'] : 'general';

        ?>
        <div class="wrap">
            <h2><?php echo __( 'Page Settings', 'users-wp' ); ?></h2>

            <div id="users-wp">
                <div class="item-list-tabs content-box">
                    <ul class="item-list-tabs-ul">

                    <?php
                     foreach( $this->get_settings_tabs() as $tab_id => $tab_name ) {

                        $tab_url = add_query_arg( array(
                            'settings-updated' => false,
                            'tab' => $tab_id,
                            'subtab' => false
                        ) );

                        $active = $active_tab == $tab_id ? ' current selected' : '';
                        ?>
                        <li id="uwp-<?php echo $tab_id; ?>-li" class="<?php echo $active; ?>">
                            <a id="uwp-<?php echo $tab_id; ?>" href="<?php echo esc_url( $tab_url ); ?>"><?php echo esc_html( $tab_name ); ?></a>
                        </li>
                        <?php
                     }
                     ?>
                     </ul>

                    <div class="tab-content">
                        <?php
                        do_action('uwp_settings_'.$active_tab.'_tab_content');
                        ?>
                    </div>
                </div>

            </div>

        </div>
    <?php }

    public function get_settings_tabs() {

        $tabs = array();

        $tabs['general']  = __( 'General', 'users-wp' );
        $tabs['form_builder'] = __( 'Form Builder', 'users-wp' );
        $tabs['notifications']   = __( 'Notifications', 'users-wp' );

        return apply_filters( 'uwp_settings_tabs', $tabs );
    }

    public function get_general_content() {
        $subtab = 'general';

        if (isset($_GET['subtab'])) {
            $subtab = $_GET['subtab'];
        }


        ?>
        <div class="item-list-sub-tabs">
            <ul class="item-list-tabs-ul">
                <li id="uwp-general-general-li" class="<?php if ($subtab == 'general') { echo "current selected"; } ?>">
                    <a id="uwp-general-general" href="<?php echo add_query_arg(array('tab' => 'general', 'subtab' => 'general')); ?>">General Settings</a>
                </li>
                <li id="uwp-general-shortcodes-li" class="<?php if ($subtab == 'shortcodes') { echo "current selected"; } ?>">
                    <a id="uwp-general-shortcodes" href="<?php echo add_query_arg(array('tab' => 'general', 'subtab' => 'shortcodes')); ?>">Shortcodes</a>
                </li>
                <li id="uwp-general-info-li" class="<?php if ($subtab == 'info') { echo "current selected"; } ?>">
                    <a id="uwp-general-info" href="<?php echo add_query_arg(array('tab' => 'general', 'subtab' => 'info')); ?>">Info</a>
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

    //main tabs

    public function display_form() {
        $active_tab = isset( $_GET['tab'] ) && array_key_exists( $_GET['tab'], $this->get_settings_tabs() ) ? $_GET['tab'] : 'general';
        ?>
        <form method="post" action="options.php">

            <h3 class="users_wp_section_heading">General Options</h3>

            <table class="form-table">
                <?php settings_fields( 'uwp_settings' ); ?>
				<?php do_settings_fields( 'uwp_settings_' . $active_tab, 'uwp_settings_' . $active_tab ); ?>
			</table>
			<?php submit_button(); ?>

        </form>
        <?php
    }

    public function uwp_get_pages_as_option($selected) {
        $page_options = $this->uwp_get_pages();
        foreach ($page_options as $key => $page_title) {
            ?>
            <option value="<?php echo $key; ?>" <?php selected( $selected, $key ); ?>><?php echo $page_title; ?></option>
            <?php
        }
    }

    public function uwp_get_pages() {
        $pages_options = array( '' => 'Select a Page' ); // Blank option

        $pages = get_pages();
        if ( $pages ) {
            foreach ( $pages as $page ) {
                $pages_options[ $page->ID ] = $page->post_title;
            }
        }
        return $pages_options;
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

    //subtabs

    public function get_general_general_content() {
        $this->display_form();
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

    public function get_form_builder_content() {
        $form_builder = new Users_WP_Form_Builder();

        $subtab = 'register';

        if (isset($_GET['subtab'])) {
            $subtab = $_GET['subtab'];
        }

        ?>
        <div class="item-list-sub-tabs">
            <ul class="item-list-tabs-ul">
                <li id="uwp-form-builder-register-li" class="<?php if ($subtab == 'register') { echo "current selected"; } ?>">
                    <a id="uwp-form-builder-register" href="<?php echo add_query_arg(array('tab' => 'form_builder', 'subtab' => 'register')); ?>">Register</a>
                </li>
                <li id="uwp-form-builder-login-li" class="<?php if ($subtab == 'login') { echo "current selected"; } ?>">
                    <a id="uwp-form-builder-login" href="<?php echo add_query_arg(array('tab' => 'form_builder', 'subtab' => 'login')); ?>">Login</a>
                </li>
                <li id="uwp-form-builder-forgot-li" class="<?php if ($subtab == 'forgot') { echo "current selected"; } ?>">
                    <a id="uwp-form-builder-forgot" href="<?php echo add_query_arg(array('tab' => 'form_builder', 'subtab' => 'forgot')); ?>">Forgot</a>
                </li>
                <li id="uwp-form-builder-account-li" class="<?php if ($subtab == 'account') { echo "current selected"; } ?>">
                    <a id="uwp-form-builder-account" href="<?php echo add_query_arg(array('tab' => 'form_builder', 'subtab' => 'account')); ?>">Account</a>
                </li>
            </ul>
        </div>
        <?php
        if ($subtab == 'register') {
            ?>
            <h3 class="users_wp_section_heading">Manage Register Form Fields</h3>
            <?php
            $form_builder->uwp_form_builder();
        } elseif ($subtab == 'login') {
            ?>
            <h3 class="users_wp_section_heading">Manage Login Form Fields</h3>
            <?php
            $form_builder->uwp_form_builder();
        } elseif ($subtab == 'forgot') {
            ?>
            <h3 class="users_wp_section_heading">Manage Forgot Form Fields</h3>
            <?php
            $form_builder->uwp_form_builder();
        } elseif ($subtab == 'account') {
            ?>
            <h3 class="users_wp_section_heading">Manage Account Form Fields</h3>
            <?php
            $form_builder->uwp_form_builder();
        }
    }

    public function get_recaptcha_content() {
        $this->display_form();
    }

    public function get_notifications_content() {
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
               </tbody>
            </table>

        <?php
        $this->display_form();
    }

    public function uwp_register_settings() {

        if ( false == get_option( 'uwp_settings' ) ) {
            add_option( 'uwp_settings' );
        }

        foreach( $this->uwp_get_registered_settings() as $tab => $settings ) {

            add_settings_section(
                'uwp_settings_' . $tab,
                __return_null(),
                '__return_false',
                'uwp_settings_' . $tab
            );

            foreach ( $settings as $option ) {

                $name = isset( $option['name'] ) ? $option['name'] : '';

                add_settings_field(
                    'uwp_settings[' . $option['id'] . ']',
                    $name,
                    function_exists( 'uwp_' . $option['type'] . '_callback' ) ? 'uwp_' . $option['type'] . '_callback' : 'uwp_missing_callback',
                    'uwp_settings_' . $tab,
                    'uwp_settings_' . $tab,
                    array(
                        'section'     => $tab,
                        'id'          => isset( $option['id'] )          ? $option['id']          : null,
                        'desc'        => ! empty( $option['desc'] )      ? $option['desc']        : '',
                        'name'        => isset( $option['name'] )        ? $option['name']        : null,
                        'size'        => isset( $option['size'] )        ? $option['size']        : null,
                        'options'     => isset( $option['options'] )     ? $option['options']     : '',
                        'std'         => isset( $option['std'] )         ? $option['std']         : '',
                        'min'         => isset( $option['min'] )         ? $option['min']         : null,
                        'max'         => isset( $option['max'] )         ? $option['max']         : null,
                        'step'        => isset( $option['step'] )        ? $option['step']        : null,
                        'chosen'      => isset( $option['chosen'] )      ? $option['chosen']      : null,
                        'placeholder' => isset( $option['placeholder'] ) ? $option['placeholder'] : null,
                        'allow_blank' => isset( $option['allow_blank'] ) ? $option['allow_blank'] : true,
                        'readonly'    => isset( $option['readonly'] )    ? $option['readonly']    : false,
                        'faux'        => isset( $option['faux'] )        ? $option['faux']        : false,
                    )
                );
            }

        }

        // Creates our settings in the options table
        register_setting( 'uwp_settings', 'uwp_settings', array($this, 'uwp_settings_sanitize') );

    }

    public function uwp_get_registered_settings() {

        $register_success_subject = __('Your Log In Details', 'users-wp');
        $register_success_content = __("<p>Dear [#client_name#],</p><p>You can log in  with the following information:</p><p>[#login_details#]</p><p>You can login here: [#login_url#]</p><p>Thank you,<br /><br />[#site_name_url#].</p>",'users-wp');

        $forgot_password_subject = __('[#site_name#] - Your new password', 'users-wp');
        $forgot_password_content = __("<p>Dear [#client_name#],<p><p>You requested a new password for [#site_name_url#]</p><p>[#login_details#]</p><p>You can login here: [#login_url#]</p><p>Thank you,<br /><br />[#site_name_url#].</p>",'users-wp');

        /**
         * 'Whitelisted' uwp settings, filters are provided for each settings
         * section to allow extensions and other plugins to add their own settings
         */
        $uwp_settings = array(
            /** General Settings */
            'general' => apply_filters( 'uwp_settings_general',
                array(
                    'user_profile_page' => array(
                        'id' => 'user_profile_page',
                        'name' => __( 'User Profile Page', 'users-wp' ),
                        'desc' => __( 'This is the front end user\'s profile page. This page automatically override the default WordPress author page.', 'users-wp' ),
                        'type' => 'select',
                        'options' => $this->uwp_get_pages(),
                        'chosen' => true,
                        'placeholder' => __( 'Select a page', 'users-wp' )
                    ),
                    'register_page' => array(
                        'id' => 'register_page',
                        'name' => __( 'Register Page', 'users-wp' ),
                        'desc' => __( 'This is the front end register page. This is where users creates their account.', 'users-wp' ),
                        'type' => 'select',
                        'options' => $this->uwp_get_pages(),
                        'chosen' => true,
                        'placeholder' => __( 'Select a page', 'users-wp' )
                    ),
                    'login_page' => array(
                        'id' => 'login_page',
                        'name' => __( 'Login Page', 'users-wp' ),
                        'desc' => __( 'This is the front end login page. This is where users will login after creating their account.', 'users-wp' ),
                        'type' => 'select',
                        'options' => $this->uwp_get_pages(),
                        'chosen' => true,
                        'placeholder' => __( 'Select a page', 'users-wp' )
                    ),
                    'account_page' => array(
                        'id' => 'account_page',
                        'name' => __( 'Account Page', 'users-wp' ),
                        'desc' => __( 'This is the front end account page. This is where users can edit their account.', 'users-wp' ),
                        'type' => 'select',
                        'options' => $this->uwp_get_pages(),
                        'chosen' => true,
                        'placeholder' => __( 'Select a page', 'users-wp' )
                    ),
                    'forgot_pass_page' => array(
                        'id' => 'forgot_pass_page',
                        'name' => __( 'Forgot Password Page', 'users-wp' ),
                        'desc' => __( 'This is the front end Forgot Password page. This is the page where users are sent to reset their password when they lose it.', 'users-wp' ),
                        'type' => 'select',
                        'options' => $this->uwp_get_pages(),
                        'chosen' => true,
                        'placeholder' => __( 'Select a page', 'users-wp' )
                    ),
                    'users_list_page' => array(
                        'id' => 'users_list_page',
                        'name' => __( 'Users List Page', 'users-wp' ),
                        'desc' => __( 'This is the front end Users List page. This is the page where all registered users of the websites are listed.', 'users-wp' ),
                        'type' => 'select',
                        'options' => $this->uwp_get_pages(),
                        'chosen' => true,
                        'placeholder' => __( 'Select a page', 'users-wp' )
                    ),
                )
            ),
            'notifications' => apply_filters( 'uwp_settings_notifications',
                array(
                    'registration_success_email_subject' => array(
                        'id' => 'registration_success_email_subject',
                        'name' => __( 'Registration success email', 'users-wp' ),
                        'desc' => "",
                        'type' => 'text',
                        'size' => 'regular',
                        'std'  => $register_success_subject,
                        'placeholder' => __( 'Enter Registration success email Subject', 'users-wp' )
                    ),
                    'registration_success_email_content' => array(
                        'id' => 'registration_success_email_content',
                        'name' => "",
                        'desc' => "",
                        'type' => 'textarea',
                        'std'  => $register_success_content,
                        'placeholder' => __( 'Enter Registration success email Content', 'users-wp' )
                    ),
                    'forgot_password_email_subject' => array(
                        'id' => 'forgot_password_email_subject',
                        'name' => __( 'Forgot password email', 'users-wp' ),
                        'desc' => "",
                        'type' => 'text',
                        'size' => 'regular',
                        'std'  => $forgot_password_subject,
                        'placeholder' => __( 'Enter forgot password email Subject', 'users-wp' )
                    ),
                    'forgot_password_email_content' => array(
                        'id' => 'forgot_password_email_content',
                        'name' => "",
                        'desc' => "",
                        'type' => 'textarea',
                        'std'  => $forgot_password_content,
                        'placeholder' => __( 'Enter forgot password email Content', 'users-wp' )
                    ),
                )
            ),

            /** Extension Settings */
            'extensions' => apply_filters('uwp_settings_extensions',
                array()
            ),
        );

        return apply_filters( 'uwp_registered_settings', $uwp_settings );
    }

    public function uwp_get_settings() {

        $settings = get_option( 'uwp_settings' );

        if( empty( $settings ) ) {

            // Update old settings with new single option

            $general_settings = is_array( get_option( 'uwp_settings_general' ) )    ? get_option( 'uwp_settings_general' )    : array();
            $ext_settings     = is_array( get_option( 'uwp_settings_extensions' ) ) ? get_option( 'uwp_settings_extensions' ) : array();

            $settings = array_merge( $general_settings, $ext_settings );

            update_option( 'uwp_settings', $settings );

        }
        return apply_filters( 'uwp_get_settings', $settings );
    }

    public function uwp_settings_sanitize( $input = array() ) {

        global $uwp_options;

        if ( empty( $_POST['_wp_http_referer'] ) ) {
            return $input;
        }

        parse_str( $_POST['_wp_http_referer'], $referrer );

        $settings = $this->uwp_get_registered_settings();
        $tab      = isset( $referrer['tab'] ) ? $referrer['tab'] : 'general';

        $input = $input ? $input : array();
        $input = apply_filters( 'uwp_settings_' . $tab . '_sanitize', $input );

        // Loop through each setting being saved and pass it through a sanitization filter
        foreach ( $input as $key => $value ) {

            // Get the setting type (checkbox, select, etc)
            $type = isset( $settings[$tab][$key]['type'] ) ? $settings[$tab][$key]['type'] : false;

            if ( $type ) {
                // Field type specific filter
                $input[$key] = apply_filters( 'uwp_settings_sanitize_' . $type, $value, $key );
            }

            // General filter
            $input[$key] = apply_filters( 'uwp_settings_sanitize', $input[$key], $key );
        }

        // Loop through the whitelist and unset any that are empty for the tab being saved
        if ( ! empty( $settings[$tab] ) ) {
            foreach ( $settings[$tab] as $key => $value ) {

                // settings used to have numeric keys, now they have keys that match the option ID. This ensures both methods work
                if ( is_numeric( $key ) ) {
                    $key = $value['id'];
                }

                if ( empty( $input[$key] ) ) {
                    unset( $uwp_options[$key] );
                }

            }
        }

        // Merge our new settings with the existing
        $output = array_merge( $uwp_options, $input );

        add_settings_error( 'uwp-notices', '', __( 'Settings updated.', 'easy-digital-downloads' ), 'updated' );

        return $output;
    }

}