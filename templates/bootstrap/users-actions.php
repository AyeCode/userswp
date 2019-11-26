<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
$default_layout = uwp_get_option('users_default_layout', 'list');

$sort_by = "";
$sort_by_options = array(
	'newer' => __("Newest", "userswp"),
	'older' => __("Oldest", "userswp"),
	'alpha_asc' => __("A-Z", "userswp"),
	'alpha_desc' => __("Z-A", "userswp"),
);
if (isset($_GET['uwp_sort_by']) && $_GET['uwp_sort_by'] != '') {
	$sort_by = strip_tags(esc_attr($_GET['uwp_sort_by']));
	if(!isset($sort_by_options[$sort_by])){$sort_by = "";} // validate
}



do_action('uwp_users_loop_actions');
?>
<div class="container mb-3 overflow-visible">
	<div class="row">
		<div class="col-sm p-0">
			<?php
			if(is_uwp_users_page() || is_admin()){
				echo do_shortcode('[uwp_users_search]');
			}
			?>
		</div>
		<div class="col p-0 d-none d-sm-block">


			<div class="btn-toolbar justify-content-end" role="toolbar" aria-label="Toolbar with button groups">


				<div class="btn-group btn-group-sm mr-2 uwp-user-sort" role="group">
					<button id="uwp-user-sort" type="button" class="btn btn-outline-primary rounded-right" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						<?php if(!empty($sort_by_options[$sort_by])){echo esc_attr( $sort_by_options[$sort_by] );}else{_e("Sort By", "userswp");} echo ' <i class="fas fa-sort"></i>'; ?>
					</button>
					<div class="dropdown-menu mt-3" aria-labelledby="uwp-user-sort">
						<h6 class="dropdown-header"><?php _e("Sort Options", "userswp"); ?></h6>
						<?php
						$base_link = uwp_get_page_id('users_page',true);
						if(!empty($_REQUEST['uwps'])){$base_link = add_query_arg(array('uwps'=>esc_attr($_REQUEST['uwps'])),$base_link);} // search param
						if(!empty($_REQUEST['uwp_sort_by'])){$base_link = remove_query_arg(array('uwp_sort_by'),$base_link);} // search param

						foreach ($sort_by_options as $key => $val){
							$active = isset($_REQUEST['uwp_sort_by']) && $_REQUEST['uwp_sort_by']==$key ? 'active' : '';
							echo '<a class="dropdown-item '.$active.'" href="'.esc_url_raw(add_query_arg(array('uwp_sort_by'=>$key),$base_link)).'">'.esc_attr($val).'</a>';
						}

						if(!empty($_REQUEST['uwp_sort_by'])){
						?>
						<div class="dropdown-divider"></div>
						<a class="dropdown-item" href="<?php echo esc_url_raw($base_link); ?>"><?php _e("Clear Sort", "userswp"); ?></a>
						<?php }?>
					</div>
				</div>


				<div class="btn-group btn-group-sm uwp-list-view-select" role="group" aria-label="First group">
<!--					<button type="button" class="btn btn-outline-primary uwp-list-view-select-list  " onclick="uwp_list_view_select(0);"><i class="fas fa-th-list"></i></button>-->
					<div class="btn-group btn-group-sm" role="group">
						<button id="uwp-list-view-select-grid" type="button" class="btn btn-outline-primary rounded-right uwp-list-view-select-grid" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							<i class="fas fa-th"></i>
						</button>
						<div class="dropdown-menu dropdown-menu-right mt-3 p-0" aria-labelledby="uwp-list-view-select-grid">
							<button class="dropdown-item" data-gridview="1" onclick="uwp_list_view_select(1);return false;"><?php echo sprintf(__("Grid %d","userswp"),1);?></button>
							<button class="dropdown-item" data-gridview="2" onclick="uwp_list_view_select(2);return false;"><?php echo sprintf(__("Grid %d","userswp"),2);?></button>
							<button class="dropdown-item" data-gridview="3" onclick="uwp_list_view_select(3);return false;"><?php echo sprintf(__("Grid %d","userswp"),3);?></button>
							<button class="dropdown-item" data-gridview="4" onclick="uwp_list_view_select(4);return false;"><?php echo sprintf(__("Grid %d","userswp"),4);?></button>
							<button class="dropdown-item" data-gridview="5" onclick="uwp_list_view_select(5);return false;"><?php echo sprintf(__("Grid %d","userswp"),5);?></button>
						</div>
					</div>
				</div>
				
			</div>
		</div>
	</div>
</div>

<?php /*

<div class="uwp-user-views" id="uwp_user_views">
	<form method="get" action="">
		<select name="uwp_layout" id="uwp_layout" class="aui-select2">
			<option <?php selected( $default_layout, "list" ); ?> value="list"><?php echo __("List View", "userswp"); ?></option>
			<option <?php selected( $default_layout, "2col" ); ?> value="2col"><?php echo __("Grid 2 Col", "userswp"); ?></option>
			<option <?php selected( $default_layout, "3col" ); ?> value="3col"><?php echo __("Grid 3 Col", "userswp"); ?></option>
			<option <?php selected( $default_layout, "4col" ); ?> value="4col"><?php echo __("Grid 4 Col", "userswp"); ?></option>
			<option <?php selected( $default_layout, "5col" ); ?> value="5col"><?php echo __("Grid 5 Col", "userswp"); ?></option>
		</select>
	</form>
</div>



<div class="uwp-user-sort" id="uwp_user_sort">
	<form method="get" action="">
		<select name="uwp_sort_by" id="uwp_sort_by" class="aui-select2" onchange="this.form.submit()">
			<option value=""><?php echo __("Sort By:", "userswp"); ?></option>
			<option <?php selected( $sort_by, "newer" ); ?> value="newer"><?php echo __("Newer", "userswp"); ?></option>
			<option <?php selected( $sort_by, "older" ); ?> value="older"><?php echo __("Older", "userswp"); ?></option>
			<option <?php selected( $sort_by, "alpha_asc" ); ?> value="alpha_asc"><?php echo __("A-Z", "userswp"); ?></option>
			<option <?php selected( $sort_by, "alpha_desc" ); ?> value="alpha_desc"><?php echo __("Z-A", "userswp"); ?></option>
		</select>
	</form>
</div>
 */?>