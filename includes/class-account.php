<?php
/**
 * User account related functions
 *
 * @since      1.2.1.2
 * @author     GeoDirectory Team <info@wpgeodirectory.com>
 */
class UsersWP_Account {


    public function __construct() {
	    add_action( 'uwp_account_form_display', array($this, 'display_form'), 10, 1 );
	    add_action('init', array($this, 'submit_handler'));
    }

	/**
	 * Displays the account form
     *
     * @since       1.0.0
	 *
	 * @param array $type Type of the form
     *
	 */
    public function display_form($type){
	    if ($type == 'account') {
		    $design_style = uwp_get_option("design_style","bootstrap");
		    $bs_btn_class = $design_style ? "btn btn-primary btn-block text-uppercase" : "";
		    ?>
            <form class="uwp-account-form uwp_form mt-3" method="post" enctype="multipart/form-data">
			    <?php do_action('uwp_template_fields', 'account'); ?>
                <input type="hidden" name="uwp_account_nonce" value="<?php echo esc_attr( wp_create_nonce( 'uwp-account-nonce' ) ); ?>" />
                <input name="uwp_account_submit" class="<?php echo esc_attr( $bs_btn_class ); ?>" value="<?php esc_attr_e( 'Update Account', 'userswp' ); ?>" type="submit">
            </form>
	    <?php }

	    if ($type == 'change-password') {
		    $design_style = uwp_get_option("design_style","bootstrap");
		    $bs_btn_class = $design_style ? "btn btn-primary btn-block text-uppercase" : "";
		    ?>
            <form class="uwp-account-form uwp_form mt-3" method="post" enctype="multipart/form-data">
			    <?php do_action('uwp_template_fields', 'change'); ?>
                <input name="uwp_change_submit" class="<?php echo esc_attr( $bs_btn_class ); ?>" value="<?php esc_attr_e( 'Change Password', 'userswp' ); ?>" type="submit">
            </form>
		<?php
		    uwp_password_strength_inline_js();
	    }

	    if ($type == 'delete-account') {
	        if(1 == uwp_get_option('disable_account_delete') || current_user_can('administrator')){
                return;
            }
		    ?>
            <form class="uwp-account-form uwp_form mt-3" method="post" enctype="multipart/form-data">

                <?php
                $design_style = uwp_get_option("design_style","bootstrap");
                $bs_btn_class = $design_style ? "btn btn-primary btn-block text-uppercase" : "";

                do_action('uwp_template_fields', 'delete-account');

                $fields = (object) array(
                    'htmlvar_name' => 'password',
                    'field_type' => 'password',
                    'data_type' => 'VARCHAR',
                    'default_value' => '',
                    'is_required' => 1,
                    'help_text' => '',
                    'form_label' => __('Password', 'userswp'),
                    'site_title' => __('Password', 'userswp'),
                );

                $obj = new UsersWP_Templates();
                $obj->template_fields_html($fields, 'delete-account');

                ?>
                <input type="hidden" name="uwp_delete_account_nonce" value="<?php echo esc_attr( wp_create_nonce( 'uwp-delete-account-nonce' ) ); ?>" />
                <input name="uwp_delete_account_submit" class="<?php echo esc_attr( $bs_btn_class ); ?>" value="<?php esc_attr_e( 'Delete Account', 'userswp' ); ?>" type="submit">
            </form>
	    <?php }

	    if($type == 'wp2fa' && class_exists('\WP2FA\WP2FA')){
		    if(1 == uwp_get_option('disable_wp_2fa')){
			    return;
		    }
            echo do_shortcode( '[wp-2fa-setup-form]' );
        }
    }

    /**
     * Handles the delete account form submission.
     *
     * @since       1.2.1.2
     * @package     userswp
     *
     * @return      void
     */
    public function submit_handler() {
        if (isset($_POST['uwp_delete_account_submit'])) {
            if( ! isset( $_POST['uwp_delete_account_nonce'] ) || ! wp_verify_nonce( $_POST['uwp_delete_account_nonce'], 'uwp-delete-account-nonce' ) ) {
                return;
            }

            global $uwp_notices;

            $password = isset($_POST['password']) ? sanitize_text_field($_POST['password']) : "";
	        $user_id = get_current_user_id();
            $user = get_user_by( 'id', get_current_user_id() );

	        do_action('uwp_before_delete_account', $user_id);

            //check password
            if ( !wp_check_password( $password, $user->data->user_pass, $user->ID) ) {
	            $message = aui()->alert(array(
			            'type'=>'error',
			            'content'=> __( '<strong>Error</strong>: Incorrect password.', 'userswp' )
		            )
	            );

	            $uwp_notices[] = array('account' => $message );
	            return;
            }

	        $errors = apply_filters('uwp_delete_account_validate', $user);

            $error_code = $errors->get_error_code();
            if (!empty($error_code)) {
                $message = aui()->alert(array(
                        'type'=>'error',
                        'content'=> $errors->get_error_message()
                    )
                );

                $uwp_notices[] = array('account' => $message );
                return;
            }

	        $ms_delete = apply_filters('uwp_delete_delete_from_network', true);
            $num_blogs_of_user = is_multisite() ? count( get_blogs_of_user( $user_id ) ) : 1;
            $delete_from_network = ( is_multisite() && ( $ms_delete == true || $num_blogs_of_user == 1 ) ) ? true : false;

	        include_once( ABSPATH . 'wp-admin/includes/user.php' );

	        if ( is_multisite() ) {

		        include_once( ABSPATH . WPINC . '/ms-functions.php' );
		        include_once( ABSPATH . 'wp-admin/includes/ms.php' );

	        }

	        $message = '<p><b>' . __('Deleted user information :', 'userswp') . '</b></p>
	            <p>' . __('First Name:', 'userswp') . ' ' . esc_attr( $user->first_name ) . '</p>
                <p>' . __('Last Name:', 'userswp') . ' ' . esc_attr( $user->last_name ) . '</p>
                <p>' . __('Username:', 'userswp') . ' ' . esc_attr( $user->user_login ). '</p>
                <p>' . __('Email:', 'userswp') . ' ' .  sanitize_email( $user->user_email ) . '</p>';

	        $message = apply_filters('uwp_account_delete_mail_message', $message, $user_id);

	        $user_email = sanitize_email( $user->user_email );
	        $user_name = !empty($user->display_name) ? esc_attr( $user->display_name ) :'';

            // Delete user
            if ( $delete_from_network ) {

	            // Global super-administrators are protected, and cannot be deleted.
	            $_super_admins = get_super_admins();
	            if ( in_array( $user->user_login, $_super_admins, true ) ) {
		            $message = aui()->alert(array(
				            'type'=>'error',
				            'content'=> __( '<strong>Error</strong>: Super Administrators cannot be deleted.', 'userswp' )
			            )
		            );

		            $uwp_notices[] = array('account' => $message );
		            return;
	            }

	            $deleted = wpmu_delete_user( $user_id );

            } else {

                $deleted = wp_delete_user( $user_id );

            }

            // notify on successful deletion.
            if($deleted){

	            $email_vars = array();
	            $email_vars['login_details'] = $message;
	            $email_vars['user_name'] = $user_name;

	            UsersWP_Mails::send($user_email, 'account_delete', $email_vars);

	            UsersWP_Mails::send(get_bloginfo('admin_email'), 'account_delete', $email_vars, true);
            }


	        do_action('uwp_after_delete_account', $user_id, $deleted);

            // Logout
            wp_logout();

            // Redirect after deletion
            $redirect_page = home_url();
            wp_safe_redirect($redirect_page);
            exit();
        }
    }
}

new UsersWP_Account();