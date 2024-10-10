<?php
/**
 * This is a Hello World test plugin for WP Super Duper Class.
 *
 * @wordpress-plugin
 * Plugin Name: Super Duper - Examples
 * Description: This is a Hello World test plugin for WP Super Duper Class.
 * Version: 1.2.13
 * Author: AyeCode
 * Author URI: https://ayecode.io
 * Text Domain: super-duper
 * Domain Path: /languages
 * Requires at least: 4.5
 * Tested up to: 6.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_Super_Duper' ) ) {
	// include the class if needed
	include_once( dirname( __FILE__ ) . "/wp-super-duper.php" );
}

/*
 * Hello world example.
 */
include_once( dirname( __FILE__ ) . "/hello-world.php" );

/*
 * Map example.
 */
include_once( dirname( __FILE__ ) . "/map.php" );


if ( ! function_exists( 'sd_get_class_build_keys' ) ) {
	include_once( dirname( __FILE__ ) . "/sd-functions.php" );
}