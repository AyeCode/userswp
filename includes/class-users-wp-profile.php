<?php
/**
 * Profile Template related functions
 *
 * This class defines all code necessary for Profile template.
 *
 * @link       http://wpgeodirectory.com
 * @since      1.0.0
 *
 * @package    Users_WP
 * @subpackage Users_WP/includes
 */

/**
 * Define the templates functionality.
 *
 * @since      1.0.0
 * @package    Users_WP
 * @subpackage Users_WP/includes
 * @author     GeoDirectory Team <info@wpgeodirectory.com>
 */
class Users_WP_Profile {

    protected $loader;

    public function __construct($loader) {
        $this->loader = $loader;
    }

    public function get_profile_header($user) {
        ?>
        <div class="uwp-profile-header">
            <div class="uwp-profile-header-img"></div>
            <div class="uwp-profile-avatar"><?php echo get_avatar($user->user_email, 128); ?></div>
        </div>
        <?php
    }

    public function get_profile_title($user) {
        ?>
        <div class="uwp-profile-name">
            <h2><?php echo $user->display_name; ?></h2>
        </div>
        <?php
    }

    public function get_profile_bio($user) {
        $bio = uwp_get_usermeta($user->ID, 'uwp_account_bio', '');
        if ($bio) {
            ?>
            <div class="uwp-profile-bio">
                <?php echo $bio; ?>
            </div>
            <?php
        }
    }

    public function get_profile_social($user) {
        $social = array(
            'facebook',
            'twitter',
            'youtube'
        );
        ?>
        <div class="uwp-profile-social">
            <ul class="uwp-profile-social-ul">
                <?php
                foreach ($social as $value) {
                    $link = uwp_get_usermeta($user->ID, 'uwp_account_social_'.$value, '');
                    echo '<li><a href="'.$link.'"><i class="fa fa-'.$value.'"></i></a></li>';
                }
                ?>
            </ul>
        </div>
        <?php
    }

    public function get_profile_tabs($user) {

        $tabs = array();

        $tabs['posts']  = array(
            'title' => __( 'Posts', 'uwp' ),
            'count' => uwp_post_count($user->ID, 'post')
        );
        $tabs['comments'] = array(
            'title' => __( 'Comments', 'uwp' ),
            'count' => uwp_comment_count($user->ID)
        );

        return apply_filters( 'uwp_profile_tabs', $tabs, $user );
    }

    function get_profile_tabs_content($user) {

        $tab = get_query_var('uwp_tab');

        $account_page = uwp_get_option('account_page', false);

        $active_tab = !empty( $tab ) && array_key_exists( $tab, $this->get_profile_tabs($user) ) ? $tab : 'posts';
        ?>
        <div class="uwp-profile-content">
            <div class="uwp-profile-nav">
                <ul class="item-list-tabs-ul">
                    <?php
                    foreach( $this->get_profile_tabs($user) as $tab_id => $tab ) {

                        $tab_url = uwp_build_profile_tab_url($user->ID, $tab_id, false);

                        $active = $active_tab == $tab_id ? ' active' : '';
                        ?>
                        <li id="uwp-profile-<?php echo $tab_id; ?>" class="<?php echo $active; ?>">
                            <a href="<?php echo esc_url( $tab_url ); ?>">
                                <span class="uwp-profile-tab-label uwp-profile-<?php echo $tab_id; ?>-label "><?php echo esc_html( $tab['title'] ); ?></span>
                                <span class="uwp-profile-tab-count uwp-profile-<?php echo $tab_id; ?>-count"><?php echo $tab['count']; ?></span>
                            </a>
                        </li>
                        <?php
                    }
                    ?>
                </ul>
                <?php if ($account_page) { ?>
                    <div class="uwp-edit-account">
                        <a href="<?php echo get_permalink( $account_page ); ?>" title="Edit Account"><i class="fa fa-gear"></i></a>
                    </div>
                <?php } ?>
            </div>

            <div class="uwp-profile-entries">
                <?php
                do_action('uwp_profile_'.$active_tab.'_tab_content', $user);
                ?>
            </div>
        </div>
    <?php }

    function get_profile_pagination($total) {
        ?>
        <div class="uwp-pagination">
            <?php
            $big = 999999999; // need an unlikely integer
            $translated = __( 'Page', 'uwp' ); // Supply translatable string

            echo paginate_links( array(
                'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
                'format' => '?paged=%#%',
                'current' => max( 1, get_query_var('paged') ),
                'total' => $total,
                'before_page_number' => '<span class="screen-reader-text">'.$translated.' </span>',
                'type' => 'list'
            ) );
            ?>
        </div>
        <?php
    }


