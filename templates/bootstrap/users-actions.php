<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$sort_by = "";
$sort_by_options = uwp_get_sort_by_order_list();

if (isset($_GET['uwp_sort_by']) && $_GET['uwp_sort_by'] != '') {
	$sort_by = strip_tags(esc_attr($_GET['uwp_sort_by']));
	if(!isset($sort_by_options[$sort_by])){$sort_by = "";} // validate
}

do_action('uwp_users_loop_actions');
?>
<div class="container mb-3 overflow-visible">
    <div class="row">
        <div class="col-sm-8 p-0">
			<?php
			if(is_uwp_users_page() || is_admin()){
				echo do_shortcode('[uwp_users_search]');
			}
			?>
        </div>
        <div class="col-sm-4 p-0">

            <div class="btn-toolbar justify-content-end" role="toolbar" aria-label="Toolbar with button groups">

                <div class="btn-group btn-group-sm mr-2 uwp-user-sort" role="group">
                    <?php
                    if(!empty($sort_by_options[$sort_by])){
                        $content =  esc_attr( $sort_by_options[$sort_by] );
                    }else{
                        $content =  __("Sort By", "userswp");
                    }
                    
                    echo aui()->button(array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                        'type'  =>  'button',
                        'id'    =>  'uwp-user-sort',
                        'icon'       => 'fas fa-sort',
                        'class'      => 'btn btn-outline-primary rounded-right',
                        'content'    => $content, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                        'extra_attributes'  => array('data-toggle'=>'dropdown', 'aria-haspopup'=>'true', 'aria-expanded'=>'false')
                    ));
                    ?>
                    <div class="dropdown-menu mt-3" aria-labelledby="uwp-user-sort">
                        <h6 class="dropdown-header"><?php esc_html_e("Sort Options", "userswp"); ?></h6>
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
							echo aui()->button(array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								'type'  =>  'a',
								'href'    =>  esc_url_raw(add_query_arg(array('uwp_sort_by'=>$key),$base_link)),
								'class'      => esc_html( 'dropdown-item '.$active ),
								'content'    => esc_attr($val),
							));
						}

						if(!empty($_REQUEST['uwp_sort_by'])){
							echo '<div class="dropdown-divider"></div>';
						    echo aui()->button(array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								'type'  =>  'a',
								'href'    =>  esc_url_raw($base_link),
								'class'      => 'dropdown-item',
								'content'    => esc_html__("Clear Sort", "userswp"),
							));
						} ?>
                    </div>
                </div>

                <div class="btn-group btn-group-sm uwp-list-view-select" role="group" aria-label="First group">
                    <div class="btn-group btn-group-sm" role="group">
	                    <?php
	                    echo aui()->button(array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		                    'type'  =>  'button',
		                    'id'      => 'uwp-list-view-select-grid',
		                    'class'      => 'btn btn-outline-primary rounded-right uwp-list-view-select-grid',
		                    'icon'    => 'fas fa-th',
		                    'extra_attributes'  => array('data-toggle'=>'dropdown', 'aria-haspopup'=>'true', 'aria-expanded'=>'false')
	                    ));
	                    ?>
                        <div class="dropdown-menu dropdown-menu-right mt-3 p-0" aria-labelledby="uwp-list-view-select-grid">
                            <?php
                            echo aui()->button(array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	                            'type'  =>  'button',
	                            'class'      => 'dropdown-item',
	                            'content'    => esc_html( sprintf(__("Grid %d","userswp"),1) ),
	                            'onclick'    => 'uwp_list_view_select(1);return false;',
	                            'extra_attributes'  => array('data-gridview'=>'1')
                            ));
                            echo aui()->button(array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	                            'type'  =>  'button',
	                            'class'      => 'dropdown-item',
	                            'content'    => esc_html( sprintf(__("Grid %d","userswp"),2) ),
	                            'onclick'    => 'uwp_list_view_select(2);return false;',
	                            'extra_attributes'  => array('data-gridview'=>'2')
                            ));
                            echo aui()->button(array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	                            'type'  =>  'button',
	                            'class'      => 'dropdown-item',
	                            'content'    => esc_html( sprintf(__("Grid %d","userswp"),3) ),
	                            'onclick'    => 'uwp_list_view_select(3);return false;',
	                            'extra_attributes'  => array('data-gridview'=>'3')
                            ));
                            echo aui()->button(array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	                            'type'  =>  'button',
	                            'class'      => 'dropdown-item',
	                            'content'    => esc_html( sprintf(__("Grid %d","userswp"),4) ),
	                            'onclick'    => 'uwp_list_view_select(4);return false;',
	                            'extra_attributes'  => array('data-gridview'=>'4')
                            ));
                            echo aui()->button(array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	                            'type'  =>  'button',
	                            'class'      => 'dropdown-item',
	                            'content'    => esc_html( sprintf(__("Grid %d","userswp"),5) ),
	                            'onclick'    => 'uwp_list_view_select(5);return false;',
	                            'extra_attributes'  => array('data-gridview'=>'5')
                            ));
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
	    <?php do_action('uwp_advanced_search_form'); ?>
    </div>
</div>
<?php do_action('uwp_after_users_loop_actions'); ?>