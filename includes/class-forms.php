<?php
/**
 * Form related functions
 *
 * This class defines all code necessary to handle UsersWP forms like login. register etc.
 *
 * @since      1.0.0
 * @author     GeoDirectory Team <info@wpgeodirectory.com>
 */
class UsersWP_Forms {

	protected $generated_password;

	/**
	 * Initialize UsersWP notices.
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 * @return      void
	 */
	public function init_notices() {
		global $uwp_notices;
		$uwp_notices = array();
	}

	/**
	 * Handles all UsersWP forms.
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 * @return      void
	 */
	public function handler()
	{
		global $uwp_notices;

		ob_start();

		$errors = null;
		$message = null;
		$redirect = false;
		$processed = false;
		$type = null;

		if (isset($_POST['uwp_avatar_submit'])) {
			$errors = $this->process_upload_submit($_POST, $_FILES, 'avatar');
			if (!is_wp_error($errors)) {
				$redirect = $errors;
			}
			$message = __('Avatar cropped successfully.', 'userswp');
			$processed = true;
		} elseif (isset($_POST['uwp_banner_submit'])) {
			$errors = $this->process_upload_submit($_POST, $_FILES, 'banner');
			if (!is_wp_error($errors)) {
				$redirect = $errors;
			}
			$message = __('Banner cropped successfully.', 'userswp');
			$processed = true;
		} elseif (isset($_POST['uwp_avatar_crop'])) {
			$errors = $this->process_image_crop($_POST, 'avatar', true);
			if (!is_wp_error($errors)) {
				$redirect = $errors;
			}
			$message = __('Avatar cropped successfully.', 'userswp');
			$processed = true;
		} elseif (isset($_POST['uwp_banner_crop'])) {
			$errors = $this->process_image_crop($_POST, 'banner', true);
			if (!is_wp_error($errors)) {
				$redirect = $errors;
			}
			$message = __('Banner cropped successfully.', 'userswp');
			$processed = true;
		} elseif (isset($_POST['uwp_avatar_reset'])) {
			$errors = $this->process_image_reset('avatar');
			if (!is_wp_error($errors)) {
				$redirect = $errors;
			}
			$message = __('Avatar reset successfully.', 'userswp');
			$processed = true;
		} elseif (isset($_POST['uwp_banner_reset'])) {
			$errors = $this->process_image_reset('banner');
			if (!is_wp_error($errors)) {
				$redirect = $errors;
			}
			$message = __('Banner reset successfully.', 'userswp');
			$processed = true;
		}

		if ($processed) {
			if (is_wp_error($errors)) {
				echo aui()->alert(array(
					'type'=>'error',
					'class'=>'text-center',
					'content'=> $errors->get_error_message()
				));
			} else {
				if ($redirect) {
					wp_safe_redirect($redirect);
					exit();
				} else {
					echo aui()->alert(array(
						'type'=>'success',
						'class'=>'text-center',
						'content'=> $message
					));
				}
			}
		}

		if($type){
			$uwp_notices[] = array($type => ob_get_contents());
		}else{
			$uwp_notices[] = ob_get_contents();
		}

		ob_end_clean();

	}

	/**
	 * Displays links in a dropdown
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 * @param $options
	 */
	public function output_dashboard_links($options){
		if(!empty($options)){
			$class = uwp_get_option("design_style",'bootstrap')=='bootstrap' ? 'form-control' : 'aui-select2';
			echo "<select class='$class' onchange='window.location = jQuery(this).val();'>";
			$this->output_options($options);
			echo "<select>";
		}
	}

	/**
	 * Displays options for the dashboard links
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 * @param $options
	 */
	public function output_options($options){
		if(!empty($options)){
			foreach($options as $key => $link){

				if(!isset($link['text']) && isset($link[0]) && is_array($link[0])){
					$this->output_options($link);
				}else{
					if(!empty($link['optgroup']) && $link['optgroup']=='open'){
						echo "<optgroup label='".esc_attr($link['text'])."'>";
					}elseif(!empty($link['optgroup']) && $link['optgroup']=='close'){
						echo "</optgroup>";
					}elseif(!empty($link['text'])){
						$disabled  = !empty($link['disabled']) ? 'disabled' : '';
						$selected  = !empty($link['selected']) ? 'selected' : '';
						$value  = !empty($link['url']) ? esc_url($link['url']) : '';
						$display_none = !empty($link['display_none']) ? 'style="display:none;"' : '';
						echo "<option $disabled $selected value='$value' $display_none>";
						echo esc_attr__($link['text'],'userswp');
						echo "</option>";
					}
				}
			}
		}
	}

	/**
	 * Displays UsersWP notices in forms.
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 * @param       string      $type       Form type
	 *
	 * @return      void
	 */
	public function display_notices($type) {
		global $uwp_notices;

		if (is_array($uwp_notices)) {
			foreach ($uwp_notices as $notice) {

				// If the notification is type specific then only output on that type
				if(is_array($notice)){
					foreach($notice as $key => $val){
						if( $key == $type ){ echo $val; }
					}
				}else{
					if (!empty($notice)) {
						echo $notice;
					}
				}

			}

		}

		if ($type == 'change') {
			$user_id = get_current_user_id();
			$password_nag = get_user_option('default_password_nag', $user_id);

			if ($password_nag) {
				$change_page = uwp_get_page_id('change_page', false);
				$remove_nag_url = add_query_arg('uwp_remove_nag', 'yes', get_permalink($change_page));

				if (isset($_GET['uwp_remove_nag']) && $_GET['uwp_remove_nag'] == 'yes') {
					delete_user_meta( $user_id, 'default_password_nag' );
					$message = sprintf(__('We have removed the system generated password warning for you. From this point forward you can continue to access our site as usual. To go to home page, <a href="%s">click here</a>.', 'userswp'), home_url('/'));
					echo aui()->alert(array(
						'class'=>'text-center',
						'type'=>'success',
						'content'=> $message
					));
				} else {
					$message = sprintf(__('<strong>Warning</strong>: It seems like you are using a system generated password. Please change the password in this page. If this is not a problem for you, you can remove this warning by <a href="%s">clicking here</a>.', 'userswp'), $remove_nag_url);
					echo aui()->alert(array(
						'class'=>'text-center',
						'type'=>'warning',
						'content'=> $message
					));
				}
			}
		}
	}

	/**
	 * Processes register form submission.
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 */
	public function process_register() {

		$data = $_POST;

		if( ! isset( $data['uwp_register_nonce'] )){
			return;
		}

		global $uwp_notices;

		if( ! isset( $data['uwp_register_nonce'] ) || ! wp_verify_nonce( $data['uwp_register_nonce'], 'uwp-register-nonce' ) ) {
			$message = aui()->alert(array(
					'type'=>'error',
					'content'=> __('Security verification failed. Try again.', 'userswp')
				)
			);
			if(wp_doing_ajax()){wp_send_json_error($message);}
			else{$uwp_notices[] = array('register' => $message); return;}
		}

		$files = $_FILES;
		$errors = new WP_Error();
		$file_obj = new UsersWP_Files();

		if (!get_option('users_can_register')) {
			$message = aui()->alert(array(
					'type'=>'error',
					'content'=> __('User registration is currently not allowed. Please check settings of your site.', 'userswp')
				)
			);
			if(wp_doing_ajax()){wp_send_json_error($message);}
			else{$uwp_notices[] = array('register' => $message); return;}
		}

		do_action('uwp_before_validate', 'register');

		$result = uwp_validate_fields($data, 'register');

		$result = apply_filters('uwp_validate_result', $result, 'register', $data);

		if (is_wp_error($result)) {
			$message = aui()->alert(array(
					'type'=>'error',
					'content'=> $result->get_error_message()
				)
			);
			if(wp_doing_ajax()){wp_send_json_error($message);}
			else{$uwp_notices[] = array('register' => $message); return;}
		}

		$uploads_result = $file_obj->validate_uploads($files, 'register');

		if (is_wp_error($uploads_result)) {
			$message = aui()->alert(array(
					'type'=>'error',
					'content'=> $uploads_result->get_error_message()
				)
			);
			if(wp_doing_ajax()){wp_send_json_error($message);}
			else{$uwp_notices[] = array('register' => $message); return;}
		}

		do_action('uwp_after_validate', 'register');

		$result = array_merge( $result, $uploads_result );

		if (isset($result['password']) && !empty($result['password'])) {
			$password = $result['password'];
			$generated_password = false;
		} else {
			$password = wp_generate_password();
			$this->generated_password = $password;
			$generated_password = true;
		}

		$first_name = "";
		if (isset($result['first_name']) && !empty($result['first_name'])) {
			$first_name = $result['first_name'];
		}

		$last_name = "";
		if (isset($result['last_name']) && !empty($result['last_name'])) {
			$last_name = $result['last_name'];
		}

		if (isset($result['display_name']) && !empty($result['display_name'])) {
			$display_name = $result['display_name'];
		} else {
			if (!empty($first_name) || !empty($last_name)) {
				$display_name = $first_name . ' ' . $last_name;
			} else {
				$display_name = !empty($result['username']) ? $result['username'] : '';
			}
		}

		$user_url = "";
		if (isset($result['user_url']) && !empty($result['user_url'])) {
			$user_url = esc_url_raw($result['user_url']);
		}

		$user_login = !empty($result['username']) ? $result['username'] : '';
		$email = !empty($result['email']) ? sanitize_email($result['email']) : '';

		if(empty($user_login)){
			$user_login = sanitize_user( str_replace( ' ', '', $display_name ), true );
			if ( !( validate_username( $user_login ) && !username_exists( $user_login ) ) ) {
				$new_user_login = strstr($email, '@', true);
				if ( validate_username( $user_login ) && username_exists( $user_login ) ) {
					$user_login = sanitize_user($new_user_login, true );
				}
				if ( validate_username( $user_login ) && username_exists( $user_login ) ) {
					$user_append_text = rand(10,1000);
					$user_login = sanitize_user($new_user_login.$user_append_text, true );
				}

				if ( !( validate_username( $user_login ) && !username_exists( $user_login ) ) ) {
					$user_login = $email;
				}
			}
		}else{
			if(!validate_username( $user_login )){
				$message = aui()->alert(array(
						'type'=>'error',
						'content'=> __('Sorry, that username is not allowed.', 'userswp')
					)
				);
				if(wp_doing_ajax()){wp_send_json_error($message);}
				else{$uwp_notices[] = array('register' => $message); return;}
			}
		}

		$args = array(
			'user_login'   => $user_login,
			'user_email'   => $email,
			'user_pass'    => $password,
			'display_name' => $display_name,
			'first_name'   => $first_name,
			'last_name'    => $last_name,
			'user_url'     => $user_url,
		);

		$user_id = wp_insert_user( $args );

		if (is_wp_error($user_id)) {
			$message = aui()->alert(array(
					'type'=>'error',
					'content'=> $user_id->get_error_message()
				)
			);
			if(wp_doing_ajax()){wp_send_json_error($message);}
			else{$uwp_notices[] = array('register' => $message); return;}
		}

		$result = apply_filters('uwp_before_extra_fields_save', $result, 'register', $user_id);

		$save_result = $this->save_user_extra_fields($user_id, $result, 'register');

		$save_result = apply_filters('uwp_after_extra_fields_save', $save_result, $result, 'register', $user_id);

		if (is_wp_error($save_result)) {
			$message = aui()->alert(array(
					'type'=>'error',
					'content'=> $save_result->get_error_message()
				)
			);
			if(wp_doing_ajax()){wp_send_json_error($message);}
			else{$uwp_notices[] = array('register' => $message); return;}
		}

		if (!$save_result) {
			$message = aui()->alert(array(
					'type'=>'error',
					'content'=> __('Something went wrong. Please contact site admin.', 'userswp')
				)
			);
			if(wp_doing_ajax()){wp_send_json_error($message);}
			else{$uwp_notices[] = array('register' => $message); return;}
		}

		//updating bio field after saving extra fields to reflect the points in mycred add on.
		if (isset($result['bio']) && !empty($result['bio'])) {
			$args = array(
				'ID'  => $user_id,
				'description'  => $result['bio'],
			);
			wp_update_user( $args );
		}

		do_action('uwp_after_custom_fields_save', 'register', $data, $result, $user_id);

		$reg_action = uwp_get_option('uwp_registration_action', false);

		$form_fields = apply_filters('uwp_send_mail_form_fields', "", 'register', $user_id);

		if ($reg_action == 'require_email_activation' && !$generated_password) {

			$user_data = get_userdata($user_id);
			$activation_link = uwp_get_activation_link($user_id);

			$message = __('To activate your account, visit the following address:', 'userswp') . "\r\n\r\n";

			$message .= "<a href='".esc_url($activation_link)."' target='_blank'>".esc_url($activation_link)."</a>" . "\r\n";

			$activate_message = '<p><b>' . __('Please activate your account :', 'userswp') . '</b></p><p>' . $message . '</p>';

			$activate_message = apply_filters('uwp_activation_mail_message', $activate_message, $user_id);

			$email_vars = array(
				'user_id' => $user_id,
				'login_details' => $activate_message,
				'activation_link' => $activation_link,
			);

			UsersWP_Mails::send($user_data->user_email, 'registration_activate', $email_vars);

		} else {

			$user_data = get_userdata($user_id);

			if(isset($this->generated_password) && !empty($this->generated_password)) {
				if(!uwp_get_option('change_disable_password_nag')) {
					update_user_meta($user_id, 'default_password_nag', true); //Set up the Password change nag.
				}
				$message_pass = $this->generated_password;
				$this->generated_password = false;
			} else {
				$message_pass = __("Password you entered during registration.", 'userswp');
			}

			$message = '<p><b>' . __('Your login Information :', 'userswp') . '</b></p>
            <p>' . __('Username:', 'userswp') . ' ' . $user_data->user_login . '</p>
            <p>' . __('Password:', 'userswp') . ' ' . $message_pass . '</p>';

			$message = apply_filters('uwp_register_mail_message', $message, $user_id, $this->generated_password);

			$email_vars = array(
				'user_id' => $user_id,
				'login_details' => $message,
				'form_fields' => $form_fields,
			);

			UsersWP_Mails::send($user_data->user_email, 'registration_success', $email_vars);
		}

		$error_code = $errors->get_error_code();
		if (!empty($error_code)) {
			$message = aui()->alert(array(
					'type'=>'error',
					'content'=> $result->get_error_message()
				)
			);
			if(wp_doing_ajax()){wp_send_json_error($message);}
			else{$uwp_notices[] = array('register' => $message); return;}
		}

		if ($reg_action != 'require_admin_review') {

			$user_data = get_userdata($user_id);
			$extras = '<p><b>' .__('User Information :', 'userswp') . '</b></p>
            <p>' . __('First Name:', 'userswp') . ' ' . $user_data->first_name . '</p>
            <p>' . __('Last Name:', 'userswp') . ' ' . $user_data->last_name . '</p>
            <p>' . __('Username:', 'userswp') . ' ' . $user_data->user_login . '</p>
            <p>' . __('Email:', 'userswp') . ' ' . $user_data->user_email . '</p>';

			$extras =  apply_filters('uwp_admin_mail_extras', $extras, 'register_admin', $user_id );

			$email_vars = array(
				'user_id' => $user_id,
				'extras' => $extras,
				'form_fields' => $form_fields,
			);

			UsersWP_Mails::send(get_option( 'admin_email' ), 'registration_success', $email_vars, true);

		}

		if ($reg_action == 'force_redirect') {
			$redirect_to = $this->get_register_redirect_url($data, $user_id);
			do_action('uwp_after_process_register', $data);
			if(wp_doing_ajax()){
				$message = aui()->alert(array(
						'type'=>'success',
						'content'=> __('Account registered successfully. Redirecting...', 'userswp')
					)
				);
				$response = array(
					'message' => $message,
					'redirect'  => $redirect_to,
				);
				wp_send_json_success($response);
			}else{
				wp_redirect($redirect_to);
			}
			exit();
		} elseif ($reg_action == 'auto_approve_login') {
			$res = wp_signon(
				array(
					'user_login' => $user_login,
					'user_password' => $password,
					'remember' => false
				)
			);

			if (is_wp_error($res)) {
				$error = aui()->alert(array(
						'type'=>'error',
						'content'=> __( 'Invalid Username or Password.', 'userswp' )
					)
				);
				$uwp_notices[] = array('register' => $error);
			} else {
				$redirect_to = $this->get_register_redirect_url($data, $user_id);
				do_action('uwp_after_process_register', $data);

				if(wp_doing_ajax()){
					$message = aui()->alert(array(
							'type'=>'success',
							'content'=> __('Account registered successfully. Redirecting...', 'userswp')
						)
					);
					$response = array(
						'message' => $message,
						'redirect'  => $redirect_to,
					);
					wp_send_json_success($response);
				}else{
					wp_redirect($redirect_to);
				}
				exit();
			}
		} else {
			if ($reg_action == 'require_email_activation') {
				$resend_link = uwp_current_page_url();
				$resend_link = add_query_arg(
					array(
						'user_id' => $user_id,
						'action'  => 'uwp_resend',
						'_nonce'  => wp_create_nonce('uwp_resend'),
					),
					$resend_link
				);
				$message = aui()->alert(array(
						'type'=>'success',
						'content'=> sprintf(__('An email has been sent to your registered email address. Please click the activation link to proceed. %sResend%s.', 'userswp'), '<a href="'.$resend_link.'"">', '</a>')
					)
				);

			} elseif ($reg_action == 'require_admin_review' && defined('UWP_MOD_VERSION')) {

				update_user_meta( $user_id, 'uwp_mod', '1' );

				do_action('uwp_require_admin_review', $user_id, $data);

				$message = aui()->alert(array(
						'type'=>'success',
						'content'=> __('Your account is under moderation. We will email you once its approved.', 'userswp')
					)
				);
			} else {

				$login_page_url = wp_login_url();

				if ($generated_password) {
					$msg = sprintf(__('Account registered successfully. A password has been generated and mailed to your registered Email ID. Please login %shere%s.', 'userswp'), '<a href="'.$login_page_url.'">', '</a>');
				} else {
					$msg = sprintf(__('Account registered successfully. Please login %shere%s', 'userswp'), '<a href="'.$login_page_url.'">', '</a>');
				}

				$message = aui()->alert(array(
						'type'=>'success',
						'content'=> $msg
					)
				);
			}

			do_action('uwp_after_process_register', $data, $user_id);

			if(wp_doing_ajax()){wp_send_json_success($message);}
			else{$uwp_notices[] = array('register' => $message);}

		}

		if(wp_doing_ajax()){wp_send_json_error();} // if we got this far there is a problem

	}

