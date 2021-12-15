<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @since      1.0.0
 * @author     GeoDirectory Team <info@wpgeodirectory.com>
 */
final class UsersWP {

	public $profile;
	public $forms;
	public $notices;
	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */
	protected $version;
	protected $i18n;
	protected $templates;
	protected $meta;
	protected $pages;
	protected $files;
	protected $shortcodes;
	protected $assets;
	protected $admin;
	protected $menus;
	protected $form_builder;
	protected $ajax;
	protected $tools;
	protected $tables;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->plugin_name = USERSWP_NAME;
		$this->version     = USERSWP_VERSION;


		$this->load_dependencies();
		$this->init_hooks();

		$this->meta          = new UsersWP_Meta();
		$this->pages         = new UsersWP_Pages();
		$this->profile       = new UsersWP_Profile();
		$this->forms         = new UsersWP_Forms();
		$this->templates     = new UsersWP_Templates();
		$this->notices       = new UsersWP_Notices();
		$this->assets        = new UsersWP_Public();
		$this->form_builder  = new UsersWP_Form_Builder();
		$this->menus         = new UsersWP_Menus();
		$this->tools         = new UsersWP_Tools();
		$this->tables        = new UsersWP_Tables();
		$this->admin         = new UsersWP_Admin();
		$this->admin_menus   = new UsersWP_Admin_Menus();
		$this->ajax          = new UsersWP_Ajax();
		$this->files         = new UsersWP_Files();
		$this->notifications = new UsersWP_Notifications();

		// actions and filters
		$this->load_assets_actions_and_filters( $this->assets );
		$this->load_meta_actions_and_filters( $this->meta );
		$this->load_files_actions_and_filters( $this->files );
		$this->load_forms_actions_and_filters( $this->forms );
		$this->load_notices_actions_and_filters( $this->notices );
		$this->load_pages_actions_and_filters( $this->pages );
		$this->load_profile_actions_and_filters( $this->profile );
		$this->load_tables_actions_and_filters( $this->tables );
		$this->load_templates_actions_and_filters( $this->templates );
		$this->load_tools_actions_and_filters( $this->tools );
		$this->load_notifications_actions_and_filters( $this->notifications );

