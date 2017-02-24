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
        if (is_user_logged_in() && get_current_user_id() == $user->ID && is_uwp_profile_page()) {
            $trigger_class = "uwp-profile-modal-form-trigger";
        } else {
            $trigger_class = "";
        }

        if (empty($banner)) {
            $banner = USERSWP_PLUGIN_URL."/public/assets/images/banner.png";
        }
        if (empty($avatar)) {
            $avatar = get_avatar($user->user_email, 150);
        } else {
            $avatar = '<img src="'.$avatar.'" class="avatar avatar-150 photo" width="150" height="150">';
        }
        ?>
        <div class="uwp-profile-header">
            <div class="uwp-profile-header-img">
                <?php
                if (!is_uwp_profile_page()) {
                    echo '<a href="'.apply_filters('uwp_profile_link', get_author_posts_url($user->ID), $user->ID).'" title="'.$user->display_name.'">';
                }
                ?>
                <img src="<?php echo $banner; ?>" alt="" class="uwp-profile-header-img-src" />
                <?php
                if (!is_uwp_profile_page()) {
                    echo '</a>';
                }
                ?>
            <?php if (is_user_logged_in() && (get_current_user_id() == $user->ID) && is_uwp_profile_page()) { ?>
                <div class="uwp-banner-change-icon">
                    <i class="fa fa-camera" aria-hidden="true"></i>
                    <div data-type="banner" class="uwp-profile-banner-change <?php echo $trigger_class; ?>">
                    <span class="uwp-profile-banner-change-inner">
                        Update Cover Photo
                    </span>
                    </div>
                </div>
            <?php } ?>
            </div>
            <div class="uwp-profile-avatar">
                <?php
                if (!is_uwp_profile_page()) {
                    echo '<a href="'.apply_filters('uwp_profile_link', get_author_posts_url($user->ID), $user->ID).'" title="'.$user->display_name.'">';
                }
                ?>
                <div class="uwp-profile-avatar-inner">
                    <?php echo $avatar; ?>
                    <?php if (is_user_logged_in() && (get_current_user_id() == $user->ID) && is_uwp_profile_page()) { ?>
                        <div class="uwp-profile-avatar-change">
                            <div class="uwp-profile-avatar-change-inner">
                                <i class="fa fa-camera" aria-hidden="true"></i>
                                <a id="uwp-profile-picture-change" data-type="avatar" class="<?php echo $trigger_class; ?>" href="#">Update</a>
                            </div>
                        </div>
                    <?php } ?>
                </div>
                <?php
                if (!is_uwp_profile_page()) {
                    echo '</a>';
                }
                ?>
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
        $bio = get_user_meta( $user->ID, 'description', true );
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

        if (is_uwp_profile_page()) {
            $show_type = '[profile_side]';
        } elseif (is_uwp_users_page()) {
            $show_type = '[users]';
        } else {
            $show_type = false;
        }

        if (!$show_type) {
            return;
        }
        ?>
        <div class="uwp-profile-social">
            <ul class="uwp-profile-social-ul">
        <?php
        foreach($fields as $field) {
            $show_in = explode(',',$field->show_in);

            if (!in_array($show_type, $show_in)) {
                continue;
            }
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

    public function get_profile_extra_count($user) {
//        global $wpdb;
//        $table_name = $wpdb->prefix . 'uwp_form_fields';
//        $fields = $wpdb->get_results("SELECT * FROM " . $table_name . " WHERE form_type = 'account' AND css_class NOT LIKE '%uwp_social%' AND field_type != 'fieldset' AND is_public = '1' AND show_in LIKE '%[more_info]%' ORDER BY sort_order ASC");
//        return count($fields);
        return '<i class="fa fa-user"></i>';
    }

    public function get_profile_extra($user) {
        return $this->uwp_get_extra_fields($user, '[more_info]');
    }

    public function get_profile_side_extra($user) {
        echo $this->uwp_get_extra_fields($user, '[profile_side]');
    }

    public function get_users_extra($user) {
        echo $this->uwp_get_extra_fields($user, '[users]');
    }

    public function uwp_get_extra_fields($user, $show_type) {

        ob_start();
        global $wpdb;
        $table_name = $wpdb->prefix . 'uwp_form_fields';
        $fields = $wpdb->get_results("SELECT * FROM " . $table_name . " WHERE form_type = 'account' AND css_class NOT LIKE '%uwp_social%' ORDER BY sort_order ASC");
        if ($fields) {
            ?>
            <div class="uwp-profile-extra">
                <div class="uwp-profile-extra-div form-table">
                    <?php
                    foreach ($fields as $field) {
                        $show_in = explode(',',$field->show_in);
                        if (!in_array($show_type, $show_in)) {
                            continue;
                        }
                        if ($field->is_public == '0') {
                            continue;
                        }

                        if ($field->is_public == '2') {
                            $field_name = $field->htmlvar_name.'_privacy';
                            $val = uwp_get_usermeta($user->ID, $field_name, false);
                            if ($val === '0') {
                                continue;
                            }
                        }

                        $value = $this->uwp_get_field_value($field, $user);

                        // Icon
                        if ($field->field_icon) {
                            $icon = '<i class="uwp_field_icon '.$field->field_icon.'"></i>';
                        } else {
                            $icon = '';
                        }

                        if ($field->field_type == 'fieldset') {
                            ?>
                            <div class="uwp-profile-extra-wrap" style="margin: 0; padding: 0">
                                <div class="uwp-profile-extra-key uwp-profile-extra-full" style="margin: 0; padding: 0"><h3 style="margin: 10px 0;"><?php echo $icon.$field->site_title; ?></h3></div>
                            </div>
                            <?php
                        } else {
                            if ($value) {
                                ?>
                                <div class="uwp-profile-extra-wrap">
                                    <div class="uwp-profile-extra-key"><?php echo $icon.$field->site_title; ?><span class="uwp-profile-extra-sep">:</span></div>
                                    <div class="uwp-profile-extra-value">
                                        <?php
                                        if ($field->htmlvar_name == 'uwp_account_bio') {
                                            $is_profile_page = is_uwp_profile_page();
                                            if ($value) {
                                                ?>
                                                <div class="uwp-profile-bio <?php if ($is_profile_page) { echo "uwp_more"; } ?>">
                                                    <?php
                                                    if ($is_profile_page) {
                                                        echo $value;
                                                    } else {
                                                        echo wp_trim_words( $value, 20, '...' );
                                                    }
                                                    ?>
                                                </div>
                                                <?php
                                            }
                                        } else {
                                            echo $value;
                                        }
                                        ?>
                                    </div>
                                </div>
                                <?php
                            }
                        }
                    }
                    ?>
                </div>
            </div>
            <?php
        }
        $output = ob_get_contents();
        ob_end_clean();
        return trim($output);
    }
    

    public function get_profile_tabs($user) {

        $tabs = array();

        // allowed tabs
        $allowed_tabs = uwp_get_option('enable_profile_tabs', array());

        if (!is_array($allowed_tabs)) {
            $allowed_tabs = array();
        }

        $extra = $this->get_profile_extra($user);
        if (in_array('more_info', $allowed_tabs) && $extra) {
            $tabs['more_info']  = array(
                'title' => __( 'More Info', 'uwp' ),
                'count' => $this->get_profile_extra_count($user)
            );
        }

        if (in_array('posts', $allowed_tabs)) {
            $tabs['posts']  = array(
                'title' => __( 'Posts', 'uwp' ),
                'count' => uwp_post_count($user->ID, 'post')
            );
        }

        if (in_array('comments', $allowed_tabs)) {
            $tabs['comments'] = array(
                'title' => __( 'Comments', 'uwp' ),
                'count' => uwp_comment_count($user->ID)
            );
        }

        return apply_filters( 'uwp_profile_tabs', $tabs, $user, $allowed_tabs );
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
                do_action('uwp_profile_tab_content', $user, $active_tab);
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

    public function get_profile_more_info($user) {
        $allowed_tabs = uwp_get_option('enable_profile_tabs', array());

        if (!is_array($allowed_tabs)) {
            $allowed_tabs = array();
        }
        if (!in_array('more_info', $allowed_tabs)) {
            return;
        }
        
        $extra = $this->get_profile_extra($user);
        echo $extra;
    }
    
    public function get_profile_posts($user) {
        
        $allowed_tabs = uwp_get_option('enable_profile_tabs', array());

        if (!is_array($allowed_tabs)) {
            $allowed_tabs = array();
        }
        if (!in_array('posts', $allowed_tabs)) {
             return;   
        }

        uwp_generic_tab_content($user, 'post', __('Posts', 'uwp'));
    }

    public function get_profile_comments($user) {
        $allowed_tabs = uwp_get_option('enable_profile_tabs', array());

        if (!is_array($allowed_tabs)) {
            $allowed_tabs = array();
        }
        if (!in_array('comments', $allowed_tabs)) {
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


            $url_type = apply_filters('uwp_profile_url_type', 'slug');

            if ($url_type && 'id' == $url_type) {
                if ('DEFAULT' == $permalink_structure) {
                    return add_query_arg(array('viewuser' => $user_id), $link);
                } else {
                    return $link . $user_id;
                }
            } else {
                $user = get_userdata($user_id);
                if ( !empty($user->user_nicename) ) {
                    $username = $user->user_nicename;
                } else {
                    $username = $user->user_login;
                }

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

            $url_type = apply_filters('uwp_profile_url_type', 'slug');

            $author_slug = $wp_query->query_vars['uwp_profile'];

            if ($url_type == 'id') {
                $user = get_user_by('id', $author_slug);
            } else {
                $user = get_user_by('slug', $author_slug);
            }
            $title = $user->display_name;
        }

        return $title;
    }

    public function uwp_image_crop_popup($image_url, $type) {

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
            $full_width  = apply_filters('uwp_avatar_image_width', 150);
            $full_height = apply_filters('uwp_avatar_image_height', 150);
        } else {
            $full_width  = apply_filters('uwp_banner_image_width', 1000);
            $full_height = apply_filters('uwp_banner_image_height', 300);
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

        $values = array(
            'error' => '',
            'image_url' => $image_url,
            'uwp_popup_type' => $type,
            'uwp_full_width' => $full_width,
            'uwp_full_height' => $full_height,
            'uwp_true_width' => $image[0],
            'uwp_true_height' => $image[1],
            'uwp_popup_content' => $this->uwp_image_crop_modal_html($type, $image_url, $full_width, $full_height),
        );
        
        $json = json_encode($values);


        return $json;
    }
    
    public function uwp_image_crop_init($user) {
        if (is_user_logged_in()) {
            echo $this->uwp_modal_loading_html();
            $this->uwp_modal_close_js();
        }
    }

    public function uwp_modal_loading_html() {
        ob_start();
        ?>
        <div id="uwp-popup-modal-wrap" style="display: none;">
            <div class="uwp-bs-modal fade show">
                <div class="uwp-bs-modal-dialog">
                    <div class="uwp-bs-modal-content">
                        <div class="uwp-bs-modal-header">
                            <h4 class="uwp-bs-modal-title">
                                Loading Form ...
                            </h4>
                        </div>
                        <div class="uwp-bs-modal-body">
                            <div class="uwp-bs-modal-loading-icon-wrap">
                                <div class="uwp-bs-modal-loading-icon"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        $output = ob_get_contents();
        ob_end_clean();
        return trim(preg_replace("/\s+|\n+|\r/", ' ', $output));
    }

    public function uwp_modal_close_js() {
        ?>
        <script type="text/javascript">
            (function( $, window, undefined ) {
                $(document).ready(function () {
                    $('.uwp-modal-close').click(function(e) {
                        e.preventDefault();
                        var uwp_popup_type = $( this ).data( 'type' );
                        // $('#uwp-'+uwp_popup_type+'-modal').hide();
                        var mod_shadow = jQuery('#uwp-modal-backdrop');
                        var container = jQuery('#uwp-popup-modal-wrap');
                        container.hide();
                        container.replaceWith('<?php echo $this->uwp_modal_loading_html(); ?>');
                        mod_shadow.remove();
                    });
                });
            }( jQuery, window ));
        </script>
        <?php
    }
    

    public function uwp_image_crop_modal_html($type, $image_url, $full_width, $full_height) {
        ob_start();
        ?>
        <div class="uwp-bs-modal fade show" id="uwp-<?php echo $type; ?>-modal">
            <div class="uwp-bs-modal-dialog">
                <div class="uwp-bs-modal-content">
                    <div class="uwp-bs-modal-header">
                        <h4 class="uwp-bs-modal-title">
                            <?php
                            if ($type == 'avatar') {
                                echo "Change your profile photo";
                            } else {
                                echo "Change your cover photo";
                            }
                            ?>
                        </h4>
                        <button type="button" class="close uwp-modal-close" data-type="<?php echo $type; ?>" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="uwp-bs-modal-body">
                        <div id="uwp-bs-modal-notice"></div>
                        <div align="center">
                            <img src="<?php echo $image_url; ?>" id="uwp-<?php echo $type; ?>-to-crop" />
                        </div>
                    </div>
                    <div class="uwp-bs-modal-footer">
                        <div class="uwp-<?php echo $type; ?>-crop-p-wrap">
                            <div id="<?php echo $type; ?>-crop-actions">
                                <form class="uwp-crop-form" method="post">
                                    <input type="hidden" name="x" value="" id="<?php echo $type; ?>-x" />
                                    <input type="hidden" name="y" value="" id="<?php echo $type; ?>-y" />
                                    <input type="hidden" name="w" value="" id="<?php echo $type; ?>-w" />
                                    <input type="hidden" name="h" value="" id="<?php echo $type; ?>-h" />
                                    <input type="hidden" id="uwp-<?php echo $type; ?>-crop-image" name="uwp_crop" value="<?php echo $image_url; ?>" />
                                    <input type="hidden" name="uwp_crop_nonce" value="<?php echo wp_create_nonce( 'uwp-crop-nonce' ); ?>" />
                                    <input type="submit" name="uwp_<?php echo $type; ?>_crop" value="<?php echo __('Apply', 'uwp'); ?>" class="button button-primary" id="save_uwp_<?php echo $type; ?>" />
                                </form>
                            </div>
                        </div>
                        <button type="button" data-type="<?php echo $type; ?>" class="button uwp_modal_btn uwp-modal-close" data-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
        <script type="text/javascript">
            (function( $, window, undefined ) {
                $(document).ready(function () {
                    $('.uwp-modal-close').click(function(e) {
                        e.preventDefault();
                        var uwp_popup_type = $( this ).data( 'type' );
                        // $('#uwp-'+uwp_popup_type+'-modal').hide();
                        var mod_shadow = jQuery('#uwp-modal-backdrop');
                        var container = jQuery('#uwp-popup-modal-wrap');
                        container.hide();
                        container.replaceWith('<?php echo $this->uwp_modal_loading_html(); ?>');
                        mod_shadow.remove();
                    });
                });
            }( jQuery, window ));
        </script>
        <?php
        $output = ob_get_contents();
        ob_end_clean();
        return trim($output);
    }

    public function uwp_crop_submit_form($type = 'avatar') {
        ob_start();
        ?>
        <div class="uwp-bs-modal fade show" id="uwp-popup-modal-wrap">
            <div class="uwp-bs-modal-dialog">
                <div class="uwp-bs-modal-content">
                    <div class="uwp-bs-modal-header">
                        <h4 class="uwp-bs-modal-title">
                            <?php
                            if ($type == 'avatar') {
                                echo "Change your profile photo";
                            } else {
                                echo "Change your cover photo";
                            }
                            ?>
                        </h4>
                        <button type="button" class="close uwp-modal-close" data-type="<?php echo $type; ?>" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="uwp-bs-modal-body">
                        <div id="uwp-bs-modal-notice"></div>
                        <form id="uwp-upload-<?php echo $type; ?>-form" method="post" enctype="multipart/form-data">
                            <input type="hidden" name="uwp_upload_nonce" value="<?php echo wp_create_nonce( 'uwp-upload-nonce' ); ?>" />
                            <input type="hidden" name="uwp_<?php echo $type; ?>_submit" value="" />
                            <button type="button" class="uwp_upload_button" onclick="document.getElementById('uwp_upload_<?php echo $type; ?>').click();">Upload <?php echo $type; ?></button>
                            <p style="text-align: center"><?php echo __('Note: Max upload image size: ', 'uwp').uwp_formatSizeUnits(uwp_get_max_upload_size()); ?></p>
                            <div class="uwp_upload_field" style="display: none">
                                <input name="uwp_<?php echo $type; ?>_file" id="uwp_upload_<?php echo $type; ?>" required="required" type="file" value="">
                            </div>
                         </form>
                        <div id="progressBar" class="tiny-green" style="display: none;"><div></div></div>
                    </div>
                    <div class="uwp-bs-modal-footer">
                        <div class="uwp-<?php echo $type; ?>-crop-p-wrap">
                            <div id="<?php echo $type; ?>-crop-actions">
                                <form class="uwp-crop-form" method="post">
                                    <input type="submit" name="uwp_<?php echo $type; ?>_crop" disabled="disabled" value="<?php echo __('Apply', 'uwp'); ?>" class="button button-primary" id="save_uwp_<?php echo $type; ?>" />
                                </form>
                            </div>
                        </div>
                        <button type="button" data-type="<?php echo $type; ?>" class="button uwp_modal_btn uwp-modal-close" data-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
        <script type="text/javascript">
            (function( $, window, undefined ) {

                var uwp_popup_type = '<?php echo $type; ?>';

                $(document).ready(function () {
                    $("#progressbar").progressbar();
                    $('.uwp-modal-close').click(function(e) {
                        e.preventDefault();
                        var uwp_popup_type = $( this ).data( 'type' );
                        // $('#uwp-'+uwp_popup_type+'-modal').hide();
                        var mod_shadow = jQuery('#uwp-modal-backdrop');
                        var container = jQuery('#uwp-popup-modal-wrap');
                        container.hide();
                        container.replaceWith('<?php echo $this->uwp_modal_loading_html(); ?>');
                        mod_shadow.remove();
                    });

                    $('#uwp_upload_<?php echo $type; ?>').on('change', function(e) {
                        e.preventDefault();

                        var container = jQuery('#uwp-popup-modal-wrap');
                        var err_container = jQuery('#uwp-bs-modal-notice');

                        var fd = new FormData();
                        var files_data = $(this); // The <input type="file" /> field
                        var file = files_data[0].files[0];

                        fd.append('uwp_<?php echo $type; ?>_file', file);
                        // our AJAX identifier
                        fd.append('action', 'uwp_avatar_banner_upload');
                        fd.append('uwp_popup_type', '<?php echo $type; ?>');

                        $("#progressBar").show();

                        $.ajax({
                            // Your server script to process the upload
                            url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
                            type: 'POST',

                            // Form data
                            data: fd,

                            // Tell jQuery not to process data or worry about content-type
                            // You *must* include these options!
                            cache: false,
                            contentType: false,
                            processData: false,

                            // Custom XMLHttpRequest
                            xhr: function() {
                                myXhr = $.ajaxSettings.xhr();
                                if(myXhr.upload){
                                    myXhr.upload.addEventListener('progress',showProgress, false);
                                } else {
                                    console.log("Upload progress is not supported.");
                                }
                                return myXhr;
                            },

                            success:function(response) {
                                $("#progressBar").hide();
                                resp = JSON.parse(response);
                                if (resp['error'] != "") {
                                    err_container.html(resp['error']);
                                } else {
                                    resp = JSON.parse(response);
                                    uwp_full_width = resp['uwp_full_width'];
                                    uwp_full_height = resp['uwp_full_height'];
                                    uwp_true_width = resp['uwp_true_width'];
                                    uwp_true_height = resp['uwp_true_height'];

                                    jQuery('#uwp-popup-modal-wrap').html(resp['uwp_popup_content']).find('#uwp-'+uwp_popup_type+'-to-crop').Jcrop({
                                        // onChange: showPreview,
                                        onSelect: updateCoords,
                                        allowResize: true,
                                        allowSelect: false,
                                        boxWidth: 650,   //Maximum width you want for your bigger images
                                        boxHeight: 400,  //Maximum Height for your bigger images
                                        setSelect: [ 0, 0, uwp_full_width, uwp_full_height ],
                                        aspectRatio: uwp_full_width/uwp_full_height,
                                        trueSize: [uwp_true_width,uwp_true_height],
                                        minSize: [uwp_full_width,uwp_full_height]
                                    });
                                }
                            }
                        });
                    });

                    function showProgress(evt) {
                        if (evt.lengthComputable) {
                            var percentComplete = (evt.loaded / evt.total) * 100;
//                            $('#progressbar').progressbar("option", "value", percentComplete );
                            progress(percentComplete, $('#progressBar'));
                        }
                    }

                    function progress(percent, $element) {
                        var progressBarWidth = percent * $element.width() / 100;
                        $element.find('div').animate({ width: progressBarWidth }, 500).html(percent + "% ");
                    }

                    function updateCoords(c) {
                        jQuery('#'+uwp_popup_type+'-x').val(c.x);
                        jQuery('#'+uwp_popup_type+'-y').val(c.y);
                        jQuery('#'+uwp_popup_type+'-w').val(c.w);
                        jQuery('#'+uwp_popup_type+'-h').val(c.h);
                    }

                });
            }( jQuery, window ));
        </script>

        <?php
        $output = ob_get_contents();
        ob_end_clean();
        return trim($output);
 }


    public function uwp_extra_user_profile_fields( $user ) {
        ?>
        <h3><?php _e("UsersWP", "uwp"); ?></h3>
        <?php echo $this->get_profile_extra($user); ?>
        <?php
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

    public function uwp_redirect_author_page() {
        if ( is_author() ) {
            $id = get_query_var( 'author' );
            $link = uwp_build_profile_tab_url( $id );
            wp_redirect($link);
            exit;
        }
    }
    
    //modify edit my profile link in admin bar
    public function uwp_modify_admin_bar_edit_profile_url( $url, $user_id, $scheme )
    {
        // Makes the link to http://example.com/account
        if (!is_admin()) {
            $account_page = uwp_get_option('account_page', false);
            if ($account_page) {
                $account_page_link = get_permalink($account_page);
                $url = $account_page_link;
            }
        }
        return $url;
    }

    public function uwp_restrict_attachment_display($wp_query) {
        if (!is_admin()) {
            if ( ! current_user_can( 'manage_options' ) ) {
                //$wp_query['author'] = get_current_user_id();
                $wp_query->set( 'author', get_current_user_id() );
            }
        }
        return $wp_query;
    }

    public function allow_all_users_profile_uploads($llcaps, $caps, $args, $user) {

        
        if(isset($caps[0]) && $caps[0] =='upload_files' && uwp_doing_upload() ){
            $llcaps['upload_files'] = true;
        }

        return $llcaps;
    }

    public function uwp_handle_file_upload_error_checks($value, $field, $file_key, $file_to_upload) {
        
        if (in_array($field->htmlvar_name, array('uwp_avatar_file', 'uwp_banner_file'))) {

            if ($field->htmlvar_name == 'uwp_avatar_file') {
                $min_width  = apply_filters('uwp_avatar_image_width', 150);
                $min_height = apply_filters('uwp_avatar_image_height', 150);
            } else {
                $min_width  = apply_filters('uwp_banner_image_width', 1000);
                $min_height = apply_filters('uwp_banner_image_height', 300);
            }

            $imagedetails = getimagesize( $file_to_upload['tmp_name'] );
            $width = $imagedetails[0];
            $height = $imagedetails[1];

            if ( $width < $min_width) {
                return new WP_Error( 'image-too-small', __( 'The uploaded file is too small. Minimum image width should be '.$min_width.'px', 'uwp' ) );
            }
            if ( $height < $min_height) {
                return new WP_Error( 'image-too-small', __( 'The uploaded file is too small. Minimum image height should be '.$min_height.'px', 'uwp' ) );
            }
        }

        return $value;
        
    }

    public function add_uwp_plupload_param($params) {

        if(!is_admin() && get_the_ID()==uwp_get_option('profile_page', false)){
            $params['uwp_profile_upload'] = true;
        }

        return $params;
    }

    public function uwp_ajax_avatar_banner_upload() {
        // Image upload handler
        // todo: security checks
        $type = strip_tags(esc_sql($_POST['uwp_popup_type']));

        if (!in_array($type, array('banner', 'avatar'))) {
            $result['error'] = uwp_wrap_notice("Invalid modal type", 'error');
            $return = json_encode($result);
            echo $return;
            die();
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'uwp_form_fields';
        $fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $table_name . " WHERE form_type = %s AND field_type = 'file' AND is_active = '1' ORDER BY sort_order ASC", array($type)));

        $field = false;
        if ($fields) {
            $field = $fields[0];
        }

        $result = array();

        if (!$field) {
            $result['error'] = uwp_wrap_notice("No fields available", 'error');
            $return = json_encode($result);
            echo $return;
            die();
        }

        $errors = handle_file_upload($field, $_FILES);

        if (is_wp_error($errors)) {
            $result['error'] = uwp_wrap_notice($errors->get_error_message(), 'error');
            $return = json_encode($result);
            echo $return;
        } else {
            $return = $this->uwp_ajax_image_crop_popup($errors['url'], $type);
            echo $return;
        }

        die();
    }

    
    public function uwp_ajax_image_crop_popup($image_url, $type){
        wp_enqueue_style( 'jcrop' );
        wp_enqueue_script( 'jcrop', array( 'jquery' ) );

        $output = null;
        if ($image_url && $type ) {
            $output = $this->uwp_image_crop_popup($image_url, $type);
        }
        return $output;
    }

    public function uwp_ajax_image_crop_popup_form(){
        $type = strip_tags(esc_sql($_POST['type']));

        $output = null;


        if ($type && in_array($type, array('banner', 'avatar'))) {
            $output = $this->uwp_crop_submit_form($type);
        }
        echo $output;
        exit();
    }

    public function uwp_define_ajaxurl() {

        echo '<script type="text/javascript">
           var ajaxurl = "' . admin_url('admin-ajax.php') . '";
         </script>';
    }
    
    public function uwp_users_search() {
        ?>
        <div class="uwp-users-list-sort-search">
            <div class="uwp-user-search">
                <?php
                $keyword = "";
                if (isset($_GET['uwps']) && $_GET['uwps'] != '') {
                    $keyword = strip_tags(esc_sql($_GET['uwps']));
                }
                ?>
                <form method="get" class="searchform search-form" action="<?php echo get_uwp_users_permalink(); ?>">
                    <input placeholder="Search For" name="uwps" value="<?php echo $keyword; ?>" class="s search-input" type="text">
                    <input class="uwp-searchsubmit uwp-search-submit" value="Search" type="submit"><br>
                </form>
            </div>
            <div class="uwp-user-sort">
                <?php
                $default_layout = uwp_get_option('users_default_layout', 'list');
                ?>
                <form method="get" action="">
                    <select name="uwp_layout" id="uwp_layout">
                        <option <?php selected( $default_layout, "list" ); ?> value="list">List View</option>
                        <option <?php selected( $default_layout, "2col" ); ?> value="2col">Grid 2 Col</option>
                        <option <?php selected( $default_layout, "3col" ); ?> value="3col">Grid 3 Col</option>
                        <option <?php selected( $default_layout, "4col" ); ?> value="4col">Grid 4 Col</option>
                        <option <?php selected( $default_layout, "5col" ); ?> value="5col">Grid 5 Col</option>
                    </select>
                    <?php
                    $sort_by = "";
                    if (isset($_GET['uwp_sort_by']) && $_GET['uwp_sort_by'] != '') {
                        $sort_by = strip_tags(esc_sql($_GET['uwp_sort_by']));
                    }
                    ?>
                    <select name="uwp_sort_by" id="uwp_sort_by" onchange="this.form.submit()">
                        <option value="">Sort By:</option>
                        <option <?php selected( $sort_by, "newer" ); ?> value="newer">Newer</option>
                        <option <?php selected( $sort_by, "older" ); ?> value="older">Older</option>
                        <option <?php selected( $sort_by, "alpha_asc" ); ?> value="alpha_asc">A-Z</option>
                        <option <?php selected( $sort_by, "alpha_desc" ); ?> value="alpha_desc">Z-A</option>
                    </select>
                </form>
            </div>
        </div>
        <?php
    }

    public function uwp_users_list() {
        get_uwp_users_list();
    }

    public function uwp_extra_fields_as_tabs($tabs, $user)
    {
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'uwp_form_fields';
        $fields = $wpdb->get_results("SELECT * FROM " . $table_name . " WHERE form_type = 'account' AND field_type != 'fieldset' AND is_public = '1' AND show_in LIKE '%[own_tab]%' ORDER BY sort_order ASC");

        foreach ($fields as $field) {
            $key = str_replace('uwp_account_', '', $field->htmlvar_name);
            $tabs[$key] = array(
                'title' => __($field->site_title, 'uwp'),
                'count' => 1
            );
        }

        return $tabs;
    }

    public function uwp_extra_fields_as_tab_values($user, $active_tab)
    {


        global $wpdb;
        $table_name = $wpdb->prefix . 'uwp_form_fields';
        $fields = $wpdb->get_results("SELECT * FROM " . $table_name . " WHERE form_type = 'account' AND field_type != 'fieldset' AND is_public = '1' AND show_in LIKE '%[own_tab]%' ORDER BY sort_order ASC");

        foreach ($fields as $field) {
            $key = str_replace('uwp_account_', '', $field->htmlvar_name);
            if ($key == $active_tab) {
                echo '<div class="uwp_profile_tab_field_content">';
                echo $this->uwp_get_field_value($field, $user);
                echo '</div>';
            }
        }
    }

    public function uwp_get_field_value($field, $user) {

        $user_data = get_userdata($user->ID);

        if ($field->htmlvar_name == 'uwp_account_email') {
            $value = $user_data->user_email;
        } elseif ($field->htmlvar_name == 'uwp_account_password') {
            $value = '';
            $field->is_required = 0;
        } elseif ($field->htmlvar_name == 'uwp_account_confirm_password') {
            $value = '';
            $field->is_required = 0;
        } elseif ($field->htmlvar_name == 'uwp_account_first_name') {
            $value = $user_data->first_name;
        } elseif ($field->htmlvar_name == 'uwp_account_last_name') {
            $value = $user_data->last_name;
        } elseif ($field->htmlvar_name == 'uwp_account_bio') {
            $value = $user_data->description;
        } else {
            $value = uwp_get_usermeta($user->ID, $field->htmlvar_name, "");
        }


        // Select and Multiselect needs Value to be converted
        if ($field->field_type == 'select' || $field->field_type == 'multiselect') {
            $option_values_arr = array();
            $option_values_arr = uwp_string_values_to_options($field->option_values, true);

            // Select
            if ($field->field_type == 'select') {
                if (!empty($value)) {
                    $data = $this->uwp_array_search($option_values_arr, 'value', $value);
                    $value = $data[0]['label'];
                } else {
                    $value = '';
                }
            }

            //Multiselect
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
        }

        if ($field->field_type == 'datepicker') {
            $extra_fields = unserialize($field->extra_fields);

            if ($extra_fields['date_format'] == '')
                $extra_fields['date_format'] = 'yy-mm-dd';

            $date_format = $extra_fields['date_format'];

            if (!empty($value)) {
                $value = date('Y-m-d', $value);
            }

            $value = uwp_date($value, $date_format, 'Y-m-d');
        }



        // URL
        if ($field->field_type == 'url' && !empty($value)) {
            $link_text = $value;
            if ($field->default_value && !empty($field->default_value) ) {
                if (substr( $field->default_value, 0, 4 ) === "http") {
                    $link_text = $value;
                } else {
                    $link_text = $field->default_value;
                }
            }
            $value = '<a href="'.$value.'">'.$link_text.'</a>';
        }

        // Checkbox
        if ($field->field_type == 'checkbox') {
            if ($value == '1') {
                $value = 'Yes';
            } else {
                $value = 'No';
            }
        }

        // File
        if ($field->field_type == 'file') {
            $value = uwp_file_upload_preview($field, $value, false);
        }

        return $value;
    }

}