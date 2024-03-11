<?php
/**
 * Admin View: Settings
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="wrap userswp">
	<form method="<?php echo esc_attr( apply_filters( 'uwp_settings_form_method_tab_' . $current_tab, 'post' ) ); ?>" id="mainform" action="" enctype="multipart/form-data">
		<nav class="nav-tab-wrapper uwp-nav-tab-wrapper">
			<?php
			foreach ( $tabs as $name => $label ) {
                echo '<a href="' . esc_url( admin_url( 'admin.php?page=userswp&tab=' . $name ) ) . '" class="nav-tab ' . ( $current_tab == $name ? 'nav-tab-active' : '' ) . '">' . esc_html( $label ) . '</a>';
				}
				do_action( 'uwp_settings_tabs' );
			?>
		</nav>
		<h1 class="screen-reader-text"><?php echo esc_html( $tabs[ $current_tab ] ); ?></h1>
		<?php
			do_action( 'uwp_sections_' . $current_tab );

			self::show_messages();

			do_action( 'uwp_settings_' . $current_tab );
			do_action( 'uwp_settings_tabs_' . $current_tab );
		?>
		<p class="submit">
			<?php if ( empty( $GLOBALS['uwp_hide_save_button'] ) ) : ?>
				<input name="save" class="button-primary uwp-save-button" type="submit" value="<?php esc_attr_e( 'Save changes', 'userswp' ); ?>" />
			<?php endif; ?>
			<?php wp_nonce_field( 'userswp-settings' ); ?>
		</p>
	</form>
</div>
