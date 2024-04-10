<?php
/**
 * Files related functions
 *
 * This class defines all code necessary to upload files.
 *
 * @since      1.0.0
 * @author     GeoDirectory Team <info@wpgeodirectory.com>
 */
class UsersWP_Files {
    
    /**
     * Handles file upload request.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       object          $field      Field info object.
     * @param       array           $files      $_FILES array.
     * @return      bool|array                  Uploaded file url info array.
     */
    public function handle_file_upload($field, $files ) {

        if ( isset( $files[ $field->htmlvar_name ] ) && ! empty( $files[ $field->htmlvar_name ] ) && ! empty( $files[ $field->htmlvar_name ]['name'] ) ) {

            $extra_fields = unserialize($field->extra_fields);

            $allowed_mime_types = array();
            if (isset($extra_fields['uwp_file_types']) && !in_array("*", $extra_fields['uwp_file_types'])) {
                $allowed_mime_types = $extra_fields['uwp_file_types'];
            }

            $allowed_mime_types = apply_filters('uwp_fields_allowed_mime_types', $allowed_mime_types, $field->htmlvar_name);

            $file_urls       = array();
            $files_to_upload = $this->prepare_files( $files[ $field->htmlvar_name ] );

            $max_upload_size = $this->uwp_get_max_upload_size($field->form_type, $field->htmlvar_name);

            if ( ! $max_upload_size ) {
                $max_upload_size = 0;
            }

            foreach ( $files_to_upload as $file_key => $file_to_upload ) {

                if (!empty($allowed_mime_types)) {
                    $ext = $this->get_file_type($file_to_upload['type']);

                    $allowed_error_text = implode(', ', $allowed_mime_types);
                    if ( !in_array( $ext , $allowed_mime_types ) )
                        return new WP_Error( 'validation-error', sprintf( __( 'Allowed files types are: %s', 'userswp' ),  $allowed_error_text) );
                }


                if ( $file_to_upload['size'] >  $max_upload_size) {
                    return new WP_Error( 'file-too-big', __( 'The uploaded file is too big. Maximum size allowed:'. $this->uwp_formatSizeUnits($max_upload_size), 'userswp' ) );
                }


                $error_result = apply_filters('uwp_handle_file_upload_error_checks', true, $field, $file_key, $file_to_upload);
                if (is_wp_error($error_result)) {
                    return $error_result;
                }

                remove_filter( 'wp_handle_upload_prefilter', array($this, 'wp_media_restrict_file_types') );
                if(in_array($field->htmlvar_name, array('avatar', 'banner'))){
                    add_filter( 'upload_dir', 'uwp_handle_multisite_profile_image', 10, 1 );
                }
                $uploaded_file = $this->upload_file( $file_to_upload, array( 'file_key' => $file_key ) );
                add_filter( 'wp_handle_upload_prefilter', array($this, 'wp_media_restrict_file_types') );

                if ( is_wp_error( $uploaded_file ) ) {

                    return new WP_Error( 'validation-error', $uploaded_file->get_error_message() );

                } else {

                    $file_urls[] = array(
                        'url'  => $uploaded_file->url,
                        'path' => $uploaded_file->path,
                        'size' => $uploaded_file->size,
                        'name' => $uploaded_file->name,
                        'type' => $uploaded_file->type,
                    );

                }

            }

            return current( $file_urls );

        }
        return true;

    }

    /**
     * Formats the file size into human readable form.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       int         $bytes      Size in Bytes.
     * @return      string                  Size in human readable form.
     */
    public function uwp_formatSizeUnits($bytes)
    {
        if ($bytes >= 1073741824)
        {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        }
        elseif ($bytes >= 1048576)
        {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        }
        elseif ($bytes >= 1024)
        {
            $bytes = number_format($bytes / 1024, 2) . ' kB';
        }
        elseif ($bytes > 1)
        {
            $bytes = $bytes . ' bytes';
        }
        elseif ($bytes == 1)
        {
            $bytes = $bytes . ' byte';
        }
        else
        {
            $bytes = '0 bytes';
        }

        return $bytes;
    }

    /**
     * Formats the file size in KB.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       int         $bytes      Size in Bytes.
     * @return      int                     Size in KB.
     */
    public function uwp_formatSizeinKb($bytes)
    {
        $kb = $bytes / 1024;
        return $kb;
    }

