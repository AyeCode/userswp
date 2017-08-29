<?php
/**
 * UsersWP Notice display functions.
 *
 * All UsersWP notice display related functions can be found here.
 *
 * @since      1.0.0
 * @author     GeoDirectory Team <info@wpgeodirectory.com>
 */
class UsersWP_Notices {
    
    /**
     * Wrap notice with a div.
     *
     * @since       1.0.0
     * @package     userswp
     * @return      string      Html string.
     */
    function wrap_notice($message, $type) {
        $output = '<div class="uwp-alert-'.$type.' text-center">';
        $output .= $message;
        $output .= '</div>';
        return $output;

    }

    /**
     *  Displays notices when registration disabled.
     *
     * @since       1.0.0
     * @package     userswp
     * @return      void
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
     * Displays noticed based on notice key. 
     *
     * @since       1.0.0
     * @package     userswp
     * @return      void
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

    public function show_admin_notices() {
        settings_errors( 'uwp-notices' );
    }

    /**
     * Displays UsersWP admin notices
     *
     * @since       1.0.0
     * @package     userswp
     *
     * @return      void
     */
    function uwp_admin_notices() {
        $errors = get_option( 'uwp_admin_notices' );

        if ( ! empty( $errors ) ) {

            echo '<div id="uwp_admin_errors" class="notice-error notice is-dismissible">';

            echo '<p>' . $errors . '</p>';

            echo '</div>';

            // Clear
            delete_option( 'uwp_admin_notices' );
        }
    }
    
}