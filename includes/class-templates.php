<?php

/**
 * Template related functions
 *
 * This class defines all code necessary for UsersWP templates like login. register etc.
 *
 * @since      1.0.0
 * @author     GeoDirectory Team <info@wpgeodirectory.com>
 */
class UsersWP_Templates {

	/**
	 * The function is use for Retrieve the name of the highest
	 * priority template file that exists.
	 *
	 * @param string $template_name Template files to search for, in order.
	 * @param string $template_path Optional. Template path. Default null.
	 * @param string $default_path  Optional. Default path. Default null.
	 *
	 * @return string Template path.
	 */
	public static function locate_template( $template_name, $template_path = '', $default_path = '' ) {

		if ( ! $template_path ) {
			$template_path = uwp_get_theme_template_dir_name();
		}

		if ( ! $default_path ) {
			$default_path = uwp_get_templates_dir();
		}

		// Look within passed path within the theme - this is priority.
		$template = locate_template(
			array(
				untrailingslashit( $template_path ) . '/' . $template_name,
				$template_name,
			)
		);

		// Get default template
		if ( ! $template ) {
			$template = untrailingslashit( $default_path ) . '/' . $template_name;
		}

		// Return what we found.
		return apply_filters( 'uwp_locate_template', $template, $template_name, $template_path );
	}

	/**
	 *
	 * Displays default content for UWP pages
	 *
	 * @param $content
	 *
	 * @return string
	 */
	public static function setup_singular_page_content( $content ) {

		global $post, $wp_query;

		if ( ! is_uwp_page() ) {
			return $content;
		}

		if ( ! ( ! empty( $wp_query ) && ! empty( $post ) && ( $post->ID == get_queried_object_id() ) ) ) {
			return $content;
		}

		/*
		 * Some page builders need to be able to take control here so we add a filter to bypass it on the fly
		 */
		if ( apply_filters( 'uwp_bypass_setup_singular_page', false ) ) {
			return $content;
		}

		remove_filter( 'the_content', array( __CLASS__, 'setup_singular_page_content' ) );

		if ( in_the_loop() ) {

			if ( $content == '' ) {
				if ( is_uwp_profile_page() ) {
					$content = '[uwp_profile]';
				} elseif ( is_uwp_register_page() ) {
					$content = '[uwp_register]';
				} elseif ( is_uwp_login_page() ) {
					$content = '[uwp_login]';
				} elseif ( is_uwp_forgot_page() ) {
					$content = '[uwp_forgot]';
				} elseif ( is_uwp_change_page() ) {
					$content = '[uwp_change]';
				} elseif ( is_uwp_reset_page() ) {
					$content = '[uwp_reset]';
				} elseif ( is_uwp_account_page() ) {
					$content = '[uwp_account]';
				} elseif ( is_uwp_users_page() ) {
					$content = '[uwp_users]';
				} elseif ( is_uwp_users_item_page() ) {
					$content = UsersWP_Defaults::page_user_list_item_content();
				} else {
					// do nothing
				}

				// run the shortcodes on the content
				$content = do_shortcode( $content );

				// run block content if its available
				if ( function_exists( 'do_blocks' ) ) {
					$content = do_blocks( $content );
				}
			}

		}

		// add our filter back
		add_filter( 'the_content', array( __CLASS__, 'setup_singular_page_content' ) );


		return $content;
	}

	/**
	 *
	 * Returns content for the users list item template
	 *
	 * @return string
	 */
	public static function users_list_item_template_content() {

		$item_page_id = uwp_get_option( 'user_list_item_page', 0 );
		$content   = get_post_field( 'post_content', $item_page_id );

		/*
		 * Some page builders need to be able to take control here so we add a filter to bypass it on the fly
		 */
		$bypass_content = apply_filters( 'uwp_bypass_users_list_item_template_content', '', $content, $item_page_id );
		if ( $bypass_content ) {
			return $bypass_content;
		}

		// if the content is blank then we grab the page defaults
		if ( $content == '' ) {
			$content = UsersWP_Defaults::page_user_list_item_content();
		}

		// run the shortcodes on the content
		$content = do_shortcode( $content );

		// run block content if its available
		if ( function_exists( 'do_blocks' ) ) {
			$content = do_blocks( $content );
		}


		return $content;
	}

	/**
	 * Disable our sub templates access from frontend.
	 *
	 * @global object $post WordPress Post object.
	 *
	 * @since 1.2.1.2
	 */
	public static function redirect_templates_sub_pages() {
		global $post;
		if ( isset( $post->ID ) && ! current_user_can( 'administrator' ) && (
				$post->ID == uwp_get_page_id( 'user_list_item_page' )
			) ) {
			wp_redirect( home_url(), 301 );
			exit;
		}
	}

