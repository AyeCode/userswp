<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$default_layout = uwp_get_option('users_default_layout', 'list');

$sort_by = "";
if (isset($_GET['uwp_sort_by']) && $_GET['uwp_sort_by'] != '') {
	$sort_by = strip_tags(esc_attr($_GET['uwp_sort_by']));
}

do_action('uwp_users_loop_actions');
?>

<div class="uwp-user-views" id="uwp_user_views">
	<form method="get" action="">
		<select name="uwp_layout" id="uwp_layout" class="aui-select2">
			<option <?php selected( $default_layout, "list" ); ?> value="list"><?php echo __("List View", "userswp"); ?></option>
			<option <?php selected( $default_layout, "2col" ); ?> value="2col"><?php echo sprintf(__("Grid %d","userswp"),2); ?></option>
			<option <?php selected( $default_layout, "3col" ); ?> value="3col"><?php echo sprintf(__("Grid %d","userswp"),3); ?></option>
			<option <?php selected( $default_layout, "4col" ); ?> value="4col"><?php echo sprintf(__("Grid %d","userswp"),4); ?></option>
			<option <?php selected( $default_layout, "5col" ); ?> value="5col"><?php echo sprintf(__("Grid %d","userswp"),5); ?></option>
		</select>
	</form>
</div>

<div class="uwp-user-sort" id="uwp_user_sort">
	<form method="get" action="">
		<select name="uwp_sort_by" id="uwp_sort_by" class="aui-select2" onchange="this.form.submit()">
			<option value=""><?php _e("Sort By:", "userswp"); ?></option>
			<option <?php selected( $sort_by, "newer" ); ?> value="newer"><?php echo __("Newer", "userswp"); ?></option>
			<option <?php selected( $sort_by, "older" ); ?> value="older"><?php echo __("Older", "userswp"); ?></option>
			<option <?php selected( $sort_by, "alpha_asc" ); ?> value="alpha_asc"><?php echo __("A-Z", "userswp"); ?></option>
			<option <?php selected( $sort_by, "alpha_desc" ); ?> value="alpha_desc"><?php echo __("Z-A", "userswp"); ?></option>
		</select>
	</form>
</div>
<?php do_action('uwp_after_users_loop_actions'); ?>