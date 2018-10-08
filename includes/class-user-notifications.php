<?php
/**
 * User Notifications related functions
 *
 * All UsersWP related mails are sent via this class.
 *
 * @since      1.0.20
 * @author     GeoDirectory Team <info@wpgeodirectory.com>
 */
class UsersWP_Notifications {

    public function __construct() {

    }

    public function uwp_user_notifications_form_front($type){
        if ($type == 'notifications') {
            $user_id = get_current_user_id();
            echo '<div class="uwp-account-form uwp_wc_form">';
            ?>
            <div class="uwp-profile-extra">
                <div class="uwp-profile-extra-div form-table">
                    <form class="uwp-account-form uwp_form" method="post">
                        <?php
                            $value = get_user_meta($user_id, 'uwp_mute_notifications', true);
                            $notifications_types = array();

                            if( 1 == uwp_get_option('enable_account_update_notification' )){
                                $notifications_types = array(
                                    'account_update' => __('Disable account update notification.', 'userswp'),
                                );
                            }
                            $notifications_types = apply_filters('uwp_mute_notification_types', $notifications_types, $user_id);

                            if($notifications_types) {
                                foreach ($notifications_types as $id => $text) {
                                    if(isset($value) && !empty($value) && isset($value[$id])){
                                        $checked = $value[$id];
                                    } else {
                                        $checked = 0;
                                    }
                                    ?>
                                    <div class="uwp-profile-extra-wrap uwp_mute_notification_items">
                                        <div id="uwp_mute_<?php echo $id; ?>"
                                             class="uwp_mute_notification_item uwp_mute_<?php echo $id; ?>">
                                            <input name="uwp_mute_notifications[<?php echo $id; ?>]"
                                                   class="" <?php checked($checked, "1", true); ?> type="checkbox"
                                                   value="1"><?php echo $text; ?>
                                        </div>
                                    </div>
                                <?php }
                            }?>
                        <input type="hidden" name="uwp_notification_nonce" value="<?php echo wp_create_nonce( 'uwp-notification-nonce' ); ?>" />
                        <input name="uwp_notification_submit" value="<?php echo __( 'Submit', 'userswp' ); ?>" type="submit">
                    </form>
                </div>
            </div>
            <?php
            echo '</div>';
        }
    }

    /**
     * Handles the notifications form submission.
     *
     * @since       1.0.0
     * @package     userswp
     *
     * @return      void
     */
    public function uwp_notification_submit_handler() {
        if (isset($_POST['uwp_notification_submit'])) {
            if( ! isset( $_POST['uwp_notification_nonce'] ) || ! wp_verify_nonce( $_POST['uwp_notification_nonce'], 'uwp-notification-nonce' ) ) {
                return;
            }

            $user_id = get_current_user_id();
            if (isset($_POST['uwp_mute_notifications']) && !empty($_POST['uwp_mute_notifications'])) {
                update_user_meta($user_id, 'uwp_mute_notifications', $_POST['uwp_mute_notifications']);
            }

            do_action('uwp_handle_notification_submit', $user_id);

        }
    }


}