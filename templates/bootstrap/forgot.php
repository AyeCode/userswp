<?php
/**
 * Forgot template (default)
 *
 * @ver 1.0.0
 */
global $uwp_widget_args;
$css_class = ! empty( $uwp_widget_args['css_class'] ) ? esc_attr( $uwp_widget_args['css_class'] ) : 'border-0';
do_action( 'uwp_template_before', 'forgot' ); ?>
	<div class="row">
		<div class="card mx-auto container-fluid p-0 <?php echo $css_class; ?>" >
			<div class="card-body">
				<?php 
				do_action( 'uwp_template_form_title_before', 'forgot' );

				$form_title = ! empty( $uwp_widget_args['form_title'] ) ? esc_attr__( $uwp_widget_args['form_title'], 'userswp' ) : __( 'Forgot Password?', 'userswp' );
				$form_title = apply_filters( 'uwp_template_form_title', $form_title, 'forgot' );
				if ( $form_title != '0' ) {
					echo '<h3 class="card-title text-center mb-4">';
					echo $form_title;
					echo '</h3>';
				}
				?>

				<?php do_action( 'uwp_template_display_notices', 'forgot' ); ?>

				<form class="uwp-forgot-form uwp_form" method="post">

					<?php do_action( 'uwp_template_fields', 'forgot' ); ?>

					<input type="submit" name="uwp_forgot_submit"
					       class="btn btn-primary btn-block text-uppercase"
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
<?php do_action( 'uwp_template_after', 'forgot' ); ?>