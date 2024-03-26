<?php
/*
Plugin Name: AyeCode UI
Plugin URI: https://ayecode.io/
Description: This is an example plugin to test AyeCode UI Quickly.
Version: 1.0.0
Author: AyeCode Ltd
Author URI: https://userswp.io
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt
Text Domain: ayecode-ui
Domain Path: /languages
Requires at least: 4.9
Tested up to: 5.4
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class AyeCode_UI_Plugin {

	/**
	 * AUI Plugin constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// load AUI
		require_once( dirname( __FILE__ ) . '/ayecode-ui-loader.php' );

		// Maybe show example page
		add_action( 'template_redirect', array( $this,'maybe_show_examples' ) );
	}

	public function maybe_show_examples(){
		if ( current_user_can( 'manage_options' ) && isset( $_REQUEST['preview-aui'] ) ) {
			echo "<head>";
			wp_head();
			echo "</head>";
			echo "<body class='bsui'>";
			echo $this->get_examples(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			wp_footer();
			echo "</body>";
			exit;
		}
	}

	public function get_examples(){
		$output = '';

		// open form
		$output .= "<form class='p-5 m-5 border rounded bg-white'>";

		$output .= aui()->input(
			array(
				'type'             => 'datepicker',
				'id'               => 'date_example_sm',
				'size'             => 'sm',
				'name'             => 'date_example_sm',
				'label'            => 'Date Input Label (small)',
				'help_text'        => 'help text',
				'label_type'       => 'top',
				'placeholder'      => 'YYYY-MM-DD 00:00',
				'value'            => '',
				'extra_attributes' => array(
					'data-enable-time' => 'true',
					'data-time_24hr'   => 'true',
					'data-allow-input' => 'true',
				),
			)
		);

		$output .= aui()->input(
			array(
				'type'             => 'datepicker',
				'id'               => 'date_example',
				//'size'             => 'smx',
				'name'             => 'date_example',
				'label'            => 'Date Input Label',
				'help_text'        => 'help text',
				'label_type'       => 'top',
				'placeholder'      => 'YYYY-MM-DD 00:00',
				'value'            => '',
				'extra_attributes' => array(
					'data-enable-time' => 'true',
					'data-time_24hr'   => 'true',
					'data-allow-input' => 'true',
				),
			)
		);

		$output .= aui()->input(
			array(
				'type'             => 'datepicker',
				'id'               => 'date_example_lg',
				'size'             => 'lg',
				'name'             => 'date_example_lg',
				'label'            => 'Date Input Label (large)',
				'help_text'        => 'help text',
				'label_type'       => 'top',
				'placeholder'      => 'YYYY-MM-DD 00:00',
				'value'            => '',
				'extra_attributes' => array(
					'data-enable-time' => 'true',
					'data-time_24hr'   => 'true',
					//'data-allow-input' => 'true',
				),
			)
		);

		// input example
		$output .= aui()->input(
			array(
				'type'  =>  'text',
				'id'    =>  'text-example',
				'size'             => 'sm',
				//'clear_icon'    => true,
				'name'    =>  'text-example',
				'placeholder'   => 'text placeholder',
				'title'   => 'Text input example',
				'value' =>  '',
				'required'  => false,
				'help_text' => 'help text',
				'label' => 'Text input example label',
				'label_type' => 'top'
			)
		);

		$output .= aui()->input(
			array(
				'type'  =>  'search',
				'id'    =>  'text-example',
				'size'             => 'sm',
				//'clear_icon'    => true,
				'name'    =>  'text-example',
				'placeholder'   => 'text placeholder',
				'title'   => 'Text input example',
				'value' =>  '',
				'required'  => false,
				'help_text' => 'help text',
				'label' => 'Text input example label',
				'label_type' => 'top'
			)
		);

		// input example
		$output .= aui()->input(
			array(
				'type'  =>  'url',
				'id'    =>  'text-example2',
				'name'    =>  'text-example',
				'placeholder'   => 'url placeholder',
				'title'   => 'Text input example',
				'value' =>  '',
				'required'  => false,
				'help_text' => 'help text',
				'label' => 'Text input example label'
			)
		);

		// checkbox example
		$output .= aui()->input(
			array(
				'type'  =>  'checkbox',
				'id'    =>  'checkbox-example',
				'name'    =>  'checkbox-example',
				'placeholder'   => 'checkbox-example',
				'title'   => 'Checkbox example',
				'value' =>  '1',
				'checked'   => true,
				'required'  => false,
				'help_text' => 'help text',
				'label' => 'Checkbox checked'
			)
		);

		// checkbox example
		$output .= aui()->input(
			array(
				'type'  =>  'checkbox',
				'id'    =>  'checkbox-example2',
				'name'    =>  'checkbox-example2',
				'placeholder'   => 'checkbox-example',
				'title'   => 'Checkbox example',
				'value' =>  '1',
				'checked'   => false,
				'required'  => false,
				'help_text' => 'help text',
				'label' => 'Checkbox un-checked'
			)
		);

		// switch example
		$output .= aui()->input(
			array(
				'type'  =>  'checkbox',
				'id'    =>  'switch-example',
				'name'    =>  'switch-example',
				'placeholder'   => 'checkbox-example',
				'title'   => 'Switch example',
				'value' =>  '1',
				'checked'   => true,
				'switch'    => true,
				'required'  => false,
				'help_text' => 'help text',
				'label' => 'Switch on'
			)
		);

		// switch example
		$output .= aui()->input(
			array(
				'type'  =>  'checkbox',
				'id'    =>  'switch-example2',
				'name'    =>  'switch-example2',
				'placeholder'   => 'checkbox-example',
				'title'   => 'Switch example',
				'value' =>  '1',
				'checked'   => false,
				'switch'    => true,
				'required'  => false,
				'help_text' => 'help text',
				'label' => 'Switch off'
			)
		);

		// close form
		$output .= "</form>";

		return $output;
	}
}

new AyeCode_UI_Plugin();