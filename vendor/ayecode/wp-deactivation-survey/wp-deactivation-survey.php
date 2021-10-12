<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'AyeCode_Deactivation_Survey' ) ) {

	class AyeCode_Deactivation_Survey {

		/**
		 * AyeCode_Deactivation_Survey instance.
		 *
		 * @access private
		 * @since  1.0.0
		 * @var    AyeCode_Deactivation_Survey There can be only one!
		 */
		private static $instance = null;

		public static $plugins;

		public $version = "1.0.4";

		public static function instance( $plugin = array() ) {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof AyeCode_Deactivation_Survey ) ) {
				self::$instance = new AyeCode_Deactivation_Survey;
				self::$plugins = array();

				add_action( 'admin_enqueue_scripts', array( self::$instance, 'scripts' ) );

				do_action( 'ayecode_deactivation_survey_loaded' );
			}

			if(!empty($plugin)){
				self::$plugins[] = (object)$plugin;
			}

			return self::$instance;
		}

		public function scripts() {
			global $pagenow;

			// Bail if we are not on the plugins page
			if ( $pagenow != "plugins.php" ) {
				return;
			}

			// Enqueue scripts
			add_thickbox();
			wp_enqueue_script('ayecode-deactivation-survey', plugin_dir_url(__FILE__) . 'ayecode-ds.js');

			/*
			 * Localized strings. Strings can be localised by plugins using this class.
			 * We deliberately don't add textdomains here so that double textdomain warning is not given in theme review.
			 */
			wp_localize_script('ayecode-deactivation-survey', 'ayecodeds_deactivate_feedback_form_strings', array(
				'quick_feedback'			=> 'Quick Feedback',
				'foreword'					=> 'If you would be kind enough, please tell us why you\'re deactivating?',
				'better_plugins_name'		=> 'Please tell us which plugin?',
				'please_tell_us'			=> 'Please tell us the reason so we can improve the plugin',
				'do_not_attach_email'		=> 'Do not send my e-mail address with this feedback',
				'brief_description'			=> 'Please give us any feedback that could help us improve',
				'cancel'					=> 'Cancel',
				'skip_and_deactivate'		=> 'Skip &amp; Deactivate',
				'submit_and_deactivate'		=> 'Submit &amp; Deactivate',
				'please_wait'				=> 'Please wait',
				'get_support'				=> 'Get Support',
				'documentation'				=> 'Documentation',
				'thank_you'					=> 'Thank you!',
			));

			// Plugins
			$plugins = apply_filters('ayecode_deactivation_survey_plugins', self::$plugins);

			// Reasons
			$defaultReasons = array(
				'suddenly-stopped-working'	=> 'The plugin suddenly stopped working',
				'plugin-broke-site'			=> 'The plugin broke my site',
				'plugin-setup-difficult'	=> 'Too difficult to setup',
				'plugin-design-difficult'	=> 'Too difficult to get the design i want',
				'no-longer-needed'			=> 'I don\'t need this plugin any more',
				'found-better-plugin'		=> 'I found a better plugin',
				'temporary-deactivation'	=> 'It\'s a temporary deactivation, I\'m troubleshooting',
				'other'						=> 'Other',
			);

			foreach($plugins as $plugin)
			{
				$plugin->reasons = apply_filters('ayecode_deactivation_survey_reasons', $defaultReasons, $plugin);
				$plugin->url = home_url();
				$plugin->activated = 0;
			}

			// Send plugin data
			wp_localize_script('ayecode-deactivation-survey', 'ayecodeds_deactivate_feedback_form_plugins', $plugins);

		}
		

	}

}