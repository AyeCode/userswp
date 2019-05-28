<?php
do_action('uwp_template_before', 'profile');
$user = uwp_get_user_by_author_slug();
$profile_access = apply_filters('uwp_profile_access', true, $user);
if ($profile_access) {
    echo do_shortcode("[uwp_profile_header][uwp_profile_section position='left' type='open'][uwp_profile_title][uwp_profile_social][uwp_profile_bio][uwp_profile_buttons][uwp_profile_section position='left' type='close'][uwp_profile_section position='right' type='open'][uwp_profile_content][uwp_profile_section position='right' type='close']");
} else {
    do_action('uwp_profile_access_denied', $user);
}
do_action('uwp_template_after', 'profile');
?>