	/**
	 * Doing some access checks for UsersWP related pages.
	 *
	 * @return      bool
	 * @package     userswp
	 * @since       1.0.0
	 */
	public function access_checks() {
		global $post;

		if ( ! is_page() ) {
			return false;
		}

		if ( uwp_is_page_builder() ) {
			return false;
		}

		$current_page_id = $post->ID;

		$register_page = uwp_get_page_id( 'register_page', false );
		$login_page    = uwp_get_page_id( 'login_page', false );
		$forgot_page   = uwp_get_page_id( 'forgot_page', false );
		$reset_page    = uwp_get_page_id( 'reset_page', false );

		$change_page  = uwp_get_page_id( 'change_page', false );
		$account_page = uwp_get_page_id( 'account_page', false );

		if ( ( $register_page && ( (int) $register_page == $current_page_id ) ) ||
		     ( $login_page && ( (int) $login_page == $current_page_id ) ) ||
		     ( $forgot_page && ( (int) $forgot_page == $current_page_id ) ) ||
		     ( $reset_page && ( (int) $reset_page == $current_page_id ) ) ) {
			if ( is_user_logged_in() ) {
				$redirect_page_id = uwp_get_page_id( 'account_page', false );

				if ( isset( $_REQUEST['redirect_to'] ) && ! empty( $_REQUEST['redirect_to'] ) ) {
					$redirect_to = esc_url_raw( $_REQUEST['redirect_to'] );
				} elseif ( isset( $redirect_page_id ) && (int) $redirect_page_id > 0 ) {
					$redirect_to = get_permalink( $redirect_page_id );
				} else {
					$redirect_to = home_url( '/' );
                }

				$redirect_to = apply_filters( 'uwp_logged_in_redirect', $redirect_to );
				wp_safe_redirect( $redirect_to );
				exit();
			}
		} elseif ( $account_page && ( (int) $account_page == $current_page_id ) ||
		           ( $change_page && ( (int) $change_page == $current_page_id ) ) ) {
			if ( ! is_user_logged_in() ) {
				wp_safe_redirect( get_permalink( $login_page ) );
				exit();
			} else {
				$can_user_can_edit_account = apply_filters( 'uwp_user_can_edit_own_profile', true, get_current_user_id() );
				if ( ! $can_user_can_edit_account && ( (int) $account_page == $current_page_id ) ) {
					wp_safe_redirect( home_url( '/' ) );
					exit();
				}
			}
		} else {
			return false;
		}

		return false;
	}

	/**
	 * If auto generated password, redirects to change password page.
	 *
	 * @return      void
	 * @package     userswp
	 * @since       1.0.0
	 */
	public function change_default_password_redirect() {
		if ( ! is_user_logged_in() ) {
			return;
		}
		if ( 1 == uwp_get_option( 'change_disable_password_nag' ) ) {
			return;
		}

		if ( uwp_is_page_builder() ) {
			return;
		}

		$change_page  = uwp_get_page_id( 'change_page', false );
		$password_nag = get_user_option( 'default_password_nag', get_current_user_id() );

		if ( $password_nag ) {
			if ( is_page() ) {
				global $post;
				$current_page_id = $post->ID;
				if ( $change_page && ( (int) $change_page == $current_page_id ) ) {
					return;
				}
			}
			if ( $change_page ) {
				wp_safe_redirect( get_permalink( $change_page ) );
				exit();
			}
		}
	}

	/**
	 * Redirects /profile to /profile/{username} for loggedin users.
	 *
	 * @return      void
	 * @package     userswp
	 * @since       1.0.0
	 */
	public function profile_redirect() {
		if ( uwp_is_page_builder() ) {
			return;
		}

		if ( is_page() ) {
			global $wp_query, $post;
			$current_page_id = $post->ID;
			$profile_page    = uwp_get_page_id( 'profile_page', false );
			if ( $profile_page && ( (int) $profile_page == $current_page_id ) ) {

				if ( isset( $wp_query->query_vars['uwp_profile'] ) ) {
					//must be profile page
					$url_type    = apply_filters( 'uwp_profile_url_type', 'slug' );
					$author_slug = $wp_query->query_vars['uwp_profile'];
					if ( $url_type == 'id' ) {
						$user = get_user_by( 'id', $author_slug );
					} else {
						$user = get_user_by( 'slug', $author_slug );
					}

					if ( ! isset( $user->ID ) ) {
						global $wp_query;
						$wp_query->set_404();
						status_header( 404 );
					}

				} else {
					if ( is_user_logged_in() ) {
						$user_id     = get_current_user_id();
						$obj         = new UsersWP_Profile();
						$profile_url = $obj->get_profile_link( get_author_posts_url( $user_id ), $user_id );
						wp_safe_redirect( $profile_url );
						exit();
					} else {
						$redirect_to = apply_filters( 'uwp_no_login_profile_redirect', home_url( '/' ) );
						wp_safe_redirect( $redirect_to );
						exit();
					}

				}

			}
		}
	}

	/**
	 * Redirects user to a predefined page after logging out.
	 *
	 * @return      void
	 * @package     userswp
	 * @since       1.0.0
	 */
	public function logout_redirect() {
		$redirect_page_id = uwp_get_page_id( 'logout_redirect_to' );
		if ( isset( $_REQUEST['redirect_to'] ) ) {
			$redirect_to = esc_url( $_REQUEST['redirect_to'] );
		} elseif ( isset( $redirect_page_id ) && (int) $redirect_page_id > 0 ) {
			$redirect_to = get_permalink( $redirect_page_id );
		} else {
			$redirect_to = home_url( '/' );
		}
		$redirect_to = apply_filters( 'uwp_logout_redirect', $redirect_to );
		wp_safe_redirect( $redirect_to );
		exit();
	}

	/**
	 * Redirects wp-login.php to UsersWP login page.
	 *
	 * @return      void
	 * @package     userswp
	 * @since       1.0.0
	 */
	public function wp_login_redirect() {
		global $pagenow;
		if ( 'wp-login.php' == $pagenow && ! isset( $_REQUEST['action'] ) ) {
			$login_page_id  = uwp_get_page_id( 'login_page', false );
			$block_wp_login = uwp_get_option( 'block_wp_login', '' );
			if ( $login_page_id && $block_wp_login == '1' ) {
				$redirect_to = get_permalink( $login_page_id );
				if ( $redirect_to ) {
					$redirect_to = add_query_arg( 'redirect_to', admin_url(), $redirect_to );
				}
				wp_safe_redirect( $redirect_to );
				exit();
			}
		}
	}

