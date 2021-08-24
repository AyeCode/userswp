<?php
/**
 * Mails related functions
 *
 * All UsersWP related mails are sent via this class.
 *
 * @since      1.0.0
 * @author     GeoDirectory Team <info@wpgeodirectory.com>
 */
class UsersWP_Mails {

	/**
	 * Constructor.
	 */
	public function __construct() {

		add_action( 'uwp_email_header', array( $this, 'email_header' ), 10, 5 );
		add_action( 'uwp_email_footer', array( $this, 'email_footer' ), 10, 4 );
		add_action( 'wp_new_user_notification_email', array( $this, 'wp_new_user_notification_email' ), 10, 2 );
		add_action( 'wp_new_user_notification_email_admin', array( $this, 'wp_new_user_notification_email_admin' ), 10, 2 );

	}

	/**
	 * Get the email logo.
	 *
	 * @since 1.2.1.2
	 *
	 * @return string Logo url.
	 */
	public static function get_email_logo( $size = 'full' ) {
		$attachment_id = uwp_get_option( 'email_logo' );

		$email_logo = '';
		if ( ! empty( $attachment_id ) ) {
			$email_logo = wp_get_attachment_image( $attachment_id, $size );
		}

		return apply_filters( 'uwp_get_email_logo', $email_logo, $attachment_id, $size );
	}

	/**
	 * The default email footer text.
	 *
	 * @since 1.2.1.2
	 *
	 * @return string
	 */
	public static function email_header_text(){
		$header_text = self::get_email_logo();

		if ( empty( $header_text ) ) {
			$header_text = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
		}

		return apply_filters( 'uwp_email_header_text', $header_text );
	}

	/**
	 * Get the email header template.
	 *
	 * @since 1.2.1.2
	 *
	 * @param string $email_heading
	 * @param string $email_name
	 * @param array $email_vars
	 * @param bool $plain_text
	 * @param bool $sent_to_admin
	 */
	public static function email_header( $email_heading = '', $email_name = '', $email_vars = array(), $plain_text = false, $sent_to_admin = false ) {
		if ( ! $plain_text ) {
			$header_text = self::email_header_text();
			$header_text = $header_text ? wpautop( wp_kses_post( wptexturize( $header_text ) ) ) : '';

			uwp_get_template( 'emails/uwp-email-header.php', array(
				'email_heading' => $email_heading,
				'email_name'    => $email_name,
				'email_vars'    => $email_vars,
				'plain_text'    => $plain_text,
				'header_text' 	=> $header_text,
				'sent_to_admin' => $sent_to_admin
			) );
		}
	}

	/**
	 * The default email footer text.
	 *
	 * @since 1.2.1.2
	 * @return string
	 */
	public static function email_footer_text(){
		$footer_text = uwp_get_option( 'email_footer_text' );

		if ( empty( $footer_text ) ) {
			$footer_text = wp_sprintf( __( '%s - Powered by UsersWP', 'userswp' ), get_bloginfo( 'name', 'display' ) );
		}

		return apply_filters( 'uwp_email_footer_text', $footer_text );
	}

	/**
	 * Get the email footer template.
	 *
	 * @since 1.2.1.2
	 *
	 * @param string $email_name
	 * @param array $email_vars
	 * @param bool $plain_text
	 * @param bool $sent_to_admin
	 */
	public static function email_footer( $email_name = '', $email_vars = array(), $plain_text = false, $sent_to_admin = false ) {
		if ( ! $plain_text ) {
			$footer_text = self::email_footer_text();
			$footer_text = $footer_text ? wpautop( wp_kses_post( wptexturize( $footer_text ) ) ) : '';

			uwp_get_template( 'emails/uwp-email-footer.php', array(
				'email_name'    => $email_name,
				'email_vars'    => $email_vars,
				'email_heading'	=> '',
				'plain_text'    => $plain_text,
				'footer_text' 	=> $footer_text,
				'sent_to_admin' => $sent_to_admin
			) );
		}
	}

