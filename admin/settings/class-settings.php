<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://wpgeodirectory.com
 * @since      1.0.0
 *
 * @package    userswp
 * @subpackage userswp/admin/settings
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    userswp
 * @subpackage userswp/admin/settings
 * @author     GeoDirectory Team <info@wpgeodirectory.com>
 */
class UsersWP_Admin_Settings {

    private $form_builder;
    
    public function __construct($form_builder) {

        $this->form_builder = $form_builder;
        
    }
    
    public function init_settings() {

        global $uwp_options;
        $uwp_options = $this->uwp_get_settings();

    }

    public function uwp_settings_page() {

        $page = isset( $_GET['page'] ) ? $_GET['page'] : 'userswp';

        $settings_array = uwp_get_settings_tabs();
        $active_tab = isset( $_GET['tab'] ) && array_key_exists( $_GET['tab'], $settings_array[$page] ) ? $_GET['tab'] : 'main';
        ?>
        <div class="wrap">

            <h1><?php echo get_admin_page_title(); ?></h1>

            <div id="users-wp">
                <div class="item-list-tabs">

                    <?php if (isset($settings_array[$page]) && count($settings_array[$page]) > 1) { ?>

                        <div class="wp-filter" style="margin-top: 0;margin-bottom: 5px">
                        <ul class="filter-links">
                        <?php
                        foreach( $settings_array[$page] as $tab_id => $tab_name ) {

                            $tab_url = add_query_arg( array(
                                'settings-updated' => false,
                                'tab' => $tab_id,
                                'subtab' => false
                            ) );

                            $active = $active_tab == $tab_id ? ' current selected' : '';
                            ?>
                            <li id="uwp-<?php echo $tab_id; ?>-li">
                                <a class="<?php echo $active; ?>" id="uwp-<?php echo $tab_id; ?>" href="<?php echo esc_url( $tab_url ); ?>"><?php echo esc_html( $tab_name ); ?></a>
                            </li>
                            <?php
                        }
                        ?>
                        </ul>
                        </div>

                    <?php } ?>
                    
                    <?php do_action($page.'_settings_'.$active_tab.'_tab_content_before'); ?>

                    <div class="postbox">
                        <div class="tab-content inside">
                            <?php
                            // {current page}_settings_{active tab}_tab_content
                            // ex: uwp_settings_main_tab_content
                            do_action($page.'_settings_'.$active_tab.'_tab_content', uwp_display_form());
                            ?>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    <?php }
    