		//admin
		$this->load_form_builder_actions_and_filters( $this->form_builder );
		$this->load_menus_actions_and_filters( $this->menus );

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {
		global $uwp_options;

		if ( ! function_exists( 'is_plugin_active' ) ) {
			/**
			 * Load all plugin functions from WordPress.
			 */
			require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
		}

		require_once dirname( dirname( __FILE__ ) ) . '/admin/settings/functions.php';

		$uwp_options = uwp_get_settings();

		require_once dirname( dirname( __FILE__ ) ) . '/admin/settings/class-uwp-font-awesome-settings.php';

		require_once( dirname( dirname( __FILE__ ) ) . '/upgrade.php' );

		/**
		 * The class responsible for activation functionality
		 * of the plugin.
		 */
		require_once dirname( dirname( __FILE__ ) ) . '/includes/class-activator.php';

		/**
		 * The libraries required.
		 */
		require_once dirname( dirname( __FILE__ ) ) . '/vendor/autoload.php';

		/**
		 * Contains functions for templates.
		 */
		require_once( dirname( dirname( __FILE__ ) ) . '/includes/template-functions.php' );

		/**
		 * The class responsible for sending emails
		 * of the plugin.
		 */
		require_once dirname( dirname( __FILE__ ) ) . '/includes/class-emails.php';

		/**
		 * The class responsible for reading and updating meta
		 * of the plugin.
		 */
		require_once dirname( dirname( __FILE__ ) ) . '/includes/class-meta.php';

		/**
		 * The class responsible for userswp dates
		 * of the plugin.
		 */
		require_once dirname( dirname( __FILE__ ) ) . '/includes/class-date.php';

		/**
		 * The class responsible for userswp pages
		 * of the plugin.
		 */
		require_once dirname( dirname( __FILE__ ) ) . '/includes/class-pages.php';

		/**
		 * The class responsible for uploading files
		 * of the plugin.
		 */
		require_once dirname( dirname( __FILE__ ) ) . '/includes/class-files.php';

		/**
		 * The class responsible for defining form handler functionality
		 * of the plugin.
		 */
		require_once dirname( dirname( __FILE__ ) ) . '/includes/class-forms.php';

		/**
		 * The class responsible for form validation
		 * of the plugin.
		 */
		require_once dirname( dirname( __FILE__ ) ) . '/includes/class-validation.php';

		/**
		 * Country helpers
		 */
		require_once dirname( dirname( __FILE__ ) ) . '/includes/class-countries.php';

		/**
		 * The class responsible for defining ajax handler functionality
		 * of the plugin.
		 */
		require_once dirname( dirname( __FILE__ ) ) . '/includes/class-ajax.php';

		/**
		 * The class responsible for defining all shortcodes
		 */
		require_once dirname( dirname( __FILE__ ) ) . '/includes/class-templates.php';

		/**
		 * The class responsible for defining profile content
		 */
		require_once dirname( dirname( __FILE__ ) ) . '/includes/class-profile.php';

		/**
		 * The class responsible for defining all menus items.
		 */
		require_once dirname( dirname( __FILE__ ) ) . '/admin/menus/class-checklist.php';

		/**
		 * The class responsible for defining all menus in the admin area.
		 */
		require_once dirname( dirname( __FILE__ ) ) . '/admin/menus/class-menus.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once dirname( dirname( __FILE__ ) ) . '/admin/class-admin.php';

		/**
		 * The class responsible for defining all actions that occur for setup wizard.
		 */
		if ( isset( $_GET['page'] ) && 'uwp-setup' == $_GET['page'] ) {
			require_once dirname( dirname( __FILE__ ) ) . '/admin/class-admin-setup-wizard.php';
		}

		/**
		 * The class responsible for defining all actions help screen.
		 */
		require_once dirname( dirname( __FILE__ ) ) . '/admin/class-uwp-admin-help.php';

		/**
		 * The class responsible for defining all menus in the admin area.
		 */
		require_once dirname( dirname( __FILE__ ) ) . '/admin/class-uwp-admin-menus.php';

		/**
		 * The class responsible for defining all admin area settings.
		 */
		require_once dirname( dirname( __FILE__ ) ) . '/admin/settings/class-settings.php';

		/**
		 * The class responsible for default content.
		 */
		require_once dirname( dirname( __FILE__ ) ) . '/includes/class-uwp-defaults.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once dirname( dirname( __FILE__ ) ) . '/public/class-public.php';

		/**
		 * The class responsible for table functions
		 */
		require_once dirname( dirname( __FILE__ ) ) . '/includes/class-tables.php';

		/**
		 * The class responsible for admin settings functions
		 */
		include_once dirname( dirname( __FILE__ ) ) . '/admin/settings/class-uwp-settings-page.php';

		/**
		 * The class responsible for adding fields in forms
		 */
		require_once dirname( dirname( __FILE__ ) ) . '/admin/settings/class-formbuilder.php';

		/**
		 * The class responsible for defining all admin area settings.
		 */
		require_once dirname( dirname( __FILE__ ) ) . '/admin/settings/class-uwp-settings-profile-tabs.php';

		/**
		 * The class responsible for user sorting builder.
		 */
		require_once dirname( dirname( __FILE__ ) ) . '/admin/settings/class-uwp-settings-user-sorting.php';

		/**
		 * The class responsible for adding tools functions
		 */
		require_once dirname( dirname( __FILE__ ) ) . '/includes/class-tools.php';

		/**
		 * The class responsible for displaying notices
		 */
		require_once dirname( dirname( __FILE__ ) ) . '/includes/class-notices.php';

		/**
		 * contents helpers files and functions.
		 */
		require_once( dirname( dirname( __FILE__ ) ) . '/includes/helpers.php' );

		/**
		 * The class for login widget.
		 */
		require_once( dirname( dirname( __FILE__ ) ) . '/widgets/login.php' );

		/**
		 * The class for register widget.
		 */
		require_once( dirname( dirname( __FILE__ ) ) . '/widgets/register.php' );

		/**
		 * The class for forgot password widget.
		 */
		require_once( dirname( dirname( __FILE__ ) ) . '/widgets/forgot.php' );

		/**
		 * The class for reset password widget.
		 */
		require_once( dirname( dirname( __FILE__ ) ) . '/widgets/reset.php' );

		/**
		 * The class for change password widget.
		 */
		require_once( dirname( dirname( __FILE__ ) ) . '/widgets/change.php' );

		/**
		 * The class for users widget.
		 */
		require_once( dirname( dirname( __FILE__ ) ) . '/widgets/users.php' );

		/**
		 * The class for users item widget.
		 */
		require_once( dirname( dirname( __FILE__ ) ) . '/widgets/users-item.php' );

		/**
		 * The class for account widget.
		 */
		require_once( dirname( dirname( __FILE__ ) ) . '/widgets/account.php' );

		/**
		 * The class for profile widget.
		 */
		require_once( dirname( dirname( __FILE__ ) ) . '/widgets/profile.php' );

		/**
		 * The class for profile sections widget.
		 */
		require_once( dirname( dirname( __FILE__ ) ) . '/widgets/profile-section.php' );

		/**
		 * The class profile header widget
		 */
		require_once dirname( dirname( __FILE__ ) ) . '/widgets/profile-header.php';

		/**
		 * The class for user title widget.
		 */
		require_once( dirname( dirname( __FILE__ ) ) . '/widgets/user-title.php' );

		/**
		 * The class for user avatar widget.
		 */
		require_once( dirname( dirname( __FILE__ ) ) . '/widgets/user-avatar.php' );

		/**
		 * The class for user post count widget.
		 */
		require_once( dirname( dirname( __FILE__ ) ) . '/widgets/user-post-counts.php' );

		/**
		 * The class for user cover widget.
		 */
		require_once( dirname( dirname( __FILE__ ) ) . '/widgets/user-cover.php' );

		/**
		 * The class for profile social fields widget.
		 */
		require_once( dirname( dirname( __FILE__ ) ) . '/widgets/profile-social.php' );

		/**
		 * The class for profile action buttons fields widget.
		 */
		require_once( dirname( dirname( __FILE__ ) ) . '/widgets/profile-actions.php' );

		/**
		 * The class for profile buttons fields widget.
		 */
		require_once( dirname( dirname( __FILE__ ) ) . '/widgets/user-actions.php' );

		/**
		 * The class for profile content widget.
		 */
		require_once( dirname( dirname( __FILE__ ) ) . '/widgets/profile-tabs.php' );

		/**
		 * The class for user meta widget.
		 */
		require_once( dirname( dirname( __FILE__ ) ) . '/widgets/user-meta.php' );

		/**
		 * The class for users search widget.
		 */
		require_once( dirname( dirname( __FILE__ ) ) . '/widgets/users-search.php' );

		/**
		 * The class for user list sorting widget.
		 */
		require_once( dirname( dirname( __FILE__ ) ) . '/widgets/users-loop-actions.php' );

		/**
		 * The class for users list widget.
		 */
		require_once( dirname( dirname( __FILE__ ) ) . '/widgets/users-loop.php' );

		/**
		 * The class for output location widget.
		 */
		require_once( dirname( dirname( __FILE__ ) ) . '/widgets/output-location.php' );

		/**
		 * The class for author box widget.
		 */
		require_once( dirname( dirname( __FILE__ ) ) . '/widgets/authorbox.php' );

		/**
		 * The class for user badge widget.
		 */
		require_once( dirname( dirname( __FILE__ ) ) . '/widgets/user-badge.php' );

		/**
		 * The class for button group widget.
		 */
		require_once( dirname( dirname( __FILE__ ) ) . '/widgets/button-group.php' );

		/**
		 * The class responsible for displaying notices
		 */
		require_once dirname( dirname( __FILE__ ) ) . '/includes/class-import-export.php';

		/**
		 * The class responsible for displaying notices
		 */
		require_once dirname( dirname( __FILE__ ) ) . '/includes/class-user-notifications.php';

		/**
		 * The class responsible for account handling
		 */
		require_once dirname( dirname( __FILE__ ) ) . '/includes/class-account.php';

		/**
		 * The class responsible for adding tools functions
		 */
		require_once dirname( dirname( __FILE__ ) ) . '/includes/class-status.php';

		/**
		 * The class responsible for extensions screen functions on admin side
		 */
		require_once dirname( dirname( __FILE__ ) ) . '/includes/libraries/class-ayecode-addons.php';

		/**
		 * The class responsible for extensions screen functions on admin side
		 */
		require_once dirname( dirname( __FILE__ ) ) . '/includes/class-addons.php';

		/**
		 * The file is responsible for defining deprecated functions.
		 */
		require_once dirname( dirname( __FILE__ ) ) . '/includes/deprecated-functions.php';

		/**
		 * The class responsible for privacy policy functions
		 */
		require_once dirname( dirname( __FILE__ ) ) . '/includes/abstract-uwp-privacy.php';
		require_once dirname( dirname( __FILE__ ) ) . '/includes/class-uwp-privacy.php';

		/**
		 * The class responsible for SEO functions
		 */
		require_once dirname( dirname( __FILE__ ) ) . '/includes/class-uwp-seo.php';

		/**
		 * The class responsible for compatibility with other themes and plugins
		 */
		require_once dirname( dirname( __FILE__ ) ) . '/includes/class-uwp-compatibility.php';

		if ( is_plugin_active( 'uwp_geodirectory/uwp_geodirectory.php' ) ) {
			deactivate_plugins( 'uwp_geodirectory/uwp_geodirectory.php' );
		}

		if ( is_plugin_active( 'geodirectory/geodirectory.php' ) || class_exists( 'GeoDirectory' ) ) {
			/**
			 * The class responsible for displaying notices
			 *
			 * @since 1.0.12
			 */
			require_once dirname( dirname( __FILE__ ) ) . '/includes/libraries/class-geodirectory-plugin.php';
		}

		if ( class_exists( 'WPInv_Plugin' ) ) {
			/**
			 * The class responsible for displaying notices
			 *
			 * @since 1.0.12
			 */
			require_once dirname( dirname( __FILE__ ) ) . '/includes/libraries/class-invoicing-plugin.php';
		}
	}

