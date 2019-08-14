<?php
/**
 * Account template (default)
 *
 * @ver 1.0.0
 */
global $uwp_widget_args;
$css_class = ! empty( $uwp_widget_args['css_class'] ) ? esc_attr( $uwp_widget_args['css_class'] ) : 'border-0';
do_action( 'uwp_template_before', 'account' ); ?>
	<div class="row">
		<div class="card mx-auto container-fluid p-0 <?php echo $css_class; ?>" >
			<div class="card-body">

				<?php do_action( 'uwp_template_form_title_before', 'account' ); ?>

				<?php
				if ( isset( $_GET['type'] ) ) {
					$type = strip_tags( esc_sql( $_GET['type'] ) );
				} else {
					$type = 'account';
				}

				$form_title = ! empty( $uwp_widget_args['form_title'] ) ? esc_attr__( $uwp_widget_args['form_title'], 'userswp' ) : __( 'Edit Account', 'userswp' );
				$form_title = apply_filters( 'uwp_template_form_title', $form_title, 'account' );
				if ( $form_title != '0' ) {
					echo '<h3 class="card-title text-center">';
					echo $form_title;
					echo '</h3>';
				}
				?>

				<?php do_action( 'uwp_template_form_title_after', 'account' ); ?>

				<?php do_action( 'uwp_template_display_notices', 'account' ); ?>

				<?php do_action( 'uwp_account_menu_display' ); ?>

				<?php do_action( 'uwp_account_form_display', $type ); ?>

			</div>
		</div>
	</div>
<?php do_action( 'uwp_template_after', 'account' ); ?>