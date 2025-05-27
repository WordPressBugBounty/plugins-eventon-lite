<?php
/**
 * Function ajax for backend
 * @version   2.4.6
 */
class EVO_admin_ajax{
	public $post_data;
	
	public function __construct(){
		$ajax_events = array(		
			'get_shortcode_generator'=>'get_shortcode_generator',	

			'export_events'			=>'export_events',	
			'export_settings'		=>'export_settings',
			'get_import_settings'	=>'get_import_settings',
			'import_settings'		=>'import_settings',
			
			'rel_event_list'		=>'rel_event_list',
			'get_latlng'				=>'get_latlng',

			'generate_custom_repeat_unix' =>'generate_custom_repeat_unix',
			'edit_custom_repeat' =>'edit_custom_repeat',

			'admin_get_environment'		=>'admin_get_environment',
			'admin_system_log'			=>'admin_system_log',
			'admin_system_log_flush'		=>'admin_system_log_flush',

			'get_secondary_settings'=> 'get_secondary_settings',
			'save_secondary_settings'=> 'save_secondary_settings',

			'config_virtual_event'	=>'config_virtual_event',
			'select_virtual_moderator'	=>'select_virtual_moderator',
			'get_virtual_users'	=>'get_virtual_users',
			'save_virtual_mod_settings'	=>'save_virtual_mod_settings',
			'save_virtual_event_settings'	=>'save_virtual_event_settings',

			// save general settings
			'general_settings_save'			=> 'settings_save', // 4.8
		);

		$restricted_actions = [];
		foreach ( $ajax_events as $ajax_event => $class ) {

			$prepend = 'eventon_';
			add_action( 'wp_ajax_'. $prepend . $ajax_event, array( $this, $class ) );

			// for non loggedin user actions
			if( in_array( $ajax_event, $restricted_actions)){
				add_action( 'wp_ajax_nopriv_'. $prepend . $ajax_event, array( $this, 'restrict_unauthenticated' ) );
			}else{
				add_action( 'wp_ajax_nopriv_'. $prepend . $ajax_event, array( $this, $class ) );
			}
		}

		add_action('wp_ajax_eventon-feature-event', array($this, 'eventon_feature_event'));
		add_action('wp_ajax_nopriv_eventon-feature-event', array($this, 'restrict_unauthenticated'));

		$this->post_data = EVO()->helper->sanitize_array( $_POST );
	}	

	// Handle unauthenticated requests
    public function restrict_unauthenticated() {
        wp_send_json( array( 'status' => 'bad', 'msg' => __( 'Authentication required', 'eventon' )) );
        wp_die();
    }

	// shortcode generator
		public function get_shortcode_generator(){
			// Allow all roles, with nonce check, authorization check, read capability
        	EVO()->helper->validate_request( 'nn', 'eventon_admin_nonce', 'read', false, true );

			$sc = isset($this->post_data['sc']) ? stripslashes( $this->post_data['sc'] ): 'add_eventon';

			$content = EVO()->shortcode_gen->get_content();	

			wp_send_json(array(
				'status'=>'good',
				'content'=> $content,
				'sc' => sanitize_text_field( $sc ),
	            'type' => isset( $this->post_data['type'] ) ? sanitize_text_field( $this->post_data['type'] ) : '',
	            'other_id' => isset( $this->post_data['other_id'] ) ? sanitize_text_field( $this->post_data['other_id'] ) : '',
			));wp_die();	
		}

	// generate custom repeat instance unix
		public function generate_custom_repeat_unix(){
			// Allow all roles, with nonce check, authorization check
			EVO()->helper->validate_request( 'nn', 'eventon_admin_nonce', false, false, true );

			$msg = '';
			$PD = $this->post_data;
			//EVO_Debug($PD);

			// required data check
			if( empty($PD['event_new_repeat_start_date_x']) || empty( $PD['event_new_repeat_end_date_x'])){
				wp_send_json(['msg'=> __('Missing required data!','eventon')]); wp_die();
			}

			// generate unix from passed data
			$timezone = EVO()->calendar->timezone0 ?: new DateTimeZone('UTC');
			$_is_24h = (!empty($PD['_evo_time_format']) && $PD['_evo_time_format']=='24h')? true:false;
			$time_format = $_is_24h ? 'H:i':'g:ia';


			$new_index = (int)$PD['new_index'] +1;
			// if editing interval
			if( !empty($PD['edit_index'])) $new_index = (int)$PD['edit_index'];

			// time strings
			$start_time_string = $PD['_new_repeat_start_hour'].':'.$PD['_new_repeat_start_minute']. ( isset($PD['_new_repeat_start_ampm'])? $PD['_new_repeat_start_ampm']:'');
			$end_time_string = $PD['_new_repeat_end_hour'].':'.$PD['_new_repeat_end_minute']. ( isset($PD['_new_repeat_end_ampm'])? $PD['_new_repeat_end_ampm']:'');

			// generate unix from passed time Y/m/d (H:i / g:ia)
			$start_unix = DateTime::createFromFormat('Y/m/d '. $time_format, $PD["event_new_repeat_start_date_x"].$start_time_string, $timezone );
			$end_unix = DateTime::createFromFormat('Y/m/d '. $time_format, $PD["event_new_repeat_end_date_x"]. $end_time_string, $timezone );

			// check if unix generated
			$start_unix_val = $end_unix_val = null;
			if ($start_unix && $end_unix) {
		       	$start_unix_val = $start_unix->format('U');
		        $end_unix_val = $end_unix->format('U');
		        
		    } else {
		        error_log('Failed to parse interval: ' . print_r($PD, true));
 		        $output['msg'] = __('Failed to parse interval','eventon');
				wp_send_json($output); wp_die();
		    }
			

			$start_dt = $PD["event_new_repeat_start_date"] .' '. $start_time_string;
			$end_dt = $PD["event_new_repeat_end_date"] .' '. $end_time_string;

			$_html =  '<li data-cnt="'.$new_index.'" style="display:flex" class="'.($new_index==0?'initial':'').($new_index>3?' over':'').'">'. ($new_index==0? '<dd>'.__('Initial','eventon').'</dd>':'').'<i>'.$new_index.'</i><span>'.__('from','eventon').'</span> '. $start_dt .' <span class="e">End</span> '. $end_dt .
				'<span class="evodfxi evofxdrr evofxaic evoclwi evogap5 evofxjcfe">
					<em class="evo_rep_edit evodfx evofxjcc evofxaic" alt="Edit"><i class="fa fa-pencil"></i></em>
					<em class="evo_rep_del evodfx evofxjcc evofxaic" alt="Delete"><i class="fa fa-times"></i></em>
				</span>'.
				'<input type="hidden" name="repeat_intervals['.$new_index.'][0]" value="'.$start_unix_val.'"/><input type="hidden" name="repeat_intervals['.$new_index.'][1]" value="'.$end_unix_val.'"/>'
				.'</li>';
			$msg = __('Repeat Instance Added','eventon');
			
			wp_send_json(array(
				'status'=> 'good',
				'content'=> $_html,
				'msg'=> $msg
			));
			wp_die();

		}

