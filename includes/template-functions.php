<?php

/**
 * get_templates_dir function.
 *
 * The function returns template dir path.
 *
 * @since 1.2.1.3
 *
 * @return string Templates dir path.
 */
function uwp_get_templates_dir() {
	return USERSWP_PATH . 'templates';
}

/**
 * get_templates_url function.
 *
 * The function returns template dir url.
 *
 * @since 1.2.1.3
 *
 * @return string Templates dir url.
 */
function uwp_get_templates_url() {
	return USERSWP_PLUGIN_URL . '/templates';
}

/**
 * get_theme_template_dir_name function.
 *
 * The function returns theme template dir name.
 *
 * @since 1.2.1.3
 *
 * @return string Theme template dir name.
 */
function uwp_get_theme_template_dir_name() {
	return untrailingslashit( apply_filters( 'uwp_templates_dir', 'userswp' ) );
}

/*
 * Function to include the template file.
 *
 * @param string $type Template type.
 * @param string $template_path Template path. (default: '').
 *
 * @deprecated 1.2.1.3 Use uwp_get_template()
 *
 */
function uwp_locate_template($type, $template_path = "" ){

	if(!$template_path){
		$template_path = uwp_get_templates_dir();
	}

	$template = locate_template(array("userswp/".$type.".php"));
	if (!$template) {
		$template = untrailingslashit( $template_path ) . '/' .$type.'.php';
	}

	$template = apply_filters('uwp_template_'.$type, $template, $template_path);

	if (file_exists($template)) {
		include($template);
	}

}

/**
 * Function to display no user found message from template.
 */
function uwp_no_users_found($args = array()){
	$design_style = uwp_get_option("design_style",'bootstrap');
	$template = $design_style ? $design_style."/no-users-found.php" : "no-users-found.php";
	uwp_get_template( $template, $args );
}

/**
 * Get other templates passing attributes and including the file.
 *
 * @param string $template_name Template name.
 * @param array  $args          Arguments. (default: array).
 * @param string $template_path Template path. (default: '').
 * @param string $default_path  Default path. (default: '').
 */
function uwp_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
	if ( ! empty( $args ) && is_array( $args ) ) {
		extract( $args );
	}

	$located = UsersWP_Templates::locate_template( $template_name, $template_path, $default_path );

	if ( ! file_exists( $located ) ) {
		uwp_doing_it_wrong( __FUNCTION__, sprintf( __( '%s does not exist.', 'userswp' ), '<code>' . $located . '</code>' ), '2.1' );

		return;
	}

	// Allow 3rd party plugin filter template file from their plugin.
	$located = apply_filters( 'uwp_get_template', $located, $template_name, $args, $template_path, $default_path );

	do_action( 'uwp_before_template_part', $template_name, $template_path, $located, $args );

	include( $located );

	do_action( 'uwp_after_template_part', $template_name, $template_path, $located, $args );
}

/**
 * Like uwp_get_template, but returns the HTML instead of outputting.
 *
 * @see uwp_get_template
 * @since 1.2.1.3
 * @param string $template_name Template name.
 * @param array  $args          Arguments. (default: array).
 * @param string $template_path Template path. (default: '').
 * @param string $default_path  Default path. (default: '').
 *
 * @return string
 */
function uwp_get_template_html( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
	ob_start();
	uwp_get_template( $template_name, $args, $template_path, $default_path );
	return ob_get_clean();
}

/**
 * doing_it_wrong function.
 *
 * A function is called when mark something as being incorrectly called.
 *
 * @since 1.2.1.3
 *
 * @param string $function The function that was called.
 * @param string $message A message explaining what has been done incorrectly.
 * @param string $version The version of WordPress where the message was added.
 */
function uwp_doing_it_wrong( $function, $message, $version ) {
	$message .= ' Backtrace: ' . wp_debug_backtrace_summary();

	if ( defined( 'DOING_AJAX' ) ) {
		do_action( 'doing_it_wrong_run', $function, $message, $version );
		uwp_error_log( $function . ' was called incorrectly. ' . $message . '. This message was added in version ' . $version . '.' );
	} else {
		_doing_it_wrong( $function, $message, $version );
	}
}

function uwp_password_strength_inline_js() {
	wp_enqueue_script( 'password-strength-meter' ); // add scripts
	?>
	<script>
        jQuery( document ).ready( function( $ ) {

            $( 'body' ).on( 'keyup', 'input[name=password], input[name=confirm_password]',
                function( event ) {
                    uwp_checkPasswordStrength(
                        $('input[name=password]'),
                        $('input[name=confirm_password]'),
                        $('#uwp-password-strength'),
                        $('input[type=submit]'),
                        ['black', 'listed', 'word']
                    );
                }
            );
        });
	</script>
	<?php
}