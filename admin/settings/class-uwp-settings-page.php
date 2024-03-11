<?php
/**
 * UsersWP Settings Page/Tab
 *
 * @author      AyeCode
 * @category    Admin
 * @package     userswp/admin
 * @version     1.0.24
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'UsersWP_Settings_Page', false ) ) :

/**
 * UsersWP_Settings_Page.
 */
abstract class UsersWP_Settings_Page {

	/**
	 * Setting page id.
	 *
	 * @var string
	 */
	protected $id = '';

	/**
	 * Setting page label.
	 *
	 * @var string
	 */
	protected $label = '';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'uwp_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'uwp_sections_' . $this->id, array( $this, 'output_toggle_advanced' ) );
		add_action( 'uwp_sections_' . $this->id, array( $this, 'output_sections' ) );
		add_action( 'uwp_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'uwp_settings_save_' . $this->id, array( $this, 'save' ) );

	}

	/**
	 * Get settings page ID.
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get settings page label.
	 * @return string
	 */
	public function get_label() {
		return $this->label;
	}

	/**
	 * Add this page to settings.
	 */
	public function add_settings_page( $pages ) {
		$pages[ $this->id ] = $this->label;

		return $pages;
	}

	/**
	 * Get settings array.
	 *
	 * @return array
	 */
	public function get_settings($current_section = '') {
		return apply_filters( 'uwp_get_settings_' . $this->id, array() );
	}

	/**
	 * Get sections.
	 *
	 * @return array
	 */
	public function get_sections() {
		return apply_filters( 'uwp_get_sections_' . $this->id, array() );
	}

	/**
	 * Detect if the advanced settings button should be shown or not.
	 *
	 * @return bool
	 */
	public function show_advanced(){
		global $current_section;
		$show = false;
		$settings = $this->get_settings($current_section);

		if(!empty($settings)){
			foreach($settings as $setting){
				if(isset($setting['advanced']) && $setting['advanced']){
					$show = true;
					break;
				}
			}
		}

		return $show;
	}

	/**
	 * Output the toggle show/hide advanced settings.
	 */
	public function output_toggle_advanced(){
		global $hide_advanced_toggle;

		if($hide_advanced_toggle){ return;}

		// check if we need to show advanced or not
		if(!$this->show_advanced()){return;}


		$this->toggle_advanced_button();

	}

    /**
     * Toggle advanced button.
     *
     */
	public static function toggle_advanced_button(){

		$show = uwp_get_option( 'admin_disable_advanced', false );

		if($show){return;} // don't show advanced toggle

		$text_show = __("Show Advanced","userswp");
		$text_hide = __("Hide Advanced","userswp");

		if(!$show){
			$toggle_CSS = '';
		}else{
			$toggle_CSS = 'uwpa-hide';
		}
		?>
		<style>
			.uwp-advanced-setting{display: none;}
			.uwp-advanced-setting.uwpa-show{display: block;}
			tr.uwp-advanced-setting.uwpa-show{display: table-row;}
			li.uwp-advanced-setting.uwpa-show{display: list-item;}
			/* Show Advanced */
			.uwp-advanced-toggle .uwpat-text-show {display: block;}
			.uwp-advanced-toggle .uwpat-text-hide {display: none;}

			/* Hide Advanced */
			.uwp-advanced-toggle.uwpa-hide .uwpat-text-show {display: none;}
			.uwp-advanced-toggle.uwpa-hide .uwpat-text-hide {display: block;}
		</style>

		<?php

		echo "<button class='button-primary uwp-advanced-toggle " . esc_attr( $toggle_CSS ) . "' type=\"button\"  >";
		echo "<span class='uwpat-text-show'>" . esc_html( $text_show ) . "</span>";
		echo "<span class='uwpat-text-hide'>" . esc_html( $text_hide ) . "</span>";
		echo "</button>";

		?>
		<script>
			uwp_init_advanced_settings();
		</script>
		<?php
	}

	/**
	 * Output sections.
	 */
	public function output_sections() {
		global $current_section;

		$sections = $this->get_sections();

		if ( empty( $sections ) || 1 === sizeof( $sections ) ) {
			return;
		}

		echo '<ul class="subsubsub">';

		$array_keys = array_keys( $sections );

		foreach ( $sections as $id => $label ) {
			echo '<li><a href="' . esc_attr( admin_url( 'admin.php?page=userswp&tab=' . $this->id . '&section=' . sanitize_title( $id ) ) ) . '" class="' . ( $current_section == $id ? 'current' : '' ) . '">' . esc_html( $label ) . '</a> ' . ( end( $array_keys ) == $id ? '' : '|' ) . ' </li>';
		}

		echo '</ul><br class="clear" />';
	}

	/**
	 * Output the settings.
	 */
	public function output() {
		$settings = $this->get_settings();

		UsersWP_Admin_Settings::output_fields( $settings );
	}

	/**
	 * Save settings.
	 */
	public function save() {
		global $current_section;

		$settings = $this->get_settings();
		UsersWP_Admin_Settings::save_fields( $settings );

		if ( $current_section ) {
			do_action( 'uwp_update_options_' . $this->id . '_' . $current_section );
		}
	}
}

endif;