	/**
	 * Hook into actions and filters.
	 */
	private function init_hooks() {
		register_activation_hook( USERSWP_PLUGIN_FILE, array( 'UsersWP_Activator', 'activate' ) );
		add_action( 'admin_init', array( 'UsersWP_Activator', 'automatic_upgrade' ) );
		add_action( 'init', array( 'UsersWP_Activator', 'init_background_updater' ), 5 );
		add_action( 'widgets_init', array( $this, 'register_widgets' ) );
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
		add_action( 'uwp_flush_rewrite_rules', array( $this, 'flush_rewrite_rules' ) );
		add_action( 'uwp_language_file_add_string', array( $this, 'register_string' ), 10, 1 );
		add_action( 'after_setup_theme', array( $this, 'hide_admin_bar' ));
	}

	/**
	 * Actions for assets
	 *
	 * @param $instance
	 */
	public function load_assets_actions_and_filters( $instance ) {
		add_action( 'wp_enqueue_scripts', array( $instance, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $instance, 'enqueue_scripts' ) );
	}

	/**
	 * Actions for meta data
	 *
	 * @param $instance
	 */
	public function load_meta_actions_and_filters( $instance ) {
		add_action( 'user_register', array( $instance, 'sync_usermeta' ), 10, 1 );
		add_action( 'delete_user', array( $instance, 'delete_usermeta_for_user' ), 10, 1 );
		add_action( 'remove_user_from_blog', array( $instance, 'remove_user_from_blog' ), 10, 2 );
		add_action( 'wp_login', array( $instance, 'save_user_ip_on_login' ), 10, 2 );
		add_filter( 'uwp_before_extra_fields_save', array( $instance, 'save_user_ip_on_register' ), 10, 3 );
		add_filter( 'uwp_update_usermeta', array( $instance, 'modify_datepicker_value_on_update' ), 10, 3 );
		add_filter( 'uwp_get_usermeta', array( $instance, 'modify_datepicker_value_on_get' ), 10, 4 );
		add_action( 'get_user_metadata', array( $instance, 'dynamically_add_user_meta' ), 10, 4 );
	}

