<?php
/**
 * A file for common functions.
 */

/**
 * Return an array of global $pagenow page names that should be used to exclude register_widgets.
 *
 * Used to block the loading of widgets on certain wp-admin pages to save on memory.
 *
 * @return mixed|void
 */
function sd_pagenow_exclude(){
	return apply_filters( 'sd_pagenow_exclude', array(
		'upload.php',
		'edit-comments.php',
		'edit-tags.php',
		'index.php',
		'media-new.php',
		'options-discussion.php',
		'options-writing.php',
		'edit.php',
		'themes.php',
		'users.php',
	) );
}


/**
 * Return an array of widget class names that should be excluded.
 *
 * Used to conditionally load widgets code.
 *
 * @return mixed|void
 */
function sd_widget_exclude(){
	return apply_filters( 'sd_widget_exclude', array() );
}