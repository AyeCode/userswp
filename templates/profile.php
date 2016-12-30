<?php do_action('uwp_template_before', 'profile'); ?>
<?php
$url_type = apply_filters('uwp_profile_url_type', 'slug');
$enable_profile_header = uwp_get_option('enable_profile_header', false);
$enable_profile_body = uwp_get_option('enable_profile_body', false);

$author_slug = get_query_var('uwp_profile');
if ($url_type == 'id') {
    $user = get_user_by('id', $author_slug);
} else {
    $user = get_user_by('slug', $author_slug);
}
?>
<div class="uwp-content-wrap">
    <?php do_action('uwp_template_display_notices', 'profile'); ?>
    <?php
    if ($enable_profile_header == '1') {
        do_action('uwp_profile_header', $user );
    }
    ?>
    <?php if ($enable_profile_body == '1') { ?>
        <div class="uwp-profile-main">
            <div class="uwp-profile uwp-profile-side">
                <?php do_action('uwp_profile_title', $user ); ?>
                <?php do_action('uwp_profile_social', $user ); ?>
                <?php do_action('uwp_profile_bio', $user ); ?>
                <?php do_action('uwp_profile_buttons', $user ); ?>
            </div>
            <?php do_action('uwp_profile_content', $user); ?>
        </div>
    <?php } ?>
</div>
<?php do_action('uwp_template_after', 'profile'); ?>