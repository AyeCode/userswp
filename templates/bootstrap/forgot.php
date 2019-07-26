<?php do_action( 'uwp_template_before', 'forgot' ); ?>
	<div class="row">
		<div class="mx-auto mw-100" style="width: 20rem;">
			<div class="card card-signin rounded border p-2">
				<div class="card-body">
					<?php do_action( 'uwp_template_form_title_before', 'forgot' ); ?>

					<h3 class="card-title text-center">
						<?php
						global $uwp_forgot_widget_args;
						$form_title = ! empty( $uwp_forgot_widget_args['form_title'] ) ? esc_attr__( $uwp_forgot_widget_args['form_title'], 'userswp' ) : __( 'Forgot Password?', 'userswp' );
						echo apply_filters( 'uwp_template_form_title', $form_title, 'forgot' );
						?>
					</h3>

					<?php do_action( 'uwp_template_display_notices', 'forgot' ); ?>

					<form class="uwp-forgot-form uwp_form" method="post">

						<?php do_action( 'uwp_template_fields', 'forgot' ); ?>

						<input type="submit" name="uwp_forgot_submit"
						       class="btn btn-lg btn-primary btn-block text-uppercase"
						       value="<?php _e( 'Submit', 'userswp' ); ?>">
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

					</form>

				</div>
			</div>
		</div>
	</div>
<?php do_action( 'uwp_template_after', 'forgot' ); ?>