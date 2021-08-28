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
                        'heading'  => __( 'Heads Up!', 'userswp' ),
                        'content'=> __( 'User registration is currently not allowed.', 'userswp' )
                    )
                );
            }
        }
    }
    
    /**
     * Displays noticed based on notice key.
     * 
     * @param string $type
     * @param bool $echo
     *
     * @return string
     */
    public function form_notice_by_key($type = '', $echo = true, $user_id = 0) {
        $key = isset($_REQUEST['uwp_err']) ? sanitize_html_class($_GET['uwp_err']) : $type;
	    $user_id = isset($_REQUEST['user_id']) ? absint($_REQUEST['user_id']) : $user_id;
        $messages = array();
        $notice = $link = '';

        $messages['act_success'] = array(
            'message' => __('Account activated successfully. Please login to continue.', 'userswp'),
            'type' => 'success',
        );

	    if(isset($user_id) && !empty($user_id)){
		    $resend_link = uwp_get_login_page_url();
		    $resend_link = add_query_arg(
			    array(
				    'user_id' => $user_id,
				    'action'  => 'uwp_resend',
				    '_nonce'  => wp_create_nonce('uwp_resend'),
			    ),
			    $resend_link
		    );

		    $link = "<a href='".esc_url_raw($resend_link)."' >".__('Resend', 'userswp')."</a>";
	    }

        $messages['act_pending'] = array(
            'message' => __('Your account is not activated yet. Please check your email for activation email.', 'userswp').$link,
            'type' => 'error',
        );
        $messages['act_error'] = array(
            'message' => __('Invalid activation key or account.', 'userswp'),
            'type' => 'error',
        );
        $messages['act_wrong'] = array(
            'message' => __('Invalid activation key or account.', 'userswp'),
            'type' => 'error',
        );

        $messages = apply_filters('uwp_form_error_messages', $messages);

        if (!empty($key) && isset($messages[$key])) {
            $value = $messages[$key];
            $message = $value['message'];
            $type = $value['type'];
	        $notice = aui()->alert(array(
			        'type'=> $type,
			        'content'=> $message
		        )
	        );

        }

        if($notice && $echo){
            echo $notice;
        }else{
            return $notice;
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
	 * Displays admin side notice to try Bootstrap layout
	 *
	 * @since       2.0.0
	 * @package     userswp
	 *
	 * @return      void
	 */
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

    public static function yoast_user_archives_disabled(){
	    if( !current_user_can( 'manage_options' ) ) {
            return;
	    }

        if( defined( 'WPSEO_VERSION' ) && version_compare( WPSEO_VERSION, '7.0', '>=' ) && class_exists('WPSEO_Options') && WPSEO_Options::get( 'disable-author', false ) ){
            $settings_link = admin_url("admin.php?page=wpseo_titles#top#archives");

	        $profile_page_id = uwp_get_page_id( 'profile_page' );
	        if ($profile_page_id > 0 && ( $page_object = get_post( $profile_page_id ) )) {
		        if ('page' === $page_object->post_type && in_array($page_object->post_status, array('publish'))) {
			        ?>
                    <div class="notice notice-error">
                        <p><strong>UsersWP - </strong><?php _e( 'Yoast SEO has disabled user profiles, please enable them.', 'userswp' ); ?>
                            <a href="<?php echo esc_url_raw( $settings_link );?>" class="button button-primary"><?php _e( 'View Settings', 'userswp' ); ?></a>
                        </p>
                    </div>
			        <?php
		        }
	        }
        }

    }
    
}