	/**
	 * Processes login form submission.
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 */
	public function process_login() {

		$data = $_POST;

		if( ! isset( $data['uwp_login_nonce'] )){
			return;
		}

		if( ! isset( $data['uwp_login_nonce'] ) || ! wp_verify_nonce( $data['uwp_login_nonce'], 'uwp-login-nonce' ) ) {
			$message = aui()->alert(array(
					'type'=>'error',
					'content'=> __('Security verification failed. Try again.', 'userswp')
				)
			);
			if(wp_doing_ajax()){wp_send_json_error($message);}
			else{return;}
		}

		global $uwp_notices;

		do_action('uwp_before_validate', 'login');

		$result = uwp_validate_fields($data, 'login');

		$result = apply_filters('uwp_validate_result', $result, 'login', $data);

		if (is_wp_error($result)) {
			$message = aui()->alert(array(
					'type'=>'error',
					'content'=> $result->get_error_message()
				)
			);
			if(wp_doing_ajax()){wp_send_json_error($message);}
			else{$uwp_notices[] = array('login' => $message); return;}
		}

		do_action('uwp_after_validate', 'login');

		if (isset($data['remember_me']) && $data['remember_me'] == 'forever') {
			$remember_me = true;
		} else {
			$remember_me = false;
		}

		remove_action( 'authenticate', 'gglcptch_login_check', 21 );

		$user = wp_signon(
			array(
				'user_login' => $result['username'],
				'user_password' => $result['password'],
				'remember' => $remember_me
			)
		);

		add_action( 'authenticate', 'gglcptch_login_check', 21, 1 );

		if (is_wp_error($user)) {
			$message = aui()->alert(array(
					'type'=>'error',
					'content'=> __( 'Invalid Username or Password.', 'userswp' )
				)
			);
			if(wp_doing_ajax()){wp_send_json_error($message);}
			else{$uwp_notices[] = array('login' => $message); return;}
		} else {
			do_action('uwp_after_process_login', $data);
			$message = aui()->alert(array(
					'type'=>'success',
					'content'=> __('Login successful. Redirecting...','userswp')
				)
			);
			if(wp_doing_ajax()){wp_send_json_success($message);}
			else{
				$redirect_to = $this->get_login_redirect_url($data, $user);
				wp_redirect($redirect_to);
				exit();
			}

		}

	}

	/**
	 * Processes forgot password form submission.
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 */
	public function process_forgot() {

		$data = $_POST;

		if( ! isset( $data['uwp_forgot_nonce'] )){
			return;
		}

		if( ! isset( $data['uwp_forgot_nonce'] ) || ! wp_verify_nonce( $data['uwp_forgot_nonce'], 'uwp-forgot-nonce' ) ) {
			$message = aui()->alert(array(
					'type'=>'error',
					'content'=> __('Security verification failed. Try again.', 'userswp')
				)
			);
			if(wp_doing_ajax()){wp_send_json_error($message);}
			else{return;}
		}

		global $uwp_notices;

		do_action('uwp_before_validate', 'forgot');

		$result = uwp_validate_fields($data, 'forgot');

		$result = apply_filters('uwp_validate_result', $result, 'forgot', $data);

		if (is_wp_error($result)) {
			$message = aui()->alert(array(
					'type'=>'error',
					'content'=> $result->get_error_message()
				)
			);
			if(wp_doing_ajax()){wp_send_json_error($message);}
			else{$uwp_notices[] = array('forgot' => $message); return;}
		}

		do_action('uwp_after_validate', 'forgot');


		$user_data = get_user_by('email', $data['email']);

		// if no user we fake it and bail
		if(!$user_data){
			$message = aui()->alert(array(
					'type'=>'error',
					'content'=> __('Invalid email or user doesn\'t exists.', 'userswp')
				)
			);
			if(wp_doing_ajax()){wp_send_json_success($message);}
			else{$uwp_notices[] = array('forgot' => $message);return;}
		}

		// make sure user account is active before account reset
		$mod_value = get_user_meta( $user_data->ID, 'uwp_mod', true );
		if ($mod_value == 'email_unconfirmed') {
			$message = aui()->alert(array(
					'type'=>'error',
					'content'=> __('Your account is not activated yet. Please activate your account first.', 'userswp')
				)
			);
			if(wp_doing_ajax()){wp_send_json_error($message);}
			else{$uwp_notices[] = array('forgot' => $message); return;}
		}

		$user_data = get_userdata($user_data->ID);

		$allow = apply_filters('allow_password_reset', true, $user_data->ID);

		if ( !$allow )
			return false;
		else if ( is_wp_error($allow) )
			return false;

		$as_password = apply_filters('uwp_forgot_message_as_password', false);

		global $wpdb, $wp_hasher;

		if ($as_password) {
			$new_pass = wp_generate_password(12, false);
			wp_set_password($new_pass, $user_data->ID);
			if(!uwp_get_option('change_disable_password_nag')) {
				update_user_meta($user_data->ID, 'default_password_nag', true); //Set up the Password change nag.
			}
			$message = '<p><b>' . __('Your login Information :', 'userswp') . '</b></p>';
			$message .= '<p>' . sprintf(__('Username: %s', 'userswp'), $user_data->user_login) . "</p>";
			$message .= '<p>' . sprintf(__('Password: %s', 'userswp'), $new_pass) . "</p>";

		} else {
			$key = wp_generate_password( 20, false );
			do_action( 'retrieve_password_key', $user_data->user_login, $key );

			if ( empty( $wp_hasher ) ) {
				require_once ABSPATH . 'wp-includes/class-phpass.php';
				$wp_hasher = new PasswordHash( 8, true );
			}
			$hashed = $wp_hasher->HashPassword( $key );
			$wpdb->update( $wpdb->users, array( 'user_activation_key' => time().":".$hashed ), array( 'user_login' => $user_data->user_login ) );
			$message = '<p>' .__('You have requested to reset your password for the following account:', 'userswp') . "</p>";
			$message .= home_url( '/' ) . "</p>";
			$message .= '<p>' .sprintf(__('Username: %s', 'userswp'), $user_data->user_login) . "</p>";
			$message .= '<p>' .__('If this was by mistake, just ignore this email and nothing will happen.', 'userswp') . "</p>";
			$message .= '<p>' .__('To reset your password, click the following link and follow the instructions.', 'userswp') . "</p>";
			$reset_page = uwp_get_page_id('reset_page', false);
			$reset_link = '';
			if ($reset_page) {
				$reset_link = add_query_arg( array(
					'key' => $key,
					'login' => rawurlencode($user_data->user_login),
				), get_permalink($reset_page) );
				$message .= "<a href='".$reset_link."' target='_blank'>".$reset_link."</a>" . "\r\n";
			} else {
				$message .= home_url("reset?key=$key&login=" . rawurlencode($user_data->user_login), 'login') . "\r\n";
			}
			$message = apply_filters('uwp_forgot_password_message', $message, $user_data, $reset_link);
		}

		$message = apply_filters('uwp_forgot_mail_message', $message, $user_data->ID);

		$email_vars = array(
			'user_id' => $user_data->ID,
			'login_details' => $message,
		);

		UsersWP_Mails::send($user_data->user_email, 'forgot_password', $email_vars);

		do_action('uwp_after_process_forgot', $data);

		$message = aui()->alert(array(
				'type'=>'success',
				'content'=> apply_filters('uwp_change_password_success_message', __('Please check your email.', 'userswp'), $data)
			)
		);
		if(wp_doing_ajax()){wp_send_json_success($message);}
		else{$uwp_notices[] = array('forgot' => $message);}
	}

	/**
	 * Processes change password form submission.
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 */
	public function process_change() {

		$data = $_POST;

		if( ! isset( $data['uwp_change_nonce'] ) || ! wp_verify_nonce( $data['uwp_change_nonce'], 'uwp-change-nonce' ) ) {
			return;
		}

		global $uwp_notices;

		do_action('uwp_before_validate', 'change');

		$result = uwp_validate_fields($data, 'change');

		$result = apply_filters('uwp_validate_result', $result, 'change', $data);

		if (is_wp_error($result)) {
			$message = aui()->alert(array(
					'type'=>'error',
					'content'=> $result->get_error_message()
				)
			);
			$uwp_notices[] = array('change' => $message );
			return;
		}

		do_action('uwp_after_validate', 'change');

		$user_data = get_user_by('id', get_current_user_id());

		if (!$user_data) {
			$message = aui()->alert(array(
					'type'=>'error',
					'content'=> $user_data->get_error_message()
				)
			);
			$uwp_notices[] = array('change' => $message);
			return;
		}

		$email_vars = array(
			'user_id' => $user_data->ID,
		);

		UsersWP_Mails::send($user_data->user_email, 'change_password', $email_vars);

		wp_set_password( $result['password'], $user_data->ID );

		$password_nag = get_user_option('default_password_nag', $user_data->ID);
		if ($password_nag) {
			delete_user_meta( $user_data->ID, 'default_password_nag' );
		}

		$message = aui()->alert(array(
				'type'=>'success',
				'content'=> apply_filters('uwp_change_password_success_message', __('Password changed successfully.', 'userswp'), $data)
			)
		);

		$uwp_notices[] = array('change' => $message);

		do_action('uwp_after_process_change', $data);

		wp_logout();exit();
	}

	/**
	 * Processes reset password form submission.
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 */
	public function process_reset() {

		$data = $_POST;

		if( ! isset( $data['uwp_reset_nonce'] ) || ! wp_verify_nonce( $data['uwp_reset_nonce'], 'uwp-reset-nonce' ) ) {
			return;
		}

		global $uwp_notices;

		do_action('uwp_before_validate', 'reset');

		$result = uwp_validate_fields($data, 'reset');

		$result = apply_filters('uwp_validate_result', $result, 'reset', $data);

		if (is_wp_error($result)) {
			$message = aui()->alert(array(
					'type'=>'error',
					'content'=> $result->get_error_message()
				)
			);
			$uwp_notices[] = array('reset' => $message);
			return;
		}

		do_action('uwp_after_validate', 'reset');

		$login = $data['uwp_reset_username'];
		$key = $data['uwp_reset_key'];
		$user_data = check_password_reset_key( $key, $login );

		if (is_wp_error($user_data)) {
			$message = aui()->alert(array(
					'type'=>'error',
					'content'=> $user_data->get_error_message()
				)
			);
			$uwp_notices[] = array('reset' => $message);
			return;
		}

		$email_vars = array(
			'user_id' => $user_data->ID,
		);

		UsersWP_Mails::send($user_data->user_email, 'reset_password', $email_vars);

		wp_set_password( $data['password'], $user_data->ID );

		$login_page_url = uwp_get_login_page_url();
		$message = sprintf(__('Password updated successfully. Please <a href="%s">login</a> with your new password.', 'userswp'), $login_page_url);
		$message = apply_filters('uwp_reset_password_success_message', $message, $data);
		$message = aui()->alert(array(
				'type'=>'success',
				'content'=> $message
			)
		);
		$uwp_notices[] = array('reset' => $message);

		do_action('uwp_after_process_reset', $data);
	}

