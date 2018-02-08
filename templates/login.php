<?php do_action('uwp_template_before', 'login'); ?>
    <div class="uwp-content-wrap">
        <div class="uwp-login">
            <div class="uwp-lf-icon"><i class="fa fa-user fa-fw"></i></div>
            <?php do_action('uwp_template_form_title_before', 'login'); ?>
            <h2><?php echo apply_filters('uwp_template_form_title', get_the_title(), 'login'); ?></h2>
            <?php do_action('uwp_template_display_notices', 'login'); ?>
            <form class="uwp-login-form uwp_form" method="post">
                <?php do_action('uwp_template_fields', 'login'); ?>
                <input type="hidden" name="uwp_login_nonce" value="<?php echo wp_create_nonce( 'uwp-login-nonce' ); ?>" />
                <?php
                if (is_uwp_login_page()) {
                    if (isset($_GET['redirect_to'])) {
                        $redirect_to = esc_url($_GET['redirect_to']);
                    } else if($referer = wp_get_referer()){
                        $redirect_to = $referer;
                    }
                } else {
                    $redirect_to = uwp_current_page_url();
                }
                ?>
                <input type="hidden" name="redirect_to" value="<?php echo $redirect_to; ?>"/>
                <?php do_action('uwp_social_fields', 'login'); ?>
                <div class="uwp-remember-me">
                    <label style="display: inline-block;" for="remember_me">
                        <input name="remember_me" id="remember_me" value="forever" type="checkbox">
                        <?php _e( 'Remember Me', 'userswp' ); ?>
                    </label>
                </div>
                <input type="submit" name="uwp_login_submit" value="<?php _e( 'Login', 'userswp' ); ?>">
            </form>
            <div class="uwp-forgotpsw"><a href="<?php echo uwp_get_page_link('forgot'); ?>"><?php _e( 'Forgot password?', 'userswp' ); ?></a></div>
            <div class="clfx"></div>
            <div class="uwp-register-now"><?php _e( 'Not a member?', 'userswp' ); ?> <a rel="nofollow" href="<?php echo uwp_get_page_link('register'); ?>"><?php _e( 'Create account', 'userswp' ); ?></a></div>
        </div>
    </div>
<?php do_action('uwp_template_after', 'login'); ?>