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

        $plugin_path = dirname( dirname( __FILE__ ) );

        switch ($template) {
            case 'register':
                $template = locate_template(array("userswp/register.php"));
                if (!$template) {
                    $template = $plugin_path . '/templates/register.php';
                }
                $template = apply_filters('uwp_template_register', $template);
                return $template;
                break;

            case 'login':
                $template = locate_template(array("userswp/login.php"));
                if (!$template) {
                    $template = $plugin_path . '/templates/login.php';
                }
                $template = apply_filters('uwp_template_login', $template);
                return $template;
                break;

            case 'forgot':
                $template = locate_template(array("userswp/forgot.php"));
                if (!$template) {
                    $template = $plugin_path . '/templates/forgot.php';
                }
                $template = apply_filters('uwp_template_forgot', $template);
                return $template;
                break;

            case 'account':
                $template = locate_template(array("userswp/account.php"));
                if (!$template) {
                    $template = $plugin_path . '/templates/account.php';
                }
                $template = apply_filters('uwp_template_account', $template);
                return $template;
                break;

            case 'profile':
                $template = locate_template(array("userswp/profile.php"));
                if (!$template) {
                    $template = $plugin_path . '/templates/profile.php';
                }
                $template = apply_filters('uwp_template_profile', $template);
                return $template;
                break;

            case 'users':
                $template = locate_template(array("userswp/users.php"));
                if (!$template) {
                    $template = $plugin_path . '/templates/users.php';
                }
                $template = apply_filters('uwp_template_users', $template);
                return $template;
                break;
        }

        return false;
    }

    public function access_checks() {
        global $wp_query;

        if (!is_page()) {
            return false;
        }

        $current_page_id = $wp_query->query_vars['page_id'];
        $condition = "";

        $register_page = uwp_get_option('register_page', false);
        if ( $register_page && ((int) $register_page ==  $current_page_id ) ) {
            $condition = "non_logged_in";
        }

        $login_page = uwp_get_option('login_page', false);
        if ( $login_page && ((int) $login_page ==  $current_page_id ) ) {
            $condition = "non_logged_in";
        }

        $forgot_pass_page = uwp_get_option('forgot_pass_page', false);
        if ( $forgot_pass_page && ((int) $forgot_pass_page ==  $current_page_id ) ) {
            $condition = "non_logged_in";
        }

        $account_page = uwp_get_option('account_page', false);
        if ( $account_page && ((int) $account_page ==  $current_page_id ) ) {
            $condition = "logged_in";
        }

        if ($condition == "non_logged_in") {
            if (is_user_logged_in()) {
                wp_redirect(home_url('/'));
                exit();
            }
        } elseif ($condition == "logged_in") {
            if (!is_user_logged_in()) {
                wp_redirect(get_permalink($login_page));
                exit();
            }
        } else {
            return false;
        }

        return false;
    }

    public function uwp_template_fields($form_type) {

        global $wpdb;
        $table_name = $wpdb->prefix . 'uwp_custom_fields';

        $fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $table_name . " WHERE form_type = %s AND is_active = '1' ORDER BY sort_order ASC", array($form_type)));

        if (!empty($fields)) {
            foreach ($fields as $field) {
                $this->uwp_template_fields_html($field, $form_type);
            }
        }
    }

    public function uwp_template_fields_html($field, $form_type) {

        $value = $field->default_value;
        if ($form_type == 'account') {
            $user_id = get_current_user_id();
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
            } else {
                $value = uwp_get_usermeta($user_id, $field->htmlvar_name, '');
            }


        }



        if (empty($value)) {
            $value = "";
        }

        switch($field->field_type) {
            default:
                ?>
                <input name="<?php echo $field->htmlvar_name; ?>"
                       class="<?php echo $field->css_class; ?>"
                       placeholder="<?php echo $field->site_title; ?>"
                    <?php if ($field->is_required == 1) { echo 'required="required"'; } ?>
                       type="<?php echo $field->field_type; ?>"
                       value="<?php echo $value; ?>">
                <?php
        }
    }

    public function uwp_author_page_content($content) {
        if (is_author()) {
            return do_shortcode('[uwp_profile]');
        } else {
            return $content;
        }

    }

}