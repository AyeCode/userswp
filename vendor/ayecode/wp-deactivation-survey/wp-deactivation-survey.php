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

		public $version = "1.0.7";

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
				'quick_feedback'			=> __( 'Quick Feedback', 'ayecode-connect' ),
				'foreword'					=> __( 'If you would be kind enough, please tell us why you\'re deactivating?', 'ayecode-connect' ),
				'better_plugins_name'		=> __( 'Please tell us which plugin?', 'ayecode-connect' ),
				'please_tell_us'			=> __( 'Please tell us the reason so we can improve the plugin', 'ayecode-connect' ),
				'do_not_attach_email'		=> __( 'Do not send my e-mail address with this feedback', 'ayecode-connect' ),
				'brief_description'			=> __( 'Please give us any feedback that could help us improve', 'ayecode-connect' ),
				'cancel'					=> __( 'Cancel', 'ayecode-connect' ),
				'skip_and_deactivate'		=> __( 'Skip &amp; Deactivate', 'ayecode-connect' ),
				'submit_and_deactivate'		=> __( 'Submit &amp; Deactivate', 'ayecode-connect' ),
				'please_wait'				=> __( 'Please wait', 'ayecode-connect' ),
				'get_support'				=> __( 'Get Support', 'ayecode-connect' ),
				'documentation'				=> __( 'Documentation', 'ayecode-connect' ),
				'thank_you'					=> __( 'Thank you!', 'ayecode-connect' ),
			));

			// Plugins
			$plugins = apply_filters('ayecode_deactivation_survey_plugins', self::$plugins);

			// Reasons
			$defaultReasons = array(
				'suddenly-stopped-working'	=> __( 'The plugin suddenly stopped working', 'ayecode-connect' ),
				'plugin-broke-site'			=> __( 'The plugin broke my site', 'ayecode-connect' ),
				'plugin-setup-difficult'	=> __( 'Too difficult to setup', 'ayecode-connect' ),
				'plugin-design-difficult'	=> __( 'Too difficult to get the design i want', 'ayecode-connect' ),
				'no-longer-needed'			=> __( 'I don\'t need this plugin any more', 'ayecode-connect' ),
				'found-better-plugin'		=> __( 'I found a better plugin', 'ayecode-connect' ),
				'temporary-deactivation'	=> __( 'It\'s a temporary deactivation, I\'m troubleshooting', 'ayecode-connect' ),
				'other'						=> __( 'Other', 'ayecode-connect' ),
			);

			foreach( $plugins as $plugin ) {
				$plugin->reasons = apply_filters( 'ayecode_deactivation_survey_reasons', $defaultReasons, $plugin );
				$plugin->url = home_url();
				$plugin->activated = 0;
			}

			// Send plugin data
			wp_localize_script('ayecode-deactivation-survey', 'ayecodeds_deactivate_feedback_form_plugins', $plugins);
		}
	}

}