    function get_profile_posts($user) {
        ?>
        <h3><?php echo $user->display_name; ?>’s Posts</h3>

        <div class="uwp-profile-item-block">
            <?php
            $paged = ( get_query_var( 'paged' ) ) ? absint( get_query_var( 'paged' ) ) : 1;

            $args = array(
                'post_type' => 'post',
                'post_status' => 'publish',
                'posts_per_page' => uwp_get_option('profile_no_of_items', 10),
                'author' => $user->ID,
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
                                $thumb_url = plugins_url()."/userswp/public/assets/images/no_thumb.png";
                            }
                            ?>
                            <img class="uwp-profile-item-alignleft uwp-profile-item-thumb" src="<?php echo $thumb_url; ?>">
                        </a>

                        <h3 class="uwp-profile-item-title">
                            <a href="<?php echo get_the_permalink(); ?>"><?php echo get_the_title(); ?></a>
                        </h3>
                        <time class="uwp-profile-item-time published" datetime="<?php echo get_the_time('c'); ?>">
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
            do_action('uwp_profile_pagination', $the_query->max_num_pages);
            ?>
        </div>
        <?php
    }

    function get_profile_comments($user) {
        ?>
        <h3><?php echo $user->display_name; ?>’s Comments</h3>

        <div class="uwp-profile-item-block">
            <?php
            $paged = ( get_query_var( 'paged' ) ) ? absint( get_query_var( 'paged' ) ) : 1;
            $number = uwp_get_option('profile_no_of_items', 10);
            $offset = ( $paged - 1 ) * $number;

            $total_comments = uwp_comment_count($user->ID);
            $maximum_pages = ceil($total_comments / $number);

            $args = array(
                'number' => $number,
                'offset' => $offset,
                'author_email' => $user->user_email,
                'paged' => $paged,
            );
            // The Query
            $the_query = new WP_Comment_Query();
            $comments = $the_query->query( $args );

            // The Loop
            if ($comments) {
                echo '<ul class="uwp-profile-item-ul">';
                foreach ( $comments as $comment ) {
                    ?>
                    <li class="uwp-profile-item-li uwp-profile-item-clearfix">
                        <a class="uwp-profile-item-img" href="<?php echo get_comment_link($comment->comment_ID); ?>">
                            <?php
                            $args = array(
                                'size' => 80
                            );
                            $thumb_url = get_avatar_url($comment->comment_author_email, $args);
                            ?>
                            <img class="uwp-profile-item-alignleft uwp-profile-item-thumb" src="<?php echo $thumb_url; ?>" />
                        </a>

                        <h3 class="uwp-profile-item-title">
                            <a href="<?php echo get_comment_link($comment->comment_ID); ?>"><?php echo get_the_title($comment->comment_post_ID); ?></a>
                        </h3>
                        <time class="uwp-profile-item-time published" datetime="<?php echo get_the_time('c'); ?>">
                            <?php echo date_i18n( get_option( 'date_format' ), strtotime( get_comment_date("", $comment->comment_ID) ) ); ?>
                        </time>
                        <div class="uwp-profile-item-summary">
                            <?php
                            $excerpt = strip_shortcodes(wp_trim_words( $comment->comment_content, 15, '...' ));
                            echo $excerpt;
                            if ($excerpt) {
                                ?>
                                <a href="<?php echo get_comment_link($comment->comment_ID); ?>" class="more-link">Read More »</a>
                                <?php
                            }
                            ?>
                        </div>
                    </li>
                    <?php
                }
                echo '</ul>';
            } else {
                // no posts found
            }

            do_action('uwp_profile_pagination', $maximum_pages);
            ?>
        </div>
        <?php
    }

