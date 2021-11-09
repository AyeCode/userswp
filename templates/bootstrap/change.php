<?php
/**
 * Change template (default)
 *
 * @ver 1.0.0
 */
$css_class = !empty($args['css_class']) ? esc_attr( $args['css_class'] ) : 'border-0';
do_action( 'uwp_template_before', 'change' ); ?>
	<div class="row">
		<div class="card mx-auto container-fluid p-0 <?php echo esc_attr( $css_class ); ?>" >
				<div class="card-body">
					<?php
					do_action( 'uwp_template_form_title_before', 'change' );

					$form_title = ! empty( $args['form_title'] ) || $args['form_title']=='0' ? esc_attr__( $args['form_title'], 'userswp' ) : __( 'Change Password', 'userswp' );
					$form_title = apply_filters( 'uwp_template_form_title', $form_title, 'change' );
					if ( $form_title ) {
						echo '<h3 class="card-title text-center mb-4">';
						echo esc_attr( $form_title );
						echo '</h3>';
					}

					do_action( 'uwp_template_display_notices', 'change' );
					?>

					<form class="uwp-change-form uwp_form" method="post">
						<?php do_action( 'uwp_template_fields', 'change' ); ?>
						<input name="uwp_change_submit" class="btn btn-primary btn-block text-uppercase"
						       value="<?php _e( 'Submit', 'userswp' ); ?>" type="submit">
					</form>

					<div class="uwp-footer-links">
						<div class="uwp-footer-link float-left">
                            <?php
                            echo aui()->button(array(
	                            'type'  =>  'a',
	                            'href'       => uwp_get_account_page_url(),
	                            'class'      => 'd-block text-center mt-2 small',
	                            'content'    => uwp_get_option("account_link_title") ? uwp_get_option("account_link_title") : __( 'Account', 'userswp' ),
	                            'extra_attributes'  => array('rel'=>'nofollow')
                            ));
                            ?>
						</div>
						<div class="uwp-footer-link float-right">
							<?php
							echo aui()->button(array(
								'type'  =>  'a',
								'href'       => uwp_get_profile_page_url(),
								'class'      => 'd-block text-center mt-2 small',
								'content'    => uwp_get_option("profile_link_title") ? uwp_get_option("profile_link_title") : __( 'Profile', 'userswp' ),
								'extra_attributes'  => array('rel'=>'nofollow')
							));
							?>
						</div>
					</div>

				</div>
		</div>
	</div>
<?php do_action( 'uwp_template_after', 'change' ); ?>