<?php
/**
 * This is a file takes advantage of anonymous functions to to load the latest version of the AyeCode UI Settings.
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
	global $ayecode_ui_version,$ayecode_ui_file_key;
	$this_version = "0.1.77";
	if(empty($ayecode_ui_version) || version_compare($this_version , $ayecode_ui_version, '>')){
		$ayecode_ui_version = $this_version ;
		$ayecode_ui_file_key = wp_hash( __FILE__ );
	}
},0);

/**
 * Load this version of WP Bootstrap Settings only if the file hash is the current one.
 */
add_action('after_setup_theme', function () {
	global $ayecode_ui_file_key;
	if($ayecode_ui_file_key && $ayecode_ui_file_key == wp_hash( __FILE__ )){
		include_once( dirname( __FILE__ ) . '/includes/class-aui.php' );
		include_once( dirname( __FILE__ ) . '/includes/ayecode-ui-settings.php' );
	}
},1);

/**
 * Add the function that calls the class.
 */
if(!function_exists('aui')){
	function aui(){
		if(!class_exists("AUI",false)){
			return false;
		}
		return AUI::instance();
	}
}
