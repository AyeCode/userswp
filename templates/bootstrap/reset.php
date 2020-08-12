<?php
/**
 * Reset template (default)
 *
 * @ver 1.0.0
 */
$css_class = ! empty( $args['css_class'] ) ? esc_attr( $args['css_class'] ) : 'border-0';
$form_title = ! empty( $args['form_title'] ) ? esc_attr__( $args['form_title'], 'userswp' ) : __( 'Reset Password', 'userswp' );
$form_title = apply_filters( 'uwp_template_form_title', $form_title, 'reset' );
do_action( 'uwp_template_before', 'reset' ); ?>
	<div class="row">
		<div class="card mx-auto container-fluid p-0 <?php echo $css_class; ?>" >
			<div class="card-body">
				<?php
				do_action( 'uwp_template_form_title_before', 'reset' );

				if ( $form_title != '0' ) {
					echo '<h3 class="card-title text-center mb-4">';
					echo $form_title;
					echo '</h3>';
				}
				?>

				<?php do_action( 'uwp_template_display_notices', 'reset' ); ?>

				<?php if ( isset( $_GET['key'] ) && isset( $_GET['login'] ) ) { ?>
					<form class="uwp-login-form uwp_form" method="post">
						<?php
                        do_action( 'uwp_template_fields', 'reset' );
						echo aui()->button(array(
							'type'       => 'submit',
							'class'      => 'btn btn-primary btn-block text-uppercase',
							'content'    => __( 'Submit', 'userswp' ),
							'name'       => 'uwp_reset_submit',
						));
                        ?>
					</form>
				<?php } else {
					echo aui()->alert(array('type'=>'danger','content'=> sprintf( __( 'You can not access this page directly. Follow the password reset link you received in your email. To request new password reset link <a href="%s">visit here</a>.', 'userswp' ), uwp_get_forgot_page_url() ) ));
				} ?>

				<div class="uwp-footer-links">
					<div class="uwp-footer-link float-left">
						<?php
						echo aui()->button(array(
							'type'  =>  'a',
							'href'       => uwp_get_login_page_url(),
							'class'      => 'd-block text-center mt-2 small',
							'content'    => __( 'Login', 'userswp' ),
							'extra_attributes'  => array('rel'=>'nofollow')
						));
						?>
					</div>
					<div class="uwp-footer-link float-right">
						<?php
						echo aui()->button(array(
							'type'  =>  'a',
							'href'       => uwp_get_register_page_url(),
							'class'      => 'd-block text-center mt-2 small',
							'content'    => __( 'Create account', 'userswp' ),
							'extra_attributes'  => array('rel'=>'nofollow')
						));
						?>
					</div>
				</div>

			</div>
		</div>
	</div>
<?php do_action( 'uwp_template_after', 'reset' ); ?>