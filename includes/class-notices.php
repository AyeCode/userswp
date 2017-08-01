<?php
/**
 * UsersWP Notice display functions.
 *
 * All UsersWP notice display related functions can be found here.
 *
 * @since      1.0.0
 * @author     GeoDirectory Team <info@wpgeodirectory.com>
 */
class Users_WP_Notices {

    /**
     *
     *
     * @since   1.0.0
     * @package UsersWP
     * @return void
     */
    public function __construct() {

        add_action('uwp_template_display_notices', array($this, 'display_registration_disabled_notice'));
        add_action('uwp_template_display_notices', array($this, 'form_notice_by_key'));

    }

    /**
     *
     *
     * @since   1.0.0
     * @package UsersWP
     * @return void
     */
    function wrap_notice($message, $type) {
        $output = '<div class="uwp-alert-'.$type.' text-center">';
        $output .= $message;
        $output .= '</div>';
        return $output;

    }

    /**
     *
     *
     * @since   1.0.0
     * @package UsersWP
     * @return void
     */
    function display_registration_disabled_notice($type) {
        if ($type == 'register') {
            if (!get_option('users_can_register')) {
                $message = __('<strong>Heads Up!</strong><br/> User registration is currently not allowed.', 'userswp');
                echo '<div class="uwp-alert-error text-center">';
                echo $message;
                echo '</div>';
            }
        }
    }

    /**
     *
     *
     * @since   1.0.0
     * @package UsersWP
     * @return void
     */
    public function form_notice_by_key() {
        $messages = array();
        $messages['act_success'] = array(
            'message' => __('Account activated successfully. Please login to continue.', 'userswp'),
            'type' => 'uwp-alert-success',
        );
        $messages['act_pending'] = array(
            'message' => __('Your account is not activated yet. Please check your email for activation email.', 'userswp'),
            'type' => 'uwp-alert-error',
        );
        $messages['act_error'] = array(
            'message' => __('Invalid activation key or account.', 'userswp'),
            'type' => 'uwp-alert-error',
        );
        $messages['act_wrong'] = array(
            'message' => __('Something went wrong.', 'userswp'),
            'type' => 'uwp-alert-error',
        );
        $messages = apply_filters('uwp_form_error_messages', $messages);
        if (isset($_GET['uwp_err'])) {
            $key = strip_tags(esc_sql($_GET['uwp_err']));
            if (isset($messages[$key])) {
                $value = $messages[$key];
                $message = $value['message'];
                $type = $value['type'];
                echo '<div class="'.$type.' text-center">';
                echo $message;
                echo '</div>';
            }
        }
    }

}