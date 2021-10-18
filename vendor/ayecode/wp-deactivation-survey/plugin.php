<?php
/**
 * This is a file to run this class for testing.
 *
 * @wordpress-plugin
 * Plugin Name: AyeCode Deactivation Survey Testing
 * Description: If you see this as a plugin something has gone wrong.
 * Version: 1.0.4
 * Author: AyeCode
 * Author URI: https://ayecode.io
 * Text Domain: ayecode-ds
 * Domain Path: /languages
 * Requires at least: 4.2
 * Tested up to: 5.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'AyeCode_Deactivation_Survey' ) ) {
	// include the class if needed
	include_once( dirname( __FILE__ ) . "/wp-deactivation-survey.php" );
}


//add_filter('ayecode_deactivation_survey_plugins', function($plugins) {
//
//	$plugins[] = (object)array(
//		'slug'		=> 'ayecode-deactivation-survey-testing',
//		'version'	=> '1.0.0'
//	);
//
//	return $plugins;
//
//});

AyeCode_Deactivation_Survey::instance(array(
	'slug'		=> 'ayecode-deactivation-survey-testing',
	'version'	=> '1.0.0'
));