<?php
// don't load directly
if ( !defined('ABSPATH') )
    die('-1');

// Load colours
$bg              = uwp_get_option( 'email_background_color', '#f5f5f5' );
$body            = uwp_get_option( 'email_body_background_color', '#fdfdfd' );
$base            = uwp_get_option( 'email_base_color', '#557da2' );
$base_text       = uwp_light_or_dark( $base, '#202020', '#ffffff' );
$text            = uwp_get_option( 'email_text_color', '#505050' );

$bg_darker_10    = uwp_hex_darker( $bg, 10 );
$body_darker_10  = uwp_hex_darker( $body, 10 );
$base_lighter_20 = uwp_hex_lighter( $base, 20 );
$base_lighter_40 = uwp_hex_lighter( $base, 40 );
$text_lighter_20 = uwp_hex_lighter( $text, 20 );

$header_bg       		= uwp_get_option( 'email_header_background_color', '#555555' );
$header_color 			= uwp_get_option( 'email_header_text_color', '#ffffff' );
$header_bg_darker_10 	= uwp_hex_darker( $header_bg, 10 );

$footer_bg      		= uwp_get_option( 'email_footer_background_color', '#666666' );
$footer_color 			= uwp_get_option( 'email_footer_text_color', '#dddddd' );
$footer_bg_darker_10  	= uwp_hex_darker( $footer_bg, 10 );

if ( empty( $body ) ) { $body = 'transparent'; }
if ( empty( $bg_darker_10 ) ) { $bg_darker_10 = 'transparent'; }
if ( empty( $header_bg ) ) { $header_bg = 'transparent'; }
if ( empty( $header_bg_darker_10 ) ) { $header_bg_darker_10 = 'transparent'; }
if ( empty( $footer_bg ) ) { $footer_bg = 'transparent'; }
if ( empty( $footer_bg_darker_10 ) ) { $footer_bg_darker_10 = 'transparent'; }

