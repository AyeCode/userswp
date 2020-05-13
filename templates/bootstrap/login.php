<?php
/**
 * Login template (default)
 * 
 * @ver 1.0.0
 */
$css_class = !empty($args['css_class']) ? esc_attr( $args['css_class'] ) : 'border-0';
$form_title = ! empty( $args['form_title'] ) || $args['form_title']=='0' ? esc_attr__( $args['form_title'], 'userswp' ) : __( 'Login', 'userswp' );
$form_title = apply_filters( 'uwp_template_form_title', $form_title, 'login' );
do_action( 'uwp_template_before', 'login' ); ?>
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
				<?php do_action( 'uwp_template_form_title_before', 'login' );

				if ( !wp_doing_ajax() && $form_title != '0' ) {
					echo '<h3 class="card-title text-center mb-4">';
					echo esc_attr($form_title);
					echo '</h3>';
				}
				
				do_action( 'uwp_template_display_notices', 'login' ); 
				?>
				<form class="uwp-login-form uwp_form" method="post">

					<?php do_action( 'uwp_template_fields', 'login' ); ?>

					<div class="uwp-remember-me custom-control custom-checkbox mb-3">
						<input name="remember_me" id="remember_me<?php if(wp_doing_ajax()){echo "_ajax";}?>" value="forever" type="checkbox"
						       class="custom-control-input">
						<label class="custom-control-label"
						       for="remember_me<?php if(wp_doing_ajax()){echo "_ajax";}?>"><?php _e( 'Remember Me', 'userswp' ); ?></label>
					</div>

					<button type="submit" name="uwp_login_submit"
					       class="btn btn-primary btn-block text-uppercase uwp_login_submit"
					       ><?php _e( 'Login', 'userswp' ); ?></button>



					<div class="uwp-footer-links">
						<div class="uwp-footer-link d-inline-block"><a rel="nofollow"
						                                           href="<?php echo uwp_get_register_page_url(); ?>"
						                                           class="d-block text-center mt-2 small uwp-register-link"><?php _e( 'Create account', 'userswp' ); ?></a>
						</div>
						<div class="uwp-footer-link float-right"><a rel="nofollow"
						                                            href="<?php echo uwp_get_forgot_page_url(); ?>"
						                                            class="d-block text-center mt-2 small uwp-forgot-password-link"><?php _e( 'Forgot password?', 'userswp' ); ?></a>
						</div>
					</div>

					<div class="form-group text-center mb-0 p-0">
						<?php do_action( 'uwp_social_fields', 'login' ); ?>
					</div>

				</form>
			</div>
	</div>
</div>
<?php do_action( 'uwp_template_after', 'login' ); ?>