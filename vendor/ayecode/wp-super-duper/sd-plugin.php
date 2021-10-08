<?php
/**
 * This is a Hello World test plugin for WP Super Duper Class.
 *
 * @wordpress-plugin
 * Plugin Name: Super Duper - Examples
 * Description: This is a Hello World test plugin for WP Super Duper Class.
 * Version: 1.0.6
 * Author: AyeCode
 * Author URI: https://ayecode.io
 * Text Domain: super-duper
 * Domain Path: /languages
 * Requires at least: 4.2
 * Tested up to: 5.3
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
