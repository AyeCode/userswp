<?php do_action( 'uwp_template_before', 'reset' ); ?>
	<div class="row">
		<div class="mx-auto mw-100" style="width: 20rem;">
			<div class="card card-signin rounded border rounded p-2">
				<div class="card-body">

					<?php do_action( 'uwp_template_form_title_before', 'reset' ); ?>

					<h3 class="card-title text-center">
						<?php
						global $uwp_reset_widget_args;
						$form_title = ! empty( $uwp_reset_widget_args['form_title'] ) ? esc_attr__( $uwp_reset_widget_args['form_title'], 'userswp' ) : __( 'Reset Password', 'userswp' );
						echo apply_filters( 'uwp_template_form_title', $form_title, 'reset' );
						?>
					</h3>

					<?php do_action( 'uwp_template_display_notices', 'reset' ); ?>

					<?php if ( isset( $_GET['key'] ) && isset( $_GET['login'] ) ) { ?>
						<form class="uwp-login-form uwp_form" method="post">
							<?php do_action( 'uwp_template_fields', 'reset' ); ?>
							<input name="uwp_reset_submit" class="btn btn-lg btn-primary btn-block text-uppercase"
							       value="<?php echo __( 'Submit', 'userswp' ); ?>" type="submit">
						</form>
					<?php } else {
						echo '<p class="alert alert-danger">' . sprintf( __( 'You can not access this page directly. Follow the password reset link you received in your email. To request new password reset link <a href="%s">visit here</a>.', 'userswp' ), uwp_get_page_link( 'forgot' ) ) . '</p>';
					} ?>

					<div class="uwp-footer-links">
						<div class="uwp-footer-link float-left"><a rel="nofollow"
						                                           href="<?php echo uwp_get_login_page_url(); ?>"
						                                           class="d-block text-center mt-2 small"><?php _e( 'Login', 'userswp' ); ?></a>
						</div>
						<div class="uwp-footer-link float-right"><a rel="nofollow"
						                                            href="<?php echo uwp_get_register_page_url(); ?>"
						                                            class="d-block text-center mt-2 small"><?php _e( 'Create account', 'userswp' ); ?></a>
						</div>
					</div>

				</div>
			</div>
		</div>
	</div>
<?php do_action( 'uwp_template_after', 'reset' ); ?>