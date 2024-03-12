<?php do_action('uwp_template_before', 'reset');
$css_class = ! empty( $args['css_class'] ) ? esc_attr( $args['css_class'] ) : '';
$form_title = ! empty( $args['form_title'] ) ? esc_attr__( $args['form_title'], 'userswp' ) : __( 'Reset Password', 'userswp' );
?>
    <div class="uwp-content-wrap <?php echo esc_attr( $css_class ); ?>">
        <div class="uwp-login">
            <div class="uwp-lf-icon"><i class="fas fa-key fa-fw"></i></div>
            <?php do_action('uwp_template_form_title_before', 'reset'); ?>
            <h2><?php
                    echo apply_filters('uwp_template_form_title', $form_title, 'reset'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                ?>
            </h2>
            <?php do_action('uwp_template_display_notices', 'reset'); ?>
            <?php if (isset($_GET['key']) && isset($_GET['login'])) { ?>
                <form class="uwp-reset-form uwp_form" method="post">
                    <?php do_action('uwp_template_fields', 'reset'); ?>
                    <input name="uwp_reset_submit" value="<?php echo esc_attr__( 'Submit', 'userswp' ); ?>" type="submit"><br>
                </form>
            <?php } else {
                echo sprintf(__('You can not access this page directly. Follow the password reset link you received in your email. To request new password reset link <a href="%s">visit here</a>.', 'userswp'), esc_url( uwp_get_page_link('forgot') )); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            } ?>

            <div class="uwp-footer-link uwp-resetpsw"><?php esc_html_e( 'Already a member?', 'userswp' ); ?><a rel="nofollow" href="<?php echo esc_url( uwp_get_login_page_url() ); ?>"><?php echo uwp_get_option("login_link_title") ? esc_html( uwp_get_option("login_link_title") ) : esc_html__( 'Login here', 'userswp' ); ?></a></div>
            <div class="clfx"></div>
            <div class="uwp-footer-link uwp-register-now"><?php esc_html_e( 'Not a member?', 'userswp' ); ?> <a rel="nofollow" href="<?php echo esc_url( uwp_get_register_page_url() ); ?>"><?php echo uwp_get_option("register_link_title") ? esc_html( uwp_get_option("register_link_title") ) : esc_html__( 'Create account', 'userswp' ); ?></a></div>
        </div>
    </div>
<?php do_action('uwp_template_after', 'reset'); ?>