		public function edit_custom_repeat(){
			EVO()->helper->validate_request( 'nn', 'eventon_admin_nonce', false, false, true );
			$PD = $this->post_data;

			$_is_24h = (!empty($PD['_evo_time_format']) && $PD['_evo_time_format']=='24h')? true:false;
			$date_format = !empty($PD['_evo_date_format']) ? $PD['_evo_date_format'] : 'Y/m/d';
			$time_format = $_is_24h ? 'H:i' : 'g:ia';

			$timezone = new DateTimeZone('UTC');
			$index = key($PD['repeat_intervals']); // Get dynamic index
		    $start_unix = $PD['repeat_intervals'][$index][0];
		    $end_unix = $PD['repeat_intervals'][$index][1];

		    $start_dt = new DateTime("@$start_unix", $timezone);
		    $end_dt = new DateTime("@$end_unix", $timezone);

		    $output = [
		        'start_date' => $start_dt->format($date_format),
		        'start_date_x' => $start_dt->format('Y/m/d'),
		        'start_hour' => $start_dt->format($_is_24h ? 'H' : 'g'),
		        'start_minute' => $start_dt->format('i'),
		        'start_ampm' => $_is_24h ? '' : $start_dt->format('a'),
		        'end_date' => $end_dt->format($date_format),
		        'end_date_x' => $end_dt->format('Y/m/d'),
		        'end_hour' => $end_dt->format($_is_24h ? 'H' : 'g'),
		        'end_minute' => $end_dt->format('i'),
		        'end_ampm' => $_is_24h ? '' : $end_dt->format('a')
		    ];

		    wp_send_json($output);
		    wp_die();


		}
		
	// get secondary lightbox settings
		public function get_secondary_settings(){

			// Validate request
			EVO()->helper->validate_request();
			
			$post_data = EVO()->helper->sanitize_array( $_POST);
			$settings_file_key = isset($post_data['setitngs_file_key']) ? $post_data['setitngs_file_key'] : '';
			$allowed_files = array(
			    'cmf_settings' => plugin_dir_path(__FILE__) . 'views/cmf_settings.php',
			);			

			if (array_key_exists($settings_file_key, $allowed_files) && file_exists($allowed_files[$settings_file_key])) {
			    ob_start();
			    include_once($allowed_files[$settings_file_key]);
			    wp_send_json([
			        'status' => 'good',
			        'content' => ob_get_clean()
			    ]);
			} else {
				error_log('Invalid settings file key attempted: ' . $settings_file_key);
			    wp_send_json([
			        'status' => 'bad',
			        'msg' => __('Invalid settings file requested', 'eventon')
			    ]);
			}
			wp_die();
			
		}
		public function save_secondary_settings(){

			// Validate request
			EVO()->helper->validate_request('evo_noncename','evo_save_secondary_settings');
			

			$post_data = EVO()->helper->sanitize_array( $_POST);

			// if html fields passed
			$html_fields = false;
			if(  isset( $post_data['html_fields'] ) ){
				$html_fields = json_decode(  stripslashes( $post_data['html_fields']) );

				$html_fields = is_array($html_fields) ? $html_fields : false;
			}


			$EVENT = new EVO_Event( $post_data['event_id']);

			foreach($post_data as $key=>$val){

				// skip fields
				if( in_array( $key, array('evo_noncename','event_id','_wp_http_referer'))) continue;
				
				// html content
				if( $html_fields &&  in_array($key, $html_fields )){
					$val = EVO()->helper->sanitize_html( $_POST[ $key ] );
				}

				$EVENT->save_meta($key, $val);
			}

			wp_send_json(array(
				'status'=>'good','msg'=> __('Event Data Saved Successfully','eventon')
			)); wp_die();
		}
	
