<?php
/**
 * UsersWP date related functions
 *
 * All UsersWP date related functions can be found here.
 *
 * @since      1.0.0
 * @author     GeoDirectory Team <info@wpgeodirectory.com>
 */
class UsersWP_Date {

    /**
     * Converts PHP Date format to jQuery UI date format.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       string      $php_format     Date Format.
     * @return      string                      Formatted Date.
     */
    public function date_format_php_to_jqueryui( $php_format ) {
        $symbols = array(
            // Day
            'd' => 'dd',
            'D' => 'D',
            'j' => 'd',
            'l' => 'DD',
            'N' => '',
            'S' => '',
            'w' => '',
            'z' => 'o',
            // Week
            'W' => '',
            // Month
            'F' => 'MM',
            'm' => 'mm',
            'M' => 'M',
            'n' => 'm',
            't' => '',
            // Year
            'L' => '',
            'o' => '',
            'Y' => 'yy',
            'y' => 'y',
            // Time
            'a' => 'tt',
            'A' => 'TT',
            'B' => '',
            'g' => 'h',
            'G' => 'H',
            'h' => 'hh',
            'H' => 'HH',
            'i' => 'mm',
            's' => '',
            'u' => ''
        );

        $jqueryui_format = "";
        $escaping = false;

        for ( $i = 0; $i < strlen( $php_format ); $i++ ) {
            $char = $php_format[$i];

            // PHP date format escaping character
            if ( $char === '\\' ) {
                $i++;

                if ( $escaping ) {
                    $jqueryui_format .= $php_format[$i];
                } else {
                    $jqueryui_format .= '\'' . $php_format[$i];
                }

                $escaping = true;
            } else {
                if ( $escaping ) {
                    $jqueryui_format .= "'";
                    $escaping = false;
                }

                if ( isset( $symbols[$char] ) ) {
                    $jqueryui_format .= $symbols[$char];
                } else {
                    $jqueryui_format .= $char;
                }
            }
        }

        return $jqueryui_format;
    }

    /**
     * Converts date from one format to another. 
     *
     * @since       1.0.0
     * @package     userswp
     * @param       string      $date_input         Date string to convert.
     * @param       string      $date_to            Date format to convert to.
     * @param       string      $date_from          Date format to convert from.
     * @return      string                          Converted date.
     */
    public function date($date_input, $date_to, $date_from = '') {
        if (empty($date_input) || empty($date_to)) {
            return NULL;
        }

        $date = '';
        if (!empty($date_from)) {
            $datetime = date_create_from_format($date_from, $date_input);

            if (!empty($datetime)) {
                $date = $datetime->format($date_to);
            }
        }

        if (empty($date)) {
            $date = strpos($date_input, '/') !== false ? str_replace('/', '-', $date_input) : $date_input;
            $date = date_i18n($date_to, strtotime($date));
        }

        $date = $this->maybe_untranslate_date($date);

        return apply_filters('uwp_date', $date, $date_input, $date_to, $date_from);
    }

    /**
     * Converts non english date months to english date months.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       string      $date   Date String.
     * @return      string              Converted Date.
     */
    public function maybe_untranslate_date($date){
        $english_long_months = array(
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December',
        );

        $non_english_long_months  = array(
            __('January'),
            __('February'),
            __('March'),
            __('April'),
            __('May'),
            __('June'),
            __('July'),
            __('August'),
            __('September'),
            __('October'),
            __('November'),
            __('December'),
        );
        $date = str_replace($non_english_long_months,$english_long_months,$date);


        $english_short_months = array(
            ' Jan ',
            ' Feb ',
            ' Mar ',
            ' Apr ',
            ' May ',
            ' Jun ',
            ' Jul ',
            ' Aug ',
            ' Sep ',
            ' Oct ',
            ' Nov ',
            ' Dec ',
        );

        $non_english_short_months = array(
            ' '._x( 'Jan', 'January abbreviation' ).' ',
            ' '._x( 'Feb', 'February abbreviation' ).' ',
            ' '._x( 'Mar', 'March abbreviation' ).' ',
            ' '._x( 'Apr', 'April abbreviation' ).' ',
            ' '._x( 'May', 'May abbreviation' ).' ',
            ' '._x( 'Jun', 'June abbreviation' ).' ',
            ' '._x( 'Jul', 'July abbreviation' ).' ',
            ' '._x( 'Aug', 'August abbreviation' ).' ',
            ' '._x( 'Sep', 'September abbreviation' ).' ',
            ' '._x( 'Oct', 'October abbreviation' ).' ',
            ' '._x( 'Nov', 'November abbreviation' ).' ',
            ' '._x( 'Dec', 'December abbreviation' ).' ',
        );

        $date = str_replace($non_english_short_months,$english_short_months,$date);


        return $date;
    }

    /**
     * Gets random date.
     *
     * @since       1.0.0
     * @package     userswp
     *
     * @param       int $days_from      Random days from.
     * @param       int $days_to        Random days to.
     *
     * @return      string              Formatted date string.
     */
    public function get_random_date( $days_from = 30, $days_to = 0 ) {
        // 1 day in seconds is 86400
        $from = $days_from * rand( 10000, 99999 );

        // $days_from should always be less than $days_to
        if ( $days_to > $days_from ) {
            $days_to = $days_from - 1;
        }

        $to        = $days_to * rand( 10000, 99999 );
        $date_from = time() - $from;
        $date_to   = time() - $to;

        return date( 'Y-m-d H:i:s', rand( $date_from, $date_to ) );
    }

}