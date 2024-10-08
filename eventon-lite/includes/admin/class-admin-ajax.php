<?php
/**
 * Function ajax for backend
 * @version   L2.3
 */
class EVO_admin_ajax{
	public $helper;
	
	public function __construct(){
		$ajax_events = array(				
			'export_events'			=>'export_events',	
			'export_settings'		=>'export_settings',
			'get_import_settings'	=>'get_import_settings',
			'import_settings'		=>'import_settings',
			
			'rel_event_list'		=>'rel_event_list',
			'get_latlng'				=>'get_latlng',

			'config_virtual_event'	=>'config_virtual_event',
			'select_virtual_moderator'	=>'select_virtual_moderator',
			'get_virtual_users'	=>'get_virtual_users',
			'save_virtual_mod_settings'	=>'save_virtual_mod_settings',
			'save_virtual_event_settings'	=>'save_virtual_event_settings',
		);
		foreach ( $ajax_events as $ajax_event => $class ) {

			$prepend = 'eventon_';
			add_action( 'wp_ajax_'. $prepend . $ajax_event, array( $this, $class ) );
			add_action( 'wp_ajax_nopriv_'. $prepend . $ajax_event, array( $this, $class ) );
		}

		add_action('wp_ajax_eventon-feature-event', array($this, 'eventon_feature_event'));

		$this->helper = EVO()->helper;
	}

	// virtual events
		public function config_virtual_event(){

			// validate if user has permission
			if( !current_user_can('edit_eventons') ){
				wp_send_json(array(
					'status'=>'bad','msg'=> __('You do not have proper permission to access this','eventon')
				));
				wp_die();
			}

			$post_data = $this->helper->sanitize_array( $_POST);

			$EVENT = new EVO_Event( (int) $post_data['eid'] );

			ob_start();

			include_once('views/virtual_event_settings.php');

			wp_send_json(array(
				'status'=>'good','content'=> ob_get_clean()
			));
			wp_die();
		}
		public function select_virtual_moderator(){
			
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

			// validate if user has permission
			if( !current_user_can('edit_eventons') ){
				wp_send_json(array(
					'status'=>'bad','msg'=> __('You do not have proper permission to access this','eventon')
				));	wp_die();
			}

			$user_role = sanitize_text_field( $_POST['_user_role']);

			wp_send_json(array(
				'status'=>'good',
				'content'=> empty($user_role) ? 
					"<option value=''>--</option>" : 
					$this->get_virtual_users_select_options($user_role)
			)); wp_die();

			
		}
		public function save_virtual_event_settings(){
			// validate if user has permission
			if( !current_user_can('edit_eventons') ){
				wp_send_json(array(
					'status'=>'bad','msg'=> __('You do not have proper permission to access this','eventon')
				));	wp_die();
			}
			
			// nonce validation
			if( empty( $_POST['evo_noncename'] ) || !wp_verify_nonce( wp_unslash( $_POST['evo_noncename'] ), 'evo_save_virtual_event_settings' ) ){
				wp_send_json(array(
					'status'=>'bad','msg'=> __('Nonce validation failed','eventon')
				));	wp_die();
			}

			$post_data = $this->helper->sanitize_array( $_POST);

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
			// validate if user has permission
			if( !current_user_can('edit_eventons') ){
				wp_send_json(array(
					'status'=>'bad','msg'=> __('You do not have proper permission to access this','eventon')
				));	wp_die();
			}			

			// nonce validation
			if( empty($_POST['evo_noncename']) || !wp_verify_nonce( wp_unslash ( $_POST['evo_noncename'] ), 'evo_save_virtual_mod_settings' ) ){
				wp_send_json(array(
					'status'=>'bad','msg'=> __('Nonce validation failed','eventon')
				));	wp_die();
			}		

			$post_data = $this->helper->sanitize_array( $_POST);	

			$EVENT = new EVO_Event( (int)$post_data['eid']);

			$EVENT->save_meta('_evo_user_role', $post_data['_user_role']);
			$EVENT->save_meta('_mod', $post_data['_mod']);

			wp_send_json(array(
				'status'=>'good','msg'=> __('Moderator Data Saved Successfully','eventon')
			)); wp_die();
			
		}
		
	// Related Events @4.5.5
		function rel_event_list(){

			// Check User Caps.
			if ( ! current_user_can( 'edit_eventons' ) ) {
				wp_send_json_error( 'missing_capabilities' );
				wp_die();
			}

			$post_data = $this->helper->sanitize_array( $_POST);


			$event_id = (int)$post_data['eventid'];
			$EVs = json_decode( stripslashes($post_data['EVs']), true );

			$wp_args = array(
				'posts_per_page'=>-1,
				'post_type'=>'ajde_events',
				'exclude'=> $event_id,
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
		function export_settings(){
			// validate if user has permission
			if( !current_user_can('edit_eventons') ){
				wp_die( __('User not loggedin','eventon'));
			}

			// verify nonce
			if(empty( $_REQUEST['nonce'] ) || !wp_verify_nonce( wp_unslash( $_REQUEST['nonce'] ), 'evo_export_settings')) {
				wp_die( __('Security Check Failed','eventon'));
			} 

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
			$output = array('status'=>'bad','msg'=>'');

			// verify nonce
			if(empty( $_REQUEST['nn'] ) || !wp_verify_nonce( wp_unslash( $_REQUEST['nn'] ), 'eventon_admin_nonce')) {
				$output['msg'] = __('Security Check Failed!','eventon');
				wp_send_json($output); wp_die();
			}

			// check if admin and loggedin
			if(!is_admin() && !is_user_logged_in()){
				$output['msg'] = __('User not loggedin!','eventon');
				wp_send_json($output); wp_die();
			} 

			// validate if user has permission
			if( !current_user_can('edit_eventons') ){
				$output['msg'] = __('Required permission missing!','eventon');
				wp_send_json($output); wp_die();
			}

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
		function import_settings(){
			$output = array('status'=>'bad','msg'=>'');
			
			// verify nonce
				if(empty( $_POST['nonce'] ) || !wp_verify_nonce($_POST['nonce'], 'eventon_admin_nonce')){ 
					$output['msg'] = __('Security Check Failed!','eventon');
					wp_send_json($output); 
					wp_die();
				}

			// check if admin and loggedin
				if(!is_admin() && !is_user_logged_in()){
					$output['msg'] = __('User not loggedin!','eventon');
					wp_send_json($output); wp_die();
				} 

			// admin permission
				if( !current_user_can('edit_eventons')){
					$output['msg'] = __('Required permission missing','eventon');

					wp_send_json($output); wp_die();
				}

			$post_data = $this->helper->sanitize_array( $_POST);
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
	// @update 4.3
		function export_events(){

			// check if admin and loggedin
				if( !current_user_can('edit_eventons') ){
					wp_die( __('User not loggedin','eventon'));
				}

			// verify nonce
				if( empty( $_REQUEST['nonce'] ) || !wp_verify_nonce( wp_unslash( $_REQUEST['nonce'] ), 'eventon_download_events')) {
					wp_die('Security Check Failed!');
				}

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

	// Feature an event from admin */
		function eventon_feature_event() {

			if ( ! is_admin() ) wp_die( __( 'Only available in admin side.', 'eventon' ) );

			if ( ! current_user_can('edit_eventons') ) wp_die( __( 'You do not have sufficient permissions to access this page.', 'eventon' ) );

			if ( ! check_admin_referer('eventon-feature-event')) wp_die( __( 'You have taken too long. Please go back and retry.', 'eventon' ) );

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
	

}
new EVO_admin_ajax();