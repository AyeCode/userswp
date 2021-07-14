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

	/**
	 * Displays the notification form
     *
     * @since       1.0.0
	 *
	 * @param array $type Type of the form
     *
	 */
    public function user_notifications_form_front($type){
        if ($type == 'notifications') {
            $user_id = get_current_user_id();
            $design_style = uwp_get_option("design_style","bootstrap");
            $bs_form_group = $design_style ? "form-group form-check" : "";
            $bs_form_control = $design_style ? "form-check-input" : "";
            $bs_sr_only = $design_style ? "form-check-label" : "";
            $bs_btn_class = $design_style ? "btn btn-primary btn-block text-uppercase" : "";
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
                                    <div class="uwp-profile-extra-wrap uwp_mute_notification_items <?php echo $bs_form_group; ?>">
                                        <div id="uwp_mute_<?php echo $id; ?>"
                                             class="uwp_mute_notification_item uwp_mute_<?php echo $id; ?>">
                                            <?php if(!empty($design_style)){ ?>
                                            <label class="<?php echo $bs_sr_only; ?>">
                                                <?php } ?>
                                            <input name="uwp_mute_notifications[<?php echo $id; ?>]"
                                                   class="<?php echo $bs_form_control; ?>" <?php checked($checked, "1", true); ?> type="checkbox"
                                                   value="1"><?php echo $text; ?>
                                            <?php if(!empty($design_style)){ ?>
                                                </label>
                                            <?php } ?>
                                        </div>
                                    </div>
                                <?php }
                                ?>
                                <input type="hidden" name="uwp_notification_nonce" value="<?php echo wp_create_nonce( 'uwp-notification-nonce' ); ?>" />
                                <input name="uwp_notification_submit" class="<?php echo $bs_btn_class; ?>" value="<?php echo __( 'Submit', 'userswp' ); ?>" type="submit">
                                <?php
                            } else {
	                            echo aui()->alert(array(
			                            'type'=>'info',
			                            'content'=> __( 'You will see the options to disable the active notifications for UsersWP and it\'s add ons.', 'userswp' )
		                            )
	                            );
                            }?>
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
    public function notification_submit_handler() {
        if (isset($_POST['uwp_notification_submit'])) {
            if( ! isset( $_POST['uwp_notification_nonce'] ) || ! wp_verify_nonce( $_POST['uwp_notification_nonce'], 'uwp-notification-nonce' ) ) {
                return;
            }

            global $uwp_notices;
            $user_id = get_current_user_id();
            if (isset($_POST['uwp_mute_notifications']) && !empty($_POST['uwp_mute_notifications'])) {
                update_user_meta($user_id, 'uwp_mute_notifications', array_map('uwp_clean', (array) $_POST['uwp_mute_notifications']));
            }

	        $message = apply_filters('uwp_notification_update_success_message', __('Notification settings updated successfully.', 'userswp'));
	        $message = aui()->alert(array(
			        'type'=>'success',
			        'content'=> $message
		        )
	        );
	        $uwp_notices[] = array('account' => $message);

            do_action('uwp_handle_notification_submit', $user_id);

        }
    }
}