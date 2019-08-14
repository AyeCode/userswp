<?php
/**
 * Account template (style1)
 *
 * @ver 1.0.0
 */
global $uwp_widget_args;
$css_class = ! empty( $uwp_widget_args['css_class'] ) ? esc_attr( $uwp_widget_args['css_class'] ) : '';
do_action( 'uwp_template_before', 'account' ); ?>
	<div class="row">
		<div class="card mx-auto container-fluid p-0 <?php echo $css_class; ?>" >
			<div class="card-header">

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
			</div>

			<div class="card-body">

				<?php do_action( 'uwp_template_display_notices', 'account' ); ?>

				<?php do_action( 'uwp_account_menu_display' ); ?>

				<?php do_action( 'uwp_account_form_display', $type ); ?>

			</div>

			<div class="card-footer text-muted">

				<?php do_action( 'uwp_account_form_footer_links' ); ?>

			</div>

		</div>
	</div>
<?php do_action( 'uwp_template_after', 'account' ); ?>