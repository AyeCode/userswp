<?php
/**
 * Gets the post count for a given post type.
 *
 * @since       1.0.0
 * @package     userswp
 * @param       int         $user_id                   User Id.
 * @param       string      $post_type                 Post Type.
 * @param       string      $extra_post_status         Optional. Extra post status.
 * @return      int                                    Post count.
 */
function uwp_post_count($user_id, $post_type, $extra_post_status = '') {
    global $wpdb;

    $post_status = "";
    if ($user_id == get_current_user_id()) {
        $post_status = ' OR post_status = "draft" OR post_status = "private" ';
    }

    if (!empty($extra_post_status)) {
        $post_status .= $extra_post_status;
    }

    $post_status_where = ' AND ( post_status = "publish" ' . $post_status . ' )';

    if($extra_post_status == 'any'){
        $post_status_where = '';
    }

    $count = $wpdb->get_var('
             SELECT COUNT(ID)
             FROM ' . $wpdb->posts. '
             WHERE post_author = "' . $user_id . '"
             ' . $post_status_where . '
             AND post_type = "' . $post_type . '"'
    );
    return $count;
}

/**
 * Gets the comment count.
 *
 * @since       1.0.0
 * @package     userswp
 * @param       int         $user_id    User ID.
 * @return      int                     Comment count.
 */
function uwp_comment_count($user_id) {
    global $wpdb;

    $count = $wpdb->get_var(
        "SELECT COUNT(comment_ID)
                FROM ".$wpdb->comments."
                WHERE comment_post_ID in (
                SELECT ID 
                FROM ".$wpdb->posts." 
                WHERE post_type = 'post' 
                AND post_status = 'publish')
                AND user_id = " . $user_id . "
                AND comment_approved = '1'
                AND comment_type NOT IN ('pingback', 'trackback' )"
    );
    
    return $count;
}