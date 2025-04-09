<?php 
/**
 * Event Top Content
 * @version 2.4
 */

class EVO_Cal_Event_Structure_Top{
	// HTML EventTop
	function get_event_top($array, $EventData, $eventtop_fields, $evOPT, $evOPT2){
			
		$EVENT = $this->EVENT;
		$SC = EVO()->calendar->shortcode_args;
		$OT = '';
		$_additions = apply_filters('evo_eventtop_adds' , array());

		$is_array_eventtop_fields = is_array($eventtop_fields)? true:false;

		extract($EventData);

		if(!is_array($array)) return $OT;



		EVO()->cal->set_cur('evcal_1');


		foreach($array as $element =>$elm){

			if(!is_array($elm)) continue;

			// convert to an object
			$object = new stdClass();
			foreach ($elm as $key => $value){
				$object->$key = $value;
			}

			$boxname = (in_array($element, $_additions))? $element: null;

			switch($element){
				case has_filter("eventon_eventtop_{$boxname}"):
					$helpers = array(
						'evOPT'=>$evOPT,
						'evoOPT2'=>$evOPT2,
					);

					$OT.= apply_filters("eventon_eventtop_{$boxname}", $object, $helpers, $EVENT);	
				break;
				case 'ft_img':
					$url = !empty($object->url_med)? $object->url_med: $object->url;
					//$url = !empty($object->url_full)? $object->url_full[0]: $url;
					$url = apply_filters('eventon_eventtop_image_url', $url);

					//$time_vals = ( $object->show_time) ? '<span class="evo_img_time"></span>':'';

					$OT .= "<span class='evoet_c1 evoet_cx '>";
					$OT.= "<span class='ev_ftImg' data-img='".(!empty($object->url_full)? $object->url_full: '')."' data-thumb='".$url."' style='background-image:url(\"".$url."\")' ></span>";
					$OT .= "</span>";

				break;
				case 'day_block':

					if(!empty($object->include) && !$object->include) break;
					if(!is_array( $object->start )) break;

					// if event hide_et_dn
					if( isset($SC['hide_et_dn']) && $SC['hide_et_dn'] == 'yes') break;
					
					$OT .= "<span class='evoet_c2 evoet_cx '>";
					$OT.="<span class='evoet_dayblock evcal_cblock ".( $year_long?'yrl ':null).( $month_long?'mnl ':null)."' data-bgcolor='".$color."' data-smon='".$object->start['F']."' data-syr='".$object->start['Y']."'>";
					
					// include dayname if allowed via settings
					$daynameS = $daynameE = '';
					if( is_array($eventtop_fields) && in_array('dayname', $eventtop_fields)){
						$daynameS = (!empty($object->html['start']['day'])? $object->html['start']['day']:'');
						$daynameE = (!empty($object->html['end']['day'])? $object->html['end']['day']:'');
					}

					$time_data = apply_filters('evo_eventtop_dates', array(
						'start'=>array(
							'year'=> 	($object->show_start_year=='yes'? $object->html['start']['year']:''),	
							'day'=>		$daynameS,
							'date'=> 	(!empty($object->html['start']['date'])?$object->html['start']['date']:''),
							'month'=>  	(!empty($object->html['start']['month'])?$object->html['start']['month']:''),
							'time'=>  	(!empty($object->html['start']['time'])?$object->html['start']['time']:''),
						),
						'end'=>array(
							'year'=> 	(($object->show_end_year=='yes' && !empty($object->html['end']['year']) )? $object->html['end']['year']:''),	
							'day'=>		$daynameE,
							'date'=> 	(!empty($object->html['end']['date'])?$object->html['end']['date']:''),
							'month'=> 	(!empty($object->html['end']['month'])? $object->html['end']['month']:''),
							'time'=> 	(!empty($object->html['end']['time'])? $object->html['end']['time']:''),
						),
					), $object->show_start_year, $object );

					$class_add = '';
					foreach($time_data as $type=>$data){					
						$end_content = '';
						foreach($data as $field=>$value){
							if(empty($value)) continue;
							$end_content .= "<em class='{$field}'>{$value}</em>";
						}

						if($type == 'end' && empty($data['year']) && empty($data['month']) && empty($data['date']) && !empty($data['time'])){
							$class_add = 'only_time';
						}
						if(empty($end_content)) continue;
						$OT .= "<span class='evo_{$type} {$class_add}'>";
						$OT .= $end_content;
						$OT .= "</span>";
					}
								
					$OT .= "</span>";
					$OT .= "</span>";

				break;

				// title section of the event top
				case 'titles':
					
					// location attributes
					$event_location_variables = '';
					if(!empty($location_name) && (!empty($location_address) || !empty($location_latlng))){
						$LL = !empty($location_latlng)? $location_latlng:false;

						if(!empty($location_address)) $event_location_variables .= ' data-location_address="'.$location_address.'" ';
						$event_location_variables .= ($LL)? 'data-location_type="lonlat"': 'data-location_type="address"';
						$event_location_variables .= ' data-location_name="'.$location_name.'"';
						if(isset($location_url))	$event_location_variables .= ' data-location_url="'.$location_url.'"';
						$event_location_variables .= ' data-location_status="true"';

						if( $LL){
							$event_location_variables .= ' data-latlng="'.$LL.'"';
						}

						$OT.= "<span style='display:none' class='event_location_attrs' {$event_location_variables}></span>";
					}

					$OT.= "<span class='evoet_c3 evoet_cx evcal_desc evo_info ". ( $year_long?'yrl ':null).( $month_long?'mnl ':null)."' >";
					


					// above title inserts
					if( isset($SC['hide_et_tags']) && $SC['hide_et_tags'] == 'yes'): else:

					$OT.= "<span class='evoet_tags evo_above_title'>";
						
						// live now virtual event 2.2.14
						if($EVENT && !$EVENT->is_cancelled() && $EVENT->is_event_live_now() && !EVO()->cal->check_yn('evo_hide_live') ){
							$OT.= "<span class='evo_live_now' title='".( evo_lang('Live Now')  )."'>". EVO()->elements->get_icon('live') ."</span>";
						}


						// get set to hide event top tags
						$eventtop_hidden_tags = isset($evOPT['evo_etop_tags']) ? $evOPT['evo_etop_tags'] : array();

						$OT .= apply_filters("eventon_eventtop_abovetitle", '', $object, $EVENT, $eventtop_hidden_tags);

						$eventtop_tags = apply_filters('eventon_eventtop_abovetitle_tags', array());

						// status
						if( $_status && $_status != 'scheduled'){
							$eventtop_tags['status'] = array(
								$EVENT->get_event_status_lang(),
								$_status
							);
						}

						// featured
						if(!empty($featured) && $featured){
							$eventtop_tags['featured'] = array(evo_lang('Featured')	);
						}

						// virtual
						if( $EVENT && $EVENT->is_virtual() ){
							if( $EVENT->is_mixed_attendance()){
								$eventtop_tags['virtual_physical'] = 
									array(evo_lang('Virtual/ Physical Event'), 'vir'	);
							}else{
								$eventtop_tags['virtual'] = array(evo_lang('Virtual Event'), 'vir'	);
							}							
						}

						// repeating event tag
						if( $EVENT && $EVENT->is_repeating_event()){
							$eventtop_tags['repeating'] = array(evo_lang('Repeating Event')	);
						}
							
						foreach($eventtop_tags as $ff=>$vv){

							if(in_array($ff, $eventtop_hidden_tags)) continue;

							$v1 = isset($vv[1]) ? ' '.$vv[1]:'';
							$OT.= "<span class='evo_event_headers {$ff}{$v1}'>". $vv[0] . "</span>";
						}



					$OT.="</span>";

					endif;
							
					
					$OT.= "<span class='evoet_title evcal_desc2 evcal_event_title' itemprop='name'>". apply_filters('eventon_eventtop_maintitle',$EVENT->get_title() ) ."</span>";
					
					// below title inserts
					$OT.= "<span class='evoet_subtitle evo_below_title'>";
						if($ST = $EVENT->get_subtitle()){
							$OT.= "<span class='evcal_event_subtitle' >" . apply_filters('eventon_eventtop_subtitle' , $ST) ."</span>";
						}

						// event status reason 
						if( $reason = $EVENT->get_status_reason()){
							$OT.= '<span class="status_reason">'. $reason .'</span>';
						}

					$OT.="</span>";
				break;

				case 'belowtitle':

					if(!$object->include) break;


					if( isset($SC['hide_et_tl']) && $SC['hide_et_tl'] == 'yes'):
					else:

						$OT.= "<span class='evoet_time_expand level_3 evcal_desc_info evogap10'>";

						// time
						if($is_array_eventtop_fields && in_array('time', $eventtop_fields) && isset($object->html)){
							
							// time
							$timezone_text = (!empty($object->timezone)? ' <em class="evo_etop_timezone evomarl5">'.$object->timezone. '</em>':null);

							//print_r($object);
							$tzo = $tzo_box = '';

							// custom timezone text
							if( !EVO()->cal->check_yn('evo_gmt_hide','evcal_1') && !empty($EVENT->gmt) ){
								$timezone_text .= "<span class='evo_tz marl5'>(". $EVENT->gmt .")</span>";
							}

							// event time
							$OT.= "<em class='evcal_time evo_tz_time level_4'><i class='fa fa-clock-o evomarr10'></i>". apply_filters('evoeventtop_belowtitle_datetime', $object->html['html_fromto'], $object->html, $object) . $timezone_text ."</em> ";

							// view in my time - local time
							if( !empty($this->ev_tz) && EVO()->cal->check_yn('evo_show_localtime','evcal_1') ){
								
								$OT.= $this->get_view_my_time_content( $this->timezone_data , $EVENT->start_unix, $EVENT->end_unix);		
							}

							// manual timezone text
							if( empty($object->_evo_tz)) $OT.= "<em class='evcal_local_time' data-s='{$event_start_unix}' data-e='{$event_end_unix}' data-tz='". $EVENT->get_prop('_evo_tz') ."'></em>";
						}
						
						
						// location information
						if($is_array_eventtop_fields){
							// location name
							$LOCname = (in_array('locationame',$eventtop_fields) && !empty($location_name) )? $location_name: false;

							// location address
							$LOCadd = (in_array('location',$eventtop_fields) && !empty($location_address))? stripslashes($location_address): false;

							if($LOCname || $LOCadd){
								$OT.= "<span class='evoet_location level_4'>";
								$OT.= '<em class="evcal_location evolh13" '.( !empty($location_latlng)? ' data-latlng="'.$location_latlng.'"':null ).' data-add_str="'.$LOCadd.'"><i class="fa fa-location-pin evomarr10"></i>'.($LOCname? '<em class="event_location_name">'.$LOCname.'</em>':'').
									( ($LOCname && $LOCadd)?', ':'').
									$LOCadd.'</em>';
								$OT.= "</span>";
							}
						}

						$OT.="</span>";
					endif;


					if( isset($SC['hide_et_extra']) && $SC['hide_et_extra'] == 'yes'):else:
					$OT.="<span class='evcal_desc3'>";

					//organizer
						if($object->fields_ && in_array('organizer',$object->fields) && !empty($event_organizer) 
						){

							$OT.="<span class='evcal_oganizer level_4'>
								<em><i>".( eventon_get_custom_language( $evOPT2,'evcal_evcard_org', 'Event Organized By')  ).':</i></em>
								<em class="evoet_dataval">'.$event_organizer->name."</em>
								</span>";
						}
					//event type
					if($object->tax)	$OT.= $object->tax;

					// event tags
						if($is_array_eventtop_fields && in_array('tags',$eventtop_fields) && !empty($object->tags) ){
							$OT.="<span class='evo_event_tags level_4'>
								<em><i>".eventon_get_custom_language( $evOPT2,'evo_lang_eventtags', 'Event Tags')."</i></em>";

							$count = count($object->tags);
							$i = 1;
							foreach($object->tags as $tag){
								$OT.="<em class='evoet_dataval' data-tagid='{$tag->term_id}'>{$tag->name}".( ($count==$i)?'':',')."</em>";
								$i++;
							}
							$OT.="</span>";
						}


					// event progress bar
						if( !EVO()->cal->check_yn('evo_eventtop_progress_hide','evcal_1')  && $EVENT->is_event_live_now() && !$EVENT->is_cancelled()
							&& !$EVENT->echeck_yn('hide_progress')
							&& $EVENT->get_event_status() != 'postponed'
						){
							

							$livenow_bar_sc = isset($SC['livenow_bar']) ? $SC['livenow_bar'] : 'yes';
							
							// check if shortcode livenow_bar is set to hide live bar
							if($livenow_bar_sc == 'yes'):

							$OT.= "<span class='evo_event_progress evo_event_progress' >";

							//$OT.= "<span class='evo_ep_pre'>". evo_lang('Live Now') ."</span>";

							$now =  EVO()->calendar->utc_time;
							$duration = $EVENT->duration;
							$end_utc = $EVENT->get_end_time( true);
							$gap = $end_utc - $now; // how far event has progressed

							$perc = $duration == 0? 0: ($duration - $gap) / $duration;
							$perc = (int)( $perc*100);
							if( $perc > 100) $perc = 100;

							// action on expire							
							$exp_act = $nonce = '';
							if( isset($SC['cal_now']) && $SC['cal_now'] == 'yes'){
								$exp_act = 'runajax_refresh_now_cal';
								$nonce = wp_create_nonce('evo_calendar_now');
							}

							
							$OT.= "<span class='evo_epbar_o'><span class='evo_ep_bar'><b style='width:{$perc}%'></b></span></span>";
							$OT.= "<span class='evo_ep_time evo_countdowner' data-endutc='{$end_utc}' data-gap='{$gap}' data-dur='{$duration}' data-exp_act='". $exp_act ."' data-n='{$nonce}' data-ds='".evo_lang('Days')."' data-d='".evo_lang('Day')."' data-t='". evo_lang('Time Left')."'></span>";

							$OT.= "</span>";

							endif;

						}

					endif;

					// custom fields on eventtop @2.3					
					if( !empty( $object->cmf_data ) && count( $object->cmf_data )> 0 ){
						foreach( $object->cmf_data  as $cmf_x => $cmf_data ){
							$cmfO = (object) $cmf_data;
							if( empty( $cmfO->login_needed_message )){

								
								if($is_array_eventtop_fields && in_array('cmd'.$cmf_x ,$eventtop_fields) && !empty($object->tags) ):

									$OT.="<span class='evo_event_cmf level_4'>";

									// button type
									if( $cmfO->type == 'button' && !empty( $cmfO->valueL ) ){

										$_target = (!empty($cmfO->_target) && $cmfO->_target=='yes')? 'target="_blank"':null;

										$OT .="<span href='". esc_url( $cmfO->valueL ) ."' {$_target} class='evcal_btn evo_cusmeta_btn'>". esc_html( $cmfO->value ) ."</span>";
									// All other types
									}else{
										$OT.= "<em><i class='fa ". $cmfO->imgurl ."'></i> <i>". $cmfO->field_name ."</i></em>";
										$OT.="<em class='evoet_dataval' >" . $cmfO->value ."</em>";
									}

									
									$OT.="</span>";
								endif;
							}
						}
					}

					
				break;

				case 'close1':
					$OT.="</span>";// span.evcal_desc3
				break;

				case 'close2':
					$OT.= "</span>";// span.evcal_desc 

					$data = array(
						'bgc'=> $color,
						'bggrad'=> ( !empty($bggrad)? $bggrad:''),
					);

					$OT.="<em class='evoet_data' style='display:none' ". $this->help->array_to_html_data( $data ) ."></em>";
					//$OT.="<em class='clear'></em>";
				break;
			}
		}	

		return $OT;
	}

}