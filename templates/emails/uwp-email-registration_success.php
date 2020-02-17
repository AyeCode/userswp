<?php
// don't load directly
if ( !defined('ABSPATH') )
    die('-1');

do_action( 'uwp_email_header', $email_heading, $email_name, $email_vars, $plain_text, $sent_to_admin );

if ( ! empty( $message_body ) ) {
    echo wpautop( wptexturize( $message_body ) );
}

do_action( 'uwp_email_footer', $email_name, $email_vars, $plain_text, $sent_to_admin );