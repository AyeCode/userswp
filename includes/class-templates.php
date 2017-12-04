<?php
/**
 * Template related functions
 *
 * This class defines all code necessary for UsersWP templates like login. register etc.
 *
 * @since      1.0.0
 * @author     GeoDirectory Team <info@wpgeodirectory.com>
 */
class UsersWP_Templates {
    
    /**
     * Locates UsersWP templates based on template type.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       string      $template       Template type.
     * @return      string                      The template filename if one is located.
     */
    public function uwp_locate_template( $template ) {
        
        switch ($template) {
            case 'register':
                return $this->uwp_generic_locate_template('register');
                break;

            case 'login':
                return $this->uwp_generic_locate_template('login');
                break;

            case 'forgot':
                return $this->uwp_generic_locate_template('forgot');
                break;

            case 'change':
                return $this->uwp_generic_locate_template('change');
                break;

            case 'reset':
                return $this->uwp_generic_locate_template('reset');
                break;

            case 'account':
                return $this->uwp_generic_locate_template('account');
                break;

            case 'profile':
                return $this->uwp_generic_locate_template('profile');
                break;

            case 'users':
                return $this->uwp_generic_locate_template('users');
                break;
        }

        return apply_filters('uwp_locate_template', false, $template);
    }

    /**
     * Locates UsersWP templates based on template type. 
     * Fallback to core templates when no custom templates found. 
     *
     * @since       1.0.0
     * @package     userswp
     * @param       string      $type           Template type.
     * @return      string                      The template filename if one is located.
     */
    public function uwp_generic_locate_template($type = 'register') {
        
        $plugin_path = dirname( dirname( __FILE__ ) );
        
        $template = locate_template(array("userswp/".$type.".php"));
        if (!$template) {
            $template = $plugin_path . '/templates/'.$type.'.php';
        }
        $template = apply_filters('uwp_template_'.$type, $template);
        return $template;
    }

    /**
     * Doing some access checks for UsersWP related pages.
     *
     * @since       1.0.0
     * @package     userswp
     * @return      bool
     */
    public function access_checks() {
        global $post;

        if (!is_page()) {
            return false;
        }

        $current_page_id = $post->ID;
        
        $register_page = uwp_get_option('register_page', false);
        $login_page = uwp_get_option('login_page', false);
        $forgot_page = uwp_get_option('forgot_page', false);
        $reset_page = uwp_get_option('reset_page', false);

        $change_page = uwp_get_option('change_page', false);
        $account_page = uwp_get_option('account_page', false);
        
        if (( $register_page && ((int) $register_page ==  $current_page_id )) ||
        ( $login_page && ((int) $login_page ==  $current_page_id ) ) ||
        ( $forgot_page && ((int) $forgot_page ==  $current_page_id ) ) ||
        ( $reset_page && ((int) $reset_page ==  $current_page_id ) )) {
            if (is_user_logged_in()) {
                $redirect_page_id = uwp_get_option('account_page', '');
                if (empty($redirect_page_id)) {
                    $redirect_to = home_url('/');
                } else {
                    $redirect_to = get_permalink($redirect_page_id);
                }
                $redirect_to = apply_filters('uwp_logged_in_redirect', $redirect_to);
                wp_redirect($redirect_to);
                exit();
            }
        } elseif ( $account_page && ((int) $account_page ==  $current_page_id ) ||
            ( $change_page && ((int) $change_page ==  $current_page_id ) )) {
            if (!is_user_logged_in()) {
                wp_redirect(get_permalink($login_page));
                exit();
            } else {
                $can_user_can_edit_account = apply_filters('uwp_user_can_edit_own_profile', true, get_current_user_id());
                if (!$can_user_can_edit_account && ((int) $account_page ==  $current_page_id )) {
                    wp_redirect(home_url('/'));
                    exit();
                }
            }
        } else {
            return false;
        }
        
        return false;
    }

