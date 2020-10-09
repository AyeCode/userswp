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
		add_action( 'admin_init', array($this, 'activation_redirect'));
		add_action( 'admin_init', array( $this, 'init_ayecode_connect_helper' ) );
		add_action( 'admin_enqueue_scripts', array($this, 'enqueue_styles') );
		add_action( 'admin_enqueue_scripts', array($this, 'enqueue_scripts') );
		add_action('admin_notices', array($this, 'show_update_messages'));
		add_action('admin_footer', array($this, 'admin_only_script'));
		add_action('user_profile_picture_description', array($this, 'user_profile_picture_description'));
		add_action('admin_body_class', array($this, 'admin_body_class'));
		add_action( 'admin_init', array( $this, 'preview_emails' ) );

		add_filter( 'views_users', array( $this, 'request_views_users' ) );
		add_filter( 'pre_user_query', array( $this, 'request_users_filter' ) );
		add_action( 'edit_user_profile', array($this, 'get_profile_extra_edit'), 10, 1 );
		add_action( 'show_user_profile', array($this, 'get_profile_extra_edit'), 10, 1 );
		add_filter('user_row_actions', array($this, 'user_row_actions'), 10, 2);
		add_action('bulk_actions-users', array($this, 'users_bulk_actions'));
		add_action('handle_bulk_actions-users', array($this, 'handle_users_bulk_actions'), 10, 3);
		add_filter('init', array($this, 'process_user_actions'));
	}

	/**
	 * Redirects to UsersWP info page after plugin activation.
	 *
	 * @since       1.0.0
	 * @package     userswp
	 * @return      void
	 */
	public function activation_redirect() {

		if (get_option('uwp_activation_redirect', false)) {
			delete_option('uwp_activation_redirect');
			update_option("uwp_setup_wizard_notice",1);
			wp_redirect(admin_url('index.php?page=uwp-setup'));
			exit;

		}

		if ( ! empty( $_GET['force_sync_data'] ) ) {
			$blog_id = get_current_blog_id();
			do_action( 'wp_' . $blog_id . '_uwp_updater_cron' );
			wp_safe_redirect( admin_url( 'admin.php?page=userswp' ) );
			exit;
		}

	}

	/**
	 * Maybe show the AyeCode Connect Notice.
	 */
	public function init_ayecode_connect_helper(){
		// AyeCode Connect notice
		if ( is_admin() ){
			// set the strings so they can be translated
			$strings = array(
				'connect_title' => __("UsersWP - an AyeCode product!","userswp"),
				'connect_external'  => __( "Please confirm you wish to connect your site?","userswp" ),
				'connect'           => sprintf( __( "<strong>Have a license?</strong> Forget about entering license keys or downloading zip files, connect your site for instant access. %slearn more%s","userswp" ),"<a href='https://ayecode.io/introducing-ayecode-connect/' target='_blank'>","</a>" ),
				'connect_button'    => __("Connect Site","userswp"),
				'connecting_button' => __("Connecting...","userswp"),
				'error_localhost'   => __( "This service will only work with a live domain, not a localhost.","userswp" ),
				'error'             => __( "Something went wrong, please refresh and try again.","userswp" ),
			);
			new AyeCode_Connect_Helper($strings,array('uwp-addons'));
		}
	}


    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     * @param $hook_suffix
     */
    public function enqueue_styles( $hook_suffix ) {

        if ( $hook_suffix == 'profile.php' || $hook_suffix == 'user-edit.php' ) {
            wp_register_style( 'jquery-ui', USERSWP_PLUGIN_URL .  'assets/css/jquery-ui.css' );
            wp_enqueue_style( 'jquery-ui' );
            wp_enqueue_style( 'jcrop' );
	        wp_enqueue_style( "uwp-country-select", USERSWP_PLUGIN_URL . 'assets/css/countryselect.css', array(), USERSWP_VERSION, 'all' );
            wp_enqueue_style( "userswp", USERSWP_PLUGIN_URL . 'assets/css/users-wp.css', array(), USERSWP_VERSION, 'all' );
            wp_enqueue_style( "uwp_timepicker_css", USERSWP_PLUGIN_URL . 'assets/css/jquery.ui.timepicker.css', array(), USERSWP_VERSION, 'all' );
        }

        if ( $hook_suffix == 'userswp_page_uwp_tools' ) {
            wp_enqueue_style( "userswp", USERSWP_PLUGIN_URL . 'assets/css/users-wp.css', array(), USERSWP_VERSION, 'all' );
        }

	    if ( $hook_suffix == 'userswp_page_uwp_form_builder' ) {
		    wp_enqueue_style( "uwp_form_builder", USERSWP_PLUGIN_URL . 'admin/assets/css/uwp-form-builder.css', array(), USERSWP_VERSION, 'all' );
	    }

	    if ( $hook_suffix == 'toplevel_page_userswp' ) {
		    wp_enqueue_style( 'wp-color-picker' );
	    }

        wp_enqueue_style( "userswp_admin_css", USERSWP_PLUGIN_URL . 'admin/assets/css/users-wp-admin.css', array(), USERSWP_VERSION, 'all' );

    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     * @param $hook_suffix
     */
    public function enqueue_scripts($hook_suffix) {

        $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

        if ( $hook_suffix == 'profile.php' || $hook_suffix == 'user-edit.php' ) {

            wp_enqueue_script( 'jquery-ui-datepicker', array( 'jquery' ) );
            wp_enqueue_script( "uwp_timepicker", USERSWP_PLUGIN_URL . 'assets/js/jquery.ui.timepicker.min.js', array( 'jquery', 'jquery-ui-datepicker', 'jquery-ui-core' ), USERSWP_VERSION );
            wp_enqueue_script( "userswp", USERSWP_PLUGIN_URL . 'assets/js/users-wp' . $suffix . '.js', array( 'jquery' ), USERSWP_VERSION, false );
            $uwp_localize_data = uwp_get_localize_data();
            wp_localize_script('userswp', 'uwp_localize_data', $uwp_localize_data );
            wp_enqueue_script( 'jquery-ui-progressbar', array( 'jquery' ) );
            wp_enqueue_script( 'jcrop', array( 'jquery' ) );
            wp_enqueue_script( "country-select", USERSWP_PLUGIN_URL . 'assets/js/countrySelect' . $suffix . '.js', array( 'jquery' ), USERSWP_VERSION );


        }
        if ( $hook_suffix == 'userswp_page_uwp_status' ) {
            wp_enqueue_script( 'jquery-ui-progressbar', array( 'jquery' ) );
            wp_enqueue_script( "uwp_status", USERSWP_PLUGIN_URL . 'admin/assets/js/system-status.js', array( 'jquery' ), USERSWP_VERSION, true );
        }

	    if ( $hook_suffix == 'userswp_page_uwp_form_builder' ) {
		    wp_enqueue_script( 'jquery-ui-sortable' );
		    wp_enqueue_script( 'uwp-nestable-script', USERSWP_PLUGIN_URL . 'admin/assets/js/jquery.nestable' . $suffix . '.js', array('jquery-ui-sortable'), USERSWP_VERSION );
		    wp_enqueue_script( "uwp_form_builder", USERSWP_PLUGIN_URL . 'admin/assets/js/uwp-form-builder' . $suffix . '.js', array(), USERSWP_VERSION, 'all' );
	    }

        wp_enqueue_script( "userswp_admin", USERSWP_PLUGIN_URL . 'admin/assets/js/users-wp-admin' . $suffix . '.js', array( 'jquery' ), USERSWP_VERSION, false );

        wp_enqueue_script( "jquery-ui-tooltip" );
        wp_enqueue_script( 'wp-color-picker' );

        $ajax_cons_data = array(
            'url' => admin_url( 'admin-ajax.php' ),
            'custom_field_not_blank_var' => __( 'Field key must not be blank', 'userswp' ),
            'custom_field_options_not_blank_var' => __( 'Option Values must not be blank', 'userswp' ),
            'custom_field_not_special_char' => __( 'Please do not use special character and spaces in field key.', 'userswp' ),
            'custom_field_unique_name' => __( 'Field key should be a unique name.', 'userswp' ),
            'custom_field_delete' => __( 'Are you sure you wish to delete this field?', 'userswp' ),
            'custom_field_id_required' => __( 'This field is required.', 'userswp' ),
            'img_spacer' => admin_url( 'images/media-button-image.gif' ),
            'txt_choose_image' => __( 'Choose an image', 'userswp' ),
            'txt_use_image' => __( 'Use image', 'userswp' ),
        );
        wp_localize_script( "userswp_admin", 'uwp_admin_ajax', $ajax_cons_data );

        $country_data = uwp_get_country_data();
        wp_localize_script( USERSWP_NAME, 'uwp_country_data', $country_data );

    }

	/**
	 * Displays update messages
	 */
	public function show_update_messages(){
		if ( !isset($_REQUEST['update']) ) return;

		$update = sanitize_text_field($_REQUEST['update']);
		$messages = array();

		switch($update) {
			case 'uwp_resend':
				$messages['msg'] = __('Activation email has been sent!','userswp');
				break;
			case 'err_uwp_resend':
				$messages['err_msg'] = __('Error while sending activation email. Please try again.','userswp');
				break;
			case 'uwp_activate_user':
				$messages['msg'] = __('User(s) has been activated!','userswp');
				break;
		}

		if ( !empty( $messages ) ) {
			if ( isset($messages['err_msg'])) {
				echo '<div class="notice notice-error"><p>' . $messages['err_msg'] . '</p></div>';
			} else {
				echo '<div class="notice notice-success is-dismissible"><p>' . $messages['msg'] . '</p></div>';
			}
		}
	}

	/**
	 * Adds UsersWP JS to admin area.
	 *
	 * @since 1.0.0
	 * @package userswp
	 *
	 * @return      void
	 */
	public function admin_only_script() {

		// check page is userswp or not.
		if( !empty( $_GET['page'] ) && 'userswp' == $_GET['page'] ) {

			// check tab is general or not.
			if( !empty( $_GET['tab'] ) && 'general' == $_GET['tab'] ) {

				// check for login section.
				if( !empty( $_GET['section'] ) && 'login' == $_GET['section'] ) {
					?>
                    <script type="text/javascript">
                        jQuery( document ).ready(function() {
                            var login_redirect_to = jQuery('#login_redirect_to');
                            login_redirect_to.on( 'change', function() {
                                var value = jQuery( this ).val();
                                var login_redirect_custom_obj = jQuery('#login_redirect_custom_url');

                                if( '-2' === value ) {
                                    login_redirect_custom_obj.parent().parent().show();
                                } else{
                                    login_redirect_custom_obj.parent().parent().hide();
                                }
                            } ).change();
                        });
                    </script>
					<?php
				}

				// check for registration section.
				if( !empty( $_GET['section'] ) && 'register' == $_GET['section'] ) {
					?>
                    <script type="text/javascript">
                        jQuery( document ).ready(function() {
                            var uwp_registration_action = jQuery('#uwp_registration_action');
                            uwp_registration_action.on( 'change', function() {
                                var value = jQuery( this ).val();
                                var register_redirect_obj = jQuery('#register_redirect_to');

                                if( 'auto_approve_login' === value || 'force_redirect' === value ) {
                                    register_redirect_obj.parent().parent().show();
                                } else{
                                    register_redirect_obj.parent().parent().hide();
                                }
                            } ).change();

                            var register_redirect_to = jQuery('#register_redirect_to');
                            register_redirect_to.on( 'change', function() {
                                var value = jQuery( this ).val();
                                var register_redirect_custom_obj = jQuery('#register_redirect_custom_url');

                                if( '-2' === value ) {
                                    register_redirect_custom_obj.parent().parent().show();
                                } else{
                                    register_redirect_custom_obj.parent().parent().hide();
                                }
                            } ).change();
                        });
                    </script>
					<?php
				}
			}

		}

	}

	/**
	 * Filters the user profile picture description displayed under the Gravatar.
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 * @param       string      $description    Profile picture description.
	 *
	 * @return      string                      Modified description.
	 */
	public function user_profile_picture_description($description) {
		if (is_admin() && IS_PROFILE_PAGE) {
			$user_id = get_current_user_id();
			$avatar = uwp_get_usermeta($user_id, 'avatar_thumb', '');

			if (!empty($avatar)) {
				$description = sprintf( __( 'You can change your profile picture on your <a href="%s">Profile Page</a>.', 'userswp' ),
					uwp_build_profile_tab_url( $user_id ));
			}

		}
		return $description;
	}

	public function admin_body_class($classes) {
		$screen = get_current_screen();
		if ( 'profile' == $screen->base || 'user-edit' == $screen->base )
			$classes .= 'uwp_page';
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

			$message 	= UsersWP_Mails::email_wrap_message( $message, $email_name, $email_vars, '', $plain_text );
			$message 	= UsersWP_Mails::style_body( $message, $email_name, $email_vars );
			$message 	= apply_filters( 'uwp_mail_content', $message, $email_name, $email_vars );

			// Print the preview email content.
			if ( $plain_text ) {
				echo '<div style="white-space:pre-wrap;font-family:sans-serif">';
			}
			echo $message;
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

		if ( isset($_REQUEST['uwp_status']) && $_REQUEST['uwp_status'] == 'pending-email-activate' ) {
			$views['all'] = str_replace('current','', $views['all']);
			$current = 'current';
		}

		$views['pending-email-activate'] = '<a href="'.admin_url('users.php').'?uwp_status=pending-email-activate" class="' . $current . '">'. __('Pending Email Activation','userswp') . ' <span class="count">(' . $this->uwp_pending_email_count() . ')</span></a>';

		return $views;
	}

	/**
	 * Returns pending email activation user counts
	 *
	 * @return int User count
	 */
	public function uwp_pending_email_count() {

		$args = array(
			'fields' => 'ID',
			'number' => 0,
			'meta_query' => array(
				array(
					'key' => 'uwp_mod',
					'value' => 'email_unconfirmed',
					'compare' => '='
				)
			)
		);
		$users = new WP_User_Query( $args );
		return isset($users->results) ? (int) count($users->results) : 0;
	}

	/**
	 * Filter to modify the user query
	 *
	 * @return object User query.
	 */
	public function request_users_filter( $query ) {

		global $wpdb, $pagenow;

		remove_filter('pre_user_query', array(&$this, 'request_users_filter') );

		if ( is_admin() && $pagenow=='users.php' && isset($_GET[ 'uwp_status' ]) && $_GET[ 'uwp_status' ] != '') {

			$status = sanitize_text_field(urldecode( $_GET['uwp_status'] ));

			if ( $status == 'pending-email-activate') {
				$query->query_where = str_replace('WHERE 1=1',
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
	 * Adds avatar and banner fields in admin side.
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 * @param       object      $user       User object.
	 *
	 * @return      void
	 */
	public function edit_profile_banner_fields($user) {
		global $wpdb;

		$file_obj = new UsersWP_Files();

		$table_name = uwp_get_table_prefix() . 'uwp_form_fields';
		$fields = $wpdb->get_results("SELECT * FROM " . $table_name . " WHERE (form_type = 'avatar' OR form_type = 'banner') ORDER BY sort_order ASC");
		if ($fields) {
			?>
            <div class="uwp-profile-extra uwp_page">
				<?php do_action('uwp_admin_profile_edit', $user ); ?>
                <table class="uwp-profile-extra-table form-table">
					<?php
					foreach ($fields as $field) {

						// Icon
						$icon = uwp_get_field_icon( $field->field_icon );

						if ($field->field_type == 'fieldset') {
							?>
                            <tr style="margin: 0; padding: 0">
                                <th class="uwp-profile-extra-key" style="margin: 0; padding: 0"><h3 style="margin: 10px 0;">
										<?php echo $icon.$field->site_title; ?></h3></th>
                                <td></td>
                            </tr>
							<?php
						} else { ?>
                            <tr>
                                <th class="uwp-profile-extra-key"><?php echo $icon.$field->site_title; ?></th>
                                <td class="uwp-profile-extra-value">
									<?php
									if ( $field->htmlvar_name == "avatar" ) {
										$value = uwp_get_usermeta( $user->ID, "avatar_thumb", "" );
									} elseif ( $field->htmlvar_name == "banner" ) {
										$value = uwp_get_usermeta( $user->ID, "banner_thumb", "" );
									} else {
										$value = "";
									}

									echo $file_obj->file_upload_preview( $field, $value );

									if ( $field->htmlvar_name == "avatar" ) {
										if ( ! empty( $value ) ) {
											$label = __( "Change Avatar", "userswp" );
										} else {
											$label = __( "Upload Avatar", "userswp" );
										}
										?>
                                        <a onclick="uwp_profile_image_change('avatar');return false;" href="#"
                                           class="uwp-banner-change-icon-admin">
											<?php echo $label; ?>
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
											<?php echo $label; ?>
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
	 * Gets UsersWP fields in WP-Admin Users Edit page.
	 *
	 * @since       1.0.0
	 * @package     userswp
	 * @param       object      $user       User Object.
	 */
	public function get_profile_extra_edit($user) {
		global $wpdb;
		$table_name = uwp_get_table_prefix() . 'uwp_form_fields';
		$excluded_fields = apply_filters('uwp_exclude_edit_profile_fields', array());
		$query = "SELECT * FROM " . $table_name . " WHERE form_type = 'account' AND is_default = '0'";
		if(is_array($excluded_fields) && count($excluded_fields) > 0){
			$query .= 'AND htmlvar_name NOT IN ('.implode(',', $excluded_fields).')';
		}
		$query .= ' ORDER BY sort_order ASC';
		$fields = $wpdb->get_results($query);

		$this->edit_profile_banner_fields($user); // Displays avatar and banner fields

		if ($fields) {
			?>
            <div class="uwp-profile-extra">
                <table class="uwp-profile-extra-table form-table">
					<?php
					foreach ($fields as $field) {

						// Icon
						$icon = uwp_get_field_icon( $field->field_icon );

						if ($field->field_type == 'fieldset') {
							?>
                            <tr style="margin: 0; padding: 0">
                                <th class="uwp-profile-extra-key" style="margin: 0; padding: 0"><h3 style="margin: 10px 0;"><?php echo $icon.$field->site_title; ?></h3></th>
                                <td></td>
                            </tr>
							<?php
						} else { ?>
                            <tr>
                                <th class="uwp-profile-extra-key"><?php echo $icon.$field->site_title; ?></th>
                                <td class="uwp-profile-extra-value">
									<?php
									$templates_obj = new UsersWP_Templates();
									$templates_obj->template_fields_html($field, 'account', $user->ID); ?>
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
	 * @package     userswp
	 *
	 * @param       array      $actions          Date string.
	 * @param       object     $user_object      The User ID.
	 *
	 * @return      array   Row actions.
	 */
	public function user_row_actions($actions, $user_object){
		$user_id = $user_object->ID;
		$mod_value = get_user_meta( $user_id, 'uwp_mod', true );
		$resend_link = add_query_arg(
			array(
				'user_id' => $user_id,
				'action'    => 'uwp_resend',
				'_nonce'  => wp_create_nonce('uwp_resend'),
			),
			admin_url( 'users.php' )
		);

		$activate_link = add_query_arg(
			array(
				'user_id' => $user_id,
				'action'    => 'uwp_activate_user',
				'_nonce'  => wp_create_nonce('uwp_activate_user'),
			),
			admin_url( 'users.php' )
		);

		if ($mod_value == 'email_unconfirmed') {
			$actions['uwp_resend_activation'] = "<a class='' href='" . $resend_link . "'>" . __( 'Resend Activation','userswp') . "</a>";
			$actions['uwp_auto_activate'] = "<a class='' href='" . $activate_link . "'>" . __( 'Activate User','userswp') . "</a>";
		}

		return $actions;
	}

	/**
	 * Returns users bulk actions
	 *
	 * @package     userswp
	 *
	 * @param       array      $bulk_actions    Bulk actions.
	 *
	 * @return      array   Bulk actions.
	 */
	public function users_bulk_actions($bulk_actions){
		$bulk_actions['uwp_resend'] = __( 'Resend Activation', 'userswp');
		$bulk_actions['uwp_activate_user'] = __( 'Activate Users', 'userswp');
		return $bulk_actions;
	}

	/**
	 * Handles users bulk actions
	 *
	 * @package     userswp
	 *
	 * @param       string      $redirect_to    Bulk actions.
	 * @param       string      $doaction    Current action.
	 * @param       array      $user_ids    User IDs to process.
	 *
	 * @return      string   Redirect URL.
	 */
	public function handle_users_bulk_actions($redirect_to, $doaction, $user_ids){
		if ( 'uwp_resend' == $doaction ) {
			foreach ( $user_ids as $user_id ) {
				$this->resend_activation_mail($user_id);
			}

			$redirect_to = add_query_arg( 'update', 'uwp_resend', $redirect_to );
		} elseif('uwp_activate_user' == $doaction){
			foreach ( $user_ids as $user_id ) {
				$this->activate_user($user_id);
			}
			$redirect_to = add_query_arg( 'update', 'uwp_activate_user', $redirect_to );
		}

		return $redirect_to;
	}

	/**
	 * Processes user action
	 *
	 * @package     userswp
	 *
	 * @return      mixed
	 */
	public function process_user_actions(){
		$user_id = isset($_REQUEST['user_id']) ? (int)$_REQUEST['user_id'] : 0;
		$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : false;
		$nonce = isset($_REQUEST['_nonce']) ? $_REQUEST['_nonce'] : false;

		if($user_id && 'uwp_resend' == $action && wp_verify_nonce( $nonce,'uwp_resend')){
			$send_result = $this->resend_activation_mail($user_id);
			if(!is_admin()){
				global $uwp_notices;

				if($send_result) {
					$message = __('Activation email has been sent!', 'userswp');
					$uwp_notices[] = aui()->alert(array(
						'type'=>'success',
						'content'=> $message
					));
				} else {
					$message = __('Error while processing request. Please contact site admin.', 'userswp');
					$uwp_notices[] = aui()->alert(array(
						'type'=>'error',
						'content'=> $message
					));
				}
				return;
			}
			if(!$send_result){
				wp_redirect( add_query_arg( 'update', 'err_uwp_resend', admin_url('users.php') ) );
			}
			wp_redirect( add_query_arg( 'update', 'uwp_resend', admin_url('users.php') ) );
			exit();
		} elseif($user_id && 'uwp_activate_user' == $action && wp_verify_nonce( $nonce,'uwp_activate_user')){
			if(is_admin() && current_user_can('edit_users')){
				$this->activate_user($user_id);
				wp_redirect( add_query_arg( 'update', 'uwp_activate_user', admin_url('users.php') ) );
			}
		}
	}

	/**
	 * Sends activation email to user
	 *
	 * @package     userswp
	 *
	 * @param       int      $user_id    User ID.
	 *
	 * @return      bool
	 */
	public function resend_activation_mail($user_id = 0){
		if(!$user_id){
			return false;
		}
		if( 'email_unconfirmed' == get_user_meta( $user_id, 'uwp_mod', true )){
			$user_data = get_userdata($user_id);

			$activation_link = uwp_get_activation_link($user_id);

			if($activation_link){

				$message = __('To activate your account, visit the following address:', 'userswp') . "\r\n\r\n";

				$message .= "<a href='".esc_url($activation_link)."' target='_blank'>".esc_url($activation_link)."</a>" . "\r\n";

				$activate_message = '<p><b>' . __('Please activate your account :', 'userswp') . '</b></p><p>' . $message . '</p>';

				$activate_message = apply_filters('uwp_activation_mail_message', $activate_message, $user_id);

				$email_vars = array(
					'user_id' => $user_id,
					'login_details' => $activate_message,
					'activation_link' => $activation_link,
				);

				$send_result = UsersWP_Mails::send($user_data->user_email, 'registration_activate', $email_vars);

				return $send_result;
			}
		}
		return true;
	}

	/**
	 * Activates user
	 *
	 * @param int $user_id User ID
	 *
	 * @return bool
	 */
	public function activate_user($user_id = 0){
		if(!$user_id){
			return false;
		}

		$uwp_mode = get_user_meta( $user_id, 'uwp_mod', true );
		if( 'email_unconfirmed' == $uwp_mode ){
			delete_user_meta( $user_id, 'uwp_mod');
			do_action('uwp_email_activation_success', $user_id);
		}

		return true;
	}
}