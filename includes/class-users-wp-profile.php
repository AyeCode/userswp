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
        $banner = uwp_get_usermeta($user->ID, 'uwp_account_banner_thumb', '');
        $avatar = uwp_get_usermeta($user->ID, 'uwp_account_avatar_thumb', '');
        if (is_user_logged_in() && get_current_user_id() == $user->ID) {
            $avatar_class = "uwp-profile-avatar-modal-trigger";
            $banner_class = "uwp-profile-banner-modal-trigger";
        } else {
            $avatar_class = "";
            $banner_class = "";
        }
        if (empty($avatar)) {
            $avatar = get_avatar($user->user_email, 128, null, null, array('class' => array($avatar_class) ));
        } else {
            $avatar = '<img src="'.$avatar.'" class="avatar avatar-128 photo" width="128" height="128">';
        }
        ?>
        <div class="uwp-profile-header">
            <div class="uwp-profile-header-img" style="background-image: url('<?php echo $banner; ?>')">
                <div class="uwp-banner-change-icon">
                    <i class="fa fa-camera" aria-hidden="true"></i>
                    <div class="uwp-profile-banner-change <?php echo $banner_class; ?>">
                    <span class="uwp-profile-banner-change-inner">
                        Update Cover Photo
                    </span>
                    </div>
                </div>
            </div>
            <div class="uwp-profile-avatar">
                <?php echo $avatar; ?>
                <div class="uwp-profile-avatar-change">
                    <div class="uwp-profile-avatar-change-inner">
                        <i class="fa fa-camera" aria-hidden="true"></i>
                        <a id="uwp-profile-picture-change" class="<?php echo $avatar_class; ?>" href="#">Update Profile Picture</a>
                    </div>
                </div>
            </div>
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
        $is_profile_page = is_uwp_profile_page();
        if ($bio) {
            ?>
            <div class="uwp-profile-bio <?php if ($is_profile_page) { echo "uwp_more"; } ?>">
                <?php
                if ($is_profile_page) {
                    echo $bio;
                } else {
                    echo wp_trim_words( $bio, 20, '...' );
                }
                ?>
            </div>
            <?php
        }
    }

    public function get_profile_social($user) {

        global $wpdb;
        $table_name = $wpdb->prefix . 'uwp_form_fields';
        $fields = $wpdb->get_results("SELECT * FROM " . $table_name . " WHERE ( form_type = 'register' OR form_type = 'account' ) AND field_type = 'url' AND css_class LIKE '%uwp_social%' ORDER BY sort_order ASC");

        ?>
        <div class="uwp-profile-social">
            <ul class="uwp-profile-social-ul">
        <?php
        foreach($fields as $field) {
            $key = $field->htmlvar_name;
            // see Users_WP_Forms -> uwp_save_user_extra_fields reason for replacing key
            $key = str_replace('uwp_register_', 'uwp_account_', $key);
            $value = uwp_get_usermeta($user->ID, $key, false);

            if ($value) {
                echo '<li><a target="_blank" rel="nofollow" href="'.$value.'"><i class="'.$field->field_icon.'"></i></a></li>';
            }
        }
        ?>
            </ul>
        </div>
        <?php
    }

    public function get_profile_extra($user) {

        ob_start();
        global $wpdb;
        $table_name = $wpdb->prefix . 'uwp_form_fields';
        $fields = $wpdb->get_results("SELECT * FROM " . $table_name . " WHERE ( form_type = 'register' OR form_type = 'account' ) AND is_default = '0' ORDER BY sort_order ASC");
        if ($fields) {
            ?>
            <div class="uwp-profile-extra">
                <table class="uwp-profile-extra-table">
                    <?php
                    foreach ($fields as $field) {
                        $key = $field->htmlvar_name;
                        // see Users_WP_Forms -> uwp_save_user_extra_fields reason for replacing key
                        $key = str_replace('uwp_register_', 'uwp_account_', $key);
                        $value = uwp_get_usermeta($user->ID, $key, false);

                        if ($value) {
                            ?>
                            <tr>
                                <td class="uwp-profile-extra-key"><?php echo $field->site_title; ?></td>
                                <td class="uwp-profile-extra-value"><?php echo $value; ?></td>
                            </tr>
                            <?php
                        }
                    }
                    ?>
                </table>
            </div>
            <?php
        }
        $output = ob_get_contents();
        ob_end_clean();
        return trim($output);
    }

    public function get_profile_tabs($user) {

        $tabs = array();
        
        $extra = $this->get_profile_extra($user);
        if ($extra) {
            $tabs['about']  = array(
                'title' => __( 'About', 'uwp' ),
                'count' => 0
            );
        }

        $enable_profile_posts_tab = uwp_get_option('enable_profile_posts_tab', false);
        if ($enable_profile_posts_tab == '1') {
            $tabs['posts']  = array(
                'title' => __( 'Posts', 'uwp' ),
                'count' => uwp_post_count($user->ID, 'post')
            );
        }

        $enable_profile_comments_tab = uwp_get_option('enable_profile_comments_tab', false);
        if ($enable_profile_comments_tab == '1') {
            $tabs['comments'] = array(
                'title' => __( 'Comments', 'uwp' ),
                'count' => uwp_comment_count($user->ID)
            );
        }

        return apply_filters( 'uwp_profile_tabs', $tabs, $user );
    }

    public function get_profile_tabs_content($user) {

        $tab = get_query_var('uwp_tab');

        $account_page = uwp_get_option('account_page', false);

        $all_tabs = $this->get_profile_tabs($user);

        $tab_keys = array_keys($all_tabs);

        if (!empty($tab_keys)) {
            $default_key = $tab_keys[0];
        } else {
            $default_key = false;
        }

        $active_tab = !empty( $tab ) && array_key_exists( $tab, $all_tabs ) ? $tab : $default_key;
        if (!$active_tab) {
            return;
        }
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
                <?php if ($account_page && is_user_logged_in() && get_current_user_id() == $user->ID) { ?>
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

    public function get_profile_pagination($total) {
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

    public function get_profile_about($user) {
        $extra = $this->get_profile_extra($user);
        echo $extra;
    }
    
    public function get_profile_posts($user) {
        $enable_profile_posts_tab = uwp_get_option('enable_profile_posts_tab', false);
        if ($enable_profile_posts_tab != '1') {
            return;
        }
        uwp_generic_tab_content($user, 'post', __('Posts', 'uwp'));
    }

    public function get_profile_comments($user) {
        $enable_profile_comments_tab = uwp_get_option('enable_profile_comments_tab', false);
        if ($enable_profile_comments_tab != '1') {
            return;
        }
        ?>
        <h3><?php echo __('Comments', 'uwp') ?></h3>

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
                            $avatar_class = "uwp-profile-item-alignleft uwp-profile-item-thumb";
                            $avatar = get_avatar($user->user_email, 80, null, null, array('class' => array($avatar_class) ));
                            echo $avatar;
                            ?>
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
                                <a href="<?php echo get_comment_link($comment->comment_ID); ?>" class="more-link"><?php echo __('Read More »', 'uwp'); ?></a>
                                <?php
                            }
                            ?>
                        </div>
                    </li>
                    <?php
                }
                echo '</ul>';
            } else {
                // no comments found
                echo "<p>".__('No Comments Found', 'uwp')."</p>";
            }

            do_action('uwp_profile_pagination', $maximum_pages);
            ?>
        </div>
        <?php
    }

    public function rewrite_profile_link() {

        $page_id = uwp_get_option('profile_page', false);

        if ($page_id && !isset($_REQUEST['page_id'])) {
            $link = get_page_link($page_id);
            $uwp_profile_link = rtrim(substr(str_replace(home_url(), '', $link), 1), '/') . '/';

            $uwp_profile_page_id = url_to_postid($link);


            // {home_url}/profile/1
            $uwp_profile_link_empty_slash = '^' . $uwp_profile_link . '([^/]+)?$';
            add_rewrite_rule($uwp_profile_link_empty_slash, 'index.php?page_id=' . $uwp_profile_page_id . '&uwp_profile=$matches[1]', 'top');

            // {home_url}/profile/1/
            $uwp_profile_link_with_slash = '^' . $uwp_profile_link . '([^/]+)/?$';
            add_rewrite_rule($uwp_profile_link_with_slash, 'index.php?page_id=' . $uwp_profile_page_id . '&uwp_profile=$matches[1]', 'top');

            // {home_url}/profile/1/page/1
            $uwp_profile_link_empty_slash_paged = '^' . $uwp_profile_link . '([^/]+)/page/([0-9]+)?$';
            add_rewrite_rule($uwp_profile_link_empty_slash_paged, 'index.php?page_id=' . $uwp_profile_page_id . '&uwp_profile=$matches[1]&paged=$matches[2]', 'top');

            // {home_url}/profile/1/page/1/
            $uwp_profile_link_with_slash_paged = '^' . $uwp_profile_link . '([^/]+)/page/([0-9]+)/?$';
            add_rewrite_rule($uwp_profile_link_with_slash_paged, 'index.php?page_id=' . $uwp_profile_page_id . '&uwp_profile=$matches[1]&paged=$matches[2]', 'top');

            // {home_url}/profile/1/tab-slug
            $uwp_profile_tab_empty_slash = '^' . $uwp_profile_link . '([^/]+)/([^/]+)?$';
            add_rewrite_rule($uwp_profile_tab_empty_slash, 'index.php?page_id=' . $uwp_profile_page_id . '&uwp_profile=$matches[1]&uwp_tab=$matches[2]', 'top');

            // {home_url}/profile/1/tab-slug/
            $uwp_profile_tab_with_slash = '^' . $uwp_profile_link . '([^/]+)/([^/]+)/?$';
            add_rewrite_rule($uwp_profile_tab_with_slash, 'index.php?page_id=' . $uwp_profile_page_id . '&uwp_profile=$matches[1]&uwp_tab=$matches[2]', 'top');

            // {home_url}/profile/1/tab-slug/page/1
            $uwp_profile_tab_empty_slash_paged = '^' . $uwp_profile_link . '([^/]+)/([^/]+)/page/([0-9]+)?$';
            add_rewrite_rule($uwp_profile_tab_empty_slash_paged, 'index.php?page_id=' . $uwp_profile_page_id . '&uwp_profile=$matches[1]&uwp_tab=$matches[2]&paged=$matches[3]', 'top');

            // {home_url}/profile/1/tab-slug/page/1/
            $uwp_profile_tab_with_slash_paged = '^' . $uwp_profile_link . '([^/]+)/([^/]+)/page/([0-9]+)/?$';
            add_rewrite_rule($uwp_profile_tab_with_slash_paged, 'index.php?page_id=' . $uwp_profile_page_id . '&uwp_profile=$matches[1]&uwp_tab=$matches[2]&paged=$matches[3]', 'top');

            // {home_url}/profile/1/tab-slug/subtab-slug
            $uwp_profile_tab_empty_slash = '^' . $uwp_profile_link . '([^/]+)/([^/]+)/([^/]+)?$';
            add_rewrite_rule($uwp_profile_tab_empty_slash, 'index.php?page_id=' . $uwp_profile_page_id . '&uwp_profile=$matches[1]&uwp_tab=$matches[2]&uwp_subtab=$matches[3]', 'top');

            // {home_url}/profile/1/tab-slug/subtab-slug/
            $uwp_profile_tab_with_slash = '^' . $uwp_profile_link . '([^/]+)/([^/]+)/([^/]+)/?$';
            add_rewrite_rule($uwp_profile_tab_with_slash, 'index.php?page_id=' . $uwp_profile_page_id . '&uwp_profile=$matches[1]&uwp_tab=$matches[2]&uwp_subtab=$matches[3]', 'top');

            // {home_url}/profile/1/tab-slug/subtab-slug/page/1
            $uwp_profile_tab_empty_slash_paged = '^' . $uwp_profile_link . '([^/]+)/([^/]+)/([^/]+)/page/([0-9]+)?$';
            add_rewrite_rule($uwp_profile_tab_empty_slash_paged, 'index.php?page_id=' . $uwp_profile_page_id . '&uwp_profile=$matches[1]&uwp_tab=$matches[2]&uwp_subtab=$matches[3]&paged=$matches[4]', 'top');

            // {home_url}/profile/1/tab-slug/subtab-slug/page/1/
            $uwp_profile_tab_with_slash_paged = '^' . $uwp_profile_link . '([^/]+)/([^/]+)/([^/]+)/page/([0-9]+)/?$';
            add_rewrite_rule($uwp_profile_tab_with_slash_paged, 'index.php?page_id=' . $uwp_profile_page_id . '&uwp_profile=$matches[1]&uwp_tab=$matches[2]&uwp_subtab=$matches[3]&paged=$matches[4]', 'top');
            
            if (get_option('uwp_flush_rewrite')) {
                //Ensure the $wp_rewrite global is loaded
                global $wp_rewrite;
                //Call flush_rules() as a method of the $wp_rewrite object
                $wp_rewrite->flush_rules( false );
                delete_option('uwp_flush_rewrite');
            }
        }
    }

    public function profile_query_vars($query_vars) {
        $query_vars[] = 'uwp_profile';
        $query_vars[] = 'uwp_tab';
        $query_vars[] = 'uwp_subtab';
        return $query_vars;
    }

    public function get_profile_link($link, $user_id) {

        $page_id = uwp_get_option('profile_page', false);

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


            $url_type = apply_filters('uwp_profile_url_type', 'login');

            if ($url_type && 'id' == $url_type) {
                if ('DEFAULT' == $permalink_structure) {
                    return add_query_arg(array('viewuser' => $user_id), $link);
                } else {
                    return $link . $user_id;
                }
            } else {
                $username = get_the_author_meta('user_login', $user_id);
                if ('DEFAULT' == $permalink_structure) {
                    return add_query_arg(array('username' => $username), $link);
                } else {
                    return $link . $username;
                }

            }
        } else {
            return '';
        }
    }

    public function modify_profile_page_title( $title, $id = null ) {

        global $wp_query;
        $page_id = uwp_get_option('profile_page', false);

        if ($page_id == $id && isset($wp_query->query_vars['uwp_profile']) && in_the_loop()) {

            $url_type = apply_filters('uwp_profile_url_type', 'login');

            $author_slug = $wp_query->query_vars['uwp_profile'];

            if ($url_type == 'id') {
                $user = get_user_by('id', $author_slug);
            } else {
                $user = get_user_by('login', $author_slug);
            }
            $title = $user->display_name;
        }

        return $title;
    }

    public function uwp_image_crop_popup($image_url, $type) {

        ob_start();
            $uploads = wp_upload_dir();
            $upload_url = $uploads['baseurl'];
            $upload_path = $uploads['basedir'];
            $image_path = str_replace($upload_url, $upload_path, $image_url);

            $image = apply_filters( 'uwp_'.$type.'_cropper_image', getimagesize( $image_path ) );
            if ( empty( $image ) ) {
                return "";
            }

            // Get avatar full width and height.
            if ($type == 'avatar') {
                $full_height = apply_filters('uwp_avatar_image_height', 128);
                $full_width  = apply_filters('uwp_avatar_image_width', 128);
            } else {
                $full_height = apply_filters('uwp_banner_image_height', 300);
                $full_width  = apply_filters('uwp_banner_image_width', 700);
            }

            // Calculate Aspect Ratio.
            if ( !empty( $full_height ) && ( $full_width != $full_height ) ) {
                $aspect_ratio = $full_width / $full_height;
            } else {
                $aspect_ratio = 1;
            }

            // Default cropper coordinates.
            // Smaller than full-width: cropper defaults to entire image.
            if ( $image[0] < $full_width ) {
                $crop_left  = 0;
                $crop_right = $image[0];

                // Less than 2x full-width: cropper defaults to full-width.
            } elseif ( $image[0] < ( $full_width * 2 ) ) {
                $padding_w  = round( ( $image[0] - $full_width ) / 2 );
                $crop_left  = $padding_w;
                $crop_right = $image[0] - $padding_w;

                // Larger than 2x full-width: cropper defaults to 1/2 image width.
            } else {
                $crop_left  = round( $image[0] / 4 );
                $crop_right = $image[0] - $crop_left;
            }

            // Smaller than full-height: cropper defaults to entire image.
            if ( $image[1] < $full_height ) {
                $crop_top    = 0;
                $crop_bottom = $image[1];

                // Less than double full-height: cropper defaults to full-height.
            } elseif ( $image[1] < ( $full_height * 2 ) ) {
                $padding_h   = round( ( $image[1] - $full_height ) / 2 );
                $crop_top    = $padding_h;
                $crop_bottom = $image[1] - $padding_h;

                // Larger than 2x full-height: cropper defaults to 1/2 image height.
            } else {
                $crop_top    = round( $image[1] / 4 );
                $crop_bottom = $image[1] - $crop_top;
            }
            ?>

            <script type="text/javascript">
                jQuery(window).load( function(){
                    jQuery('#uwp-<?php echo $type; ?>-to-crop').Jcrop({
                        onChange: showPreview,
                        onSelect: updateCoords,
                        aspectRatio: <?php echo (int) $aspect_ratio; ?>,
                        setSelect: [ <?php echo (int) $crop_left; ?>, <?php echo (int) $crop_top; ?>, <?php echo (int) $crop_right; ?>, <?php echo (int) $crop_bottom; ?> ]
                    });
                });

                function updateCoords(c) {
                    jQuery('#<?php echo $type; ?>-x').val(c.x);
                    jQuery('#<?php echo $type; ?>-y').val(c.y);
                    jQuery('#<?php echo $type; ?>-w').val(c.w);
                    jQuery('#<?php echo $type; ?>-h').val(c.h);
                }

                function showPreview(coords) {
                    if ( parseInt(coords.w) > 0 ) {
                        var fw = <?php echo (int) $full_width; ?>;
                        var fh = <?php echo (int) $full_height; ?>;
                        var rx = fw / coords.w;
                        var ry = fh / coords.h;

                        jQuery( '#uwp-<?php echo $type; ?>-crop-preview' ).css({
                            width: Math.round(rx * <?php echo (int) $image[0]; ?>) + 'px',
                            height: Math.round(ry * <?php echo (int) $image[1]; ?>) + 'px',
                            marginLeft: '-' + Math.round(rx * coords.x) + 'px',
                            marginTop: '-' + Math.round(ry * coords.y) + 'px'
                        });
                    }
                }
            </script>

            <div align="center">
                <img src="<?php echo $image_url; ?>" id="uwp-<?php echo $type; ?>-to-crop" />
                <div class="uwp-<?php echo $type; ?>-crop-p-wrap">
                    <div id="uwp-<?php echo $type; ?>-crop-pane" style="width:<?php echo $full_width; ?>px; height:<?php echo $full_height; ?>px">
                        <img src="<?php echo $image_url; ?>" id="uwp-<?php echo $type; ?>-crop-preview" />
                    </div>
                    <div id="<?php echo $type; ?>-crop-actions">
                        <form class="uwp-crop-form" method="post">
                            <input type="hidden" name="x" value="" id="<?php echo $type; ?>-x" />
                            <input type="hidden" name="y" value="" id="<?php echo $type; ?>-y" />
                            <input type="hidden" name="w" value="" id="<?php echo $type; ?>-w" />
                            <input type="hidden" name="h" value="" id="<?php echo $type; ?>-h" />
                            <input type="hidden" name="uwp_crop" value="<?php echo $image_url; ?>" />
                            <input type="hidden" name="uwp_crop_nonce" value="<?php echo wp_create_nonce( 'uwp-crop-nonce' ); ?>" />
                            <input type="submit" name="uwp_<?php echo $type; ?>_crop" value="<?php echo __('Crop Image', 'uwp'); ?>" id="save_uwp_<?php echo $type; ?>" />
                        </form>
                    </div>
                </div>
            </div>
        <?php
        $output = ob_get_contents();
        ob_end_clean();
        return trim($output);
    }

    public function uwp_image_crop_init($user) {

        if (is_user_logged_in()) {
            $this->uwp_image_crop_modal($user);
            $this->uwp_image_crop_js();
        }

    }

    public function uwp_image_crop_modal($user) {

        if(isset($_GET['uwp_crop']) && isset($_GET['type']) && $_GET['uwp_crop'] != '' && $_GET['type'] != '') {
            $type = sanitize_text_field($_GET['type']);
            $image_url = esc_url_raw($_GET['uwp_crop']);
            $style = "display: block";
            $this->uwp_image_crop_modal_html('avatar', $style, $this->uwp_image_crop_popup($image_url, $type));
        } else {
            $style = "display: none";
            $this->uwp_image_crop_modal_html('avatar', $style, $this->uwp_crop_submit_form('avatar'));
            $this->uwp_image_crop_modal_html('banner', $style, $this->uwp_crop_submit_form('banner'));
        }

    }

    public function uwp_image_crop_modal_html($type, $style, $content) {
        ?>
        <div id="uwp-<?php echo $type; ?>-modal" class="uwp-modal" style="<?php echo $style; ?>">
            <a id="uwp-<?php echo $type; ?>-modal-close" href="#" class="uwp-modal-close-x"><i class="fa fa-times"></i></a>
            <div class="uwp-modal-content-wrap">
                <div class="uwp-modal-content">
                    <?php echo $content; ?>
                </div>
            </div>
        </div>
        <?php
    }

    public function uwp_crop_submit_form($type = 'avatar') {
        ob_start();
        ?>
        <form id="uwp-upload-<?php echo $type; ?>-form" method="post" enctype="multipart/form-data">
            <input type="hidden" name="uwp_upload_nonce" value="<?php echo wp_create_nonce( 'uwp-upload-nonce' ); ?>" />
            <input type="hidden" name="uwp_<?php echo $type; ?>_submit" value="" />
            <button type="button" class="uwp_upload_button" onclick="document.getElementById('uwp_upload_<?php echo $type; ?>').click();">Upload <?php echo $type; ?></button>
            <p style="text-align: center"><?php echo __('Note: Max upload image size: ', 'uwp').uwp_get_option('profile_avatar_max_size', 5); ?> MB</p>
            <div class="uwp_upload_field" style="display: none">
                <input name="uwp_<?php echo $type; ?>_file" id="uwp_upload_<?php echo $type; ?>" onchange="this.form.submit()" required="required" type="file" value="">
                <button type="submit" id="uwp_<?php echo $type; ?>_submit_button" style="display: none"></button>
            </div>
        </form>
        <?php
        $output = ob_get_contents();
        ob_end_clean();
        return trim($output);
    }

    public function uwp_image_crop_js() {
        if (isset($_GET['uwp_crop']) && isset($_GET['type'])) {
            $backdrop = true;
        } else {
            $backdrop = false;
        }
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function() {
                <?php
                if ($backdrop) {
                ?>
                jQuery(document.body).append("<div id='uwp-modal-backdrop'></div>");
                <?php
                }
                ?>
            });
        </script>
        <?php
    }

    public function uwp_extra_user_profile_fields_in_admin( $user ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'uwp_form_fields';
        $excluded_fields = array('uwp_account_email', 'uwp_account_password', 'uwp_account_confirm_password', 'uwp_account_first_name', 'uwp_account_last_name');
        $fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $table_name . " WHERE form_type = %s AND field_type != 'file' AND is_active = '1' ORDER BY sort_order ASC", array('account')));
        if (!empty($fields)) {
            ?>
            <h3><?php _e("UsersWP", "uwp"); ?></h3>
            <table class="form-table">
            <?php
            foreach ($fields as $field) {
                if (in_array($field->htmlvar_name, $excluded_fields)) {
                    continue;
                }
                $option_values_arr = uwp_string_values_to_options($field->option_values, true);
                ?>
                    <tr>
                        <th><?php echo $field->site_title; ?></th>
                        <td>
                            <?php
                            $value = uwp_get_usermeta($user->ID, $field->htmlvar_name, "");
                            if ($field->field_type == 'select') {
                                if (!empty($value)) {
                                    $data = $this->uwp_array_search($option_values_arr, 'value', $value);
                                    $value = $data[0]['label'];
                                } else {
                                    $value = '';
                                }

                            }
                            if ($field->field_type == 'multiselect' && !empty($value)) {
                                if (!empty($value) && is_array($value)) {
                                    $array_value = array();
                                    foreach ($value as $v) {
                                        $data = $this->uwp_array_search($option_values_arr, 'value', $v);
                                        $array_value[] = $data[0]['label'];
                                    }
                                    $value = implode(', ', $array_value);
                                } else {
                                    $value = '';
                                }
                            }
                            echo $value;
                            ?>
                        </td>
                    </tr>
                <?php
            }
            ?>
            </table>
            <?php
        }
    }

    public function uwp_array_search($array, $key, $value)
    {
        $results = array();

        if (is_array($array)) {
            if (isset($array[$key]) && $array[$key] == $value) {
                $results[] = $array;
            }

            foreach ($array as $subarray) {
                $results = array_merge($results, $this->uwp_array_search($subarray, $key, $value));
            }
        }

        return $results;
    }

    public function uwp_modify_get_avatar( $avatar, $id_or_email, $size, $default, $alt ) {
        $user = false;

        if ( is_numeric( $id_or_email ) ) {

            $id = (int) $id_or_email;
            $user = get_user_by( 'id' , $id );

        } elseif ( is_object( $id_or_email ) ) {

            if ( ! empty( $id_or_email->user_id ) ) {
                $id = (int) $id_or_email->user_id;
                $user = get_user_by( 'id' , $id );
            }

        } else {
            $user = get_user_by( 'email', $id_or_email );
        }

        if ( $user && is_object( $user ) ) {
            $avatar_thumb = uwp_get_usermeta($user->data->ID, 'uwp_account_avatar_thumb', '');
            if ( !empty($avatar_thumb) ) {
                $avatar = "<img alt='{$alt}' src='{$avatar_thumb}' class='avatar avatar-{$size} photo' height='{$size}' width='{$size}' />";
            }

        }

        return $avatar;
    }

    public function uwp_get_comment_author_link($link) {
        global $comment;
        if ( !empty( $comment->user_id ) && !empty( get_userdata( $comment->user_id )->ID ) ) {
            $user = get_userdata( $comment->user_id );
            $link = sprintf(
                '<a href="%s" rel="external nofollow" class="url">%s</a>',
                uwp_build_profile_tab_url( $comment->user_id ),
                strip_tags( $user->display_name )
            );
        }
        return $link;
    }

}