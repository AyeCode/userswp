<?php
/**
 * Login template (default)
 * 
 * @ver 1.0.1
 */
$css_class = !empty($args['css_class']) ? esc_attr( $args['css_class'] ) : 'border-0';
$form_title = ! empty( $args['form_title'] ) || $args['form_title']=='0' ? esc_attr__( $args['form_title'], 'userswp' ) : __( 'Login', 'userswp' );
$form_title = apply_filters( 'uwp_template_form_title', $form_title, 'login' );
do_action( 'uwp_template_before', 'login', $args ); ?>
<div class="row">
	<div class="card mx-auto container-fluid p-0 <?php echo esc_attr( $css_class ); ?>" >
		<?php
		// ajax modal
		if(wp_doing_ajax() && $form_title != '0'){
			?>
			<div class="modal-header">
				<h5 class="modal-title"><?php echo esc_attr($form_title);?></h5>
				<?php
				echo aui()->button(array(
					'type'       =>  'button',
					'class'      => 'close',
					'content'    => '<span aria-hidden="true">&times;</span>',
					'extra_attributes'  => array('aria-label'=>__("Close","userswp"), 'data-dismiss'=>"modal")
				));
				?>
			</div>
			<?php
		}
		?>
			<div class="card-body">
				<?php do_action( 'uwp_template_form_title_before', 'login', $args );

				if ( !wp_doing_ajax() && $form_title != '0' ) {
					echo '<h3 class="card-title text-center mb-4">';
					echo esc_attr($form_title);
					echo '</h3>';
				}
				
				do_action( 'uwp_template_display_notices', 'login', $args );
				?>
				<form class="uwp-login-form uwp_form" method="post">

					<?php do_action( 'uwp_template_fields', 'login', $args ); ?>

					<div class="uwp-remember-me custom-checkbox mb-3">
                        <?php
                        $id = 'remember_me';
                        if(wp_doing_ajax()){$id.='_ajax';}

                        echo aui()->input(array(
	                        'type'  =>  'checkbox',
	                        'id'    =>  $id,
	                        'name'    =>  'remember_me',
	                        'value' =>  'forever',
	                        'label' => __( 'Remember Me', 'userswp' ),
                        ));
                        ?>
					</div>

					<?php
					echo aui()->button(array(
						'type'       =>  'submit',
						'class'      => 'btn btn-primary btn-block text-uppercase uwp_login_submit',
						'content'    => __( 'Login', 'userswp' ),
						'name'       => 'uwp_login_submit',
					));
					?>

				</form>

                <div class="uwp-footer-links">
                    <div class="uwp-footer-link d-inline-block">
						<?php
						echo aui()->button(array(
							'type'  =>  'a',
							'href'       => uwp_get_register_page_url(),
							'class'      => 'd-block text-center mt-2 small uwp-register-link',
							'content'    => uwp_get_option("register_link_title") ? uwp_get_option("register_link_title") : __( 'Create account', 'userswp' ),
							'extra_attributes'  => array('rel'=>'nofollow')
						));
						?>
                    </div>
                    <div class="uwp-footer-link float-right">
						<?php
						echo aui()->button(array(
							'type'  =>  'a',
							'href'       => uwp_get_forgot_page_url(),
							'class'      => 'd-block text-center mt-2 small uwp-forgot-password-link',
							'content'    => uwp_get_option("forgot_link_title") ? uwp_get_option("forgot_link_title") : __( 'Forgot password?', 'userswp' ),
							'extra_attributes'  => array('rel'=>'nofollow')
						));
						?>
                    </div>
                </div>

                <div class="form-group text-center mb-0 p-0">
					<?php do_action( 'uwp_social_fields', 'login', $args ); ?>
                </div>

			</div>
	</div>
</div>
<?php do_action( 'uwp_template_after', 'login', $args ); ?>