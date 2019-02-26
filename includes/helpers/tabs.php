<?php
/**
 * Checks the current page is a UsersWP profile tab page.
 *
 * @since       1.0.0
 * @package     userswp
 * @param       string|bool        $tab     Tab slug.
 * @return      bool                        True when success. False when failure.
 */
function is_uwp_profile_tab($tab = false) {
    global $wp_query;
    if (is_uwp_profile_page()) {
        if (isset($wp_query->query_vars['uwp_tab']) && !empty($wp_query->query_vars['uwp_tab'])) {
            if ($wp_query->query_vars['uwp_tab'] == $tab) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    } else {
        return false;
    }
}

/**
 * Checks the current page is a UsersWP profile subtab page.
 *
 * @since       1.0.0
 * @package     userswp
 * @param       string|bool        $subtab     Subtab slug.
 * @return      bool                           True when success. False when failure.
 */
function is_uwp_profile_subtab($subtab = false) {
    global $wp_query;
    if (is_uwp_profile_page()) {
        if (isset($wp_query->query_vars['uwp_subtab']) && !empty($wp_query->query_vars['uwp_subtab'])) {
            if ($wp_query->query_vars['uwp_subtab'] == $subtab) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    } else {
        return false;
    }
}

/**
 * Prints the tab content based on post type.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @param       object          $user       User object.
 * @param       bool            $post_type  Post type.
 * @param       string          $title      Tab title
 * @param       array|bool      $post_ids   Post ids for post__in. Optional
 *
 * @return      void
 */
function uwp_generic_tab_content($user, $post_type = false, $title, $post_ids = false) {
    ?>
    <h3><?php echo $title; ?></h3>
    <div class="uwp-profile-item-block">
        <?php
        $paged = ( get_query_var( 'paged' ) ) ? absint( get_query_var( 'paged' ) ) : 1;

        $args = array(
            'post_status' => 'publish',
            'posts_per_page' => uwp_get_option('profile_no_of_items', 10),
            'author' => $user->ID,
            'paged' => $paged,
        );

        if ($post_type) {
            $args['post_type'] = $post_type;
        }

        if (is_array($post_ids)) {
            if (!empty($post_ids)) {
                $args['post__in'] = $post_ids;
            } else {
                // no posts found
                echo "<p>".__('No '.$title.' Found', 'userswp')."</p>";
                return;
            }
        }
        // The Query
        $the_query = new WP_Query($args);

        // The Loop
        if ($the_query->have_posts()) {
            echo '<ul class="uwp-profile-item-ul">';
            while ($the_query->have_posts()) {
                $the_query->the_post();
                ?>
                <li class="uwp-profile-item-li uwp-profile-item-clearfix">
                    <div class="uwp_generic_thumb_wrap">
                        <a class="uwp-profile-item-img" href="<?php echo get_the_permalink(); ?>">
                            <?php
                            if ( has_post_thumbnail() ) {
                                $thumb_url = get_the_post_thumbnail_url(get_the_ID(), array(80, 80));
                            } else {
                                $thumb_url = USERSWP_PLUGIN_URL."/public/assets/images/no_thumb.png";
                            }
                            ?>
                            <img class="uwp-profile-item-alignleft uwp-profile-item-thumb" src="<?php echo $thumb_url; ?>">
                        </a>
                    </div>

                    <h3 class="uwp-profile-item-title">
                        <a href="<?php echo get_the_permalink(); ?>"><?php echo get_the_title(); ?></a>
                    </h3>
                    <time class="uwp-profile-item-time published" datetime="<?php echo get_the_time('c'); ?>">
                        <?php echo get_the_date(); ?>
                    </time>
                    <div class="uwp-profile-item-summary">
                        <?php
                        do_action('uwp_before_profile_summary', get_the_ID(), $user, $post_type);
                        $excerpt = strip_shortcodes(wp_trim_words( get_the_excerpt(), 15, '...' ));
                        echo $excerpt;
                        do_action('uwp_after_profile_summary', get_the_ID(), $user, $post_type);
                        ?>
                    </div>
                </li>
                <?php
            }
            echo '</ul>';
            /* Restore original Post Data */
            wp_reset_postdata();
        } else {
            // no posts found
            echo "<p>".__('No '.$title.' Found', 'userswp')."</p>";
        }
        do_action('uwp_profile_pagination', $the_query->max_num_pages);
        ?>
    </div>
    <?php
}


/**
 * Adds Privacy tab to available account tabs if privacy enabled.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @return      array       Account Tabs.
 */
function uwp_account_get_available_tabs() {

    $tabs = array();
    $type = 'account';
    if (isset($_GET['type'])) {
        $type = strip_tags(esc_sql($_GET['type']));
    }

    if('account' != $type){
        $tabs['account']  = array(
            'title' => __( 'Edit Account', 'userswp' ),
            'icon' => 'fas fa-user',
        );
    }

    if('notifications' != $type){
        $tabs['notifications']  = array(
            'title' => __( 'Notifications', 'userswp' ),
            'icon' => 'fas fa-bell',
        );
    }

    if('privacy' != $type) {
        $tabs['privacy'] = array(
            'title' => __('Privacy', 'userswp'),
            'icon' => 'fas fa-lock',
        );
    }

    return apply_filters( 'uwp_account_available_tabs', $tabs );
}