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
        wp_enqueue_style( "userswp_admin_css", USERSWP_PLUGIN_URL . 'admin/assets/css/users-wp-admin.css', array(), USERSWP_VERSION, 'all' );

        if ( $hook_suffix == 'toplevel_page_userswp' ) {
            wp_enqueue_style( 'wp-color-picker' );
        }

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
	function user_profile_picture_description($description) {
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
	function edit_profile_banner_fields($user) {
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

	function admin_body_class($classes) {
		$screen = get_current_screen();
		if ( 'profile' == $screen->base || 'user-edit' == $screen->base )
			$classes .= 'uwp_page';
		return $classes;
	}
}