	/**
	 * Get the email message wraped in the header and footer.
	 *
	 * @since 1.2.1.2
	 *
	 * @param string $message Message.
	 * @param string $email_name Optional. Email name. Default null.
	 * @param array $email_vars Optional. Email vars. Default array.
	 * @param string $email_heading Optional. Email header. Default null.
	 * @param bool $plain_text Optional. Plain text. Default false.
	 * @param bool $sent_to_admin Optional. Send to admin. Default false.
	 *
	 * @return string $message.
	 */
	public static function email_wrap_message( $message, $email_name = '', $email_vars = array(), $email_heading = '', $plain_text = false, $sent_to_admin = false ) {
		// Buffer
		ob_start();

		if ( $plain_text ) {
			echo wp_strip_all_tags( $message );
		} else {
			do_action( 'uwp_email_header', $email_heading, $email_name, $email_vars, $plain_text, $sent_to_admin );

			echo wpautop( wptexturize( $message ) );

			do_action( 'uwp_email_footer', $email_name, $email_vars, $plain_text, $sent_to_admin );
		}

		// Get contents
		$message = ob_get_clean();

		return $message;
	}

	/**
	 * Check if the email is enabled for the email type.
	 *
	 * @since 1.2.1.2
	 *
	 * @param string $email_name Email name.
	 * @param string $default Optional. Default option value. Default null.
	 * @param bool $is_admin Optional. Admin email. Default false.
	 *
	 * @return string
	 */
	public static function is_email_enabled( $email_name, $default = '', $is_admin = false ) {
		if($is_admin){
			$key = $email_name . '_email_admin';
		} else {
			$key = $email_name . '_email';
		}

		switch ( $email_name ) {
			// TODO add some cases
			default:
				$active = uwp_get_option( $key , $default);
				$active = $active === 'yes' || $active == '1' ? true : false;
				break;
		}

		return apply_filters( 'uwp_email_is_enabled', $active, $email_name );
	}

	/**
	 * Get the email subject by type.
	 *
	 * @since 1.2.1.2
	 *
	 * @param string $email_name Optional. Email name. Default null.
	 * @param array $email_vars Optional. Email vars. Default array.
	 * @param bool $is_admin Optional. Admin email. Default false.
	 *
	 * @return string
	 */
	public static function get_subject( $email_name = '', $email_vars = array(), $is_admin = false ) {
		if($is_admin){
			$key = $email_name . '_email_subject_admin';
		} else {
			$key = $email_name . '_email_subject';
		}

		switch ( $email_name ) {
			// TODO some custom options
			default:
				$subject = uwp_get_option( $key );
				break;

		}

		// Get the default text is empty
		if(!$subject && method_exists('UsersWP_Defaults', $key)){
			$subject = UsersWP_Defaults::$key();
		}

		if ( !$subject ) {
			// Used to override subject for specific email type
			$subject = apply_filters( 'uwp_'.$key, $subject, $email_name, $email_vars );
		}

		$subject = self::replace_variables( __( $subject, 'userswp' ), $email_name, $email_vars );

		return apply_filters( 'uwp_email_subject', $subject, $email_name, $email_vars );
	}

	/**
	 * Get the email content by type.
	 *
	 * @since 1.2.1.2
	 *
	 * @param string $email_name Optional. Email name. Default null.
	 * @param array $email_vars Optional. Email vars. Default array.
	 * @param bool $is_admin Optional. Admin email. Default false.
	 *
	 * @return string
	 */
	public static function get_content( $email_name = '', $email_vars = array(), $is_admin = false ) {
		if($is_admin){
			$key = $email_name . '_email_content_admin';
		} else {
			$key = $email_name . '_email_content';
		}

		switch ( $email_name ) {
			// TODO some custom options
			default:
				$content = uwp_get_option( $key );
				break;
		}

		// Get the default text is empty
		if(!$content && method_exists('UsersWP_Defaults', $key)){
			$content = UsersWP_Defaults::$key();
		}

		if ( !$content ) {
			// Used to override content for specific email type
			$content = apply_filters( 'uwp_'.$key, $content, $email_name, $email_vars );
		}

		$content = self::replace_variables( __( $content, 'userswp' ), $email_name, $email_vars );

		return apply_filters( 'uwp_email_content', $content, $email_name, $email_vars );
	}