    public function rewrite_profile_link() {

        $page_id = uwp_get_option('user_profile_page', false);

        if ($page_id && !isset($_REQUEST['page_id'])) {
            $link = get_page_link($page_id);
            $uwp_profile_link = rtrim(substr(str_replace(home_url(), '', $link), 1), '/') . '/';

            $uwp_profile_page_id = url_to_postid($link);


            // example.com/profile/1
            $uwp_profile_link_empty_slash = '^' . $uwp_profile_link . '([^/]+)?$';
            add_rewrite_rule($uwp_profile_link_empty_slash, 'index.php?page_id=' . $uwp_profile_page_id . '&uwp_profile=$matches[1]', 'top');

            // example.com/profile/1/
            $uwp_profile_link_with_slash = '^' . $uwp_profile_link . '([^/]+)/?$';
            add_rewrite_rule($uwp_profile_link_with_slash, 'index.php?page_id=' . $uwp_profile_page_id . '&uwp_profile=$matches[1]', 'top');

            // example.com/profile/1/page/1
            $uwp_profile_link_empty_slash_paged = '^' . $uwp_profile_link . '([^/]+)/page/([0-9]+)?$';
            add_rewrite_rule($uwp_profile_link_empty_slash_paged, 'index.php?page_id=' . $uwp_profile_page_id . '&uwp_profile=$matches[1]&paged=$matches[2]', 'top');

            // example.com/profile/1/page/1/
            $uwp_profile_link_with_slash_paged = '^' . $uwp_profile_link . '([^/]+)/page/([0-9]+)/?$';
            add_rewrite_rule($uwp_profile_link_with_slash_paged, 'index.php?page_id=' . $uwp_profile_page_id . '&uwp_profile=$matches[1]&paged=$matches[2]', 'top');

            // example.com/profile/1/tab-slug
            $uwp_profile_tab_empty_slash = '^' . $uwp_profile_link . '([^/]+)/([^/]+)?$';
            add_rewrite_rule($uwp_profile_tab_empty_slash, 'index.php?page_id=' . $uwp_profile_page_id . '&uwp_profile=$matches[1]&uwp_tab=$matches[2]', 'top');

            // example.com/profile/1/tab-slug/
            $uwp_profile_tab_with_slash = '^' . $uwp_profile_link . '([^/]+)/([^/]+)/?$';
            add_rewrite_rule($uwp_profile_tab_with_slash, 'index.php?page_id=' . $uwp_profile_page_id . '&uwp_profile=$matches[1]&uwp_tab=$matches[2]', 'top');

            // example.com/profile/1/tab-slug/page/1
            $uwp_profile_tab_empty_slash_paged = '^' . $uwp_profile_link . '([^/]+)/([^/]+)/page/([0-9]+)?$';
            add_rewrite_rule($uwp_profile_tab_empty_slash_paged, 'index.php?page_id=' . $uwp_profile_page_id . '&uwp_profile=$matches[1]&uwp_tab=$matches[2]&paged=$matches[3]', 'top');

            // example.com/profile/1/tab-slug/page/1/
            $uwp_profile_tab_with_slash_paged = '^' . $uwp_profile_link . '([^/]+)/([^/]+)/page/([0-9]+)/?$';
            add_rewrite_rule($uwp_profile_tab_with_slash_paged, 'index.php?page_id=' . $uwp_profile_page_id . '&uwp_profile=$matches[1]&uwp_tab=$matches[2]&paged=$matches[3]', 'top');

            // example.com/profile/1/tab-slug/subtab-slug
            $uwp_profile_tab_empty_slash = '^' . $uwp_profile_link . '([^/]+)/([^/]+)/([^/]+)?$';
            add_rewrite_rule($uwp_profile_tab_empty_slash, 'index.php?page_id=' . $uwp_profile_page_id . '&uwp_profile=$matches[1]&uwp_tab=$matches[2]&uwp_subtab=$matches[3]', 'top');

            // example.com/profile/1/tab-slug/subtab-slug/
            $uwp_profile_tab_with_slash = '^' . $uwp_profile_link . '([^/]+)/([^/]+)/([^/]+)/?$';
            add_rewrite_rule($uwp_profile_tab_with_slash, 'index.php?page_id=' . $uwp_profile_page_id . '&uwp_profile=$matches[1]&uwp_tab=$matches[2]&uwp_subtab=$matches[3]', 'top');

            // example.com/profile/1/tab-slug/subtab-slug/page/1
            $uwp_profile_tab_empty_slash_paged = '^' . $uwp_profile_link . '([^/]+)/([^/]+)/([^/]+)/page/([0-9]+)?$';
            add_rewrite_rule($uwp_profile_tab_empty_slash_paged, 'index.php?page_id=' . $uwp_profile_page_id . '&uwp_profile=$matches[1]&uwp_tab=$matches[2]&uwp_subtab=$matches[3]&paged=$matches[4]', 'top');

            // example.com/profile/1/tab-slug/subtab-slug/page/1/
            $uwp_profile_tab_with_slash_paged = '^' . $uwp_profile_link . '([^/]+)/([^/]+)/([^/]+)/page/([0-9]+)/?$';
            add_rewrite_rule($uwp_profile_tab_with_slash_paged, 'index.php?page_id=' . $uwp_profile_page_id . '&uwp_profile=$matches[1]&uwp_tab=$matches[2]&uwp_subtab=$matches[3]&paged=$matches[4]', 'top');



        }
    }

    public function profile_query_vars($query_vars) {
        $query_vars[] = 'uwp_profile';
        $query_vars[] = 'uwp_tab';
        $query_vars[] = 'uwp_subtab';
        return $query_vars;
    }

    public function get_profile_link($link, $user_id) {

        $page_id = uwp_get_option('user_profile_page', false);

        if ($page_id) {
            $link = get_permalink($page_id);
        } else {
            $link = '';
        }

        if ($link != '') {

            if (isset($_REQUEST['page_id'])) {
                $permalink_structure = 'DEFAULT';
            } else {
                $permalink_structure = 'CUSTOM';
                // Add forward slash if not available
                $link = rtrim($link, '/') . '/';
            }


            $url_type = 1;

            if ($url_type && 2 == $url_type) {
                $username = get_the_author_meta('user_login', $user_id);
                if ('DEFAULT' == $permalink_structure) {
                    return add_query_arg(array('username' => $username), $link);
                } else {
                    $username = str_replace('@', '-at-', $username);
                    return $link . $username;
                }
            } else {
                if ('DEFAULT' == $permalink_structure) {
                    return add_query_arg(array('viewuser' => $user_id), $link);
                } else {
                    return $link . $user_id;
                }
            }
        } else {
            return '';
        }
    }

    public function modify_profile_page_title( $title, $id = null ) {

        global $wp_query;
        $page_id = uwp_get_option('user_profile_page', false);

        if ($page_id == $id && isset($wp_query->query_vars['uwp_profile'])) {
            $user = get_user_by('id', $wp_query->query_vars['uwp_profile']);
            $title = $user->display_name;
        }

        return $title;
    }

}