	/**
	 * Processes account form submission.
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 */
	public function process_account() {

		$data = $_POST;
		$files = $_FILES;

		if( ! isset( $data['uwp_account_nonce'] ) || ! wp_verify_nonce( $data['uwp_account_nonce'], 'uwp-account-nonce' ) ) {
			return;
		}

		if(!is_user_logged_in()){
            return;
        }

		global $uwp_notices;
		$file_obj = new UsersWP_Files();

		do_action('uwp_before_validate', 'account');

		$result = uwp_validate_fields($data, 'account');

		$result = apply_filters('uwp_validate_result', $result, 'account', $data);

		if (is_wp_error($result)) {
			$message = aui()->alert(array(
					'type'=>'error',
					'content'=> $result->get_error_message()
				)
			);
			$uwp_notices[] = array('account' => $message);
			return;
		}

		$uploads_result = $file_obj->validate_uploads($files, 'account');

		if (is_wp_error($uploads_result)) {
			$message = aui()->alert(array(
					'type'=>'error',
					'content'=> $uploads_result->get_error_message()
				)
			);
			$uwp_notices[] = array('account' => $message);
			return;
		}

		do_action('uwp_after_validate', 'account');

		//unset if value is empty for files
		foreach ($uploads_result as $upload_file_key => $upload_file_value) {
			if (empty($upload_file_value)) {
				unset($uploads_result[$upload_file_key]);
			}
		}

		$result = array_merge( $result, $uploads_result );


		$args = array(
			'ID' => get_current_user_id()
		);

		if (isset($result['email'])) {
			$args['user_email'] = $result['email'];
		}

		if (isset($result['first_name']) && isset($result['last_name'])) {
			$args['display_name'] = $result['first_name'] . ' ' . $result['last_name'];
		}

		if (isset($result['first_name'])) {
			$args['first_name'] = $result['first_name'];
		}

		if (isset($result['last_name'])) {
			$args['last_name'] = $result['last_name'];
		}

		if (isset($result['user_url'])) {
			$args['user_url'] = $result['user_url'];
		}

		if (isset($result['display_name']) && !empty($result['display_name'])) {
			$args['display_name'] = $result['display_name'];
		}

		if (isset($result['password'])) {
			$args['user_pass'] = $result['password'];
		}

		$user_id = wp_update_user( $args );

		if (is_wp_error($user_id)) {
			$message = aui()->alert(array(
					'type'=>'error',
					'content'=> sprintf(__('%s', 'userswp'), $user_id->get_error_message())
				)
			);
			$uwp_notices[] = array('account' => $message);
			return;
		}

		$res = $this->save_user_extra_fields($user_id, $result, 'account');

		if (!$res) {
			$message = aui()->alert(array(
					'type'=>'error',
					'content'=> __('Something went wrong. Please contact site admin.', 'userswp')
				)
			);
			$uwp_notices[] = array('account' => $message);
			return;
		}

		//updating bio field after saving extra fields to reflect the points in mycred add on.
		if (isset($result['bio']) && !empty($result['bio'])) {
			$args = array(
				'ID'  => $user_id,
				'description'  => $result['bio'],
			);
			wp_update_user( $args );
		}

		$user_data = get_userdata($user_id);

		$form_fields = apply_filters('uwp_send_mail_form_fields', "", 'account', $user_id);

		$email_vars = array(
			'user_id' => $user_id,
			'form_fields' => $form_fields,
		);

		UsersWP_Mails::send($user_data->user_email, 'account_update', $email_vars);

		$message = apply_filters('uwp_account_update_success_message', __('Account updated successfully.', 'userswp'), $data);
		$message = aui()->alert(array(
				'type'=>'success',
				'content'=> $message
			)
		);
		$uwp_notices[] = array('account' => $message);

		do_action('uwp_after_process_account', $data);

	}

	/**
	 * Processes avatar and banner uploads form submission.
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 * @param       array                   $data       Submitted $_POST data
	 * @param       array                   $files      Submitted $_FILES data
	 *
	 * @return      bool|WP_Error|string                File url to crop.
	 */
	public function process_upload_submit($data = array(), $files = array(), $type = 'avatar') {

		$file_obj = new UsersWP_Files();

		$current_user_id = get_current_user_id();
		if (!$current_user_id) {
			return false;
		}

		if( ! isset( $data['uwp_upload_nonce'] ) || ! wp_verify_nonce( $data['uwp_upload_nonce'], 'uwp-upload-nonce' ) ) {
			return false;
		}

		do_action('uwp_before_validate', $type);

		$result = $file_obj->validate_uploads($files, $type);

		$result = apply_filters('uwp_validate_result', $result, $type, $data);

		if (is_wp_error($result)) {
			return $result;
		}

		$profile_url = uwp_build_profile_tab_url($current_user_id);

		$url = add_query_arg(
			array(
				'uwp_crop' => $result['uwp_'.$type.'_file'],
				'type' => $type
			),
			$profile_url);

		return $url;

	}

	/**
	 * Processes avatar and banner image reset.
	 *
	 * @package     userswp
	 *
	 * @param       string                  $type       Image type. Default 'avatar'.
	 *
	 * @return      bool|WP_Error|string                Profile url.
	 */
	public function process_image_reset($type){
		if (!is_user_logged_in()) {
			return false;
		}

		if ( is_admin() && defined('IS_PROFILE_PAGE') && IS_PROFILE_PAGE ) {
			$user_id = get_current_user_id();
			// If is another user's profile page
		} elseif (is_admin() && ! empty($_GET['user_id']) && is_numeric($_GET['user_id']) ) {
			$user_id = $_GET['user_id'];
			// Otherwise something is wrong.
		} else {
			$user_id = get_current_user_id();
		}

		$errors = new WP_Error();
		if (empty($user_id)) {
			$errors->add('something_wrong', __('Something went wrong. Please try again.', 'userswp'));
		}

		$error_code = $errors->get_error_code();
		if (!empty($error_code)) {
			return $errors;
		}

		if ($type == 'avatar') {
			uwp_update_usermeta($user_id, 'avatar_thumb', '');
		} elseif ($type == 'banner') {
			uwp_update_usermeta($user_id, 'banner_thumb', '');
        } else {
		    // Do nothing
        }

		if (is_admin()) {
			if ($user_id == get_current_user_id()) {
				$profile_url = admin_url( 'profile.php' );
			} else {
				$profile_url = admin_url( 'user-edit.php?user_id='.$user_id );
			}
		} else {
			$profile_url = uwp_build_profile_tab_url($user_id);
		}

		return $profile_url;
    }

	/**
	 * Processes avatar and banner uploads image crop.
	 *
	 * @since       1.0.0
	 * @since       1.0.12 New param $unlink_prev_img introduced.
	 * @package     userswp
	 *
	 * @param       array                   $data       Submitted $_POST data
	 * @param       string                  $type       Image type. Default 'avatar'.
	 * @param       bool         			$unlink_prev_img True to remove previous image. Default false;
	 *
	 * @return      bool|WP_Error|string                Profile url.
	 */
	public function process_image_crop($data = array(), $type = 'avatar', $unlink_prev_img = false) {

		if (!is_user_logged_in()) {
			return false;
		}

		// If is current user's profile (profile.php)
		if ( is_admin() && defined('IS_PROFILE_PAGE') && IS_PROFILE_PAGE ) {
			$user_id = get_current_user_id();
			// If is another user's profile page
		} elseif (is_admin() && ! empty($_GET['user_id']) && is_numeric($_GET['user_id']) ) {
			$user_id = $_GET['user_id'];
			// Otherwise something is wrong.
		} else {
			$user_id = get_current_user_id();
		}
		$image_url = $data['uwp_crop'];

		$errors = new WP_Error();
		if (empty($image_url)) {
			$errors->add('something_wrong', __('Something went wrong. Please contact site admin.', 'userswp'));
		}

		$error_code = $errors->get_error_code();
		if (!empty($error_code)) {
			return $errors;
		}

		if ($image_url) {
			if ($type == 'avatar') {
				$full_width  = apply_filters('uwp_avatar_image_width', uwp_get_option('profile_avatar_width', 150));
			} else {
				$full_width  = apply_filters('uwp_banner_image_width', uwp_get_option('profile_banner_width', 1000));
			}

			$image_url = esc_url($image_url);
			add_filter( 'upload_dir', 'uwp_handle_multisite_profile_image', 10, 1 );
			$uploads = wp_upload_dir();
			remove_filter( 'upload_dir', 'uwp_handle_multisite_profile_image' );
			$upload_url = $uploads['baseurl'];
			$upload_path = $uploads['basedir'];
			$image_url = str_replace($upload_url, $upload_path, $image_url);
			$ext = pathinfo($image_url, PATHINFO_EXTENSION); // to get extension
			$name =pathinfo($image_url, PATHINFO_FILENAME); //file name without extension
			$thumb_image_name = $name.'_uwp_'.$type.'_thumb'.'.'.$ext;
			$thumb_image_location = str_replace($name.'.'.$ext, $thumb_image_name, $image_url);
			//Get the new coordinates to crop the image.
			$x = $data["x"];
			$y = $data["y"];
			$w = $data["w"];
			$h = $data["h"];
			//Scale the image based on cropped width setting
			$scale = $full_width/$w;
			//$scale = 1; // no scaling
			$cropped = uwp_resizeThumbnailImage($thumb_image_location, $image_url,$x, $y, $w, $h,$scale);
			$cropped = str_replace($upload_path, $upload_url, $cropped);

			// Remove previous avatar/banner
			$unlink_img = '';
			if ($unlink_prev_img) {
				if ($type == 'avatar') {
					$previous_img = uwp_get_usermeta($user_id, 'avatar_thumb');
				} else if ($type == 'banner') {
					$previous_img = uwp_get_usermeta($user_id, 'banner_thumb');
				} else {
					$previous_img = '';
				}

				if ($previous_img) {
					$unlink_img = untrailingslashit($upload_path) . '/' . ltrim($previous_img, '/');
				}
			}

			// remove the uploads path for easy migrations
			$cropped = str_replace($upload_url, '', $cropped);
			if ($type == 'avatar') {
				uwp_update_usermeta($user_id, 'avatar_thumb', $cropped);
			} else {
				uwp_update_usermeta($user_id, 'banner_thumb', $cropped);
			}

			if ($unlink_img && $unlink_img != $thumb_image_location && is_file($unlink_img) && file_exists($unlink_img)) {
				@unlink($unlink_img);
				$unlink_ori_img = str_replace('_uwp_'.$type.'_thumb'.'.', '.', $unlink_img);
				if (is_file($unlink_ori_img) && file_exists($unlink_ori_img)) {
					@unlink($unlink_ori_img);
				}
			}
		}

		if (is_admin()) {
			if ($user_id == get_current_user_id()) {
				$profile_url = admin_url( 'profile.php' );
			} else {
				$profile_url = admin_url( 'user-edit.php?user_id='.$user_id );
			}
		} else {
			$profile_url = uwp_build_profile_tab_url($user_id);
		}
		return $profile_url;

	}

	/**
	 * Modifies the forms field in email based on the form type.
	 *
	 * @package    userswp
	 * @subpackage userswp/includes
	 * @param string $form_fields Form fields.
	 * @param string $type Form type.
	 * @param int $user_id User ID.
	 * @return string Modified mail field.
	 */
	public function init_mail_form_fields( $form_fields, $type, $user_id ) {
		switch ($type) {
			case "register":
				$fields = get_register_form_fields();
				$user_data = get_userdata($user_id);
				if( !empty( $fields ) && is_array($fields)) {
					$form_fields = '<p><b>'.__('User Information:','userswp').'</b></p>';
					$excluded = uwp_get_excluded_fields();
					foreach ( $fields as $key => $field ) {
						if ($field->is_active != '1' || in_array($field->htmlvar_name, $excluded)) {
							continue;
						}
						if( $field->htmlvar_name == 'email' && isset($user_data->user_email) ) {
							$field_value = $user_data->user_email;
						} elseif ( $field->htmlvar_name == 'display_name' && isset($user_data->user_login) ) {
							$field_value = $user_data->user_login;
						} elseif ( $field->htmlvar_name == 'bio' ){
							$field_value = get_user_meta($user_id, 'description', true);
						} else {
							$field_value = uwp_get_usermeta($user_id, $field->htmlvar_name);
						}

						if(is_array($field_value) && count($field_value) > 0){
							$field_value = uwp_maybe_serialize($field->htmlvar_name, $field_value);
						}

						if(isset($field->site_title) && !empty($field_value)){
							$form_fields .= '<p><b>' . __($field->site_title, 'userswp') . '</b>:&nbsp;' . $field_value . '</p>';
						}
					}
				}
				break;
			case "account":
				$fields = get_account_form_fields();
				$user_data = get_userdata($user_id);
				if( !empty( $fields ) && is_array($fields)) {
					$form_fields = '<p><b>'.__('User Information:','userswp').'</b></p>';
					foreach ( $fields as $key => $field ) {
						if ($field->is_active != '1') {
							continue;
						}
						if( $field->htmlvar_name == 'email' && isset($user_data->user_email) ) {
							$field_value = $user_data->user_email;
						} elseif ( $field->htmlvar_name == 'display_name' && isset($user_data->user_login) ) {
							$field_value = $user_data->user_login;
						} elseif ( $field->htmlvar_name == 'bio' ){
							$field_value = get_user_meta($user_id, 'description', true);
						} else {
							$field_value = uwp_get_usermeta($user_id, $field->htmlvar_name);
						}

						if(is_array($field_value) && count($field_value) > 0){
							$field_value = uwp_maybe_serialize($field->htmlvar_name, $field_value);
						}

						if(isset($field->site_title) && !empty($field_value)){
							$form_fields .= '<p><b>' . __($field->site_title, 'userswp') . '</b>:&nbsp;' . $field_value . '</p>';
						}
					}
				}
				break;
		}
		return apply_filters('uwp_mail_form_fields', $form_fields, $type, $user_id );
	}

	/**
	 * Saves UsersWP related user custom fields.
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 * @param       int         $user_id        User ID.
	 * @param       array       $data           Result array.
	 * @param       string      $type           Form type.
	 *
	 * @return      bool                        True when success. False when failure.
	 */
	public function save_user_extra_fields($user_id, $data, $type) {

		if (empty($user_id) || empty($data) || empty($type)) {
			return false;
		}

		// custom user fields not applicable for login and forgot
		if ($type == 'login' || $type == 'forgot') {
			return true;
		}


		if ($type == 'account' || $type == 'register') {
			if (isset($data['password'])) {
				unset($data['password']);
			}
		}

		if ($type == 'register') {
			if (isset($data['username'])) {
				unset($data['username']);
			}
			if (isset($data['email'])) {
				unset($data['email']);
			}
			if (isset($data['display_name'])) {
				unset($data['display_name']);
			}
			if (isset($data['first_name'])) {
				unset($data['first_name']);
			}
			if (isset($data['last_name'])) {
				unset($data['last_name']);
			}
		}

		if (empty($data)) {
			// no extra fields. so just return
			return true;
		} else {
			foreach($data as $key => $value) {
				uwp_update_usermeta($user_id, $key, $value);
			}
			return true;
		}
	}

	/**
	 * Logs the error message.
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 * @param       array|object|string     $log        Error message.
	 *
	 * @return      void
	 */
	public static function uwp_error_log($log){
		uwp_error_log($log);
	}

	/**
	 *
	 *
	 * @since   1.0.0
	 * @since   1.0.12 Unlink file.
	 * @package userswp
	 * @return void
	 */
	public function upload_file_remove() {

		$htmlvar = strip_tags(esc_sql($_POST['htmlvar']));
		$user_id = (int) strip_tags(esc_sql($_POST['uid']));
		$permission = false;
		if ($user_id == get_current_user_id()) {
			$permission = true;
		} else {
			if (current_user_can('manage_options')) {
				$permission = true;
			}
		}
		if ($permission) {
			// Remove file
			if ($htmlvar == "banner_thumb") {
				$file = uwp_get_usermeta($user_id, 'banner_thumb');
				$type = 'banner';
			} elseif ($htmlvar == "avatar_thumb") {
				$file = uwp_get_usermeta($user_id, 'avatar_thumb');
				$type = 'avatar';
			} else {
				$file = '';
				$type = '';
			}

			uwp_update_usermeta($user_id, $htmlvar, '');

			if ($file) {
				$uploads = wp_upload_dir();
				$upload_path = $uploads['basedir'];
				$unlink_file = untrailingslashit($upload_path) . '/' . ltrim($file, '/');

				if (is_file($unlink_file) && file_exists($unlink_file)) {
					@unlink($unlink_file);
					$unlink_ori_file = str_replace('_uwp_'.$type.'_thumb'.'.', '.', $unlink_file);
					if (is_file($unlink_ori_file) && file_exists($unlink_ori_file)) {
						@unlink($unlink_ori_file);
					}
				}
			}
		}
		die();
	}