	// virtual events
		public function config_virtual_event(){

			// Validate request
			EVO()->helper->validate_request();
			

			$post_data = EVO()->helper->sanitize_array( $_POST);

			$EVENT = new EVO_Event( (int) $post_data['eid'] );

			ob_start();

			include_once('views/virtual_event_settings.php');

			wp_send_json(array(
				'status'=>'good','content'=> ob_get_clean()
			));
			wp_die();
		}
		public function select_virtual_moderator(){

			// Validate request
			EVO()->helper->validate_request();
			
			ob_start();

			$eid = (int) $_POST['eid'];
			$EVENT = new EVO_Event( $eid);			
			$set_user_role = $EVENT->get_prop('_evo_user_role');
			$set_mod = $EVENT->get_prop('_mod');

			global $wp_roles;
			?>
			<div style="padding:20px">
				<form class='evo_vir_select_mod'>
					<input type="hidden" name="action" value='eventon_save_virtual_mod_settings'>
					<input type="hidden" name="eid" value='<?php echo esc_attr( $eid );?>'>

					<?php wp_nonce_field( 'evo_save_virtual_mod_settings', 'evo_noncename' );?>
					
					<p class='row'>
						<label><?php _e('Select a user role to find users');?></label>
						<select class='evo_select_more_field evo_virtual_moderator_role' name='_user_role' data-eid='<?php echo esc_attr( $eid );?>'>
							<option value=''> -- </option>
							<?php 
							
							foreach($wp_roles->roles as $role_slug=>$rr){
								$select = $set_user_role == $role_slug ? 'selected="selected"' :'';
								echo "<option value='". esc_attr( $role_slug ). "' ". esc_html( $select ).">". esc_attr( $rr['name'] ) .'</option>';
							}

						?></select>
					</p>
					<p class='row evo_select_more_field_2'>
						<label><?php _e('Select a user for above role');?></label>
						<select name='_mod' class='evo_virtual_moderator_users'>
							<?php
							if( $set_user_role ):
								echo $this->get_virtual_users_select_options( esc_attr( $set_user_role ), esc_attr( $set_mod ) );
							else:
							?>
								<option value=''>--</option>
							<?php endif;?>
						</select>
					</p>
					<p class='evo_save_changes' ><span class='evo_btn save_virtual_event_mod_config ' data-eid='<?php echo esc_attr( $eid );?>' style='margin-right: 10px'><?php _e('Save Changes','eventon');?></span></p>
				</form>
			</div>

			<?php

			wp_send_json(array(
				'status'=>'good','content'=> ob_get_clean()
			));wp_die();
		}
		public function get_virtual_users_select_options($role_slug, $set_user_id=''){
			
			// Validate request
			EVO()->helper->validate_request();

			$users = get_users( array( 
				'role' => $role_slug,
				'fields'=> array('ID','user_email', 'display_name') 
			) );
			$output = false;
			
			if($users){
				foreach($users as $user){
					$select = ( !empty($set_user_id) && $set_user_id == $user->ID) ? "selected='selected'":'';
					$output .= "<option value='". esc_attr( $user->ID )."' ". esc_html( $select ).">".esc_attr( $user->display_name ) . " (".esc_attr( $user->user_email ) . ")</option>";
				}
			}
			return $output;
		}
		public function get_virtual_users(){

			// Validate request
			EVO()->helper->validate_request();

			$user_role = sanitize_text_field( $_POST['_user_role']);

			wp_send_json(array(
				'status'=>'good',
				'content'=> empty($user_role) ? 
					"<option value=''>--</option>" : 
					$this->get_virtual_users_select_options($user_role)
			)); wp_die();

			
		}
		public function save_virtual_event_settings(){

			// Validate request
			EVO()->helper->validate_request('evo_noncename','evo_save_virtual_event_settings');
			
			$post_data = EVO()->helper->sanitize_array( $_POST);

			$EVENT = new EVO_Event( $post_data['event_id']);

			foreach($post_data as $key=>$val){
				if( in_array($key, array( '_vir_url','_vir_after_content','_vir_pre_content','_vir_embed'))){
					$val = $post_data[$key];
				}

				$EVENT->save_meta($key, $val);
			}

			wp_send_json(array(
				'status'=>'good','msg'=> __('Virtual Event Data Saved Successfully','eventon')
			)); wp_die();
		}
		public function save_virtual_mod_settings(){

			// Validate request
			EVO()->helper->validate_request('evo_noncename','evo_save_virtual_mod_settings');
			

			$post_data = EVO()->helper->sanitize_array( $_POST);	

			$EVENT = new EVO_Event( (int)$post_data['eid']);

			$EVENT->save_meta('_evo_user_role', $post_data['_user_role']);
			$EVENT->save_meta('_mod', $post_data['_mod']);

			wp_send_json(array(
				'status'=>'good','msg'=> __('Moderator Data Saved Successfully','eventon')
			)); wp_die();
			
		}
		
	// Related Events @2.3
		public function rel_event_list(){

			// Validate request
			EVO()->helper->validate_request();		

			$post_data = EVO()->helper->sanitize_array( $_POST);
			$event_id = (int)$post_data['eventid'];
			$EVs = json_decode( stripslashes($post_data['EVs']), true );

			$wp_args = array(
				'posts_per_page'=>-1,
				'post_type'=>'ajde_events',
				'post__not_in'=> array( $event_id ),
				'post_status'=>'publish'
			);
			$events = new WP_Query($wp_args );

			
			$content = '';

			$content .= "<div class='evo_rel_events_form' data-eventid='{$event_id}'>";

			$ev_count = 0;

			// each event
			if($events->have_posts()){	
				
					
				$events_list = array();

				foreach( $events->posts as $post ) {		

					$event_id = $post->ID;
					$EV = new EVO_Event($event_id);

					$time = $EV->get_formatted_smart_time();

					ob_start();
					?><span class='rel_event<?php echo (is_array($EVs) && array_key_exists($event_id.'-0', $EVs))?' select':'';?>' data-id="<?php echo esc_attr( $event_id ).'-0';?>" data-n="<?php echo esc_attr( htmlentities($post->post_title, ENT_QUOTES) ); ?>" data-t='<?php echo esc_attr( $time );?>'><b></b>
						<span class='o'>
							<span class='n evofz14'><?php echo esc_attr( $post->post_title );?></span>
							<span class='t'><?php echo esc_attr( $time );?></span>							
						</span>
					</span><?php

					$events_list[ $EV->get_start_time() . '_' . $event_id ] = ob_get_clean();
					$ev_count++;

					$repeats = $EV->get_repeats_count();
					if($repeats){
						for($x=1; $x<=$repeats; $x++){
							$EV->load_repeat($x);
							$time = $EV->get_formatted_smart_time($x);

							ob_start();

							$select = (is_array($EVs) && array_key_exists($event_id.'-'.$x, $EVs) ) ?' select':'';
							
							?><span class='rel_event<?php echo esc_attr( $select );?>' data-id="<?php echo esc_attr( $event_id ).'-'. esc_attr( $x );?>" data-n="<?php echo esc_attr( htmlentities($post->post_title, ENT_QUOTES) );?>" data-t='<?php echo esc_attr( $time );?>'><b></b>
								<span class='o'>									
									<span class='n evofz14'><?php echo esc_attr( $post->post_title );?></span>
									<span class='t'><?php echo esc_attr( $time );?></span>
								</span>
							</span><?php

							$events_list[ $EV->get_start_time() . '_' . $x ] = ob_get_clean();
							$ev_count++;
						}
					}
				}

				krsort($events_list);

				$content .= "<div class='evo_rel_search'>
					<span class='evo_rel_ev_count' data-t='".__('Events','eventon')."'>". esc_attr( $ev_count ) .' '. __('Events','eventon') ."</span>
					<input class='evo_rel_search_input' type='text' name='event' value='' placeholder='" . __('Search events by name','eventon'). " '/>
				</div>
				<div class='evo_rel_events_list'>";


				foreach($events_list as $ed=>$ee){
					$content .= $ee;
				}
				
				$content .= "</div><p style='text-align:center; padding-top:10px;'><span class='evo_btn evo_save_rel_events'>". __('Save Changes','eventon') ."</span></p>";
				
			}else{
				$content .= "<p>". __('You must create events first!','eventon') ."</p>";
			}

			$content .= "</div>";

			wp_send_json(array(
				'status'=>'good',
				'content'=> $content
			)); wp_die();
		}


