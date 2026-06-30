<?php
/**
 * Invite Codes frontend template.
 *
 * Displays user's invite codes and the generation form.
 * Override by placing a copy in your-theme/userswp/invite-codes.php.
 *
 * @since 1.2.66
 * @package userswp
 *
 * @var int   $user_id    Current user ID.
 * @var int   $max_codes  Max codes this user can create.
 * @var int   $user_count Current active code count.
 * @var bool  $can_create Whether user can create more codes.
 * @var array $codes      Array of invite code row objects.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="uwp-invite-codes-wrap">

	<h3><?php esc_html_e( 'Your Invite Codes', 'userswp' ); ?></h3>
	<p class="uwp-invite-codes-count">
		<?php
		echo esc_html(
			sprintf(
				/* translators: %1$d: current count, %2$d: max */
				__( 'You have %1$d of %2$d active invite codes.', 'userswp' ),
				$user_count,
				$max_codes
			)
		);
		?>
	</p>

	<?php if ( $can_create ) : ?>
		<form method="post" action="" class="uwp-invite-codes-form">
			<?php wp_nonce_field( 'uwp_invite_codes_frontend', 'uwp_invite_nonce' ); ?>
			<p>
				<label for="uwp_invite_usage_limit"><?php esc_html_e( 'Usage limit', 'userswp' ); ?></label>
				<input type="number" name="uwp_invite_usage_limit" id="uwp_invite_usage_limit" value="1" min="1" max="100" class="form-control" style="width:100px;" />
				<span class="description"><?php esc_html_e( 'How many times this code can be used.', 'userswp' ); ?></span>
			</p>
			<p>
				<label for="uwp_invite_expiry"><?php esc_html_e( 'Expiry date (optional)', 'userswp' ); ?></label>
				<input type="date" name="uwp_invite_expiry" id="uwp_invite_expiry" class="form-control" style="width:200px;" />
			</p>
			<p>
				<input type="submit" name="uwp_invite_generate_submit" value="<?php esc_attr_e( 'Generate Code', 'userswp' ); ?>" class="btn btn-primary" />
			</p>
		</form>
	<?php else : ?>
		<p class="uwp-invite-codes-limit">
			<?php esc_html_e( 'You have reached the maximum number of invite codes. Delete old codes to create new ones.', 'userswp' ); ?>
		</p>
	<?php endif; ?>

	<?php if ( ! empty( $codes ) ) : ?>
		<table class="table table-bordered uwp-invite-codes-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Code', 'userswp' ); ?></th>
					<th><?php esc_html_e( 'Link', 'userswp' ); ?></th>
					<th><?php esc_html_e( 'Used', 'userswp' ); ?></th>
					<th><?php esc_html_e( 'Limit', 'userswp' ); ?></th>
					<th><?php esc_html_e( 'Expires', 'userswp' ); ?></th>
					<th><?php esc_html_e( 'Action', 'userswp' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $codes as $code ) : ?>
					<tr>
						<td><code><?php echo esc_html( $code->code ); ?></code></td>
						<td>
							<input type="text" readonly="readonly" class="form-control uwp-invite-link"
								value="<?php echo esc_url( uwp_get_invite_code_register_url( $code->code ) ); ?>"
								onclick="this.select();"
								style="width:100%;"
							/>
						</td>
						<td><?php echo (int) $code->usage_count; ?></td>
						<td><?php echo (int) $code->usage_limit > 0 ? (int) $code->usage_limit : '&infin;'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML entity ?></td>
						<td>
							<?php
							if ( ! empty( $code->expiry_date ) && '0000-00-00 00:00:00' !== $code->expiry_date ) {
								echo esc_html( mysql2date( get_option( 'date_format' ), $code->expiry_date ) );
							} else {
								esc_html_e( 'Never', 'userswp' );
							}
							?>
						</td>
						<td>
							<form method="post" action="" onsubmit="return confirm('<?php echo esc_js( __( 'Delete this invite code? This cannot be undone.', 'userswp' ) ); ?>');">
								<?php wp_nonce_field( 'uwp_invite_delete_' . (int) $code->id, 'uwp_invite_delete_nonce' ); ?>
								<input type="hidden" name="code_id" value="<?php echo (int) $code->id; ?>" />
								<input type="submit" name="uwp_invite_delete_submit" value="<?php esc_attr_e( 'Delete', 'userswp' ); ?>" class="btn btn-danger btn-sm" />
							</form>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php else : ?>
		<p><?php esc_html_e( 'You have not created any invite codes yet.', 'userswp' ); ?></p>
	<?php endif; ?>

</div>
