<?php
do_action('uwp_template_before', 'profile');
$user = uwp_get_user_by_author_slug();
do_action('uwp_profile_body', $user);
do_action('uwp_template_after', 'profile');
?>