	// Get Location Cordinates
		public function get_latlng(){

			// Validate request
			EVO()->helper->validate_request('nn','eventon_admin_nonce', 'read', false ,true);		

			$gmap_api = EVO()->cal->get_prop('evo_gmap_api_key', 'evcal_1');

			if( !isset($_POST['address'])){
				wp_send_json(array(
				'status'=>'bad','m'=> __('Address Missing','eventon'))); wp_die();
			}

			$address = sanitize_text_field($_POST['address']);
			
			$address = str_replace(" ", "+", $address);
			$address = urlencode($address);
			
			$url = "https://maps.google.com/maps/api/geocode/json?address=$address&sensor=false&key=".$gmap_api;

			$response = wp_remote_get($url);

			$response = wp_remote_retrieve_body( $response );
			if(!$response){ 
				wp_send_json(array(
				'status'=>'bad','m'=> __('Could not connect to google maps api','eventon'))); wp_die();
			}

			$RR = json_decode($response);

			if( !empty( $RR->error_message)){
				wp_send_json(array(
				'status'=>'bad','m'=> $RR->error_message )); wp_die();
			}

		    wp_send_json(array(
				'status'=>'good',
				'lat' => $RR->results[0]->geometry->location->lat,
		        'lng' => $RR->results[0]->geometry->location->lng,
			)); wp_die();
		}

	// export eventon settings
		public function export_settings(){

			// Validate request
			EVO()->helper->validate_request('nonce','evo_export_settings', 'edit_eventons', true ,true);		
		

			header('Content-type: text/plain');
			header("Content-Disposition: attachment; filename=Evo_settings__". gmdate("d-m-y").".json");
			
			$json = array();
			$evo_options = get_option('evcal_options_evcal_1');
			foreach($evo_options as $field=>$option){
				// skip fields
				if(in_array($field, array('option_page','action','_wpnonce','_wp_http_referer'))) continue;
				$json[$field] = $option;
			}

			wp_send_json($json); wp_die();
		}

	// import settings
		public function get_import_settings(){
			// Validate request
			EVO()->helper->validate_request('nn','eventon_admin_nonce', 'edit_eventons', true ,true);		

			$output = array('status'=>'bad','msg'=>'');
			
			ob_start();

			EVO()->elements->print_import_box_html(array(
				'box_id'=>'evo_settings_upload',
				'title'=>__('Upload JSON Settings File Form'),
				'message'=>__('NOTE: You can only upload settings data as .json file'),
				'file_type'=>'.json',
				'type'		=> 'inlinebox'
			));

			$output['status'] = 'good';
			$output['content'] = ob_get_clean();

			wp_send_json($output); wp_die();
			

		}
		public function import_settings(){
			// Validate request
			EVO()->helper->validate_request('nonce','eventon_admin_nonce', 'edit_eventons', true ,true);		

			$output = array('status'=>'bad','msg'=>'');			
			$post_data = EVO()->helper->sanitize_array( $_POST);
			$JSON_data = isset( $post_data['jsondata'] ) ? $post_data['jsondata'] : false;

			// check if json array present
			if( $JSON_data && !is_array($JSON_data)){
				$output['msg'] = __('Uploaded file is not a json format!','eventon');
				wp_send_json($output); wp_die();
			}  

			// if all good
			if( empty($output['msg'])){

				// process the fields and save to options
				update_option('evcal_options_evcal_1', $JSON_data);

				$output['status'] = 'good';
				$output['msg'] = 'Successfully updated settings!';
			}
			
			wp_send_json($output); wp_die();
		}

