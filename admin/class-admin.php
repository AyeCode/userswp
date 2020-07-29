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
		add_action( 'admin_init', array( $this, 'preview_emails' ) );
		add_action( 'admin_init', array( $this, 'init_ayecode_connect_helper' ) );
		add_filter( 'views_users', array( $this, 'request_views_users' ) );
		add_filter( 'pre_user_query', array( $this, 'request_users_filter' ) );
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

	    wp_enqueue_script( 'jquery-ui-sortable' );
	    wp_enqueue_script( 'uwp-nestable-script', USERSWP_PLUGIN_URL . 'admin/assets/js/jquery.nestable' . $suffix . '.js', array('jquery-ui-sortable'), USERSWP_VERSION );

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
     * Adds UsersWP css to admin area
     *
     * @since       1.0.0
     * @package     userswp
     *
     * @return      void
     */
    public function admin_only_css() {
        ?>
        <style type="text/css">
            .uwp_page .uwp-bs-modal input[type="submit"].button,
            .uwp_page .uwp-bs-modal button.button {
                padding: 0 10px 1px;
            }
        </style>
        <?php
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
}