	public function load_files_actions_and_filters( $instance ) {
		if ( $instance->doing_upload() ) {
			add_filter( 'wp_handle_upload_prefilter', array( $instance, 'wp_media_restrict_file_types' ) );
		}
		add_filter( 'uwp_get_max_upload_size', array( $instance, 'uwp_modify_get_max_upload_size' ), 10, 2 );
	}

	/**
	 * Actions for forms
	 *
	 * @param $instance
	 */
	public function load_forms_actions_and_filters( $instance ) {

		// login
		add_action( 'wp_ajax_nopriv_uwp_ajax_login_form', array( $instance, 'ajax_login_form' ) );
		add_action( 'wp_ajax_nopriv_uwp_ajax_login', array( $instance, 'process_login' ) );
		add_action( 'wp_ajax_nopriv_uwp_ajax_login_process_2fa', array( $instance, 'process_login_2fa' ) );

		// register
		add_action( 'wp_ajax_nopriv_uwp_ajax_register_form', array( $instance, 'ajax_register_form' ) );
		add_action( 'wp_ajax_nopriv_uwp_ajax_register', array( $instance, 'process_register' ) );

		// forgot
		add_action( 'wp_ajax_nopriv_uwp_ajax_forgot_password_form', array( $instance, 'ajax_forgot_password_form' ) );
		add_action( 'wp_ajax_nopriv_uwp_ajax_forgot_password', array( $instance, 'process_forgot' ) );

		// general
		add_action( 'init', array( $instance, 'init_notices' ), 1 );
		add_action( 'uwp_loaded', array( $instance, 'handler' ) );
		add_action( 'init', array( $instance, 'privacy_submit_handler' ) );
		add_action( 'uwp_template_display_notices', array( $instance, 'display_notices' ), 10, 1 );
		add_action( 'wp_ajax_uwp_upload_file_remove', array( $instance, 'upload_file_remove' ) );
		//User search form
		add_action( 'personal_options_update', array( $instance, 'update_profile_extra_admin_edit' ), 10, 1 );
		add_action( 'edit_user_profile_update', array( $instance, 'update_profile_extra_admin_edit' ), 10, 1 );
		add_action( 'user_edit_form_tag', array( $instance, 'add_multipart_to_admin_edit_form' ) );
		add_action( 'template_redirect', array( $instance, 'process_login' ) );
		add_action( 'template_redirect', array( $instance, 'process_register' ) );
		add_action( 'template_redirect', array( $instance, 'process_account' ) );
		add_action( 'template_redirect', array( $instance, 'process_forgot' ) );
		add_action( 'template_redirect', array( $instance, 'process_change' ) );
		add_action( 'template_redirect', array( $instance, 'process_reset' ) );

		// Forms
		add_filter( 'uwp_form_input_html_datepicker', array( $instance, 'form_input_datepicker' ), 10, 4 );
		add_filter( 'uwp_form_input_html_time', array( $instance, 'form_input_time' ), 10, 4 );
		add_filter( 'uwp_form_input_html_select', array( $instance, 'form_input_select' ), 10, 4 );
		add_filter( 'uwp_form_input_html_multiselect', array( $instance, 'form_input_multiselect' ), 10, 4 );
		add_filter( 'uwp_form_input_html_text', array( $instance, 'form_input_text' ), 10, 4 );
		add_filter( 'uwp_form_input_html_textarea', array( $instance, 'form_input_textarea' ), 10, 4 );
		add_filter( 'uwp_form_input_html_editor', array( $instance, 'form_input_editor' ), 10, 4 );
		add_filter( 'uwp_form_input_html_fieldset', array( $instance, 'form_input_fieldset' ), 10, 4 );
		add_filter( 'uwp_form_input_html_file', array( $instance, 'form_input_file' ), 10, 4 );
		add_filter( 'uwp_form_input_html_checkbox', array( $instance, 'form_input_checkbox' ), 10, 4 );
		add_filter( 'uwp_form_input_html_radio', array( $instance, 'form_input_radio' ), 10, 4 );
		add_filter( 'uwp_form_input_html_url', array( $instance, 'form_input_url' ), 10, 4 );
		add_filter( 'uwp_form_input_html_email', array( $instance, 'form_input_email' ), 10, 4 );
		add_filter( 'uwp_form_input_html_password', array( $instance, 'form_input_password' ), 10, 4 );
		add_filter( 'uwp_form_input_html_checkbox_register_gdpr', array(
			$instance,
			'form_input_register_gdpr'
		), 10, 4 );
		add_filter( 'uwp_form_input_html_checkbox_register_tos', array( $instance, 'form_input_register_tos' ), 10, 4 );
		// Country select
		add_filter( 'uwp_form_input_html_select_uwp_country', array( $instance, 'form_input_select_country' ), 10, 4 );
		add_filter( 'uwp_form_input_html_phone', array( $instance, 'form_input_phone' ), 10, 4 );
		add_filter( 'uwp_form_input_email_email_after', array( $instance, 'register_confirm_email_field' ), 10, 4 );
		add_filter( 'uwp_form_input_password_password_after', array(
			$instance,
			'register_confirm_password_field'
		), 10, 4 );
		add_filter( 'uwp_form_input_html_custom_html', array( $instance, 'form_custom_html' ), 10, 4 );

		// Emails
		add_filter( 'uwp_send_mail_form_fields', array( $instance, 'init_mail_form_fields' ), 10, 4 );
	}

