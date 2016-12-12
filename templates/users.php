<?php do_action('uwp_template_before', 'users'); ?>
<div class="uwp-content-wrap">
    <div class="uwp-users-list">
        <div class="uwp-users-list-sort-search">
            <div class="uwp-user-search">
                <?php
                $keyword = "";
                if (isset($_GET['uwps']) && $_GET['uwps'] != '') {
                    $keyword = sanitize_title($_GET['uwps']);
                }
                ?>
                <form method="get" class="searchform search-form" action="">
                    <input placeholder="Search For" name="uwps" value="<?php echo $keyword; ?>" class="s search-input" type="text"><input class="searchsubmit search-submit" value="Search" type="submit"><br>
                </form>
            </div>
            <div class="uwp-user-sort">
                <form method="get" action="">
                    <?php
                    $sort_by = "";
                    if (isset($_GET['uwp_sort_by']) && $_GET['uwp_sort_by'] != '') {
                        $sort_by = sanitize_title($_GET['uwp_sort_by']);
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
        <ul class="uwp-users-list-wrap">
            <?php
            $users = uwp_get_users();

            if ($users) {
                foreach ($users as $user) {
                    $user_obj = get_user_by('id', $user['id']);
                    ?>
                    <li class="uwp-users-list-user">
                        <div class="uwp-users-list-user-left">
                            <div class="uwp-users-list-user-avatar"><?php echo $user['avatar']; ?></div>
                        </div>
                        <div class="uwp-users-list-user-right">
                            <div class="uwp-users-list-user-name">
                                <h3><a href="<?php echo apply_filters('uwp_profile_link', $user['link'], $user['id']); ?>"><?php echo $user['name']; ?></a></h3>
                            </div>
                            <div class="uwp-users-list-user-social">
                                <?php do_action('uwp_profile_social', $user_obj ); ?>
                            </div>
                            <div class="uwp-users-list-user-bio">
                                <?php do_action('uwp_profile_bio', $user_obj ); ?>
                            </div>
                            <div class="clfx"></div>
                        </div>
                    </li>
                    <?php
                }
            } else {
                // no users found
                echo '<div class="uwp-alert-error text-center">';
                echo __('No Users Found', 'uwp');
                echo '</div>';
            }
            ?>
        </ul>
    </div>
</div>
<?php do_action('uwp_template_after', 'users'); ?>