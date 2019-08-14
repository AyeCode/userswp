<?php
/**
 * This is a file takes advantage of anonymous functions to to load the latest version of the WP Bootstrap Settings.
 */

/**
 * Bail if we are not in WP.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Set the version only if its the current newest while loading.
 */
add_action('after_setup_theme', function () {
	global $wp_bootstrap_version,$wp_bootstrap_file_key;
	$this_version = "1.0.0";
	if(version_compare($this_version ,  $wp_bootstrap_version, '>')){
		$wp_bootstrap_version = $this_version ;
		$wp_bootstrap_file_key = wp_hash( __FILE__ );
	}
},0);

/**
 * Load this version of WP Bootstrap Settings only if the file hash is the current one.
 */
add_action('after_setup_theme', function () {
	global $wp_bootstrap_file_key;
	if($wp_bootstrap_file_key &&  $wp_bootstrap_file_key == wp_hash( __FILE__ )){
		include_once( dirname( __FILE__ ) . '/includes/class-bsui.php' );
		include_once( dirname( __FILE__ ) . '/wp-bootstrap-settings.php' );
	}
},1);