	/**
	 * Actions for notices
	 *
	 * @param $instance
	 */
	public function load_notices_actions_and_filters( $instance ) {
		add_action( 'uwp_template_display_notices', array( $instance, 'display_registration_disabled_notice' ) );
		add_action( 'uwp_template_display_notices', array( $instance, 'form_notice_by_key' ) );
		add_action( 'admin_notices', array( $instance, 'show_admin_notices' ) );
		add_action( 'admin_notices', array( $instance, 'try_bootstrap' ) );
		add_action( 'admin_notices', array( $instance, 'yoast_user_archives_disabled' ) );
	}

	/**
	 * Actions for pages
	 *
	 * @param $instance
	 */
	public function load_pages_actions_and_filters( $instance ) {
		add_action( 'wpmu_new_blog', array( $instance, 'wpmu_generate_default_pages_on_new_site' ), 10, 1 );
		add_filter( 'display_post_states', array( $instance, 'add_display_post_states' ), 10, 2 );
	}

	/**
	 * Actions for user profile
	 *
	 * @param $instance
	 */
	public function load_profile_actions_and_filters( $instance ) {
		add_action( 'template_redirect', array( $instance, 'redirect_author_page' ), 10, 2 );
		//profile page
		add_filter( 'query_vars', array( $instance, 'profile_query_vars' ), 10, 1 );
		add_action( 'init', array( $instance, 'rewrite_profile_link' ), 10, 1 );
		add_filter( 'author_link', array( $instance, 'get_author_link' ), 11, 2 );
		add_filter( 'edit_profile_url', array( $instance, 'modify_admin_bar_edit_profile_url' ), 10, 3 );
		add_filter( 'the_title', array( $instance, 'modify_profile_page_title' ), 10, 2 );
		add_filter( 'get_comment_author_link', array( $instance, 'get_comment_author_link' ), 10, 2 );
		add_action( 'uwp_user_title', array( $instance, 'get_profile_title' ), 10, 2 );
		add_action( 'uwp_profile_social', array( $instance, 'get_profile_social' ), 10, 2 );
		add_filter( 'get_avatar_url', array( $instance, 'get_avatar_url' ), 99, 2 );

		// Popup and crop functions
		add_filter( 'ajax_query_attachments_args', array( $instance, 'restrict_attachment_display' ) );
		add_action( 'uwp_handle_file_upload_error_checks', array(
			$instance,
			'handle_file_upload_error_checks'
		), 10, 4 );
		add_action( 'wp_ajax_uwp_avatar_banner_upload', array( $instance, 'ajax_avatar_banner_upload' ) );
		add_action( 'wp_ajax_uwp_ajax_image_crop_popup_form', array( $instance, 'ajax_image_crop_popup_form' ) );
		add_action( 'wp_ajax_uwp_ajax_profile_image_remove', array( $instance, 'ajax_profile_image_remove' ) );
		add_action( 'wp_head', array( $instance, 'define_ajaxurl' ) );
		add_action( 'uwp_profile_header', array( $instance, 'image_crop_init' ), 10, 1 );
		add_action( 'uwp_admin_profile_edit', array( $instance, 'image_crop_init' ), 10, 1 );

		// Profile Tabs
		add_action( 'uwp_profile_more_info_tab_content', array( $instance, 'get_profile_more_info' ), 10, 1 );
		add_action( 'uwp_profile_posts_tab_content', array( $instance, 'get_profile_posts' ), 10, 1 );
		add_action( 'uwp_profile_comments_tab_content', array( $instance, 'get_profile_comments' ), 10, 1 );
		add_action( 'uwp_profile_user-comments_tab_content', array( $instance, 'get_profile_user_comments' ), 10, 1 );

		// Profile Pagination
		add_action( 'uwp_profile_pagination', array( $instance, 'get_profile_pagination' ) );

		// Profile title
		add_action( 'uwp_profile_after_title', array( $instance, 'edit_profile_button' ), 10, 1 );

		// Users
		add_action( 'uwp_output_location', array( $instance, 'show_output_location_data' ), 10, 2 );
		add_action( 'wpdiscuz_profile_url', array( $instance, 'wpdiscuz_profile_url' ), 10, 2 );

		// User, allow subscribers to upload profile and banner pictures
		add_filter( 'plupload_default_params', array( $instance, 'add_uwp_plupload_param' ), 10, 1 );
		add_filter( 'user_has_cap', array( $instance, 'allow_all_users_profile_uploads' ), 10, 4 );
	}

