<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>
<div class="col mb-4">
	<div class="card uwp-users-list-user text-center h-100">
			<?php

//			$sc = "[/uwp_profile_header][uwp_user_title tag= 'h4'][uwp_profile_social][uwp_output_location location='users'][uwp_user_actions]";



			echo UsersWP_Templates::users_list_item_template_content();

			?>
	</div>
</div>