    /**
     * If auto generated password, redirects to change password page.
     *
     * @since       1.0.0
     * @package     userswp
     * @return      void
     */
    public function change_default_password_redirect() {
        if (!is_user_logged_in()) {
            return;
        }
        $change_page = uwp_get_option('change_page', false);
        $password_nag = get_user_option('default_password_nag', get_current_user_id());
        
        if ($password_nag) {
            if (is_page()) {
                global $post;
                $current_page_id = $post->ID;
                if ( $change_page && ((int) $change_page ==  $current_page_id ) ) {
                    return;
                }
            }
            if ($change_page) {
                wp_redirect( get_permalink($change_page) );
                exit();   
            }
        }
    }

    /**
     * Redirects /profile to /profile/{username} for loggedin users.
     *
     * @since       1.0.0
     * @package     userswp
     * @return      void
     */
    public function profile_redirect() {
        if (is_page()) {
            global $wp_query, $post;
            $current_page_id = $post->ID;
            $profile_page = uwp_get_option('profile_page', false);
            if ( $profile_page && ((int) $profile_page ==  $current_page_id ) ) {

                if (isset($wp_query->query_vars['uwp_profile'])) {
                    //must be profile page
                    $url_type = apply_filters('uwp_profile_url_type', 'slug');
                    $author_slug = $wp_query->query_vars['uwp_profile'];
                    if ($url_type == 'id') {
                        $user = get_user_by('id', $author_slug);
                    } else {
                        $user = get_user_by('slug', $author_slug);
                    }

                    if (!isset($user->ID)) {
                        global $wp_query;
                        $wp_query->set_404();
                        status_header( 404 );
                        get_template_part( 404 ); exit();
                    }

                } else {
                    if (is_user_logged_in()) {
                        $user_id = get_current_user_id();
                        $profile_url = uwp_build_profile_tab_url($user_id);
                        wp_redirect( $profile_url );
                        exit();
                    } else {
                        wp_redirect( home_url('/') );
                        exit();
                    }

                }

            }
        }
    }

    /**
     * Redirects user to a predefined page after logging out.
     *
     * @since       1.0.0
     * @package     userswp
     * @return      void
     */
    public function logout_redirect() {
        $redirect_page_id = uwp_get_option('logout_redirect_to', '');
        if(isset( $_REQUEST['redirect_to'] )){
            $redirect_to = esc_url($_REQUEST['redirect_to']);
        } elseif ( isset($redirect_page_id) && (int)$redirect_page_id > 0) {
            $redirect_to = get_permalink($redirect_page_id);
        } else {
            $redirect_to = home_url('/');
        }
        $redirect_to = apply_filters('uwp_logout_redirect', $redirect_to);
        wp_redirect( $redirect_to );
        exit();
    }


    /**
     * Redirects wp-login.php to UsersWP login page.
     *
     * @since       1.0.0
     * @package     userswp
     * @return      void
     */
    public function wp_login_redirect() {
        $login_page_id = uwp_get_option('login_page', false);
        $block_wp_login = uwp_get_option('block_wp_login', '');
        if ($login_page_id && $block_wp_login == '1') {
            global $pagenow;
            if( 'wp-login.php' == $pagenow && !isset($_REQUEST['action']) ) {
                $redirect_to = get_permalink($login_page_id);
                wp_redirect( $redirect_to );
                exit();
            }
        }
    }

    /**
     * Changes the login url to the UWP login page.
     *
     * @param $login_url string The URL for login.
     * @param $redirect string The URL to redirect back to upon successful login.
     * @param $force_reauth bool Whether to force reauthorization, even if a cookie is present.
     * @since 1.0.12
     * @package userswp
     *
     * @return string The login url.
     */
    public function wp_login_url($login_url, $redirect, $force_reauth) {
        $login_page_id = uwp_get_option('login_page', false);
        $redirect_page_id = uwp_get_option('login_redirect_to', '');
        if (!is_admin() && $login_page_id) {
            $login_page = get_permalink($login_page_id);
            if($redirect){
                $login_url = add_query_arg( 'redirect_to', $redirect, $login_page );
            }elseif($redirect_page_id){
                $redirect_to = get_permalink($redirect_page_id);
                $login_url = add_query_arg( 'redirect_to', $redirect_to, $login_page );
            }else{
                $login_url = $login_page;
            }
        }

        return $login_url;
    }

