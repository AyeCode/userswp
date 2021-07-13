<?php
/**
 * Account template (default)
 *
 * @ver 1.0.0
 */
$css_class    = ! empty( $args['css_class'] ) ? esc_attr( $args['css_class'] ) : 'border-0';
$user_id      = get_current_user_id();
$user_info    = get_userdata( $user_id );
$display_name = esc_attr($user_info->user_login);

do_action( 'uwp_template_before', 'account' ); ?>
    <div class="container h-100">
        <div class="row h-100 height-auto">
            <div class="col-lg-3 h-100 height-auto px-0">
                <div class="navbar-light h-100 height-auto">
                    <div class="bg-light pt-5 h-100">
                        <div class="d-flex justify-content-center flex-column align-items-center">
							<?php
							echo do_shortcode( '[uwp_user_avatar size=150 allow_change=1]' );

							do_action( 'uwp_template_form_title_before', 'account' );

							echo aui()->button( array(
								'type'    => 'a',
								'href'    => uwp_build_profile_tab_url( $user_id ),
								'class'   => 'mt-0 text-decoration-none font-weight-bold',
								'icon'    => '',
								'title'   => $display_name,
								'content' => '@' . $display_name,
							) );

							do_action( 'uwp_template_form_title_after', 'account' );
							?>
                        </div>
                        <div class="d-flex justify-content-center nav mt-0">
							<?php do_action( 'uwp_account_menu_display' ); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-9">
                <div class="pl-lg-4 pl-sm-0 h-100 pt-5 pb-lg-0 pb-3">
					<?php
					$form_title = ! empty( $args['form_title'] ) ? esc_attr__( $args['form_title'], 'userswp' ) : __( 'Edit Account', 'userswp' );

					if ( isset( $_GET['type'] ) ) {
						$type = strip_tags( esc_sql( $_GET['type'] ) );
					} else {
						$type = 'account';
					}

					$form_title = apply_filters( 'uwp_account_page_title', $form_title, $type );
					if ( $form_title != '0' ) {
						echo '<h3 class="mb-lg-5 mb-4">';
						echo esc_attr( $form_title );
						echo '</h3>';
					}

					do_action( 'uwp_template_display_notices', 'account' );

					do_action( 'uwp_account_form_display', $type );

					?>
                </div>
            </div>
        </div>
    </div>

<?php do_action( 'uwp_template_after', 'account' ); ?>