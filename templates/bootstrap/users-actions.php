<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

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
        <div class="col-sm-9 p-0">
			<?php
			if(is_uwp_users_page() || is_admin()){
				echo do_shortcode('[uwp_users_search]');
			}
			?>
        </div>
        <div class="col-sm-3 p-0">

            <div class="btn-toolbar justify-content-end" role="toolbar" aria-label="Toolbar with button groups">

                <div class="btn-group btn-group-sm mr-2 uwp-user-sort" role="group">
                    <button id="uwp-user-sort" type="button" class="btn btn-outline-primary rounded-right" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						<?php if(!empty($sort_by_options[$sort_by])){echo esc_attr( $sort_by_options[$sort_by] );}else{_e("Sort By", "userswp");} echo ' <i class="fas fa-sort"></i>'; ?>
                    </button>
                    <div class="dropdown-menu mt-3" aria-labelledby="uwp-user-sort">
                        <h6 class="dropdown-header"><?php _e("Sort Options", "userswp"); ?></h6>
						<?php
						$base_link = uwp_get_page_id('users_page',true);

						$query_string = array();
						$default_query = array('uwp_sort_by');
						if(!empty($_SERVER['QUERY_STRING'])) {
							$query_string_temp = explode('&',$_SERVER['QUERY_STRING']);
							if(!empty($query_string_temp) && is_array($query_string_temp)) {
								foreach ($query_string_temp as $string ) {
									$string_temp = explode('=',$string);
									$key = !empty($string_temp[0]) ? $string_temp[0] : '';
									$value = !empty($string_temp[1]) ? $string_temp[1] : '';
									if(!empty($key) && !empty($value) && !in_array($key,$default_query)) {
										$query_string[$key] = !empty($value)? esc_attr($value) : '';
									}
								}
							}
						}

						if(!empty($query_string)){
							$base_link = add_query_arg($query_string,$base_link);
						} // search param

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