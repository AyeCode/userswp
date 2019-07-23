<?php do_action( 'uwp_template_before', 'change' ); ?>
	<div class="row">
		<div class="mx-auto mw-100" style="width: 20rem;">
			<div class="card card-signin rounded border rounded p-2">
				<div class="card-body">

					<?php do_action( 'uwp_template_form_title_before', 'change' ); ?>

					<h3 class="card-title text-center">
						<?php
						global $uwp_change_widget_args;
						$form_title = ! empty( $uwp_change_widget_args['form_title'] ) ? esc_attr__( $uwp_change_widget_args['form_title'], 'userswp' ) : __( 'Change', 'userswp' );
						echo apply_filters( 'uwp_template_form_title', $form_title, 'change' );
						?>
					</h3>

					<?php do_action( 'uwp_template_display_notices', 'change' ); ?>

					<form class="uwp-change-form uwp_form" method="post">
						<?php do_action( 'uwp_template_fields', 'change' ); ?>
						<input name="uwp_change_submit" class="btn btn-lg btn-primary btn-block text-uppercase"
						       value="<?php echo __( 'Submit', 'userswp' ); ?>" type="submit">
					</form>

					<div class="uwp-footer-links">
						<div class="uwp-footer-link float-left"><a rel="nofollow"
						                                           href="<?php echo uwp_get_account_page_url(); ?>"
						                                           class="d-block text-center mt-2 small"><?php _e( 'Account', 'userswp' ); ?></a>
						</div>
						<div class="uwp-footer-link float-right"><a rel="nofollow"
						                                            href="<?php echo uwp_get_profile_page_url(); ?>"
						                                            class="d-block text-center mt-2 small"><?php _e( 'Profile', 'userswp' ); ?></a>
						</div>
					</div>

				</div>
			</div>
		</div>
	</div>
<?php do_action( 'uwp_template_after', 'change' ); ?>