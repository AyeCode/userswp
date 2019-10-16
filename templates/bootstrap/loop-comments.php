<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
global $uwp_widget_args;
$the_query = isset( $uwp_widget_args['template_args']['the_query'] ) ? $uwp_widget_args['template_args']['the_query'] : '';
$maximum_pages = isset( $uwp_widget_args['template_args']['maximum_pages'] ) ? $uwp_widget_args['template_args']['maximum_pages'] : '';
$title = isset( $uwp_widget_args['template_args']['title'] ) ? $uwp_widget_args['template_args']['title'] : __('Comments', 'userswp');
?>
<h3><?php echo $title;?></h3>

<div class="uwp-profile-comments-loop">
	<?php
	// The Loop
	if ($the_query) {
		$design_style = ! empty( $uwp_widget_args['design_style'] ) ? esc_attr( $uwp_widget_args['design_style'] ) : uwp_get_option( "design_style", 'bootstrap' );
		$template     = $design_style ? $design_style . "/comments-item" : "comments-item";
		echo '<div class="cards">';
		foreach ( $the_query as $comment ) {
			$uwp_widget_args['template_args']['comment'] = $comment;
			uwp_locate_template($template);
		}
		echo '</div>';
	} else {
		// no comments found
		echo aui()->alert(array(
			'type'=>'info',
			'content'=> sprintf(__('No %s found', 'userswp'), strtolower($title))
		));
	}

	do_action('uwp_profile_pagination', $maximum_pages);
	?>
</div>