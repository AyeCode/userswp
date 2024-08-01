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

		add_action( 'wp_ajax_uwp_ajax_create_register', array( $this, 'process_create_register_form' ) );
		add_action( 'wp_ajax_uwp_ajax_update_register', array( $this, 'process_update_register_form' ) );
		add_action( 'wp_ajax_uwp_ajax_remove_register', array( $this, 'process_remove_register_form' ) );

		// Register with the deactivation survey class.
		if(class_exists('AyeCode_Deactivation_Survey')){
			AyeCode_Deactivation_Survey::instance(array(
				'slug'		=> 'userswp',
				'version'	=> USERSWP_VERSION,
				'support_url'=> 'https://userswp.io/support/',
				'documentation_url'=> 'https://docs.userswp.io/',
				'activated' => get_option('uwp_installed_on', 0)
			));
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
			update_option( "uwp_setup_wizard_notice", 1 );
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
				'connect_title'     => __( "UsersWP - an AyeCode product!", "userswp" ),
				'connect_external'  => __( "Please confirm you wish to connect your site?", "userswp" ),
				'connect'           => sprintf( __( "<strong>Have a license?</strong> Forget about entering license keys or downloading zip files, connect your site for instant access. %slearn more%s", "userswp" ), "<a href='https://ayecode.io/introducing-ayecode-connect/' target='_blank'>", "</a>" ),
				'connect_button'    => __( "Connect Site", "userswp" ),
				'connecting_button' => __( "Connecting...", "userswp" ),
				'error_localhost'   => __( "This service will only work with a live domain, not a localhost.", "userswp" ),
				'error'             => __( "Something went wrong, please refresh and try again.", "userswp" ),
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
		if ( ! empty ( $restricted_roles ) && is_user_logged_in() && ! wp_doing_ajax() && ! wp_doing_cron() ) {
			$roles = wp_get_current_user()->roles;

			$prevent = false;

			// Always allow administrator role.
			if ( ! ( ! empty( $roles ) && in_array( 'administrator', $roles ) ) ) {
				foreach( $restricted_roles as $role ) {
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
			wp_enqueue_style( "uwp-country-select", USERSWP_PLUGIN_URL . 'assets/css/countryselect.css', array(), USERSWP_VERSION, 'all' );
			wp_enqueue_style( "userswp", USERSWP_PLUGIN_URL . 'assets/css/users-wp.css', array(), USERSWP_VERSION, 'all' );
			wp_enqueue_style( "uwp_timepicker_css", USERSWP_PLUGIN_URL . 'assets/css/jquery.ui.timepicker.css', array(), USERSWP_VERSION, 'all' );
		}

		if ( false !== strpos($hook_suffix, 'page_uwp_tools' )) {
			wp_enqueue_style( "userswp", USERSWP_PLUGIN_URL . 'assets/css/users-wp.css', array(), USERSWP_VERSION, 'all' );
		}

		if ( false !== strpos($hook_suffix, 'page_uwp_form_builder' )) {
			wp_enqueue_style( "uwp_form_builder", USERSWP_PLUGIN_URL . 'admin/assets/css/uwp-form-builder.css', array(), USERSWP_VERSION, 'all' );
		}

		if ( $hook_suffix == 'toplevel_page_userswp' ) {
			wp_enqueue_style( 'wp-color-picker' );
		}

		if ( in_array( $screen_id, uwp_get_screen_ids() ) ) {
			wp_enqueue_style( "userswp_admin_css", USERSWP_PLUGIN_URL . 'admin/assets/css/users-wp-admin.css', array(), USERSWP_VERSION, 'all' );
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
			wp_enqueue_script( "uwp_timepicker", USERSWP_PLUGIN_URL . 'assets/js/jquery.ui.timepicker.min.js', array(
				'jquery',
				'jquery-ui-datepicker',
				'jquery-ui-core'
			), USERSWP_VERSION );
			wp_enqueue_script( "userswp", USERSWP_PLUGIN_URL . 'assets/js/users-wp' . $suffix . '.js', array( 'jquery' ), USERSWP_VERSION, false );
			$uwp_localize_data = uwp_get_localize_data();
			wp_localize_script( 'userswp', 'uwp_localize_data', $uwp_localize_data );
			wp_enqueue_script( 'jquery-ui-progressbar', array( 'jquery' ) );
			wp_enqueue_script( 'jcrop', array( 'jquery' ) );
			wp_enqueue_script( "country-select", USERSWP_PLUGIN_URL . 'assets/js/countrySelect' . $suffix . '.js', array( 'jquery' ), USERSWP_VERSION );

			$country_data = uwp_get_country_data();
			wp_localize_script( USERSWP_NAME, 'uwp_country_data', $country_data );
		}

		if ( false !== strpos($hook_suffix, 'page_uwp_tools' )) {
			wp_enqueue_script( 'jquery-ui-progressbar', array( 'jquery' ) );
		}

		if ( false !== strpos($hook_suffix, 'page_uwp_status' )) {
			wp_enqueue_script( "uwp_status", USERSWP_PLUGIN_URL . 'admin/assets/js/system-status.js', array( 'jquery' ), USERSWP_VERSION, true );
		}

		if ( false !== strpos($hook_suffix, 'page_uwp_form_builder' )) {
			wp_enqueue_script( 'jquery-ui-sortable' );
			wp_enqueue_script( 'uwp-nestable-script', USERSWP_PLUGIN_URL . 'admin/assets/js/jquery.nestable' . $suffix . '.js', array( 'jquery-ui-sortable' ), USERSWP_VERSION );
			wp_enqueue_script( "uwp_form_builder", USERSWP_PLUGIN_URL . 'admin/assets/js/uwp-form-builder' . $suffix . '.js', array(), USERSWP_VERSION, 'all' );
		}

		if ( in_array( $screen_id, uwp_get_screen_ids() ) ) {
			wp_enqueue_script( "userswp_admin", USERSWP_PLUGIN_URL . 'admin/assets/js/users-wp-admin' . $suffix . '.js', array( 'jquery' ), USERSWP_VERSION, false );

			wp_enqueue_script( "jquery-ui-tooltip" );
			wp_enqueue_script( 'wp-color-picker' );

			$ajax_cons_data = array(
				'url'                                => admin_url( 'admin-ajax.php' ),
				'custom_field_not_blank_var'         => __( 'Field key must not be blank', 'userswp' ),
				'custom_field_options_not_blank_var' => __( 'Option Values must not be blank', 'userswp' ),
				'custom_field_not_special_char'      => __( 'Please do not use special character and spaces in field key.', 'userswp' ),
				'custom_field_unique_name'           => __( 'Field key should be a unique name.', 'userswp' ),
				'custom_field_delete'                => __( 'Are you sure you wish to delete this field?', 'userswp' ),
				'custom_field_id_required'           => __( 'This field is required.', 'userswp' ),
				'img_spacer'                         => admin_url( 'images/media-button-image.gif' ),
				'txt_choose_image'                   => __( 'Choose an image', 'userswp' ),
				'txt_use_image'                      => __( 'Use image', 'userswp' ),
				'delete_register_form'               => __( 'Are you sure you wish to delete this form?', 'userswp' ),
				'ask_register_form_title'            => __( 'Enter register form title', 'userswp' ),
				'form_updated_msg'                   => __( 'Updated! Reloading page...', 'userswp' )
			);
			wp_localize_script( "userswp_admin", 'uwp_admin_ajax', $ajax_cons_data );
		}

	}

	public function get_screen_ids(){
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
		if ( ! empty( $_GET['page'] ) && 'userswp' == $_GET['page']) {

			// check tab is general or not.
			if ( ! empty( $_GET['tab'] ) && 'general' == $_GET['tab']) {

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

		if(! empty( $_GET['page'] ) && 'uwp_form_builder' == $_GET['page']) {
			?>
            <script type="text/javascript">
                jQuery(document).ready(function () {
                    var uwp_registration_action = jQuery('#uwp_registration_action');
                    uwp_registration_action.on('change', function () {
                        var value = jQuery(this).val();
                        var register_redirect_obj = jQuery('#register_redirect_to');

                        if ('auto_approve_login' === value) {
                            register_redirect_obj.parent().parent().show();
                        } else {
                            register_redirect_obj.parent().parent().hide();
                        }
                    }).change();

                    var register_redirect_to = jQuery('#register_redirect_to');
                    register_redirect_to.on('change', function () {
                        var value = jQuery(this).val();
                        var register_redirect_custom_obj = jQuery('#register_redirect_custom_url');

                        if ('-2' === value) {
                            register_redirect_custom_obj.parent().show();
                            register_redirect_custom_obj.parent().prev().show();
                        } else {
                            register_redirect_custom_obj.parent().hide();
                            register_redirect_custom_obj.parent().prev().hide();
                        }
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
				$description = sprintf( __( 'You can change your profile picture on your <a href="%s">Profile Page</a>.', 'userswp' ),
					uwp_build_profile_tab_url( $user_id ) );
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
			include( 'views/html-email-template-preview.php' );
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
					'compare' => '='
				)
			)
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

			do_action('uwp_users_show_pending_email_activation', $query);

			$status = sanitize_text_field( urldecode( $_GET['uwp_status'] ) );

			if ( $status == 'pending-email-activate' ) {
				$query->query_where = str_replace( 'WHERE 1=1',
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
		$excluded_fields = apply_filters( 'uwp_exclude_edit_profile_fields', array('bio', 'register_gdpr', 'register_tos', 'uwp_language', 'multisite_site_title', 'multisite_site_address') );
		$query           = $wpdb->prepare("SELECT * FROM " . $table_name . " WHERE form_type = 'account' AND is_default = '0' AND form_id = %d", $form_id);
		if ( is_array( $excluded_fields ) && count( $excluded_fields ) > 0 ) {
			$query .= " AND htmlvar_name NOT IN ('" . implode( "','", $excluded_fields ) . "')";
		}
		$query  .= ' ORDER BY sort_order ASC';
		$fields = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

		$this->edit_profile_banner_fields( $user ); // Displays avatar and banner fields

		if ( $fields ) {
			?>
            <div class="uwp-profile-extra">
                <table class="uwp-profile-extra-table form-table bsui">
					<?php
					foreach ( $fields as $field ) {

						if(isset($field->css_class) && !empty($field->css_class)){
							$field->css_class = $field->css_class.' w-50';
                        } else {
							$field->css_class = 'w-50';
                        }

						if ( $field->field_type == 'fieldset' ) {
							?>
                            <tr style="margin: 0; padding: 0">
                                <th class="uwp-profile-extra-key" style="margin: 0; padding: 0"><h3
                                            style="margin: 10px 0;"><?php echo esc_html( $field->site_title ); ?></h3></th>
                                <td></td>
                            </tr>
							<?php
						} else { ?>
                            <tr>
                                <th class="uwp-profile-extra-key"><?php echo esc_html( $field->site_title ); ?></th>
                                <td class="uwp-profile-extra-value">
									<?php
									$templates_obj = new UsersWP_Templates();
									$templates_obj->template_fields_html( $field, 'account', $user->ID ); ?>
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
		$fields     = $wpdb->get_results( "SELECT * FROM " . $table_name . " WHERE (form_type = 'avatar' OR form_type = 'banner') ORDER BY sort_order ASC" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		if ( $fields ) {
			?>
            <div class="uwp-profile-extra uwp_page">
				<?php do_action( 'uwp_admin_profile_edit', $user ); ?>
                <h2><?php esc_html_e('UsersWP Account Details', 'userswp'); ?></h2>
                <table class="uwp-profile-extra-table form-table">
					<?php
					foreach ( $fields as $field ) {

						if ( $field->field_type == 'fieldset' ) {
							?>
                            <tr style="margin: 0; padding: 0">
                                <th class="uwp-profile-extra-key" style="margin: 0; padding: 0"><h3
                                            style="margin: 10px 0;">
										<?php echo esc_html( $field->site_title ); ?></h3></th>
                                <td></td>
                            </tr>
							<?php
						} else { ?>
                            <tr>
                                <th class="uwp-profile-extra-key"><?php echo esc_html( $field->site_title ); ?></th>
                                <td class="uwp-profile-extra-value">
									<?php
									if ( $field->htmlvar_name == "avatar" ) {
										$value = uwp_get_usermeta( $user->ID, "avatar_thumb", "" );
									} elseif ( $field->htmlvar_name == "banner" ) {
										$value = uwp_get_usermeta( $user->ID, "banner_thumb", "" );
									} else {
										$value = "";
									}

									echo $file_obj->file_upload_preview( $field, $value ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

									if ( $field->htmlvar_name == "avatar" ) {
										if ( ! empty( $value ) ) {
											$label = __( "Change Avatar", "userswp" );
										} else {
											$label = __( "Upload Avatar", "userswp" );
										}
										?>
                                        <a onclick="uwp_profile_image_change('avatar');return false;" href="#"
                                           class="uwp-banner-change-icon-admin">
											<?php echo esc_html( $label ); ?>
                                        </a>
										<?php
									} elseif ( $field->htmlvar_name == "banner" ) {
										if ( ! empty( $value ) ) {
											$label = __( "Change Banner", "userswp" );
										} else {
											$label = __( "Upload Banner", "userswp" );
										} ?>
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
				'user_id' => $user_id,
				'action'  => 'uwp_resend',
				'_nonce'  => wp_create_nonce( 'uwp_resend' ),
				'uwp_is_admin'  => 1,
			),
			admin_url( 'users.php' )
		);

		$activate_link = add_query_arg(
			array(
				'user_id' => $user_id,
				'action'  => 'uwp_activate_user',
				'_nonce'  => wp_create_nonce( 'uwp_activate_user' ),
				'uwp_is_admin'  => 1,
			),
			admin_url( 'users.php' )
		);

		if ( $mod_value == 'email_unconfirmed' ) {
			$actions['uwp_resend_activation'] = "<a class='' href='" . $resend_link . "'>" . __( 'Resend Activation', 'userswp' ) . "</a>";
			$actions['uwp_auto_activate']     = "<a class='' href='" . $activate_link . "'>" . __( 'Activate User', 'userswp' ) . "</a>";
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
		$action  = isset( $_REQUEST['action'] ) ? sanitize_text_field($_REQUEST['action']) : false;
		$nonce   = isset( $_REQUEST['_nonce'] ) ? sanitize_text_field($_REQUEST['_nonce']) : false;
		$is_admin   = isset( $_REQUEST['uwp_is_admin'] ) ? sanitize_text_field($_REQUEST['uwp_is_admin']) : false;

		if ( $user_id && 'uwp_resend' == $action && !empty($nonce) && wp_verify_nonce( $nonce, 'uwp_resend' ) ) {
			uwp_resend_activation_mail( $user_id );
			if ( isset($is_admin) && $is_admin ) {
				wp_redirect( add_query_arg( 'update', 'uwp_resend', admin_url( 'users.php' ) ) );
				exit();
			} else {
				global $uwp_notices;
				$message       = __( 'Activation email has been sent!', 'userswp' );
				$uwp_notices[] = aui()->alert( array(
					'type'    => 'success',
					'content' => $message
				) );
			}
		} elseif ( $user_id && 'uwp_activate_user' == $action && wp_verify_nonce( $nonce, 'uwp_activate_user' ) ) {
			if ( isset($is_admin) && $is_admin && current_user_can( 'edit_users' ) ) {
				$this->activate_user( $user_id );
				wp_redirect( add_query_arg( 'update', 'uwp_activate_user', admin_url( 'users.php' ) ) );
			}
		}
	}

	/**
	 * Show row meta on the plugin screen.
	 *
	 * @param	mixed $links Plugin Row Meta
	 * @param	mixed $file  Plugin Base file
	 * @return	array
	 */
	public static function plugin_row_meta( $links, $file ) {
		if ( USERSWP_PLUGIN_BASENAME == $file ) {
			$row_meta = array(
				'docs'    => '<a href="' . esc_url( 'https://docs.userswp.io/' ) . '" aria-label="' . esc_attr__( 'View UsersWP Documentation', 'userswp' ) . '">' . esc_html__( 'Docs', 'userswp' ) . '</a>',
				'support' => '<a href="' . esc_url( 'https://userswp.io/support/' ) . '" aria-label="' . esc_attr__( 'Visit UsersWP support', 'userswp' ) . '">' . esc_html__( 'Support', 'userswp' ) . '</a>',
				'translation' => '<a href="' . esc_url( 'https://userswp.io/translate/projects' ) . '" aria-label="' . esc_attr__( 'View translations', 'userswp' ) . '">' . esc_html__( 'Translations', 'userswp' ) . '</a>',
			);

			return array_merge( $links, $row_meta );
		}

		return (array) $links;
	}

	public function process_create_register_form() {

	    if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( -1 );
		}

		$type       = ! empty( $_POST['type'] ) ? sanitize_text_field($_POST['type']) : '';
		$form_title = ! empty( $_POST['form_title'] ) ? sanitize_title_with_dashes($_POST['form_title']) : '';
		$nonce      = ! empty( $_POST['nonce'] ) ? sanitize_text_field($_POST['nonce']) : '';

		$status   = false;
		$message  = __( 'Something went wrong. Please try again.', 'userswp' );
		$redirect = '';
		if ( ! empty( $type ) && $type == 'create' && ! empty( $nonce ) && wp_verify_nonce( $nonce, 'uwp-create-register-form-nonce' ) ) {

			$get_register_forms = uwp_get_option( 'multiple_registration_forms' );
			$new_form_id        = uwp_get_next_register_form_id();

			if ( ! empty( $new_form_id ) ) {

				$get_register_forms[] = array(
					'id'    => $new_form_id,
					'title' => ! empty( $form_title ) ? sanitize_text_field( $form_title ) : sprintf( __( 'Form %d', 'userswp' ), $new_form_id ),
				);

				uwp_update_option( 'multiple_registration_forms', $get_register_forms );

				$fields = UsersWP_Activator::uwp_default_custom_fields_account();
				$form_builder = new UsersWP_Form_Builder();

				if(isset($fields) && count($fields) > 0){
					foreach ($fields as $field_index => $field) {
						$field['form_id'] = $new_form_id;
						$form_builder->admin_form_field_save($field);
					}
                }

				UsersWP_Activator::insert_form_extras($new_form_id);

				do_action('uwp_create_register_form', $new_form_id);

				$status   = true;
				$redirect = admin_url( 'admin.php?page=uwp_form_builder&tab=account&form=' . $new_form_id );
			}
		}

		$response = array(
			'status'   => $status,
			'message'  => $message,
			'redirect' => $redirect,
		);

		echo json_encode( $response );
		wp_die();
	}

	public function process_update_register_form() {

		check_ajax_referer( 'uwp-update-register-form-nonce', 'uwp_update_register_form_nonce' );

	    if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( -1 );
		}

		$type    = ! empty( $_POST['type'] ) ? sanitize_text_field($_POST['type']) : '';
		$form_id = ! empty( $_POST['manage_field_form_id'] ) ? (int)$_POST['manage_field_form_id'] : '';
		$form_title = ! empty( $_POST['form_title'] ) ? sanitize_text_field($_POST['form_title']) : __( 'Form', 'userswp' );
		$user_role = ! empty( $_POST['user_role'] ) ? sanitize_text_field($_POST['user_role']) : '';
		$action = ! empty( $_POST['reg_action'] ) ? sanitize_text_field($_POST['reg_action']) : uwp_get_option( 'uwp_registration_action', 'auto_approve' );
		$redirect_to = (int) $_POST['redirect_to'];
		$custom_url = ! empty( $_POST['custom_url'] ) ? sanitize_text_field($_POST['custom_url']) : '';
		$gdpr_page = ! empty( $_POST['gdpr_page'] ) ? (int)$_POST['gdpr_page'] : (int)uwp_get_option('register_gdpr_page', false);
		$tos_page = ! empty( $_POST['tos_page'] ) ? (int)$_POST['tos_page'] : (int)uwp_get_option('register_terms_page', false);

		$status  = false;
		$message = __( 'Something went wrong. Please try again.', 'userswp' );
		if ( ! empty( $type ) && ! empty( $form_id ) && $type === 'update' ) {

			$register_forms = uwp_get_option( 'multiple_registration_forms' );

			if ( ! empty( $register_forms ) && is_array( $register_forms ) ) {

				foreach ( $register_forms as $key => $register_form ) {

					if ( ! empty( $register_form['id'] ) && $register_form['id'] == $form_id ) {
						$status                          = true;
						$register_forms[ $key ]['title'] = $form_title;
						$register_forms[ $key ]['user_role'] = $user_role;
						$register_forms[ $key ]['reg_action'] = $action;
						$register_forms[ $key ]['redirect_to'] = $redirect_to;
						$register_forms[ $key ]['custom_url'] = $custom_url;
						$register_forms[ $key ]['gdpr_page'] = $gdpr_page;
						$register_forms[ $key ]['tos_page'] = $tos_page;
					}
				}
			}

			$register_forms = array_values( $register_forms );
			$register_forms = apply_filters('uwp_multiple_registration_forms_update', $register_forms);
			uwp_update_option( 'multiple_registration_forms', $register_forms );
		}

		$response = array(
			'status'  => $status,
			'message' => $message,
		);

		echo json_encode( $response );
		wp_die();
	}

	public function process_remove_register_form() {

		check_ajax_referer( 'uwp-delete-register-form-nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( -1 );
		}

		global $wpdb;
		$table_name = uwp_get_table_prefix() . 'uwp_form_fields';

		$type    = ! empty( $_POST['type'] ) ? sanitize_text_field($_POST['type']) : '';
		$form_id = ! empty( $_POST['form_id'] ) ? (int)$_POST['form_id'] : '';

		$status   = false;
		$message  = __( 'Security nonce failed. Please try again.', 'userswp' );
		$redirect = '';
		if ( ! empty( $type ) && ! empty( $form_id ) && $type === 'remove' ) {

			$register_forms = uwp_get_option( 'multiple_registration_forms' );
			$form_builder = new UsersWP_Form_Builder();

			if ( ! empty( $register_forms ) && is_array( $register_forms ) ) {

				foreach ( $register_forms as $key => $register_form ) {

					if ( ! empty( $register_form['id'] ) && $register_form['id'] == $form_id ) {
						$status = true;
						unset( $register_forms[ $key ] );
						$fields = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
							$wpdb->prepare(
								"select id from  " . $table_name . " where form_type = %s AND form_id = %d order by sort_order asc",
								array('account', $form_id)
							)
						);

						if (!empty($fields)) {
							foreach ($fields as $field) {
								$form_builder->admin_form_field_delete($field->id, false, $form_id);
							}
						}
					}
				}
			}

			$register_forms = array_values( $register_forms );
			uwp_update_option( 'multiple_registration_forms', $register_forms );
			$redirect = admin_url( 'admin.php?page=uwp_form_builder&tab=account' );
		}

		$response = array(
			'status'   => $status,
			'message'  => $message,
			'redirect' => $redirect,
		);

		echo json_encode( $response );
		wp_die();
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
}