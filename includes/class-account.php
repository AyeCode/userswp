<?php
/**
 * User account related functions
 *
 * @since      1.2.1.2
 * @author     GeoDirectory Team <info@wpgeodirectory.com>
 */
class UsersWP_Account {


    public function __construct() {
	    add_action( 'uwp_account_form_display', array( $this, 'display_form' ), 10, 1 );
	    add_action( 'init', array( $this, 'submit_handler' ) );

        add_filter( 'uwp_get_account_deletion_message', array( $this, 'get_deletion_message' ), 10, 2 );
        add_action( 'uwp_send_account_deletion_emails', array( $this, 'send_account_deletion_emails' ), 10, 2 );
    }

	/**
	 * Displays the account form
     *
     * @since       1.0.0
	 *
	 * @param array $type Type of the form
     *
	 */
    public function display_form( $type ) {
	    if ( $type == 'account' ) {
		    $design_style = uwp_get_option( 'design_style', 'bootstrap' );
		    $bs_btn_class = $design_style ? 'btn btn-primary btn-block text-uppercase' : '';
		    ?>
            <form class="uwp-account-form uwp_form mt-3" method="post" enctype="multipart/form-data">
			    <?php do_action( 'uwp_template_fields', 'account' ); ?>
                <input type="hidden" name="uwp_account_nonce" value="<?php echo esc_attr( wp_create_nonce( 'uwp-account-nonce' ) ); ?>" />
                <input name="uwp_account_submit" class="<?php echo esc_attr( $bs_btn_class ); ?>" value="<?php esc_attr_e( 'Update Account', 'userswp' ); ?>" type="submit">
            </form>
	    <?php
        }

	    if ( $type == 'change-password' ) {
		    $design_style = uwp_get_option( 'design_style', 'bootstrap' );
		    $bs_btn_class = $design_style ? 'btn btn-primary btn-block text-uppercase' : '';
		    ?>
            <form class="uwp-account-form uwp_form mt-3" method="post" enctype="multipart/form-data">
			    <?php do_action( 'uwp_template_fields', 'change' ); ?>
                <input name="uwp_change_submit" class="<?php echo esc_attr( $bs_btn_class ); ?>" value="<?php esc_attr_e( 'Change Password', 'userswp' ); ?>" type="submit">
            </form>
		<?php
		    uwp_password_strength_inline_js();
	    }

	    if ( $type == 'delete-account' ) {
	        if ( 1 == uwp_get_option( 'disable_account_delete' ) || current_user_can( 'administrator' ) ) {
                return;
            }
		    ?>
            <form class="uwp-account-form uwp_form mt-3" method="post" enctype="multipart/form-data">

                <?php
                $design_style = uwp_get_option( 'design_style', 'bootstrap' );
                $bs_btn_class = $design_style ? 'btn btn-primary btn-block text-uppercase' : '';

                do_action( 'uwp_template_fields', 'delete-account' );

                $fields = (object) array(
                    'htmlvar_name'  => 'password',
                    'field_type'    => 'password',
                    'data_type'     => 'VARCHAR',
                    'default_value' => '',
                    'is_required'   => 1,
                    'help_text'     => '',
                    'form_label'    => __( 'Password', 'userswp' ),
                    'site_title'    => __( 'Password', 'userswp' ),
                );

                $obj = new UsersWP_Templates();
                $obj->template_fields_html( $fields, 'delete-account' );

                ?>
                <input type="hidden" name="uwp_delete_account_nonce" value="<?php echo esc_attr( wp_create_nonce( 'uwp-delete-account-nonce' ) ); ?>" />
                <input name="uwp_delete_account_submit" class="<?php echo esc_attr( $bs_btn_class ); ?>" value="<?php esc_attr_e( 'Delete Account', 'userswp' ); ?>" type="submit">
            </form>
	    <?php
        }

	    if ( $type == 'wp2fa' && class_exists( '\WP2FA\WP2FA' ) ) {
		    if ( 1 == uwp_get_option( 'disable_wp_2fa' ) ) {
			    return;
		    }
            echo do_shortcode( '[wp-2fa-setup-form]' );
        }
    }

