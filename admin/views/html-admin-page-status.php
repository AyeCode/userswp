<?php
/**
 * Admin View: Page - Status
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$current_tab = ! empty( $_REQUEST['tab'] ) ? sanitize_title( $_REQUEST['tab'] ) : 'status';
$tabs        = array(
	'status' => __( 'System Status', 'userswp' ),
);
$tabs        = apply_filters( 'uwp_admin_status_tabs', $tabs );
?>
<div class="wrap userswp">
	<nav class="nav-tab-wrapper uwp-nav-tab-wrapper">
		<?php
			foreach ( $tabs as $name => $label ) {
				echo '<a href="' . esc_url( admin_url( 'admin.php?page=uwp_status&tab=' . $name ) ) . '" class="nav-tab ';
				if ( $current_tab == $name ) {
					echo 'nav-tab-active';
				}
				echo '">' . esc_html( $label ) . '</a>';
			}
		?>
	</nav>
	<h1 class="screen-reader-text"><?php echo esc_html( $tabs[ $current_tab ] ); ?></h1>
	<?php
		switch ( $current_tab ) {
			default :
				if ( array_key_exists( $current_tab, $tabs ) && has_action( 'uwp_admin_status_content_' . $current_tab ) ) {
					do_action( 'uwp_admin_status_content_' . $current_tab );
				} else {
                    UsersWP_Status::status_report();
				}
			break;
		}
	?>
</div>
