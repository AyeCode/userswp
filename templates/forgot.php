<?php do_action('uwp_template_before', 'forgot'); ?>
<div class="uwp-content-wrap">
    <div class="uwp-content-wrap">
        <div class="uwp-login">
            <div class="uwp-lf-icon"><i class="fa fa-user fa-fw"></i></div>
            <?php do_action('uwp_template_form_title_before', 'forgot'); ?>
            <h2><?php echo __( 'Forgot Password?', 'users-wp' ); ?></h2>
            <?php do_action('uwp_template_form_title_after', 'forgot'); ?>
            <form class="uwp-login-form" method="post">
                <?php do_action('uwp_template_fields', 'forgot'); ?>
                <input type="hidden" name="uwp_forgot_nonce" value="<?php echo wp_create_nonce( 'uwp-forgot-nonce' ); ?>" />
                <input name="uwp_forgot_submit" value="<?php echo __( 'Submit', 'users-wp' ); ?>" type="submit"><br>
            </form>
            <div class="uwp-forgotpsw"><a href="<?php echo uwp_get_page_link('login'); ?>"><?php echo __( 'Login?', 'users-wp' ); ?></a></div>
            <div class="clfx"></div>
            <div class="uwp-register-now"><?php echo __( 'Not a Member?', 'users-wp' ); ?> <a rel="nofollow" href="<?php echo uwp_get_page_link('register'); ?>"><?php echo __( 'Create Account', 'users-wp' ); ?></a></div>
        </div>
    </div>
</div>
<?php do_action('uwp_template_after', 'forgot'); ?>