    /**
     * Gets the size is bytes for given value.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       string      $val    Size in human readable form.
     * @return      int                 Value in bytes.
     */
    public function uwp_get_size_in_bytes($val) {
        $val = trim($val);
        $last = strtolower($val[strlen($val)-1]);
        $val = substr($val, 0, -1);
        switch($last) {
            // The 'G' modifier is available since PHP 5.1.0
            case 'g':
                $val *= (1024 * 1024 * 1024); //1073741824
                break;
            case 'm':
                $val *= (1024 * 1024); //1048576
                break;
            case 'k':
                $val *= 1024;
                break;
        }

        return $val;
    }

    /**
     * Processes the file upload.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       array       $file       File info to upload.
     * @param       array       $args       File upload helper args.
     * @return      object                  Uploaded file info
     */
    public function upload_file( $file, $args = array() ) {

        include_once ABSPATH . 'wp-admin/includes/file.php';
        include_once ABSPATH . 'wp-admin/includes/media.php';

        $args = wp_parse_args( $args, array(
            'file_key'           => '',
            'file_label'         => '',
            'allowed_mime_types' => get_allowed_mime_types()
        ) );

        $uploaded_file              = new stdClass();

        if ( ! in_array( $file['type'], $args['allowed_mime_types'] ) ) {
            if ( $args['file_label'] ) {
                return new WP_Error( 'upload', sprintf( __( '"%s" (filetype %s) needs to be one of the following file types: %s', 'userswp' ), $args['file_label'], $file['type'], implode( ', ', array_keys( $args['allowed_mime_types'] ) ) ) );
            } else {
                return new WP_Error( 'upload', sprintf( __( 'Uploaded files need to be one of the following file types: %s', 'userswp' ), implode( ', ', array_keys( $args['allowed_mime_types'] ) ) ) );
            }
        } else {
            $upload = wp_handle_upload( $file, apply_filters( 'uwp_handle_upload_overrides', array( 'test_form' => false ) ) );

            if ( ! empty( $upload['error'] ) ) {
                return new WP_Error( 'upload', $upload['error'] );
            } else {
                if ( ! empty( $upload['type'] ) && $upload['type'] != 'image/png' && strpos( $upload['type'], 'image/' ) === 0 ) {
                    // Fetch additional metadata from EXIF/IPTC.
                    $exif_meta = wp_read_image_metadata( $upload['file'] );

                    if ( ! empty( $exif_meta ) && is_array( $exif_meta ) && ! empty( $exif_meta['orientation'] ) && 1 !== (int) $exif_meta['orientation'] ) {
                        $editor = wp_get_image_editor( $upload['file'] );

                        if ( ! empty( $editor ) && ! is_wp_error( $editor ) ) {
                            // Rotate the whole original image if there is EXIF data and "orientation" is not 1.
                            $rotated = $editor->maybe_exif_rotate();
                            $rotated = $rotated === true ? $editor->save( $editor->generate_filename( 'rotated' ) ) : false;

                            if ( ! empty( $rotated ) && ! is_wp_error( $rotated ) && ! empty( $rotated['path'] ) ) {
                                $upload['url'] = str_replace( basename( $upload['url'] ), basename( $rotated['path'] ), $upload['url'] );
                                $upload['file'] = $rotated['path'];
                            }
                        }
                    }
                }

                $uploaded_file->url       = $upload['url'];
                $uploaded_file->name      = basename( $upload['file'] );
                $uploaded_file->path      = $upload['file'];
                $uploaded_file->type      = $upload['type'];
                $uploaded_file->size      = $file['size'];
                $uploaded_file->extension = substr( strrchr( $uploaded_file->name, '.' ), 1 );
            }
        }

        return $uploaded_file;
    }

