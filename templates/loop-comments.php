<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
global $uwp_widget_args;
$the_query = isset( $uwp_widget_args['template_args']['the_query'] ) ? $uwp_widget_args['template_args']['the_query'] : '';
$maximum_pages = isset( $uwp_widget_args['template_args']['maximum_pages'] ) ? $uwp_widget_args['template_args']['maximum_pages'] : '';
?>
<h3><?php echo __('Comments', 'userswp') ?></h3>

<div class="uwp-profile-item-block">
	<?php
	// The Loop
	if ($the_query) {
		echo '<ul class="uwp-profile-item-ul">';
		foreach ( $the_query as $comment ) {
			$uwp_widget_args['template_args']['comment'] = $comment;
			uwp_get_template("comments-item.php");
			?>
			
			<?php
		}
		echo '</ul>';
	} else {
		// no comments found
		echo "<p>".__('No Comments Found', 'userswp')."</p>";
	}

	do_action('uwp_profile_pagination', $maximum_pages);
	?>
</div>