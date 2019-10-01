<?php
/**
 * Register template (default)
 *
 * @ver 1.0.0
 */
global $uwp_widget_args;
$css_class = ! empty( $uwp_widget_args['css_class'] ) ? esc_attr( $uwp_widget_args['css_class'] ) : 'border-0';
$form_title = ! empty( $uwp_widget_args['form_title'] ) ? esc_attr__( $uwp_widget_args['form_title'], 'userswp' ) : __( 'Register', 'userswp' );
$form_title = apply_filters( 'uwp_template_form_title', $form_title, 'register' );
do_action( 'uwp_template_before', 'register' ); ?>
	<div class="row">
		<div class="card mx-auto container-fluid p-0 <?php echo $css_class; ?>" >
			<?php
			// ajax modal
			if(wp_doing_ajax() && $form_title != '0'){
				?>
				<div class="modal-header">
					<h5 class="modal-title"><?php echo esc_attr($form_title);?></h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="<?php _e("Close","userswp");?>">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<?php
			}
			?>
			<div class="card-body">
				<?php
				do_action( 'uwp_template_form_title_before', 'register' );

				if (!wp_doing_ajax() && $form_title != '0' ) {
					echo '<h3 class="card-title text-center mb-4">';
					echo $form_title;
					echo '</h3>';
				}
				?>

				<?php do_action( 'uwp_template_display_notices', 'register' ); ?>

				<form class="uwp-registration-form uwp_form" method="post" enctype="multipart/form-data">
					<?php do_action( 'uwp_template_fields', 'register' ); ?>

					<div class="form-group">
						<?php do_action( 'uwp_social_fields', 'login' ); ?>
					</div>

					<button name="uwp_register_submit" class="btn btn-primary btn-block text-uppercase uwp_register_submit"
					        type="submit"><?php echo __( 'Create Account', 'userswp' ); ?></button>
				</form>

				<div class="uwp-footer-links">
					<div class="uwp-footer-link"><a rel="nofollow"
					                                href="<?php echo uwp_get_login_page_url(); ?>"
					                                class="d-block text-center mt-2 small uwp-login-link"><?php _e( 'Login', 'userswp' ); ?></a>
					</div>
				</div>

			</div>
		</div>
	</div>
<?php do_action( 'uwp_template_after', 'register' ); ?>