	// export events as CSV
		public function export_events(){

			// Validate request
			EVO()->helper->validate_request('nonce','eventon_download_events', 'edit_eventons', true ,true,'message');		


			$run_process_content = false;
			
			header('Content-Encoding: UTF-8');
        	header('Content-type: text/csv; charset=UTF-8');
			header("Content-Disposition: attachment; filename=Eventon_events_".gmdate("d-m-y").".csv");
			header("Pragma: no-cache");
			header("Expires: 0");
			echo "\xEF\xBB\xBF"; // UTF-8 BOM
			
			$evo_opt = get_option('evcal_options_evcal_1');
			$event_type_count = evo_get_ett_count($evo_opt);
			$cmd_count = evo_calculate_cmd_count($evo_opt);

			$fields = apply_filters('evo_csv_export_fields',array(
				'publish_status',	
				'event_id',			
				'evcal_event_color'=>'color',
				'event_name',				
				'event_description','event_start_date','event_start_time','event_end_date','event_end_time',

				'evcal_allday'=>'all_day',
				'evo_hide_endtime'=>'hide_end_time',
				'evcal_gmap_gen'=>'event_gmap',
				'evo_year_long'=>'yearlong',
				'_featured'=>'featured',

				'evo_location_id'=>'evo_location_id',
				'evcal_location_name'=>'location_name',	// location name			
				'evcal_location'=>'event_location',	// address		
				'location_desc'=>'location_description',	
				'location_lat'=>'location_latitude',	
				'location_lon'=>'location_longitude',	
				'location_link'=>'location_link',	
				'location_img'=>'location_img',	
				
				'evo_organizer_id'=>'evo_organizer_id',
				'evcal_organizer'=>'event_organizer',
				'organizer_description'=>'organizer_description',
				'organizer_contact'=>'evcal_org_contact',
				'organizer_address'=>'evcal_org_address',
				'organizer_link'=>'evcal_org_exlink',
				'organizer_img'=>'evo_org_img',

				'evcal_subtitle'=>'evcal_subtitle',
				'evcal_lmlink'=>'learnmore link',
				'image_url',

				'evcal_repeat'=>'repeatevent',
				'evcal_rep_freq'=>'frequency',
				'evcal_rep_num'=>'repeats',
				'evp_repeat_rb'=>'repeatby',
			));
			
			// Print out the CSV file header
				$csvHeader = '';
				foreach($fields as $var=>$val){	$csvHeader.= $val.',';	}

				// event types
					for($y=1; $y<=$event_type_count;  $y++){
						$_ett_name = ($y==1)? 'event_type': 'event_type_'.$y;
						$csvHeader.= $_ett_name.',';
						$csvHeader.= $_ett_name.'_slug,';
					}
				// for event custom meta data
					for($z=1; $z<=$cmd_count;  $z++){
						$_cmd_name = 'cmd_'.$z;
						$csvHeader.= $_cmd_name.",";
					}

				$csvHeader = apply_filters('evo_export_events_csv_header',$csvHeader);
				$csvHeader.= "\n";
				
				echo (function_exists('iconv'))? iconv("UTF-8", "ISO-8859-2", $csvHeader): $csvHeader;
 	
 			// events
			$events = new WP_Query(array(
				'posts_per_page'=>-1,
				'post_type' => 'ajde_events',
				'post_status'=>'any'			
			));

			if($events->have_posts()):

				$DD = new DateTime('now', EVO()->calendar->timezone0);
				
				// allow processing content for html readability
				$process_html_content = true;

				// for each event
				while($events->have_posts()): $events->the_post();
					$__id = get_the_ID();
					$pmv = get_post_meta($__id);

					// create Event
					$EVENT = new EVO_Event( $__id, '', 0, true, $events->post );


					$csvRow = '';
					$csvRow.= get_post_status($__id).",";
					$csvRow.= $__id.",";
					$loctaxid = $orgtaxid = '';
					$loctaxname = $orgtaxname = '';

					$csvRow.= ( $EVENT->get_hex() ).",";

					// location for this event
						$lDATA = $EVENT->get_location_data();
						$location_term_meta = $event_location_term_id = false;
						
						if ( $lDATA ){
							$event_location_term_id = $lDATA['location_term_id'];
							$location_term_meta = $lDATA;
						}

					// Organizer for this event
						$_event_organizer_term = wp_get_object_terms( $__id, 'event_organizer' );
						$organizer_term_meta = $organizer_term_id = false;
						if( $_event_organizer_term && !is_wp_error($_event_organizer_term)){
							$organizer_term_id = $_event_organizer_term[0]->term_id;
							$organizer_term_meta = evo_get_term_meta('event_organizer',$organizer_term_id, '', true);
						}

					// Event Initial
						// event name
							$eventName = $EVENT->get_title();
							if( $run_process_content){
								$eventName = $this->html_process_content($eventName, $process_html_content);
								$eventName = iconv("utf-8", "ascii//TRANSLIT//IGNORE", $eventName);
								$eventName =  preg_replace("/^'|[^A-Za-z0-9\s-]|'$/", '', $output); 
								$eventName = str_replace('&amp;#8217;', "'", $eventName);
							}
							$csvRow.= '"'. $eventName.'",';

						// summary for the ICS file
						$event_content = (!empty($EVENT->content))? $EVENT->content:'';
							$event_content = str_replace('"', "'", $event_content);
							$event_content = str_replace(',', "\,", $event_content);
							if( $run_process_content){
								$event_content = $this->html_process_content( $event_content, $process_html_content);
							}
						$csvRow.= '"'.$event_content.'",';

						// start time
							$start = (!empty($pmv['evcal_srow'])?$pmv['evcal_srow'][0]:'');
							if(!empty($start)){
								$DD->setTimestamp( $start);
								// date and time as separate columns
								$csvRow.= '"'. $DD->format( apply_filters('evo_csv_export_dateformat','m/d/Y') ) .'",';
								$csvRow.= '"'. $DD->format( apply_filters('evo_csv_export_timeformat','h:i:A') ) .'",';
							}else{ $csvRow.= "'','',";	}

						// end time
							$end = (!empty($pmv['evcal_erow'])?$pmv['evcal_erow'][0]:'');
							if(!empty($end)){
								$DD->setTimestamp( $end);
								// date and time as separate columns
								$csvRow.= '"'. $DD->format( apply_filters('evo_csv_export_dateformat','m/d/Y') ) .'",';
								$csvRow.= '"'. $DD->format( apply_filters('evo_csv_export_timeformat','h:i:A') ) .'",';
							}else{ $csvRow.= "'','',";	}

						
					// FOR EACH field
					
					foreach($fields as $var=>$val){
						// skip already added fields
							if(in_array($val, array('publish_status',	
								'event_id',			
								'color',
								'event_name',				
								'event_description','event_start_date','event_start_time','event_end_date','event_end_time',))){
								continue;
							}
						
						// yes no values
							if(in_array($val, array('featured','all_day','hide_end_time','event_gmap','evo_year_long','_evo_month_long','repeatevent'))){

								$csvRow.= ( (!empty($pmv[$var]) && $pmv[$var][0]=='yes') ? 'yes': 'no').',';
								continue;
							}

						// organizer field
							$continue = false;
							switch($val){
								case 'evo_organizer_id':
									if($organizer_term_id){
										$csvRow .= '"'. $organizer_term_id .'",';
									}else{
										$csvRow.= ",";
									}
									$continue = true;
								break;
								case 'event_organizer':
									if($organizer_term_id){
										$csvRow.= '"'. $this->html_process_content($_event_organizer_term[0]->name, $process_html_content) . '",';	
									}elseif(!empty($pmv[$var]) ){
										$value = $this->html_process_content($pmv[$var][0], $process_html_content);
										$csvRow.= '"'.$value.'"';
									}else{	$csvRow.= ",";	}
									$continue = true;
								break;
								case 'organizer_description':
									if($organizer_term_id){
										$csvRow.= '"'. $this->html_process_content($_event_organizer_term[0]->description) . '",';
									}else{	$csvRow.= ",";	}
									$continue = true;
								break;
								case 'evcal_org_contact':
									$csvRow.= ($organizer_term_meta && !empty($organizer_term_meta['evcal_org_contact'])) ? '"'. $this->html_process_content($organizer_term_meta['evcal_org_contact']) .'",':
										","; $continue = true;
								break;
								case 'evcal_org_address':
									$csvRow.= ($organizer_term_meta && !empty($organizer_term_meta['evcal_org_address'])) ? '"'. $this->html_process_content($organizer_term_meta['evcal_org_address']) .'",':
										","; $continue = true;
								break;
								case 'evcal_org_exlink':
									$csvRow.= ($organizer_term_meta && !empty($organizer_term_meta['evcal_org_exlink'])) ? '"'. $this->html_process_content($organizer_term_meta['evcal_org_exlink']) .'",':
										","; $continue = true;
								break;
								case 'evo_org_img':
									$csvRow.= ($organizer_term_meta && !empty($organizer_term_meta['evo_org_img'])) ? '"'. $organizer_term_meta['evo_org_img'] .'",':","; $continue = true;
								break;
							}
							if($continue) continue;

						// location tax field
							$continue = false;
							switch ($val){
								case 'location_description':
									if ( $event_location_term_id && !empty($location_term_meta['location_description']) ){
										$csvRow.= '"'. $this->html_process_content( $location_term_meta['location_description']) . '",';
									}else{	$csvRow.= ",";	}
									$continue = true;
								break;
								case 'evo_location_id':
									if ( $event_location_term_id ){
										$csvRow.= '"'.$event_location_term_id . '",';
									}else{	$csvRow.= ",";	}
									$continue = true;
								break;
								case 'location_name':
									if($event_location_term_id && !empty(  $location_term_meta['location_name'] )){
										$csvRow.= '"'. $this->html_process_content( $location_term_meta['location_name'], $process_html_content) . '",';									
									}elseif(!empty($pmv[$var]) ){
										$value = $this->html_process_content($pmv[$var][0], $process_html_content);
										$csvRow.= '"'.$value.'"';
									}else{	$csvRow.= ",";	}
									$continue = true;
								break;
								case 'event_location':
									if($location_term_meta){
										$csvRow.= !empty($location_term_meta['location_address'])? 
											'"'. $this->html_process_content($location_term_meta['location_address'], $process_html_content) . '",':
											",";									
									}elseif(!empty($pmv[$var]) ){
										$value = $this->html_process_content($pmv[$var][0], $process_html_content);
										$csvRow.= '"'.$value.'"';
									}else{	$csvRow.= ",";	}
									$continue = true;
								break;
								case 'location_latitude':
									$csvRow.= ($location_term_meta && !empty($location_term_meta['location_lat'])) ? '"'. $location_term_meta['location_lat'] .'",':
										","; $continue = true;									
								break;
								case 'location_longitude':
									$csvRow.= ($location_term_meta && !empty($location_term_meta['location_lon'])) ? '"'. $location_term_meta['location_lon'] .'",':
										","; $continue = true;									
								break;
								case 'location_link':
									$csvRow.= ($location_term_meta && !empty($location_term_meta['evcal_location_link'])) ? '"'. $location_term_meta['evcal_location_link'] .'",':
										","; $continue = true;									
								break;
								case 'location_img':
									$csvRow.= ($location_term_meta && !empty($location_term_meta['evo_loc_img'])) ? '"'. $location_term_meta['evo_loc_img'] .'",':
										","; $continue = true;									
								break;
							}

							if($continue) continue;

						// skip fields
						if(in_array($val, array('featured','all_day','hide_end_time','event_gmap','evo_year_long','_evo_month_long','repeatevent','color','publish_status','event_name','event_description','event_start_date','event_start_time','event_end_date','event_end_time','evo_organizer_id', 'evo_location_id'
							)
						)) continue;

						// image
							if($val =='image_url'){
								$img_id =get_post_thumbnail_id($__id);
								if($img_id!=''){
									
									$img_src = wp_get_attachment_image_src($img_id,'full');
									if($img_src){
										$csvRow.= $img_src[0].",";
									}else{
										$csvRow.= ",";
									}
									
								}else{ $csvRow.= ",";}
							}else{
								if(!empty($pmv[$var])){
									$value = $this->html_process_content($pmv[$var][0], $process_html_content);
									$csvRow.= '"'.$value.'"';
								}else{ $csvRow.= '';}
								$csvRow.= ',';
							}
					}
					
					// event types
						for($y=1; $y<=$event_type_count;  $y++){
							$_ett_name = ($y==1)? 'event_type': 'event_type_'.$y;
							$terms = get_the_terms( $__id, $_ett_name );

							if ( $terms && ! is_wp_error( $terms ) ){
								$csvRow.= '"';
								foreach ( $terms as $term ) {
									$csvRow.= $term->term_id.',';
									//$csvRow.= $term->name.',';
								}
								$csvRow.= '",';

								// slug version
								$csvRow.= '"';
								foreach ( $terms as $term ) {
									$csvRow.= $term->slug.',';
								}
								$csvRow.= '",';
							}else{ $csvRow.= ",";}
						}
					// for event custom meta data
						for($z=1; $z<=$cmd_count;  $z++){
							$cmd_name = '_evcal_ec_f'.$z.'a1_cus';
							$csvRow.= (!empty($pmv[$cmd_name])? 
								'"'.str_replace('"', "'", $this->html_process_content($pmv[$cmd_name][0], $process_html_content) ) .'"'
								:'');
							$csvRow.= ",";
						}

					$csvRow = apply_filters('evo_export_events_csv_row',$csvRow, $__id, $pmv);
					$csvRow.= "\n";

					if( EVO()->cal->check_yn('evo_disable_csv_formatting','evcal_1')){
						echo $csvRow;
					}else{
						echo (function_exists('iconv'))? iconv("UTF-8", "ISO-8859-2", $csvRow): $csvRow;
					}
				

				endwhile;
			endif;

			wp_reset_postdata();
		}

