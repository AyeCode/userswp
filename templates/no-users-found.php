<?php
/**
 * Displayed when no users are found matching the current query.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

echo aui()->alert(array(
	'type'=> 'info',
	'content'=> __( "No users were found matching your selection.", 'userswp' )
));