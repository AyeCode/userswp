<div class="uwp-user-search" id="uwp_user_search">
    <?php
    $keyword = "";
    if (isset($_GET['uwps']) && $_GET['uwps'] != '') {
        $keyword = esc_attr( apply_filters( 'get_search_query',$_GET['uwps']) );
    }
    ?>
    <form method="get" class="searchform search-form" action="<?php echo get_uwp_users_permalink(); ?>">
        <input placeholder="<?php _e('Search For', 'userswp'); ?>" name="uwps" value="<?php echo $keyword; ?>" class="s search-input" type="text">
        <?php do_action('uwp_users_page_search_form_inner', $keyword); ?>
        <input class="uwp-searchsubmit uwp-search-submit" value="<?php _e('Search', 'userswp'); ?>" type="submit">
    </form>
</div>