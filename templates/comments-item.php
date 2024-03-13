<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
global $comment;
$comment = isset( $args['template_args']['comment'] ) ? $args['template_args']['comment'] : '';
?>
<li class="uwp-profile-item-li uwp-profile-item-clearfix">
	<a class="uwp-profile-item-img" href="<?php echo esc_url( get_comment_link($comment->comment_ID) ); ?>">
		<?php
		if ( has_post_thumbnail($comment->comment_post_ID) ) {
			$thumb_url = get_the_post_thumbnail_url($comment->comment_post_ID, array(80, 80));
		} else {
			$thumb_url = uwp_get_default_thumb_uri();
		}
		?>
		<img class="uwp-profile-item-alignleft uwp-profile-item-thumb" src="<?php echo esc_url( $thumb_url ); ?>">
	</a>

	<h3 class="uwp-profile-item-title">
		<a href="<?php echo esc_url( get_comment_link($comment->comment_ID) ); ?>"><?php echo esc_html( get_the_title($comment->comment_post_ID) ); ?></a>
	</h3>
	<time class="uwp-profile-item-time published" datetime="<?php echo esc_attr( get_comment_time('c') ); ?>">
		<?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( get_comment_date("", $comment->comment_ID) ) ) ); ?>
	</time>
	<div class="uwp-profile-item-summary">
		<?php
		$excerpt = strip_shortcodes(wp_trim_words( $comment->comment_content, 100, '...' ));
		echo esc_attr( $excerpt );
		?>
	</div>
</li>