<?php do_action('uwp_template_before', 'login'); ?>
    <div class="uwp-content-wrap">
        <div class="uwp-login" <?php if(!empty($uwp_login_widget_args['form_padding'])){echo "style='padding:".absint($uwp_login_widget_args['form_padding'])."px'";}?>>
            <div class="uwp-lf-icon"><i class="fas fa-user fa-fw"></i></div>
            <?php do_action('uwp_template_form_title_before', 'login'); ?>
            <h2><?php
                $form_title = ! empty( $args['form_title'] ) || ( isset($args['form_title']) && $args['form_title']=='0' ) ? esc_attr__( $args['form_title'], 'userswp' ) : __( 'Login', 'userswp' );
                echo apply_filters('uwp_template_form_title',  $form_title, 'login');
                ?></h2>
            <?php do_action('uwp_template_display_notices', 'login'); ?>
            <form class="uwp-login-form uwp_form" method="post">
                <?php do_action('uwp_template_fields', 'login', $args); ?>
                <div class="uwp-remember-me">
                    <label style="display: inline-block;" for="remember_me<?php if(wp_doing_ajax()){echo "_ajax";}?>">
                        <input name="remember_me" id="remember_me<?php if(wp_doing_ajax()){echo "_ajax";}?>" value="forever" type="checkbox">
                        <?php _e( 'Remember Me', 'userswp' ); ?>
                    </label>
                </div>
                <input type="submit" name="uwp_login_submit" value="<?php _e( 'Login', 'userswp' ); ?>">
            </form>
            <div class="uwp-login-links">
                <div class="uwp-footer-link uwp-register-now"><?php _e( 'Not a member?', 'userswp' ); ?> <a rel="nofollow" href="<?php echo uwp_get_register_page_url(); ?>"><?php echo uwp_get_option("register_link_title") ? uwp_get_option("register_link_title") : __( 'Create account', 'userswp' ); ?></a></div>
                <div class="uwp-footer-link uwp-forgotpsw"><a rel="nofollow" href="<?php echo uwp_get_forgot_page_url(); ?>"><?php echo uwp_get_option("forgot_link_title") ? uwp_get_option("forgot_link_title") : __( 'Forgot password?', 'userswp' ); ?></a></div>
            </div>
	        <?php do_action('uwp_social_fields', 'login'); ?>
        </div>
    </div>
<?php do_action('uwp_template_after', 'login'); ?>