	// saving general settings -- @added 4.8 @updated 4.8.1		
		
		// loadin new language
		public function settings_load_new_lang(){}

		// save language settings
		public function settings_save(){

			// Validate request
			EVO()->helper->validate_request('evoajax','eventon_settings_save_nonce', 'edit_eventons', true ,true);		


	        // Decode JSON data and validate it
		    $form_data = json_decode(stripslashes($_POST['formData']), true);		    
		    if (json_last_error() !== JSON_ERROR_NONE) {
		        wp_send_json_error(array('message' => 'Invalid JSON data'));wp_die();
		    }

		    // get current tab
		    $page_tabs = array(
				'eventon'=>'evcal_1',
				'eventon-lang'=>'evcal_2',
				'eventon-styles'=>'evcal_3',
				'eventon-extend'=>'evcal_4',
				'eventon-support'=>'evcal_5',
			);
		    $current_page = (!empty($_POST['page']))? sanitize_text_field($_POST['page']): 'eventon';
		    $current_tab = $page_tabs[ $current_page ];

		    $help = new evo_helper();
		    $new_settings = array();

		    // load existing settings values
		    	EVO()->cal->set_cur( $current_tab );
		    	$saved_settings = EVO()->cal->get_op( $current_tab );
				$saved_settings = !empty($saved_settings) && is_array($saved_settings)? $saved_settings : array();

		    // for language settings
			    if( $current_tab == 'evcal_2'):
					$_lang_version = (!empty($_POST['lang']))? sanitize_text_field($_POST['lang']): 'L1';
					$sanitized_form_data = array();

					// Process duplicates and sanitize each value
				    foreach ($form_data as $itemkey => $itemvalue ) {
				        // skip saving unnecessary text
				    	if( in_array($itemkey, array('action','option_page','_wp_http_referer','_wpnonce','evcal_noncename','evo_current_lang','evo_translated_text'))) continue;

				        if( !empty($itemvalue )) {
				            $key = sanitize_text_field( $itemkey );
				            $value = sanitize_textarea_field( $itemvalue );

				            if (strpos($key, '_v_') !== false) {
				                $key = str_replace('_v_', '', $key);
				            }

				            $sanitized_form_data[$key] = $value;
				        }
				    }

					$lang_opt = get_option('evcal_options_evcal_2');
					if(!empty($lang_opt) ){
						$new_settings[$_lang_version] = $sanitized_form_data;
						$new_settings = array_merge($lang_opt, $new_settings);
					}else{
						$new_settings[$_lang_version] = $sanitized_form_data;
					}

					// Update the option with sanitized data
    				update_option('evcal_options_evcal_2', $new_settings);

    		// all other settings
				else:
					

					// fields to skip sanitization @u 4.6
					$none_san_fields = apply_filters('evo_settings_non_san_fields', array('evo_etl','evcal_top_fields','evcal_sort_options'), $current_tab);
					
					// field keys with html
					$html_fields = apply_filters( 'evo_settings_html_fields', array());

					//$new_settings = array();
					$new_settings = $saved_settings;

					// process all form data
					foreach($form_data as $settings_field => $settings_value ){

						// strip [] from feild name
						if( strpos($settings_field, '[]') !== false ){
							$settings_field = str_replace('[]','', $settings_field);							
						}

						// skip fields
						if(in_array($settings_field, array( 
							'option_page', 'action','_wpnonce','_wp_http_referer','evcal_noncename','qm-theme','qm-editor-select'
						))){	continue;	}


						// HTML fields 4.6
						if( in_array( $settings_field, $html_fields )){
							$new_settings[ $settings_field ] = $help->sanitize_html( $settings_value );
							continue;
						}

						// none sanitize fields
						if( in_array($settings_field, $none_san_fields) ){
							$new_settings[ $settings_field ] = $settings_value;

						// If value contains 'http' or 'https', treat it as a URL
					    } elseif (is_string($settings_value) && (strpos($settings_value, 'http') !== false)) {
					        $new_settings[$settings_field] = esc_url($settings_value);

					    // Sanitize normal text fields
					    } else {
					        $new_settings[$settings_field] = !is_array($settings_value) ? sanitize_text_field($settings_value) : $settings_value;
					    }	
						
					}

					// check isolatedly saved setting values and include them
						foreach( array('evowhs') as $_iso_field){
							if( array_key_exists( $_iso_field, $saved_settings)){

								$new_settings[ $_iso_field ] = $saved_settings[ $_iso_field ];
							}
						}

					// for general settings evcal_1
						if( $current_tab == 'evcal_1'){
							// update custom meta fields count into settings
							$new_settings['cmd_count'] = evo_calculate_cmd_count();
						}


					// plug
						do_action('evo_before_settings_saved', $current_tab, '',  $new_settings);


					// save settings
						EVO()->cal->set_cur( $current_tab );

						$new_settings = apply_filters('evo_save_settings_optionvals', $new_settings, $current_tab, $form_data);

						EVO()->cal->set_option_values( $new_settings );


					// save custom styles and php code
						if( isset($new_settings['evcal_styles']) ){
							$styles = urldecode($new_settings['evcal_styles']); // Decode the encoded CSS
						    $sanitized_styles = eventon_sanitize_css($styles);
						    
						    if (!empty($sanitized_styles)) update_option('evcal_styles', $sanitized_styles);
						}

						if( isset($new_settings['evcal_php']) )	
							update_option('evcal_php', strip_tags(stripslashes($new_settings['evcal_php'])) );

					// update dynamic styles after settings are saved to options field
						if( $current_tab == 'evcal_1' || $current_tab == 'evcal_3'){

							// add dynamic styles to options
							EVO()->evo_admin->update_dynamic_styles();

							// update the dynamic styles .css file or write to headr
							EVO()->evo_admin->generate_dynamic_styles_file();
						}


					// update global settings values
					$GLOBALS['EVO_Settings'][ 'evcal_options_' .$current_tab] = $new_settings;

				endif;
			
			
			$return_content = array(
				//'debug'=> $form_data,
				'debug2'=> $new_settings,
				'content'=> '',
				'msg'=> __('Saved Successfully','eventon'),
				'status'=>'good'
			);			
			wp_send_json($return_content);	wp_die();
		}

