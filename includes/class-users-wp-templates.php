<?php
/**
 * Template related functions
 *
 * This class defines all code necessary for UsersWP templates like login. register etc.
 *
 * @link       http://wpgeodirectory.com
 * @since      1.0.0
 *
 * @package    Users_WP
 * @subpackage Users_WP/includes
 */

/**
 * Define the templates functionality.
 *
 * @since      1.0.0
 * @package    Users_WP
 * @subpackage Users_WP/includes
 * @author     GeoDirectory Team <info@wpgeodirectory.com>
 */
class Users_WP_Templates {

    protected $loader;

    public function __construct($loader) {
        $this->loader = $loader;
    }

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
    
    public function uwp_generic_locate_template($type = 'register') {
        
        $plugin_path = dirname( dirname( __FILE__ ) );
        
        $template = locate_template(array("userswp/".$type.".php"));
        if (!$template) {
            $template = $plugin_path . '/templates/'.$type.'.php';
        }
        $template = apply_filters('uwp_template_'.$type, $template);
        return $template;
    }

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
        } elseif ( $account_page && ((int) $account_page ==  $current_page_id ) ) {
            if (!is_user_logged_in()) {
                wp_redirect(get_permalink($login_page));
                exit();
            }
        } else {
            return false;
        }
        
        return false;
    }

    public function profile_redirect() {
        if (is_page()) {
            global $wp_query, $post;
            $current_page_id = $post->ID;
            $profile_page = uwp_get_option('profile_page', false);
            if ( $profile_page && ((int) $profile_page ==  $current_page_id ) ) {

                if (isset($wp_query->query_vars['uwp_profile'])) {
                    //must be profile page
                    $username = $wp_query->query_vars['uwp_profile'];
                    if ( !username_exists( $username ) ) {
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

    public function logout_redirect() {
        $redirect_page_id = uwp_get_option('logout_redirect_to', '');
        if (empty($redirect_page_id)) {
            $redirect_to = home_url('/');
        } else {
            $redirect_to = get_permalink($redirect_page_id);
        }
        $redirect_to = apply_filters('uwp_logout_redirect', $redirect_to);
        wp_redirect( $redirect_to );
        exit();
    }

    public function uwp_template_fields($form_type) {

        global $wpdb;
        $table_name = $wpdb->prefix . 'uwp_form_fields';
        $extras_table_name = $wpdb->prefix . 'uwp_form_extras';

        if ($form_type == 'register') {
            $fields = get_register_form_fields();
        } elseif ($form_type == 'account') {
            $fields = get_account_form_fields();
        } else {
            $fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $table_name . " WHERE form_type = %s AND is_active = '1' ORDER BY sort_order ASC", array($form_type)));
        }

        if (!empty($fields)) {
            foreach ($fields as $field) {
                if ($form_type == 'register') {
                    $enable_password = uwp_get_option('enable_register_password', false);
                    if ($enable_password != '1') {
                        if ( ($field->htmlvar_name == 'uwp_account_password') OR ($field->htmlvar_name == 'uwp_account_confirm_password') ) {
                            continue;
                        }
                    }
                    $count = $wpdb->get_var($wpdb->prepare("select count(*) from ".$extras_table_name." where site_htmlvar_name=%s", array($field->htmlvar_name)));
                    if ($count == 1) {
                        $this->uwp_template_fields_html($field, $form_type);
                    }
                } else {
                    $this->uwp_template_fields_html($field, $form_type);
                }
            }
        }
    }
    
    public function uwp_account_edit_form_display($type) {
        if ($type == 'account') {
            ?>
            <form class="uwp-account-form" method="post" enctype="multipart/form-data">
                <?php do_action('uwp_template_fields', 'account'); ?>
                <input type="hidden" name="uwp_account_nonce" value="<?php echo wp_create_nonce( 'uwp-account-nonce' ); ?>" />
                <input name="uwp_account_submit" value="<?php echo __( 'Update Account', 'uwp' ); ?>" type="submit">
            </form>
        <?php }
    }

    public function uwp_template_fields_html($field, $form_type) {

        $user_id = get_current_user_id();

        $value = $field->default_value;
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
            } elseif ($field->htmlvar_name == 'uwp_account_first_name') {
                $value = $user_data->first_name;
            } elseif ($field->htmlvar_name == 'uwp_account_last_name') {
                $value = $user_data->last_name;
            } elseif ($field->htmlvar_name == 'uwp_account_bio') {
                $value = $user_data->description;
            } else {
                $value = uwp_get_usermeta($user_id, $field->htmlvar_name, false);
                if ($value != '0' && !$value) {
                    $value = $field->default_value;
                }
            }


        }

        if (empty($value)) {
            $value = "";
        }

        $html = apply_filters("uwp_form_input_html_{$field->field_type}", "", $field, $value, $form_type);

        if (empty($html)) {
            ?>
            <input name="<?php echo $field->htmlvar_name; ?>"
                   class="<?php echo $field->css_class; ?>"
                   placeholder="<?php echo $field->site_title; ?>"
                   title="<?php echo $field->site_title; ?>"
                <?php if ($field->is_required == 1) { echo 'required="required"'; } ?>
                   type="<?php echo $field->field_type; ?>"
                   value="<?php echo $value; ?>">
            <?php
        } else {
            echo $html;
        }
    }

    public function uwp_author_page_content($content) {
        if (is_author()) {
            return do_shortcode('[uwp_profile]');
        } else {
            return $content;
        }

    }
    
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

        $register_slug = $this->uwp_get_page_slug('register_page');
        $login_slug = $this->uwp_get_page_slug('login_page');
        $account_slug = $this->uwp_get_page_slug('account_page');
        $forgot_slug = $this->uwp_get_page_slug('forgot_page');
        $logout_slug = "logout";

        $register_class = "users-wp-{$register_slug}-nav";
        $login_class = "users-wp-{$login_slug}-nav";
        $account_class = "users-wp-{$account_slug}-nav";
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

        return $menu_item;

    }

    public function uwp_get_page_slug($page_type = 'register_page') {
        $page_id = uwp_get_option($page_type, 0);
        if ($page_id) {
            $slug = get_post_field( 'post_name', get_post($page_id) );
        } else {
            $slug = false;
        }
        return $slug;

    }

    public function uwp_logout_url( $custom_redirect = null ) {

        $redirect = null;

        if ( !empty( $custom_redirect ) ) {
            $redirect = esc_url( $custom_redirect );
        } else if ( uwp_get_option('logout_redirect_to', false) ) {
            $redirect = esc_url( get_permalink( uwp_get_option('logout_redirect_to', 0) ) );
        }

        return wp_logout_url( apply_filters( 'uwp_logout_url', $redirect, $custom_redirect ) );

    }

    public function uwp_activation_redirect() {

        if (get_option('uwp_activation_redirect', false)) {

            delete_option('uwp_activation_redirect');

            wp_redirect(admin_url('admin.php?page=uwp&tab=main&subtab=info'));

        }

    }

}