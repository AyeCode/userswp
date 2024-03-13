<?php
/**
 * Reset template (default)
 *
 * @ver 1.0.0
 */
$css_class = ! empty( $args['css_class'] ) ? esc_attr( $args['css_class'] ) : 'border-0';
$form_title = ! empty( $args['form_title'] ) || $args['form_title']=='0' ? esc_attr__( $args['form_title'], 'userswp' ) : __( 'Reset Password', 'userswp' );
$form_title = apply_filters( 'uwp_template_form_title', $form_title, 'reset' );
do_action( 'uwp_template_before', 'reset' ); ?>
	<div class="row">
		<div class="card mx-auto container-fluid p-0 <?php echo esc_attr( $css_class ); ?>" >
			<div class="card-body">
				<?php
				do_action( 'uwp_template_form_title_before', 'reset' );

				if ( $form_title != '0' ) {
					echo '<h3 class="card-title text-center mb-4">';
					echo esc_attr( $form_title );
					echo '</h3>';
				}
				?>

				<?php do_action( 'uwp_template_display_notices', 'reset' ); ?>

				<?php if ( isset( $_GET['key'] ) && isset( $_GET['login'] ) ) { ?>
					<form class="uwp-reset-form uwp_form" method="post">
						<?php
                        do_action( 'uwp_template_fields', 'reset' );
						echo aui()->button(array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							'type'       => 'submit',
							'class'      => 'btn btn-primary btn-block text-uppercase',
							'content'    => esc_html__( 'Submit', 'userswp' ),
							'name'       => 'uwp_reset_submit',
						));
                        ?>
					</form>
				<?php } else {
					echo aui()->alert(array('type'=>'danger','content'=> sprintf( __( 'You can not access this page directly. Follow the password reset link you received in your email. To request new password reset link <a href="%s">visit here</a>.', 'userswp' ), esc_url( uwp_get_forgot_page_url() ) ) )); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				} ?>

				<div class="uwp-footer-links">
					<div class="uwp-footer-link float-left">
						<?php
						echo aui()->button(array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							'type'  =>  'a',
							'href'       => esc_url( uwp_get_login_page_url() ),
							'class'      => 'd-block text-center mt-2 small',
							'content'    => uwp_get_option("login_link_title") ? esc_html( uwp_get_option("login_link_title") ) : esc_html__( 'Login', 'userswp' ),
							'extra_attributes'  => array('rel'=>'nofollow')
						));
						?>
					</div>
					<div class="uwp-footer-link float-right">
						<?php
						echo aui()->button(array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							'type'  =>  'a',
							'href'       => esc_url( uwp_get_register_page_url() ),
							'class'      => 'd-block text-center mt-2 small',
							'content'    => uwp_get_option("register_link_title") ? esc_html( uwp_get_option("register_link_title") ) : esc_html__( 'Create account', 'userswp' ),
							'extra_attributes'  => array('rel'=>'nofollow')
						));
						?>
					</div>
				</div>

			</div>
		</div>
	</div>
<?php do_action( 'uwp_template_after', 'reset' ); ?>