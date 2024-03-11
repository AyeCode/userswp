<?php
/**
 * Displayed when no users are found matching the current query.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
echo aui()->alert(array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	'type'=>'info',
	'content'=> esc_html__( 'No users were found matching your selection.', 'userswp' )
));