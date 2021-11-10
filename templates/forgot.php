<?php do_action('uwp_template_before', 'forgot'); ?>
<div class="uwp-content-wrap">
    <div class="uwp-login">
        <div class="uwp-lf-icon"><i class="fas fa-user fa-fw"></i></div>
        <?php do_action('uwp_template_form_title_before', 'forgot'); ?>
        <h2>
            <?php $form_title = ! empty( $args['form_title'] ) ? esc_attr__( $args['form_title'], 'userswp' ) : __( 'Forgot Password?', 'userswp' );
            echo apply_filters('uwp_template_form_title', $form_title, 'forgot'); ?>
        </h2>
        <?php do_action('uwp_template_display_notices', 'forgot'); ?>
        <form class="uwp-login-form uwp_form" method="post">
            <?php do_action('uwp_template_fields', 'forgot'); ?>
            <input name="uwp_forgot_submit" value="<?php _e( 'Submit', 'userswp' ); ?>" type="submit"><br>
        </form>
        <div class="uwp-footer-link uwp-forgotpsw"><?php _e( 'Already a member?', 'userswp' ); ?> <a rel="nofollow" href="<?php echo uwp_get_login_page_url(); ?>"><?php echo uwp_get_option("login_link_title") ? uwp_get_option("login_link_title") : __( 'Login here', 'userswp' ); ?></a></div>
        <div class="clfx"></div>
        <div class="uwp-footer-link uwp-register-now"><?php _e( 'Not a member?', 'userswp' ); ?> <a rel="nofollow" href="<?php echo uwp_get_register_page_url(); ?>"><?php echo uwp_get_option("register_link_title") ? uwp_get_option("register_link_title") : __( 'Create account', 'userswp' ); ?></a></div>
    </div>
</div>
<?php do_action('uwp_template_after', 'forgot'); ?>