<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<li class="uwp-users-list-user">
    <?php echo UsersWP_Templates::users_list_item_template_content(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
</li>