<?php
/**
 * Forgot template (default)
 *
 * @ver 1.0.0
 */

global $aui_bs5;

$css_class = ! empty( $args['css_class'] ) ? esc_attr( $args['css_class'] ) : 'border-0';
$form_title = ! empty( $args['form_title'] ) || $args['form_title']=='0' ? esc_attr__( $args['form_title'], 'userswp' ) : __( 'Forgot Password?', 'userswp' );
$form_title = apply_filters( 'uwp_template_form_title', $form_title, 'forgot' );
do_action( 'uwp_template_before', 'forgot' ); ?>
	<div class="row">
		<div class="card mx-auto container-fluid p-0 <?php echo esc_attr( $css_class ); ?>" >
			<?php
			// ajax modal
			if(wp_doing_ajax() && $form_title != '0'){
				?>
				<div class="modal-header">
					<h5 class="modal-title"><?php echo esc_attr($form_title);?></h5>
					<?php
					echo aui()->button(array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						'type'       =>  'button',
						'class'      => 'close',
						'content'    => '<span aria-hidden="true">&times;</span>',
						'extra_attributes'  => array('aria-label'=> esc_html__("Close","userswp"), 'data-' . ( $aui_bs5 ? 'bs-' : '' ) . 'dismiss'=>"modal")
					));
					?>
				</div>
				<?php
			}
			?>
			<div class="card-body">
				<?php 
				do_action( 'uwp_template_form_title_before', 'forgot' );

				if ( !wp_doing_ajax() && $form_title != '0' ) {
					echo '<h3 class="card-title text-center mb-4">';
					echo esc_attr( $form_title );
					echo '</h3>';
				}
				?>

				<?php do_action( 'uwp_template_display_notices', 'forgot' ); ?>

				<form class="uwp-forgot-form uwp_form" method="post">

					<?php
                    do_action( 'uwp_template_fields', 'forgot' );
					echo aui()->button(array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						'type'       =>  'submit',
						'class'      => 'btn btn-primary btn-block text-uppercase uwp_forgot_submit',
						'content'    => esc_html__( 'Submit', 'userswp' ),
						'name'       => 'uwp_forgot_submit',
					));
					?>

					<div class="uwp-footer-links">
						<div class="uwp-footer-link float-left">
							<?php
							echo aui()->button(array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								'type'  =>  'a',
								'href'       => esc_url( uwp_get_login_page_url() ),
								'class'      => 'd-block text-center mt-2 small uwp-login-link',
								'content'    => esc_attr( uwp_get_option("login_link_title") ? __(uwp_get_option("login_link_title"), 'userswp') : __( 'Login', 'userswp' ) ),
								'extra_attributes'  => array('rel'=>'nofollow')
							));
							?>
						</div>
						<div class="uwp-footer-link float-right">
							<?php
							echo aui()->button(array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								'type'  =>  'a',
								'href'       => esc_url( uwp_get_register_page_url() ),
								'class'      => 'd-block text-center mt-2 small uwp-register-link',
								'content'    => esc_attr( uwp_get_option("register_link_title") ? __(uwp_get_option("register_link_title"), 'userswp') : __( 'Create account', 'userswp' ) ),
								'extra_attributes'  => array('rel'=>'nofollow')
							));
							?>
						</div>
					</div>

				</form>

			</div>
		</div>
	</div>
<?php do_action( 'uwp_template_after', 'forgot' ); ?>