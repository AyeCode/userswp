<?php
/**
 * Upgrade related functions.
 *
 * @since 1.0.0
 * @package UsersWP
 */



// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Change account fields to not have uwp_account_ prefix.
 */
function uwp_upgrade_1200(){

	// Change the users item page template content and backup the old content
	$page_id = uwp_get_page_id('user_list_item_page');
	$updated = uwp_get_option("user_list_page_updated");
	if($page_id && !$updated){
		$backup_content = get_post_meta($page_id,'uwp_1100_content');
		if(!$backup_content){
			$content = get_post_field('post_content', $page_id);
			if($content){
				update_post_meta($page_id,'uwp_1100_content',$content);
				wp_update_post( array( 'ID' => $page_id, 'post_content'=> '[uwp_users_item]'));
				uwp_update_option("user_list_page_updated","1100");
			}
		}
	}

	//@todo convert 
}