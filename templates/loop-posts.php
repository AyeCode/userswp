<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$the_query = isset( $args['template_args']['the_query'] ) ? $args['template_args']['the_query'] : '';
$title = isset( $args['template_args']['title'] ) ? $args['template_args']['title'] : '';
?>
<h3><?php echo esc_html( $title ); ?></h3>
<div class="uwp-profile-item-block">
	<?php
	// The Loop
	if ($the_query && $the_query->have_posts()) {

		echo '<ul class="uwp-profile-item-ul">';
		while ($the_query->have_posts()) {
			$the_query->the_post();
			uwp_get_template('posts-post.php', $args);
		}
		echo '</ul>';

		/* Restore original Post Data */
		wp_reset_postdata();
	} else {
		// no posts found
		echo "<p>" . esc_html( sprintf( __( "No %s found.", 'userswp' ), $title ) )."</p>";
	}
	do_action('uwp_profile_pagination', $the_query->max_num_pages);
	?>
</div>