	/**
	 * Replace variables in the email text.
	 *
	 * @since 1.2.1.2
	 *
	 * @param string $content Content.
	 * @param string $email_name Optional. Email name. Default null.
	 * @param array $email_vars Optional. Email vars. Default array.
	 *
	 * @return mixed
	 */
	public static function replace_variables( $content, $email_name = '', $email_vars = array() ) {
		$site_url        = home_url();
		$blogname        = uwp_get_blogname();
		$email_from_name = self::get_mail_from_name();
		$login_url       = wp_login_url( false ); // 'false' to prevent adding redirect_to to login url.
		$timestamp       = current_time( 'timestamp' );
		$date            = date_i18n( get_option( 'date_format' ), $timestamp );
		$time            = date_i18n( get_option( 'time_format' ), $timestamp );
		$date_time       = $date . ' ' . $time;

		$replace_array = array(
			'[#blogname#]'      => $blogname,
			'[#site_url#]'      => esc_url( $site_url ),
			'[#site_name_url#]' => '<a href="' . esc_url( $site_url ) . '">' . $site_url . '</a>',
			'[#site_link#]'     => '<a href="' . esc_url( $site_url ) . '">' . $blogname . '</a>',
			'[#site_name#]'     => esc_attr( $email_from_name ),
			'[#login_url#]'     => esc_url( $login_url ),
			'[#login_link#]'    => '<a href="' . esc_url( $login_url ) . '">' . __( 'Login', 'userswp' ) . '</a>',
			'[#current_date#]'  => date_i18n( 'Y-m-d H:i:s', $timestamp ),
			'[#date#]'          => $date,
			'[#time#]'          => $time,
			'[#date_time#]'     => $date_time,
			'[#from_name#]'     => esc_attr( self::get_mail_from_name() ),
			'[#from_email#]'    => sanitize_email( self::get_mail_from() ),
		);

		$user_id = ! empty( $email_vars['user_id'] ) ? $email_vars['user_id'] : null;
		if ( !empty( $user_id ) && $user_id > 0 ) {
			$user_data = get_userdata($user_id);
			$profile_link = uwp_build_profile_tab_url($user_id);
			$replace_array = array_merge(
				$replace_array,
				array(
					'[#to_name#]'         => esc_attr( $user_data->display_name ),
					'[#user_login#]'      => esc_attr( $user_data->user_login ),
					'[#user_name#]'       => esc_attr( $user_data->display_name ),
					'[#username#]'        => esc_attr( $user_data->user_login ),
					'[#user_email#]'      => sanitize_email( $user_data->user_email ),
					'[#profile_link#]'    => esc_url( $profile_link ),
				)
			);
		}

		$replace_array = apply_filters( 'uwp_email_format_text', $replace_array, $content, $user_id );

		foreach ( $email_vars as $key => $value ) {
			if ( is_scalar( $value ) ) {
				$replace_array[ '[#' . $key . '#]' ] = $value ;
			}
		}

		$replace_array = apply_filters( 'uwp_email_wild_cards', $replace_array, $content, $email_name, $email_vars );

		foreach ( $replace_array as $key => $value ) {
			$content = str_replace( $key, $value, $content );
		}

		return apply_filters( 'uwp_email_content_replace', $content, $email_name, $email_vars );
	}

	/**
	 * Returns email headers
	 *
	 * @since 1.2.1.2
	 *
	 * @param string $email_name Email name.
	 * @param array $email_vars Optional. Email vars. Default array.
	 * @param string $from_email Optional. From email. Default null.
	 * @param string $from_name Optional. From name. Default null.
	 *
	 * @return string
	 */
	public static function get_headers( $email_name, $email_vars = array(), $from_email = '', $from_name = '' ) {
		$from_email = ! empty( $from_email ) ? $from_email : self::get_mail_from();
		$from_name  = ! empty( $from_name ) ? $from_name : self::get_mail_from_name();
		$reply_to   = ! empty( $email_vars['reply_to'] ) ? $email_vars['reply_to'] : $from_email;

		$headers = "From: " . stripslashes_deep( html_entity_decode( $from_name, ENT_COMPAT, 'UTF-8' ) ) . " <$from_email>\r\n";
		$headers .= "Reply-To: " . $reply_to . "\r\n";
		$headers .= "Content-Type: " . self::get_content_type() . "; charset=\"" . get_option( 'blog_charset' ) . "\"\r\n";

		return apply_filters( 'uwp_email_headers', $headers, $email_name, $email_vars, $from_email, $from_name );
	}

