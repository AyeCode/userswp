<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class UWP_Notices {

    public function __construct() {
        add_action( 'admin_notices', array( $this, 'show_notices' ) );
    }

    public function show_notices() {
        settings_errors( 'uwp-notices' );
    }
    
}
new UWP_Notices;