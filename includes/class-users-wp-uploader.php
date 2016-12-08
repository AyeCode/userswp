<?php
Class Users_WP_Uploader {

    function __construct() {
        add_action( 'init', array( $this, 'init' ) );
    }
    
    function init() {
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_filter( 'ajax_query_attachments_args', array( $this, 'filter_media' ) );
    }
    
    function enqueue_scripts() {
        wp_enqueue_media();
    }
    
    function filter_media( $query ) {
        // admins get to see everything
        if ( ! current_user_can( 'manage_options' ) )
            $query['author'] = get_current_user_id();
        return $query;
    }
    
    function frontend_shortcode( $args ) {
        // check if user can upload files
        if ( current_user_can( 'upload_files' ) ) {
            $str = __( 'Select File', 'frontend-media' );
            return '<input id="frontend-button" type="button" value="' . $str . '" class="button" style="position: relative; z-index: 1;"><img id="frontend-image" />';
        }
        return __( 'Please Login To Upload', 'frontend-media' );
    }

}

new Users_WP_Uploader();