    public function get_general_content() {

        $subtab = 'general';

        if (isset($_GET['subtab'])) {
            $subtab = $_GET['subtab'];
        }
        ?>
        <div class="item-list-sub-tabs">
            <ul class="item-list-sub-tabs-ul">
                <li class="<?php if ($subtab == 'general') { echo "current selected"; } ?>">
                    <a href="<?php echo add_query_arg(array('tab' => 'main', 'subtab' => 'general')); ?>"><?php echo __( 'General Settings', 'userswp' ); ?></a>
                </li>
                <li class="<?php if ($subtab == 'shortcodes') { echo "current selected"; } ?>">
                    <a href="<?php echo add_query_arg(array('tab' => 'main', 'subtab' => 'shortcodes')); ?>"><?php echo __( 'Shortcodes', 'userswp' ); ?></a>
                </li>
                <li class="<?php if ($subtab == 'info') { echo "current selected"; } ?>">
                    <a href="<?php echo add_query_arg(array('tab' => 'main', 'subtab' => 'info')); ?>"><?php echo __( 'Info', 'userswp' ); ?></a>
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

    public function uwp_get_pages_as_option($selected) {
        $page_options = uwp_get_pages();
        foreach ($page_options as $key => $page_title) {
            ?>
            <option value="<?php echo $key; ?>" <?php selected( $selected, $key ); ?>><?php echo $page_title; ?></option>
            <?php
        }
    }
    
    public function get_general_shortcodes_content() {
        ?>
        <table class="uwp-form-table">

            <?php do_action('uwp_before_general_shortcodes_content'); ?>

            <tr valign="top">
                <th scope="row"><?php echo __( 'User Profile Shortcode', 'userswp' ); ?></th>
                <td>
                    <span class="short_code">[uwp_profile]</span>
                    <span class="description"><?php echo __( 'This is the shortcode for the front end user\'s profile.', 'userswp' ); ?></span>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row"><?php echo __( 'Register Form Shortcode', 'userswp' ); ?></th>
                <td>
                    <span class="short_code">[uwp_register]</span>
                    <span class="description"><?php echo __( 'This is the shortcode for the front end register form.', 'userswp' ); ?></span>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row"><?php echo __( 'Login Form Shortcode', 'userswp' ); ?></th>
                <td>
                    <span class="short_code">[uwp_login]</span>
                    <span class="description"><?php echo __( 'This is the shortcode for the front end login form.', 'userswp' ); ?></span>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row"><?php echo __( 'Account Form Shortcode', 'userswp' ); ?></th>
                <td>
                    <span class="short_code">[uwp_account]</span>
                    <span class="description"><?php echo __( 'This is the shortcode for the front end account form.', 'userswp' ); ?></span>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row"><?php echo __( 'Forgot Password Form Shortcode', 'userswp' ); ?></th>
                <td>
                    <span class="short_code">[uwp_forgot]</span>
                    <span class="description"><?php echo __( 'This is the shortcode for the front end forgot password form.', 'userswp' ); ?></span>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row"><?php echo __( 'Change Password Form Shortcode', 'userswp' ); ?></th>
                <td>
                    <span class="short_code">[uwp_change]</span>
                    <span class="description"><?php echo __( 'This is the shortcode for the front end change password form.', 'userswp' ); ?></span>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row"><?php echo __( 'Reset Password Form Shortcode', 'userswp' ); ?></th>
                <td>
                    <span class="short_code">[uwp_reset]</span>
                    <span class="description"><?php echo __( 'This is the shortcode for the front end reset password form.', 'userswp' ); ?></span>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row"><?php echo __( 'Users List Shortcode', 'userswp' ); ?></th>
                <td>
                    <span class="short_code">[uwp_users]</span>
                    <span class="description"><?php echo __( 'This is the shortcode for the front end users list.', 'userswp' ); ?></span>
                </td>
            </tr>

            <?php do_action('uwp_after_general_shortcodes_content'); ?>

        </table>
        <?php
    }

    //subtabs
    public function get_general_general_content() {
        echo uwp_display_form();
    }

    public function display_form_title($title, $page, $active_tab) {
        if ($page == 'userswp' && $active_tab == 'main') {
            $title = __('General Options', 'userswp');
        }
        return $title;
    }

    public function get_general_info_content() {
        ?>
        <h3><?php echo __( 'Welcome to UsersWP', 'userswp' ); ?></h3>
        <h4><?php echo __( 'Version 1.0.0', 'userswp' ); ?></h4>

        <h3><?php echo __( 'Flexible, Lightweight and Fast', 'userswp' ); ?></h3>
        <p><?php echo __( 'UsersWP allows you to add a customizable register and login form to your website.
        It also adds an extended profile page that override the default author page.
        UsersWP has been built to be lightweight and fast', 'userswp' ); ?></p>

        <h3><?php echo __( 'Less options, more hooks', 'userswp' ); ?></h3>
        <p><?php echo __( 'We cut down the options to the bare minimum and you will not find any fancy
        styling options in this plugin as we believe they belong in your theme.
        This doesn\'t mean that you cannot customize the plugin behaviour.
        To do this we provided a long list of Filters and Actions for any developer
        to extend UsersWP to fit their needs.', 'userswp' ); ?></p>

        <h3><?php echo __( 'Override Templates', 'userswp' ); ?></h3>
        <p><?php echo __( 'If you need to change the look and feel of any UsersWP templates,
        simply create a folder named userswp inside your active child theme
        and copy the template you wish to modify in it. You can now modify the template.
        The plugin will use your modified version and you don\'t have to worry about plugin or theme updates.
        <a href="https://userswp.io/docs/override-templates/">Click here for examples</a>', 'userswp' ); ?></p>

        <h3><?php echo __( 'Add-ons', 'userswp' ); ?></h3>
        <p><?php echo __( 'We have a long list of free and premium add-ons that will help you extend users management on your website.
        <a href="https://userswp.io/downloads/category/addons/">Click here for our official free and premium add-ons</a>', 'userswp' ); ?></p>
        <?php
    }

    public function get_form_builder_tabs() {
        $tab = 'account';

        if (isset($_GET['tab'])) {
            $tab = $_GET['tab'];
        }

        ?>

        <div class="wp-filter" style="margin-top: 0;margin-bottom: 5px">
            <ul class="filter-links">
                <li id="uwp-form-builder-account-li">
                    <a id="uwp-form-builder-account" class="<?php if ($tab == 'account') { echo "current selected"; } ?>" href="<?php echo add_query_arg(array('tab' => 'account')); ?>"><?php echo __( 'Account', 'userswp' ); ?></a>
                </li>
                <li id="uwp-form-builder-register-li">
                    <a id="uwp-form-builder-register" class="<?php if ($tab == 'register') { echo "current selected"; } ?>" href="<?php echo add_query_arg(array('tab' => 'register')); ?>"><?php echo __( 'Register', 'userswp' ); ?></a>
                </li>
                <?php do_action('uwp_form_builder_tab_items', $tab); ?>
            </ul>
        </div>
        <?php
    }

    public function get_form_builder_content() {

        $tab = 'account';

        if (isset($_GET['tab'])) {
            $tab = $_GET['tab'];
        }

        $tab_content = $this->form_builder->uwp_form_builder($tab);
        if ($tab == 'account') {
            ?>
            <h3 class=""><?php echo __( 'Manage Account Form Fields', 'userswp' ); ?></h3>
            <?php
            echo $tab_content;
        } elseif ($tab == 'register') {
            ?>
            <h3 class=""><?php echo __( 'Manage Register Form Fields', 'userswp' ); ?></h3>
            <?php
            echo $tab_content;
        }
        
        do_action('uwp_extra_form_builder_content', $tab, $tab_content);
    }

    public function generic_display_form() {
        echo uwp_display_form();
    }

    public function get_recaptcha_content() {
        echo uwp_display_form();
    }

    public function get_geodirectory_content() {
        echo uwp_display_form();
    }

    public function get_notifications_content() {
        ?>
        <h3 class=""><?php echo __( 'Email Notifications', 'userswp' ); ?></h3>

            <table class="uwp-form-table">
               <tbody>
               <tr valign="top">
                    <th scope="row" class="titledesc"><?php echo __( 'List of usable shortcodes', 'userswp' ); ?></th>
                    <td class="forminp">
                        <span class="description">[#site_name_url#],[#site_name#],[#to_name#],[#from_name#],[#login_url#],[#user_name#],[#from_email#],[#user_login#],[#username#],[#current_date#],[#login_details#],[#reset_link#],[#activation_link#]</span>
                    </td>
               </tr>
               </tbody>
            </table>

        <?php
        echo uwp_display_form();
    }

    public function uwp_register_settings() {

        if ( false == get_option( 'uwp_settings' ) ) {
            add_option( 'uwp_settings' );
        }

        $callback = new UsersWP_Callback();

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
                        method_exists($callback, 'uwp_' . $option['type'] . '_callback') ? array($callback, 'uwp_' . $option['type'] . '_callback') : array($callback, 'uwp_missing_callback'),
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
                            'global'        => isset( $setting['global'] )        ? $setting['global']        : true,
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
        
        $file_obj = new UsersWP_Files();

        /**
         * 'Whitelisted' uwp settings, filters are provided for each settings
         * section to allow extensions and other plugins to add their own settings
         */
        $uwp_settings = array(
            /** General Settings */
            'userswp' => array(
                'main' => apply_filters( 'uwp_settings_general_main',
                    array(
                        'profile_page' => array(
                            'id' => 'profile_page',
                            'name' => __( 'User Profile Page', 'userswp' ),
                            'desc' => __( 'This is the front end user\'s profile page. This page automatically overrides the default WordPress author page.', 'userswp' ),
                            'type' => 'select',
                            'options' => uwp_get_pages(),
                            'chosen' => true,
                            'placeholder' => __( 'Select a page', 'userswp' ),
                            'class' => 'uwp_label_block',
                        ),
                        'register_page' => array(
                            'id' => 'register_page',
                            'name' => __( 'Register Page', 'userswp' ),
                            'desc' => __( 'This is the front end register page. This is where users create their account.', 'userswp' ),
                            'type' => 'select',
                            'options' => uwp_get_pages(),
                            'chosen' => true,
                            'placeholder' => __( 'Select a page', 'userswp' ),
                            'class' => 'uwp_label_block',
                        ),
                        'login_page' => array(
                            'id' => 'login_page',
                            'name' => __( 'Login Page', 'userswp' ),
                            'desc' => __( 'This is the front end login page. This is where users will login after creating their account.', 'userswp' ),
                            'type' => 'select',
                            'options' => uwp_get_pages(),
                            'chosen' => true,
                            'placeholder' => __( 'Select a page', 'userswp' ),
                            'class' => 'uwp_label_block',
                        ),
                        'account_page' => array(
                            'id' => 'account_page',
                            'name' => __( 'Account Page', 'userswp' ),
                            'desc' => __( 'This is the front end account page. This is where users can edit their account.', 'userswp' ),
                            'type' => 'select',
                            'options' => uwp_get_pages(),
                            'chosen' => true,
                            'placeholder' => __( 'Select a page', 'userswp' ),
                            'class' => 'uwp_label_block',
                        ),
                        'change_page' => array(
                            'id' => 'change_page',
                            'name' => __( 'Change Password Page', 'userswp' ),
                            'desc' => __( 'This is the front end Change Password page.', 'userswp' ),
                            'type' => 'select',
                            'options' => uwp_get_pages(),
                            'chosen' => true,
                            'placeholder' => __( 'Select a page', 'userswp' ),
                            'class' => 'uwp_label_block',
                        ),
                        'forgot_page' => array(
                            'id' => 'forgot_page',
                            'name' => __( 'Forgot Password Page', 'userswp' ),
                            'desc' => __( 'This is the front end Forgot Password page. This is the page where users are sent to reset their password when they lose it.', 'userswp' ),
                            'type' => 'select',
                            'options' => uwp_get_pages(),
                            'chosen' => true,
                            'placeholder' => __( 'Select a page', 'userswp' ),
                            'class' => 'uwp_label_block',
                        ),
                        'reset_page' => array(
                            'id' => 'reset_page',
                            'name' => __( 'Reset Password Page', 'userswp' ),
                            'desc' => __( 'This is the front end Reset Password page. This is the page where users can reset their password when they lose it.', 'userswp' ),
                            'type' => 'select',
                            'options' => uwp_get_pages(),
                            'chosen' => true,
                            'placeholder' => __( 'Select a page', 'userswp' ),
                            'class' => 'uwp_label_block',
                        ),
                        'users_page' => array(
                            'id' => 'users_page',
                            'name' => __( 'Users List Page', 'userswp' ),
                            'desc' => __( 'This is the front end Users List page. This is the page where all registered users of the websites are listed.', 'userswp' ),
                            'type' => 'select',
                            'options' => uwp_get_pages(),
                            'chosen' => true,
                            'placeholder' => __( 'Select a page', 'userswp' ),
                            'class' => 'uwp_label_block',
                        ),
                        'profile_no_of_items' => array(
                            'id' => 'profile_no_of_items',
                            'name' => __( 'Number of Items', 'userswp' ),
                            'type' => 'text',
                            'std' => '',
                            'desc' 	=> __( 'Enter number of items to display in profile tabs.', 'userswp' ),
                        ),
                    )
                ),
                'register' => apply_filters( 'uwp_settings_general_register', uwp_settings_general_register_fields()),
                'login' => apply_filters( 'uwp_settings_general_login', uwp_settings_general_loginout_fields()),
                'profile' => apply_filters( 'uwp_settings_general_profile',
                    array(
                        'enable_profile_header' => array(
                            'id'   => 'enable_profile_header',
                            'name' => __( 'Display Header in Profile', 'userswp' ),
                            'desc' => '',
                            'type' => 'checkbox',
                            'std'  => '1',
                            'class' => 'uwp_label_inline',
                        ),
                        'profile_avatar_size' => array(
                            'id'   => 'profile_avatar_size',
                            'name' => __( 'Profile Avatar max file size', 'userswp' ),
                            'desc' => sprintf(__( 'Enter Profile Avatar max file size in Kb. e.g. 512 for 512 kb, 1024 for 1 Mb, 2048 for 2 Mb etc. If empty WordPress default (%s) will be used.', 'userswp' ), '<b>'.$file_obj->uwp_formatSizeinKb($file_obj->uwp_get_max_upload_size()).'</b>'),
                            'type' => 'number',
                            'std'  => '',
                            'size' => 'regular',
                            'placeholder' => __( 'Enter Profile Avatar max file size.', 'userswp' ),
                        ),
                        'profile_banner_size' => array(
                            'id'   => 'profile_banner_size',
                            'name' => __( 'Profile Banner max file size', 'userswp' ),
                            'desc' => sprintf(__( 'Enter Profile Banner max file size in Kb. e.g. 512 for 512 kb, 1024 for 1 Mb, 2048 for 2 Mb etc. If empty WordPress default (%s) will be used.', 'userswp' ), '<b>'.$file_obj->uwp_formatSizeinKb($file_obj->uwp_get_max_upload_size()).'</b>'),
                            'type' => 'number',
                            'std'  => '',
                            'size' => 'regular',
                            'placeholder' => __( 'Enter Profile Banner max file size.', 'userswp' ),
                        ),
                        'profile_banner_width' => array(
                            'id'   => 'profile_banner_width',
                            'name' => __( 'Profile banner width', 'userswp' ),
                            'desc' => '',
                            'type' => 'number',
                            'std'  => '1000',
                            'size' => 'regular',
                            'placeholder' => __( 'Enter Profile banner width in Pixels', 'userswp' ),
                        ),
                        'profile_default_banner' => array(
                            'id' => 'profile_default_banner',
                            'name' => __( 'Default banner image', 'userswp' ),
                            'desc' => __( 'Recommended image size: 1000x300', 'userswp'),
                            'type' => 'media',
                            'std' => '',
                            'placeholder' => USERSWP_PLUGIN_URL."public/assets/images/banner.png"
                        ),
                        'profile_default_profile' => array(
                            'id' => 'profile_default_profile',
                            'name' => __( 'Default profile image', 'userswp' ),
                            'desc' => __( 'Recommended image size: 150x150', 'userwp'),
                            'type' => 'media',
                            'std' => '',
                            'placeholder' => USERSWP_PLUGIN_URL."public/assets/images/no_profile.png"
                        ),
                        'enable_profile_body' => array(
                            'id'   => 'enable_profile_body',
                            'name' => __( 'Display Body in Profile', 'userswp' ),
                            'desc' => '',
                            'type' => 'checkbox',
                            'std'  => '1',
                            'class' => 'uwp_label_inline',
                        ),
                        'enable_profile_tabs' => array(
                            'id' => 'enable_profile_tabs',
                            'name' => __( 'Choose the tabs to display in Profile', 'userswp' ),
                            'desc' => __( 'Choose the tabs to display in UsersWP Profile', 'userswp' ),
                            'multiple'    => true,
                            'chosen'      => true,
                            'type'        => 'select_order',
                            'options' =>   $this->uwp_available_tab_items(),
                            'placeholder' => __( 'Select Tabs', 'userswp' )
                        ),
                    )
                ),
                'users' => apply_filters( 'uwp_settings_general_profile',
                    array(
                        'users_default_layout' => array(
                            'id' => 'users_default_layout',
                            'name' => __( 'Users default layout', 'userswp' ),
                            'desc' => __( 'Choose the default layout for Users Page - Users List', 'userswp' ),
                            'type'        => 'select',
                            'options' =>   $this->uwp_available_users_layout(),
                            'placeholder' => __( 'Select Layout', 'userswp' )
                        ),
                        'users_excluded_from_list' => array(
                            'id' => 'users_excluded_from_list',
                            'name' => __( 'Users to exclude', 'userswp' ),
                            'type' => 'text',
                            'std' => '',
                            'desc' 	=> __( 'Enter comma separated ids of users to exclude from users listing.', 'userswp' ),
                        ),
                    )
                ),
                'change' => apply_filters( 'uwp_settings_general_change',
                    array(
                        'change_enable_old_password' => array(
                            'id'   => 'change_enable_old_password',
                            'name' => __( 'Enabled Old Password?', 'userswp' ),
                            'desc' => 'This option adds an extra layer of security. User need to enter their old password before changing the password.',
                            'type' => 'checkbox',
                            'std'  => '1',
                            'class' => 'uwp_label_inline',
                        ),
                        'change_disable_password_nag' => array(
                            'id'   => 'change_disable_password_nag',
                            'name' => __( 'Disable system generated password notice.', 'userswp' ),
                            'desc' => 'This option will disable system generated password notice if user has not changed default password after registration.',
                            'type' => 'checkbox',
                            'std'  => '0',
                            'class' => 'uwp_label_inline',
                        ),
                    )
                ),
                'uninstall' => apply_filters( 'uwp_settings_general_uninstall',
                    array(
                        'uninstall_erase_data' => array(
                            'id'   => 'uninstall_erase_data',
                            'name' => __( 'UsersWP', 'userswp' ),
                            'desc' => __( 'Remove all data when deleted?', 'userswp' ),
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
                        'registration_activate_email_subject' => array(
                            'id' => 'registration_activate_email_subject',
                            'name' => __( 'Registration activate email', 'userswp' ),
                            'desc' => "",
                            'type' => 'text',
                            'size' => 'regular',
                            'placeholder' => __( 'Enter Registration activate email Subject', 'userswp' )
                        ),
                        'registration_activate_email_content' => array(
                            'id' => 'registration_activate_email_content',
                            'name' => "",
                            'desc' => "",
                            'type' => 'textarea',
                            'placeholder' => __( 'Enter Registration activate email Content', 'userswp' )
                        ),
                        'registration_success_email_subject' => array(
                            'id' => 'registration_success_email_subject',
                            'name' => __( 'Registration success email', 'userswp' ),
                            'desc' => "",
                            'type' => 'text',
                            'size' => 'regular',
                            'placeholder' => __( 'Enter Registration success email Subject', 'userswp' )
                        ),
                        'registration_success_email_content' => array(
                            'id' => 'registration_success_email_content',
                            'name' => "",
                            'desc' => "",
                            'type' => 'textarea',
                            'placeholder' => __( 'Enter Registration success email Content', 'userswp' )
                        ),
                        'forgot_password_email_subject' => array(
                            'id' => 'forgot_password_email_subject',
                            'name' => __( 'Forgot password email', 'userswp' ),
                            'desc' => "",
                            'type' => 'text',
                            'size' => 'regular',
                            'placeholder' => __( 'Enter forgot password email Subject', 'userswp' )
                        ),
                        'forgot_password_email_content' => array(
                            'id' => 'forgot_password_email_content',
                            'name' => "",
                            'desc' => "",
                            'type' => 'textarea',
                            'placeholder' => __( 'Enter forgot password email Content', 'userswp' )
                        ),
                        'change_password_email_subject' => array(
                            'id' => 'change_password_email_subject',
                            'name' => __( 'Change password email', 'userswp' ),
                            'desc' => "",
                            'type' => 'text',
                            'size' => 'regular',
                            'placeholder' => __( 'Enter change password email Subject', 'userswp' )
                        ),
                        'change_password_email_content' => array(
                            'id' => 'change_password_email_content',
                            'name' => "",
                            'desc' => "",
                            'type' => 'textarea',
                            'placeholder' => __( 'Enter change password email Content', 'userswp' )
                        ),
                        'reset_password_email_subject' => array(
                            'id' => 'reset_password_email_subject',
                            'name' => __( 'Reset password email', 'userswp' ),
                            'desc' => "",
                            'type' => 'text',
                            'size' => 'regular',
                            'placeholder' => __( 'Enter reset password email Subject', 'userswp' )
                        ),
                        'reset_password_email_content' => array(
                            'id' => 'reset_password_email_content',
                            'name' => "",
                            'desc' => "",
                            'type' => 'textarea',
                            'placeholder' => __( 'Enter reset password email Content', 'userswp' )
                        ),
                        'enable_account_update_notification' => array(
                            'id'   => 'enable_account_update_notification',
                            'name' => __( 'Account update email', 'userswp' ),
                            'desc' => 'Enable account update notification',
                            'type' => 'checkbox',
                            'std'  => '0',
                            'class' => 'uwp_label_inline',
                        ),
                        'account_update_email_subject' => array(
                            'id' => 'account_update_email_subject',
                            'name' => "",
                            'desc' => "",
                            'type' => 'text',
                            'size' => 'regular',
                            'placeholder' => __( 'Enter account update email Subject', 'userswp' )
                        ),
                        'account_update_email_content' => array(
                            'id' => 'account_update_email_content',
                            'name' => "",
                            'desc' => "",
                            'type' => 'textarea',
                            'placeholder' => __( 'Enter account update email Content', 'userswp' )
                        ),
                    )
                ),
                'admin' => apply_filters( 'uwp_settings_notifications_admin',
                    array(
                        'registration_success_email_subject_admin' => array(
                            'id' => 'registration_success_email_subject_admin',
                            'name' => __( 'New account registration', 'userswp' ),
                            'desc' => "",
                            'type' => 'text',
                            'std' => __( 'New account registration', 'userswp' ),
                            'size' => 'regular',
                        ),
                        'registration_success_email_content_admin' => array(
                            'id' => 'registration_success_email_content_admin',
                            'name' => "",
                            'desc' => "",
                            'type' => 'textarea',
                            'std' => __("A user has been registered recently on your website. [#extras#]", "userswp"),
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
        $page      = isset( $referrer['page'] ) ? $referrer['page'] : 'userswp';

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
        add_settings_error( 'uwp-notices', '', __( 'Settings updated.', 'userswp' ), 'updated' );

        return $output;
    }

    public function uwp_available_tab_items_options(){
        $all_tabs = $this->uwp_available_tab_items();
        $return = array();

        if(!empty($all_tabs) && is_array($all_tabs)) {
            foreach ($all_tabs as $tab_key => $tab) {
                $return[$tab_key] = $tab;
            }
        }

        return $return;
    }

    public function uwp_available_tab_items() {
        $tabs_arr = array(
            'more_info' => __( 'More Info', 'userswp' ),
            'posts' => __( 'Posts', 'userswp' ),
            'comments' => __( 'Comments', 'userswp' ),
        );

        $tabs_arr = apply_filters('uwp_available_tab_items', $tabs_arr);

        return $tabs_arr;
    }

    public function uwp_available_users_layout() {
        $tabs_arr = array(
            'list' => __( 'List View', 'userswp' ),
            '2col' => __( 'Grid View - 2 Column', 'userswp' ),
            '3col' => __( 'Grid View - 3 Column', 'userswp' ),
            '4col' => __( 'Grid View - 4 Column', 'userswp' ),
            '5col' => __( 'Grid View - 5 Column', 'userswp' ),
        );

        $tabs_arr = apply_filters('uwp_available_users_layout', $tabs_arr);

        return $tabs_arr;
    }

}