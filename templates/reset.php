<?php do_action('uwp_template_before', 'reset'); ?>
    <div class="uwp-content-wrap">
        <div class="uwp-login">
            <div class="uwp-lf-icon"><i class="fa fa-key fa-fw"></i></div>
            <?php do_action('uwp_template_form_title_before', 'reset'); ?>
            <h2><?php echo __( 'Reset Password', 'userswp' ); ?></h2>
            <?php do_action('uwp_template_display_notices', 'reset'); ?>
            <?php if (isset($_GET['key']) && isset($_GET['login'])) { ?>
                <form class="uwp-login-form uwp_form" method="post">
                    <?php do_action('uwp_template_fields', 'reset'); ?>
                    <input type="hidden" name="uwp_reset_username" value="<?php echo sanitize_text_field($_GET['login']); ?>" />
                    <input type="hidden" name="uwp_reset_key" value="<?php echo sanitize_text_field($_GET['key']); ?>" />
                    <input type="hidden" name="uwp_reset_nonce" value="<?php echo wp_create_nonce( 'uwp-reset-nonce' ); ?>" />
                    <input name="uwp_reset_submit" value="<?php echo __( 'Submit', 'userswp' ); ?>" type="submit"><br>
                </form>
            <?php } else {
                echo sprintf(__('You cannot access this page directly. Follow the password reset link you received in your email. To request new password reset link <a href="%s">visit here</a>.', 'userswp'), uwp_get_page_link('forgot'));
            } ?>

            <div class="uwp-resetpsw"><a href="<?php echo uwp_get_page_link('login'); ?>"><?php echo __( 'Login?', 'userswp' ); ?></a> <a style="float: right" href="<?php echo uwp_get_page_link('forgot'); ?>"><?php echo __( 'Request reset link', 'userswp' ); ?></a></div>
            <div class="clfx"></div>
            <div class="uwp-register-now"><?php echo __( 'Not a Member?', 'userswp' ); ?> <a rel="nofollow" href="<?php echo uwp_get_page_link('register'); ?>"><?php echo __( 'Create Account', 'userswp' ); ?></a></div>
        </div>
    </div>
<?php do_action('uwp_template_after', 'reset'); ?>