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

    public function uwp_settings_page() {

        $page = isset( $_GET['page'] ) ? $_GET['page'] : 'uwp';

        $settings_array = $this->get_settings_tabs();
        $active_tab = isset( $_GET['tab'] ) && array_key_exists( $_GET['tab'], $settings_array[$page] ) ? $_GET['tab'] : 'main';
        ?>
        <div class="wrap">

            <h1><?php echo get_admin_page_title(); ?></h1>

            <div id="users-wp">
                <div class="item-list-tabs">

                    <?php if (count($settings_array[$page]) > 1) { ?>

                        <ul class="item-list-tabs-ul">
                        <?php
                        foreach( $settings_array[$page] as $tab_id => $tab_name ) {

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

                    <?php } ?>

                    <div class="tab-content">
                        <?php
                        // {current page}_settings_{active tab}_tab_content
                        // ex: uwp_settings_main_tab_content
                        do_action($page.'_settings_'.$active_tab.'_tab_content', $this->display_form());
                        ?>
                    </div>
                </div>

            </div>

        </div>
    <?php }

    public function get_settings_tabs() {

        $tabs = array();

        // wp-admin/admin.php?page=uwp
        $tabs['uwp']  = array(
            'main' => __( 'General', 'uwp' ),
            'register' => __( 'Register', 'uwp' ),
            'login' => __( 'Login', 'uwp' ),
            'profile' => __( 'Profile', 'uwp' ),
            'uninstall' => __( 'Uninstall', 'uwp' ),
        );

        // wp-admin/admin.php?page=uwp_form_builder
        $tabs['uwp_form_builder'] = array(
            'main' => __( 'Form Builder', 'uwp' ),
        );

        // wp-admin/admin.php?page=uwp_notifications
        $tabs['uwp_notifications'] = array(
            'main' => __( 'Notifications', 'uwp' ),
        );

        return apply_filters( 'uwp_settings_tabs', $tabs );
    }

    public function get_general_content() {

        $subtab = 'general';

        if (isset($_GET['subtab'])) {
            $subtab = $_GET['subtab'];
        }
        ?>
        <div class="item-list-sub-tabs">
            <ul class="item-list-sub-tabs-ul">
                <li class="<?php if ($subtab == 'general') { echo "current selected"; } ?>">
                    <a href="<?php echo add_query_arg(array('tab' => 'main', 'subtab' => 'general')); ?>"><?php echo __( 'General Settings', 'uwp' ); ?></a>
                </li>
                <li class="<?php if ($subtab == 'shortcodes') { echo "current selected"; } ?>">
                    <a href="<?php echo add_query_arg(array('tab' => 'main', 'subtab' => 'shortcodes')); ?>"><?php echo __( 'Shortcodes', 'uwp' ); ?></a>
                </li>
                <li class="<?php if ($subtab == 'info') { echo "current selected"; } ?>">
                    <a href="<?php echo add_query_arg(array('tab' => 'main', 'subtab' => 'info')); ?>"><?php echo __( 'Info', 'uwp' ); ?></a>
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

        $page = isset( $_GET['page'] ) ? $_GET['page'] : 'uwp';
        $settings_array = $this->get_settings_tabs();

        $active_tab = isset( $_GET['tab'] ) && array_key_exists( $_GET['tab'], $settings_array[$page] ) ? $_GET['tab'] : 'main';
        ob_start();
        ?>
        <form method="post" action="options.php">
            <?php
            $title = apply_filters('uwp_display_form_title', false, $page, $active_tab);
            if ($title) { ?>
                <h2 class="title"><?php echo $title; ?></h2>
            <?php } ?>

            <table class="uwp-form-table">
                <?php
                settings_fields( 'uwp_settings' );
                do_settings_fields( 'uwp_settings_' . $page .'_'.$active_tab, 'uwp_settings_' . $page .'_'.$active_tab );
                ?>
			</table>
			<?php submit_button(); ?>
        </form>

        <?php
        $output = ob_get_contents();
        ob_end_clean();

        return $output;
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
        $pages_options = array( '' => __( 'Select a Page', 'uwp' ) ); // Blank option

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
                <th scope="row"><?php echo __( 'User Profile Shortcode', 'uwp' ); ?></th>
                <td>
                    <span class="short_code">[uwp_profile]</span>
                    <span class="description"><?php echo __( 'This is the shortcode for the front end user\'s profile.', 'uwp' ); ?></span>
                    <span class="description"><?php echo __( 'Parameters: header=yes or no (default yes) body=yes or no (default yes) posts=yes or no (default yes) comments=yes or no (default yes)', 'uwp' ); ?></span>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row"><?php echo __( 'Register Form Shortcode', 'uwp' ); ?></th>
                <td>
                    <span class="short_code">[uwp_register]</span>
                    <span class="description"><?php echo __( 'This is the shortcode for the front end register form.', 'uwp' ); ?></span>
                    <span class="description"><?php echo __( 'Parameters: set_password=yes or no (default yes) captcha=yes or no (default yes)', 'uwp' ); ?></span>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row"><?php echo __( 'Login Form Shortcode', 'uwp' ); ?></th>
                <td>
                    <span class="short_code">[uwp_login]</span>
                    <span class="description"><?php echo __( 'This is the shortcode for the front end login form.', 'uwp' ); ?></span>
                    <span class="description"><?php echo __( 'Parameters: captcha=yes or no (default yes) redirect_to=home or profile or page id (default home)', 'uwp' ); ?></span>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row"><?php echo __( 'Account Form Shortcode', 'uwp' ); ?></th>
                <td>
                    <span class="short_code">[uwp_account]</span>
                    <span class="description"><?php echo __( 'This is the shortcode for the front end account form.', 'uwp' ); ?></span>
                    <span class="description"><?php echo __( 'Parameters: none', 'uwp' ); ?></span>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row"><?php echo __( 'Forgot Password Form Shortcode', 'uwp' ); ?></th>
                <td>
                    <span class="short_code">[uwp_forgot]</span>
                    <span class="description"><?php echo __( 'This is the shortcode for the front end forgot password form.', 'uwp' ); ?></span>
                    <span class="description"><?php echo __( 'Parameters: none', 'uwp' ); ?></span>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row"><?php echo __( 'Reset Password Form Shortcode', 'uwp' ); ?></th>
                <td>
                    <span class="short_code">[uwp_forgot]</span>
                    <span class="description"><?php echo __( 'This is the shortcode for the front end reset password form.', 'uwp' ); ?></span>
                    <span class="description"><?php echo __( 'Parameters: none', 'uwp' ); ?></span>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row"><?php echo __( 'Users List Shortcode', 'uwp' ); ?></th>
                <td>
                    <span class="short_code">[uwp_users]</span>
                    <span class="description"><?php echo __( 'This is the shortcode for the front end users list.', 'uwp' ); ?></span>
                    <span class="description"><?php echo __( 'Parameters: none', 'uwp' ); ?></span>
                </td>
            </tr>

        </table>
        <?php
    }

    //subtabs
    public function get_general_general_content() {
        echo $this->display_form();
    }

    public function display_form_title($title, $page, $active_tab) {
        if ($page == 'uwp' && $active_tab == 'main') {
            $title = __('General Options', 'uwp');
        }
        return $title;
    }

    public function get_general_info_content() {
        ?>
        <h3><?php echo __( 'Welcome to UsersWP', 'uwp' ); ?></h3>
        <h4><?php echo __( 'Version 1.0.0', 'uwp' ); ?></h4>

        <h3><?php echo __( 'Flexible, Lightweight and Fast', 'uwp' ); ?></h3>
        <p><?php echo __( 'UsersWP allows you to add a customizable register and login form to your website.
        It also adds an extended profile page that override the default author page.
        UsersWP has been built to be lightweight and fast', 'uwp' ); ?></p>

        <h3><?php echo __( 'Less options, more hooks', 'uwp' ); ?></h3>
        <p><?php echo __( 'We cut down the options to the bare minimum and you will not find any fancy
        styling options in this plugin as we believe they belong in your theme.
        This doesn\'t mean that you cannot customize the plugin behaviour.
        To do this we provided a long list of Filters and Actions for any developer
        to extend UsersWP to fit their needs. <a href="">Click here for the list of available hooks</a>', 'uwp' ); ?></p>

        <h3><?php echo __( 'Override Templates', 'uwp' ); ?></h3>
        <p><?php echo __( 'If you need to change the look and feel of any UsersWP templates,
        simply create a folder named userswp inside your active child theme
        and copy the template you wish to modify in it. You can now modify the template.
        The plugin will use your modified version and you don\'t hve to worry about plugin or theme updates.
        <a href="">Click here for examples</a>', 'uwp' ); ?></p>

        <h3><?php echo __( 'Add-ons', 'uwp' ); ?></h3>
        <p><?php echo __( 'We have a long list of free and premium add-ons that will help you extend users management on your wesbsite.
        <a href="">Click here for our official free and premium add-ons</a>', 'uwp' ); ?></p>
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
                    <a id="uwp-form-builder-register" href="<?php echo add_query_arg(array('tab' => 'form_builder', 'subtab' => 'register')); ?>"><?php echo __( 'Register', 'uwp' ); ?></a>
                </li>
                <li id="uwp-form-builder-account-li" class="<?php if ($subtab == 'account') { echo "current selected"; } ?>">
                    <a id="uwp-form-builder-account" href="<?php echo add_query_arg(array('tab' => 'form_builder', 'subtab' => 'account')); ?>"><?php echo __( 'Account', 'uwp' ); ?></a>
                </li>
            </ul>
        </div>
        <?php
        if ($subtab == 'register') {
            ?>
            <h3 class=""><?php echo __( 'Manage Register Form Fields', 'uwp' ); ?></h3>
            <?php
            $form_builder->uwp_form_builder();
        } elseif ($subtab == 'account') {
            ?>
            <h3 class=""><?php echo __( 'Manage Account Form Fields', 'uwp' ); ?></h3>
            <?php
            $form_builder->uwp_form_builder();
        }
    }

    public function generic_display_form() {
        echo $this->display_form();
    }

    public function get_recaptcha_content() {
        echo $this->display_form();
    }

    public function get_geodirectory_content() {
        echo $this->display_form();
    }

    public function get_notifications_content() {
        ?>
        <h3 class=""><?php echo __( 'Email Notifications', 'uwp' ); ?></h3>

            <table class="uwp-form-table">
               <tbody>
               <tr valign="top">
                    <th scope="row" class="titledesc"><?php echo __( 'List of usable shortcodes', 'uwp' ); ?></th>
                    <td class="forminp">
                        <span class="description">[#site_name_url#],[#site_name#],[#to_name#],[#from_name#],[#login_url#],[#user_name#],[#from_email#],[#user_login#],[#username#],[#current_date#],[#login_details#]</span>
                    </td>
               </tr>
               </tbody>
            </table>

        <?php
        echo $this->display_form();
    }

    public function uwp_register_settings() {

        if ( false == get_option( 'uwp_settings' ) ) {
            add_option( 'uwp_settings' );
        }

        foreach( $this->uwp_get_registered_settings() as $tab => $settings ) {

            foreach ( $settings as $key => $opt ) {
                add_settings_section(
                    'uwp_settings_' . $tab .'_'.$key,
                    __return_null(),
                    '__return_false',
                    'uwp_settings_' . $tab .'_'.$key
                );

                foreach ($opt as $option) {
                    $name = isset( $option['name'] ) ? $option['name'] : '';

                    add_settings_field(
                        'uwp_settings[' . $option['id'] . ']',
                        $name,
                        function_exists( 'uwp_' . $option['type'] . '_callback' ) ? 'uwp_' . $option['type'] . '_callback' : 'uwp_missing_callback',
                        'uwp_settings_' . $tab .'_'.$key,
                        'uwp_settings_' . $tab .'_'.$key,
                        array(
                            'section'     => $tab,
                            'id'          => isset( $option['id'] )          ? $option['id']          : null,
                            'class'       => isset( $option['class'] )       ? $option['class']       : null,
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
                            'multiple'        => isset( $option['multiple'] )        ? $option['multiple']        : false,
                        )
                    );
                }
            }

        }

        // Creates our settings in the options table
        register_setting( 'uwp_settings', 'uwp_settings', array($this, 'uwp_settings_sanitize') );

    }

    public function uwp_get_registered_settings() {

        /**
         * 'Whitelisted' uwp settings, filters are provided for each settings
         * section to allow extensions and other plugins to add their own settings
         */
        $uwp_settings = array(
            /** General Settings */
            'uwp' => array(
                'main' => apply_filters( 'uwp_settings_general_main',
                    array(
                        'profile_page' => array(
                            'id' => 'profile_page',
                            'name' => __( 'User Profile Page', 'uwp' ),
                            'desc' => __( 'This is the front end user\'s profile page. This page automatically override the default WordPress author page.', 'uwp' ),
                            'type' => 'select',
                            'options' => $this->uwp_get_pages(),
                            'chosen' => true,
                            'placeholder' => __( 'Select a page', 'uwp' ),
                            'class' => 'uwp_label_block',
                        ),
                        'register_page' => array(
                            'id' => 'register_page',
                            'name' => __( 'Register Page', 'uwp' ),
                            'desc' => __( 'This is the front end register page. This is where users creates their account.', 'uwp' ),
                            'type' => 'select',
                            'options' => $this->uwp_get_pages(),
                            'chosen' => true,
                            'placeholder' => __( 'Select a page', 'uwp' ),
                            'class' => 'uwp_label_block',
                        ),
                        'login_page' => array(
                            'id' => 'login_page',
                            'name' => __( 'Login Page', 'uwp' ),
                            'desc' => __( 'This is the front end login page. This is where users will login after creating their account.', 'uwp' ),
                            'type' => 'select',
                            'options' => $this->uwp_get_pages(),
                            'chosen' => true,
                            'placeholder' => __( 'Select a page', 'uwp' ),
                            'class' => 'uwp_label_block',
                        ),
                        'account_page' => array(
                            'id' => 'account_page',
                            'name' => __( 'Account Page', 'uwp' ),
                            'desc' => __( 'This is the front end account page. This is where users can edit their account.', 'uwp' ),
                            'type' => 'select',
                            'options' => $this->uwp_get_pages(),
                            'chosen' => true,
                            'placeholder' => __( 'Select a page', 'uwp' ),
                            'class' => 'uwp_label_block',
                        ),
                        'forgot_page' => array(
                            'id' => 'forgot_page',
                            'name' => __( 'Forgot Password Page', 'uwp' ),
                            'desc' => __( 'This is the front end Forgot Password page. This is the page where users are sent to reset their password when they lose it.', 'uwp' ),
                            'type' => 'select',
                            'options' => $this->uwp_get_pages(),
                            'chosen' => true,
                            'placeholder' => __( 'Select a page', 'uwp' ),
                            'class' => 'uwp_label_block',
                        ),
                        'reset_page' => array(
                            'id' => 'reset_page',
                            'name' => __( 'Reset Password Page', 'uwp' ),
                            'desc' => __( 'This is the front end Reset Password page. This is the page where users can reset their password when they lose it.', 'uwp' ),
                            'type' => 'select',
                            'options' => $this->uwp_get_pages(),
                            'chosen' => true,
                            'placeholder' => __( 'Select a page', 'uwp' ),
                            'class' => 'uwp_label_block',
                        ),
                        'users_page' => array(
                            'id' => 'users_page',
                            'name' => __( 'Users List Page', 'uwp' ),
                            'desc' => __( 'This is the front end Users List page. This is the page where all registered users of the websites are listed.', 'uwp' ),
                            'type' => 'select',
                            'options' => $this->uwp_get_pages(),
                            'chosen' => true,
                            'placeholder' => __( 'Select a page', 'uwp' ),
                            'class' => 'uwp_label_block',
                        ),
                        'profile_no_of_items' => array(
                            'id' => 'profile_no_of_items',
                            'name' => __( 'Number of Items', 'uwp' ),
                            'type' => 'text',
                            'std' => '',
                            'desc' 	=> __( 'Enter number of items to display in profile tabs.', 'uwp' ),
                        ),
                    )
                ),
                'register' => apply_filters( 'uwp_settings_general_register',
                    array(
                        'enable_register_password' => array(
                            'id'   => 'enable_register_password',
                            'name' => __( 'Display Password field in Regsiter Form', 'uwp' ),
                            'desc' => '',
                            'type' => 'checkbox',
                            'std'  => '1',
                            'class' => 'uwp_label_inline',
                        ),
                    )
                ),
                'login' => apply_filters( 'uwp_settings_general_login',
                    array(
                        'login_redirect_to' => array(
                            'id' => 'login_redirect_to',
                            'name' => __( 'Login Redirect Page', 'uwp' ),
                            'desc' => __( 'Set the page to redirect the user after logging in. If no page set it will user WordPress default.', 'uwp' ),
                            'type' => 'select',
                            'options' => $this->uwp_get_pages(),
                            'chosen' => true,
                            'placeholder' => __( 'Select a page', 'uwp' ),
                            'class' => 'uwp_label_block',
                        ),
                        'logout_redirect_to' => array(
                            'id' => 'logout_redirect_to',
                            'name' => __( 'Logout Redirect Page', 'uwp' ),
                            'desc' => __( 'Set the page to redirect the user after logging out. If no page set it will user WordPress default', 'uwp' ),
                            'type' => 'select',
                            'options' => $this->uwp_get_pages(),
                            'chosen' => true,
                            'placeholder' => __( 'Select a page', 'uwp' ),
                            'class' => 'uwp_label_block',
                        ),
                    )
                ),
                'profile' => apply_filters( 'uwp_settings_general_profile',
                    array(
                        'enable_profile_header' => array(
                            'id'   => 'enable_profile_header',
                            'name' => __( 'Display Header in Profile', 'uwp' ),
                            'desc' => '',
                            'type' => 'checkbox',
                            'std'  => '1',
                            'class' => 'uwp_label_inline',
                        ),
                        'enable_profile_body' => array(
                            'id'   => 'enable_profile_body',
                            'name' => __( 'Display Body in Profile', 'uwp' ),
                            'desc' => '',
                            'type' => 'checkbox',
                            'std'  => '1',
                            'class' => 'uwp_label_inline',
                        ),
                        'enable_profile_posts_tab' => array(
                            'id'   => 'enable_profile_posts_tab',
                            'name' => __( 'Display Posts Tab in Profile', 'uwp' ),
                            'desc' => '',
                            'type' => 'checkbox',
                            'std'  => '1',
                            'class' => 'uwp_label_inline',
                        ),
                        'enable_profile_comments_tab' => array(
                            'id'   => 'enable_profile_comments_tab',
                            'name' => __( 'Display Comments Tab in Profile', 'uwp' ),
                            'desc' => '',
                            'type' => 'checkbox',
                            'std'  => '1',
                            'class' => 'uwp_label_inline',
                        ),
                        'profile_avatar_max_size' => array(
                            'id' => 'profile_avatar_max_size',
                            'name' => __( 'Profile Avatar Max File Size', 'uwp' ),
                            'desc' => "",
                            'type' => 'text',
                            'size' => 'regular',
                            'placeholder' => __( 'Enter Registration success email Subject', 'uwp' )
                        ),
                    )
                ),
                'uninstall' => apply_filters( 'uwp_settings_general_uninstall',
                    array(
                        'uninstall_erase_data' => array(
                            'id'   => 'uninstall_erase_data',
                            'name' => __( 'Remove Data on Uninstall?', 'uwp' ),
                            'desc' => '',
                            'type' => 'checkbox',
                            'std'  => '1',
                            'class' => 'uwp_label_inline',
                        ),
                    )
                ),
            ),
            'uwp_notifications' => array(
                'main' => apply_filters( 'uwp_settings_notifications_main',
                    array(
                        'registration_success_email_subject' => array(
                            'id' => 'registration_success_email_subject',
                            'name' => __( 'Registration success email', 'uwp' ),
                            'desc' => "",
                            'type' => 'text',
                            'size' => 'regular',
                            'placeholder' => __( 'Enter Registration success email Subject', 'uwp' )
                        ),
                        'registration_success_email_content' => array(
                            'id' => 'registration_success_email_content',
                            'name' => "",
                            'desc' => "",
                            'type' => 'textarea',
                            'placeholder' => __( 'Enter Registration success email Content', 'uwp' )
                        ),
                        'forgot_password_email_subject' => array(
                            'id' => 'forgot_password_email_subject',
                            'name' => __( 'Forgot password email', 'uwp' ),
                            'desc' => "",
                            'type' => 'text',
                            'size' => 'regular',
                            'placeholder' => __( 'Enter forgot password email Subject', 'uwp' )
                        ),
                        'forgot_password_email_content' => array(
                            'id' => 'forgot_password_email_content',
                            'name' => "",
                            'desc' => "",
                            'type' => 'textarea',
                            'placeholder' => __( 'Enter forgot password email Content', 'uwp' )
                        ),
                        'reset_password_email_subject' => array(
                            'id' => 'reset_password_email_subject',
                            'name' => __( 'Reset password email', 'uwp' ),
                            'desc' => "",
                            'type' => 'text',
                            'size' => 'regular',
                            'placeholder' => __( 'Enter reset password email Subject', 'uwp' )
                        ),
                        'reset_password_email_content' => array(
                            'id' => 'reset_password_email_content',
                            'name' => "",
                            'desc' => "",
                            'type' => 'textarea',
                            'placeholder' => __( 'Enter reset password email Content', 'uwp' )
                        ),
                    )
                ),
            ),
        );

        $uwp_settings = apply_filters( 'uwp_registered_settings', $uwp_settings );

        return $uwp_settings;
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

        $parsed_url = wp_parse_url($_POST['_wp_http_referer']);

        if (!isset($parsed_url['query'])) {
            return $input;
        }
        parse_str( $parsed_url['query'], $referrer );

        $settings = $this->uwp_get_registered_settings();

        $tab      = isset( $referrer['tab'] ) ? $referrer['tab'] : 'main';
        $page      = isset( $referrer['page'] ) ? $referrer['page'] : 'uwp';

        $input = $input ? $input : array();
        $input = apply_filters( 'uwp_settings_'.$page.'_' . $tab . '_sanitize', $input );

        // Loop through each setting being saved and pass it through a sanitization filter
        foreach ( $input as $key => $value ) {
            // Get the setting type (checkbox, select, etc)
            $type = isset( $settings[$page][$tab][$key]['type'] ) ? $settings[$page][$tab][$key]['type'] : false;

            if ( $type ) {
                // Field type specific filter
                $input[$key] = apply_filters( 'uwp_settings_sanitize_' . $type, $value, $key );
            }

            // General filter
            $input[$key] = apply_filters( 'uwp_settings_sanitize', $input[$key], $key );
        }

        // Loop through the whitelist and unset any that are empty for the tab being saved
        if ( ! empty( $settings[$page][$tab] ) ) {
            foreach ( $settings[$page][$tab] as $key => $value ) {

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

        flush_rewrite_rules();
        add_settings_error( 'uwp-notices', '', __( 'Settings updated.', 'uwp' ), 'updated' );

        return $output;
    }

}