	/**
	 * Get the from name for outgoing emails.
	 *
	 * @since 1.2.1.2
	 *
	 * @return string Site name.
	 */
	public static function get_mail_from_name() {
		$mail_from_name = uwp_get_option( 'email_name' );

		if ( ! $mail_from_name ) {
			$mail_from_name = get_bloginfo('name');
		}

		return apply_filters( 'uwp_get_mail_from_name', stripslashes( $mail_from_name ) );
	}

	/**
	 * Get the from address for outgoing emails.
	 *
	 * @since 1.2.1.2
	 *
	 * @return string The email ID.
	 */
	public static function get_mail_from() {
		$mail_from = uwp_get_option( 'email_address' );

		if ( ! $mail_from ) {
			$sitename = strtolower( $_SERVER['SERVER_NAME'] );
			if ( substr( $sitename, 0, 4 ) == 'www.' ) {
				$sitename = substr( $sitename, 4 );
			}

			$mail_from = 'wordpress@' . $sitename;
		}

		return apply_filters( 'uwp_get_mail_from', $mail_from );
	}

	/**
	 * Get the content type of the email html or plain.
	 *
	 * @since 1.2.1.2
	 *
	 * @param string $content_type Optional. Content type. Default text/html.
	 * @param string $email_type Optional. Email type. Default null.
	 *
	 * @return string $content_type
	 */
	public static function get_content_type( $content_type = 'text/html', $email_type = '' ) {
		if ( empty( $email_type ) ) {
			$email_type = self::get_email_type();
		}

		switch ( $email_type ) {
			case 'plain' :
				$content_type = 'text/plain';
				break;
			case 'multipart' :
				$content_type = 'multipart/alternative';
				break;
		}

		return $content_type;
	}

	/**
	 * Get the email type from settings, html/plain.
	 *
	 * @since 1.2.1.2
	 *
	 * @return string
	 */
	public static function get_email_type() {
		$email_type = uwp_get_option( 'email_type' );

		if ( empty( $email_type ) ) {
			$email_type = 'html';
		}

		return apply_filters( 'uwp_get_email_type', $email_type );
	}

	/**
	 * Get the email attachments per type.
	 *
	 * @since 1.2.1.2
	 *
	 * @param string $email_name Optional. Email name. Default null.
	 * @param array $email_vars Optional. Email vars. Default array.
	 * @param bool $is_admin Optional. Admin email. Default false.
	 *
	 * @return array $attachments
	 */
	public static function get_attachments( $email_name = '', $email_vars = array(), $is_admin = false ) {
		$attachments = array();

		return apply_filters( 'uwp_email_attachments', $attachments, $email_name, $email_vars, $is_admin );
	}

	/**
	 * Style the body of the email content.
	 *
	 * @since 1.2.1.2
	 *
	 * @param string $content Email content.
	 * @param string $email_name Optional. Email name. Default null.
	 * @param array $email_vars Optional. Email vars. Default array.
	 *
	 * @return string $content.
	 */
	public static function style_body( $content, $email_name = '', $email_vars = array() ) {
		// make sure we only inline CSS for html emails
		if ( in_array( self::get_email_type(), array( 'html', 'multipart' ) ) && class_exists( 'DOMDocument' ) ) {
			// include css inliner
			if ( ! class_exists( 'Emogrifier' ) ) {
				include_once( USERSWP_PATH . 'includes/libraries/class-emogrifier.php' );
			}

			ob_start();
			uwp_get_template( 'emails/uwp-email-styles.php', array(
				'email_name' => $email_name,
				'email_vars' => $email_vars
			) );
			$css = apply_filters( 'uwp_email_styles', ob_get_clean() );

			// apply CSS styles inline for picky email clients
			try {
				$emogrifier = new Emogrifier( $content, $css );
				$content    = $emogrifier->emogrify();
			} catch ( Exception $e ) {
				uwp_error_log( $e->getMessage() );
			}
		}

		return $content;
	}