	/**
	 * Redirects wp-login.php?action=register to UsersWP registration page.
	 *
	 * @return      void
	 * @package     userswp
	 * @since       1.0.0
	 */
	public function wp_register_redirect() {

		global $pagenow;
		if ( 'wp-login.php' == $pagenow && isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'register' ) {
			$reg_page_id  = uwp_get_page_id( 'register_page' );
			$block_wp_reg = uwp_get_option( 'wp_register_redirect' );
			if ( $reg_page_id && $block_wp_reg == '1' ) {

				$redirect    = isset( $_REQUEST['redirect_to'] ) ? esc_url( $_REQUEST['redirect_to'] ) : '';
				$redirect_to = get_permalink( $reg_page_id );
				if ( $redirect ) {
					$redirect_to = add_query_arg( 'redirect_to', $redirect, $redirect_to );
				}
				wp_safe_redirect( $redirect_to );
				exit();
			}
		}
	}

	/**
	 * Changes the login url to the UWP login page.
	 *
	 * @param $login_url    string The URL for login.
	 * @param $redirect     string The URL to redirect back to upon successful login.
	 * @param $force_reauth bool Whether to force reauthorization, even if a cookie is present.
	 *
	 * @return string The login url.
	 * @package userswp
	 *
	 * @since   1.0.12
	 */
	public function wp_login_url( $login_url, $redirect, $force_reauth ) {
		global $pagenow;

		if ( class_exists( 'Jetpack' ) && 'wp-login.php' == $pagenow && Jetpack::is_module_active( 'sso' ) ) {
			return $login_url; // Do not change the URL for Jetpack SSO
		}

		$login_page_id    = uwp_get_page_id( 'login_page', false );
		$redirect_page_id = uwp_get_page_id( 'login_redirect_to' );
		if ( ( ! is_admin() || wp_doing_ajax() ) && $login_page_id ) {
			$login_page = get_permalink( $login_page_id );
			if ( $redirect ) {
				$login_url = add_query_arg( 'redirect_to', urlencode( $redirect ), $login_page );
			} elseif ( isset( $redirect_page_id ) && (int) $redirect_page_id == - 1 && wp_get_referer() ) {
				$redirect_to = esc_url( wp_get_referer() );
				$login_url   = add_query_arg( 'redirect_to', $redirect_to, $login_page );
			} elseif ( isset( $redirect_page_id ) && $redirect_page_id > 0 ) {
				$redirect_to = get_permalink( $redirect_page_id );
				$login_url   = add_query_arg( 'redirect_to', $redirect_to, $login_page );
			} else {
				$login_url = $login_page;
			}
		}

		return $login_url;
	}

	/**
	 * Changes the register url with the UWP register page.
	 *
	 * @param $register_url string The URL for register.
	 *
	 * @return string The register url.
	 * @since 1.0.22
	 *
	 */
	public function wp_register_url( $register_url ) {
		$register_page_id = uwp_get_page_id( 'register_page' );
		$redirect_page_id = uwp_get_page_id( 'register_redirect_to' );

		$redirect = isset( $_REQUEST['redirect_to'] ) ? esc_url( $_REQUEST['redirect_to'] ) : '';
		if ( isset( $register_page_id ) && $register_page_id > 0 ) {
			$register_page = get_permalink( $register_page_id );
			if ( $register_url && isset( $redirect ) && ! empty( $redirect ) ) {
				$register_url = add_query_arg( 'redirect_to', $redirect, $register_page );
			} elseif ( (int) $redirect_page_id > 0 ) {
				$redirect_to  = get_permalink( $redirect_page_id );
				$register_url = add_query_arg( 'redirect_to', $redirect_to, $register_page );
			} else {
				$register_url = $register_page;
			}
		}

		return $register_url;
	}

	/**
	 * Changes the lost password url with the UWP page.
	 *
	 * @param $lostpassword_url string The URL for lost password.
	 *
	 * @return string The lost password page url.
	 */
	public function wp_lostpassword_url( $lostpassword_url ) {
		$forgot_page_url = uwp_get_forgot_page_url();

		if ( is_multisite() && isset( $_GET['redirect_to'] ) && false !== strpos( wp_unslash( $_GET['redirect_to'] ), network_admin_url() ) ) {
			return $lostpassword_url;
		}

		$redirect = isset( $_REQUEST['redirect_to'] ) ? esc_url( $_REQUEST['redirect_to'] ) : '';
		if ( $forgot_page_url ) {
			if ( $forgot_page_url && isset( $redirect ) && ! empty( $redirect ) ) {
				$lostpassword_url = add_query_arg( 'redirect_to', $redirect, $forgot_page_url );
			} else {
				$lostpassword_url = $forgot_page_url;
			}
		}

		return $lostpassword_url;
	}

