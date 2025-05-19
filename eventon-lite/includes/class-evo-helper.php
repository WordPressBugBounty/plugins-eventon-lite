<?php
/** 
 * Helper functions to be used by eventon or its addons
 * front-end only 
 * @version 2.4.5
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class evo_helper{	
	public $options2;
	public $opt2 = array();
	public $debug = 'good';
	
	public function __construct(){
		$this->opt2 = EVO()->cal->get_op('evcal_2');
	}


	// Process permalink appends
		public function process_link($link,  $var, $append, $force_par = false){
			if(strpos($link, '?')=== false ){

				if($force_par){
					if( substr($link,-1) == '/') $link = substr($link,0,-1);
					$link .= "?".$var."=".$append;
				}else{
					if( substr($link,-1) == '/') $link = substr($link,0,-1);
					$link .= "/".$var."/".$append;
				}
				
			}else{
				$link .= "&".$var."=".$append;
			}
			return $link;
		}
		

	// convert array to data element values - 3.1 / U 4.1
		public function array_to_html_data($array){
			$html = '';
			foreach($array as $k=>$v){
				if( is_array($v)) $v = htmlspecialchars( wp_json_encode($v), ENT_QUOTES);
				//$html .= 'data-'. $k .'="'. $v .'" ';
				$html .= 'data-'. $k ."='". $v ."'";
			}
			return $html;
		}
		public function print_array_to_html_data( $array ){
			echo $this->array_to_html_data( $array );
		}
		

	// sanitization
		// @since 2.4.5
		public function validate_request( 
			$nonce_field = 'nn', 
			$nonce_action = 'eventon_admin_nonce', 
			$capability = 'edit_eventons', 
			$require_admin = false, 
			$require_auth = true, 
			$output_type = 'json' , 
			$use_admin_referer = false
		) {
		    $error_msg = '';

		    // Check if in admin context if required
		    if ( $require_admin && ! is_admin() ) {
		        $error_msg = __( 'Only available in admin side.', 'eventon' );
		    }
		    // Check authentication if required
		    elseif ( $require_auth && ! is_user_logged_in() ) {
		        $error_msg = __( 'Authentication required', 'eventon' );
		    }
		    // Verify user permissions if capability is specified
		    elseif ( $capability && ! current_user_can( $capability ) ) {
		        EVO_Debug( 'Unauthorized access attempt to ' . $nonce_action );
		        $error_msg = __( 'You do not have proper permission', 'eventon' );
		    } 
		    // admin referer check
		    elseif ( $use_admin_referer ) {
		        if ( ! check_admin_referer( $nonce_action, $nonce_field ) ) {
		            $error_msg = __( 'Nonce or referrer validation failed', 'eventon' );
		        }
		    } 
		    // Verify nonce
		    elseif ( empty( $_REQUEST[$nonce_field] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST[$nonce_field] ), $nonce_action ) ) {
		        $error_msg = __( 'Nonce validation failed', 'eventon' );
		    }

		    // Handle output based on $output_type
		    if ( $error_msg ) {
		        if ( $output_type === 'json' ) {
		            wp_send_json( array( 'status' => 'bad', 'msg' => $error_msg ) );
		        } else {
		            wp_die( $error_msg );
		        }
		    }
		}
		// @+ 4.0.3
		public function sanitize_array($array){
			return $this->recursive_sanitize_array_fields($array);
		}
		public function recursive_sanitize_array_fields($array){
			if(is_array($array)){
				$new_array = array();
				foreach ( $array as $key => $value ) {
		        	if ( is_array( $value ) ) {
		        		$key = sanitize_title($key);
		            	$new_array[ $key ] = $this->recursive_sanitize_array_fields($value);
		        	}
		        	else {
		            	$new_array[ $key ] = sanitize_text_field( $value );
		        	}
	    		}

	    		return $new_array;
	    	}else{
	    		return sanitize_text_field( $array );	    		
	    	}
		}		

		// check ajax submissions for sanitation and nonce verification
		// @+3.1
		public function process_post($array, $nonce_key='', $nonce_code='', $filter = true){
			$array = $this->recursive_sanitize_array_fields( $array);

			if( !empty($nonce_key) && !empty($nonce_code)){

				if( !wp_verify_nonce( $array[ $nonce_key], $nonce_code ) ) return false;
			}

			if($filter)	$array = array_filter( $array );
			return $array;
		}	

		// sanitize html content @u 4.5.3
			function sanitize_html($content){
				if( !EVO()->cal->check_yn('evo_sanitize_html','evcal_1')) return $content;

				return wp_kses( $content, apply_filters('evo_sanitize_html', array( 
				    'a' => array(
				        'href' => array(),
				        'title' => array()
				    ),
				    'br' => array(),
				    'p' => array(),
				    'i' => array(),
				    'b' => array(),
				    'u' => array(),			    
				    'ul' => array(),
				    'li' => array(),
				    'br' => array(),
				    'em' => array(),
				    'strong' => array(),
				    'span' => array(),
				    'font' => array('color' => array()),
				    'img' => array(
				    	'src'      => true,
				        'srcset'   => true,
				        'sizes'    => true,
				        'class'    => true,
				        'id'       => true,
				        'width'    => true,
				        'height'   => true,
				        'alt'      => true,
				        'align'    => true,
				    ),
				) ) );
			}
			function sanitize_html_for_eventtop( $content ){
				return wp_kses( $content, apply_filters('evo_sanitize_html_eventtop',
					array( 				    
				    'i' => array(),
				    'b' => array(),
				    'u' => array(),			    
				    'br' => array(),
				    'em' => array(),
				    'strong' => array(),
				    'img' => array(
				    	'src'      => true,
				        'srcset'   => true,
				        'sizes'    => true,
				        'class'    => true,
				        'id'       => true,
				        'width'    => true,
				        'height'   => true,
				        'alt'      => true,
				        'align'    => true,
				    ),
				) ) );
			}

	// Timezones
		//@2.3
		private function __get_evo_timezone_choices($selected_zone, $locale = null ){
			return apply_filters(
				'evo_events_timezone_choice',
				wp_timezone_choice( $selected_zone, $locale ),
				$selected_zone,
				$locale
			);
		}

		// return readable list of wp based timezone values @4.7.1
		function get_modified_wp_timezone_list($unix = ''){
			// using WP timezones
			$html = $this->__get_evo_timezone_choices('UTC');

			preg_match_all('/<option value="([^"]+)">/', $html, $matches);
			$tzs = $matches[1];

			$DD = new DateTime( 'now' );

			// if unix is passed adjust according to time present in unix
			if( !empty( $unix ))	$DD->setTimestamp( $unix );

			$updated_zones = array();
			foreach($tzs as $tz_string ){

				if(	strpos($tz_string, 'UTC') !== false ) continue;

				try {
					$DD->setTimezone( new DateTimeZone( $tz_string ));
				}
				catch (Exception $e) {
					// invalid timezone name
					error_log(print_r($e->getMessage(), TRUE));
					continue;
				}

				$updated_zones[ $tz_string ] = '(GMT'. $DD->format('P').') '. $tz_string;
				
			}

			return $updated_zones;
		}

		// @updated 4.5.7
		// $unix value passed to calculate DST for a given date - otherwise DST for now
		function get_timezone_array( $unix = '' , $adjusted = true) {
			return $this->get_modified_wp_timezone_list( $unix );
		}
		
		// return time offset from saved timezone values @4.5.2
		public function _get_tz_offset_seconds( $tz_key, $unix = ''){

			$DD = new DateTime( 'now' );

			// set passed on tz key
			try {
				$DD->setTimezone( new DateTimeZone( $tz_key ));
			}
			catch (Exception $e) {
				// invalid timezone name
				error_log(print_r($e->getMessage(), TRUE));	
				$DD->setTimezone( new DateTimeZone( 'UTC' ));			
			}

			if( !empty( $unix ))	$DD->setTimestamp( $unix );

			$GMT_value = $DD->format('P');

			// if it is UTC 0
			if(strpos($GMT_value, '+0:') !== false)	return 0;

			// alter
			if(strpos($GMT_value, '+') !== false){
				$ss = str_replace('+', '-', $GMT_value);
			}else{
				$ss = str_replace('-', '+', $GMT_value);	
			}

			// convert to seconds
			sscanf($ss, "%d:%d", $hours, $minutes);

			return $hours * 3600 + $minutes * 60;
		}

		// return GMT value
		function get_timezone_gmt($key, $unix = false){

			$DD = new DateTime();
			if($unix) $DD->setTimestamp($unix);
			$DD->setTimezone( new DateTimeZone( $key ));

			return 'GMT'. $DD->format('P');
		}

	// ICS modifications

		// return utc offset time for unix
		function get_ics_format_from_unix( $unix, $sep = true){
			
			$enviro = new EVO_Environment();

			$unix = $unix - $enviro->get_UTC_offset();

			if(!$sep) return $unix;
			
			$new_timeT = gmdate("Ymd", $unix);
			$new_timeZ = gmdate("Hi", $unix);
			return $new_timeT.'T'.$new_timeZ.'00Z';
		}

		// Escape ICS text
			function esc_ical_text( $text='' ) {
				
			    $text = str_replace("\\", "", $text);
			    $text = str_replace("\r", "\r\n ", $text);
			    $text = str_replace("\n", "\r\n ", $text);
			    $text = str_replace(",", "\, ", $text);
			    $text = EVO()->calendar->helper->htmlspecialchars_decode($text);
			    return $text;
			}

}