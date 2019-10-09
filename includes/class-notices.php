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

        $types = array(
            'error' => 'danger'
        );

        $alert_type = isset($types[$type]) ? $types[$type] : $type;

        $output = '<div class="uwp-alert-'.esc_attr($type).' text-center alert alert-'.esc_attr($alert_type).'">';
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
                echo aui()->alert(array(
                        'class' => 'text-center',
                        'type'=>'danger',
                        'heading'  => 'Heads Up!',
                        'content'=> __( 'User registration is currently not allowed.', 'userswp' )
                    )
                );
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

        include_once dirname( __FILE__ ) . '/class-uwp-background-updater.php';
        $updater = new UsersWP_Background_Updater();
        if ( $updater->is_updating() || ! empty( $_GET['force_sync_data'] ) ) {
            ?>
            <div id="message" class="updated notice notice-alt uwp-message">
                <p><strong><?php _e( 'UsersWP data sync', 'userswp' ); ?></strong> &#8211; <?php _e( 'Users data sync is running in the background.', 'userswp' ); ?> <a href="<?php echo esc_url( add_query_arg( 'force_sync_data', 'true', admin_url( 'admin.php?page=userswp' ) ) ); ?>"><?php _e( 'Taking a while? Click here to run it now.', 'userswp' ); ?></a></p>
            </div>
            <?php
        }
    }

    /**
     * Displays UsersWP admin notices
     *
     * @since       1.0.0
     * @package     userswp
     *
     * @return      void
     */
    function admin_notices() {
        $errors = get_option( 'uwp_admin_notices' );

        if ( ! empty( $errors ) ) {

            echo '<div id="uwp_admin_errors" class="notice-error notice is-dismissible">';

            echo '<p>' . $errors . '</p>';

            echo '</div>';

            // Clear
            delete_option( 'uwp_admin_notices' );
        }
    }

    public static function try_bootstrap(){
        $show = get_option("uwp_notice_try_bootstrap");
        if( $show && uwp_get_option('design_style','bootstrap') != 'bootstrap'){
            $settings_link = admin_url("admin.php?page=userswp&tab=general&section=developer&try-bootstrap=true");
            // set the setting on the fly if set to do so
            if(is_admin() && current_user_can( 'manage_options' )  && isset($_REQUEST['page']) && $_REQUEST['page']=='userswp' && !empty($_REQUEST['try-bootstrap']) ){
                uwp_update_option('design_style','bootstrap');
                ?>
                <div class="notice notice-success">
                    <p><strong>UsersWP - </strong><?php _e( 'Congratulations your site is now set to use the new Bootstrap styles!', 'userswp' ); ?></p>
                </div>
                <?php
            }else{
            ?>
            <div class="notice notice-info is-dismissible uwp-notice-try-bootstrap">
                <p><strong>UsersWP - </strong><?php _e( 'Try our exciting new bootstrap styling for a more modern and clean look (switch back at any time).', 'userswp' ); ?>
                    <a href="<?php echo esc_url_raw( $settings_link );?>" class="button button-primary"><?php _e( 'Try Now', 'userswp' ); ?></a>
                </p>
            </div>
                <script>
                    jQuery(function() {
                        setTimeout(function(){
                            jQuery('.uwp-notice-try-bootstrap .notice-dismiss').click(function(){
                                jQuery.post("<?php echo admin_url("admin-ajax.php?action=uwp_notice_clear_try_bootstrap"); ?>", function(data, status){
                                });
                            });
                        }, 300);
                    });
                </script>
            <?php
            }

        }

    }
    
}