	/**
	 * Actions for database tables
	 *
	 * @param $instance
	 */
	public function load_tables_actions_and_filters( $instance ) {
		add_filter( 'wpmu_drop_tables', array( $instance, 'drop_tables_on_delete_blog' ) );
	}

	/**
	 * Actions for templates
	 *
	 * @param $instance
	 */
	public function load_templates_actions_and_filters( $instance ) {

		add_action( 'template_redirect', array( $instance, 'change_default_password_redirect' ) );
		add_action( 'uwp_template_fields', array( $instance, 'template_fields' ), 10, 2 );
		add_action( 'uwp_template_fields', array( $instance, 'template_extra_fields' ), 10, 2 );
		add_action( 'uwp_account_form_display', array( $instance, 'privacy_edit_form_display' ), 10, 1 );
		add_action( 'wp_logout', array( $instance, 'logout_redirect' ) );
		add_action( 'init', array( $instance, 'wp_login_redirect' ) );
		add_action( 'init', array( $instance, 'wp_register_redirect' ) );
		// Redirect functions
		add_action( 'template_redirect', array( $instance, 'profile_redirect' ), 10 );
		add_action( 'template_redirect', array( $instance, 'access_checks' ), 20 );
		add_action( 'wp', array( $instance, 'redirect_templates_sub_pages' ) );
		add_action( 'wp_login', array( $instance, 'unconfirmed_login_redirect' ), 10, 2 );

		add_filter( 'wp_setup_nav_menu_item', array( $instance, 'setup_nav_menu_item' ), 10, 1 );
		add_filter( 'the_content', array( $instance, 'author_page_content' ), 10, 1 );
		add_filter( 'the_content', array( $instance, 'author_box_page_content' ), 10, 1 );
		add_filter( 'the_content', array( $instance, 'setup_singular_page_content' ), 10, 1 );
		add_filter( 'body_class', array( $instance, 'add_body_class' ), 10, 1 );

		// filter the login and register url
		add_filter( 'login_url', array( $instance, 'wp_login_url' ), 10, 3 );
		add_filter( 'register_url', array( $instance, 'wp_register_url' ), 10, 1 );
		add_filter( 'lostpassword_url', array( $instance, 'wp_lostpassword_url' ), 10, 1 );

		// Oxygen plugin
		if ( defined( 'CT_VERSION' ) ) {
			add_filter( 'uwp_get_template', array( $instance, 'oxygen_override_template' ), 11, 5 );
		}

	}