	/**
	 * Form field template for datepicker field type.
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 * @param       string          $html           Form field html
	 * @param       object          $field          Field info.
	 * @param       string          $value          Form field default value.
	 * @param       string          $form_type      Form type
	 *
	 * @return      string                          Modified form field html.
	 */
	public function form_input_datepicker($html, $field, $value, $form_type){

		// Check if there is a field specific filter.
		if(has_filter("uwp_form_input_html_datepicker_{$field->htmlvar_name}")){
			$html = apply_filters("uwp_form_input_html_datepicker_{$field->htmlvar_name}", $html, $field, $value, $form_type);
		}

		// If no html then we run the standard output.
		if(empty($html)) {

			$design_style = uwp_get_option("design_style","bootstrap");
			$bs_form_group = $design_style ? "form-group" : "";
			$bs_sr_only = $design_style ? "sr-only" : "";
			$bs_form_control = $design_style ? "form-control" : "";

			ob_start(); // Start  buffering;

			$extra_fields = unserialize($field->extra_fields);

			if ($extra_fields['date_format'] == '')
				$extra_fields['date_format'] = 'yy-mm-dd';

			$date_format = $extra_fields['date_format'];
			$jquery_date_format  = $date_format;

			if (!empty($value) && !is_string($value)) {
				$value = date('Y-m-d', $value);
			}


			// check if we need to change the format or not
			$date_format_len = strlen(str_replace(' ', '', $date_format));
			if($date_format_len>5){// if greater then 5 then it's the old style format.

				$search = array('dd','d','DD','mm','m','MM','yy'); //jQuery UI datepicker format
				$replace = array('d','j','l','m','n','F','Y');//PHP date format

				$date_format = str_replace($search, $replace, $date_format);
			}else{
				$jquery_date_format = uwp_date_format_php_to_jqueryui( $jquery_date_format );
			}
			if($value=='0000-00-00'){$value='';}//if date not set, then mark it empty
			$value = uwp_date($value, 'Y-m-d', $date_format);
			?>
            <script type="text/javascript">

                jQuery(function () {

                    jQuery("#<?php echo $field->htmlvar_name;?>").datepicker({changeMonth: true, changeYear: true
						<?php if($field->htmlvar_name == 'dob'){ echo ", yearRange: '1900:+0'"; } else { echo ", yearRange: '1900:2050'"; }?>
						<?php echo apply_filters("uwp_datepicker_extra_{$field->htmlvar_name}",'');?>});

                    jQuery("#<?php echo $field->htmlvar_name;?>").datepicker("option", "dateFormat", '<?php echo $jquery_date_format;?>');

					<?php if(!empty($value)){?>
                    var parsedDate = jQuery.datepicker.parseDate('yy-mm-dd', '<?php echo $value;?>');
                    jQuery("#<?php echo $field->htmlvar_name;?>").datepicker("setDate", parsedDate);
					<?php } ?>

                });

            </script>
            <div id="<?php echo $field->htmlvar_name;?>_row"
                 class="<?php if ($field->is_required) echo 'required_field';?> uwp_form_row clearfix uwp_clear uwp-fieldset-details <?php echo esc_attr($bs_form_group);?>">

				<?php
				$site_title = uwp_get_form_label($field);
				if (!is_admin()) { ?>
                    <label class="<?php echo esc_attr($bs_sr_only);?>">
						<?php echo (trim($site_title)) ? $site_title : '&nbsp;'; ?>
						<?php if ($field->is_required) echo '<span>*</span>';?>
                    </label>
				<?php } ?>

                <input name="<?php echo $field->htmlvar_name;?>"
                       id="<?php echo $field->htmlvar_name;?>"
                       placeholder="<?php echo uwp_get_field_placeholder($field); ?>"
                       title="<?php echo $site_title; ?>"
                       type="text"
					<?php if ($field->is_required == 1) { echo 'required="required"'; } ?>
                       value="<?php echo esc_attr($value);?>" class="uwp_textfield <?php echo esc_attr($bs_form_control);?>"/>

                <span class="uwp_message_note"><?php echo uwp_get_field_description($field);?></span>
				<?php if ($field->is_required) { ?>
                    <span class="uwp_message_error invalid-feedback"><?php _e($field->required_msg, 'userswp'); ?></span>
				<?php } ?>
            </div>

			<?php
			$html = ob_get_clean();
		}

		return $html;
	}

	/**
	 * Form field template for time field type.
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 * @param       string          $html           Form field html
	 * @param       object          $field          Field info.
	 * @param       string          $value          Form field default value.
	 * @param       string          $form_type      Form type
	 *
	 * @return      string                          Modified form field html.
	 */
	public function form_input_time($html, $field, $value, $form_type){

		if(has_filter("uwp_form_input_html_time_{$field->htmlvar_name}")){

			$html = apply_filters("uwp_form_input_html_time_{$field->htmlvar_name}",$html, $field, $value, $form_type);
		}

		// If no html then we run the standard output.
		if(empty($html)) {

			$design_style = uwp_get_option("design_style","bootstrap");
			$bs_form_group = $design_style ? "form-group" : "";
			$bs_sr_only = $design_style ? "sr-only" : "";
			$bs_form_control = $design_style ? "form-control bg-white" : "";

			ob_start(); // Start  buffering;

			if ($value != '')
				$value = date('H:i', strtotime($value));
			?>
            <script type="text/javascript">
                jQuery(document).ready(function () {

                    jQuery('#<?php echo $field->htmlvar_name;?>').timepicker({
                        showPeriod: true,
                        showLeadingZero: true
                    });
                });
            </script>
            <div id="<?php echo $field->htmlvar_name;?>_row"
                 class="<?php if ($field->is_required) echo 'required_field';?> uwp_form_row clearfix uwp_clear uwp-fieldset-details <?php echo esc_attr($bs_form_group);?>">

				<?php
				$site_title = uwp_get_form_label($field);
				if (!is_admin()) { ?>
                    <label class="<?php echo esc_attr($bs_sr_only);?>">
						<?php echo (trim($site_title)) ? $site_title : '&nbsp;'; ?>
						<?php if ($field->is_required) echo '<span>*</span>';?>
                    </label>
				<?php } ?>

                <input readonly="readonly" name="<?php echo $field->htmlvar_name;?>"
                       id="<?php echo $field->htmlvar_name;?>"
                       value="<?php echo esc_attr($value);?>"
                       placeholder="<?php echo uwp_get_field_placeholder($field); ?>"
                       type="text"
                       class="uwp_textfield <?php echo esc_attr($bs_form_control);?>"/>

                <span class="uwp_message_note"><?php echo uwp_get_field_description($field);?></span>
				<?php if ($field->is_required) { ?>
                    <span class="uwp_message_error invalid-feedback"><?php _e($field->required_msg, 'userswp'); ?></span>
				<?php } ?>
            </div>
			<?php
			$html = ob_get_clean();
		}

		return $html;
	}

	/**
	 * Form field template for select field type.
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 * @param       string          $html           Form field html
	 * @param       object          $field          Field info.
	 * @param       string          $value          Form field default value.
	 * @param       string          $form_type      Form type
	 *
	 * @return      string                          Modified form field html.
	 */
	public function form_input_select($html, $field, $value, $form_type){

		// Check if there is a field specific filter.
		if(has_filter("uwp_form_input_html_select_{$field->htmlvar_name}")){
			$html = apply_filters("uwp_form_input_html_select_{$field->htmlvar_name}", $html, $field, $value, $form_type);
		}

		// Check if there is a field type specific filter.
		if(has_filter("uwp_form_input_html_select_{$field->field_type_key}")){
			$html = apply_filters("uwp_form_input_html_select_{$field->field_type_key}", $html, $field, $value, $form_type);
		}

		// If no html then we run the standard output.
		if(empty($html)) {

			$design_style = uwp_get_option("design_style","bootstrap");
			$bs_form_group = $design_style ? "form-group" : "";
			$bs_sr_only = $design_style ? "sr-only" : "";
			$bs_form_control = $design_style ? "form-control" : "";

			ob_start(); // Start  buffering;
			$option_values_arr = uwp_string_values_to_options($field->option_values, true);
			$site_title = uwp_get_form_label($field);

			// bootstrap
			if( $design_style ) {

				echo aui()->select(array(
					'id'    =>  $field->htmlvar_name,
					'name'    =>  $field->htmlvar_name,
					'placeholder'   => uwp_get_field_placeholder($field),
					'title'   => $site_title,
					'value' =>  $value,
					'required'  => $field->is_required,
					'validation_text' => !empty($field->is_required) ? __($field->required_msg, 'userswp') : '',
					'help_text' => uwp_get_field_description($field),
					'label' => $site_title,
					'options'=> $option_values_arr,
					'select2' => true
				));
			}else{
				?>
                <div id="<?php echo $field->htmlvar_name;?>_row"
                     class="<?php if ($field->is_required) echo 'required_field';?> uwp_form_row uwp_clear <?php echo esc_attr($bs_form_group);?>">

					<?php
					if (!is_admin()) { ?>
                        <label class="<?php echo esc_attr($bs_sr_only);?>">
							<?php echo (trim($site_title)) ? $site_title : '&nbsp;'; ?>
							<?php if ($field->is_required) echo '<span>*</span>';?>
                        </label>
					<?php } ?>

					<?php

					$select_options = '';
					if (!empty($option_values_arr)) {
						foreach ($option_values_arr as $option_row) {
							if (isset($option_row['optgroup']) && ($option_row['optgroup'] == 'start' || $option_row['optgroup'] == 'end')) {
								$option_label = isset($option_row['label']) ? $option_row['label'] : '';

								$select_options .= $option_row['optgroup'] == 'start' ? '<optgroup label="' . esc_attr($option_label) . '">' : '</optgroup>';
							} else {
								$option_label = isset($option_row['label']) ? $option_row['label'] : '';
								$option_value = isset($option_row['value']) ? $option_row['value'] : '';
								$selected = $option_value == $value ? 'selected="selected"' : '';

								$select_options .= '<option value="' . esc_attr($option_value) . '" ' . $selected . '>' . $option_label . '</option>';
							}
						}
					}
					?>
                    <select name="<?php echo $field->htmlvar_name;?>" id="<?php echo $field->htmlvar_name;?>"
                            class="uwp_textfield aui-select2 <?php echo esc_attr($bs_form_control);?>"
                            title="<?php echo $site_title; ?>"
                            data-placeholder="<?php echo uwp_get_field_placeholder($field); ?>"
                    ><?php echo $select_options;?>
                    </select>
                    <span class="uwp_message_note"><?php echo uwp_get_field_description($field);?></span>
					<?php if ($field->is_required) { ?>
                        <span class="uwp_message_error invalid-feedback"><?php _e($field->required_msg, 'userswp'); ?></span>
					<?php } ?>
                </div>

				<?php
			}
			$html = ob_get_clean();
		}

		return $html;
	}

	/**
	 * Form field template for multiselect field type.
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 * @param       string          $html           Form field html
	 * @param       object          $field          Field info.
	 * @param       string          $value          Form field default value.
	 * @param       string          $form_type      Form type
	 *
	 * @return      string                          Modified form field html.
	 */
	public function form_input_multiselect($html, $field, $value, $form_type){

		// Check if there is a field specific filter.
		if(has_filter("uwp_form_input_html_multiselect_{$field->htmlvar_name}")){
			$html = apply_filters("uwp_form_input_html_multiselect_{$field->htmlvar_name}", $html, $field, $value, $form_type);
		}

		if(empty($html)) {

			$design_style = uwp_get_option("design_style","bootstrap");
			$bs_form_group = $design_style ? "form-group" : "";
			$bs_sr_only = $design_style ? "sr-only" : "";
			$bs_form_control = $design_style ? "form-control" : "";

			ob_start(); // Start  buffering;

			$multi_display = 'select';
			if (!empty($field->extra_fields)) {
				$multi_display = unserialize($field->extra_fields);
			}

			$option_values_arr = uwp_string_values_to_options($field->option_values, true);
			$site_title = uwp_get_form_label($field);
			// bootstrap
			if( $design_style ) {

				echo aui()->select(array(
					'id'    =>  $field->htmlvar_name,
					'name'    =>  $field->htmlvar_name,
					'placeholder'   => uwp_get_field_placeholder($field),
					'title'   => $site_title,
					'value' =>  $value,
					'required'  => $field->is_required,
					'validation_text' => !empty($field->is_required) ? __($field->required_msg, 'userswp') : '',
					'help_text' => uwp_get_field_description($field),
					'label' => $site_title,
					'options'=> $option_values_arr,
					'select2' => true,
					'multiple' => true
				));
			}else {
				?>
                <div id="<?php echo $field->htmlvar_name; ?>_row"
                     class="<?php if ( $field->is_required ) {
					     echo 'required_field';
				     } ?> uwp_form_row uwp_clear <?php echo esc_attr( $bs_form_group ); ?>">

					<?php
					if ( ! is_admin() ) { ?>
                        <label class="<?php echo esc_attr( $bs_sr_only ); ?>">
							<?php echo ( trim( $site_title ) ) ? $site_title : '&nbsp;'; ?>
							<?php if ( $field->is_required ) {
								echo '<span>*</span>';
							} ?>
                        </label>
					<?php } ?>

                    <input type="hidden" name="<?php echo $field->htmlvar_name; ?>" value=""/>
					<?php if ( $multi_display == 'select' ) { ?>
                    <div class="uwp_multiselect_list">
                        <select name="<?php echo $field->htmlvar_name; ?>[]"
                                id="<?php echo $field->htmlvar_name; ?>"
                                title="<?php echo $site_title; ?>"
                                data-placeholder="<?php echo uwp_get_field_placeholder($field); ?>"
                                class="aui-select2 <?php echo esc_attr( $bs_form_control ); ?>"
                        >
							<?php
							} else {
								?>
                                <ul class="uwp_multi_choice">
								<?php
							}

							$option_values_arr = uwp_string_values_to_options( $field->option_values, true );
							$select_options    = '';
							if ( ! empty( $option_values_arr ) ) {
								foreach ( $option_values_arr as $option_row ) {
									if ( isset( $option_row['optgroup'] ) && ( $option_row['optgroup'] == 'start' || $option_row['optgroup'] == 'end' ) ) {
										$option_label = isset( $option_row['label'] ) ? $option_row['label'] : '';

										if ( $multi_display == 'select' ) {
											$select_options .= $option_row['optgroup'] == 'start' ? '<optgroup label="' . esc_attr( $option_label ) . '">' : '</optgroup>';
										} else {
											$select_options .= $option_row['optgroup'] == 'start' ? '<li>' . $option_label . '</li>' : '';
										}
									} else {
										$option_label = isset( $option_row['label'] ) ? $option_row['label'] : '';
										$option_value = isset( $option_row['value'] ) ? $option_row['value'] : '';
										$selected     = $option_value == $value ? 'selected="selected"' : '';
										$checked      = '';

										if ( ( ! is_array( $value ) && trim( $value ) != '' ) || ( is_array( $value ) && ! empty( $value ) ) ) {
											if ( ! is_array( $value ) ) {
												$value_array = explode( ',', $value );
											} else {
												$value_array = $value;
											}

											if ( is_array( $value_array ) ) {
												if ( in_array( $option_value, $value_array ) ) {
													$selected = 'selected="selected"';
													$checked  = 'checked="checked"';
												}
											}
										}

										if ( $multi_display == 'select' ) {
											$select_options .= '<option value="' . esc_attr( $option_value ) . '" ' . $selected . '>' . $option_label . '</option>';
										} else {
											$select_options .= '<li><input name="' . $field->name . '[]" ' . $checked . ' value="' . esc_attr( $option_value ) . '" class="uwp-' . $multi_display . '" type="' . $multi_display . '" />&nbsp;' . $option_label . ' </li>';
										}
									}
								}
							}
							echo $select_options;

							if ( $multi_display == 'select' ) { ?></select></div>
				<?php } else { ?>
                    </ul>
				<?php } ?>
					<?php if ( $field->is_required ) { ?>
                        <span class="uwp_message_error invalid-feedback"><?php _e( $field->required_msg, 'userswp' ); ?></span>
					<?php } ?>
                </div>
				<?php
			}
			$html = ob_get_clean();
		}

