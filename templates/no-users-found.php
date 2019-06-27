<?php
/**
 * Displayed when no users are found matching the current query.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

uwp_wrap_notice(__( "No users were found matching your selection.", 'userswp' ), 'info');

?>