	/**
	 * Function to send email.
	 *
	 * @since 1.2.1.3
	 *
	 * @param string $to To email address.
	 * @param string $subject Email subject.
	 * @param string $message Email message.
	 * @param string $headers Email Headers.
	 * @param array $attachments Optional. Email attachments. Default array.
	 *
	 * @return bool
	 */
	public static function uwp_mail( $to, $subject, $message, $headers, $attachments = array() ) {
		add_filter( 'wp_mail_from', array( __CLASS__, 'get_mail_from' ) );
		add_filter( 'wp_mail_from_name', array( __CLASS__, 'get_mail_from_name' ) );
		add_filter( 'wp_mail_content_type', array( __CLASS__, 'get_content_type' ) );

		$message = self::style_body( $message );
		$message = apply_filters( 'uwp_mail_content', $message);

		$sent = wp_mail( $to, $subject, $message, $headers, $attachments );

		if ( ! $sent ) {
			$log_message = wp_sprintf( __( "Email from UsersWP failed to send.\nTime: %s\nTo: %s\nSubject: %s\nError: %s\n\n", 'userswp' ), date_i18n( 'F j Y H:i:s', current_time( 'timestamp' ) ), ( is_array( $to ) ? implode( ', ', $to ) : $to ), $subject );
			uwp_error_log( $log_message);
		}

		remove_filter( 'wp_mail_from', array( __CLASS__, 'get_mail_from' ) );
		remove_filter( 'wp_mail_from_name', array( __CLASS__, 'get_mail_from_name' ) );
		remove_filter( 'wp_mail_content_type', array( __CLASS__, 'get_content_type' ) );

		return $sent;
	}

	/**
	 * Function to send email for default UWP emails.
	 *
	 * @since 1.2.1.3
	 *
	 * @param string $to To email address.
	 * @param string $email_name Email name.
	 * @param array $email_vars Optional. Email vars. Default null.
	 * @param bool $is_admin Optional. Admin email. Default false.
	 *
	 * @return bool
	 */
	public static function send( $to, $email_name, $email_vars = array(), $is_admin = false ) {

		if ( !self::is_email_enabled( $email_name, '', $is_admin ) ) {
			return false;
		}

		$to = apply_filters( 'uwp_send_email_to', $to, $email_name, $email_vars, $is_admin );
		$subject      = self::get_subject( $email_name, $email_vars, $is_admin );
		$message_body = self::get_content( $email_name, $email_vars, $is_admin );
		$headers      = self::get_headers( $email_name, $email_vars, $is_admin );
		$attachments  = self::get_attachments( $email_name, $email_vars, $is_admin );

		$plain_text = self::get_email_type() != 'html' ? true : false;
		$template   = $plain_text ? 'emails/plain/uwp-email-' . $email_name . '.php' : 'emails/uwp-email-' . $email_name . '.php';

		$content = uwp_get_template_html( $template, array(
			'email_name'    => $email_name,
			'email_vars'    => $email_vars,
			'email_heading'	=> '',
			'sent_to_admin' => $is_admin,
			'plain_text'    => $plain_text,
			'message_body'  => $message_body,
		) );

		do_action( 'uwp_before_'.$email_name.'_email', $email_name, $email_vars );

		$sent = self::uwp_mail($to, $subject, $content, $headers, $attachments);

		do_action( 'uwp_after_'.$email_name.'_email', $email_name, $email_vars );

		return $sent;
	}

