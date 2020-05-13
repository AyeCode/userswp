<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$the_query = isset( $args['template_args']['the_query'] ) ? $args['template_args']['the_query'] : '';
$maximum_pages = isset( $args['template_args']['maximum_pages'] ) ? $args['template_args']['maximum_pages'] : '';
$title = isset( $args['template_args']['title'] ) ? $args['template_args']['title'] : __('Comments', 'userswp');
?>
<h3><?php echo $title;?></h3>

<div class="uwp-profile-comments-loop">
	<?php
	// The Loop
	if ($the_query) {
		$design_style = ! empty( $args['design_style'] ) ? esc_attr( $args['design_style'] ) : uwp_get_option( "design_style", 'bootstrap' );
		$template     = $design_style ? $design_style . "/comments-item.php" : "comments-item.php";
		echo '<div class="cards">';
		foreach ( $the_query as $comment ) {
			$args['template_args']['comment'] = $comment;
			uwp_get_template($template, $args);
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