	/**
	 * Actions for tools
	 *
	 * @param $instance
	 */
	public function load_tools_actions_and_filters( $instance ) {
		add_action( 'uwp_admin_sub_menus', array( $instance, 'uwp_add_admin_tools_sub_menu' ), 100, 1 );
		add_action( 'uwp_tools_settings_main_tab_content', array( $instance, 'uwp_tools_main_tab_content' ) );
		add_action( 'wp_ajax_uwp_process_diagnosis', array( $instance, 'uwp_process_diagnosis_ajax' ) );
	}

	/**
	 * Actions for notifications
	 *
	 * @param $instance
	 */
	public function load_notifications_actions_and_filters( $instance ) {
		add_action( 'uwp_account_form_display', array( $instance, 'user_notifications_form_front' ), 10, 1 );
		add_action( 'init', array( $instance, 'notification_submit_handler' ) );
	}

	/**
	 * Actions for form builder
	 *
	 * @param $instance
	 */
	public function load_form_builder_actions_and_filters( $instance ) {
		// Actions
		add_action( 'uwp_manage_available_fields_predefined', array(
			$instance,
			'manage_available_fields_predefined'
		) );
		add_action( 'uwp_manage_available_fields_custom', array( $instance, 'manage_available_fields_custom' ) );
		add_action( 'uwp_manage_available_fields', array( $instance, 'manage_available_fields' ) );
		add_action( 'uwp_manage_selected_fields', array( $instance, 'manage_selected_fields' ) );
		add_action( 'uwp_admin_extra_custom_fields', array( $instance, 'advance_admin_custom_fields' ), 10, 2 );

		add_filter( 'uwp_before_form_builder_content', array( $instance, 'multiple_registration_form' ) );
		add_filter( 'uwp_before_available_fields', array( $instance, 'display_before_available_fields' ) );

		add_action( 'wp_ajax_uwp_ajax_register_action', array( $instance, 'register_ajax_handler' ) );
		add_action( 'wp_ajax_uwp_ajax_action', array( $instance, 'create_field' ) );
		add_action( 'uwp_form_builder_tabs_content', array( $instance, 'uwp_form_builder' ) );

		// Filters
		add_filter( 'uwp_builder_extra_fields_multiselect', array( $instance, 'builder_extra_fields_smr' ), 10, 4 );
		add_filter( 'uwp_builder_extra_fields_select', array( $instance, 'builder_extra_fields_smr' ), 10, 4 );
		add_filter( 'uwp_builder_extra_fields_radio', array( $instance, 'builder_extra_fields_smr' ), 10, 4 );
		add_filter( 'uwp_builder_extra_fields_datepicker', array(
			$instance,
			'builder_extra_fields_datepicker'
		), 10, 4 );
		add_filter( 'uwp_builder_extra_fields_password', array( $instance, 'builder_extra_fields_password' ), 10, 4 );
		add_filter( 'uwp_builder_extra_fields_email', array( $instance, 'builder_extra_fields_email' ), 10, 4 );
		add_filter( 'uwp_builder_extra_fields_file', array( $instance, 'builder_extra_fields_file' ), 10, 4 );
		add_filter( 'uwp_builder_data_type_text', array( $instance, 'builder_data_type_text' ), 10, 4 );
		add_filter( 'uwp_form_builder_available_fields_head', array(
			$instance,
			'register_available_fields_head'
		), 10, 2 );
		add_filter( 'uwp_form_builder_available_fields_note', array(
			$instance,
			'register_available_fields_note'
		), 10, 2 );
		add_filter( 'uwp_form_builder_selected_fields_head', array(
			$instance,
			'register_selected_fields_head'
		), 10, 2 );
		add_filter( 'uwp_form_builder_selected_fields_note', array(
			$instance,
			'register_selected_fields_note'
		), 10, 2 );
		// htmlvar not needed for taxonomy
		add_filter( 'uwp_builder_htmlvar_name_taxonomy', array( $instance, 'return_empty_string' ), 10, 4 );
		// default_value not needed for textarea, html, file, fieldset
		add_filter( 'uwp_builder_default_value_textarea', array( $instance, 'return_empty_string' ), 10, 4 );
		add_filter( 'uwp_builder_default_value_html', array( $instance, 'return_empty_string' ), 10, 4 );
		add_filter( 'uwp_builder_default_value_file', array( $instance, 'return_empty_string' ), 10, 4 );
		add_filter( 'uwp_builder_default_value_fieldset', array( $instance, 'return_empty_string' ), 10, 4 );
		// is_required not needed for fieldset
		add_filter( 'uwp_builder_is_required_fieldset', array( $instance, 'return_empty_string' ), 10, 4 );
		add_filter( 'uwp_builder_required_msg_fieldset', array( $instance, 'return_empty_string' ), 10, 4 );
		// field_icon not needed for fieldset
		add_filter( 'uwp_builder_css_class_fieldset', array( $instance, 'return_empty_string' ), 10, 4 );
		// filters for which is_public not required
		add_filter( 'uwp_builder_is_public_password', array( $instance, 'return_empty_string' ), 10, 4 );
		add_filter( 'uwp_builder_validation_pattern_text', array( $instance, 'validation_pattern' ), 10, 4 );
		add_filter( 'uwp_builder_validation_pattern_email', array( $instance, 'validation_pattern' ), 10, 4 );
		add_filter( 'uwp_builder_validation_pattern_phone', array( $instance, 'validation_pattern' ), 10, 4 );
		add_filter( 'uwp_builder_validation_pattern_url', array( $instance, 'validation_pattern' ), 10, 4 );
	}