	// Feature an event from admin */
		public function eventon_feature_event() {

			// Validate request
			EVO()->helper->validate_request('_wpnonce','eventon-feature-event', 'edit_eventons', true ,true,'message');		
		

			$post_id = isset( $_GET['eventID'] ) && (int) $_GET['eventID'] ? (int) $_GET['eventID'] : '';
			if (!$post_id) wp_die( __( 'Event id is missing!', 'eventon' ) );

			$post = get_post($post_id);

			if(!$post) wp_die( __( 'Event post doesnt exists!'),'eventon');
			if( $post->post_type !== 'ajde_events' ) wp_die( __('Post type is not an event', 'eventon' ) );

			$featured = get_post_meta( $post->ID, '_featured', true );

			wp_safe_redirect( remove_query_arg( array('trashed', 'untrashed', 'deleted', 'ids'), wp_get_referer() ) );
			
			if( $featured == 'yes' )
				update_post_meta($post->ID, '_featured', 'no');
			else
				update_post_meta($post->ID, '_featured', 'yes'); 

			wp_safe_redirect( remove_query_arg( array('trashed', 'untrashed', 'deleted', 'ids'), wp_get_referer() ) );
			exit;
		}
	// system log
		public function admin_system_log(){
			// Validate request
			EVO()->helper->validate_request('nn','eventon_admin_nonce', 'edit_eventons', true ,true);	
			
			
			$html = '';
			ob_start();

			echo EVO_Error()->_get_html_log_view();

			echo "<div class='evopadt20'>";

				EVO()->elements->print_trigger_element(array(
					'extra_classes'=>'',
					'title'=>__('Flush Log','eventon'),
					'dom_element'=> 'span',
					'uid'=>'evo_admin_flush_log',
					'lb_class' =>'evoadmin_system_log',
					'lb_load_new_content'=> true,	
					'ajax_data' =>array('action'=>'eventon_admin_system_log_flush'),
				), 'trig_ajax');

			echo "</div>";


			$html = ob_get_clean();

			wp_send_json(array(
				'status'=>'good',
				'content'=> $html
			));
			wp_die();
		}
		public function admin_system_log_flush(){
			// Validate request
			EVO()->helper->validate_request('nn','eventon_admin_nonce', 'edit_eventons', true ,true);	

			EVO_Error()->_flush_all_logs();

			$html = EVO_Error()->_get_html_log_view();
			
			wp_send_json(array(
				'status'=>'good',
				'msg'=> __('All system logs flushed'),
				'content'=> $html
			));
			wp_die();
		}

