<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
global $comment;
$comment = isset( $args['template_args']['comment'] ) ? $args['template_args']['comment'] : '';
$avatar_url = get_avatar_url( $comment->comment_author_email, array( 'size' => 500 ) );
?>
<div class="card mb-5">

	<div class="card-header">
        <a href="<?php echo esc_url( get_comment_link($comment->comment_ID) ); ?>"><?php echo esc_html( get_the_title($comment->comment_post_ID) ); ?></a>
	</div>

	<div class="card-body">
		<div class="row justify-content-center">
			<div class="col-5 col-md-2 mb-3 col-xl-2 text-center">
				<?php 
					$args = array('class' => 'align-self-start img-thumbnail rounded-circle mx-auto d-block');
					echo get_avatar($comment->user_id,'',esc_url($avatar_url),'',$args);
				?>
                <cite><?php echo uwp_get_username($comment->user_id); ?></cite>
			</div>
			<div class="col-12 col-md-10 col-xl-10 text-muted">
				<?php
				do_action( 'uwp_before_comment_summary', $comment );
				comment_text();
				do_action( 'uwp_after_comment_summary', $comment );
				?>
			</div>
		</div>
	</div>

	<div class="card-footer bg-white">
		<?php
		$footer_html = '<time class="uwp-profile-item-time published timeago" datetime="'.get_comment_time( 'c' ).'">'.date_i18n( get_option( 'date_format' ), strtotime( get_comment_date("", $comment->comment_ID) ) ).'</time>';
		$footer_html .= aui()->button(array(
                            'type'  =>  'a',
                            'href'       => get_comment_link($comment->comment_ID),
                            'class'      => 'btn btn-sm btn-outline-primary float-right',
                            'icon'       => 'fas fa-comments',
                            'title'      => __( 'View Comment', 'userswp' ),
                            'content'    => __( 'View Comment', 'userswp' ),
                        ));

		echo apply_filters('uwp_tp_comments_item_footer', $footer_html,$comment); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		?>
	</div>
</div>