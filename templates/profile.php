<?php do_action('uwp_template_before', 'profile'); ?>
<?php
$author_id = get_current_user_id();
//$author_id = get_query_var( 'author' );
$user = get_user_by( 'id', $author_id );
?>
<div class="uwp-content-wrap">
    <div class="uwp-profile-header">
        <div class="uwp-profile-header-img"></div>
        <div class="uwp-profile-avatar"><?php echo get_avatar( $user->user_email, 128 ); ?></div>
    </div>

    <div class="uwp-profile">
        <div class="uwp-profile-name">
            <h2><?php echo $user->display_name; ?></h2>
        </div>
        <div class="uwp-profile-bio">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua… <a href="#">More</a></div>
        <div class="uwp-profile-social"><i class="fa fa-facebook-official"></i> &nbsp; <i class="fa fa-twitter-square"></i> </div>
        <div class="uwp-profile-map"><img src="https://maps.googleapis.com/maps/api/staticmap?center=Brooklyn+Bridge,New+York,NY&amp;zoom=13&amp;size=600x300&amp;maptype=roadmap&#10;&amp;markers=color:blue%7Clabel:S%7C40.702147,-74.015794&amp;markers=color:green%7Clabel:G%7C40.711614,-74.012318&#10;&amp;markers=color:red%7Clabel:C%7C40.718217,-73.998284&#10;&amp;key=AIzaSyABvMvjh07i55MIuOhQcwoL8cagSc5-X0k" style="width:100%;margin:10px 0"></div>
    </div>

    <div class="uwp-profile-content">
        <div class="uwp-profile-nav">
            <ul>
                <li class="uwp-profile-posts active"><a href="#"><span class="uwp-profile-post-label ">POSTS</span><span class="uwp-profile-post-count">13</span></a></li>
                <li class="uwp-profile-comments"><a href="#"><span class="uwp-profile-comment-label">COMMENTS</span><span class="uwp-profile-comment-count"><?php echo uwp_comment_count($author_id); ?></span></a></li>
            </ul>
            <div class="uwp-edit-account"><a href="#" title="Edit Account"><i class="fa fa-gear"></i></a></div>
        </div>
        <div class="clfx"></div>
        <div class="uwp-profile-entries">
            <h3><?php echo $user->display_name; ?>’s Posts</h3>
            <div class="rpwe-block ">
                <ul class="rpwe-ul">
                    <?php
                    $args = array(
                        'post_type' => 'post',
                        'posts_per_page' => 10,
                        'author' => $author_id
                    );
                    // The Query
                    $the_query = new WP_Query( $args );

                    // The Loop
                    if ( $the_query->have_posts() ) {
                        echo '<ul>';
                        while ( $the_query->have_posts() ) {
                            $the_query->the_post();
                            ?>
                            <li class="rpwe-li rpwe-clearfix">
                                <a class="rpwe-img" href="<?php echo get_the_permalink(); ?>">
                                    <img class="rpwe-alignleft rpwe-thumb" src="http://dropct.com/wp-content/uploads/2016/09/about-us-80x80.jpg">
                                </a>
                                <h3 class="rpwe-title">
                                    <a href="<?php echo get_the_permalink(); ?>"><?php echo get_the_title(); ?></a>
                                </h3>
                                <time class="rpwe-time published" datetime="2016-09-07T21:22:34+00:00">September 7, 2016</time>
                                <div class="rpwe-summary">
                                    <?php echo get_the_excerpt(); ?>
                                    <a href="<?php echo get_the_permalink(); ?>" class="more-link">&nbsp;Read More »</a>
                                </div>
                            </li>
                            <?php
                            echo '<li>' . get_the_title() . '</li>';
                        }
                        echo '</ul>';
                        /* Restore original Post Data */
                        wp_reset_postdata();
                    } else {
                        // no posts found
                    }
                    ?>
            <div class="uwp-profile-post-nav">
                <span><a class="active" href="#">1</a></span><span><a href="#">2</a></span><span><a href="#">3</a></span><span><a href="#">4</a></span><span><a href="#">5</a></span>
            </div>
        </div>
    </div>
</div>
<?php do_action('uwp_template_after', 'profile'); ?>