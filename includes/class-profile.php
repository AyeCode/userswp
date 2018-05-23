<?php
/**
 * Profile Template related functions
 *
 * This class defines all code necessary for Profile template.
 *
 * @since      1.0.0
 * @author     GeoDirectory Team <info@wpgeodirectory.com>
 */
class UsersWP_Profile {
    
    /**
     * Prints the profile page header section.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       object      $user       The User ID.
     */
    public function get_profile_header($user) {
        $banner = uwp_get_usermeta($user->ID, 'uwp_account_banner_thumb', '');
        $avatar = uwp_get_usermeta($user->ID, 'uwp_account_avatar_thumb', '');
        $uploads = wp_upload_dir();
        $upload_url = $uploads['baseurl'];
        if (is_user_logged_in() && get_current_user_id() == $user->ID && is_uwp_profile_page()) {
            $trigger_class = "uwp-profile-modal-form-trigger";
        } else {
            $trigger_class = "";
        }

        if (empty($banner)) {
            $banner = uwp_get_option('profile_default_banner', '');
            if(empty($banner)){
                $banner = USERSWP_PLUGIN_URL."/public/assets/images/banner.png";
            } else {
                $banner = wp_get_attachment_url($banner);
            }
        } else {
            $banner = $upload_url.$banner;
        }
        if (empty($avatar)) {
            $avatar = get_avatar($user->user_email, 150);
        } else {
            // check the image is not a full url before adding the local upload url
            if (strpos($avatar, 'http:') === false && strpos($avatar, 'https:') === false) {
                $avatar = $upload_url.$avatar;
            }
            $avatar = '<img src="'.$avatar.'" class="avatar avatar-150 photo" width="150" height="150">';
        }
        ?>
        <div class="uwp-profile-header clearfix">
            <div class="uwp-profile-header-img clearfix">
                <?php
                if (!is_uwp_profile_page()) {
                    echo '<a href="'.apply_filters('uwp_profile_link', get_author_posts_url($user->ID), $user->ID).'" title="'.$user->display_name.'">';
                }
                ?>
                <img src="<?php echo $banner; ?>" alt="" class="uwp-profile-header-img-src" data-recalc-dims="0" />
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
                        <?php echo __( 'Update Cover Photo', 'userswp' ); ?>
                    </span>
                    </div>
                </div>
            <?php } ?>
            </div>
            <div class="uwp-profile-avatar clearfix">
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
                                <a id="uwp-profile-picture-change" data-type="avatar" class="<?php echo $trigger_class; ?>" href="#"><?php echo __( 'Update', 'userswp' ); ?></a>
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

    /**
     * Prints the profile page title section. 
     *
     * @since       1.0.0
     * @package     userswp
     * @param       object      $user       The User ID.
     */
    public function get_profile_title($user) {
        ?>
        <div class="uwp-profile-name">
            <h2 class="uwp-user-title" data-user="<?php echo $user->ID; ?>">
                <?php echo apply_filters('uwp_profile_display_name', $user->display_name); ?>
                <?php do_action('uwp_profile_after_title', $user->ID ); ?>
            </h2>
        </div>
        <?php
    }

