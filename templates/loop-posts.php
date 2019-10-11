<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
global $uwp_widget_args;
$the_query = isset( $uwp_widget_args['template_args']['the_query'] ) ? $uwp_widget_args['template_args']['the_query'] : '';
$title = isset( $uwp_widget_args['template_args']['title'] ) ? $uwp_widget_args['template_args']['title'] : '';
?>
<h3><?php echo $title; ?></h3>
<div class="uwp-profile-item-block">
	<?php
	// The Loop
	if ($the_query && $the_query->have_posts()) {

		$template = "posts-post";

		echo '<ul class="uwp-profile-item-ul">';
		while ($the_query->have_posts()) {
			$the_query->the_post();
			uwp_locate_template($template);
		}
		echo '</ul>';

		/* Restore original Post Data */
		wp_reset_postdata();
	} else {
		// no posts found
		echo "<p>".__('No '.$title.' Found', 'userswp')."</p>";
	}
	do_action('uwp_profile_pagination', $the_query->max_num_pages);
	?>
</div>