    /**
     * Handles the delete account form submission.
     *
     * @since 1.2.1.2
     * @return void
     */
    public function submit_handler() {
        if ( ! isset( $_POST['uwp_delete_account_submit'] ) ) {
            return;
        }

        if ( ! isset( $_POST['uwp_delete_account_nonce'] ) || ! wp_verify_nonce( $_POST['uwp_delete_account_nonce'], 'uwp-delete-account-nonce' ) ) {
            return;
        }

        global $uwp_notices;

        $password = isset( $_POST['password'] ) ? sanitize_text_field( $_POST['password'] ) : '';
        $user_id  = get_current_user_id();
        $user     = get_user_by( 'id', $user_id );

        do_action( 'uwp_before_delete_account', $user_id );

        if ( ! wp_check_password( $password, $user->data->user_pass, $user->ID ) ) {
            $uwp_notices[] = array(
                'account' => aui()->alert(
                    array(
						'type'    => 'error',
						'content' => __( '<strong>Error</strong>: Incorrect password.', 'userswp' ),
                    )
                ),
            );
            return;
        }

        $errors = apply_filters( 'uwp_delete_account_validate', $user );

        if ( ! empty( $errors->get_error_code() ) ) {
            $uwp_notices[] = array(
                'account' => aui()->alert(
                    array(
						'type'    => 'error',
						'content' => $errors->get_error_message(),
                    )
                ),
            );
            return;
        }

        $ms_delete = apply_filters( 'uwp_delete_delete_from_network', true );
        $num_blogs_of_user = is_multisite() ? count( get_blogs_of_user( $user_id ) ) : 1;
        $delete_from_network = ( is_multisite() && ( $ms_delete == true || $num_blogs_of_user == 1 ) ) ? true : false;

        include_once ABSPATH . 'wp-admin/includes/user.php';

        if ( is_multisite() ) {
            include_once ABSPATH . WPINC . '/ms-functions.php';
            include_once ABSPATH . 'wp-admin/includes/ms.php';
        }

        $message = $this->get_deletion_message( '', $user );

        if ( $delete_from_network ) {
            if ( in_array( $user->user_login, get_super_admins(), true ) ) {
                $uwp_notices[] = array(
                    'account' => aui()->alert(
                        array(
							'type'    => 'error',
							'content' => __( '<strong>Error</strong>: Super Administrators cannot be deleted.', 'userswp' ),
                        )
                    ),
                );
                return;
            }

            $deleted = wpmu_delete_user( $user_id );
        } else {
            $deleted = wp_delete_user( $user_id );
        }

        if ( $deleted ) {
            $this->send_account_deletion_emails( $user, $message );
        }

        do_action( 'uwp_after_delete_account', $user_id, $deleted );

        wp_logout();
        wp_safe_redirect( home_url() );
        exit();
    }

    /**
    * Generate the account deletion message.
    *
    * @since 1.2.28
    * @param string   $message The initial message (empty by default).
    * @param WP_User  $user    The user being deleted.
    * @return string  The formatted deletion message.
    */
    public function get_deletion_message( $message, $user ) {
        $message_parts = array(
            'header'     => '<p><strong>' . esc_html__( 'Deleted user information:', 'userswp' ) . '</strong></p>',
            'first_name' => '<p>' . esc_html__( 'First Name:', 'userswp' ) . ' ' . esc_html( $user->first_name ) . '</p>',
            'last_name'  => '<p>' . esc_html__( 'Last Name:', 'userswp' ) . ' ' . esc_html( $user->last_name ) . '</p>',
            'username'   => '<p>' . esc_html__( 'Username:', 'userswp' ) . ' ' . esc_html( $user->user_login ) . '</p>',
            'email'      => '<p>' . esc_html__( 'Email:', 'userswp' ) . ' ' . esc_html( $user->user_email ) . '</p>',
        );

        /**
         * Filters the account deletion message parts.
         *
         * @since 1.2.28
         * @param array    $message_parts The message parts.
         * @param WP_User $user          The user being deleted.
         */
        $message_parts = apply_filters( 'uwp_account_deletion_message_parts', $message_parts, $user );

        $message .= implode( '', $message_parts );

        /**
         * Filters the final account deletion message.
         *
         * @since 1.2.28
         * @param string   $message The deletion message.
         * @param WP_User $user    The user being deleted.
         */
        return apply_filters( 'uwp_account_deletion_message', $message, $user );
    }

    /**
     * Sends account deletion emails.
     *
     * @since 1.2.28
     * @param WP_User $user The user being deleted.
     * @param string $message The deletion message.
     */
    public function send_account_deletion_emails( $user, $message ) {
        $email_vars = array(
            'login_details' => $message,
            'user_name'     => ! empty( $user->display_name ) ? esc_attr( $user->display_name ) : '',
        );

        do_action( 'uwp_before_account_delete_email', $user, $email_vars );

        UsersWP_Mails::send( $user->user_email, 'account_delete', $email_vars );
        UsersWP_Mails::send( get_bloginfo( 'admin_email' ), 'account_delete', $email_vars, true );

        do_action( 'uwp_after_account_delete_email', $user, $email_vars );
    }
}

new UsersWP_Account();