	/**
	 * Actions for admin menus
	 *
	 * @param $instance
	 */
	public function load_menus_actions_and_filters( $instance ) {
		add_action( 'load-nav-menus.php', array( $instance, 'users_wp_admin_menu_metabox' ) );
		add_action( 'admin_bar_menu', array( $instance, 'admin_bar_menu' ), 51 );
	}

	/**
	 * Registers an individual text string for WPML translation.
	 *
	 * @since 1.2.2
	 *
	 * @param string $string The string that needs to be translated.
	 * @param string $domain The plugin domain. Default userswp.
	 * @param string $name   The name of the string which helps to know what's being translated.
	 */
	public static function register_string( $string, $domain = 'userswp', $name = '' ) {
		do_action( 'wpml_register_single_string', $domain, $name, $string );
	}

	/**
	 * Register widgets
	 *
	 */
	public function register_widgets() {
		global $pagenow;

		$block_widget_init_screens = function_exists('sd_pagenow_exclude') ? sd_pagenow_exclude() : array();

		if ( is_admin() && $pagenow && in_array($pagenow, $block_widget_init_screens)) {
			// don't initiate in these conditions.
		}else{

			$exclude = function_exists('sd_widget_exclude') ? sd_widget_exclude() : array();
			$widgets = $this->get_widgets();

			if( !empty($widgets) ){
				foreach ( $widgets as $widget ) {
					if(!in_array($widget,$exclude)){
						// SD V1 used to extend the widget class. V2 does not, so we cannot call register widget on it.
						if ( is_subclass_of( $widget, 'WP_Widget' ) ) {
							register_widget( $widget );
						} else {
							new $widget();
						}
					}
				}
			}
		}
	}

	public function get_widgets(){
		$widgets = array(
			'UWP_Register_Widget',
			'UWP_Forgot_Widget',
			'UWP_Login_Widget',
			'UWP_Change_Widget',
			'UWP_Reset_Widget',
			'UWP_Users_Widget',
			'UWP_Users_Item_Widget',
			'UWP_Account_Widget',
			'UWP_Profile_Widget',
			'UWP_Profile_Header_Widget',
			'UWP_Profile_Social_Widget',
			'UWP_Profile_Tabs_Widget',
			'UWP_Profile_Actions_Widget',
			'UWP_Profile_Section_Widget',
			'UWP_User_Title_Widget',
			'UWP_User_Avatar_Widget',
			'UWP_User_Cover_Widget',
			'UWP_User_Post_Counts_Widget',
			'UWP_User_Meta_Widget',
			'UWP_Users_Search_Widget',
			'UWP_Users_Loop_Actions',
			'UWP_Users_Loop_Widget',
			'UWP_User_Actions_Widget',
			'UWP_Output_Location_Widget',
			'UWP_Author_Box_Widget',
			'UWP_Button_Group_Widget',
			'UWP_User_Badge_Widget',
		);

		return apply_filters('uwp_get_widgets', $widgets );
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since       1.0.0
	 * @package     userswp
	 * @return      void
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain( 'userswp', false, basename( dirname( dirname( __FILE__ ) ) ) . '/languages' );

		do_action( 'uwp_loaded' );

	}

	/**
	 * Flush rewrite rules.
	 *
	 * @return      void
	 */
	public function flush_rewrite_rules() {
		flush_rewrite_rules();
	}

	/**
	 * Hide admin bar based on settings.
	 */
	public function hide_admin_bar(){

		$is_hidden = false;
		if(is_user_logged_in()){
			$user = get_userdata(get_current_user_id());

			if($user && isset($user->roles[0])){
				$user_role = $user->roles[0];
				$is_hidden = uwp_get_option( 'hide_admin_bar_'.$user_role );
			}
		}

		if($is_hidden){
			show_admin_bar(false);
		}
	}
}