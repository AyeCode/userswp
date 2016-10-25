<?php do_action('uwp_template_before', 'profile'); ?>
<?php
$author_id = get_current_user_id();
//$author_id = get_query_var( 'author' );
$user = get_user_by('id', $author_id);
$current_url = home_url('/profile/admin'); //todo:fix this
?>
<div class="uwp-content-wrap">
<?php do_action('uwp_profile_header', $user ); ?>
    <div class="uwp-profile-main">
        <div class="uwp-profile">
            <?php do_action('uwp_profile_title', $user ); ?>
            <?php do_action('uwp_profile_bio', $user ); ?>
            <?php do_action('uwp_profile_social', $user ); ?>
        </div>
        <?php do_action('uwp_profile_content') ?>
</div>
<?php do_action('uwp_template_after', 'profile'); ?>