// !important; is a gmail hack to prevent styles being stripped if it doesn't like something.
?>
#wrapper {
    background-color: <?php echo esc_attr( $bg ); ?>;
    margin: 0;
    -webkit-text-size-adjust: none !important;
    padding: 3%;
    width: 94%;
}
#wrapper > p {
    height: 0;
    margin: 0;
    padding: 0;
}
#wrapper .wrapper-table {
    margin: auto;
    max-width: 900px;
    width: 100%;
}
#template_body {
    background-color: <?php echo esc_attr( $body ); ?>;
    border: 1px solid <?php echo esc_attr( $bg_darker_10 ); ?>;
    border-radius: 3px !important;
	box-shadow: 0 1px 4px rgba(0,0,0,0.1) !important;
}
#template_header {
	background-color: <?php echo esc_attr( $header_bg ); ?>;
	border: 1px solid <?php echo esc_attr( $header_bg_darker_10 ); ?>;
	padding: 15px;
}
#template_header,
#template_header a {
	color: <?php echo esc_attr( $header_color ); ?>;
    font-family: Arial;
}
#template_footer {
	background-color: <?php echo esc_attr( $footer_bg ); ?>;
	border: 1px solid <?php echo esc_attr( $footer_bg_darker_10 ); ?>;
    text-align:center;
    padding: 10px 30px 10px 30px;
}
#template_footer,
#template_footer a {
	color: <?php echo esc_attr( $footer_color ); ?>;
    font-family: Arial;
    font-size:12px;
}
#footer_text > p {
	margin: .5em 0;
}
#template_heading {
    background-color: <?php echo esc_attr( $base ); ?>;
    border-radius: 3px 3px 0 0 !important;
    color: <?php echo esc_attr( $base_text ); ?>;
    border-bottom: 0;
    font-weight: bold;
    line-height: 100%;
    vertical-align: middle;
    font-family: Arial,Helvetica,sans-serif;
}
#template_header_logo {
    width: 100%;
}
#template_header_logo > p {
	margin: 0;
	font-size: 1.5em;
}
#template_heading h1 {
    color: <?php echo esc_attr( $base_text ); ?>;
}
#template_footer td {
    padding: 0;
    -webkit-border-radius: 6px;
    font-size: 14px;
}
#body_content {
    background-color: <?php echo esc_attr( $body ); ?>;
}
#body_content table td {
    padding: 27px;
}
#body_content table td td {
    padding: 10px;
}
#body_content table td th {
    padding: 10px;
}
#body_content p {
    margin: 0 0 16px;
}
#body_content_inner {
    color: <?php echo esc_attr( $text_lighter_20 ); ?>;
    font-family: Arial,Helvetica,sans-serif;
    font-size: 14px;
    line-height: 150%;
    text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>;
}
.td {
    color: <?php echo esc_attr( $text_lighter_20 ); ?>;
    border: 1px solid <?php echo esc_attr( $body_darker_10 ); ?>;
}
.text {
    color: <?php echo esc_attr( $text ); ?>;
    font-family: Arial,Helvetica,sans-serif;
}
.link {
    color: <?php echo esc_attr( $base ); ?>;
}
#header_wrapper {
    padding: 22px 24px;
    display: block;
}
h1 {
    color: <?php echo esc_attr( $base ); ?>;
    font-family: Arial,Helvetica,sans-serif;
    font-size: 30px;
    font-weight: 300;
    line-height: 150%;
    margin: 0;
    text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>;
    text-shadow: 0 1px 0 <?php echo esc_attr( $base_lighter_20 ); ?>;
    -webkit-font-smoothing: antialiased;
}
h2 {
    color: <?php echo esc_attr( $base ); ?>;
    display: block;
    font-family: Arial,Helvetica,sans-serif;
    font-size: 18px;
    font-weight: bold;
    line-height: 130%;
    margin: 16px 0 8px;
    text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>;
}
h3 {
    color: <?php echo esc_attr( $base ); ?>;
    display: block;
    font-family: Arial,Helvetica,sans-serif;
    font-size: 16px;
    font-weight: bold;
    line-height: 130%;
    margin: 16px 0 8px;
    text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>;
}
a {
    color: <?php echo esc_attr( $base ); ?>;
    font-weight: normal;
    text-decoration: underline;
}
img {
    border: none;
    display: inline;
    font-size: 14px;
    font-weight: bold;
    height: auto;
    line-height: 100%;
    outline: none;
    text-decoration: none;
    text-transform: capitalize;
	vertical-align: middle;
}
.table-bordered {
    border: 1px solid <?php echo esc_attr( $body_darker_10 ); ?>;
    border-collapse: collapse;
    border-spacing: 0;
    width: 100%;
}
.table-bordered th,
.table-bordered td {
    border: 1px solid <?php echo esc_attr( $body_darker_10 ); ?>;
    color: <?php echo esc_attr( $text_lighter_20 ); ?>;
    font-size: 14px;
}
.small {
    font-size: 85%;
}
.bold {
    font-weight: bold;
}
.normal {
    font-weight: normal;
}
.text-left {
  text-align: left;
}
.text-right {
  text-align: right;
}
.text-center {
  text-align: center;
}
.text-justify {
  text-align: justify;
}
.text-nowrap {
  white-space: nowrap;
}
.text-lowercase {
  text-transform: lowercase;
}
.text-uppercase {
  text-transform: uppercase;
}
.text-capitalize {
  text-transform: capitalize;
}
.btn {
  display: inline-block;
  padding: 0.2rem .6rem;
  font-size: 95%;
  font-weight: normal;
  line-height: 1.5;
  text-align: center;
  white-space: nowrap;
  vertical-align: middle;
  cursor: pointer;
  -webkit-user-select: none;
     -moz-user-select: none;
      -ms-user-select: none;
          user-select: none;
  border: 1px solid transparent;
  border-radius: .25rem;
  text-decoration: none;
}
.btn-default {
    color: <?php echo esc_attr( $base_text ); ?>;
    background-color: <?php echo esc_attr( $base ); ?>;
    border-color: <?php echo esc_attr( $base ); ?>;
}
.btn-primary {
  color: #fff;
  background-color: #0275d8;
  border-color: #0275d8;
}
.btn-success {
  color: #fff;
  background-color: #5cb85c;
  border-color: #5cb85c;
}