<?php do_action('uwp_template_before', 'reset'); ?>
    <div class="uwp-content-wrap">
        <div class="uwp-login">
            <div class="uwp-lf-icon"><i class="fas fa-key fa-fw"></i></div>
            <?php do_action('uwp_template_form_title_before', 'reset'); ?>
            <h2><?php echo apply_filters('uwp_template_form_title', get_the_title(), 'reset'); ?></h2>
            <?php do_action('uwp_template_display_notices', 'reset'); ?>
            <?php if (isset($_GET['key']) && isset($_GET['login'])) { ?>
                <form class="uwp-login-form uwp_form" method="post">
                    <?php do_action('uwp_template_fields', 'reset'); ?>
                    <input name="uwp_reset_submit" value="<?php echo __( 'Submit', 'userswp' ); ?>" type="submit"><br>
                </form>
            <?php } else {
                echo sprintf(__('You can not access this page directly. Follow the password reset link you received in your email. To request new password reset link <a href="%s">visit here</a>.', 'userswp'), uwp_get_page_link('forgot'));
            } ?>

            <div class="uwp-footer-link uwp-resetpsw"><?php echo __( 'Already a member?', 'userswp' ); ?><a rel="nofollow" href="<?php echo uwp_get_login_page_url(); ?>"><?php echo __( 'Login here', 'userswp' ); ?></a></div>
            <div class="clfx"></div>
            <div class="uwp-footer-link uwp-register-now"><?php echo __( 'Not a member?', 'userswp' ); ?> <a rel="nofollow" href="<?php echo uwp_get_register_page_url(); ?>"><?php echo __( 'Create account', 'userswp' ); ?></a></div>
        </div>
    </div>
<?php do_action('uwp_template_after', 'reset'); ?>