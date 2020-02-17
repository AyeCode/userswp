<?php
/**
 * Personal data exporters.
 */

defined( 'ABSPATH' ) || exit;

/**
 * UsersWP_Privacy_Exporters Class.
 */
class UsersWP_Privacy_Exporters {
    /**
     * Finds and exports user data by email address.
     *
     * @since 1.0.14
     * @param string $email_address The user email address.
     * @param int    $page  Page.
     * @return array An array of user data in name value pairs
     */
    public static function user_data_exporter( $email_address, $page ) {
        $user           = get_user_by( 'email', $email_address );
        $data_to_export = array();

        if ( $user instanceof WP_User ) {
            $data_to_export[] = array(
                'group_id'    => 'uwp_user',
                'group_label' => __( 'UsersWP user data', 'userswp' ),
                'group_description' => __( 'The UsersWP user data', 'userswp' ),
                'item_id'     => 'user',
                'data'        => self::get_user_meta_data( $user ),
            );
        }

        return array(
            'data' => $data_to_export,
            'done' => true,
        );
    }

    /**
     * Get personal data (key/value pairs) for a user object.
     *
     * @since 1.0.14
     * @param WP_User $user user object.
     * @return array
     */
    protected static function get_user_meta_data( $user ) {
        $metadata = uwp_get_usermeta_row($user->ID);

        $personal_data = array();
        $skip_keys = array('user_id', 'user_privacy', 'tabs_privacy');
        $skip_keys = apply_filters('uwp_privacy_export_skip_user_data_columns', $skip_keys, $user);

        if($metadata) {
            foreach ($metadata as $key => $value) {
                if (!empty($value) && !in_array($key, $skip_keys)) {

                    if(in_array($key, array('avatar_thumb', 'banner_thumb'))){
                        $uploads = wp_upload_dir();
                        $upload_url = $uploads['baseurl'];
                        $value = $upload_url.$value;
                    }
                    
                    $key = self::get_formatted_column_name($key);

                    $personal_data[] = array(
                        'name' => $key,
                        'value' => $value,
                    );
                }
            }
        }

        /**
         * Allow extensions to register their own personal data for this user for the export.
         *
         * @since 1.0.14
         * @param array    $personal_data Array of name value pairs.
         * @param WP_User $user user object.
         */
        $personal_data = apply_filters( 'uwp_privacy_export_user_personal_data', $personal_data, $user );

        return $personal_data;

    }

    /**
     * Get formatted table column name.
     *
     * @since 1.0.14
     * @param string $string string to replace.
     * @param array $arrays array of keys to match.
     * @return string
     */
    public static function get_formatted_column_name($string, $arrays = array('uwp_account_', 'uwp_', '_')){

        $temp_string = $string;

        if( !empty($arrays) && count($arrays) > 0 ) {

            foreach ( $arrays as $replace_val) {
                $temp_string = str_replace($replace_val," ",$temp_string);
            }

            $temp_string = trim($temp_string);
            $temp_string = ucfirst($temp_string);

        }

        return $temp_string;

    }

}
