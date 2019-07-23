<?php do_action( 'uwp_template_before', 'login' ); ?>
	<div class="row">
		<div class="mx-auto mw-100" style="width: 20rem;">
			<div class="card card-signin rounded border rounded">
				<div class="card-header">
					<?php do_action( 'uwp_template_form_title_before', 'login' ); ?>

					<?php
					global $uwp_login_widget_args;
					$form_title = ! empty( $uwp_login_widget_args['form_title'] ) ? esc_attr__( $uwp_login_widget_args['form_title'], 'userswp' ) : __( 'Login', 'userswp' );
					$form_title = apply_filters( 'uwp_template_form_title', $form_title, 'login' );
					if ( $form_title ) {
						echo '<h3 class="card-title text-center">';
						echo $form_title;
						echo '</h3>';
					}
					?>
				</div>
				<div class="card-body">

					<?php do_action( 'uwp_template_display_notices', 'login' ); ?>

					<form class="uwp-login-form uwp_form" method="post">

						<?php do_action( 'uwp_template_fields', 'login' ); ?>

						<div class="uwp-remember-me custom-control custom-checkbox mb-3">
							<input name="remember_me" id="remember_me" value="forever" type="checkbox"
							       class="custom-control-input">
							<label class="custom-control-label"
							       for="remember_me"><?php _e( 'Remember Me', 'userswp' ); ?></label>
						</div>

						<div class="form-group">
							<?php do_action( 'uwp_social_fields', 'login' ); ?>
						</div>

						<input type="submit" name="uwp_login_submit"
						       class="btn btn-primary btn-block text-uppercase"
						       value="<?php _e( 'Login', 'userswp' ); ?>">

					</form>
				</div>

				<div class="card-footer text-muted">
						<div class="uwp-footer-link float-left">
							<a rel="nofollow"
						                                           href="<?php echo uwp_get_register_page_url(); ?>"
						                                           class="d-block text-center small"><?php _e( 'Create account', 'userswp' ); ?></a>
						</div>
						<div class="uwp-footer-link float-right">
							<a rel="nofollow"
						                                            href="<?php echo uwp_get_forgot_page_url(); ?>"
						                                            class="d-block text-center small"><?php _e( 'Forgot password?', 'userswp' ); ?></a>
						</div>
				</div>
			</div>
		</div>
	</div>
<?php do_action( 'uwp_template_after', 'login' ); ?>