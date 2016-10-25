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
        $bio = get_user_meta($user->ID, 'uwp_account_bio', true);
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
                    $link = get_user_meta($user->ID, 'uwp_account_social_'.$value, true);
                    echo '<li><a href="'.$link.'"><i class="fa fa-'.$value.'"></i></a></li>';
                }
                ?>
            </ul>
        </div>
        <?php
    }

    public function get_profile_tabs() {

        //todo: fix this
        $author_id = get_current_user_id();

        $tabs = array();

        $tabs['posts']  = array(
            'title' => __( 'Posts', 'uwp' ),
            'count' => uwp_post_count($author_id)
        );
        $tabs['comments'] = array(
            'title' => __( 'Comments', 'uwp' ),
            'count' => uwp_comment_count($author_id)
        );

        return apply_filters( 'uwp_profile_tabs', $tabs );
    }

    function get_profile_tabs_content() {
        global $uwp_options;
        $account_page = isset($uwp_options['account_page']) ? esc_attr( $uwp_options['account_page']) : false;
        $active_tab = isset( $_GET['tab'] ) && array_key_exists( $_GET['tab'], $this->get_profile_tabs() ) ? $_GET['tab'] : 'posts';
        ?>
        <div class="uwp-profile-content">
            <div class="uwp-profile-nav">
                <ul class="item-list-tabs-ul">
                    <?php
                    foreach( $this->get_profile_tabs() as $tab_id => $tab ) {

                        $tab_url = add_query_arg( array(
                            'tab' => $tab_id,
                            'subtab' => false
                        ) );

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
                do_action('uwp_profile_'.$active_tab.'_tab_content');
                ?>
            </div>
        </div>
    <?php }

    function get_profile_pagination($the_query) {
        ?>
        <div class="uwp-pagination">
            <?php
            $big = 999999999; // need an unlikely integer
            $translated = __( 'Page', 'uwp' ); // Supply translatable string

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
        <?php
    }

    function get_profile_posts() {
        //todo: fix this
        $author_id = get_current_user_id();
        $user = get_user_by('id', $author_id);
        ?>
        <h3><?php echo $user->display_name; ?>’s Posts</h3>

        <div class="uwp-profile-item-block">
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
            do_action('uwp_profile_pagination', $the_query);
            ?>
        </div>
        <?php
    }

    function get_profile_comments() {
        //todo: fix this
        $author_id = get_current_user_id();
        $user = get_user_by('id', $author_id);
        ?>
        <h3><?php echo $user->display_name; ?>’s Comments</h3>

        <div class="uwp-profile-item-block">
            <?php
            $paged = ( get_query_var( 'paged' ) ) ? absint( get_query_var( 'paged' ) ) : 1;
            $number = 10;
            $offset = ( $paged - 1 ) * $number;

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
                /* Restore original Post Data */
                wp_reset_postdata();
            } else {
                // no posts found
            }
            //todo: fix pagination for comments http://stackoverflow.com/a/12379058/736037
            do_action('uwp_profile_pagination', $the_query);
            ?>
        </div>
        <?php
    }

}