    /**
     * Prints the profile page bio section.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       object      $user       The User ID.
     */
    public function get_profile_bio($user) {
        $bio = uwp_get_usermeta( $user->ID, 'uwp_account_bio', "" );
        $bio = stripslashes($bio);
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

    /**
     * Prints the profile page social links section.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       object      $user       The User ID.
     */
    public function get_profile_social($user) {

        global $wpdb;
        $table_name = uwp_get_table_prefix() . 'uwp_form_fields';
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
            // see UsersWP_Forms -> uwp_save_user_extra_fields reason for replacing key
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

    /**
     * More info tab title.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       object      $user       The User ID.
     * @return      string                  Tab title.
     */
    public function get_profile_count_icon($user) {
        return '<i class="fa fa-user"></i>';
    }

    /**
     * Returns the custom fields content of profile page more info tab.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       object      $user       The User ID.
     * @return      string                  More info tab content.
     */
    public function get_profile_extra($user) {
        return $this->uwp_get_extra_fields($user, '[more_info]');
    }

    /**
     * Returns the custom fields content of profile page sidebar.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       object      $user       The User ID.
     * @return      string                  Sidebar custom fields content.
     */
    public function get_profile_side_extra($user) {
        echo $this->uwp_get_extra_fields($user, '[profile_side]');
    }

    /**
     * Returns the custom fields content of users page.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       object      $user       The User ID.
     * @return      string                  Users page custom fields content.
     */
    public function get_users_extra($user) {
        echo $this->uwp_get_extra_fields($user, '[users]');
    }

    /**
     * Returns the custom fields content based on type.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       object      $user       The User ID.
     * @param       string      $show_type  Filter type. 
     * @return      string                  Custom fields content.
     */
    public function uwp_get_extra_fields($user, $show_type) {

        ob_start();
        global $wpdb;
        $table_name = uwp_get_table_prefix() . 'uwp_form_fields';
        $fields = $wpdb->get_results("SELECT * FROM " . $table_name . " WHERE form_type = 'account' AND css_class NOT LIKE '%uwp_social%' ORDER BY sort_order ASC");
        $wrap_html = false;
        if ($fields) {
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
                    if ($val === 'no') {
                        continue;
                    }
                }

                $value = $this->uwp_get_field_value($field, $user);

                // Icon
                $icon = uwp_get_field_icon($field->field_icon);

                if ($field->field_type == 'fieldset') {
                    $icon = '';
                    ?>
                    <div class="uwp-profile-extra-wrap" style="margin: 0; padding: 0">
                        <div class="uwp-profile-extra-key uwp-profile-extra-full" style="margin: 0; padding: 0"><h3 style="margin: 10px 0;"><?php echo $icon.$field->site_title; ?></h3></div>
                    </div>
                    <?php
                } else {
                    if ($value) {
                        $wrap_html = true;
                        ?>
                        <div class="uwp-profile-extra-wrap">
                            <div class="uwp-profile-extra-key"><?php echo $icon.$field->site_title; ?><span class="uwp-profile-extra-sep">:</span></div>
                            <div class="uwp-profile-extra-value">
                                <?php
                                if ($field->htmlvar_name == 'uwp_account_bio') {
                                    $is_profile_page = is_uwp_profile_page();
                                    $value = stripslashes($value);
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
        }
        $output = ob_get_contents();
        $wrapped_output = '';
        if ($wrap_html) {
            $wrapped_output .= '<div class="uwp-profile-extra"><div class="uwp-profile-extra-div form-table">';
            $wrapped_output .= $output;
            $wrapped_output .= '</div></div>';
        }
        ob_end_clean();
        return trim($wrapped_output);
    }

    /**
     * Returns enabled profile tabs
     *
     * @since       1.0.0
     * @package     userswp
     * @param       object      $user       The User ID.
     * @return      array                   Profile tabs
     */
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
                'title' => __( 'More Info', 'userswp' ),
                'count' => $this->get_profile_count_icon($user)
            );
        }

        if (in_array('posts', $allowed_tabs)) {
            $tabs['posts']  = array(
                'title' => __( 'Posts', 'userswp' ),
                'count' => uwp_post_count($user->ID, 'post')
            );
        }

        if (in_array('comments', $allowed_tabs)) {
            $tabs['comments'] = array(
                'title' => __( 'Comments', 'userswp' ),
                'count' => uwp_comment_count($user->ID)
            );
        }

        $all_tabs = apply_filters( 'uwp_profile_tabs', $tabs, $user, $allowed_tabs );

        // order tabs as per option values
        if(!empty($allowed_tabs)){
            $allowed_tabs = array_reverse($allowed_tabs);
            foreach($allowed_tabs as $key => $val){
                if(isset($all_tabs[$val])){
                    $all_tabs = array($val => $all_tabs[$val]) + $all_tabs;
                }

            }
        }

        return $all_tabs;
    }

    /**
     * Prints the profile tab content template
     *
     * @since       1.0.0
     * @package     userswp
     * @param       object      $user       The User ID.
     * @return      void
     */
    public function get_profile_tabs_content($user) {

        $tab = get_query_var('uwp_tab');

        $account_page = uwp_get_page_id('account_page', false);

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
                <?php
                $can_user_edit_account = apply_filters('uwp_user_can_edit_own_profile', true, $user->ID);
                ?>
                <?php if ($account_page && is_user_logged_in() && (get_current_user_id() == $user->ID) && $can_user_edit_account) { ?>
                    <div class="uwp-edit-account">
                        <a href="<?php echo get_permalink( $account_page ); ?>" title="<?php echo  __( 'Edit Account', 'userswp' ); ?>"><i class="fa fa-gear"></i></a>
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

    /**
     * Prints the tab content pagination section.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       int         $total       Total items.
     * @return      void
     */
    public function get_profile_pagination($total) {
        ?>
        <div class="uwp-pagination">
            <?php
            $big = 999999999; // need an unlikely integer
            $translated = __( 'Page', 'userswp' ); // Supply translatable string

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

    /**
     * Prints the profile page more info tab content.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       object      $user       The User ID.
     * @return      void
     */
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

    /**
     * Prints the profile page "posts" tab content.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       object      $user       The User ID.
     * @return      void
     */
    public function get_profile_posts($user) {
        
        $allowed_tabs = uwp_get_option('enable_profile_tabs', array());

        if (!is_array($allowed_tabs)) {
            $allowed_tabs = array();
        }
        if (!in_array('posts', $allowed_tabs)) {
             return;   
        }

        uwp_generic_tab_content($user, 'post', __('Posts', 'userswp'));
    }

    /**
     * Prints the profile page "comments" tab content.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       object      $user       The User ID.
     * @return      void
     */
    public function get_profile_comments($user) {
        $allowed_tabs = uwp_get_option('enable_profile_tabs', array());

        if (!is_array($allowed_tabs)) {
            $allowed_tabs = array();
        }
        if (!in_array('comments', $allowed_tabs)) {
            return;
        }
        ?>
        <h3><?php echo __('Comments', 'userswp') ?></h3>

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
                            if ( has_post_thumbnail() ) {
                                $thumb_url = get_the_post_thumbnail_url(get_the_ID(), array(80, 80));
                            } else {
                                $thumb_url = USERSWP_PLUGIN_URL."/public/assets/images/no_thumb.png";
                            }
                            ?>
                            <img class="uwp-profile-item-alignleft uwp-profile-item-thumb" src="<?php echo $thumb_url; ?>">
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
                                <a href="<?php echo get_comment_link($comment->comment_ID); ?>" class="more-link"><?php echo __('Read More »', 'userswp'); ?></a>
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
                echo "<p>".__('No Comments Found', 'userswp')."</p>";
            }

            do_action('uwp_profile_pagination', $maximum_pages);
            ?>
        </div>
        <?php
    }

    /**
     * Rewrites profile page links
     *
     * @since       1.0.0
     * @package     userswp
     * @return      void
     */
    public function rewrite_profile_link() {

        $page_id = uwp_get_page_id('profile_page', false);
        if ($page_id && !isset($_REQUEST['page_id'])) {
            $link = get_page_link($page_id);
            $uwp_profile_link = rtrim(substr(str_replace(home_url(), '', $link), 1), '/') . '/';
            //$uwp_profile_page_id = url_to_postid($link);
            $uwp_profile_page_id = $page_id;



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

    /**
     * Adds profile page query variables.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       array       $query_vars     Query variables.
     * @return      array                       Modified Query variables array.
     */
    public function profile_query_vars($query_vars) {
        $query_vars[] = 'uwp_profile';
        $query_vars[] = 'uwp_tab';
        $query_vars[] = 'uwp_subtab';
        return $query_vars;
    }

    /**
     * Returns user profile link based on user id.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       string          $link           Unmodified link.
     * @param       int             $user_id        User id.
     * @return      string                          Modified link.
     */
    public function get_profile_link($link, $user_id) {

        $page_id = uwp_get_page_id('profile_page', false);

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

    /**
     * Modifies profile page title to include username.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       string          $title          Original title
     * @param       int|null        $id             Page id.
     * @return      string                          Modified page title.
     */
    public function modify_profile_page_title( $title, $id = null ) {

        global $wp_query;
        $page_id = uwp_get_page_id('profile_page', false);

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

    /**
     * Returns json content for image crop.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       string              $image_url      Image url
     * @param       string              $type           popup type. Avatar or Banner
     * @return      string                              Json
     */
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
            $full_width  = apply_filters('uwp_banner_image_width', uwp_get_option('profile_banner_width', 1000));
            $full_height = apply_filters('uwp_banner_image_height', 300);
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

    /**
     * Initializes image crop js.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       object      $user       The User ID.
     */
    public function uwp_image_crop_init($user) {
        if (is_user_logged_in()) {
            add_action( 'wp_footer', array($this,'uwp_modal_loading_html'));
            add_action( 'wp_footer', array($this,'uwp_modal_close_js'));
            if(is_admin()) {
                add_action('admin_footer', array($this, 'uwp_modal_loading_html'));
                add_action('admin_footer', array($this, 'uwp_modal_close_js'));
            }
        }
    }

    /**
     * Returns modal content loading html.
     *
     * @since       1.0.0
     * @package     userswp
     * @return      string      Modal loding html.
     */
    public function uwp_modal_loading_html() {
        ob_start();
        ?>
        <div id="uwp-popup-modal-wrap" style="display: none;">
            <div class="uwp-bs-modal uwp_fade uwp_show">
                <div class="uwp-bs-modal-dialog">
                    <div class="uwp-bs-modal-content">
                        <div class="uwp-bs-modal-header">
                            <h4 class="uwp-bs-modal-title">
                                <?php echo __( "Loading Form ...", "userswp" ); ?>
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
        echo trim(preg_replace("/\s+|\n+|\r/", ' ', $output));
    }

    /**
     * Adds modal close js.
     *
     * @since       1.0.0
     * @package     userswp
     * @return      void
     */
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

    /**
     * Returns avatar and banner crop modal html.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       string          $type           Avatar or Banner
     * @param       string          $image_url      Image url to crop
     * @param       int             $full_width     Full image width
     * @param       int             $full_height    Full image height
     * @return      string                          Html.
     */
    public function uwp_image_crop_modal_html($type, $image_url, $full_width, $full_height) {
        ob_start();
        ?>
        <div class="uwp-bs-modal uwp_fade uwp_show" id="uwp-<?php echo $type; ?>-modal">
            <div class="uwp-bs-modal-dialog">
                <div class="uwp-bs-modal-content">
                    <div class="uwp-bs-modal-header">
                        <h4 class="uwp-bs-modal-title">
                            <?php
                            if ($type == 'avatar') {
                                echo __( 'Change your profile photo', 'userswp' );
                            } else {
                                echo __( 'Change your cover photo', 'userswp' );
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
                                    <input type="submit" name="uwp_<?php echo $type; ?>_crop" value="<?php echo __('Apply', 'userswp'); ?>" class="button button-primary" id="save_uwp_<?php echo $type; ?>" />
                                </form>
                            </div>
                        </div>
                        <button type="button" data-type="<?php echo $type; ?>" class="button uwp_modal_btn uwp-modal-close" data-dismiss="modal"><?php echo __( 'Cancel', 'userswp' ); ?></button>
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

    /**
     * Returns avatar and banner crop submit form.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       string          $type           Avatar or Banner
     * @return      string                          Html.
     */
    public function uwp_crop_submit_form($type = 'avatar') {
        $files = new UsersWP_Files();
        ob_start();
        ?>
        <div class="uwp-bs-modal uwp_fade uwp_show" id="uwp-popup-modal-wrap">
            <div class="uwp-bs-modal-dialog">
                <div class="uwp-bs-modal-content">
                    <div class="uwp-bs-modal-header">
                        <h4 class="uwp-bs-modal-title">
                            <?php
                            if ($type == 'avatar') {
                                echo __( 'Change your profile photo', 'userswp' );
                            } else {
                                echo __( 'Change your cover photo', 'userswp' );
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
                            <button type="button" class="uwp_upload_button" onclick="document.getElementById('uwp_upload_<?php echo $type; ?>').click();"><?php echo __( 'Upload', 'userswp' ); ?> <?php echo $type; ?></button>
                            <p style="text-align: center"><?php echo __('Note: Max upload image size: ', 'userswp').$files->uwp_formatSizeUnits($files->uwp_get_max_upload_size($type)); ?></p>
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
                                    <input type="submit" name="uwp_<?php echo $type; ?>_crop" disabled="disabled" value="<?php echo __('Apply', 'userswp'); ?>" class="button button-primary" id="save_uwp_<?php echo $type; ?>" />
                                </form>
                            </div>
                        </div>
                        <button type="button" data-type="<?php echo $type; ?>" class="button uwp_modal_btn uwp-modal-close" data-dismiss="modal"><?php echo __( 'Cancel', 'userswp' ); ?></button>
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

    
    /**
     * Array search by value
     *
     * @since       1.0.0
     * @package     userswp
     * @param       array       $array      Original array.
     * @param       mixed       $key        Array key
     * @param       mixed       $value      Array value.
     * @return      array                   Result array.
     */
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

    /**
     * Modifies get_avatar function to use userswp avatar.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       string      $avatar         img tag value for the user's avatar.
     * @param       mixed       $id_or_email    The Gravatar to retrieve. Accepts a user_id, gravatar md5 hash,
     *                                          user email, WP_User object, WP_Post object, or WP_Comment object.
     * @param       int         $size           Square avatar width and height in pixels to retrieve.
     * @param       string      $default        URL for the default image or a default type. Accepts '404', 'retro', 'monsterid',
     *                                          'wavatar', 'indenticon','mystery' (or 'mm', or 'mysteryman'), 'blank', or 'gravatar_default'.
     *                                          Default is the value of the 'avatar_default' option, with a fallback of 'mystery'.
     * @param       string      $alt            Alternative text to use in the avatar image tag. Default empty.
     * @return      string                      Modified img tag value
     */
    public function uwp_modify_get_avatar( $avatar, $id_or_email, $size, $default, $alt, $args ) {
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
                $uploads = wp_upload_dir();
                $upload_url = $uploads['baseurl'];
                if (substr( $avatar_thumb, 0, 4 ) !== "http") {
                    $avatar_thumb = $upload_url.$avatar_thumb;
                }
                $avatar = "<img alt='{$alt}' src='{$avatar_thumb}' class='avatar avatar-{$size} photo' height='{$size}' width='{$size}' />";
            } else {
                $default = uwp_get_default_avatar_uri();
                $args = get_avatar_data( $id_or_email, $args );
                $url = $args['url'];
                $url = remove_query_arg('d', $url);
                $url = add_query_arg(array('d' => $default), $url);
                if ( ! $url || is_wp_error( $url ) ) {
                    return $avatar;
                }
                $avatar = '<img src="' .$url  .'" class="gravatar avatar avatar-'.$size.' uwp-avatar" width="'.$size.'" height="'.$size.'" alt="'.$alt.'" />';
            }

        }

        return $avatar;
    }

    /**
     * Modified the comment author url to profile page link for loggedin users.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       string      $link       Original author link.
     * @return      string                  Modified author link.    
     */
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

    /**
     * Redirects /author page to /profile page.
     *
     * @since       1.0.0
     * @package     userswp
     * @return      void
     */
    public function uwp_redirect_author_page() {
        if ( is_author() && apply_filters( 'uwp_check_redirect_author_page', true ) ) {
            $id = get_query_var( 'author' );
            $link = uwp_build_profile_tab_url( $id );
            $link = apply_filters( 'uwp_redirect_author_page', $link, $id );
            wp_redirect($link);
            exit;
        }
    }

    /**
     * Modifies "edit my profile" link in admin bar
     *
     * @since       1.0.0
     * @package     userswp
     * @param       string      $url        The complete URL including scheme and path.
     * @param       int         $user_id    The user ID.
     * @param       string      $scheme     Scheme to give the URL context. Accepts 'http', 'https', 'login',
     *                                      'login_post', 'admin', 'relative' or null.
     * @return      false|string            Filtered url.
     */
    public function uwp_modify_admin_bar_edit_profile_url( $url, $user_id, $scheme )
    {
        // Makes the link to http://example.com/account
        if (!is_admin()) {
            $account_page = uwp_get_page_id('account_page', false);
            if ($account_page) {
                $account_page_link = get_permalink($account_page);
                $url = $account_page_link;
            }
        }
        return $url;
    }

    /**
     * Restrict the files display only to current users in media popup.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       object      $wp_query      Unmodified wp_query. 
     * @return      object                     Modified wp_query.
     */
    public function uwp_restrict_attachment_display($wp_query) {
        if (!is_admin()) {
            if ( ! current_user_can( 'manage_options' ) ) {
                //$wp_query['author'] = get_current_user_id();
                $wp_query->set( 'author', get_current_user_id() );
            }
        }
        return $wp_query;
    }

    /**
     * Allow users to upload files who has upload capability.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       array   $llcaps         An array of all the user's capabilities.
     * @param       array   $caps           Actual capabilities for meta capability.
     * @param       array   $args           Optional parameters passed to has_cap(), typically object ID.
     * @param       object  $user           The user object.
     * @return      array                   User capabilities.
     */
    public function allow_all_users_profile_uploads($llcaps, $caps, $args, $user) {

        $files = new UsersWP_Files();
        
        if(isset($caps[0]) && $caps[0] =='upload_files' && $files->uwp_doing_upload() ){
            $llcaps['upload_files'] = true;
        }

        return $llcaps;
    }

    /**
     * Validates file uploads and returns errors if found.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       string|bool         $value              Original value.
     * @param       object              $field              Field info.
     * @param       string              $file_key           Field key.
     * @param       array               $file_to_upload     File data to upload.
     * @return      string|WP_Error                         Returns original value if no erros. Else returns errors.
     */
    public function uwp_handle_file_upload_error_checks($value, $field, $file_key, $file_to_upload) {
        
        if (in_array($field->htmlvar_name, array('uwp_avatar_file', 'uwp_banner_file'))) {

            if ($field->htmlvar_name == 'uwp_avatar_file') {
                $min_width  = apply_filters('uwp_avatar_image_width', 150);
                $min_height = apply_filters('uwp_avatar_image_height', 150);
            } else {
                $min_width  = apply_filters('uwp_banner_image_width', uwp_get_option('profile_banner_width', 1000));
                $min_height = apply_filters('uwp_banner_image_height', 300);
            }

            $imagedetails = getimagesize( $file_to_upload['tmp_name'] );
            $width = $imagedetails[0];
            $height = $imagedetails[1];

            if ( $width < $min_width) {
                return new WP_Error( 'image-too-small', sprintf( __( 'The uploaded file is too small. Minimum image width should be %s px', 'userswp' ),  $min_width));
            }
            if ( $height < $min_height) {
                return new WP_Error( 'image-too-small', sprintf( __( 'The uploaded file is too small. Minimum image height should be %s px', 'userswp' ), $min_height) );
            }
        }

        return $value;
        
    }

    /**
     * Sets uwp_profile_upload to true on profile page.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       array      $params     Plupload params
     * @return      array                  Plupload params
     */
    public function add_uwp_plupload_param($params) {

        if(!is_admin() && get_the_ID()==uwp_get_page_id('profile_page', false)){
            $params['uwp_profile_upload'] = true;
        }

        return $params;
    }

    /**
     * Handles avatar and banner file upload.
     *
     * @since       1.0.0
     * @package     userswp
     * @return      void
     */
    public function uwp_ajax_avatar_banner_upload() {
        // Image upload handler
        // todo: security checks
        $type = strip_tags(esc_sql($_POST['uwp_popup_type']));

        if (!in_array($type, array('banner', 'avatar'))) {
            $result['error'] = uwp_wrap_notice(__("Invalid modal type", "userswp"), 'error');
            $return = json_encode($result);
            echo $return;
            die();
        }

        global $wpdb;
        $table_name = uwp_get_table_prefix() . 'uwp_form_fields';
        $fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $table_name . " WHERE form_type = %s AND field_type = 'file' AND is_active = '1' ORDER BY sort_order ASC", array($type)));

        $field = false;
        if ($fields) {
            $field = $fields[0];
        }

        $result = array();

        if (!$field) {
            $result['error'] = uwp_wrap_notice(__("No fields available", "userswp"), 'error');
            $return = json_encode($result);
            echo $return;
            die();
        }

        $files = new UsersWP_Files();
        $errors = $files->handle_file_upload($field, $_FILES);

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

    /**
     * Returns the avatar and banner crop popup html and js.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       string          $image_url      Image url to crop.
     * @param       string          $type           Crop type. Avatar or Banner
     * @return      string|null                     Html and js content.
     */
    public function uwp_ajax_image_crop_popup($image_url, $type){
        wp_enqueue_style( 'jcrop' );
        wp_enqueue_script( 'jcrop', array( 'jquery' ) );

        $output = null;
        if ($image_url && $type ) {
            $output = $this->uwp_image_crop_popup($image_url, $type);
        }
        return $output;
    }

    /**
     * Handles crop popup form ajax request.
     *
     * @since       1.0.0
     * @package     userswp
     * @return      void
     */
    public function uwp_ajax_image_crop_popup_form(){
        $type = strip_tags(esc_sql($_POST['type']));

        $output = null;


        if ($type && in_array($type, array('banner', 'avatar'))) {
            $output = $this->uwp_crop_submit_form($type);
        }
        echo $output;
        exit();
    }

    /**
     * Defines javascript ajaxurl variable.
     *
     * @since       1.0.0
     * @package     userswp
     * @return      void
     */
    public function uwp_define_ajaxurl() {

        echo '<script type="text/javascript">
           var ajaxurl = "' . admin_url('admin-ajax.php') . '";
         </script>';
    }

    /**
     * Adds UsersWP serach form in Users page.
     *
     * @since       1.0.0
     * @package     userswp
     * @return      void
     */
    public function uwp_users_search() {
        ?>
        <div class="uwp-users-list-sort-search">
            <div class="uwp-user-search" id="uwp_user_search">
                <?php
                $keyword = "";
                if (isset($_GET['uwps']) && $_GET['uwps'] != '') {
                    $keyword = stripslashes(strip_tags($_GET['uwps']));
                }
                ?>
                <form method="get" class="searchform search-form" action="<?php echo get_uwp_users_permalink(); ?>">
                    <?php do_action('uwp_users_page_search_form_inner', $keyword); ?>
                </form>
            </div>
            <div class="uwp-user-sort">
                <?php
                $default_layout = uwp_get_option('users_default_layout', 'list');
                ?>
                <form method="get" action="">
                    <select name="uwp_layout" id="uwp_layout">
                        <option <?php selected( $default_layout, "list" ); ?> value="list"><?php echo __("List View", "userswp"); ?></option>
                        <option <?php selected( $default_layout, "2col" ); ?> value="2col"><?php echo __("Grid 2 Col", "userswp"); ?></option>
                        <option <?php selected( $default_layout, "3col" ); ?> value="3col"><?php echo __("Grid 3 Col", "userswp"); ?></option>
                        <option <?php selected( $default_layout, "4col" ); ?> value="4col"><?php echo __("Grid 4 Col", "userswp"); ?></option>
                        <option <?php selected( $default_layout, "5col" ); ?> value="5col"><?php echo __("Grid 5 Col", "userswp"); ?></option>
                    </select>
                    <?php
                    $sort_by = "";
                    if (isset($_GET['uwp_sort_by']) && $_GET['uwp_sort_by'] != '') {
                        $sort_by = strip_tags(esc_sql($_GET['uwp_sort_by']));
                    }
                    ?>
                    <select name="uwp_sort_by" id="uwp_sort_by" onchange="this.form.submit()">
                        <option value=""><?php echo __("Sort By:", "userswp"); ?></option>
                        <option <?php selected( $sort_by, "newer" ); ?> value="newer"><?php echo __("Newer", "userswp"); ?></option>
                        <option <?php selected( $sort_by, "older" ); ?> value="older"><?php echo __("Older", "userswp"); ?></option>
                        <option <?php selected( $sort_by, "alpha_asc" ); ?> value="alpha_asc"><?php echo __("A-Z", "userswp"); ?></option>
                        <option <?php selected( $sort_by, "alpha_desc" ); ?> value="alpha_desc"><?php echo __("Z-A", "userswp"); ?></option>
                    </select>
                </form>
            </div>
        </div>
        <?php
    }

    /**
     * Prints the users page main content.
     *
     * @since       1.0.0
     * @package     userswp
     *
     * @return      void
     */
    public function uwp_users_list() {
        get_uwp_users_list();
    }

    /**
     * Displays custom fields as tabs
     *
     * @since       1.0.0
     * @package     userswp
     * @param       $tabs
     * @param       $user
     * @return      mixed
     */
    public function uwp_extra_fields_as_tabs($tabs, $user)
    {
        global $wpdb;
        $table_name = uwp_get_table_prefix() . 'uwp_form_fields';
        $fields = $wpdb->get_results("SELECT * FROM " . $table_name . " WHERE form_type = 'account' AND is_public != '0' AND show_in LIKE '%[own_tab]%' ORDER BY sort_order ASC");
        $usermeta = uwp_get_usermeta_row($user->ID);
        $privacy = ! empty( $usermeta ) && $usermeta->user_privacy ? explode(',', $usermeta->user_privacy) : array();

        foreach ($fields as $field) {
            if ($field->field_icon != '') {
                $icon = uwp_get_field_icon($field->field_icon);
            } else {
                $field_icon = uwp_field_type_to_fa_icon($field->field_type);
                if ($field_icon) {
                    $icon = '<i class="'.$field_icon.'"></i>';
                } else {
                    $icon = '<i class="fa fa-user"></i>';
                }
            }
            $key = str_replace('uwp_account_', '', $field->htmlvar_name);
            if ($field->is_public == '1') {
                $tabs[$key] = array(
                    'title' => __($field->site_title, 'userswp'),
                    'count' => $icon
                );
            } else {
                if (!in_array($field->htmlvar_name.'_privacy', $privacy)) {
                    $tabs[$key] = array(
                        'title' => __($field->site_title, 'userswp'),
                        'count' => $icon
                    );
                }
            }
        }
        return $tabs;
    }

    /**
     * Prints custom field values as tab content.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       object      $user           The User ID.
     * @param       string      $active_tab     Active tab.
     */
    public function uwp_extra_fields_as_tab_values($user, $active_tab)
    {
        global $wpdb;
        $table_name = uwp_get_table_prefix() . 'uwp_form_fields';
        $fields = $wpdb->get_results("SELECT * FROM " . $table_name . " WHERE form_type = 'account' AND is_public != '0' ORDER BY sort_order ASC");
        $usermeta = uwp_get_usermeta_row($user->ID);
        $privacy = ! empty( $usermeta ) && $usermeta->user_privacy ? explode(',', $usermeta->user_privacy) : array();

        $fieldsets = array();
        $fieldset = false;
        foreach ($fields as $field) {
            $key = str_replace('uwp_account_', '', $field->htmlvar_name);
            if ($field->field_type == 'fieldset') {
                $fieldset = $key;
            }
            $show_in = explode(',',$field->show_in);
            if (in_array("[fieldset]", $show_in)) {
                $fieldsets[$fieldset][] = $field;
            }
            if ($key == $active_tab && $field->field_type != 'fieldset') {
                $value = $this->uwp_get_field_value($field, $user);
                echo '<div class="uwp_profile_tab_field_content">';
                echo $value;
                echo '</div>';
            }
        }

        if (isset($fieldsets[$active_tab])) {
            $fieldset_fields = $fieldsets[$active_tab];
            ?>
            <div class="uwp-profile-extra">
                <div class="uwp-profile-extra-div form-table">
                <?php
                foreach ($fieldset_fields as $field) {
                    $display = false;
                    if ($field->is_public == '1') {
                        $display = true;
                    } else {
                        if (!in_array($field->htmlvar_name.'_privacy', $privacy)) {
                            $display = true;
                        }
                    }
                    if (!$display) {
                        continue;
                    }
                    $value = $this->uwp_get_field_value($field, $user);
                    // Icon
                    $icon = uwp_get_field_icon( $field->field_icon );
                    ?>
                    <div class="uwp-profile-extra-wrap">
                        <div class="uwp-profile-extra-key"><?php echo $icon.$field->site_title; ?><span class="uwp-profile-extra-sep">:</span></div>
                        <div class="uwp-profile-extra-value">
                            <?php
                                echo $value;
                            ?>
                        </div>
                    </div>
                    <?php
                }
                ?>
                </div>
            </div>
            <?php
        }
    }

    /**
     * Gets custom field value based on key.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       object      $field      Field info object.
     * @param       object      $user       The User ID.
     * @return      string                  Custom field value.
     */
    public function uwp_get_field_value($field, $user) {

        $user_data = get_userdata($user->ID);
        $file_obj = new UsersWP_Files();

        if ($field->htmlvar_name == 'uwp_account_email') {
            $value = $user_data->user_email;
        } elseif ($field->htmlvar_name == 'uwp_account_password') {
            $value = '';
            $field->is_required = 0;
        } elseif ($field->htmlvar_name == 'uwp_account_confirm_password') {
            $value = '';
            $field->is_required = 0;
        } else {
            $value = uwp_get_usermeta($user->ID, $field->htmlvar_name, "");
        }
        
        // Select and Multiselect needs Value to be converted
        if ($field->field_type == 'select' || $field->field_type == 'multiselect') {
            $option_values_arr = uwp_string_values_to_options($field->option_values, true);

            // Select
            if ($field->field_type == 'select') {

                if($field->field_type_key != 'country'){
                    if (!empty($value)) {
                        $data = $this->uwp_array_search($option_values_arr, 'value', $value);
                        $value = $data[0]['label'];
                    } else {
                        $value = '';
                    }
                }
            }

            //Multiselect
            if ($field->field_type == 'multiselect' && !empty($value)) {
                if (!empty($value) && is_array($value)) {
                    $array_value = array();
                    foreach ($value as $v) {
                        if(!empty($v)){
                            $data = $this->uwp_array_search($option_values_arr, 'value', $v);
                            $array_value[] = $data[0]['label'];
                        }

                    }
                    if(!empty($array_value)){
                        $value = implode(', ', $array_value);
                    }else{
                        $value = '';
                    }

                } else {
                    $value = '';
                }
            }
        }

        // Date
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


        // Time
        if ($field->field_type == 'time') {
            $value = date(get_option('time_format'), strtotime($value));
        }

        // URL
        if ($field->field_type == 'url' && !empty($value)) {
            $link_text = $value;
            // if deafult_value is not url then it will be used as link text. 
            if ($field->default_value && !empty($field->default_value) ) {
                if (substr( $field->default_value, 0, 4 ) === "http") {
                    $link_text = $value;
                } else {
                    $link_text = $field->default_value;
                }
            }
            if (substr( $link_text, 0, 4 ) === "http") {
                $link_text = esc_url($link_text);
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
            $value = $file_obj->uwp_file_upload_preview($field, $value, false);
        }

        // Sanitize
        switch ($field->field_type) {
            case 'url':
                // already escaped
                break;
            case 'file':
                // already escaped
                break;
            case 'textarea':
                $value = esc_textarea( $value );
                break;
            default:
                $value = esc_html( $value );
        }

        if($field->field_type_key == 'country'){
            $value = uwp_output_country_html($value);
        }

        return $value;
    }

}