	/**
	 * Prints html for form fields of that particular form.
	 *
	 * @param string $form_type Form type.
	 *
	 * @return      void
	 * @since       1.0.0
	 * @package     userswp
	 */
	public function template_fields( $form_type, $args = array() ) {

		global $wpdb;
		$table_name        = uwp_get_table_prefix() . 'uwp_form_fields';
		$extras_table_name = uwp_get_table_prefix() . 'uwp_form_extras';

		$form_id = ! empty( $args['id'] ) ? (int) $args['id'] : uwp_get_option('register_modal_form', 1);
		if ( $form_type == 'register' ) {
			$fields = get_register_form_fields($form_id);
			$form_limit = ! empty( $args['limit'] ) ? $args['limit'] : '';
			if(isset($form_limit) && !is_array($form_limit)){
				$form_limit = explode(',', $form_limit);
			}

			if(isset($form_limit) && !empty($form_limit) && count($form_limit) > 1){

				$form_limit = array_map('uwp_clean', $form_limit);
				$form_limit = array_map('trim', $form_limit);
				$options  = uwp_get_register_forms_dropdown_options($form_limit);
				$id         = wp_doing_ajax() ? "uwp-form-select-ajax" : 'uwp-form-select';


				?>
                <div class="btn-group btn-group-sm mb-2" role="group" id="<?php echo $id; ?>">
				<?php
				$options = array_chunk( $options, 5, true );
				$current_url   = uwp_current_page_url();
				if ( isset( $options[0] ) && ! empty( $options[0] ) && count( $options[0] ) > 1 ) {
					foreach ( $options[0] as $id => $val ) {
						$active = $form_id == $id ? 'active' : '';
						$url = esc_url_raw( add_query_arg( array( 'uwp_form_id' => $id ), $current_url ) );
						echo aui()->button( array(
							'type'    => 'a',
							'href'    => $url,
							'class'   => 'btn btn-secondary '.$active,
							'content' => esc_attr( $val ),
							'extra_attributes'  => array('data-form_id'=>$id)
						) );
					}
				}elseif(count( $options[0] ) == 1){
					$form_id = key($options[0]);
				}

				if ( isset( $options[1] ) && ! empty( $options[1] ) && count( $options[1] ) > 0 ) {
					foreach ( $options[1] as $id => $val ) {
						$active = $form_id == $id ? 'active' : '';
						$url = esc_url_raw( add_query_arg( array( 'uwp_form_id' => $id ), $current_url ) );
						?>
                        <div class="btn-group" role="group">
                            <button id="uwp-form-select-dropdown" type="button" class="btn btn-secondary dropdown-toggle"
                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <?php _e('More', 'userswp'); ?>
                            </button>
                            <div class="dropdown-menu mt-3" aria-labelledby="uwp-form-select">
								<?php
								echo aui()->button( array(
									'type'    => 'a',
									'href'    => $url,
									'class'   => 'dropdown-item ' . $active,
									'content' => esc_attr( $val ),
									'extra_attributes'  => array('data-form_id'=>$id)
								) );
								?>
                            </div>
                        </div>
						<?php
					}
				}
				?>
                </div>
				<?php
			}
		} elseif ( $form_type == 'account' ) {
			$fields = get_account_form_fields();
		} elseif ( $form_type == 'change' ) {
			$fields = get_change_form_fields();
		} else {
			$fields = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $table_name . " WHERE form_type = %s AND is_active = '1' AND for_admin_use != '1' ORDER BY sort_order ASC", array( $form_type ) ) );
		}

		if ( ! empty( $fields ) ) {
			foreach ( $fields as $field ) {

				if ( $form_type == 'account' ) {
					if ( $field->htmlvar_name == 'display_name' ) {
						if ( $field->is_active != '1' ) {
							continue;
						}
					}
					if ( $field->htmlvar_name == 'bio' ) {
						if ( $field->is_active != '1' ) {
							continue;
						}
					}
				}

				if ( $form_type == 'register' ) {
					if ( $field->is_active != '1' ) {
						continue;
					}
					$count = $wpdb->get_var( $wpdb->prepare( "select count(*) from " . $extras_table_name . " where site_htmlvar_name=%s AND form_type = %s AND form_id=%d", array(
						$field->htmlvar_name,
						$form_type,
						$form_id
					) ) );
					if ( $count == 1 ) {
						$this->template_fields_html( $field, $form_type );
					}
				} else {
					$this->template_fields_html( $field, $form_type );
				}
			}
		}
	}

	/**
	 * Prints field html based on field type.
	 *
	 * @param object   $field     Field info.
	 * @param string   $form_type Form type.
	 * @param int|bool $user_id   User ID.
	 *
	 * @return      void
	 * @since       1.0.0
	 * @package     userswp
	 */
	public function template_fields_html( $field, $form_type, $user_id = false ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		$value = $this->get_default_form_value( $field );
		if ( $form_type == 'account' ) {
			$user_data = get_userdata( $user_id );

			if ( $field->htmlvar_name == 'email' ) {
				$value = $user_data->user_email;
			} elseif ( $field->htmlvar_name == 'password' ) {
				$value              = '';
				$field->is_required = 0;
			} elseif ( $field->htmlvar_name == 'confirm_password' ) {
				$value              = '';
				$field->is_required = 0;
			} else {
				$value = uwp_get_usermeta( $user_id, $field->htmlvar_name, false );
				if ( $value != '0' && ! $value ) {
					$value = $this->get_default_form_value( $field );
				}
			}

		}

		if ( ! isset( $value ) ) {
			$value = "";
		}

		if ( isset( $_POST[ $field->htmlvar_name ] ) && $field->field_type != 'password' ) {
			$value = isset( $_POST[ $field->htmlvar_name ] ) ? $_POST[ $field->htmlvar_name ] : ''; //@todo: Used to pre fill form when validation fails, need to find better solution
		}

		if ( 'checkbox' == $field->field_type ) {
			if ( in_array( $value, array( 'true', 'on', 1 ) ) ) {
				$value = 1;
			} else {
				$value = 0;
			}
		}

		$field = apply_filters( "uwp_form_input_field_{$field->field_type}", $field, $value, $form_type );

		$html = apply_filters( "uwp_form_input_html_{$field->field_type}", "", $field, $value, $form_type );

		if ( empty( $html ) ) {

			$design_style    = uwp_get_option( "design_style", "bootstrap" );
			$bs_form_group   = $design_style ? "form-group" : "";
			$bs_sr_only      = $design_style ? "sr-only" : "";
			$bs_form_control = $design_style ? "form-control" : "";

			?>
            <div id="<?php echo esc_attr( $field->htmlvar_name ); ?>_row"
                 class="<?php if ( $field->is_required ) {
				     echo 'required_field';
			     } ?> uwp_form_row clearfix uwp_clear <?php echo esc_attr( $bs_form_group ); ?>">
				<?php

				$label = $site_title = uwp_get_form_label( $field );
				if ( ! is_admin() ) { ?>
                    <label class="<?php echo esc_attr( $bs_sr_only ); ?>">
						<?php echo ( trim( $site_title ) ) ? $site_title : '&nbsp;'; ?>
						<?php if ( $field->is_required ) {
							echo '<span>*</span>';
						} ?>
                    </label>
				<?php } ?>

                <input name="<?php echo esc_attr($field->htmlvar_name); ?>"
                       class="<?php echo esc_attr($field->css_class); ?> <?php echo esc_attr( $bs_form_control ); ?>"
                       placeholder="<?php echo uwp_get_field_placeholder( $field ); ?>"
                       title="<?php echo esc_attr($label); ?>"
					<?php if ( $field->for_admin_use == 1 ) {
						echo 'readonly="readonly"';
					} ?>
					<?php if ( $field->is_required == 1 ) {
						echo 'required="required"';
					} ?>
                       type="<?php echo esc_attr($field->field_type); ?>"
                       value="<?php echo esc_html( $value ); ?>">

            </div>
			<?php
		} else {
			echo $html;
		}
	}

	/**
	 * Returns default value based on field type.
	 *
	 * @param object $field Field info.
	 *
	 * @return      string                  Field default value.
	 * @since       1.0.0
	 * @package     userswp
	 */
	public function get_default_form_value( $field ) {
		if ( $field->field_type == 'url' ) {
			if ( substr( $field->default_value, 0, 4 ) === "http" ) {
				$value = $field->default_value;
			} else {
				$value = "";
			}
		} else {
			$value = $field->default_value;
		}

		return $value;
	}

	/**
	 * Display redirect to input of that particular form.
	 *
	 * @param string $form_type Form type.
	 *
	 * @return      void
	 * @since       1.0.21
	 * @package     userswp
	 */
	public function template_extra_fields( $form_type, $args = array() ) {
		if ( $form_type == 'login' ) {
			$redirect_to = '';
			if ( isset( $args['redirect_to'] ) && ! empty( $args['redirect_to'] ) ) {
				$redirect_to = $args['redirect_to'];
			} else {
				if ( - 1 == uwp_get_option( 'login_redirect_to', - 1 ) ) {
					$referer = wp_get_referer();
					if ( isset( $_REQUEST['redirect_to'] ) && ! empty( $_REQUEST['redirect_to'] ) ) {
						$redirect_to = esc_url( urldecode( $_REQUEST['redirect_to'] ) );
					} else if ( isset( $referer ) && ! empty( $referer ) ) {
						$redirect_to = $referer;
					} else {
						$redirect_to = home_url();
					}
				}
			}

			if ( $redirect_to ) {
				echo '<input type="hidden" name="redirect_to" value="' . esc_url( $redirect_to ) . '"/>';
			}

			echo '<input type="hidden" name="uwp_login_nonce" value="' . wp_create_nonce( 'uwp-login-nonce' ) . '" />';
		} elseif ( $form_type == 'register' ) {
			$redirect_to = '';
			$form_id = ! empty( $args['id'] ) ? $args['id'] : 1;
			if ( isset( $args['redirect_to'] ) && ! empty( $args['redirect_to'] ) ) {
				$redirect_to = $args['redirect_to'];
			} else {
				$redirect_to_value = uwp_get_register_form_by( $form_id, 'redirect_to');
				if(!$redirect_to_value){
					$redirect_to_value = uwp_get_option( 'register_redirect_to', - 1 );
                }

				if ( - 1 ==  $redirect_to_value) {
					$referer = wp_get_referer();
					if ( isset( $_REQUEST['redirect_to'] ) && ! empty( $_REQUEST['redirect_to'] ) ) {
						$redirect_to = esc_url( urldecode( $_REQUEST['redirect_to'] ) );
					} else if ( isset( $referer ) && ! empty( $referer ) ) {
						$redirect_to = $referer;
					} else {
						$redirect_to = home_url();
					}
				}
			}

			if ( $redirect_to ) {
				echo '<input type="hidden" name="redirect_to" value="' . esc_url( $redirect_to ) . '"/>';
			}

			$hash = substr( hash( 'SHA256', AUTH_KEY . site_url() ), 0, 25 );
			echo '<input type="hidden" name="uwp_register_hash" value="' . $hash . '" style="display:none !important; visibility:hidden !important;" />';
			echo '<input type="hidden" name="uwp_register_hp" value="" style="display:none !important; visibility:hidden !important;" size="25" autocomplete="off" />';
			echo '<input type="hidden" name="uwp_register_nonce" value="' . wp_create_nonce( 'uwp-register-nonce' ) . '" />';
			echo '<input type="hidden" name="uwp_register_form_id" value="' . absint($form_id) . '">';
		} elseif ( $form_type == 'change' ) {
			echo '<input type="hidden" name="uwp_change_nonce" value="' . wp_create_nonce( 'uwp-change-nonce' ) . '" />';
		} elseif ( $form_type == 'forgot' ) {
			echo '<input type="hidden" name="uwp_forgot_nonce" value="' . wp_create_nonce( 'uwp-forgot-nonce' ) . '" />';
		} elseif ( $form_type == 'reset' ) {
			if ( isset( $_GET['key'] ) && isset( $_GET['login'] ) ) {
				echo '<input type="hidden" name="uwp_reset_username" value="' . esc_attr( $_GET['login'] ) . '" />';
				echo '<input type="hidden" name="uwp_reset_key" value="' . esc_attr( $_GET['key'] ) . '" />';
			}
			echo '<input type="hidden" name="uwp_reset_hp" value="" style="display:none !important; visibility:hidden !important;" size="25" autocomplete="off" />';
			echo '<input type="hidden" name="uwp_reset_nonce" value="' . wp_create_nonce( 'uwp-reset-nonce' ) . '" />';
		}
	}

	/**
	 * Modifies the author page content with UsersWP profile content.
	 *
	 * @param string $content Original page content.
	 *
	 * @return      string                  Modified page content.
	 * @since       1.0.0
	 * @package     userswp
	 */
	public function author_page_content( $content ) {
		if ( is_author() && 1 != uwp_get_option( 'uwp_disable_author_link' ) && apply_filters( 'uwp_use_author_page_content', true ) ) {
			return do_shortcode( '[uwp_profile]' );
		} else {
			return $content;
		}

	}

	/**
	 * Modifies the menu item visibility based on UsersWP page type.
	 *
	 * @param object $menu_item Menu item info.
	 *
	 * @return      object                      Modified menu item.
	 * @since       1.0.0
	 * @package     userswp
	 */
	public function setup_nav_menu_item( $menu_item ) {

		if ( is_admin() ) {
			return $menu_item;
		}

		// Prevent a notice error when using the customizer
		$menu_classes = $menu_item->classes;

		if ( is_array( $menu_classes ) ) {
			$menu_classes = implode( ' ', $menu_item->classes );
			$str          = 'users-wp-menu ';
			if ( strpos( $menu_classes, 'users-wp-menu ' ) !== false ) {
				$menu_classes = str_replace( $str, '', $menu_classes );
			}

			$menu_classes = explode( " ", $menu_classes );
		}

		$register_slug = uwp_get_page_slug( 'register_page' );
		$login_slug    = uwp_get_page_slug( 'login_page' );
		$change_slug   = uwp_get_page_slug( 'change_page' );
		$account_slug  = uwp_get_page_slug( 'account_page' );
		$profile_slug  = uwp_get_page_slug( 'profile_page' );
		$forgot_slug   = uwp_get_page_slug( 'forgot_page' );
		$logout_slug   = "logout";

		$register_class = "users-wp-{$register_slug}-nav";
		$login_class    = "users-wp-{$login_slug}-nav";
		$change_class   = "users-wp-{$change_slug}-nav";
		$account_class  = "users-wp-{$account_slug}-nav";
		$profile_class  = "users-wp-{$profile_slug}-nav";
		$forgot_class   = "users-wp-{$forgot_slug}-nav";
		$logout_class   = "users-wp-{$logout_slug}-nav";

		if ( ! empty( $menu_classes ) ) {
			foreach ( $menu_classes as $menu_class ) {
				switch ( $menu_class ) {
					case $register_class:
						if ( is_user_logged_in() ) {
							$menu_item->_invalid = true;
						} else {
							$menu_item->url = uwp_get_page_id( 'register_page', true );
						}
						break;
					case $login_class:
						if ( is_user_logged_in() ) {
							$menu_item->_invalid = true;
						} else {
							$menu_item->url = uwp_get_page_id( 'login_page', true );
						}
						break;
					case $account_class:
						if ( ! is_user_logged_in() ) {
							$menu_item->_invalid = true;
						} else {
							$menu_item->url = uwp_get_page_id( 'account_page', true );
						}
						break;
					case $profile_class:
						if ( ! is_user_logged_in() ) {
							$menu_item->_invalid = true;
						} else {
							$menu_item->url = uwp_get_page_id( 'profile_page', true );
						}
						break;
					case $change_class:
						if ( ! is_user_logged_in() ) {
							$menu_item->_invalid = true;
						} else {
							$menu_item->url = uwp_get_page_id( 'change_page', true );
						}
						break;
					case $forgot_class:
						if ( is_user_logged_in() ) {
							$menu_item->_invalid = true;
						} else {
							$menu_item->url = uwp_get_page_id( 'forgot_page', true );
						}
						break;
					case $logout_class:
						if ( ! is_user_logged_in() ) {
							$menu_item->_invalid = true;
						} else {
							$menu_item->url = $this->uwp_logout_url();
						}
						break;
				}
			}
		}

		$menu_item = apply_filters( 'uwp_setup_nav_menu_item', $menu_item, $menu_classes );

		return $menu_item;

	}

	/**
	 * Returns the logout url by adding redirect page link.
	 *
	 * @param null $custom_redirect Redirect page link.
	 *
	 * @return      string                         Logout url.
	 * @since       1.0.0
	 * @package     userswp
	 */
	public function uwp_logout_url( $custom_redirect = null ) {

		$redirect = null;

		$user = get_userdata(get_current_user_id());
		if($user && isset($user->roles[0])){
			$user_role = $user->roles[0];
			$redirect_page_id = uwp_get_option( 'logout_redirect_to_'.$user_role );
		}

		if ( ! empty( $custom_redirect ) ) {
			$redirect = esc_url( $custom_redirect );
		} else if ( isset($redirect_page_id) && !empty($redirect_page_id) ) {
			if ( uwp_is_wpml() ) {
				$wpml_page_id = uwp_wpml_object_id( $redirect_page_id, 'page', true, ICL_LANGUAGE_CODE );
				if ( ! empty( $wpml_page_id ) ) {
					$redirect_page_id = $wpml_page_id;
				}
			}
			$redirect = get_permalink( $redirect_page_id );
		}

		return wp_logout_url( apply_filters( 'uwp_logout_url', $redirect, $custom_redirect ) );

	}

	/**
	 * Adds the UsersWP body class to body tag.
	 *
	 * @param array $classes Existing class array.
	 *
	 * @return      array                    Modified class array.
	 * @since       1.0.0
	 * @package     userswp
	 */
	public function add_body_class( $classes ) {

		if ( is_uwp_page() ) {
			$classes[] = 'uwp_page';

			if ( is_uwp_page( 'register_page' ) ) {
				$classes[] = 'uwp_register_page';
			} elseif ( is_uwp_page( 'login_page' ) ) {
				$classes[] = 'uwp_login_page';
			} elseif ( is_uwp_page( 'forgot_page' ) ) {
				$classes[] = 'uwp_forgot_page';
			} elseif ( is_uwp_page( 'change_page' ) ) {
				$classes[] = 'uwp_change_page';
			} elseif ( is_uwp_page( 'reset_page' ) ) {
				$classes[] = 'uwp_reset_page';
			} elseif ( is_uwp_page( 'account_page' ) ) {
				$classes[] = 'uwp_account_page';
			} elseif ( is_uwp_page( 'profile_page' ) ) {
				$classes[] = 'uwp_profile_page';
			} elseif ( is_uwp_page( 'users_page' ) ) {
				$classes[] = 'uwp_users_page';
			}
		}

		return $classes;
	}

	/**
	 *
	 * Returns content for author box
	 *
	 * @param $content
	 *
	 * @return string
	 */
	public function author_box_page_content( $content ) {

		global $post;

		if ( is_single() ) {

			$author_box_enable_disable = uwp_get_option( 'author_box_enable_disable', 1 );

			if ( 1 == $author_box_enable_disable ) {

				$author_box_display_post_types = uwp_get_option( 'author_box_display_post_types' );

				if ( ! empty( $post->post_type ) && in_array( $post->post_type, (array) $author_box_display_post_types ) ) {

					$author_box_display_content = uwp_get_option( 'author_box_display_content' );

					if ( ! empty( $author_box_display_content ) && 'above_content' === $author_box_display_content ) {
						$content = do_shortcode( '[uwp_author_box]' ) . $content;
					} else {
						$content = $content . do_shortcode( '[uwp_author_box]' );
					}

					// run block content if its available
					if ( function_exists( 'do_blocks' ) ) {
						$content = do_blocks( $content );
					}
				}

			}

		}

		return $content;
	}

	/**
	 * Adds form html for privacy fields in account page.
	 *
	 * @param string $type Form type.
	 *
	 * @return      void
	 * @since       1.0.0
	 * @package     userswp
	 *
	 */
	public function privacy_edit_form_display( $type ) {
		if ( $type == 'privacy' ) {
			$make_profile_private = uwp_can_make_profile_private();
			echo '<div class="uwp-account-form">';
			$extra_where     = "AND is_public='2'";
			$fields          = get_account_form_fields( $extra_where );
			$fields          = apply_filters( 'uwp_account_privacy_fields', $fields );
			$user_id         = get_current_user_id();
			$design_style    = uwp_get_option( "design_style", "bootstrap" );
			$bs_form_group   = $design_style ? "form-group row" : "";
			$bs_form_control = $design_style ? "form-control" : "";
			$bs_btn_class    = $design_style ? "btn btn-primary btn-block text-uppercase" : "";
			?>
            <div class="uwp-profile-extra">
                <div class="uwp-profile-extra-div form-table">
                    <form class="uwp-account-form uwp_form" method="post">
						<?php if ( $fields ) { ?>
                            <div class="uwp-profile-extra-wrap <?php echo $bs_form_group; ?>">
                                <div class="uwp-profile-extra-key col" style="font-weight: bold;">
									<?php _e( "Field", "userswp" ) ?>
                                </div>
                                <div class="uwp-profile-extra-value col" style="font-weight: bold;">
									<?php _e( "Is Public?", "userswp" ) ?>
                                </div>
                            </div>
							<?php foreach ( $fields as $field ) { ?>
                                <div class="uwp-profile-extra-wrap <?php echo $bs_form_group; ?>">
                                    <div class="uwp-profile-extra-key col"><?php echo $field->site_title; ?>
                                        <span class="uwp-profile-extra-sep">:</span></div>
                                    <div class="uwp-profile-extra-value col">
										<?php
										$field_name = $field->htmlvar_name . '_privacy';
										$value      = uwp_get_usermeta( $user_id, $field_name, false );
										if ( $value === false ) {
											$value = 'yes';
										}
										?>
                                        <select name="<?php echo $field_name; ?>"
                                                class="uwp_privacy_field aui-select2 <?php echo $bs_form_control; ?>"
                                                style="margin: 0;">
                                            <option value="no" <?php selected( $value, "no" ); ?>><?php echo __( "No", "userswp" ) ?></option>
                                            <option value="yes" <?php selected( $value, "yes" ); ?>><?php echo __( "Yes", "userswp" ) ?></option>
                                        </select>
                                    </div>
                                </div>
							<?php }
						}

						global $wpdb;
						$tabs_table_name = uwp_get_table_prefix() . 'uwp_profile_tabs';
						$tabs            = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $tabs_table_name . " WHERE form_type=%s AND user_decided = 1 ORDER BY sort_order ASC", 'profile-tabs' ) );

						if ( $tabs ) { ?>
                            <div class="uwp-profile-extra-wrap <?php echo $bs_form_group; ?>">
                                <div class="uwp-profile-extra-key col" style="font-weight: bold;">
									<?php _e( "Tab Name", "userswp" ) ?>
                                </div>
                                <div class="uwp-profile-extra-value col" style="font-weight: bold;">
									<?php _e( "Privacy", "userswp" ) ?>
                                </div>
                            </div>
						<?php }

						foreach ( $tabs as $tab ) { ?>
                            <div class="uwp-profile-extra-wrap <?php echo $bs_form_group; ?>">
                                <div class="uwp-profile-extra-key col"><?php _e( $tab->tab_name, 'userswp' ); ?>
                                    <span class="uwp-profile-extra-sep">:</span></div>
                                <div class="uwp-profile-extra-value col">
									<?php
									$field_name = $tab->tab_key . '_tab_privacy';
									$value      = uwp_get_usermeta( $user_id, $field_name, '' );

									$privacy_options = array(
										0 => __( "Anyone", "userswp" ),
										1 => __( "Logged in", "userswp" ),
										2 => __( "Author only", "userswp" ),
									);

									// Admin default
									$admin_privacy   = isset( $tab->tab_privacy ) ? absint( $tab->tab_privacy ) : 0;
									$privacy_options = apply_filters( 'uwp_tab_privacy_options', $privacy_options, $tab );

									if ( empty( $value ) ) {
										$value = $admin_privacy;
									}
									?>
                                    <select name="<?php echo $field_name; ?>"
                                            class="uwp_tab_privacy_field aui-select2 <?php echo $bs_form_control; ?>"
                                            style="margin: 0;">
										<?php
										foreach ( $privacy_options as $key => $val ) {

											$default = '';
											if ( $admin_privacy == $key ) {
												$default = __( ' (Default)', 'userswp' );
											}
											echo '<option value="' . $key . '"' . selected( $value, $key, false ) . '>' . $val . $default . '</option>';
										}
										?>
                                    </select>
                                </div>
                            </div>
						<?php }

						$value = get_user_meta( $user_id, 'uwp_hide_from_listing', true ); ?>
                        <div class="uwp-profile-extra-wrap">
                            <div id="uwp_hide_from_listing" class="uwp_hide_from_listing">
                                <input name="uwp_hide_from_listing" class="" <?php checked( $value, "1", true ); ?>
                                       type="checkbox"
                                       value="1"><?php _e( 'Hide profile from the users listing page.', 'userswp' ); ?>
                            </div>
                        </div>
						<?php
						do_action( 'uwp_after_privacy_form_fields', $fields );
						if ( $make_profile_private ) {
							$field_name = 'uwp_make_profile_private';
							$value      = get_user_meta( $user_id, $field_name, true );
							if ( $value === false ) {
								$value = '0';
							}
							?>
                            <div id="uwp_make_profile_private" class=" uwp_make_profile_private_row">
                                <input type="hidden" name="uwp_make_profile_private" value="0">
                                <input name="uwp_make_profile_private" class="" <?php checked( $value, "1", true ); ?>
                                       type="checkbox" value="1">
								<?php _e( 'Make the whole profile private', 'userswp' ); ?>
                            </div>
							<?php
						}
						?>
                        <input type="hidden" name="uwp_privacy_nonce"
                               value="<?php echo wp_create_nonce( 'uwp-privacy-nonce' ); ?>"/>
                        <input name="uwp_privacy_submit" class="<?php echo $bs_btn_class; ?>"
                               value="<?php echo __( 'Submit', 'userswp' ); ?>" type="submit">
                    </form>
                </div>
            </div>
			<?php
			echo '</div>';
		}
	}

	/**
	 * Redirects the user to login page when email not confirmed.
	 *
	 * @param string $username Username.
	 * @param object $user     User object.
	 *
	 * @return      void
	 * @package     userswp
	 *
	 * @since       1.0.0
	 */
	public function unconfirmed_login_redirect( $username, $user ) {
		if ( ! is_wp_error( $user ) ) {
			$mod_value = get_user_meta( $user->ID, 'uwp_mod', true );
			if ( $mod_value == 'email_unconfirmed' ) {
				if ( ! in_array( 'administrator', $user->roles ) ) {
					$login_page = uwp_get_page_id( 'login_page', false );
					if ( $login_page ) {
						$redirect_to = add_query_arg( array(
							'uwp_err' => 'act_pending',
							'user_id' => $user->ID
						), get_permalink( $login_page ) );
						wp_destroy_current_session();
						wp_clear_auth_cookie();
						if ( wp_doing_ajax() ) {
							global $userswp;
							$message = $userswp->notices->form_notice_by_key( 'act_pending', false, $user->ID );
							wp_send_json_error( $message );
						} else {
							wp_redirect( $redirect_to );
						}
						exit();
					}
				}
			}
		}
	}

	/**
	 * Oxygen override theme template.
	 *
	 * @since 1.2.2.15
	 *
	 * @param string $located Located template.
	 * @param string $template_name Template name.
	 * @param array $located Template args.
	 * @param string $template_path Template path.
	 * @param string $default_path Template default path.
	 * @return string Located template.
	 */
	public function oxygen_override_template( $located, $template_name, $args, $template_path, $default_path ) {
		if ( $_located = $this->oxygen_locate_template( $template_name ) ) {
			$located = $_located;
		}

		return $located;
	}

	/**
	 * Oxygen locate theme template.
	 *
	 * @since 1.2.2.15
	 *
	 * @param string $template The template.
	 * @return string The theme template.
	 */
	public function oxygen_locate_template( $template ) {
		$located = '';

		if ( ! $template ) {
			return $located;
		}

		$has_filter = has_filter( 'template', 'ct_oxygen_template_name' );

		// Remove template filter
		if ( $has_filter ) {
			remove_filter( 'template', 'ct_oxygen_template_name' );
		}

		$_located = $this->get_theme_template_path() . '/' . $template;

		if ( file_exists( $_located ) ) {
			$located = $_located;
		}

		// Add template filter
		if ( $has_filter ) {
			add_filter( 'template', 'ct_oxygen_template_name' );
		}

		return $located;
	}

	/**
	 * Get the UsersWP templates theme path.
	 *
	 * @since 1.2.2.15
	 *
	 * @return string Template path.
	 */
	public static function get_theme_template_path() {
		$template = get_template();
		$theme_root = get_theme_root( $template );

		$theme_template_path = $theme_root . '/' . $template . '/' . untrailingslashit( uwp_get_theme_template_dir_name() );

		return $theme_template_path;
	}
}