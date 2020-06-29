<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>
<div class="col mb-4">
	<div class="card uwp-users-list-user text-center h-100">
			<?php echo UsersWP_Templates::users_list_item_template_content(); ?>
	</div>
</div>