		return $html;
	}

	/**
	 * Form field template for file field type.
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 * @param       string          $html           Form field html
	 * @param       object          $field          Field info.
	 * @param       string          $value          Form field default value.
	 * @param       string          $form_type      Form type
	 *
	 * @return      string                          Modified form field html.
	 */
	public function form_input_file($html, $field, $value, $form_type){

		$file_obj = new UsersWP_Files();

		// Check if there is a field specific filter.
		if(has_filter("uwp_form_input_html_file_{$field->htmlvar_name}")){
			$html = apply_filters("uwp_form_input_html_file_{$field->htmlvar_name}", $html, $field, $value, $form_type);
		}

		// If no html then we run the standard output.
		if(empty($html)) {

			$design_style = uwp_get_option("design_style","bootstrap");
			$bs_form_group = $design_style ? "form-group" : "";
			$bs_sr_only = $design_style ? "sr-only" : "";
			$bs_form_control = $design_style ? "form-control" : "";

			ob_start(); // Start  buffering;

			?>
            <div id="<?php echo $field->htmlvar_name;?>_row"
                 class="<?php if ($field->is_required) echo 'required_field';?> uwp_form_<?php echo $field->field_type; ?>_row uwp_clear <?php echo esc_attr($bs_form_group);?>">

				<?php
				$site_title = uwp_get_form_label($field);
				if (!is_admin()) { ?>
                    <label class="<?php echo esc_attr($bs_sr_only);?>">
						<?php echo (trim($site_title)) ? $site_title : '&nbsp;'; ?>
						<?php if ($field->is_required) echo '<span>*</span>';?>
                    </label>
				<?php } ?>

				<?php echo $file_obj->file_upload_preview($field, $value); ?>
                <input name="<?php echo $field->htmlvar_name; ?>"
                       class="<?php echo $field->css_class; ?> <?php echo esc_attr($bs_form_control);?>"
                       placeholder="<?php echo uwp_get_field_placeholder($field); ?>"
                       title="<?php echo $site_title; ?>"
					<?php
					if ($field->is_required == 1 ) { echo 'data-is-required="1"'; }
					if ($field->is_required == 1 && !$value) { echo 'required="required"'; }
					?>
                       type="<?php echo $field->field_type; ?>">
                <span class="uwp_message_note"><?php echo uwp_get_field_description($field);?></span>
				<?php if ($field->is_required) { ?>
                    <span class="uwp_message_error invalid-feedback"><?php _e($field->required_msg, 'userswp'); ?></span>
				<?php } ?>
            </div>

			<?php
			$html = ob_get_clean();
		}

		return $html;
	}

	/**
	 * Form field template for checkbox field type.
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 * @param       string          $html           Form field html
	 * @param       object          $field          Field info.
	 * @param       string          $value          Form field default value.
	 * @param       string          $form_type      Form type
	 *
	 * @return      string                          Modified form field html.
	 */
	public function form_input_checkbox($html, $field, $value, $form_type){

		// Check if there is a field specific filter.
		if(has_filter("uwp_form_input_html_checkbox_{$field->htmlvar_name}")){
			$html = apply_filters("uwp_form_input_html_checkbox_{$field->htmlvar_name}", $html, $field, $value, $form_type);
		}

		// If no html then we run the standard output.
		if(empty($html)) {

			$design_style = uwp_get_option("design_style","bootstrap");
			$bs_form_group = $design_style ? "form-group form-check" : "";
			$bs_sr_only = $design_style ? "form-check-label" : "";
			$bs_form_control = $design_style ? "form-check-input" : "";

			ob_start(); // Start  buffering;
			$site_title = uwp_get_form_label($field);

			$design_style = uwp_get_option("design_style","bootstrap");
			$id = wp_doing_ajax() ? $field->htmlvar_name."_ajax" : $field->htmlvar_name;

			// bootstrap
			if( $design_style ) { ?>
                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="hidden" name="<?php echo $field->htmlvar_name; ?>" id="<?php echo $id; ?>_hidden" value="1">
                        <input type="checkbox" name="<?php echo $field->htmlvar_name; ?>" id="<?php echo $id; ?>" placeholder="<?php echo $field->htmlvar_name; ?>" title="<?php echo $site_title; ?>" value="1" class="form-control custom-control-input"
                               onchange="if(this.checked){jQuery('#<?php echo $id; ?>_hidden').val('1');} else{ jQuery('#<?php echo $id; ?>_hidden').val('0');}" <?php echo checked($value, 1)?> <?php if ($field->is_required == 1) { echo 'required="required"'; } ?>>
                        <label for="<?php echo $id; ?>" class="custom-control-label"><?php echo $site_title; ?></label>
                    </div>
                </div>
			<?php
			}else{
				?>
                <div id="<?php echo $field->htmlvar_name;?>_row"
                     class="<?php if ($field->is_required) echo 'required_field';?> uwp_form_<?php echo $field->field_type; ?>_row uwp_clear <?php echo esc_attr($bs_form_group);?>">
					<?php if(!empty($design_style)){ ?>
                    <label class="<?php echo $bs_sr_only; ?>">
						<?php } ?>
                        <input type="hidden" name="<?php echo $field->htmlvar_name; ?>" value="0" />
                        <input name="<?php echo $field->htmlvar_name; ?>"
                               class="<?php echo $field->css_class; ?> <?php echo esc_attr($bs_form_control);?>"
                               placeholder="<?php echo uwp_get_field_placeholder($field); ?>"
                               title="<?php echo $site_title; ?>"
							<?php if ($field->is_required == 1) { echo 'required="required"'; } ?>
							<?php if ($value == '1') { echo 'checked="checked"'; } ?>
                               type="<?php echo $field->field_type; ?>"
                               value="1">
						<?php
						echo (trim($site_title)) ? $site_title : '&nbsp;';
						?>
						<?php if(!empty($design_style)){ ?>
                    </label>
				<?php } ?>
                    <span class="uwp_message_note"><?php echo uwp_get_field_description($field);?></span>
					<?php if ($field->is_required) { ?>
                        <span class="uwp_message_error invalid-feedback"><?php _e($field->required_msg, 'userswp'); ?></span>
					<?php } ?>
                </div>

				<?php

			}
			$html = ob_get_clean();

		}

		return $html;
	}

	/**
	 * Form field template for radio field type.
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 * @param       string          $html           Form field html
	 * @param       object          $field          Field info.
	 * @param       string          $value          Form field default value.
	 * @param       string          $form_type      Form type
	 *
	 * @return      string                          Modified form field html.
	 */
	public function form_input_radio($html, $field, $value, $form_type){

		// Check if there is a field specific filter.
		if(has_filter("uwp_form_input_html_radio_{$field->htmlvar_name}")){
			$html = apply_filters("uwp_form_input_html_radio_{$field->htmlvar_name}", $html, $field, $value, $form_type);
		}

		// If no html then we run the standard output.
		if(empty($html)) {

			$design_style = uwp_get_option("design_style","bootstrap");
			$bs_form_group = $design_style ? "form-group form-check-inline" : "";
			$bs_sr_only = $design_style ? "sr-only" : "";
			$bs_label_class = $design_style ? "form-check-label" : "";
			$bs_form_control = $design_style ? "form-check-input" : "";

			ob_start(); // Start  buffering;

			?>
            <div id="<?php echo $field->htmlvar_name;?>_row"
                 class="<?php if ($field->is_required) echo 'required_field';?> uwp_form_<?php echo $field->field_type; ?>_row uwp_clear <?php echo esc_attr($bs_form_group);?>">

				<?php
				$site_title = uwp_get_form_label($field);
				if (!is_admin()) { ?>
                    <label class="<?php echo esc_attr($bs_sr_only);?>">
						<?php echo (trim($site_title)) ? $site_title : '&nbsp;'; ?>
						<?php if ($field->is_required) echo '<span>*</span>';?>
                    </label>
				<?php } ?>

				<?php if ($field->option_values) {
					$option_values = uwp_string_values_to_options($field->option_values, true);

					if (!empty($option_values)) {
						$count = 0;
						foreach ($option_values as $option_value) {
							if (empty($option_value['optgroup'])) {
								$count++;
								if ($count == 1) {
									$class = "uwp-radio-first";
								} else {
									$class = "";
								}
								?>
								<?php if(!empty($design_style)){ ?>
                                    <label class="<?php echo $bs_label_class; ?>">
								<?php } else { ?>
                                    <span class="uwp-radios <?php echo $class; ?>">
								<?php } ?>
                                <input name="<?php echo $field->htmlvar_name; ?>"
                                       id="<?php echo $field->htmlvar_name; ?>"
                                       title="<?php echo esc_attr($option_value['label']); ?>"
									<?php checked($value, $option_value['value']);?>
									<?php if ($field->is_required == 1) { echo 'required="required"'; } ?>
                                       value="<?php echo esc_attr($option_value['value']); ?>"
                                       class="uwp-radio <?php echo esc_attr($bs_form_control);?>" type="radio" />
								<?php echo $option_value['label']; ?>
								<?php if(!empty($design_style)){ ?>
                                    </label>
								<?php } else { ?>
                                    </span>
									<?php
								}
							}
						}
					}
				}
				?>
                <span class="uwp_message_note"><?php echo uwp_get_field_description($field);?></span>
				<?php if ($field->is_required) { ?>
                    <span class="uwp_message_error invalid-feedback"><?php _e($field->required_msg, 'userswp'); ?></span>
				<?php } ?>
            </div>
			<?php
			$html = ob_get_clean();
		}

		return $html;
	}

	/**
	 * Form field template for text field type.
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 * @param       string          $html           Form field html
	 * @param       object          $field          Field info.
	 * @param       string          $value          Form field default value.
	 * @param       string          $form_type      Form type
	 *
	 * @return      string                          Modified form field html.
	 */
	public function form_input_text($html, $field, $value, $form_type){

		// Check if there is a custom field specific filter.
		if(has_filter("uwp_form_input_text_{$field->htmlvar_name}")){
			$html = apply_filters("uwp_form_input_text_{$field->htmlvar_name}",$html, $field, $value, $form_type);
		}

		// If no html then we run the standard output.
		if(empty($html)) {

			ob_start(); // Start  buffering;

			$type = 'text';
			$step = false;
			//number and float validation $validation_pattern
			if(isset($field->data_type) && $field->data_type == 'INT'){
				$type = 'number';
			} elseif(isset($field->data_type) && $field->data_type == 'FLOAT'){
				$dp = $field->decimal_point;
				switch ($dp) {
					case "1":
						$step = "0.1";
						break;
					case "2":
						$step = "0.01";
						break;
					case "3":
						$step = "0.001";
						break;
					case "4":
						$step = "0.0001";
						break;
					case "5":
						$step = "0.00001";
						break;
					case "6":
						$step = "0.000001";
						break;
					case "7":
						$step = "0.0000001";
						break;
					case "8":
						$step = "0.00000001";
						break;
					case "9":
						$step = "0.000000001";
						break;
					case "10":
						$step = "0.0000000001";
						break;
					default:
						$step = "0.01";
						break;
				}
				$type = 'number';
			}


			$site_title = uwp_get_form_label($field);
			$manual_label = apply_filters('uwp_login_username_label_manual', true);
			if ($manual_label
			    && isset($field->form_type)
			    && $field->form_type == 'login'
			    && $field->htmlvar_name == 'username') {
				$site_title = __("Username or Email", 'userswp');
			}

			$design_style = uwp_get_option("design_style","bootstrap");
			$bs_form_group = $design_style ? "form-group" : "";
			$bs_sr_only = $design_style ? "sr-only" : "";
			$bs_form_control = $design_style ? "form-control" : "";

			// bootstrap
			if( $design_style ){
				echo aui()->input(array(
					'type'  =>  $type,
					'id'    =>  $field->htmlvar_name,
					'name'    =>  $field->htmlvar_name,
					'placeholder'   => uwp_get_field_placeholder($field),
					'title'   => $site_title,
					'value' =>  $value,
					'required'  => $field->is_required,
					'validation_text' => __($field->required_msg, 'userswp'),
					'help_text' => uwp_get_field_description($field),
					'label' => is_admin() ? '' : $site_title,
					'step' => $step
				));
			}else{
				?>
                <div id="<?php echo $field->htmlvar_name;?>_row" class="<?php if ($field->is_required) echo 'required_field';?> uwp_form_<?php echo $field->field_type; ?>_row uwp_clear <?php echo esc_attr($bs_form_group);?>">
					<?php

					if (!is_admin()) { ?>
                        <label class="<?php echo esc_attr($bs_sr_only);?>">
							<?php echo (trim($site_title)) ? $site_title : '&nbsp;'; ?>
							<?php if ($field->is_required) echo '<span>*</span>';?>
                        </label>
					<?php }
					?>

                    <input name="<?php echo $field->htmlvar_name;?>" class="<?php echo $field->css_class; ?> uwp_textfield <?php echo esc_attr($bs_form_control);?>"
                           id="<?php echo $field->htmlvar_name;?>"
                           placeholder="<?php echo uwp_get_field_placeholder($field); ?>"
                           value="<?php echo esc_attr(stripslashes($value));?>"
                           title="<?php echo $site_title; ?>"
                           oninvalid="this.setCustomValidity('<?php _e($field->required_msg, 'userswp'); ?>')"
                           oninput="setCustomValidity('')"
						<?php if ($field->is_required == 1) { echo 'required="required"'; } ?>
						<?php if ($field->for_admin_use == 1) { echo 'readonly="readonly"'; } ?>
                           type="<?php echo $type; ?>"
						<?php if ($step) { echo 'step="'.$step.'"'; } ?>
                    />
                    <span class="uwp_message_note"><?php echo uwp_get_field_description($field);?></span>
					<?php if ($field->is_required) { ?>
                        <span class="uwp_message_error invalid-feedback"><?php _e($field->required_msg, 'userswp'); ?></span>
					<?php } ?>
                </div>


				<?php
			}
			$html = ob_get_clean();
		}

		return $html;
	}

	/**
	 * Form field template for textarea field type.
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 * @param       string          $html           Form field html
	 * @param       object          $field          Field info.
	 * @param       string          $value          Form field default value.
	 * @param       string          $form_type      Form type
	 *
	 * @return      string                          Modified form field html.
	 */
	public function form_input_textarea($html, $field, $value, $form_type){

		// Check if there is a field specific filter.
		if(has_filter("uwp_form_input_textarea_{$field->htmlvar_name}")){
			$html = apply_filters("uwp_form_input_textarea_{$field->htmlvar_name}", $html, $field, $value, $form_type);
		}

		// If no html then we run the standard output.
		if(empty($html)) {

			$design_style = uwp_get_option("design_style","bootstrap");
			$bs_form_group = $design_style ? "form-group" : "";
			$bs_sr_only = $design_style ? "sr-only" : "";
			$bs_form_control = $design_style ? "form-control" : "";
			$site_title = uwp_get_form_label($field);
			ob_start(); // Start  buffering;

			// bootstrap
			if( $design_style ){
				echo aui()->textarea(array(
					'id'    =>  $field->htmlvar_name,
					'name'    =>  $field->htmlvar_name,
					'placeholder'   => uwp_get_field_placeholder($field),
					'title'   => $site_title,
					'value' =>  stripslashes($value),
					'required'  => $field->is_required,
					'validation_text' => __($field->required_msg, 'userswp'),
					'help_text' => uwp_get_field_description($field),
					'label' => is_admin() ? '' : $site_title,
					'rows' => '4'
				));
			}else{
				?>

                <div id="<?php echo $field->htmlvar_name;?>_row"
                     class="<?php if ($field->is_required) echo 'required_field';?> uwp_form_<?php echo $field->field_type; ?>_row uwp_clear <?php echo esc_attr($bs_form_group);?>">

					<?php

					if (!is_admin()) { ?>
                        <label class="<?php echo esc_attr($bs_sr_only);?>">
							<?php echo (trim($site_title)) ? $site_title : '&nbsp;'; ?>
							<?php if ($field->is_required) echo '<span>*</span>';?>
                        </label>
					<?php } ?>

                    <textarea name="<?php echo $field->htmlvar_name; ?>"
                              class="<?php echo $field->css_class; ?> <?php echo esc_attr($bs_form_control);?>"
                              placeholder="<?php echo uwp_get_field_placeholder($field); ?>"
                              title="<?php echo $site_title; ?>"
                              oninvalid="this.setCustomValidity('<?php _e($field->required_msg, 'userswp'); ?>')"
                              oninput="setCustomValidity('')"
						<?php if ($field->is_required == 1) { echo 'required="required"'; } ?>
                              type="<?php echo $field->field_type; ?>"
                              rows="4"><?php echo stripslashes($value); ?></textarea>
                    <span class="uwp_message_note"><?php echo uwp_get_field_description($field);?></span>
					<?php if ($field->is_required) { ?>
                        <span class="uwp_message_error invalid-feedback"><?php _e($field->required_msg, 'userswp'); ?></span>
					<?php } ?>
                </div>

				<?php
			}
			$html = ob_get_clean();
		}

		return $html;
	}

	public function form_input_editor($html, $field, $value, $form_type) {

		// Check if there is a field specific filter.
		if(has_filter("uwp_form_input_editor_{$field->htmlvar_name}")){
			$html = apply_filters("uwp_form_input_editor_{$field->htmlvar_name}", $html, $field, $value, $form_type);
		}

		if(empty($html)) {

			ob_start();

			$design_style = uwp_get_option("design_style","bootstrap");
			$bs_form_group = $design_style ? "form-group" : "";
			$bs_sr_only = $design_style ? "sr-only" : "";
			$site_title = uwp_get_form_label($field);

			$content = stripslashes($value);
			$editor_id = $field->htmlvar_name;
			$args = array(
				'textarea_rows' => 5,
				'media_buttons' => false,
				'quicktags'     => false,
			);

			// bootstrap
			if( $design_style ){
				echo aui()->textarea(array(
					'id'    =>  $field->htmlvar_name,
					'name'    =>  $field->htmlvar_name,
					'placeholder'   => uwp_get_field_placeholder($field),
					'title'   => $site_title,
					'value' =>  stripslashes($value),
					'required'  => $field->is_required,
					'validation_text' => __($field->required_msg, 'userswp'),
					'help_text' => uwp_get_field_description($field),
					'label' => is_admin() ? '' : $site_title,
					'rows' => 5,
					'wysiwyg' => true
				));
			}else {
				?>
                <div id="<?php echo $field->htmlvar_name; ?>_row"
                     class="<?php if ( $field->is_required ) {
					     echo 'required_field';
				     } ?> uwp_form_<?php echo $field->field_type; ?>_row uwp_clear <?php echo esc_attr( $bs_form_group ); ?>">

					<?php

					if ( ! is_admin() ) { ?>
                        <label class="<?php echo esc_attr( $bs_sr_only ); ?>">
							<?php echo ( trim( $site_title ) ) ? $site_title : '&nbsp;'; ?>
							<?php if ( $field->is_required ) {
								echo '<span>*</span>';
							} ?>
                        </label>
					<?php } ?>

					<?php wp_editor( $content, $editor_id, $args ); ?>

                    <span class="uwp_message_note"><?php echo uwp_get_field_description($field); ?></span>
					<?php if ( $field->is_required ) { ?>
                        <span class="uwp_message_error invalid-feedback"><?php _e( $field->required_msg, 'userswp' ); ?></span>
					<?php } ?>
                </div>
				<?php
			}
			$html = ob_get_clean();
		}

		return $html;
	}

	/**
	 * Form field template for fieldset field type.
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 * @param       string          $html           Form field html
	 * @param       object          $field          Field info.
	 * @param       string          $value          Form field default value.
	 * @param       string          $form_type      Form type
	 *
	 * @return      string                          Modified form field html.
	 */
	public function form_input_fieldset($html, $field, $value, $form_type) {
		// Check if there is a custom field specific filter.
		if(has_filter("uwp_form_input_fieldset_{$field->htmlvar_name}")){
			$html = apply_filters("uwp_form_input_fieldset_{$field->htmlvar_name}",$html, $field, $value, $form_type);
		}

		// If no html then we run the standard output.
		if(empty($html)) {

			ob_start(); // Start  buffering;
			$site_title = uwp_get_form_label($field);
			?>
            <h3 class="uwp_input_fieldset <?php echo $field->css_class; ?>">
				<?php echo $site_title; ?>
				<?php if ( $field->help_text != '' ) {
					echo '<small>( ' . $field->help_text . ' )</small>';
				} ?></h3>
			<?php
			$html = ob_get_clean();
		}

		return $html;
	}

	/**
	 * Form field template for url field type.
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 * @param       string          $html           Form field html
	 * @param       object          $field          Field info.
	 * @param       string          $value          Form field default value.
	 * @param       string          $form_type      Form type
	 *
	 * @return      string                          Modified form field html.
	 */
	public function form_input_url($html, $field, $value, $form_type){


		// Check if there is a custom field specific filter.
		if(has_filter("uwp_form_input_url_{$field->htmlvar_name}")){
			$html = apply_filters("uwp_form_input_url_{$field->htmlvar_name}",$html, $field, $value, $form_type);
		}

		// If no html then we run the standard output.
		if(empty($html)) {

			$design_style = uwp_get_option("design_style","bootstrap");
			$bs_form_group = $design_style ? "form-group" : "";
			$bs_sr_only = $design_style ? "sr-only" : "";
			$bs_form_control = $design_style ? "form-control" : "";

			ob_start(); // Start  buffering;
			$site_title = uwp_get_form_label( $field );

			// bootstrap
			if( $design_style ){
				echo aui()->input(array(
					'type'  =>  'url',
					'id'    =>  $field->htmlvar_name,
					'name'    =>  $field->htmlvar_name,
					'placeholder'   => uwp_get_field_placeholder($field),
					'title'   => $site_title,
					'value' =>  $value,
					'required'  => $field->is_required,
					'validation_text' => __( 'Please enter a valid URL including http://', 'userswp' ),
					'help_text' => uwp_get_field_description($field),
					'label' => is_admin() ? '' : $site_title
				));
			}else {


				?>
                <div id="<?php echo $field->htmlvar_name; ?>_row"
                     class="<?php if ( $field->is_required ) {
					     echo 'required_field';
				     } ?> uwp_form_<?php echo $field->field_type; ?>_row uwp_clear <?php echo esc_attr( $bs_form_group ); ?>">

					<?php
					if ( ! is_admin() ) { ?>
                        <label class="<?php echo esc_attr( $bs_sr_only ); ?>">
							<?php echo ( trim( $site_title ) ) ? $site_title : '&nbsp;'; ?>
							<?php if ( $field->is_required ) {
								echo '<span>*</span>';
							} ?>
                        </label>
					<?php } ?>

                    <input name="<?php echo $field->htmlvar_name; ?>"
                           class="<?php //echo $field->css_class;
					       ?> uwp_textfield <?php echo esc_attr( $bs_form_control ); ?>"
                           id="<?php echo $field->htmlvar_name; ?>"
                           placeholder="<?php echo uwp_get_field_placeholder($field); ?>"
                           value="<?php echo esc_attr( stripslashes( $value ) ); ?>"
                           title="<?php echo $site_title; ?>"
						<?php if ( $field->is_required == 1 ) {
							echo 'required="required"';
						} ?>
                           type="url"
                           oninvalid="setCustomValidity('<?php _e( 'Please enter a valid URL including http://', 'userswp' ); ?>')"
                           onchange="try{setCustomValidity('')}catch(e){}"
                    />
                    <span class="uwp_message_note"><?php echo uwp_get_field_description($field); ?></span>
					<?php if ( $field->is_required ) { ?>
                        <span class="uwp_message_error invalid-feedback"><?php _e( $field->required_msg, 'userswp' ); ?></span>
					<?php } ?>
                </div>

				<?php
			}

			$html = ob_get_clean();
		}

		return $html;
	}

	/**
	 * Form field template for email field type.
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 * @param       string          $html           Form field html
	 * @param       object          $field          Field info.
	 * @param       string          $value          Form field default value.
	 * @param       string          $form_type      Form type
	 *
	 * @return      string                          Modified form field html.
	 */
	public function form_input_email($html, $field, $value, $form_type){


		// Check if there is a custom field specific filter.
		if(has_filter("uwp_form_input_email_{$field->htmlvar_name}")){
			$html = apply_filters("uwp_form_input_email_{$field->htmlvar_name}",$html, $field, $value, $form_type);
		}

		// If no html then we run the standard output.
		if(empty($html)) {

			$design_style = uwp_get_option("design_style","bootstrap");
			$bs_form_group = $design_style ? "form-group" : "";
			$bs_sr_only = $design_style ? "sr-only" : "";
			$bs_form_control = $design_style ? "form-control" : "";

			ob_start(); // Start  buffering;
			$site_title = uwp_get_form_label( $field );

			if( $design_style ){
				echo aui()->input(array(
					'type'  =>  'email',
					'id'    =>  $field->htmlvar_name,
					'name'    =>  $field->htmlvar_name,
					'placeholder'   => uwp_get_field_placeholder($field),
					'title'   => $site_title,
					'value' =>  $value,
					'required'  => $field->is_required,
					'help_text' => uwp_get_field_description($field),
					'label' => is_admin() ? '' : $site_title
				));
			}else {


				?>
                <div id="<?php echo $field->htmlvar_name; ?>_row"
                     class="<?php if ( $field->is_required ) {
					     echo 'required_field';
				     } ?> uwp_form_<?php echo $field->field_type; ?>_row uwp_clear <?php echo esc_attr( $bs_form_group ); ?>">

					<?php
					if ( ! is_admin() ) { ?>
                        <label class="<?php echo esc_attr( $bs_sr_only ); ?>">
							<?php echo ( trim( $site_title ) ) ? $site_title : '&nbsp;'; ?>
							<?php if ( $field->is_required ) {
								echo '<span>*</span>';
							} ?>
                        </label>
					<?php } ?>

                    <input name="<?php echo $field->htmlvar_name; ?>"
                           class="<?php echo $field->css_class; ?> uwp_textfield <?php echo esc_attr( $bs_form_control ); ?>"
                           id="<?php echo $field->htmlvar_name; ?>"
                           placeholder="<?php echo uwp_get_field_placeholder($field); ?>"
                           value="<?php echo esc_attr( stripslashes( $value ) ); ?>"
                           title="<?php echo $site_title; ?>"
						<?php if ( $field->is_required == 1 ) {
							echo 'required="required"';
						} ?>
                           type="email"
                    />
                    <span class="uwp_message_note"><?php echo uwp_get_field_description($field); ?></span>
					<?php if ( $field->is_required ) { ?>
                        <span class="uwp_message_error invalid-feedback"><?php _e( $field->required_msg, 'userswp' ); ?></span>
					<?php } ?>
                </div>


				<?php
			}
			$html = ob_get_clean();

		}

		if(has_filter("uwp_form_input_email_{$field->htmlvar_name}_after")){
			$html = apply_filters("uwp_form_input_email_{$field->htmlvar_name}_after",$html, $field, $value, $form_type);
		}

		return $html;
	}

	/**
	 * Form field template for password field type.
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 * @param       string          $html           Form field html
	 * @param       object          $field          Field info.
	 * @param       string          $value          Form field default value.
	 * @param       string          $form_type      Form type
	 *
	 * @return      string                          Modified form field html.
	 */
	public function form_input_password($html, $field, $value, $form_type){


		// Check if there is a custom field specific filter.
		if(has_filter("uwp_form_input_password_{$field->htmlvar_name}")){
			$html = apply_filters("uwp_form_input_password_{$field->htmlvar_name}",$html, $field, $value, $form_type);
		}

		// If no html then we run the standard output.
		if(empty($html)) {

			$design_style = uwp_get_option("design_style","bootstrap");
			$bs_form_group = $design_style ? "form-group" : "";
			$bs_sr_only = $design_style ? "sr-only" : "";
			$bs_form_control = $design_style ? "form-control" : "";

			ob_start(); // Start  buffering;
			$site_title = uwp_get_form_label( $field );

			if( $design_style ){
				echo aui()->input(array(
					'type'  =>  'password',
					'id'    =>  $field->htmlvar_name,
					'name'    =>  $field->htmlvar_name,
					'placeholder'   => uwp_get_field_placeholder($field),
					'title'   => $site_title,
					'value' =>  $value,
					'required'  => $field->is_required,
					'help_text' => uwp_get_field_description($field),
					'label' => is_admin() ? '' : $site_title
				));
			}else {
				?>
                <div id="<?php echo $field->htmlvar_name; ?>_row" class="<?php if ( $field->is_required ) {
					echo 'required_field';
				} ?> uwp_form_<?php echo $field->field_type; ?>_row uwp_clear <?php echo esc_attr( $bs_form_group ); ?>">
					<?php
					if ( ! is_admin() ) { ?>
                        <label class="<?php echo esc_attr( $bs_sr_only ); ?>">
							<?php echo ( trim( $site_title ) ) ? $site_title : '&nbsp;'; ?>
							<?php if ( $field->is_required ) {
								echo '<span>*</span>';
							} ?>
                        </label>
					<?php } ?>

                    <input name="<?php echo $field->htmlvar_name; ?>"
                           class="<?php echo $field->css_class; ?> uwp_textfield <?php echo esc_attr( $bs_form_control ); ?>"
                           id="<?php echo $field->htmlvar_name; ?>"
                           placeholder="<?php echo uwp_get_field_placeholder($field); ?>"
                           value="<?php echo esc_attr( stripslashes( $value ) ); ?>"
                           title="<?php echo $site_title; ?>"
						<?php if ( $field->is_required == 1 ) {
							echo 'required="required"';
						} ?>
                           type="password"
                    />
                    <span class="uwp_message_note"><?php echo uwp_get_field_description($field); ?></span>
					<?php if ( $field->is_required ) { ?>
                        <span class="uwp_message_error invalid-feedback"><?php _e( $field->required_msg, 'userswp' ); ?></span>
					<?php } ?>
                </div>


				<?php
			}

			$html = ob_get_clean();
		}

		if(has_filter("uwp_form_input_password_{$field->htmlvar_name}_after")){
			$html = apply_filters("uwp_form_input_password_{$field->htmlvar_name}_after",$html, $field, $value, $form_type);
		}

		return $html;
	}

	/**
	 * Form field template for Phone field.
	 *
	 * @since 1.0.0
	 *
	 * @param string $html Form field html
	 * @param object $field Field info.
	 * @param string $value Form field default value.
	 * @param string $form_type Form type
	 *
	 * @return string $html Modified form field html.
	 */
	public function form_input_phone($html, $field, $value, $form_type){
		if(empty($html)) {
			$design_style = uwp_get_option("design_style","bootstrap");
			$bs_form_group = $design_style ? "form-group" : "";
			$bs_sr_only = $design_style ? "sr-only" : "";
			$bs_form_control = $design_style ? "form-control" : "";
			ob_start(); // Start  buffering;
			$site_title = uwp_get_form_label( $field );

			if( $design_style ){
				echo aui()->input(array(
					'type'  =>  'tel',
					'id'    =>  $field->htmlvar_name,
					'name'    =>  $field->htmlvar_name,
					'placeholder'   => uwp_get_field_placeholder($field),
					'title'   => $site_title,
					'value' =>  $value,
					'required'  => $field->is_required,
					'help_text' => uwp_get_field_description($field),
					'label' => is_admin() ? '' : $site_title
				));
			}else {
				?>
                <div id="<?php echo $field->htmlvar_name; ?>_row"
                     class="<?php if ( $field->is_required ) {
					     echo 'required_field';
				     } ?> uwp_form_row clearfix uwp_clear <?php echo esc_attr( $bs_form_group ); ?>">
					<?php
					if ( ! is_admin() ) { ?>
                        <label class="<?php echo esc_attr( $bs_sr_only ); ?>">
							<?php echo ( trim( $site_title ) ) ? $site_title : '&nbsp;'; ?>
							<?php if ( $field->is_required ) {
								echo '<span>*</span>';
							} ?>
                        </label>
					<?php } ?>
                    <input name="<?php echo $field->htmlvar_name; ?>"
                           class="<?php echo $field->css_class; ?> <?php echo esc_attr( $bs_form_control ); ?>"
                           placeholder="<?php echo uwp_get_field_placeholder($field); ?>"
                           title="<?php echo $site_title; ?>"
						<?php if ( $field->for_admin_use == 1 ) {
							echo 'readonly="readonly"';
						} ?>
						<?php if ( $field->is_required == 1 ) {
							echo 'required="required"';
						} ?>
                           type="tel"
                           value="<?php echo esc_html( $value ); ?>">
                </div>
				<?php
			}
			$html = ob_get_clean();
		}
		return $html;
	}

	public function form_input_register_gdpr( $html, $field, $value, $form_type ) {

		$reg_gdpr = uwp_get_option('register_gdpr_page', false);

		if(!empty($reg_gdpr)) {

			$design_style = uwp_get_option("design_style","bootstrap");
			$bs_form_group = $design_style ? "form-group form-check" : "";
			$bs_sr_only = $design_style ? "form-check-label" : "";
			$bs_form_control = $design_style ? "form-check-input" : "";
			$site_title = uwp_get_form_label($field);
			$field->htmlvar_name = 'register_gdpr';
			$id = wp_doing_ajax() ? $field->htmlvar_name."_ajax" : $field->htmlvar_name;

			$gdpr_page = get_permalink($reg_gdpr);
			$link_start = '<a href="'.$gdpr_page.'" target="_blank">';
			$link_end = '</a>';
			$field_desc = uwp_get_field_description($field, '');
			$field_desc = str_replace('%%link_start%%', $link_start, $field_desc);
			$field_desc = str_replace('%%link_end%%', $link_end, $field_desc);
			$content = $field_desc ? $field_desc : sprintf( __( 'By using this form I agree to the storage and handling of my data by this website. View our %s %s %s.', 'userswp' ), '<a href="'.$gdpr_page.'" target="_blank">', $site_title,'</a>');

			ob_start(); // Start  buffering;

			// bootstrap
			if( $design_style ) { ?>
                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="hidden" name="<?php echo $field->htmlvar_name; ?>" id="<?php echo $id; ?>_hidden" value="0">
                        <input type="checkbox" name="<?php echo $field->htmlvar_name; ?>" id="<?php echo $id; ?>" placeholder="<?php echo $field->htmlvar_name; ?>" title="<?php echo $site_title; ?>" value="1" class="form-control custom-control-input"
                               onchange="if(this.checked){jQuery('#<?php echo $id; ?>_hidden').val('1');} else{ jQuery('#<?php echo $id; ?>_hidden').val('0');}" <?php echo checked($value, 1)?>>
                        <label for="<?php echo $id; ?>" class="custom-control-label"><?php echo $content; ?></label>
                    </div>
                </div>
				<?php
			}else{
				?>
                <div id="<?php echo $field->htmlvar_name;?>_row"
                     class="<?php if ($field->is_required) echo 'required_field';?> uwp_form_<?php echo $field->field_type; ?>_row uwp_clear <?php echo esc_attr($bs_form_group);?>">
                    <label class="<?php echo $bs_sr_only; ?>">
                    <input type="hidden" name="<?php echo $field->htmlvar_name; ?>" value="0" />
                    <input name="<?php echo $field->htmlvar_name; ?>"
                           class="<?php echo $field->css_class; ?> <?php echo esc_attr($bs_form_control);?>"
                           placeholder="<?php echo uwp_get_field_placeholder($field); ?>"
                           title="<?php echo $site_title; ?>"
						<?php if ($value == '1') { echo 'checked="checked"'; } ?>
                           type="<?php echo $field->field_type; ?>"
                           value="1">
                    </label>
					<?php
					echo (trim($content)) ? $content : '&nbsp;';
					?>
                    <span class="uwp_message_note"><?php echo uwp_get_field_description($field);?></span>
					<?php if ($field->is_required) { ?>
                        <span class="uwp_message_error invalid-feedback"><?php _e($field->required_msg, 'userswp'); ?></span>
					<?php } ?>
                </div>

				<?php

			}

			$html = ob_get_clean();

		} else{
			$html = '<input type="hidden" name="register_gdpr" value="-1"/>';
		}

		return $html;
	}

	public function form_input_register_tos( $html, $field, $value, $form_type ) {

		$reg_tos = uwp_get_option('register_terms_page', false);

		if(!empty($reg_tos)) {

			$design_style = uwp_get_option("design_style","bootstrap");
			$bs_form_group = $design_style ? "form-group form-check" : "";
			$bs_sr_only = $design_style ? "form-check-label" : "";
			$bs_form_control = $design_style ? "form-check-input" : "";

			$site_title = uwp_get_form_label($field);
			$terms_page = get_permalink($reg_tos);
			$link_start = '<a href="'.$terms_page.'" target="_blank">';
			$link_end = '</a>';
			$field_desc = uwp_get_field_description($field, '');
			$field_desc = str_replace('%%link_start%%', $link_start, $field_desc);
			$field_desc = str_replace('%%link_end%%', $link_end, $field_desc);
			$field->htmlvar_name = 'register_tos';
			$id = wp_doing_ajax() ? $field->htmlvar_name."_ajax" : $field->htmlvar_name;

			$content = $field_desc ? $field_desc : sprintf( __( 'I accept %s %s %s.', 'userswp' ), '<a href="'.$terms_page.'" target="_blank">',$site_title, '</a>');

			ob_start();

			// bootstrap
			if( $design_style ) { ?>
                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="hidden" name="<?php echo $field->htmlvar_name; ?>" id="<?php echo $id; ?>_hidden" value="0">
                        <input type="checkbox" name="<?php echo $field->htmlvar_name; ?>" id="<?php echo $id; ?>" placeholder="<?php echo $field->htmlvar_name; ?>" title="<?php echo $site_title; ?>" value="1" class="form-control custom-control-input"
                               onchange="if(this.checked){jQuery('#<?php echo $id; ?>_hidden').val('1');} else{ jQuery('#<?php echo $id; ?>_hidden').val('0');}" <?php echo checked($value, 1)?>>
                        <label for="<?php echo $id; ?>" class="custom-control-label"><?php echo $content; ?></label>
                    </div>
                </div>
				<?php
			}else{
				?>
                <div id="<?php echo $field->htmlvar_name;?>_row"
                     class="<?php if ($field->is_required) echo 'required_field';?> uwp_form_<?php echo $field->field_type; ?>_row uwp_clear <?php echo esc_attr($bs_form_group);?>">
                    <label class="<?php echo $bs_sr_only; ?>">
                    <input type="hidden" name="<?php echo $field->htmlvar_name; ?>" value="0" />
                    <input name="<?php echo $field->htmlvar_name; ?>"
                           class="<?php echo $field->css_class; ?> <?php echo esc_attr($bs_form_control);?>"
                           placeholder="<?php echo uwp_get_field_placeholder($field); ?>"
                           title="<?php echo $site_title; ?>"
						<?php if ($value == '1') { echo 'checked="checked"'; } ?>
                           type="<?php echo $field->field_type; ?>"
                           value="1">
                    </label>
					<?php
					echo (trim($content)) ? $content : '&nbsp;';
					?>
                    <span class="uwp_message_note"><?php echo uwp_get_field_description($field);?></span>
					<?php if ($field->is_required) { ?>
                        <span class="uwp_message_error invalid-feedback"><?php _e($field->required_msg, 'userswp'); ?></span>
					<?php } ?>
                </div>

				<?php

			}

			$html = ob_get_clean();

		} else{
			$html = '<input type="hidden" name="register_tos" value="-1"/>';
		}

		return $html;
	}

	/**
	 * Adds enctype tag in form for file fields.
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 * @return      void
	 */
	function add_multipart_to_admin_edit_form() {
		global $wpdb;
		$table_name = uwp_get_table_prefix() . 'uwp_form_fields';
		$fields = $wpdb->get_results("SELECT * FROM " . $table_name . " WHERE form_type = 'account' AND field_type = 'file' AND is_default = '0' ORDER BY sort_order ASC");
		if ($fields) {
			echo 'enctype="multipart/form-data"';
		}
	}

	/**
	 * Handles UsersWP custom field requests from admin.
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 * @param       int         $user_id        User ID.
	 *
	 * @return      void
	 */
	public function update_profile_extra_admin_edit($user_id) {
		global $wpdb;
		$file_obj = new UsersWP_Files();
		$table_name = uwp_get_table_prefix() . 'uwp_form_fields';
		//Normal fields
		$fields = $wpdb->get_results("SELECT * FROM " . $table_name . " WHERE form_type = 'account' AND field_type != 'file' AND field_type != 'fieldset' ORDER BY sort_order ASC");
		if ($fields) {
			$result = uwp_validate_fields($_POST, 'account', $fields);
			if(is_wp_error($result )){
				die($result->get_error_message());
			}
			if (isset($result['display_name']) && !empty($result['display_name'])) {
				$display_name = $result['display_name'];
			} else {
				if (!empty($first_name) || !empty($last_name)) {
					$display_name = $result['first_name'] . ' ' . $result['last_name'];
				} else {
					$user_info = get_userdata($user_id);
					$display_name = $user_info->user_login;
				}
			}
			$result['display_name'] = $display_name;
			if (!is_wp_error($result)) {
				foreach ($fields as $field) {
					$value = isset($result[$field->htmlvar_name]) ? $result[$field->htmlvar_name] : '';
					if ($value == '0' || !empty($value)) {
						uwp_update_usermeta($user_id, $field->htmlvar_name, $value);
					}
				}
			}
		}

		//File fields
		$fields = $wpdb->get_results("SELECT * FROM " . $table_name . " WHERE form_type = 'account' AND field_type = 'file' AND is_default = '0' ORDER BY sort_order ASC");
		if ($fields) {
			$result = $file_obj->validate_uploads($_FILES, 'account', true, $fields);
			if (!is_wp_error($result)) {
				foreach ($fields as $field) {
					$value = $result[$field->htmlvar_name];
					if ($value == '0' || !empty($value)) {
						uwp_update_usermeta($user_id, $field->htmlvar_name, $value);
					}
				}
			}
		}
	}

	/**
	 * Form field template for country field.
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 * @param       string          $html           Form field html
	 * @param       object          $field          Field info.
	 * @param       string          $value          Form field default value.
	 * @param       string          $form_type      Form type
	 *
	 * @return      string                          Modified form field html.
	 */
	public function form_input_select_country($html, $field, $value, $form_type){

		// If no html then we run the standard output.
		if(empty($html)) {

			$design_style = uwp_get_option("design_style","bootstrap");
			$bs_form_group = $design_style ? "form-group m-0" : ""; // country wrapper div added by JS adds marginso we remove ours
			$bs_sr_only = $design_style ? "sr-only" : "";
			$bs_form_control = $design_style ? "form-control" : "";

			ob_start(); // Start  buffering;

			?>
            <div id="<?php echo $field->htmlvar_name;?>_row"
                 class="<?php if ($field->is_required) echo 'required_field';?> uwp_form_row uwp_clear <?php echo esc_attr($bs_form_group);?>">

				<?php
				$site_title = uwp_get_form_label($field);
				if (!is_admin()) { ?>
                    <label class="<?php echo esc_attr($bs_sr_only);?>">
						<?php echo (trim($site_title)) ? $site_title : '&nbsp;'; ?>
						<?php if ($field->is_required) echo '<span>*</span>';?>
                    </label>
				<?php } ?>

				<?php
				// if value empty set the default
				if($value=='' && isset($field->default_value) && $field->default_value){
					$value = $field->default_value;
				}
				$select_country_options = apply_filters('uwp_form_input_select_country',"{defaultCountry: '$value'}",$field, $value, $form_type);
				?>

                <input type="text" class="uwp_textfield <?php echo esc_attr($bs_form_control);?>" title="<?php echo $site_title; ?>" id="<?php echo $field->htmlvar_name;if(wp_doing_ajax()){echo "_ajax";}?>"  />
                <input type="hidden" id="<?php echo $field->htmlvar_name;if(wp_doing_ajax()){echo "_ajax";}?>_code" name="<?php echo $field->htmlvar_name;?>" />

                <script>
                    jQuery(function() {
                        jQuery("#<?php echo $field->htmlvar_name; if(wp_doing_ajax()){echo "_ajax";}?>").countrySelect(<?php echo $select_country_options;?>);
                    });
                </script>


                <span class="uwp_message_note"><?php echo uwp_get_field_description($field);?></span>
				<?php if ($field->is_required) { ?>
                    <span class="uwp_message_error invalid-feedback"><?php _e($field->required_msg, 'userswp'); ?></span>
				<?php } ?>
            </div>

			<?php
			$html = ob_get_clean();
		}

		return $html;
	}

	/**
	 * Prints the username link in "Edit Account" page
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 * @param       string      $type       Page type.
	 *
	 * @return      void
	 */
	public function display_username_in_account($type) {
		if ($type == 'account') {
			$user_id = get_current_user_id();
			$user_info = get_userdata($user_id);
			$display_name = $user_info->user_login;
			$template = new UsersWP_Templates();
			$logout_url = $template->uwp_logout_url();
			?>
            <div class="uwp_account_page_username text-center">
				<?php _e('Hello, ', 'userswp'); ?><a href="<?php echo uwp_build_profile_tab_url($user_id); ?>"> @<?php echo $display_name; ?> </a>
                <a class="uwp-account-logout-link" href="<?php echo $logout_url; ?>">(<?php _e('Logout', 'userswp'); ?>)</a>
            </div>
			<?php
		}
	}


	/**
	 * Adds confirm password field in forms.
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 * @param       string          $html           Form field html
	 * @param       object          $field          Field info.
	 * @param       string          $value          Form field default value.
	 * @param       string          $form_type      Form type
	 *
	 * @return      string                          Modified form field html.
	 */
	public function register_confirm_password_field($html, $field, $value, $form_type) {
		if ($form_type == 'register') {
			//confirm password field
			$extra = array();
			if (isset($field->extra_fields) && $field->extra_fields != '') {
				$extra = unserialize($field->extra_fields);
			}

			$enable_confirm_password_field = isset($extra['confirm_password']) ? $extra['confirm_password'] : '0';
			if ($enable_confirm_password_field == '1') {

				$design_style = uwp_get_option("design_style","bootstrap");
				$bs_form_group = $design_style ? "form-group" : "";
				$bs_sr_only = $design_style ? "sr-only" : "";
				$bs_form_control = $design_style ? "form-control" : "";
				$site_title = __("Confirm Password", 'userswp');
				ob_start(); // Start  buffering;

				if( $design_style ){
					echo aui()->input(array(
						'type'  =>  'password',
						'id'    =>  'confirm_password',
						'name'    =>  'confirm_password',
						'placeholder'   => $site_title,
						'title'   => $site_title,
						'value' =>  $value,
						'required'  => $field->is_required,
						'help_text' => uwp_get_field_description($field),
						'label' => is_admin() ? '' : $site_title
					));
				}else{
					?>
                    <div id="uwp_account_confirm_password_row"
                         class="<?php echo 'required_field';?> uwp_form_password_row uwp_clear <?php echo esc_attr($bs_form_group);?>">

						<?php

						if (!is_admin()) { ?>
                            <label class="<?php echo esc_attr($bs_sr_only);?>">
								<?php echo (trim($site_title)) ? $site_title : '&nbsp;'; ?>
								<?php if ($field->is_required) echo '<span>*</span>';?>
                            </label>
						<?php } ?>

                        <input name="confirm_password"
                               class="uwp_textfield <?php echo esc_attr($bs_form_control);?>"
                               id="uwp_account_confirm_password"
                               placeholder="<?php echo uwp_get_field_placeholder($field); ?>"
                               value=""
                               title="<?php echo $site_title; ?>"
							<?php echo 'required="required"'; ?>
                               type="password"
                        />
                    </div>

					<?php
				}
				$confirm_html = ob_get_clean();
				$html = $html.$confirm_html;
			}
		}
		return $html;
	}

	/**
	 * Adds confirm email field in forms.
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 * @param       string          $html           Form field html
	 * @param       object          $field          Field info.
	 * @param       string          $value          Form field default value.
	 * @param       string          $form_type      Form type
	 *
	 * @return      string                          Modified form field html.
	 */
	public function register_confirm_email_field($html, $field, $value, $form_type) {
		if ($form_type == 'register') {
			//confirm email field
			$extra = array();
			if (isset($field->extra_fields) && $field->extra_fields != '') {
				$extra = unserialize($field->extra_fields);
			}
			$enable_confirm_email_field = isset($extra['confirm_email']) ? $extra['confirm_email'] : '0';
			if ($enable_confirm_email_field == '1') {

				$design_style = uwp_get_option("design_style","bootstrap");
				$bs_form_group = $design_style ? "form-group" : "";
				$bs_sr_only = $design_style ? "sr-only" : "";
				$bs_form_control = $design_style ? "form-control" : "";

				ob_start(); // Start  buffering;
				?>
                <div id="uwp_account_confirm_email_row"
                     class="<?php echo 'required_field';?> uwp_form_email_row uwp_clear <?php echo esc_attr($bs_form_group);?>">

					<?php
					$site_title = __("Confirm Email", 'userswp');
					if (!is_admin()) { ?>
                        <label class="<?php echo esc_attr($bs_sr_only);?>">
							<?php echo (trim($site_title)) ? $site_title : '&nbsp;'; ?>
							<?php if ($field->is_required) echo '<span>*</span>';?>
                        </label>
					<?php } ?>

                    <input name="confirm_email"
                           class="uwp_textfield <?php echo esc_attr($bs_form_control);?>"
                           id="uwp_account_confirm_email"
                           placeholder="<?php echo uwp_get_field_placeholder($field); ?>"
                           value=""
                           title="<?php echo $site_title; ?>"
						<?php echo 'required="required"'; ?>
                           type="email"
                    />
                </div>

				<?php
				$confirm_html = ob_get_clean();
				$html = $html.$confirm_html;
			}
		}
		return $html;
	}


	/**
	 * Handles the privacy form submission.
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 * @return      void
	 */
	public function privacy_submit_handler() {
		if (isset($_POST['uwp_privacy_submit'])) {
			if( ! isset( $_POST['uwp_privacy_nonce'] ) || ! wp_verify_nonce( $_POST['uwp_privacy_nonce'], 'uwp-privacy-nonce' ) ) {
				return;
			}

			// Save fields privacy settings
			$extra_where = "AND is_public='2'";
			$fields = get_account_form_fields($extra_where);
			$fields = apply_filters('uwp_account_privacy_fields', $fields);
			$user_id = get_current_user_id();
			global $wpdb, $uwp_notices;
			$meta_table = get_usermeta_table_prefix() . 'uwp_usermeta';

			$user_meta_info = $wpdb->get_row( $wpdb->prepare( "SELECT user_privacy, tabs_privacy FROM $meta_table WHERE user_id = %d", $user_id ) );

			if ($fields) {

				foreach ($fields as $field) {
					$field_name = $field->htmlvar_name.'_privacy';

					$field_value = strip_tags(esc_sql($_POST[$field_name]));
					$value = '';

					if (!empty($user_meta_info->user_privacy)) {
						$public_fields = explode(',', $user_meta_info->user_privacy);
						if ($field_value == 'no') {
							if (!in_array($field_name, $public_fields)) {
								$public_fields[] = $field_name;
							}
							$value = implode(',', $public_fields);
						} else {
							if (($field_name = array_search($field_name, $public_fields)) !== false) {
								unset($public_fields[$field_name]);
							}
							$value = implode(',', $public_fields);
						}
					} else {

						if ($field_value == 'no') {
							$public_fields = array($field_name);
							$value = implode(',', $public_fields);
						} else {
							// For yes values no need to update since its a public field.
							// We store only the private fields.
						}
					}

					uwp_update_usermeta($user_id, 'user_privacy', $value);
				}
			}

			// Save tabs privacy settings
			$tabs_table_name = uwp_get_table_prefix() . 'uwp_profile_tabs';
			$tabs = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$tabs_table_name." WHERE form_type=%s AND user_decided = 1 ORDER BY sort_order ASC", 'profile-tabs'));

			if( $tabs ){
				$public_fields = maybe_unserialize($user_meta_info->tabs_privacy);
//                print_r( $public_fields );exit;
				$do_tabs_update = false;
				foreach ($tabs as $tab) {
					$field_name = $tab->tab_key . '_tab_privacy';

					if(isset($_POST[$field_name])){
						$do_tabs_update = true;
						$field_value = $_POST[$field_name] == '' ? '' : absint($_POST[$field_name]);

						if($field_value==='' && isset($public_fields[$field_name])){
							unset($public_fields[$field_name]);
						}else{
							$public_fields[$field_name] =  $field_value;
						}
					}

				}

				if($do_tabs_update){
					if (!empty($user_meta_info)) {
						$wpdb->update(
							$meta_table,
							array('tabs_privacy' => maybe_serialize($public_fields)),
							array('user_id' => $user_id),
							array('%s'),
							array('%d')
						);
					} else {
						$wpdb->insert(
							$meta_table,
							array('user_id' => $user_id, 'tabs_privacy' => $public_fields)
						);
					}
				}
			}

			if (isset($_POST['uwp_hide_from_listing']) && 1 == $_POST['uwp_hide_from_listing']) {
				update_user_meta($user_id, 'uwp_hide_from_listing', 1);
			} else {
				update_user_meta($user_id, 'uwp_hide_from_listing', 0);
			}

			$make_profile_private = uwp_can_make_profile_private();
			if ($make_profile_private) {
				$field_name = 'uwp_make_profile_private';
				if (isset($_POST[$field_name])) {
					$value = strip_tags(esc_sql($_POST[$field_name]));
					$user_id = get_current_user_id();
					update_user_meta($user_id, $field_name, $value);
				}
			}

			$message = apply_filters('uwp_privacy_update_success_message', __('Privacy settings updated successfully.', 'userswp'));
			$message = aui()->alert(array(
					'type'=>'success',
					'content'=> $message
				)
			);
			$uwp_notices[] = array('account' => $message);

		}
	}

	/**
	 * Get the ajax login form.
	 *
	 * @since 1.2.0
	 */
	public function ajax_login_form(){

		// add the modal error container
		add_action('uwp_template_display_notices', array($this,'modal_error_container'));

		// get the form
		ob_start();
		uwp_get_template("bootstrap/login.php");
		$form = ob_get_clean();

		// send ajax response
		wp_send_json_success(  $form );
	}

	/**
	 * Get the ajax register form.
	 *
	 * @since 1.2.0
	 */
	public function ajax_register_form(){

		// add the modal error container
		add_action('uwp_template_display_notices', array($this,'modal_error_container'));

		global $wp_scripts;
		if(empty($wp_scripts)){$wp_scripts = wp_scripts();}

		// do we need country code script in ajax?
		$country_field = false;
		$fields = get_register_form_fields();
		if (!empty($fields)) {
			foreach ($fields as $field) {
				if ($field->field_type_key == 'country' || $field->field_type_key == 'uwp_country') {
					$country_field  = true;
				}
			}
		}

		ob_start();

		// maybe add country code JS
		if( $country_field ){
			$country_data = uwp_get_country_data();
			echo "<script>var uwp_country_data = ".json_encode( $country_data )."</script>";
			echo "<script type='text/javascript' src='".USERSWP_PLUGIN_URL . 'assets/js/countrySelect.min.js'."' ></script>";
		}

		// get template
		uwp_get_template("bootstrap/register.php");


		// only show the JS if NOT doing a block render
		if(isset($_REQUEST['action']) && $_REQUEST['action'] != 'super_duper_output_shortcode'){


			// load scripts
			$wp_scripts->do_item( 'zxcvbn-async' );
			$wp_scripts->do_item( 'password-strength-meter' );
			?>
            <script>
                // Password strength indicator script
                jQuery( document ).ready( function( $ ) {

                    // Load the settings like WP does.
                    var first, s;
                    s = document.createElement('script');
                    s.src = _zxcvbnSettings.src;
                    s.type = 'text/javascript';
                    s.async = true;
                    first = document.getElementsByTagName('script')[0];
                    first.parentNode.insertBefore(s, first);

                    // Enable any pass inputs.
                    $( 'body' ).on( 'keyup', 'input[name=password], input[name=confirm_password]',
                        function( event ) {
                            uwp_checkPasswordStrength(
                                $('input[name=password]'),         // First password field
                                $('input[name=confirm_password]'), // Second password field
                                $('#uwp-password-strength'),           // Strength meter
                                $('input[type=submit]'),           // Submit button
                                ['black', 'listed', 'word']        // Blacklisted words
                            );
                        }
                    );
                });
            </script>
			<?php
		}
		$form = ob_get_clean();

		// send ajax response
		wp_send_json_success(  $form );
	}

	/**
	 * Get the ajax forgot password form.
	 *
	 * @since 1.2.0
	 */
	public function ajax_forgot_password_form(){

		// add the modal error container
		add_action('uwp_template_display_notices', array($this,'modal_error_container'));

		// get the form
		ob_start();
		uwp_get_template("bootstrap/forgot.php");
		$form = ob_get_clean();

		// send ajax response
		wp_send_json_success(  $form );
	}

	/**
	 * Output the modal error container.
	 *
	 * @param string $type
	 * @since 1.2.0
	 */
	public function modal_error_container($type = ''){
		echo '<div class="form-group"><div class="modal-error"></div></div>';
	}

	public function get_register_redirect_url($data, $user){
		if(is_int($user)){
			$user = get_userdata($user);
		}

		$redirect_page_id = uwp_get_option('register_redirect_to', '');

		if (isset($_REQUEST['redirect_to']) && !empty($_REQUEST['redirect_to'])) {
			$redirect_to = esc_url_raw($_REQUEST['redirect_to']);
		} elseif (isset($data['redirect_to']) && !empty($data['redirect_to'])) {
			$redirect_to = esc_url_raw($data['redirect_to']);
		} elseif (isset($redirect_page_id) && (int)$redirect_page_id > 0) {
			if (uwp_is_wpml()) {
				$wpml_page_id = uwp_wpml_object_id($redirect_page_id, 'page', true, ICL_LANGUAGE_CODE);
				if (!empty($wpml_page_id)) {
					$redirect_page_id = $wpml_page_id;
				}
			}
			$redirect_to = get_permalink($redirect_page_id);
		} elseif (isset($redirect_page_id) && (int)$redirect_page_id == -2 && uwp_get_option('register_redirect_custom_url' ) ) {
			$redirect_to = uwp_get_option('register_redirect_custom_url' );
		}  else {
			if ( isset($user) && $user->has_cap('manage_options') ) {
				$redirect_to = admin_url();
			} else {
				$redirect_to = home_url('/');
			}

			$redirect_to = apply_filters( 'registration_redirect', $redirect_to );
		}

		return apply_filters('uwp_register_redirect', $redirect_to, $redirect_page_id, $data);

	}

	public function get_login_redirect_url($data, $user){
		if(is_int($user)){
			$user = get_userdata($user);
		}

		$redirect_page_id = uwp_get_option('login_redirect_to', -1);

		if (isset($_REQUEST['redirect_to']) && !empty($_REQUEST['redirect_to'])) {
			$redirect_to = esc_url_raw($_REQUEST['redirect_to']);
		} elseif (isset($data['redirect_to']) && !empty($data['redirect_to'])) {
			$redirect_to = esc_url_raw($data['redirect_to']);
		} elseif (isset($redirect_page_id) && (int)$redirect_page_id > 0) {
			if (uwp_is_wpml()) {
				$wpml_page_id = uwp_wpml_object_id($redirect_page_id, 'page', true, ICL_LANGUAGE_CODE);
				if (!empty($wpml_page_id)) {
					$redirect_page_id = $wpml_page_id;
				}
			}
			$redirect_to = get_permalink($redirect_page_id);
		} elseif (isset($redirect_page_id) && (int)$redirect_page_id == -2 && uwp_get_option('login_redirect_custom_url' ) ) {
			$redirect_to = uwp_get_option('login_redirect_custom_url' );
		}  else {
			if ( isset($user) && $user->has_cap('manage_options') ) {
				$redirect_to = admin_url();
			} else {
				$redirect_to = home_url('/');
			}

			$redirect_to = apply_filters( 'login_redirect', $redirect_to, '', $user );
		}

		return apply_filters('uwp_login_redirect', $redirect_to, $redirect_page_id, $data, $user);

	}
}