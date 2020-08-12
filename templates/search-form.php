<div class="uwp-user-search" id="uwp_user_search">
	<?php
	$keyword = "";
	if (isset($_GET['uwps']) && $_GET['uwps'] != '') {
		$keyword = esc_attr( apply_filters( 'get_search_query', $_GET['uwps']) );
	}
	?>
    <form method="get" class="uwp-user-search-form form-inline" action="<?php echo uwp_get_users_page_url(); ?>">
        <div class="form-group mb-2 mr-md-2">
            <label for="uwp-search-input" class="sr-only"><?php _e('Search for users...', 'userswp'); ?></label>
            <input type="search" name="uwps" class="form-control form-control-sm " id="uwp-search-input" value="<?php echo $keyword; ?>" placeholder="<?php _e('Search for users...', 'userswp'); ?>">
        </div>
		<?php if(!empty($_GET['uwp_sort_by'])) { ?>
            <input type="hidden" name="uwp_sort_by" value="<?php esc_attr_e($_GET['uwp_sort_by']); ?>">
		<?php } ?>
        <button type="submit" class="btn btn-sm btn-outline-primary mb-2 uwp-search-submit"><?php _e('Search', 'userswp'); ?></button>
		<?php do_action('uwp_after_search_button'); ?>
    </form>
</div>