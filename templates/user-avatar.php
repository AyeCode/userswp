<?php
/**
 * Profile header template (default)
 *
 * @ver 0.0.1
 */
global $uwp_in_user_loop;
$css_class    = ! empty( $args['css_class'] ) ? esc_attr( $args['css_class'] ) : 'border-0';
$allow_change = $args['allow_change'];
$avatar_url   = $args['avatar_url'];
$user_id      = $args['user_id'];
$link         = $args['link'];
$size         = isset($args['size']) ? $args['size'] : 50;

if($user_id){
    $user = get_userdata($user_id);
} else {
	$user = uwp_get_displayed_user();
}

if ( ! $user ) {
	return;
}
?>
<div class="uwp-profile-avatar clearfix">
    <?php
        if ($uwp_in_user_loop || 1==$link) {
            echo '<a href="' . esc_url( uwp_build_profile_tab_url($user->ID) ) . '" title="' . esc_attr( $user->display_name ) . '">';
        }
        ?>
        <div class="uwp-profile-avatar">
            <img class="avatar avatar-<?php echo absint( $size ); ?> photo" src="<?php echo esc_url( $avatar_url ); ?>" alt="<?php esc_attr_e("User avatar","userswp");?>" width="<?php echo absint( $size ); ?>" height="<?php echo absint( $size ); ?>">
            <?php if (!$uwp_in_user_loop && is_user_logged_in() && (get_current_user_id() == $user->ID) && $allow_change) { ?>
                <div class="uwp-profile-avatar-change">
                    <div class="uwp-profile-avatar-change-inner">
                        <i class="fas fa-camera" aria-hidden="true"></i>
                        <a id="uwp-profile-picture-change" data-type="avatar"
                           class="uwp-profile-modal-form-trigger"
                           href="#"><?php esc_attr_e('Update', 'userswp'); ?></a>
                    </div>
                </div>
            <?php } ?>
        </div>
        <?php
        if ($uwp_in_user_loop || 1==$link) {
            echo '</a>';
        }
    ?>
</div>