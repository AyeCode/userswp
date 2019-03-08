<?php
/**
 * Admin View: Settings
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="wrap">
    <nav class="nav-tab-wrapper uwp-nav-tab-wrapper">
        <?php
        foreach ( $tabs as $name => $label ) {
            echo '<a href="' . admin_url( 'admin.php?page=uwp_form_builder&tab=' . $name ) . '" class="nav-tab ' . ( $current_tab == $name ? 'nav-tab-active' : '' ) . '">' . $label . '</a>';
            }
            do_action( 'uwp_settings_tabs' );
        ?>
    </nav>
    <h1 class="screen-reader-text"><?php echo esc_html( $tabs[ $current_tab ] ); ?></h1>
    <?php
        do_action( 'uwp_sections_' . $current_tab );
        do_action( 'uwp_settings_' . $current_tab );
        do_action( 'uwp_settings_tabs_' . $current_tab );
    ?>
</div>
