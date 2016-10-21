<?php do_action('uwp_template_before', 'profile'); ?>
<?php
$author_id = get_current_user_id();
//$author_id = get_query_var( 'author' );
$user = get_user_by('id', $author_id);
$current_url = home_url('/profile/admin'); //todo:fix this
?>
<div class="uwp-content-wrap">
    <div class="uwp-profile-header">
        <div class="uwp-profile-header-img"></div>
        <div class="uwp-profile-avatar"><?php echo get_avatar($user->user_email, 128); ?></div>
    </div>

    <div class="uwp-profile-main">
        <div class="uwp-profile">
            <div class="uwp-profile-name">
                <h2><?php echo $user->display_name; ?></h2>
            </div>
            <?php
            $bio = get_user_meta($author_id, 'uwp_account_bio', true);
            if ($bio) {
                ?>
                <div class="uwp-profile-bio">
                    <?php echo $bio; ?>
                </div>
                <?php
            }
            ?>

            <?php
            $social = array(
                'facebook',
                'twitter',
                'youtube'
            );
//            $social[] = get_user_meta($author_id, 'uwp_account_social_facebook', true);
//            $social[] = get_user_meta($author_id, 'uwp_account_social_twitter', true);
//            $social[] = get_user_meta($author_id, 'uwp_account_social_googleplus', true);
//            $social[] = get_user_meta($author_id, 'uwp_account_social_linkedin', true);
//            $social[] = get_user_meta($author_id, 'uwp_account_social_youtube', true);
            ?>
            <div class="uwp-profile-social">
                <ul class="uwp-profile-social-ul">
                <?php
                foreach ($social as $value) {
                    $link = get_user_meta($author_id, 'uwp_account_social_'.$value, true);
                    echo '<li><a href="'.$link.'"><i class="fa fa-'.$value.'"></i></a></li>';
                }
                ?>
                </ul>
            </div>
        </div>
        <div class="uwp-profile-content">
            <div class="uwp-profile-nav">
                <ul>
                    <li class="uwp-profile-posts active">
                        <a href="#">
                            <span class="uwp-profile-post-label ">Posts</span>
                            <span class="uwp-profile-post-count"><?php echo uwp_post_count($author_id); ?></span>
                        </a>
                    </li>
                    <li class="uwp-profile-comments">
                        <a href="#">
                            <span class="uwp-profile-comment-label">Comments</span>
                            <span class="uwp-profile-comment-count"><?php echo uwp_comment_count($author_id); ?></span>
                        </a>
                    </li>
                </ul>
                <div class="uwp-edit-account"><a href="#" title="Edit Account"><i class="fa fa-gear"></i></a></div>
            </div>
            <div class="uwp-profile-entries">
                <h3><?php echo $user->display_name; ?>’s Posts</h3>

                <div class="uwp-profile-item-block ">
                    <?php
                    $paged = ( get_query_var( 'paged' ) ) ? absint( get_query_var( 'paged' ) ) : 1;

                    $args = array(
                        'post_type' => 'post',
                        'post_status' => 'publish',
                        'posts_per_page' => 10,
                        'author' => $author_id,
                        'paged' => $paged,
                    );
                    // The Query
                    $the_query = new WP_Query($args);

                    // The Loop
                    if ($the_query->have_posts()) {
                        echo '<ul class="uwp-profile-item-ul">';
                        while ($the_query->have_posts()) {
                            $the_query->the_post();
                            ?>
                            <li class="uwp-profile-item-li uwp-profile-item-clearfix">
                                <a class="uwp-profile-item-img" href="<?php echo get_the_permalink(); ?>">
                                    <?php
                                    if ( has_post_thumbnail() ) {
                                        $thumb_url = get_the_post_thumbnail_url(get_the_ID(), array(80, 80));
                                    } else {
                                        $thumb_url = "http://dropct.com/wp-content/uploads/2016/09/about-us-80x80.jpg";
                                    }
                                    ?>
                                    <img class="uwp-profile-item-alignleft uwp-profile-item-thumb" src="<?php echo $thumb_url; ?>">
                                </a>

                                <h3 class="uwp-profile-item-title">
                                    <a href="<?php echo get_the_permalink(); ?>"><?php echo get_the_title(); ?></a>
                                </h3>
                                <time class="uwp-profile-item-time published" datetime="2016-09-07T21:22:34+00:00">
                                    <?php echo get_the_date(); ?>
                                </time>
                                <div class="uwp-profile-item-summary">
                                    <?php
                                    $excerpt = strip_shortcodes(wp_trim_words( get_the_excerpt(), 15, '...' ));
                                    echo $excerpt;
                                    if ($excerpt) {
                                        ?>
                                        <a href="<?php echo get_the_permalink(); ?>" class="more-link">Read More »</a>
                                        <?php
                                    }
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
                    }
                    ?>
                    <div class="uwp-pagination">
                        <?php
                        $big = 999999999; // need an unlikely integer
                        $translated = __( 'Page', 'mytextdomain' ); // Supply translatable string

                        echo paginate_links( array(
                            'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
                            'format' => '?paged=%#%',
                            'current' => max( 1, get_query_var('paged') ),
                            'total' => $the_query->max_num_pages,
                            'before_page_number' => '<span class="screen-reader-text">'.$translated.' </span>',
                            'type' => 'list'
                        ) );
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php do_action('uwp_template_after', 'profile'); ?>