    /**
     * Prepares the files for upload
     *
     * @since       1.0.0
     * @package     userswp
     * @param       array       $file_data      Files to upload
     * @return      array                       Prepared files.
     */
    public function prepare_files( $file_data ) {
        $files_to_upload = array();

        if ( is_array( $file_data['name'] ) ) {
            foreach ( $file_data['name'] as $file_data_key => $file_data_value ) {

                if ( $file_data['name'][ $file_data_key ] ) {
                    $files_to_upload[] = array(
                        'name'     => $file_data['name'][ $file_data_key ],
                        'type'     => $file_data['type'][ $file_data_key ],
                        'tmp_name' => $file_data['tmp_name'][ $file_data_key ],
                        'error'    => $file_data['error'][ $file_data_key ],
                        'size'     => $file_data['size'][ $file_data_key ]
                    );
                }
            }
        } else {
            $files_to_upload[] = $file_data;
        }

        return $files_to_upload;
    }

    /**
     * Validates the file uploads.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       array       $files          $_FILES array
     * @param       string      $type           Form type.
     * @param       bool        $url_only       Return only the url or whole file info?
     * @param       array|bool  $fields         Form fields.
     * @return      array                       Validated data.
     */
    public function validate_uploads($files, $type, $url_only = true, $fields = false) {

        $validated_data = array();

        if (empty($files)) {
            return $validated_data;
        }

        if (!$fields) {
            global $wpdb;
            $table_name = uwp_get_table_prefix() . 'uwp_form_fields';

            if ($type == 'register') {
                $fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $table_name . " WHERE form_type = %s AND field_type = 'file' AND is_active = '1' AND is_register_field = '1' ORDER BY sort_order ASC", array('account')));
            } elseif ($type == 'account') {
                $fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $table_name . " WHERE form_type = %s AND field_type = 'file' AND is_active = '1' AND is_register_only_field = '0' ORDER BY sort_order ASC", array('account')));
            } else {
                $fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $table_name . " WHERE form_type = %s AND field_type = 'file' AND is_active = '1' ORDER BY sort_order ASC", array($type)));
            }
        }

        if ( ! empty( $fields ) ) {
            foreach ( $fields as $field ) {
                if ( isset( $files[ $field->htmlvar_name ] ) && ! empty( $files[ $field->htmlvar_name ]['name'] ) ) {
                    $file_urls = $this->handle_file_upload( $field, $files );

                    if ( is_wp_error( $file_urls ) ) {
                        return $file_urls;
                    }

                    if ( $url_only ) {
                        $validated_data[$field->htmlvar_name] = $file_urls['url'];
                    } else {
                        $validated_data[$field->htmlvar_name] = $file_urls;
                    }
                }
            }
        }

        return $validated_data;
    }

    /**
     * Displays the preview for images and links for other types above the field for existing uploads.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       object      $field          Form field info.
     * @param       string      $value          Value of the field.
     * @param       bool        $removable      Is this value removable by user?
     * @return      string                      HTML output.
     */
    public function file_upload_preview($field, $value, $removable = true) {
        $output = '';

        $value = esc_html($value);

        if ($field->htmlvar_name == "banner") {
            $htmlvar = "banner_thumb";
        } elseif ($field->htmlvar_name == "avatar") {
            $htmlvar = "avatar_thumb";
        } else {
            $htmlvar = $field->htmlvar_name;
        }

        // If is current user's profile (profile.php)
        if ( is_admin() && defined('IS_PROFILE_PAGE') && IS_PROFILE_PAGE ) {
            $user_id = get_current_user_id();
            // If is another user's profile page
        } elseif (is_admin() && ! empty($_GET['user_id']) && is_numeric($_GET['user_id']) ) {
            $user_id = absint( $_GET['user_id'] );
            // Otherwise something is wrong.
        } else {
            $user_id = get_current_user_id();
        }

        if ($value) {

            $upload_dir = wp_upload_dir();
            if (substr( $value, 0, 4 ) !== "http") {
                $value = $upload_dir['baseurl'].$value;
            }

            $file = basename( $value );
            $filetype = wp_check_filetype($file);
            $image_types = array('png', 'jpg', 'jpeg', 'gif');
            if (in_array($filetype['ext'], $image_types)) {
                $output .= '<div class="uwp_file_preview_wrap">';
                $output .= '<a href="'.$value.'" class="uwp_upload_file_preview"><img style="max-width:100px;" src="'.$value.'" /></a>';
                if ($removable) {
                    $output .= '<a onclick="return confirm(\'Are you sure?\')" style="display: block;margin: 5px 0;" href="#" id="'.$htmlvar.'" data-htmlvar="'.$htmlvar.'" data-uid="'.$user_id.'" class="uwp_upload_file_remove">'. __( 'Remove Image' , 'userswp' ).'</a>';
                }
                $output .= '</div>';
                ?>
                <?php
            } else {
                $output .= '<div class="uwp_file_preview_wrap">';
                $output .= '<a href="'.$value.'" class="uwp_upload_file_preview">'.$file.'</a>';
                if ($removable) {
                    $output .= '<a onclick="return confirm(\'Are you sure?\')" style="display: block;margin: 5px 0;" href="#" id="'.$htmlvar.'" data-htmlvar="'.$htmlvar.'" data-uid="'.$user_id.'" class="uwp_upload_file_remove">'. __( 'Remove File' , 'userswp' ).'</a>';
                }
                $output .= '</div>';
                ?>
                <?php
            }
        }
        return $output;
    }

    /**
     * restrict files to certain types
     * 
     * @since       1.0.0
     * @package     userswp
     * @param       array       $file   File info.
     * @return      array               Modified file info.
     */
    public function wp_media_restrict_file_types($file) {
        // This bit is for the flash uploader
        if ($file['type']=='application/octet-stream' && isset($file['tmp_name'])) {
            $file_size = getimagesize($file['tmp_name']);
            if (isset($file_size['error']) && $file_size['error']!=0) {
                $file['error'] = "Unexpected Error: {$file_size['error']}";
                return $file;
            } else {
                $file['type'] = $file_size['mime'];
            }
        }
        list($category,$type) = explode('/',$file['type']);
        if ('image'!=$category || !in_array($type,array('jpg','jpeg','gif','png'))) {
            $file['error'] = "Sorry, you can only upload a .GIF, a .JPG, or a .PNG image file.";
        } else if ($post_id = (isset($_REQUEST['post_id']) ? absint($_REQUEST['post_id']) : false)) {
            if (count(get_posts("post_type=attachment&post_parent={$post_id}"))>0)
                $file['error'] = "Sorry, you cannot upload more than one (1) image.";
        }
        return $file;
    }

    /**
     * Check whether the uploads is from the profile page.
     *
     * @since       1.0.0
     * @package     userswp
     * @return      bool    
     */
    public function doing_upload(){
        return isset($_POST['uwp_profile_upload']) ? true : false;
    }

    /**
     * Gets the maximum file upload size.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       string|bool        $form_type              Form type.
     * @param       string|bool        $field_htmlvar_name     htmlvar_name key.
     * @return      int                                         Allowed upload size.
     */
    public function uwp_get_max_upload_size($form_type = false, $field_htmlvar_name = false) {
        if (is_multisite()) {
            $network_setting_size = esc_attr( get_option( 'fileupload_maxk', 300 ) );
            $max_upload_size = $this->uwp_get_size_in_bytes($network_setting_size.'k');
            if ($max_upload_size > wp_max_upload_size()) {
                $max_upload_size = wp_max_upload_size();
            }
        } else {
            $max_upload_size = wp_max_upload_size();
        }
        $max_upload_size = apply_filters('uwp_get_max_upload_size', $max_upload_size, $form_type, $field_htmlvar_name);

        return $max_upload_size;
    }


    /**
     * Modifies the maximum file upload size based on the setting.
     *
     * @since       1.0.0
     * @package     userswp
     *
     * @param       int         $bytes      Size in bytes.
     * @param       string      $type       File upload type.
     *
     * @return      int                     Size in bytes.
     */
    public function uwp_modify_get_max_upload_size($bytes, $type) {

        if ($type == 'avatar') {
            $kb = uwp_get_option('profile_avatar_size', false);
            if ($kb) {
                $bytes = intval($kb) * 1024;
            }
        }

        if ($type == 'banner') {
            $kb = uwp_get_option('profile_banner_size', false);
            if ($kb) {
                $bytes = intval($kb) * 1024;
            }
        }

        return $bytes;

    }

    /**
     * Gets the file type using the extension.
     * Ex: 'jpg'  => 'image/jpeg'
     *
     * @since       1.0.0
     * @package     userswp
     *
     * @param       string      $ext        Extension string. Ex: png, jpg
     *
     * @return      string                  File type.
     */
    public function get_file_type($ext) {
        $allowed_file_types = $this->allowed_mime_types();
        $file_types = array();
        foreach ( $allowed_file_types as $format => $types ) {
            $file_types = array_merge($file_types, $types);
        }
        $file_types = array_flip($file_types);
        return $file_types[$ext];
    }

    /**
     * Returns allowed mime types in uploads
     *
     * @since       1.0.0
     * @package     userswp
     *
     * @return      array       Allowed mime types.
     */
    public function allowed_mime_types() {
        return apply_filters( 'uwp_allowed_mime_types', array(
                'Image'       => array( // Image formats.
                    'jpg'  => 'image/jpeg',
                    'jpe'  => 'image/jpeg',
                    'jpeg' => 'image/jpeg',
                    'gif'  => 'image/gif',
                    'png'  => 'image/png',
                    'bmp'  => 'image/bmp',
                    'ico'  => 'image/x-icon',
                ),
                'Video'       => array( // Video formats.
                    'asf'  => 'video/x-ms-asf',
                    'avi'  => 'video/avi',
                    'flv'  => 'video/x-flv',
                    'mkv'  => 'video/x-matroska',
                    'mp4'  => 'video/mp4',
                    'mpeg' => 'video/mpeg',
                    'mpg'  => 'video/mpeg',
                    'wmv'  => 'video/x-ms-wmv',
                    '3gp'  => 'video/3gpp',
                ),
                'Audio'       => array( // Audio formats.
                    'ogg' => 'audio/ogg',
                    'mp3' => 'audio/mpeg',
                    'wav' => 'audio/wav',
                    'wma' => 'audio/x-ms-wma',
                ),
                'Text'        => array( // Text formats.
                    'css'  => 'text/css',
                    'csv'  => 'text/csv',
                    'htm'  => 'text/html',
                    'html' => 'text/html',
                    'txt'  => 'text/plain',
                    'rtx'  => 'text/richtext',
                    'vtt'  => 'text/vtt',
                ),
                'Application' => array( // Application formats.
                    'doc'  => 'application/msword',
                    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'exe'  => 'application/x-msdownload',
                    'js'   => 'application/javascript',
                    'odt'  => 'application/vnd.oasis.opendocument.text',
                    'pdf'  => 'application/pdf',
                    'pot'  => 'application/vnd.ms-powerpoint',
                    'ppt'  => 'application/vnd.ms-powerpoint',
                    'pptx' => 'application/vnd.ms-powerpoint',
                    'psd'  => 'application/octet-stream',
                    'rar'  => 'application/rar',
                    'rtf'  => 'application/rtf',
                    'swf'  => 'application/x-shockwave-flash',
                    'tar'  => 'application/x-tar',
                    'xls'  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'zip'  => 'application/zip',
                )
            )
        );
    }

	/**
	 * Initiate the WordPress file system and provide fallback if needed.
	 *
	 * @since 1.2.2
	 * @package userswp
	 * @return bool|string Returns the file system class on success. False on failure.
	 */
	public static function uwp_init_filesystem() {

		if ( ! function_exists( 'get_filesystem_method' ) ) {
			require_once( ABSPATH . "/wp-admin/includes/file.php" );
		}
		$access_type = get_filesystem_method();
		if ( $access_type === 'direct' ) {
			/* you can safely run request_filesystem_credentials() without any issues and don't need to worry about passing in a URL */
			$creds = request_filesystem_credentials( trailingslashit( site_url() ) . 'wp-admin/', '', false, false, array() );

			/* initialize the API */
			if ( ! WP_Filesystem( $creds ) ) {
				/* any problems and we exit */
				return false;
			}

			global $wp_filesystem;

			return $wp_filesystem;
			/* do our file manipulations below */
		} elseif ( defined( 'FTP_USER' ) ) {
			$creds = request_filesystem_credentials( trailingslashit( site_url() ) . 'wp-admin/', '', false, false, array() );

			/* initialize the API */
			if ( ! WP_Filesystem( $creds ) ) {
				/* any problems and we exit */
				return false;
			}

			global $wp_filesystem;

			return $wp_filesystem;

		} else {
			return false;
		}

	}

}