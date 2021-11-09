<?php
/**
 * Register template (default)
 *
 * @ver 1.0.1
 */

$css_class = ! empty( $args['css_class'] ) ? esc_attr( $args['css_class'] ) : 'border-0';
$form_title = ! empty( $args['form_title'] ) || $args['form_title']=='0' ? esc_attr__( $args['form_title'], 'userswp' ) : __( 'Register', 'userswp' );
$form_title = apply_filters( 'uwp_template_form_title', $form_title, 'register' );
do_action( 'uwp_template_before', 'register', $args ); ?>
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
				<?php
				do_action( 'uwp_template_form_title_before', 'register', $args );

				if (!wp_doing_ajax() && $form_title != '0' ) {
					echo '<h3 class="card-title text-center mb-4">';
					echo esc_attr( $form_title );
					echo '</h3>';
				}

				do_action( 'uwp_template_display_notices', 'register', $args ); ?>

                <form class="uwp-registration-form uwp_form" method="post" enctype="multipart/form-data">
					<?php
					do_action( 'uwp_template_fields', 'register', $args );
					echo aui()->button(array(
						'type'       => 'submit',
						'class'      => 'btn btn-primary btn-block text-uppercase uwp_register_submit',
						'content'    => __( 'Create Account', 'userswp' ),
						'name'       => 'uwp_register_submit',
					));
					?>
                </form>

                <div class="uwp-footer-links">
                    <div class="uwp-footer-link">
						<?php
						echo aui()->button(array(
							'type'  =>  'a',
							'href'       => uwp_get_login_page_url(),
							'class'      => 'd-block text-center mt-2 small uwp-login-link',
							'content'    => uwp_get_option("login_link_title") ? uwp_get_option("login_link_title") : __( 'Login', 'userswp' ),
							'extra_attributes'  => array('rel'=>'nofollow')
						));
						?>
                    </div>
                </div>

                <div class="form-group text-center mb-0 p-0">
					<?php do_action( 'uwp_social_fields', 'register', $args ); ?>
                </div>

            </div>
        </div>
    </div>
<?php do_action( 'uwp_template_after', 'register', $args ); ?>