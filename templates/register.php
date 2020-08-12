<?php do_action( 'uwp_template_before', 'register' );
$css_class = ! empty( $args['css_class'] ) ? esc_attr( $args['css_class'] ) : '';
$form_title = ! empty( $args['form_title'] ) ? esc_attr__( $args['form_title'], 'userswp' ) : __( 'Register', 'userswp' );
$form_title = apply_filters( 'uwp_template_form_title', $form_title, 'register' );
?>
    <div class="uwp-content-wrap <?php echo $css_class; ?>">
        <div class="uwp-registration">
            <div class="uwp-rf-icon"><i class="fas fa-pencil-alt fa-fw"></i></div>
			<?php do_action( 'uwp_template_form_title_before', 'register' ); ?>
            <h2><?php
				echo apply_filters( 'uwp_template_form_title', $form_title, 'register' );
				?>
            </h2>
			<?php do_action( 'uwp_template_display_notices', 'register' ); ?>
            <form class="uwp-registration-form uwp_form" method="post" enctype="multipart/form-data">
				<?php do_action( 'uwp_template_fields', 'register' ); ?>
				<?php do_action( 'uwp_social_fields', 'register' ); ?>
                <input name="uwp_register_submit" value="<?php _e( 'Create Account', 'userswp' ); ?>" type="submit">
            </form>
            <div class="uwp-footer-link uwp-login-now"><?php _e( 'Already a member?', 'userswp' ); ?> <a
                        rel="nofollow"
                        href="<?php echo uwp_get_login_page_url(); ?>"><?php _e( 'Login here', 'userswp' ); ?></a>
            </div>
        </div>
    </div>
<?php do_action( 'uwp_template_after', 'register' ); ?>