	// environment @u 4.5.5
		public function admin_get_environment(){

			// Validate request
			EVO()->helper->validate_request('nn','eventon_admin_nonce', 'edit_eventons', true ,true);	

			
			$data = array(); $html = ''; global $wpdb;

			// event count
			$event_posts_r = $wpdb->get_results( "SELECT ID FROM {$wpdb->posts} WHERE post_type='ajde_events'" );
			$events_count = ($event_posts_r && is_array($event_posts_r) )? count($event_posts_r):0;

			// event post meta count
			$pm_cunt_r = $wpdb->get_results( "SELECT pm.meta_id FROM {$wpdb->posts} p INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id WHERE p.post_type = 'ajde_events'" );
			$pm_count = ($pm_cunt_r && is_array($pm_cunt_r) )? count($pm_cunt_r):0;

			$data['EventON_version'] = EVO()->version;			

			$data['shead0'] = __('WordPress Environment');
			$data['WordPress_version'] = get_bloginfo( 'version' );
			$data['is_multisite'] = is_multisite()?'Yes':'No';
			$data['WordPress_memory_limit'] =  WP_MEMORY_LIMIT;
			$data['WordPress_Debug_mode'] = ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? 'Yes':'No';
			$data['WordPress_Cron'] = ! ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) ? 'Yes':'No';
			$data['Active_plugins_count'] = count(get_option('active_plugins'));
			$data['Permalink_structure'] = get_option('permalink_structure') ?: 'Default';
			$db_size = $wpdb->get_var("SELECT SUM(data_length + index_length) FROM information_schema.tables WHERE table_schema = '" . DB_NAME . "'");
			$data['Database_size'] = size_format($db_size);
						
			$data['shead1'] = __('Server Environment');
			$data['PHP_version'] = phpversion();
			$data['PHP_max_input_vars'] = ini_get( 'max_input_vars' ) . ' '. __('Characters');
			$data['Maximum_update_size'] = size_format( wp_max_upload_size() );
			$data['PHP_memory_limit'] = ini_get('memory_limit');
			$data['MySQL_version'] = $wpdb->db_version();
			$data['PHP_max_execution_time'] = ini_get('max_execution_time') . ' seconds';
			$data['Server_timezone'] = date_default_timezone_get();
			$data['SSL_enabled'] = is_ssl() ? 'Yes' : 'No';
			$data['CURL_enabled'] = in_array  ('curl', get_loaded_extensions() ) ? 'Yes':'No';

			

			$data['shead2'] = __('Post Data');
			$data['Events_count'] = $events_count;
			$data['Total_event_postmeta_DB_entries'] = $pm_count;

			// database information
			if ( defined( 'DB_NAME' ) ) {	}

			$html = '<div class="evo_environment">';

			foreach($data as $D=>$V){

				if( strpos($D, 'shead') !==  false ){ 
					$html .= "<p class='shead'>". $V ."</p>"; continue;
				}

				$D = str_replace('_', ' ', $D);
				$html .= "<p><span>".$D."</span><span class='data'>". $V ."</span></p>";
			}


			$html .= "</div>";
				
			wp_send_json(array(
				'status'=>'good',
				'content'=> $html,
			)); wp_die();
		}
	

}
new EVO_admin_ajax();