<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://wpgeodirectory.com
 * @since      1.0.0
 *
 * @package    userswp
 * @subpackage userswp/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    userswp
 * @subpackage userswp/admin
 * @author     GeoDirectory Team <info@wpgeodirectory.com>
 */
class UsersWP_Admin {

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'activation_redirect' ) );
		add_action( 'admin_init', array( $this, 'init_ayecode_connect_helper' ) );
		add_action( 'admin_init', array( $this, 'prevent_admin_access' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_notices', array( $this, 'show_update_messages' ) );
		add_action( 'admin_footer', array( $this, 'admin_only_script' ) );
		add_action( 'user_profile_picture_description', array( $this, 'user_profile_picture_description' ) );
		add_action( 'admin_body_class', array( $this, 'admin_body_class' ) );
		add_action( 'admin_init', array( $this, 'preview_emails' ) );

		add_filter( 'views_users', array( $this, 'request_views_users' ) );
		add_filter( 'pre_user_query', array( $this, 'request_users_filter' ) );
		add_action( 'edit_user_profile', array( $this, 'get_profile_extra_edit' ), 10, 1 );
		add_action( 'show_user_profile', array( $this, 'get_profile_extra_edit' ), 10, 1 );
		add_filter( 'user_row_actions', array( $this, 'user_row_actions' ), 10, 2 );
		add_action( 'bulk_actions-users', array( $this, 'users_bulk_actions' ) );
		add_action( 'handle_bulk_actions-users', array( $this, 'handle_users_bulk_actions' ), 10, 3 );
		add_filter( 'init', array( $this, 'process_user_actions' ) );
		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
		add_filter( 'aui_screen_ids', array( $this, 'add_aui_screens' ), 20, 1 );

		add_action( 'admin_notices', array( $this, 'add_user_type_updated_notice' ) );
        add_action( 'manage_users_extra_tablenav', array( $this, 'add_bulk_user_type_dropdown' ) );
        add_action( 'admin_init', array( $this, 'handle_bulk_user_type_change' ) );
        add_action( 'admin_footer', array( $this, 'add_validation_script' ) );
		add_filter( 'manage_users_columns', array( $this, 'add_user_type_column' ) );
		add_action( 'manage_users_custom_column', array( $this, 'display_user_type_column' ), 10, 3 );
		add_action( 'edit_user_profile', array( $this, 'display_admin_change_user_type' ), 10, 1 );
		add_action( 'show_user_profile', array( $this, 'display_admin_change_user_type' ), 10, 1 );
		add_action( 'personal_options_update', array( $this, 'update_user_type' ) );
		add_action( 'edit_user_profile_update', array( $this, 'update_user_type' ) );

		add_action( 'wp_ajax_uwp_ajax_create_register', array( $this, 'process_create_register_form' ) );
		add_action( 'wp_ajax_uwp_ajax_update_register', array( $this, 'process_update_register_form' ) );
		add_action( 'wp_ajax_uwp_ajax_remove_user_type', array( $this, 'process_remove_register_form' ) );
		add_action( 'wp_ajax_uwp_ajax_reorder_user_types', array( $this, 'reorder_user_types' ) );

		// Register with the deactivation survey class.
		if ( class_exists( 'AyeCode_Deactivation_Survey' ) ) {
			AyeCode_Deactivation_Survey::instance(
                array(
					'slug'              => 'userswp',
					'version'           => USERSWP_VERSION,
					'support_url'       => 'https://userswp.io/support/',
					'documentation_url' => 'https://userswp.io/documentation/',
					'activated'         => get_option( 'uwp_installed_on', 0 ),
                )
            );
        }
	}

	/**
	 * Redirects to UsersWP info page after plugin activation.
	 *
	 * @return      void
	 * @package     userswp
	 * @since       1.0.0
	 */
	public function activation_redirect() {
		if ( get_option( 'uwp_activation_redirect', false ) ) {
			delete_option( 'uwp_activation_redirect' );
			update_option( 'uwp_setup_wizard_notice', 1 );
			wp_redirect( admin_url( 'index.php?page=uwp-setup' ) );
			exit;
		}

		if ( ! empty( $_GET['force_sync_data'] ) && ! empty( $_GET['_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_GET['_nonce'] ), 'force_sync_data' ) && current_user_can( 'manage_options' ) ) {
			$blog_id = get_current_blog_id();
			do_action( 'wp_' . $blog_id . '_uwp_updater_cron' );
			wp_safe_redirect( admin_url( 'admin.php?page=userswp' ) );
			exit;
		}
	}

	/**
	 * Maybe show the AyeCode Connect Notice.
	 */
	public function init_ayecode_connect_helper() {
		// AyeCode Connect notice
		if ( is_admin() ) {
			// set the strings so they can be translated
			$strings = array(
				'connect_title'     => __( 'UsersWP - an AyeCode product!', 'userswp' ),
				'connect_external'  => __( 'Please confirm you wish to connect your site?', 'userswp' ),
				'connect'           => sprintf( __( '<strong>Have a license?</strong> Forget about entering license keys or downloading zip files, connect your site for instant access. %1$slearn more%2$s', 'userswp' ), "<a href='https://ayecode.io/introducing-ayecode-connect/' target='_blank'>", '</a>' ),
				'connect_button'    => __( 'Connect Site', 'userswp' ),
				'connecting_button' => __( 'Connecting...', 'userswp' ),
				'error_localhost'   => __( 'This service will only work with a live domain, not a localhost.', 'userswp' ),
				'error'             => __( 'Something went wrong, please refresh and try again.', 'userswp' ),
			);
			new AyeCode_Connect_Helper( $strings, array( 'uwp-addons' ) );
		}
	}

	/**
	 * Restrict the wp-admin area from specific user roles if set to do so.
	 */
	public function prevent_admin_access() {
		$restricted_roles = (array) uwp_get_option( 'admin_blocked_roles', array() );

		// Checking action in request to allow ajax request go through
		if ( ! empty( $restricted_roles ) && is_user_logged_in() && ! wp_doing_ajax() && ! wp_doing_cron() ) {
			$action = ! empty( $_POST['action'] ) ? sanitize_text_field( wp_unslash( $_POST['action'] ) ) : '';

			/*
			 * Prevent conflicts with MailPoet plugin.
			 */
			if ( in_array( $action, array( 'mailpoet_subscription_update' ) ) ) {
				return;
			}

			$roles = wp_get_current_user()->roles;

			$prevent = false;

			// Always allow administrator role.
			if ( ! ( ! empty( $roles ) && in_array( 'administrator', $roles ) ) ) {
				foreach ( $restricted_roles as $role ) {
					if ( in_array( $role, $roles ) ) {
						$prevent = true;
						break;
					}
				}
			}

			/*
			 * Check and prevent admin access based on user role.
			 *
			 * @param bool $prevent True to prevent admin access.
			 */
			$prevent = apply_filters( 'uwp_prevent_wp_admin_access', $prevent );

			if ( $prevent ) {
				wp_safe_redirect( home_url() );
				exit;
			}
		}
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @param $hook_suffix
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles( $hook_suffix ) {
		$screen       = get_current_screen();
		$screen_id    = $screen ? $screen->id : '';

		if ( $hook_suffix == 'profile.php' || $hook_suffix == 'user-edit.php' ) {
			wp_register_style( 'jquery-ui', USERSWP_PLUGIN_URL . 'assets/css/jquery-ui.css' );
			wp_enqueue_style( 'jquery-ui' );
			wp_enqueue_style( 'jcrop' );
			wp_enqueue_style( 'uwp-country-select', USERSWP_PLUGIN_URL . 'assets/css/countryselect.css', array(), USERSWP_VERSION, 'all' );
			wp_enqueue_style( 'userswp', USERSWP_PLUGIN_URL . 'assets/css/users-wp.css', array(), USERSWP_VERSION, 'all' );
			wp_enqueue_style( 'uwp_timepicker_css', USERSWP_PLUGIN_URL . 'assets/css/jquery.ui.timepicker.css', array(), USERSWP_VERSION, 'all' );
		}

		if ( false !== strpos( $hook_suffix, 'page_uwp_tools' ) ) {
			wp_enqueue_style( 'userswp', USERSWP_PLUGIN_URL . 'assets/css/users-wp.css', array(), USERSWP_VERSION, 'all' );
		}

		if ( false !== strpos( $hook_suffix, 'page_uwp_form_builder' ) ) {
			wp_enqueue_style( 'uwp_form_builder', USERSWP_PLUGIN_URL . 'admin/assets/css/uwp-form-builder.css', array(), USERSWP_VERSION, 'all' );
		}

		if ( $hook_suffix == 'toplevel_page_userswp' ) {
			wp_enqueue_style( 'wp-color-picker' );
		}

		if ( in_array( $screen_id, uwp_get_screen_ids() ) ) {
			wp_enqueue_style( 'userswp_admin_css', USERSWP_PLUGIN_URL . 'admin/assets/css/users-wp-admin.css', array(), USERSWP_VERSION, 'all' );
		}
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @param $hook_suffix
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts( $hook_suffix ) {

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$screen       = get_current_screen();
		$screen_id    = $screen ? $screen->id : '';

		if ( $hook_suffix == 'profile.php' || $hook_suffix == 'user-edit.php' ) {

			wp_enqueue_script( 'jquery-ui-datepicker', array( 'jquery' ) );
			wp_enqueue_script(
                'uwp_timepicker',
                USERSWP_PLUGIN_URL . 'assets/js/jquery.ui.timepicker.min.js',
                array(
					'jquery',
					'jquery-ui-datepicker',
					'jquery-ui-core',
                ),
                USERSWP_VERSION
            );
			wp_enqueue_script( 'userswp', USERSWP_PLUGIN_URL . 'assets/js/users-wp' . $suffix . '.js', array( 'jquery' ), USERSWP_VERSION, false );
			$uwp_localize_data = uwp_get_localize_data();
			wp_localize_script( 'userswp', 'uwp_localize_data', $uwp_localize_data );
			wp_enqueue_script( 'jquery-ui-progressbar', array( 'jquery' ) );
			wp_enqueue_script( 'jcrop', array( 'jquery' ) );
			wp_enqueue_script( 'country-select', USERSWP_PLUGIN_URL . 'assets/js/countrySelect' . $suffix . '.js', array( 'jquery' ), USERSWP_VERSION );

			$country_data = uwp_get_country_data();
			wp_localize_script( USERSWP_NAME, 'uwp_country_data', $country_data );
		}

		if ( false !== strpos( $hook_suffix, 'page_uwp_tools' ) ) {
			wp_enqueue_script( 'jquery-ui-progressbar', array( 'jquery' ) );
		}

		if ( false !== strpos( $hook_suffix, 'page_uwp_status' ) ) {
			wp_enqueue_script( 'uwp_status', USERSWP_PLUGIN_URL . 'admin/assets/js/system-status.js', array( 'jquery' ), USERSWP_VERSION, true );
		}

		if ( false !== strpos( $hook_suffix, 'page_uwp_form_builder' ) ) {
			wp_enqueue_script( 'jquery-ui-sortable' );
			wp_enqueue_script( 'uwp-nestable-script', USERSWP_PLUGIN_URL . 'admin/assets/js/jquery.nestable' . $suffix . '.js', array( 'jquery-ui-sortable' ), USERSWP_VERSION );
			wp_enqueue_script( 'uwp_form_builder', USERSWP_PLUGIN_URL . 'admin/assets/js/uwp-form-builder' . $suffix . '.js', array(), USERSWP_VERSION, 'all' );
		}

		if ( in_array( $screen_id, uwp_get_screen_ids() ) ) {
			wp_enqueue_script( 'userswp_admin', USERSWP_PLUGIN_URL . 'admin/assets/js/users-wp-admin' . $suffix . '.js', array( 'jquery' ), USERSWP_VERSION, false );

			wp_enqueue_script( 'jquery-ui-tooltip' );
			wp_enqueue_script( 'wp-color-picker' );

            if ( 'userswp_page_uwp_user_types' === $screen_id ) {
                wp_enqueue_script( 'jquery-ui-sortable' );
            }

			$ajax_cons_data = array(
				'url'                                => admin_url( 'admin-ajax.php' ),
                'nonces'                             => array(
                    'uwp_delete_user_type'   => wp_create_nonce( 'uwp_delete_user_types' ),
                    'uwp_reorder_user_types' => wp_create_nonce( 'uwp_reorder_user_types' ),
                ),
				'custom_field_not_blank_var'         => __( 'Field key must not be blank', 'userswp' ),
				'custom_field_options_not_blank_var' => __( 'Option Values must not be blank', 'userswp' ),
				'custom_field_not_special_char'      => __( 'Please do not use special character and spaces in field key.', 'userswp' ),
				'custom_field_unique_name'           => __( 'Field key should be a unique name.', 'userswp' ),
				'custom_field_delete'                => __( 'Are you sure you wish to delete this field?', 'userswp' ),
				'custom_field_id_required'           => __( 'This field is required.', 'userswp' ),
				'img_spacer'                         => admin_url( 'images/media-button-image.gif' ),
				'txt_choose_image'                   => __( 'Choose an image', 'userswp' ),
                'txt_use_image'                      => __( 'Use image', 'userswp' ),
                'txt_save'                           => __( 'Save', 'userswp' ),
                'txt_saved'                          => __( 'Saved', 'userswp' ),
                'txt_delete'                         => __( 'Delete', 'userswp' ),
                'txt_deleted'                        => __( 'Deleted', 'userswp' ),
                'txt_cancel'                         => __( 'Cancel', 'userswp' ),
                'txt_saving'                         => __( 'Saving...', 'userswp' ),
                'txt_saving_error'                   => __( 'An error occurred while saving. Please try again.', 'userswp' ),
				'delete_register_form'               => __( 'Are you sure you wish to delete this form?', 'userswp' ),
				'ask_register_form_title'            => __( 'Enter register form title', 'userswp' ),
				'form_updated_msg'                   => __( 'Updated! Reloading page...', 'userswp' ),
			);

			wp_localize_script( 'userswp_admin', 'uwp_admin_ajax', $ajax_cons_data );
		}
	}

	public function get_screen_ids() {
		return uwp_get_screen_ids();
	}

	/**
	 * Displays update messages
	 */
	public function show_update_messages() {
		if ( ! isset( $_REQUEST['update'] ) ) {
			return;
		}

		$update   = sanitize_text_field( $_REQUEST['update'] );
		$messages = array();

		switch ( $update ) {
			case 'uwp_resend':
				$messages['msg'] = __( 'Activation email has been sent!', 'userswp' );
				break;
			case 'err_uwp_resend':
				$messages['err_msg'] = __( 'Error while sending activation email. Please try again.', 'userswp' );
				break;
			case 'uwp_activate_user':
				$messages['msg'] = __( 'User(s) has been activated!', 'userswp' );
				break;
		}

		if ( ! empty( $messages ) ) {
			if ( isset( $messages['err_msg'] ) ) {
				echo '<div class="notice notice-error"><p>' . esc_html( $messages['err_msg'] ) . '</p></div>';
			} else {
				echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( $messages['msg'] ) . '</p></div>';
			}
		}
	}

	/**
	 * Adds UsersWP JS to admin area.
	 *
	 * @return      void
	 * @package userswp
	 *
	 * @since   1.0.0
	 */
	public function admin_only_script() {

		// check page is userswp or not.
		if ( ! empty( $_GET['page'] ) && 'userswp' == $_GET['page'] ) {

			// check tab is general or not.
			if ( ! empty( $_GET['tab'] ) && 'general' == $_GET['tab'] ) {

				// check for login section.
				if ( ! empty( $_GET['section'] ) && 'login' == $_GET['section'] ) {
					?>
                    <script type="text/javascript">
                        jQuery(document).ready(function () {
                            var login_redirect_to = jQuery('#login_redirect_to');
                            login_redirect_to.on('change', function () {
                                var value = jQuery(this).val();
                                var login_redirect_custom_obj = jQuery('#login_redirect_custom_url');

                                if ('-2' === value) {
                                    login_redirect_custom_obj.parent().parent().show();
                                } else {
                                    login_redirect_custom_obj.parent().parent().hide();
                                }
                            }).change();
                        });
                    </script>
					<?php
				}
			}
		}

		if ( ! empty( $_GET['page'] ) && 'uwp_user_types' == $_GET['page'] ) {
			?>
            <script type="text/javascript">
                jQuery(document).ready(function () {
                    var uwp_registration_action = jQuery('#uwp_registration_action');
                    uwp_registration_action.on('change', function () {
                        var value = jQuery(this).val();
                        var register_redirect_obj = jQuery('#register_redirect_to');

						register_redirect_obj.parents('tr').toggle(value === 'auto_approve_login');
                    }).change();

                    var register_redirect_to = jQuery('#register_redirect_to');
                    register_redirect_to.on('change', function () {
                        var value = jQuery(this).val();
                        var register_redirect_custom_obj = jQuery('#register_redirect_custom_url');
						register_redirect_custom_obj.parents('tr').toggle(value === '-2');
                    }).change();
                });
            </script>
			<?php
		}
	}

	/**
	 * Filters the user profile picture description displayed under the Gravatar.
	 *
	 * @param string $description Profile picture description.
	 *
	 * @return      string                      Modified description.
	 * @since       1.0.0
	 * @package     userswp
	 *
	 */
	public function user_profile_picture_description( $description ) {
		if ( is_admin() && IS_PROFILE_PAGE ) {
			$user_id = get_current_user_id();
			$avatar  = uwp_get_usermeta( $user_id, 'avatar_thumb', '' );

			if ( ! empty( $avatar ) ) {
				$description = sprintf(
                    __( 'You can change your profile picture on your <a href="%s">Profile Page</a>.', 'userswp' ),
                    uwp_build_profile_tab_url( $user_id )
                );
			}
}

		return $description;
	}

	public function admin_body_class( $classes ) {
		$screen = get_current_screen();
		if ( 'profile' == $screen->base || 'user-edit' == $screen->base ) {
			$classes .= ' uwp_page';
		}

		// Add original UsersWP page class when UsersWP screen is translated.
		if ( ! empty( $_GET['page'] ) && in_array( $_GET['page'], array( 'uwp_form_builder', 'uwp_tools', 'uwp_status', 'uwp-addons' ) ) ) {
			$uwp_screen_id = sanitize_title( __( 'UsersWP', 'userswp' ) );

			if ( $uwp_screen_id != 'userswp' ) {
				$classes .= ' userswp_page_' . esc_attr( sanitize_text_field( $_GET['page'] ) );
			}
		}

		// Add body class for admin pages.
		$screen = get_current_screen();
		$screen_id = $screen ? $screen->id : '';

		if ( $screen_id && in_array( $screen_id, uwp_get_screen_ids() ) ) {
			$classes .= ' uwp-admin-page uwp-admin-page-' . sanitize_key( $screen_id );
		}

		return $classes;
	}

	/**
	 * Preview email template for UWP emails.
	 */
	public function preview_emails() {
		if ( isset( $_GET['uwp_preview_mail'] ) ) {
			if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'uwp-preview-mail' ) ) {
				die( 'Security check failed.' );
			}

			$email_name = 'preview_mail';
			$email_vars = array();
			$plain_text = UsersWP_Mails::get_email_type() != 'html' ? true : false;

			// Get the preview email content.
			ob_start();
			include  'views/html-email-template-preview.php';
			$message = ob_get_clean();

			$message = UsersWP_Mails::email_wrap_message( $message, $email_name, $email_vars, '', $plain_text );
			$message = UsersWP_Mails::style_body( $message, $email_name, $email_vars );
			$message = apply_filters( 'uwp_mail_content', $message, $email_name, $email_vars );

			// Print the preview email content.
			if ( $plain_text ) {
				echo '<div style="white-space:pre-wrap;font-family:sans-serif">';
			}
			echo $message; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			if ( $plain_text ) {
				echo '</div>';
			}
			exit;
		}
	}

	/**
	 * Returns user views filter
	 *
	 * @return array User views.
	 */
	public function request_views_users( $views ) {

		$current = '';

		if ( isset( $_REQUEST['uwp_status'] ) && $_REQUEST['uwp_status'] == 'pending-email-activate' ) {
			$views['all'] = str_replace( 'current', '', $views['all'] );
			$current      = 'current';
		}

		$views['pending-email-activate'] = '<a href="' . admin_url( 'users.php' ) . '?uwp_status=pending-email-activate" class="' . $current . '">' . __( 'Pending Email Activation', 'userswp' ) . ' <span class="count">(' . $this->uwp_pending_email_count() . ')</span></a>';

		return $views;
	}

	/**
	 * Returns pending email activation user counts
	 *
	 * @return int User count
	 */
	public function uwp_pending_email_count() {

		$args  = array(
			'fields'     => 'ID',
			'number'     => 0,
			'meta_query' => array(
				array(
					'key'     => 'uwp_mod',
					'value'   => 'email_unconfirmed',
					'compare' => '=',
				),
			),
		);
		$users = new WP_User_Query( $args );

		return isset( $users->results ) ? (int) count( $users->results ) : 0;
	}

	/**
	 * Filter to modify the user query
	 *
	 * @return object User query.
	 */
	public function request_users_filter( $query ) {

		global $wpdb, $pagenow;

		if ( is_admin() && $pagenow == 'users.php' && isset( $_GET['uwp_status'] ) && $_GET['uwp_status'] != '' ) {

			do_action( 'uwp_users_show_pending_email_activation', $query );

			$status = sanitize_text_field( urldecode( $_GET['uwp_status'] ) );

			if ( $status == 'pending-email-activate' ) {
				$query->query_where = str_replace(
                    'WHERE 1=1',
					"WHERE 1=1 AND {$wpdb->users}.ID IN (
							 SELECT {$wpdb->usermeta}.user_id FROM $wpdb->usermeta
								WHERE {$wpdb->usermeta}.meta_key = 'uwp_mod'
								AND {$wpdb->usermeta}.meta_value = 'email_unconfirmed')",
					$query->query_where
				);
			}
		}

		return $query;
	}

	/**
	 * Gets UsersWP fields in WP-Admin Users Edit page.
	 *
	 * @param object $user User Object.
	 *
	 * @package     userswp
	 * @since       1.0.0
	 */
	public function get_profile_extra_edit( $user ) {
		global $wpdb;
		$table_name      = uwp_get_table_prefix() . 'uwp_form_fields';
		$form_id = uwp_get_register_form_id( $user->ID );
		$excluded_fields = apply_filters( 'uwp_exclude_edit_profile_fields', array( 'bio', 'register_gdpr', 'register_tos', 'uwp_language', 'multisite_site_title', 'multisite_site_address' ) );
		$query           = $wpdb->prepare( 'SELECT * FROM ' . $table_name . " WHERE form_type = 'account' AND is_default = '0' AND form_id = %d", $form_id );
		if ( is_array( $excluded_fields ) && count( $excluded_fields ) > 0 ) {
			$query .= " AND htmlvar_name NOT IN ('" . implode( "','", $excluded_fields ) . "')";
		}
		$query  .= ' ORDER BY sort_order ASC';
		$fields = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

		$this->edit_profile_banner_fields( $user ); // Displays avatar and banner fields

		if ( $fields ) {
			?>
            <div class="uwp-profile-extra">
                <table class="uwp-profile-extra-table form-table">
					<?php
					foreach ( $fields as $field ) {

						if ( isset( $field->css_class ) && ! empty( $field->css_class ) ) {
							$field->css_class = $field->css_class . ' w-50';
                        } else {
							$field->css_class = 'w-50';
                        }

						if ( $field->field_type == 'fieldset' ) {
							?>
                            <tr>
                                <th class="uwp-profile-extra-key">
									<h2><?php echo esc_html( $field->site_title ); ?></h2>
								</th>
                                <td></td>
                            </tr>
							<?php
						} else {
                        ?>
                            <tr>
                                <th class="uwp-profile-extra-key"><?php echo esc_html( $field->site_title ); ?></th>
                                <td class="uwp-profile-extra-value bsui">
									<?php
									$templates_obj = new UsersWP_Templates();
									$templates_obj->template_fields_html( $field, 'account', $user->ID );
                                    ?>
                                </td>
                            </tr>
							<?php
						}
					}
					?>
                </table>
            </div>
			<?php
		}
	}

	/**
	 * Adds avatar and banner fields in admin side.
	 *
	 * @param object $user User object.
	 *
	 * @return      void
	 * @since       1.0.0
	 * @package     userswp
	 *
	 */
	public function edit_profile_banner_fields( $user ) {
		global $wpdb;

		$file_obj = new UsersWP_Files();

		$table_name = uwp_get_table_prefix() . 'uwp_form_fields';
		$fields     = $wpdb->get_results( 'SELECT * FROM ' . $table_name . " WHERE (form_type = 'avatar' OR form_type = 'banner') ORDER BY sort_order ASC" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		if ( $fields ) {
			?>
            <div class="uwp-profile-extra uwp_page">
				<?php do_action( 'uwp_admin_profile_edit', $user ); ?>
                <h2><?php esc_html_e( 'UsersWP Account Details', 'userswp' ); ?></h2>
                <table class="uwp-profile-extra-table form-table">
					<?php
					foreach ( $fields as $field ) {

						if ( $field->field_type == 'fieldset' ) {
							?>
                            <tr>
                                <th class="uwp-profile-extra-key">
									<h2><?php echo esc_html( $field->site_title ); ?></h2>
								</th>
                                <td></td>
                            </tr>
							<?php
						} else {
                        ?>
                            <tr>
                                <th class="uwp-profile-extra-key"><?php echo esc_html( $field->site_title ); ?></th>
                                <td class="uwp-profile-extra-value bsui">
									<?php
									if ( $field->htmlvar_name == 'avatar' ) {
										$value = uwp_get_usermeta( $user->ID, 'avatar_thumb', '' );
									} elseif ( $field->htmlvar_name == 'banner' ) {
										$value = uwp_get_usermeta( $user->ID, 'banner_thumb', '' );
									} else {
										$value = '';
									}

									echo $file_obj->file_upload_preview( $field, $value ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

									if ( $field->htmlvar_name == 'avatar' ) {
										if ( ! empty( $value ) ) {
											$label = __( 'Change Avatar', 'userswp' );
										} else {
											$label = __( 'Upload Avatar', 'userswp' );
										}
										?>
                                        <a onclick="uwp_profile_image_change('avatar');return false;" href="#"
                                            class="uwp-banner-change-icon-admin">
											<?php echo esc_html( $label ); ?>
                                        </a>
										<?php
									} elseif ( $field->htmlvar_name == 'banner' ) {
										if ( ! empty( $value ) ) {
											$label = __( 'Change Banner', 'userswp' );
										} else {
											$label = __( 'Upload Banner', 'userswp' );
										}
                                        ?>
                                        <a onclick="uwp_profile_image_change('banner');return false;" href="#"
                                            class="uwp-banner-change-icon-admin">
											<?php echo esc_html( $label ); ?>
                                        </a>
										<?php
									}
									?>
                                </td>
                            </tr>
							<?php
						}
					}
					?>
                </table>
            </div>
			<?php
		}
	}

	/**
	 * Returns user row actions
	 *
	 * @param array  $actions     Date string.
	 * @param object $user_object The User ID.
	 *
	 * @return      array   Row actions.
	 * @package     userswp
	 *
	 */
	public function user_row_actions( $actions, $user_object ) {
		$user_id     = $user_object->ID;
		$mod_value   = get_user_meta( $user_id, 'uwp_mod', true );
		$resend_link = add_query_arg(
			array(
				'user_id'      => $user_id,
				'action'       => 'uwp_resend',
				'_nonce'       => wp_create_nonce( 'uwp_resend' ),
				'uwp_is_admin' => 1,
			),
			admin_url( 'users.php' )
		);

		$activate_link = add_query_arg(
			array(
				'user_id'      => $user_id,
				'action'       => 'uwp_activate_user',
				'_nonce'       => wp_create_nonce( 'uwp_activate_user' ),
				'uwp_is_admin' => 1,
			),
			admin_url( 'users.php' )
		);

		if ( $mod_value == 'email_unconfirmed' ) {
			$actions['uwp_resend_activation'] = "<a class='' href='" . $resend_link . "'>" . __( 'Resend Activation', 'userswp' ) . '</a>';
			$actions['uwp_auto_activate']     = "<a class='' href='" . $activate_link . "'>" . __( 'Activate User', 'userswp' ) . '</a>';
		}

		return $actions;
	}

	/**
	 * Returns users bulk actions
	 *
	 * @param array $bulk_actions Bulk actions.
	 *
	 * @return      array   Bulk actions.
	 * @package     userswp
	 *
	 */
	public function users_bulk_actions( $bulk_actions ) {
		$bulk_actions['uwp_resend']        = __( 'Resend Activation', 'userswp' );
		$bulk_actions['uwp_activate_user'] = __( 'Activate Users', 'userswp' );

		return $bulk_actions;
	}

	/**
	 * Handles users bulk actions
	 *
	 * @param string $redirect_to Bulk actions.
	 * @param string $doaction    Current action.
	 * @param array  $user_ids    User IDs to process.
	 *
	 * @return      string   Redirect URL.
	 * @package     userswp
	 *
	 */
	public function handle_users_bulk_actions( $redirect_to, $doaction, $user_ids ) {
		if ( 'uwp_resend' == $doaction ) {
			foreach ( $user_ids as $user_id ) {
				uwp_resend_activation_mail( $user_id );
			}

			$redirect_to = add_query_arg( 'update', 'uwp_resend', $redirect_to );
		} elseif ( 'uwp_activate_user' == $doaction ) {
			foreach ( $user_ids as $user_id ) {
				$this->activate_user( $user_id );
			}
			$redirect_to = add_query_arg( 'update', 'uwp_activate_user', $redirect_to );
		}

		return $redirect_to;
	}

	/**
	 * Activates user
	 *
	 * @param int $user_id User ID
	 *
	 * @return bool
	 */
	public function activate_user( $user_id = 0 ) {
		if ( ! $user_id ) {
			return false;
		}

		$uwp_mode = get_user_meta( $user_id, 'uwp_mod', true );
		if ( 'email_unconfirmed' == $uwp_mode ) {
			delete_user_meta( $user_id, 'uwp_mod' );
			do_action( 'uwp_email_activation_success', $user_id );
		}

		return true;
	}

	/**
	 * Processes user action
	 *
	 * @return      mixed
	 * @package     userswp
	 *
	 */
	public function process_user_actions() {
		$user_id = isset( $_REQUEST['user_id'] ) ? (int) $_REQUEST['user_id'] : 0;
		$action  = isset( $_REQUEST['action'] ) ? sanitize_text_field( $_REQUEST['action'] ) : false;
		$nonce   = isset( $_REQUEST['_nonce'] ) ? sanitize_text_field( $_REQUEST['_nonce'] ) : false;
		$is_admin   = isset( $_REQUEST['uwp_is_admin'] ) ? sanitize_text_field( $_REQUEST['uwp_is_admin'] ) : false;

		if ( $user_id && 'uwp_resend' == $action && ! empty( $nonce ) && wp_verify_nonce( $nonce, 'uwp_resend' ) ) {
			uwp_resend_activation_mail( $user_id );
			if ( isset( $is_admin ) && $is_admin ) {
				wp_redirect( add_query_arg( 'update', 'uwp_resend', admin_url( 'users.php' ) ) );
				exit();
			} else {
				global $uwp_notices;
				$message       = __( 'Activation email has been sent!', 'userswp' );
				$uwp_notices[] = aui()->alert(
                    array(
						'type'    => 'success',
						'content' => $message,
                    )
                );
			}
		} elseif ( $user_id && 'uwp_activate_user' == $action && wp_verify_nonce( $nonce, 'uwp_activate_user' ) ) {
			if ( isset( $is_admin ) && $is_admin && current_user_can( 'edit_users' ) ) {
				$this->activate_user( $user_id );
				wp_redirect( add_query_arg( 'update', 'uwp_activate_user', admin_url( 'users.php' ) ) );
			}
		}
	}

	/**
	 * Show row meta on the plugin screen.
	 *
	 * @param   mixed $links Plugin Row Meta
	 * @param   mixed $file  Plugin Base file
	 * @return  array
	 */
	public static function plugin_row_meta( $links, $file ) {
		if ( USERSWP_PLUGIN_BASENAME == $file ) {
			$row_meta = array(
				'docs'        => '<a href="' . esc_url( 'https://userswp.io/documentation/' ) . '" aria-label="' . esc_attr__( 'View UsersWP Documentation', 'userswp' ) . '">' . esc_html__( 'Docs', 'userswp' ) . '</a>',
				'support'     => '<a href="' . esc_url( 'https://userswp.io/support/' ) . '" aria-label="' . esc_attr__( 'Visit UsersWP support', 'userswp' ) . '">' . esc_html__( 'Support', 'userswp' ) . '</a>',
				'translation' => '<a href="' . esc_url( 'https://userswp.io/translate/projects' ) . '" aria-label="' . esc_attr__( 'View translations', 'userswp' ) . '">' . esc_html__( 'Translations', 'userswp' ) . '</a>',
			);

			return array_merge( $links, $row_meta );
		}

		return (array) $links;
	}

    /**
     * Process the creation of a new registration form.
     *
     * This method handles the AJAX request to create a new registration form,
     * including validation, sanitization, and database operations.
     */
    public function process_create_register_form() {
        check_ajax_referer( 'uwp-create-register-form-nonce', 'uwp_create_register_form_nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( -1 );
        }

        $form_title = isset( $_POST['form_title'] ) ? sanitize_text_field( $_POST['form_title'] ) : __( 'Form', 'userswp' );
        $user_role = isset( $_POST['user_role'] ) ? sanitize_text_field( $_POST['user_role'] ) : '';
        $action = isset( $_POST['reg_action'] ) ? sanitize_text_field( $_POST['reg_action'] ) : uwp_get_option( 'uwp_registration_action', 'auto_approve' );
        $redirect_to = isset( $_POST['redirect_to'] ) ? (int)$_POST['redirect_to'] : 0;
        $custom_url = isset( $_POST['custom_url'] ) ? sanitize_text_field( $_POST['custom_url'] ) : '';
        $gdpr_page = isset( $_POST['gdpr_page'] ) ? (int)$_POST['gdpr_page'] : (int)uwp_get_option( 'register_gdpr_page', false );
        $tos_page = isset( $_POST['tos_page'] ) ? (int)$_POST['tos_page'] : (int)uwp_get_option( 'register_terms_page', false );

        if ( empty( $form_title ) ) {
            wp_send_json_error(
				array(
					'message' => esc_html__( 'Form title is required.', 'userswp' ),
				)
			);
        }

        $status = false;
        $redirect = '';

        $new_form_id = uwp_get_next_register_form_id();
        if ( $new_form_id ) {
            $register_forms = (array) uwp_get_option( 'multiple_registration_forms', array() );
            // Filter out invalid items.
            $register_forms = array_filter(
                $register_forms,
                function ( $form ) {
                return isset( $form['id'] );
                }
            );

            $register_forms[] = array(
                'id'          => $new_form_id,
                'title'       => ! empty( $form_title ) ? $form_title : sprintf( __( 'Form %d', 'userswp' ), $new_form_id ),
                'slug'        => ! empty( $form_title ) ? sanitize_title( $form_title ) : sprintf( 'form-%d', $new_form_id ),
                'user_role'   => $user_role,
                'reg_action'  => $action,
                'redirect_to' => $redirect_to,
                'custom_url'  => $custom_url,
                'gdpr_page'   => $gdpr_page,
                'tos_page'    => $tos_page,
            );

            $register_forms = array_values( $register_forms );
            $register_forms = apply_filters( 'uwp_multiple_registration_forms_update', $register_forms, $new_form_id );

            uwp_update_option( 'multiple_registration_forms', $register_forms );

            $fields = UsersWP_Activator::uwp_default_custom_fields_account();
            $form_builder = new UsersWP_Form_Builder();

            foreach ( $fields as $field ) {
                $field['form_id'] = $new_form_id;
                $form_builder->admin_form_field_save( $field );
            }

            UsersWP_Activator::insert_form_extras( $new_form_id );

            do_action( 'uwp_create_register_form', $new_form_id );

            $status = true;
            $redirect = admin_url( sprintf( 'admin.php?page=uwp_user_types&form=%d', $new_form_id ) );
        }

        wp_send_json_success(
            array(
				'status'   => $status,
                'message'  => esc_html__( 'User Type added successfully.', 'userswp' ),
				'redirect' => $redirect,
            )
        );
    }

    /**
     * Process the update of an existing registration form.
     *
     * This method handles the AJAX request to update an existing registration form,
     * including validation, sanitization, and database operations.
     */
    public function process_update_register_form() {
        check_ajax_referer( 'uwp-update-register-form-nonce', 'uwp_update_register_form_nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( -1 );
        }

        $form_id = isset( $_POST['manage_field_form_id'] ) ? (int)$_POST['manage_field_form_id'] : 0;
        $form_title = isset( $_POST['form_title'] ) && ! empty( $_POST['form_title'] ) ? sanitize_text_field( $_POST['form_title'] ) : __( 'Form', 'userswp' );
        $user_role = isset( $_POST['user_role'] ) ? sanitize_text_field( $_POST['user_role'] ) : '';
        $action = isset( $_POST['reg_action'] ) ? sanitize_text_field( $_POST['reg_action'] ) : uwp_get_option( 'uwp_registration_action', 'auto_approve' );
        $redirect_to = isset( $_POST['redirect_to'] ) ? (int)$_POST['redirect_to'] : 0;
        $custom_url = isset( $_POST['custom_url'] ) ? sanitize_text_field( $_POST['custom_url'] ) : '';
        $gdpr_page = isset( $_POST['gdpr_page'] ) ? (int)$_POST['gdpr_page'] : (int)uwp_get_option( 'register_gdpr_page', false );
        $tos_page = isset( $_POST['tos_page'] ) ? (int)$_POST['tos_page'] : (int)uwp_get_option( 'register_terms_page', false );

        if ( ! $form_id ) {
            wp_send_json_error(
				array(
					'message' => esc_html__( 'Something went wrong. Please try again.', 'userswp' ),
				)
			);
        }

        $redirect = '';
        $status = false;

        $register_forms = (array) uwp_get_option( 'multiple_registration_forms', array() );

        foreach ( $register_forms as &$register_form ) {
            if ( $register_form['id'] == $form_id ) {
                $register_form['title'] = $form_title;
                $register_form['user_role'] = $user_role;
                $register_form['reg_action'] = $action;
                $register_form['redirect_to'] = $redirect_to;
                $register_form['custom_url'] = $custom_url;
                $register_form['gdpr_page'] = $gdpr_page;
                $register_form['tos_page'] = $tos_page;
                $status = true;
                break;
            }
        }

        if ( $status ) {
            $register_forms = array_values( $register_forms );
            $register_forms = apply_filters( 'uwp_multiple_registration_forms_update', $register_forms, $form_id );
            uwp_update_option( 'multiple_registration_forms', $register_forms );
            $redirect = add_query_arg(
                array(
                    'page' => 'uwp_user_types',
                    'form' => $form_id,
                ),
                admin_url( 'admin.php' )
            );
        }

        wp_send_json_success(
            array(
				'status'  => $status,
                'message' => esc_html__( 'The user type has been updated successfully.', 'userswp' ),
            )
        );
    }

    public function reorder_user_types() {
        check_ajax_referer( 'uwp_reorder_user_types', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'You do not have permission to perform this action.', 'userswp' ) ) );
        }

        $order = isset( $_POST['order'] ) ? array_map( 'absint', $_POST['order'] ) : array();

        if ( empty( $order ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid order data.', 'userswp' ) ) );
        }

        $register_forms = (array) uwp_get_option( 'multiple_registration_forms', array() );
        $new_order = array();

        foreach ( $order as $id ) {
            foreach ( $register_forms as $form ) {
                if ( (int) $form['id'] === (int) $id ) {
                    $new_order[] = $form;
                    break;
                }
            }
        }

        uwp_update_option( 'multiple_registration_forms', $new_order );

        wp_send_json_success( array( 'message' => __( 'User types order updated successfully.', 'userswp' ) ) );
    }

	public function process_remove_register_form() {

		check_ajax_referer( 'uwp_delete_user_types', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( -1 );
		}

		$type    = ! empty( $_POST['type'] ) ? sanitize_text_field( $_POST['type'] ) : '';
		$form_id = ! empty( $_POST['form_id'] ) ? (int) $_POST['form_id'] : '';

		$status   = false;
		$message  = __( 'Security nonce failed. Please try again.', 'userswp' );
		$redirect = '';

		if ( ! empty( $type ) && ! empty( $form_id ) && $type === 'remove' ) {
			$status = self::remove_registration_form( (int) $form_id );
			$redirect = admin_url( 'admin.php?page=uwp_user_types' );
		}

		wp_send_json(
			array(
				'status'   => $status,
				'message'  => $message,
				'redirect' => $redirect,
			)
		);
	}

	/**
     * Removes the registration form and its associated fields.
     *
     * @global wpdb $wpdb WordPress database abstraction object.
     *
     * @param int $form_id The form ID to remove.
     * @return bool True if the form was found and removed; false otherwise.
     */
    public static function remove_registration_form( int $form_id ) {
        global $wpdb;

        $table_name     = uwp_get_table_prefix() . 'uwp_form_fields';
        $status         = false;
        $register_forms = (array) uwp_get_option( 'multiple_registration_forms', array() );
        $form_builder   = new UsersWP_Form_Builder();

        if ( empty( $register_forms ) || ! is_array( $register_forms ) ) {
            return $status;
		}

        foreach ( $register_forms as $key => $register_form ) {
            if ( empty( $register_form['id'] ) || (int) $register_form['id'] !== $form_id ) {
                continue;
            }

            $status = true;
            unset( $register_forms[ $key ] );

            $fields = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT id FROM {$table_name} WHERE form_type = %s AND form_id = %d ORDER BY sort_order ASC",
                    'account',
                    $form_id
                )
            );

            if ( ! empty( $fields ) ) {
                foreach ( $fields as $field ) {
                    $form_builder->admin_form_field_delete( (int) $field->id, false, $form_id );
                }
            }
            }

        $register_forms = array_values( $register_forms );
        uwp_update_option( 'multiple_registration_forms', $register_forms );

        return $status;
    }

	/**
	 * Tell AyeCode UI to load on certain admin pages.
	 *
	 * @since 1.2.3.22
	 *
	 * @param array $screen_ids Screen IDs.
	 * @return array Screen IDs.
	 */
	public function add_aui_screens( $screen_ids ) {
		// Load on these pages if set
		if ( is_admin() && ! wp_doing_ajax() ) {
			$screen_ids = array_merge( $screen_ids, uwp_get_screen_ids() );
		}

		// AUI is also needed for setup wizard.
		$screen_ids[] = 'uwp-setup';

		return $screen_ids;
	}

	/**
	 * Adds a new user type column to the users list table.
	 *
	 * @param array $columns Existing columns in the users list table.
	 * @return array Modified columns array with the new user type column.
	 */
	public function add_user_type_column( array $columns ) {
		$columns['uwp_user_type'] = __( 'User Type', 'userswp' );
		return $columns;
	}

	/**
	 * Displays user type in the user list column.
	 *
	 * @param string $value       Current column value.
	 * @param string $column_name Column name being displayed.
	 * @param int    $user_id     Current user ID.
	 * @return string user type title.
	 */
	public function display_user_type_column( $value, $column_name, $user_id ) {
		if ( 'uwp_user_type' === $column_name ) {
			$form_id 		= uwp_get_register_form_id( $user_id );
			$uwp_user_type 	= uwp_get_user_register_form( $form_id );

			if ( ! isset( $uwp_user_type['id'], $uwp_user_type['title'] ) ) {
				return '&mdash;';
			}

			return $uwp_user_type['title'];
		}

		return $value;
	}

	/**
	 * Display user type options in the admin user edit screen.
	 *
	 * @param WP_User $user The WP user object.
	 * @return void
	 */
	public function display_admin_change_user_type( $user ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$register_forms = UsersWP_User_Types::get_register_forms();
		$user_type_id   = (int) uwp_get_register_form_id( $user->ID );

		ob_start();
		?>
		<table class="form-table">
			<tbody>
				<tr>
					<th>
						<label for="uwp_user_type_id" class="fw-semibold">
							<?php esc_html_e( 'User Type', 'userswp' ); ?>
						</label>
					</th>
					<td>
						<select name="uwp_user_type_id" id="uwp_user_type_id" class="regular-text">
							<?php foreach ( $register_forms as $form ) : ?>
								<?php 
								$form_id = absint( $form['id'] ); 
								$is_current = ( $form_id === $user_type_id );
								?>
								<option value="<?php echo esc_attr( $form_id ); ?>" <?php selected( $form_id, $user_type_id ); ?>>
									<?php echo esc_html( $form['title'] ); ?><?php echo $is_current ? ' (' . esc_html__( 'Active', 'userswp' ) . ')' : ''; ?>
								</option>
							<?php endforeach; ?>
						</select>
						<p class="description">
							<?php esc_html_e( "Select the user type for this account.", 'userswp' ); ?>
						</p>
					</td>
				</tr>
			</tbody>
		</table>
		<?php

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo apply_filters( 'uwp_admin_change_user_type_display', ob_get_clean(), $user );
	}

	/**
	 * Update user membership.
	 *
	 * @param int $user_id The ID of the user to update.
	 * @return void
	 */
	public function update_user_type( $user_id ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		check_admin_referer( 'update-user_' . $user_id );

		if ( isset( $_POST['uwp_user_type_id'] ) && ! empty( $_POST['uwp_user_type_id'] ) ) {
			$user_type_id = absint( $_POST['uwp_user_type_id'] );

			// Validate new membership.
			$uwp_user_type = uwp_get_user_register_form( $user_type_id );
			if ( ! $uwp_user_type ) {
				return;
			}

			update_user_meta( $user_id, '_uwp_register_form_id', $user_type_id );
		}
	}

	/**
     * Adds a notice after updating user type.
     */
    public function add_user_type_updated_notice() {
        if ( isset( $_GET['user_type_updated'] ) && 'true' === $_GET['user_type_updated'] ) {
            ?>
            <div class="updated notice is-dismissible">
                <p><?php esc_html_e( 'User type updated successfully.', 'userswp' ); ?></p>
            </div>
            <?php
        }
    }

    /**
     * Adds a bulk action dropdown for changing user types on the Users list table.
     *
     * @param string $which The position of the table (top or bottom).
     */
    public function add_bulk_user_type_dropdown( $which ) {
        if ( ! is_admin() || 'users' !== get_current_screen()->id || 'top' !== $which ) {
            return;
        }

        $user_types = UsersWP_User_Types::get_register_forms();

        if ( empty( $user_types ) ) {
            return;
        }

        $options = array_map( function( $user_type ) {
            return sprintf(
                '<option value="%d">%s</option>',
                absint( $user_type['id'] ),
                esc_html( $user_type['title'] )
            );
        }, $user_types );

		ob_start();
        ?>
        <div class="alignleft actions">
            <label class="screen-reader-text" for="uwp_new_user_type">
                <?php esc_html_e( 'Change user type to…', 'userswp' ); ?>
            </label>
            <select name="uwp_new_user_type" id="uwp_new_user_type">
                <option value=""><?php esc_html_e( 'Change user type to…', 'userswp' ); ?></option>
                <?php echo implode( '', $options ); ?>
            </select>
            <input type="submit" name="uwp_change_user_type" id="uwp_change_user_type" class="button" value="<?php esc_attr_e( 'Change', 'userswp' ); ?>">
        </div>
        <?php

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo apply_filters( 'uwp_bulk_change_user_type_display', ob_get_clean() );
    }

    /**
     * Handles the bulk user type change.
     */
    public function handle_bulk_user_type_change() {
        if ( ! isset( $_GET['uwp_change_user_type'], $_GET['uwp_new_user_type'] ) ) {
            return;
        }

        $new_user_type = absint( $_GET['uwp_new_user_type'] );

        if ( ! $new_user_type ) {
            return;
        }

		$users = isset( $_REQUEST['users'] ) && ! empty( $_REQUEST['users'] ) ? (array) $_REQUEST['users'] : array();

        if ( ! empty( $users ) ) {
            array_map( function( $user_id ) use ( $new_user_type ) {
                update_user_meta( absint( $user_id ), '_uwp_register_form_id', (int) $new_user_type );
            }, $users );
        } 

        wp_safe_redirect( add_query_arg( 'user_type_updated', 'true', admin_url( 'users.php' ) ) );
        exit;
    }

    /**
     * Adds JavaScript validation script.
     */
    public function add_validation_script() {
		$screen = get_current_screen();

		if ( 'users' !== $screen->id ) {
			return;
		}
        ?>
        <script type="text/javascript">
        (function($) {
            $(document).ready(function() {
				var $userTypeErrorNotice = $(
					'<div id="no-user-type-selected" class="notice notice-error is-dismissible" style="display:none;">' +
						'<p><?php esc_html_e( "Please select a user type to perform this action.", "userswp" ); ?></p>' +
						'<button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php esc_html_e( "Dismiss this notice.", "userswp" ); ?></span></button>' +
					'</div>',
				);

				var $itemSelectionErrorNotice = $(
					'<div id="no-item-selected" class="notice notice-error is-dismissible" style="display:none;">' +
						'<p><?php esc_html_e( "Please select at least one item to perform this action on.", "userswp" ); ?></p>' +
						'<button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php esc_html_e( "Dismiss this notice.", "userswp" ); ?></span></button>' +
					'</div>',
				);

				$("#wpbody-content").prepend($userTypeErrorNotice, $itemSelectionErrorNotice)

				$("#uwp_change_user_type").on("click", (e) => {
					if ($('input[name="users[]"]:checked').length === 0) {
						e.preventDefault();
						showErrorNotice($itemSelectionErrorNotice);
						hideErrorNotice($userTypeErrorNotice);
					} else if ($("#new_user_type").val() === "") {
						e.preventDefault();
						showErrorNotice($userTypeErrorNotice);
						hideErrorNotice($itemSelectionErrorNotice);
					} else {
						hideErrorNotice($userTypeErrorNotice);
						hideErrorNotice($itemSelectionErrorNotice);
					}
				});

				$(".notice-dismiss").on("click", function () {
					hideErrorNotice($(this).closest(".notice"));
				})

				function showErrorNotice($notice) {
					$notice.fadeIn(300);
				}

				function hideErrorNotice($notice) {
					$notice.fadeOut(300);
				}
            });
        })(jQuery);
        </script>
        <?php
    }
}