	public function wp_new_user_notification_email($wp_new_user_notification_email, $user){

		$email_name = 'wp_new_user_notification';
		$is_admin = false;
		if ( !self::is_email_enabled( $email_name, '', $is_admin ) ) {
			return $wp_new_user_notification_email;
		}

		$key = get_password_reset_key( $user );
		if ( is_wp_error( $key ) ) {
			return $wp_new_user_notification_email;
		}

		$email_vars = array(
			'user_id' => $user->ID
		);

		$to = apply_filters( 'uwp_send_email_to', $user->user_email, $email_name, $email_vars, $is_admin);
		$subject      = self::get_subject( $email_name, $email_vars, $is_admin );
		$subject = !empty($subject) ? $subject : UsersWP_Defaults::wp_new_user_notification_email_subject();
		$headers      = self::get_headers( $email_name, $email_vars, $is_admin );
		$message = self::get_content( $email_name, $email_vars, $is_admin );
		$message = !empty($message) ? $message : UsersWP_Defaults::wp_new_user_notification_email_content();

		$reset_page = uwp_get_page_id( 'reset_page', false );
		if ( $reset_page ) {
			$reset_link = add_query_arg( array(
				'key'   => $key,
				'login' => rawurlencode( $user->user_login ),
			), get_permalink( $reset_page ) );
			$reset_link    = "<a href='" . $reset_link . "' target='_blank'>" . $reset_link . "</a>";
		} else {
			$reset_link = home_url( "reset?key=$key&login=" . rawurlencode( $user->user_login ), 'login' );
			$reset_link = "<a href='" .$reset_link. "' target='_blank'>" . $reset_link . "</a>";
		}

		$message = str_replace( '[#reset_link#]', $reset_link, $message );

		$plain_text = self::get_email_type() != 'html' ? true : false;
		$template   = $plain_text ? 'emails/plain/uwp-email-' . $email_name . '.php' : 'emails/uwp-email-' . $email_name . '.php';

		$content = uwp_get_template_html( $template, array(
			'email_name'    => $email_name,
			'email_vars'    => $email_vars,
			'email_heading'	=> '',
			'sent_to_admin' => $is_admin,
			'plain_text'    => $plain_text,
			'message_body'  => $message,
		) );

		$content = self::style_body( $content );
		$content = apply_filters( 'uwp_mail_content', $content);

		$wp_new_user_notification_email = array(
			'to'      => $to,
			'subject' => $subject,
			'message' => $content,
			'headers' => $headers,
		);

		return $wp_new_user_notification_email;

	}

	public function wp_new_user_notification_email_admin($wp_new_user_notification_email_admin, $user){

		$email_name = 'wp_new_user_notification';
		$is_admin = true;
		if ( !self::is_email_enabled( $email_name, '', $is_admin ) ) {
			return;
		}

		$email_vars = array(
			'user_id' => $user->ID
		);

		$to = apply_filters( 'uwp_send_email_to', get_option( 'admin_email' ), $email_name, $email_vars, $is_admin);
		$subject      = self::get_subject( $email_name, $email_vars, $is_admin );
		$subject = !empty($subject) ? $subject : UsersWP_Defaults::wp_new_user_notification_email_subject_admin();
		$headers      = self::get_headers( $email_name, $email_vars, $is_admin );
		$message = self::get_content( $email_name, $email_vars, $is_admin );
		$message = !empty($message) ? $message : UsersWP_Defaults::wp_new_user_notification_email_content_admin();

		$plain_text = self::get_email_type() != 'html' ? true : false;
		$template   = $plain_text ? 'emails/plain/uwp-email-' . $email_name . '.php' : 'emails/uwp-email-' . $email_name . '.php';

		$content = uwp_get_template_html( $template, array(
			'email_name'    => $email_name,
			'email_vars'    => $email_vars,
			'email_heading'	=> '',
			'sent_to_admin' => $is_admin,
			'plain_text'    => $plain_text,
			'message_body'  => $message,
		) );

		$content = self::style_body( $content );
		$content = apply_filters( 'uwp_mail_content', $content);

		$wp_new_user_notification_email = array(
			'to'      => $to,
			'subject' => $subject,
			'message' => $content,
			'headers' => $headers,
		);

		return $wp_new_user_notification_email;

	}

}

new UsersWP_Mails();