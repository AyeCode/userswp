<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
global $uwp_widget_args;
$comment = isset( $uwp_widget_args['template_args']['comment'] ) ? $uwp_widget_args['template_args']['comment'] : '';

if ( has_post_thumbnail($comment->comment_post_ID) ) {
	$thumb_url = get_the_post_thumbnail_url($comment->comment_post_ID, array(80, 80));
} else {
	$thumb_url = uwp_get_default_thumb_uri();
}

$avatar_url = get_avatar_url( $comment->comment_author_email, array( 'size' => 500 ) );
//print_r($comment);
$user_name = isset($comment->comment_author) ? $comment->comment_author : '';
$user = !empty($comment->user_id) ? get_userdata( $comment->user_id ) : '';
if(!empty($user->display_name)) { $user_name = $user->display_name;}

?>
<div class="card mb-5">

	<div class="card-header">
			<a href="<?php echo get_comment_link($comment->comment_ID); ?>"><?php echo get_the_title($comment->comment_post_ID); ?></a>
	</div>

	<div class="card-body">
		<div class="row justify-content-center">
			<div class="col-5 col-md-2 mb-3 col-xl-1 ">
				<img src="<?php echo esc_url_raw( $avatar_url ); ?>" class="align-self-start img-thumbnail rounded-circle mx-auto d-block" alt="...">
			</div>
			<div class="col-12 col-md-10 col-xl-11 text-muted">
				<?php
				do_action( 'uwp_before_comment_summary', $comment );
				$excerpt = strip_shortcodes(wp_trim_words( $comment->comment_content, 100, '...' ));
				echo $excerpt;
				do_action( 'uwp_after_comment_summary', $comment );
				?>
				<footer class="blockquote-footer"><cite><?php echo esc_attr($user_name);?></cite></footer>
			</div>
		</div>
	</div>

	<div class="card-footer bg-white">
		<time class="uwp-profile-item-time published" datetime="<?php echo get_the_time( 'c' ); ?>">
			<?php echo date_i18n( get_option( 'date_format' ), strtotime( get_comment_date("", $comment->comment_ID) ) ); ?>
		</time>
		<a href="<?php echo get_comment_link($comment->comment_ID); ?>" class="btn btn-sm btn-outline-primary float-right"><i class="fas fa-comments"></i> <?php esc_attr_e("View Comment","userswp");?></a>
	</div>

</div>