    /**
     * Prints html for form fields of that particular form.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       string      $form_type      Form type.
     * @return      void
     */
    public function uwp_template_fields($form_type) {

        global $wpdb;
        $table_name = uwp_get_table_prefix() . 'uwp_form_fields';
        $extras_table_name = uwp_get_table_prefix() . 'uwp_form_extras';

        if ($form_type == 'register') {
            $fields = get_register_form_fields();
        } elseif ($form_type == 'account') {
            $fields = get_account_form_fields();
        } elseif ($form_type == 'change') {
            $fields = get_change_form_fields();
        } else {
            $fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $table_name . " WHERE form_type = %s AND is_active = '1' AND for_admin_use != '1' ORDER BY sort_order ASC", array($form_type)));
        }
        
        if (!empty($fields)) {
            foreach ($fields as $field) {

                if ($form_type == 'account') {
                    if ($field->htmlvar_name == 'uwp_account_display_name') {
                        if ($field->is_active != '1') {
                            continue;
                        }
                    }
                    if ($field->htmlvar_name == 'uwp_account_bio') {
                        if ($field->is_active != '1') {
                            continue;
                        }
                    }
                }

                if ($form_type == 'register') {
                    if ($field->is_active != '1') {
                        continue;
                    }
                    $count = $wpdb->get_var($wpdb->prepare("select count(*) from ".$extras_table_name." where site_htmlvar_name=%s AND form_type = %s", array($field->htmlvar_name, $form_type)));
                    if ($count == 1) {
                        $this->uwp_template_fields_html($field, $form_type);
                    }
                } else {
                    $this->uwp_template_fields_html($field, $form_type);
                }
            }
        }
    }

    /**
     * Adds "Edit Account" form on account page.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       string      $type       Template type.
     * @return      void
     */
    public function uwp_account_edit_form_display($type) {
        if ($type == 'account') {
            ?>
            <form class="uwp-account-form uwp_form" method="post" enctype="multipart/form-data">
                <?php do_action('uwp_template_fields', 'account'); ?>
                <input type="hidden" name="uwp_account_nonce" value="<?php echo wp_create_nonce( 'uwp-account-nonce' ); ?>" />
                <input name="uwp_account_submit" value="<?php echo __( 'Update Account', 'userswp' ); ?>" type="submit">
            </form>
        <?php }
    }

    /**
     * Prints field html based on field type.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       object      $field      Field info.
     * @param       string      $form_type  Form type.
     * @param       int|bool    $user_id    User ID.
     * @return      void
     */
    public function uwp_template_fields_html($field, $form_type, $user_id = false) {
        if (!$user_id) {
            $user_id = get_current_user_id();    
        }

        $value = $this->uwp_get_default_form_value($field);
        if ($form_type == 'account') {
            $user_data = get_userdata($user_id);

            if ($field->htmlvar_name == 'uwp_account_email') {
                $value = $user_data->user_email;
            } elseif ($field->htmlvar_name == 'uwp_account_password') {
                $value = '';
                $field->is_required = 0;
            } elseif ($field->htmlvar_name == 'uwp_account_confirm_password') {
                $value = '';
                $field->is_required = 0;
            } else {
                $value = uwp_get_usermeta($user_id, $field->htmlvar_name, false);
                if ($value != '0' && !$value) {
                    $value = $this->uwp_get_default_form_value($field);
                }
            }

        }

        if (empty($value)) {
            $value = "";
        }

        if (isset($_POST[$field->htmlvar_name]) && $field->field_type != 'password') {
            $value = $_POST[$field->htmlvar_name];
        }

        $field = apply_filters("uwp_form_input_field_{$field->field_type}", $field, $value, $form_type);
        
        $html = apply_filters("uwp_form_input_html_{$field->field_type}", "", $field, $value, $form_type);

        if (empty($html)) {
            $label = $site_title = uwp_get_form_label($field);
            ?>
            <input name="<?php echo $field->htmlvar_name; ?>"
                   class="<?php echo $field->css_class; ?>"
                   placeholder="<?php echo $label; ?>"
                   title="<?php echo $label; ?>"
                <?php if ($field->for_admin_use == 1) { echo 'readonly="readonly"'; } ?>
                <?php if ($field->is_required == 1) { echo 'required="required"'; } ?>
                   type="<?php echo $field->field_type; ?>"
                   value="<?php echo esc_html($value); ?>">
            <?php
        } else {
            echo $html;
        }
    }

    /**
     * Returns default value based on field type.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       object      $field      Field info.
     * @return      string                  Field default value.
     */
    public function uwp_get_default_form_value($field) {
        if ($field->field_type == 'url') {
            if (substr( $field->default_value, 0, 4 ) === "http") {
                $value = $field->default_value;
            } else {
                $value = "";
            }
        } else {
            $value = $field->default_value;
        }

        return $value;
    }

    /**
     * Modifies the author page content with UsersWP profile content.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       string      $content    Original page content.
     * @return      string                  Modified page content.
     */
    public function uwp_author_page_content($content) {
        if (is_author()) {
            return do_shortcode('[uwp_profile]');
        } else {
            return $content;
        }

    }

    /**
     * Modifies the menu item visibility based on UsersWP page type.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       object      $menu_item      Menu item info.
     * @return      object                      Modified menu item.
     */
    public function uwp_setup_nav_menu_item( $menu_item ) {

        if ( is_admin() ) {
            return $menu_item;
        }

        // Prevent a notice error when using the customizer
        $menu_classes = $menu_item->classes;

        if ( is_array( $menu_classes ) ) {
            $menu_classes = implode( ' ', $menu_item->classes );
            $str = 'users-wp-menu ';
            if (strpos($menu_classes, 'users-wp-menu ') !== false) {
                $menu_classes = str_replace($str, '', $menu_classes);
            }
        }

        $register_slug = uwp_get_page_slug('register_page');
        $login_slug = uwp_get_page_slug('login_page');
        $change_slug = uwp_get_page_slug('change_page');
        $account_slug = uwp_get_page_slug('account_page');
        $profile_slug = uwp_get_page_slug('profile_page');
        $forgot_slug = uwp_get_page_slug('forgot_page');
        $logout_slug = "logout";

        $register_class = "users-wp-{$register_slug}-nav";
        $login_class = "users-wp-{$login_slug}-nav";
        $change_class = "users-wp-{$change_slug}-nav";
        $account_class = "users-wp-{$account_slug}-nav";
        $profile_class = "users-wp-{$profile_slug}-nav";
        $forgot_class = "users-wp-{$forgot_slug}-nav";
        $logout_class = "users-wp-{$logout_slug}-nav";

        switch ( $menu_classes ) {
            case $register_class:
                if ( is_user_logged_in() ) {
                    $menu_item->_invalid = true;
                } else {
                    $menu_item->url = get_permalink(uwp_get_option('register_page', 0));
                }
                break;
            case $login_class:
                if ( is_user_logged_in() ) {
                    $menu_item->_invalid = true;
                } else {
                    $menu_item->url = get_permalink(uwp_get_option('login_page', 0));
                }
                break;
            case $account_class:
                if ( ! is_user_logged_in() ) {
                    $menu_item->_invalid = true;
                } else {
                    $menu_item->url = get_permalink(uwp_get_option('account_page', 0));
                }
                break;
            case $profile_class:
                if ( ! is_user_logged_in() ) {
                    $menu_item->_invalid = true;
                } else {
                    $menu_item->url = get_permalink(uwp_get_option('profile_page', 0));
                }
                break;
            case $change_class:
                if ( ! is_user_logged_in() ) {
                    $menu_item->_invalid = true;
                } else {
                    $menu_item->url = get_permalink(uwp_get_option('change_page', 0));
                }
                break;
            case $forgot_class:
                if ( is_user_logged_in() ) {
                    $menu_item->_invalid = true;
                } else {
                    $menu_item->url = get_permalink(uwp_get_option('forgot_page', 0));
                }
                break;
            case $logout_class:
                if ( ! is_user_logged_in() ) {
                    $menu_item->_invalid = true;
                } else {
                    $menu_item->url = $this->uwp_logout_url();
                }
                break;
        }

        $menu_item = apply_filters('uwp_setup_nav_menu_item', $menu_item, $menu_classes);

        return $menu_item;

    }

    /**
     * Returns the logout url by adding redirect page link.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       null        $custom_redirect   Redirect page link.
     * @return      string                         Logout url.
     */
    public function uwp_logout_url( $custom_redirect = null ) {

        $redirect = null;

        if ( !empty( $custom_redirect ) ) {
            $redirect = esc_url( $custom_redirect );
        } else if ( uwp_get_option('logout_redirect_to', false) ) {
            $redirect = esc_url( get_permalink( uwp_get_option('logout_redirect_to', 0) ) );
        }

        return wp_logout_url( apply_filters( 'uwp_logout_url', $redirect, $custom_redirect ) );

    }

    /**
     * Redirects to UsersWP info page after plugin activation.
     *
     * @since       1.0.0
     * @package     userswp
     * @return      void
     */
    public function uwp_activation_redirect() {

        if (get_option('uwp_activation_redirect', false)) {
            delete_option('uwp_activation_redirect');
            wp_redirect(admin_url('admin.php?page=userswp&tab=main&subtab=info'));
            exit;

        }

    }

    /**
     * Prints UsersWP fields in WP-Admin Users Edit page.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       object      $user       User Object.
     */
    public function get_profile_extra_admin_edit($user) {
        echo $this->get_profile_extra_edit($user);
    }

    /**
     * Gets UsersWP fields in WP-Admin Users Edit page.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       object      $user       User Object.
     * @return      string                  HTML string.
     */
    public function get_profile_extra_edit($user) {
        ob_start();
        global $wpdb;
        $table_name = uwp_get_table_prefix() . 'uwp_form_fields';
        $fields = $wpdb->get_results("SELECT * FROM " . $table_name . " WHERE form_type = 'account' AND is_default = '0' ORDER BY sort_order ASC");
        if ($fields) {
            ?>
            <div class="uwp-profile-extra">
                <table class="uwp-profile-extra-table form-table">
                    <?php
                    foreach ($fields as $field) {

                        // Icon
                        $icon = uwp_get_field_icon( $field->field_icon );

                        if ($field->field_type == 'fieldset') {
                            ?>
                            <tr style="margin: 0; padding: 0">
                                <th class="uwp-profile-extra-key" style="margin: 0; padding: 0"><h3 style="margin: 10px 0;"><?php echo $icon.$field->site_title; ?></h3></th>
                                <td></td>
                            </tr>
                            <?php
                        } else { ?>
                            <tr>
                                <th class="uwp-profile-extra-key"><?php echo $icon.$field->site_title; ?></th>
                                <td class="uwp-profile-extra-value">
                                    <?php $this->uwp_template_fields_html($field, 'account', $user->ID); ?>
                                </td>
                            </tr>
                            <?php
                        }
                    }
                    ?>
                </table>
            </div>
            <?php
        }
        $output = ob_get_contents();
        ob_end_clean();
        return trim($output);
    }

    /**
     * Adds the UsersWP body class to body tag.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       array       $classes     Existing class array.
     * @return      array                    Modified class array.
     */
    public function uwp_add_body_class( $classes ) {
        
        if ( is_uwp_page() ) {
            $classes[] = 'uwp_page';
        }

        return $classes;
    }
}