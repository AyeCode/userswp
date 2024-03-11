<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$the_query = isset( $args['template_args']['the_query'] ) ? $args['template_args']['the_query'] : '';
$maximum_pages = isset( $args['template_args']['maximum_pages'] ) ? $args['template_args']['maximum_pages'] : '';
$title = isset( $args['template_args']['title'] ) ? $args['template_args']['title'] : __('Comments', 'userswp');
?>
<h3><?php echo esc_attr( $title );?></h3>

<div class="uwp-profile-item-block">
	<?php
	// The Loop
	if ($the_query) {
		echo '<ul class="uwp-profile-item-ul">';
		foreach ( $the_query as $comment ) {
			$args['template_args']['comment'] = $comment;
			uwp_get_template("comments-item.php", $args);
			?>
			
			<?php
		}
		echo '</ul>';
	} else {
		// no comments found
		echo "<p>".esc_html__('No comments found', 'userswp')."</p>";
	}

	do_action('uwp_profile_pagination', $maximum_pages);
	?>
</div>