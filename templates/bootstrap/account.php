<?php do_action( 'uwp_template_before', 'account' ); ?>
	<div class="row">
		<div class="mx-auto mw-100" style="width: 20rem;">
			<div class="card card-signin rounded border p-2">
				<div class="card-body">

					<?php do_action( 'uwp_template_form_title_before', 'account' ); ?>

					<?php
					if ( isset( $_GET['type'] ) ) {
						$type = strip_tags( esc_sql( $_GET['type'] ) );
					} else {
						$type = 'account';
					}
					?>

					<h3 class="card-title text-center">
						<?php
						global $uwp_account_widget_args;
						$form_title = ! empty( $uwp_account_widget_args['form_title'] ) ? esc_attr__( $uwp_account_widget_args['form_title'], 'userswp' ) : __( 'Edit Account', 'userswp' );
						echo apply_filters( 'uwp_template_form_title', $form_title, 'account' );
						?>
					</h3>

					<?php do_action( 'uwp_template_form_title_after', 'account' ); ?>

					<?php do_action( 'uwp_template_display_notices', 'account' ); ?>

					<?php do_action( 'uwp_account_menu_display' ); ?>

					<?php do_action( 'uwp_account_form_display', $type ); ?>

				</div>
			</div>
		</div>
	</div>
<?php do_action( 'uwp_template_after', 'account' ); ?>