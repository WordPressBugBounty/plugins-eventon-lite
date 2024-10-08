<?php
/**
* Calendar single event's html structure 
* @version L 2.2.21
*/

class EVO_Cal_Event_Structure{
	private $EVENT;
	private $timezone = '';
	private $ev_tz = '';
	private $timezone_data = array();
	public $helper, $help;

	private $OO = array();
	private $OO2 = array();
	
	public function __construct($EVENT=''){

		$this->timezone_data = array(
			'__f'=>'YYYY-MM-DD h:mm:a',
			'__df'=> 'YYYY-MM-DD',
			'__tf'=> 'h:mm:a',
			'__t'=> evo_lang('View in my time')
		);

		if(!empty($EVENT)) $this->EVENT = $EVENT;

		$this->timezone = get_option('gmt_offset', 0);
		$this->ev_tz = $EVENT->get_timezone_key();

		$this->helper = $this->help = EVO()->helper;
	}


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

					$time_vals = ( $object->show_time) ? '<span class="evo_img_time"></span>':'';

					$OT .= "<span class='evoet_c1 evoet_cx '>";
					$OT.= "<span class='ev_ftImg' data-img='".(!empty($object->url_full)? $object->url_full: '')."' data-thumb='".$url."' style='background-image:url(\"".$url."\")' >{$time_vals}</span>";
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

						$OT.= "<span class='event_location_attrs' {$event_location_variables}></span>";
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


					if( isset($SC['hide_et_tl']) && $SC['hide_et_tl'] == 'yes'):else:

						$OT.= "<span class='evoet_time_expand level_3 evcal_desc_info' >";

						// time
						if($is_array_eventtop_fields && in_array('time', $eventtop_fields) && isset($object->html)){
							
							// time
							$timezone_text = (!empty($object->timezone)? ' <em class="evo_etop_timezone">'.$object->timezone. '</em>':null);

							//print_r($object);
							$tzo = $tzo_box = '';

							// custom timezone text
							if( !EVO()->cal->check_yn('evo_gmt_hide','evcal_1') && !empty($this->ev_tz) ){

								$GMT_text = $this->help->get_timezone_gmt( $this->ev_tz , $EVENT->start_unix);
								$timezone_text .= "<span class='evo_tz'>(". $GMT_text .")</span>";
							}

							// event time
							$OT.= "<em class='evcal_time evo_tz_time level_4'>". apply_filters('evoeventtop_belowtitle_datetime', $object->html['html_fromto'], $object->html, $object) . $timezone_text ."</em> ";

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
								$OT.= '<em class="evcal_location" '.( !empty($location_latlng)? ' data-latlng="'.$location_latlng.'"':null ).' data-add_str="'.$LOCadd.'">'.($LOCname? '<em class="event_location_name">'.$LOCname.'</em>':'').
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
					if($object->tax)
						$OT.= $object->tax;

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



	// EvnetCard HTML	
	public function print_event_card($array, $EventData, $evOPT, $evoOPT2, $ep_fields = ''){
		// INIT
			$EVENT = $this->EVENT;
			$ED = $EventData;
			$evoOPT2 = (!empty($evoOPT2))? $evoOPT2: '';
			$this->OO = $evOPT;
			$this->OO2 = $evoOPT2;
			
			$OT ='';
			$count = 1;
			$items = count($array);	

			extract($EventData);

			$ep_fields = !empty($ep_fields)? explode(',', $ep_fields): false;
			
			// close button
			$close = "<div class='evcal_evdata_row evcal_close' title='".eventon_get_custom_language($evoOPT2, 'evcal_lang_close','Close')."'></div>";

			// additional fields array 
			$array = apply_filters('evo_eventcard_adds' , $array);

		

		$RR = '';


		// get event card designer fields
		$eventcard_fields = EVO()->calendar->helper->get_eventcard_fields_array();

		$already_printed_boxes = array();

		if( !is_array($eventcard_fields)) return;

		$processed_fields = array();		

		$rows = count($eventcard_fields);
		$i = 1;
		foreach( $eventcard_fields as $R=>$boxes){
			
			$CC = '';
			$box_count = 0;
			
			$opened = false;

			foreach( $boxes as $B=>$box){

				if( !isset($box['n'])) continue;
				$NN = $box['n'];
				if( isset($box['h']) && $box['h'] =='y' ) continue;

				// record printed boxes to avoid duplicate
				if( in_array($box['n'], $already_printed_boxes)) continue;
				if(!in_array($box['n'], $already_printed_boxes)) $already_printed_boxes[] = $box['n'];

				// get box data
					if( !array_key_exists($NN, $array)) continue;
					
					$BD = $array[ $NN ];
					// convert to an object
					$BDO = new stdClass();
					foreach ($BD as $key => $value){
						$BDO->$key = $value;
					}

				// if only specific fields set
					if( $ep_fields && !in_array($NN, $ep_fields) ) continue;

				// if already processed
					if( in_array( $NN, $processed_fields)) continue;
					$processed_fields[] = $NN;

				// box content
				$BCC = $this->get_eventcard_box_content( $NN, $BDO, $EVENT , $EventData);

				if( empty($BCC)) continue;

				$color = isset($box['c']) ? $box['c']:'';

				if( $B == 'L1' || $B == 'R1'){
					$CC .= "<div class='evocard_box_h'>";
					$opened = true;
				}

				$CC .= "<div class='evocard_box ". esc_attr( $NN )."' data-c='". esc_attr( $color ) ."' 
					style='". (!empty($color) ? "background-color:#". esc_attr( $color )."":'') ."'>". $BCC . "</div>";

				if( $B == 'L2' || $B == 'R2' && $opened){
					$CC .= "</div>"; $opened = false;
				}
				$box_count++;
			}

			if( $opened ) $CC .= "</div>";
			if( empty($CC)) continue;

			$row_class = array('evocard_row');
			if($box_count>1) $row_class[] ='bx'.$box_count;
			if($box_count>1) $row_class[] ='bx';
			if( array_key_exists('L1', $boxes)) $row_class[] = 'L';
			if($i == $rows)  $row_class[] = 'lastrow';

			$RR .= "<div class='". esc_attr( implode(' ', $row_class) ) ."'>";
			$RR .= $CC;
			$RR .= "</div>";
			$i++;
		}

		$RR .= "<div class='evo_card_row_end evcal_close' title='".eventon_get_custom_language($evoOPT2, 'evcal_lang_close','Close')."'></div>";

		echo  $RR;
	}	

	// return box HTML content using box field name
	function get_eventcard_box_content($box_name, $box_data, $EVENT, $EventData){

		$OT = '';
		$evOPT = $this->OO;
		$evoOPT2 = $this->OO2;
		$object = $box_data;
		$end_row_class = $end = '';
		$ED = $EventData;

		extract($EventData);


		// each eventcard type
			switch($box_name){

				// addition
					case has_filter("eventon_eventCard_{$box_name}"):
					
						$helpers = array(
							'evOPT'=> $evOPT,
							'evoOPT2'=>$evoOPT2,
							'end_row_class'=> '','end'=>'',
						);

						$OT.= apply_filters("eventon_eventCard_{$box_name}", $object, $helpers, $EVENT);							
					break;
					
				// Event Details
					case 'eventdetails':	
						
						$more_code=''; $evo_more_active_class = '';

						// check if character length of description is longer than X size
						if( !empty($evOPT['evo_morelass']) && $evOPT['evo_morelass']!='yes' && (strlen($object->fulltext) )>600 ){
							$more_code = 
								"<p class='eventon_shad_p' style='padding:5px 0 0; margin:0'>
									<span class='evcal_btn evo_btn_secondary evobtn_details_show_more' content='less'>
										<span class='ev_more_text' data-txt='".evo_lang_get('evcal_lang_less','less')."'>".evo_lang_get('evcal_lang_more','more')."</span><span class='ev_more_arrow ard'></span>
									</span>
								</p>";
							$evo_more_active_class = 'shorter_desc';
						}

						$iconHTML = "<span class='evcal_evdata_icons'><i class='fa ". esc_attr( get_eventON_icon('evcal__fai_001', 'fa-align-justify',$evOPT ) ) ."'></i></span>";

						$_full_event_details = stripslashes( $object->fulltext );

						
						$OT.="<div class='evo_metarow_details evorow evcal_evdata_row evcal_event_details". esc_attr( $end_row_class ) ."'>
								".$object->excerpt.$iconHTML."
								
								<div class='evcal_evdata_cell ". esc_attr( $evo_more_active_class )."'>
									<div class='eventon_full_description'>
										<h3 class='padb5 evo_h3'>".$iconHTML . evo_lang_get('evcal_evcard_details','Event Details')."</h3>
										<div class='eventon_desc_in' itemprop='description'>
										". 

										apply_filters('evo_eventcard_details',EVO()->frontend->filter_evo_content( $_full_event_details )) 

										."</div>";
										
										// pluggable inside event details
										do_action('eventon_eventcard_event_details');

										$OT .= $more_code;

										$OT.="<div class='clear'></div>
									</div>
								</div>
							</div>";
									
					break;

				// TIME
					case 'time':
						$iconTime = "<span class='evcal_evdata_icons'><i class='fa ". esc_attr( get_eventON_icon('evcal__fai_002', 'fa-clock-o',$evOPT ) ) ."'></i></span>";
						
						
						// time for event card
						$timezone = (!empty($object->timezone)? ' <em class="evo_eventcard_tiemzone">'. $object->timezone.'</em>':null);
						
						// event time
						$evc_time_text = "<span class='evo_eventcard_time_t'>". apply_filters('evo_eventcard_time', $object->timetext. $timezone, $object) . "</span>";

						// custom timezone text
						if( !EVO()->cal->check_yn('evo_gmt_hide','evcal_1') && !empty($this->ev_tz) ){

							$GMT_text = $this->help->get_timezone_gmt( $this->ev_tz, $EVENT->start_unix);
							$evc_time_text .= "<span class='evo_tz'>(". esc_html( $GMT_text ) .")</span>";
						}							

						// view in my time - local time
						if( !empty($this->ev_tz) && EVO()->cal->check_yn('evo_show_localtime','evcal_1') ){
							
							$evc_time_text.= $this->get_view_my_time_content( $this->timezone_data , $EVENT->start_unix, $EVENT->end_unix);		
						}

						$OT.="<div class='evo_metarow_time evorow evcal_evdata_row evcal_evrow_sm ". esc_attr( $end_row_class ) ."'>
								{$iconTime}
								<div class='evcal_evdata_cell'>							
									<h3 class='evo_h3'>".$iconTime . evo_lang_get('evcal_lang_time','Time')."</h3><p>".$evc_time_text."</p>
								</div>
							</div>";
					break;

				// location
					case 'location':
						$iconLoc = "<span class='evcal_evdata_icons'><i class='fa ". esc_attr( get_eventON_icon('evcal__fai_003', 'fa-map-marker',$evOPT ) ) ."'></i></span>";
						
						if(!empty($EventData['location_name']) || !empty($EventData['location_address'])){
							
							$locationLink = (!empty($location_link))? '<a target="'. ($location_link_target=='yes'? '_blank':'') .'" href="'. esc_url( evo_format_link($location_link) ).'">':false;
							
							$OT.= 
							"<div class='evcal_evdata_row evo_metarow_time_location evorow '>
								
									{$iconLoc}
									<div class='evcal_evdata_cell' data-loc_tax_id='". esc_attr( $EventData['location_term_id'] )."'>";

									if( $location_hide){
										$OT.= "<h3 class='evo_h3'>".$iconLoc. evo_lang_get('evcal_lang_location','Location'). "</h3>";
										$OT .= "<p class='evo_location_name'>". EVO()->calendar->helper->get_field_login_message() . "</p>";
									}else{
										
										$OT.= "<h3 class='evo_h3'>".$iconLoc.($locationLink? $locationLink:''). evo_lang_get('evcal_lang_location','Location').($locationLink?'</a>':'')."</h3>";

										if( !empty($location_name) && !$EVENT->check_yn('evcal_hide_locname') )
											$OT.= "<p class='evo_location_name'>". $locationLink. $location_name . ($locationLink? '</a>':'') ."</p>";

										// for virtual location
										if( $location_type == 'virtual'){
											if( $locationLink) 
												$OT.= "<p class='evo_virtual_location_url'>" . evo_lang('URL:'). $locationLink . ' '. $location_link."</a></p>";
										}else{

											if(!empty($location_address)){
												$OT .= "<p class='evo_location_address'>". $locationLink . stripslashes($location_address) . ($locationLink? '</a>':'') ."</p>";
											}
											
										}											
									}
									$OT.= "</div>
								
							</div>";
						}
					break;
			
				// REPEAT SERIES
					case 'repeats':
						$OT.="<div class='evo_metarow_repeats evorow evcal_evdata_row evcal_evrow_sm ". esc_attr( $end_row_class ) ."'>
								<span class='evcal_evdata_icons'><i class='fa ". esc_attr( get_eventON_icon('evcal__fai_repeats', 'fa-repeat',$evOPT ) ) ."'></i></span>
								<div class='evcal_evdata_cell'>							
									<h3 class='evo_h3'>". esc_html( eventon_get_custom_language($evoOPT2, 'evcal_lang_repeats','Future Event Times in this Repeating Event Series') ) ."</h3>
									<p class='evo_repeat_series_dates ".($object->clickable?'clickable':'')."'' data-click='". esc_attr( $object->clickable ) ."' data-event_url='". esc_url( $object->event_permalink )."'>";

							$datetime = new evo_datetime();

							// allow for custom date time format passing to repeat event times
							$repeat_start_time_format = apply_filters('evo_eventcard_repeatseries_start_dtformat','');
							$repeat_end_time_format = apply_filters('evo_eventcard_repeatseries_end_dtformat','');
							
							foreach($object->future_intervals as $key=>$interval){
								$OT .= "<span data-repeat='". esc_attr( $key )."' data-l='". esc_url( $EVENT->get_permalink($key,$EVENT->l) ) ."' class='evo_repeat_series_date'>"; 
								

								if($EVENT->is_year_long()){
									$OT .= gmdate('Y', $interval[0]);
								}elseif( $EVENT->is_month_long()){

									$OT .= $EVENT->get_readable_formatted_date( $interval[0], 'F, Y');
									
								}else{

									$OT .= $EVENT->get_readable_formatted_date( $interval[0], $repeat_start_time_format);

									if( $object->showendtime && !empty($interval[1])){

										$OT .= ' - '.$EVENT->get_readable_formatted_date( $interval[1], $repeat_end_time_format);
									}

								}
								
								
								$OT.= "</span>";
							}

						$OT.="</p></div></div>";
					break;

				// Location Image
					case 'locImg':

						if(empty($location_img_id)) break;
						$img_src = wp_get_attachment_image_src($location_img_id,'full');
						if(empty($img_src)) break;

						$fullheight = (int)EVO()->calendar->get_opt1_prop('evo_locimgheight',400);

						if(!empty($img_src)){
							
							// text over location image
							$inside = $inner = '';
							if(!empty($location_name) && $EVENT->check_yn('evcal_name_over_img') ){

								if(!empty($location_address))	
									$inner .= '<span style="padding-bottom:10px">'. stripslashes($location_address) .'</span>';
								if(!empty($location_description)) 
									$inner .= '<span class="location_description">'. wp_kses_post( $location_description ) .'</span>';
								
								$inside = "<p class='evoLOCtxt'><span class='evo_loc_text_title'>{$location_name}</span>{$inner}</p>";
							}
							$OT.="<div class='evcal_evdata_row evo_metarow_locImg evorow ".( !empty($inside)?'tvi':null)."' style='height:{$fullheight}px; background-image:url(". esc_url( $img_src[0] ).")' id='". esc_attr( $location_img_id ) ."_locimg' >{$inside}</div>";
						}
					break;

				// GOOGLE map
					case 'gmap':	

						// since 4.5
						$is_google_map_good = true;
						if( !EVO()->cal->get_prop('evo_gmap_api_key','evcal_1')) $is_google_map_good = false;

						if( !$is_google_map_good ) break;

						if( $EventData['location_type'] != 'virtual' || !isset($EventData['location_type'])){
							$OT.="<div class='evcal_evdata_row evo_metarow_gmap evorow evcal_gmaps ". esc_attr( $object->id ) ."_gmap' id='". esc_attr( $object->id ) ."_gmap' style='max-width:none'></div>";
						}
					break;
				
				// Featured image
					case 'ftimage':
						
						$__hoverclass = (!empty($object->hovereffect) && $object->hovereffect!='yes')? ' evo_imghover':null;
						$__noclickclass = (!empty($object->clickeffect) && $object->clickeffect=='yes')? ' evo_noclick':null;
						$__zoom_cursor = (!empty($evOPT['evo_ftim_mag']) && $evOPT['evo_ftim_mag']=='yes')? ' evo_imgCursor':null;

						ob_start();

						
						// if set to direct image
						if(!empty($evOPT['evo_ftimg_height_sty']) && $evOPT['evo_ftimg_height_sty']=='direct'){
							// ALT Text for the image
								$alt = !empty($object->img_id)? get_post_meta($object->img_id,'_wp_attachment_image_alt', true):false;
								$alt = !empty($alt)? 'alt="'. esc_html( $alt ) .'"': '';
							echo "<div class='evo_metarow_directimg evcal_evdata_row'><img class='evo_event_main_img' src='". esc_url( $object->img )."' ".  $alt ."/></div>";
						}else{

							// make sure image array object passed
							if( $object->main_image && is_array($object->main_image)){

								$main_image = $object->main_image;

								$height = !empty($object->img[2])? $object->img[2]:'';
								$width = !empty($object->img[1])? $object->img[1]:'';

								echo "<div class='evo_metarow_fimg evorow evcal_evdata_img evcal_evdata_row ". esc_attr( $end_row_class.$__hoverclass.$__zoom_cursor.$__noclickclass )."' data-imgheight='". esc_attr( $main_image['full_h'] ) ."' data-imgwidth='". esc_attr( $main_image['full_w'] ) ."'  style='background-image: url(\"". esc_url( $object->img )."\")' data-imgstyle='". esc_attr( $object->ftimg_sty )."' data-minheight='". esc_attr( $object->min_height )."' data-status=''></div>";
							}
						}
						

						$OT .= ob_get_clean();
						
					break;
				
				// event organizer
					case 'organizer':					
						
						if(empty($ED['event_organizer'])) break;
						ob_start();
						include('views/html-eventcard-organizer.php');
						return ob_get_clean();
						
					break;
				
				// get directions
					case 'getdirection':
						
						$_lang_1 = evo_lang_get('evcalL_getdir_placeholder','Type your address to get directions');
						$_lang_2 = evo_lang_get('evcalL_getdir_title','Click here to get directions');

						$_from_address = false;
						if(!empty($location_address)) $_from_address = $location_address;
						if(!empty($location_getdir_latlng) && $location_getdir_latlng =='yes' && !empty($location_latlng)){
							$_from_address = $location_latlng;
						}

						if(!$_from_address) break;
						
						$OT.="<div class='evo_metarow_getDr evorow evcal_evdata_row evcal_evrow_sm getdirections'>
							<form action='https://maps.google.com/maps' method='get' target='_blank'>
							<input type='hidden' name='daddr' value=\"". esc_attr( $_from_address )."\"/> 
							<p><input class='evoInput' type='text' name='saddr' placeholder='". esc_html( $_lang_1 ) ."' value=''/>
							<button type='submit' class='evcal_evdata_icons evcalicon_9' title='". esc_html( $_lang_2 ) ."'><i class='fa ".esc_attr( get_eventON_icon('evcal__fai_008a', 'fa-road',$evOPT ) ) ."'></i></button>
							</p></form>
						</div>";
						
					break;

				// learn more link
					case "learnmore":
						// learn more link with pluggability
						$learnmore_link = !empty($EVENT->get_prop('evcal_lmlink'))? apply_filters('evo_learnmore_link', $EVENT->get_prop('evcal_lmlink'), $object): false;
						$learnmore_target = ($EVENT->get_prop('evcal_lmlink_target')  && $EVENT->get_prop('evcal_lmlink_target')=='yes')? 'target="_blank"':null;

						if(!$learnmore_link) break;
						
						$OT.= "<div class='evo_metarow_learnM evo_metarow_learnmore evorow'>
							<a class='evcal_evdata_row evo_clik_row ' href='". esc_url( $learnmore_link ) ."' ".$learnmore_target.">
								<span class='evcal_evdata_icons'><i class='fa ". esc_attr( get_eventON_icon('evcal__fai_006', 'fa-link',$evOPT ) ) ."'></i></span>
								<h3 class='evo_h3'>". esc_html( eventon_get_custom_language($evoOPT2, 'evcal_evcard_learnmore2','Learn More') ) ."</h3>
							</a>
							</div>";
					break;

					case "addtocal":

						$__ics_url = add_query_arg(array(
						    'action' => 'eventon_ics_download',
						    'event_id'	=> $EVENT->ID,
						    'ri'	=> $EVENT->ri,
						    'nonce'=> wp_create_nonce('eventon_ics_oneevent')
						), admin_url('admin-ajax.php'));


							$O = (object)array(
								'location_name'=> !empty($location_name)?$location_name:'',
								'location_address'=> !empty($location_address)?$location_address:'',
								'etitle'=> $event_title,
								'excerpt'=> $event_excerpt_txt
							);

							
							$__googlecal_link = $EVENT->get_addto_googlecal_link(
								$O->location_name,
								$O->location_address
							);


						// which options to show for add to calendar
							$addCaloptions = !empty($evOPT['evo_addtocal'])? $evOPT['evo_addtocal']: 'all';
							$addCalContent = '';

						// add to cal section
							switch($addCaloptions){
								case 'ics':
									$addCalContent = "<a href='{$__ics_url}' class='evo_ics_nCal' title='".eventon_get_custom_language($evoOPT2, 'evcal_evcard_addics','Add to your calendar')."'>".eventon_get_custom_language($evoOPT2, 'evcal_evcard_calncal','Calendar')."</a>";
								break;
								case 'gcal':
									$addCalContent = "<a href='". $__googlecal_link. "' target='_blank' class='evo_ics_gCal' title='".eventon_get_custom_language($evoOPT2, 'evcal_evcard_addgcal','Add to google calendar')."'>".eventon_get_custom_language($evoOPT2, 'evcal_evcard_calgcal','GoogleCal')."</a>";
								break;
								case 'all':
									$addCalContent = "<a href='{$__ics_url}' class='evo_ics_nCal' title='".eventon_get_custom_language($evoOPT2, 'evcal_evcard_addics','Add to your calendar')."'>".eventon_get_custom_language($evoOPT2, 'evcal_evcard_calncal','Calendar')."</a>".
										"<a href='{$__googlecal_link}' target='_blank' class='evo_ics_gCal' title='".eventon_get_custom_language($evoOPT2, 'evcal_evcard_addgcal','Add to google calendar')."'>".eventon_get_custom_language($evoOPT2, 'evcal_evcard_calgcal','GoogleCal')."</a>";
								break;
							}

						if( $addCaloptions != 'none'){
							$OT .= "<div class='evo_metarow_ICS evorow evcal_evdata_row'>
									<span class='evcal_evdata_icons'><i class='fa ".get_eventON_icon('evcal__fai_008', 'fa-calendar',$evOPT )."'></i></span>
									<div class='evcal_evdata_cell'>
										<p>{$addCalContent}</p>	
									</div>
								</div>";
						}
					break;
									
				// Related Events u 2.2.13
					case 'relatedEvents':
						$events = $EVENT->get_prop('ev_releated');
						if( !$events ) break;

						$events = json_decode($events, true);

						if( !is_array( $events )) break;


						ob_start();
						include('views/html-eventcard-related.php');
						return ob_get_clean();
					break;
				
				// Virtual Event
					case 'virtual':
						if($EVENT->is_virtual() && !$EVENT->is_cancelled()):
							ob_start();

							$vir = new EVO_Event_Virtual($EVENT);
							$vir->print_eventcard_cell_html();
							
							$OT.= ob_get_clean();
						endif;
					break;

				// health guidance
					case 'health':

						if( !$EVENT->check_yn('_health')) break;

						ob_start();
						include('views/html-eventcard-health.php');
						return ob_get_clean();


					break;

				// paypal link
						case 'paypal':
							$ev_txt = $EVENT->get_prop('evcal_paypal_text');
							$text = ($ev_txt)? $ev_txt: evo_lang_get('evcal_evcard_tix1','Buy ticket via Paypal');

							$currency = !empty($evOPT['evcal_pp_cur'])? $evOPT['evcal_pp_cur']: false;
							$email = ($EVENT->get_prop('evcal_paypal_email')? $EVENT->get_prop('evcal_paypal_email'): $evOPT['evcal_pp_email']);

							if($currency && $email):
								$_event_time = $EVENT->get_formatted_smart_time();							
								
								ob_start();
							?>
							

							<div class='evo_metarow_paypal evorow evcal_evdata_row evo_paypal'>
								<span class='evcal_evdata_icons'><i class='fa <?php echo esc_html( get_eventON_icon('evcal__fai_007', 'fa-ticket',$evOPT ) );?>'></i></span>
								<div class='evcal_evdata_cell'>
									<p style='padding-bottom:5px;'><?php echo esc_html( $text );?></p>
									<form target="_blank" name="_xclick" action="https://www.paypal.com/us/cgi-bin/webscr" method="post">
										<input type="hidden" name="cmd" value="_xclick">
										<input type="hidden" name="business" value="<?php echo esc_html( $email );?>">
										<input type="hidden" name="currency_code" value="<?php echo esc_html( $currency );?>">
										<input type="hidden" name="item_name" value="<?php echo esc_html( $EVENT->post_title.' '.$_event_time );?>">
										<input type="hidden" name="amount" value="<?php echo esc_html( $EVENT->get_prop('evcal_paypal_item_price') );?>">
										<input type='submit' class='evcal_btn' value='<?php echo esc_html( evo_lang_get('evcal_evcard_btn1','Buy Now') );?>'/>
									</form>										
								</div></div>							
							<?php $OT.= ob_get_clean();
							endif;

						break;

				// social share u2.2.13
					case 'evosocial':
						ob_start();
						include('views/html-eventcard-social.php');
						return ob_get_clean();

					break;
				
			}// end switch

			// for custom meta data fields
				if(!empty($object->x) && $box_name == 'customfield'.$object->x){
					$i18n_name = eventon_get_custom_language($evoOPT2,'evcal_cmd_'.$object->x , $evOPT['evcal_ec_f'.$object->x.'a1']);

					// user role restriction access validation
					if( 
						($object->visibility_type=='admin' && !current_user_can( 'manage_options' ) ) ||
						($object->visibility_type=='loggedin' && !is_user_logged_in() && empty($object->login_needed_message))
					){}else{

						$OT .="<div class='evo_metarow_cusF". esc_attr( $object->x )." evorow evcal_evdata_row evcal_evrow_sm '>
								<span class='evcal_evdata_icons'><i class='fa ". esc_attr( $object->imgurl ) ."'></i></span>
								<div class='evcal_evdata_cell'>							
									<h3 class='evo_h3'>". esc_html( $i18n_name ) ."</h3>";

							// if visible only to loggedin users and user is not logged in
							if( !empty($object->login_needed_message)){
								$OT .="<div class='evo_custom_content evo_data_val'>". $object->login_needed_message . "</div>";
							}else{
								if($object->type=='button'){
									$_target = (!empty($object->_target) && $object->_target=='yes')? 'target="_blank"':null;
									$OT .="<a href='". esc_url( $object->valueL ) ."' {$_target} class='evcal_btn evo_cusmeta_btn'>". esc_html( $object->value ) ."</a>";
								}else{
									$OT .="<div class='evo_custom_content evo_data_val'>". 
									(  EVO()->frontend->filter_evo_content($object->value) )."</div>";
								}
							}
						
						$OT .="</div></div>";
					}
				}

		return $OT;
	}

// view in my time 
	public function get_view_my_time_content($timezone_data, $start, $end){
		extract( $timezone_data );
		
		$data = array(
			'__df'=> $__df,
			'__tf'=> $__tf,
			'__f'=> $__f,
			'times'=> $start . '-' . $end,
			'tzo' => $this->help->get_timezone_offset( $this->ev_tz,  $start)
		);

		return "<em class='evcal_tz_time evo_mytime tzo_trig' title='". evo_lang('My Time') ."'  ". $this->help->array_to_html_data($data)."><i class='fa fa-globe-americas'></i> <b>{$__t}</b></em>";	

	}

// SEO Schema data
	function get_schema($EventData, $_eventcard){
		extract($EventData);
		$EVENT = $this->EVENT;

		//print_r($EventData);

		$__scheme_data = '<div class="evo_event_schema" style="display:none" >';

		$tz = strpos($this->timezone, '-') === false? '+'. $this->timezone : $this->timezone;


		// Start time 
			$_schema_starttime = $_schema_endtime = '';
			if(is_array($start_date_data))
				$_schema_starttime = $start_date_data['Y'].'-'.$start_date_data['n'].'-'.$start_date_data['j'].( !$EVENT->is_all_day()? 'T'.$start_date_data['H'].':'.$start_date_data['i']. $tz. ':00' :'');
			if(is_array($end_date_data))
				$_schema_endtime = $end_date_data['Y'].'-'.$end_date_data['n'].'-'.$end_date_data['j']. ( !$EVENT->is_all_day()? 'T'.$end_date_data['H'].':'.$end_date_data['i'].$tz. ':00':'');

		// Event Status
			$ES = array(
				'cancelled'=>'https://schema.org/EventCancelled',
				'movedonline'=>'https://schema.org/EventMovedOnline',
				'postponed'=>'https://schema.org/EventPostponed',
				'rescheduled'=>'https://schema.org/EventRescheduled',
			);

			$_ES = isset($ES[$_status])? $ES[$_status]: 'https://schema.org/EventScheduled';

		
		// Event details				
			$__schema_desc = !empty($event_excerpt_txt)? $event_excerpt_txt : (isset($EVENT->post_title)? '"'.$EVENT->post_title.'"':'');
			if(!empty($event_details)) $__schema_desc = $event_details;
			$__schema_desc = str_replace("'","'", $__schema_desc);
			$__schema_desc = str_replace('"',"", $__schema_desc);
			$__schema_desc = preg_replace( "/\r|\n/", " ", $__schema_desc );

		// attendence mode
			$AM = ucfirst( $EVENT->get_attendance_mode() );
			$_AM = 'https://schema.org/'. $AM .'EventAttendanceMode';
		
		if(!empty($schema) && $schema){	
			// for each schema custom values
			foreach(apply_filters('evo_event_schema',array(
				'url'=>array(
					'type'=>'a',
					'attr'=>'href',
					'attrcontent'=> $EVENT->get_permalink()
				),					
				'image'=>array(
					'type'=>'meta',
					'content'=> (!empty($img_src) &&!empty($img_src)? $img_src:'')
				),					
				'startDate'=>array(
					'type'=>'meta',
					'content'=> $_schema_starttime
				),
				'endDate'=>array(
					'type'=>'meta',
					'content'=> $_schema_endtime
				),
				'eventStatus'=>array(
					'type'=>'meta',
					'content'=>  $_ES
				),
			),$EVENT, $EVENT->ID) as $key=>$value){
				$__scheme_data .= "<".(!empty($value['type'])? $value['type']:'meta') ." itemprop='". esc_attr( $key ) ."' ".(!empty($value['content'])? 'content="'. esc_html( $value['content'] ).'"':'') ." ". ( !empty($value['attr'])? $value['attr']."='". esc_html( $value['attrcontent'] )."'":'');

				if(!empty($value['itemtype'])) $__scheme_data .= ' itemscope itemtype="'. esc_html( $value['itemtype'] ) .'"';
				
				$__scheme_data .= ($value['type'] =='meta')? "/>": ">";
				$__scheme_data .= (!empty($value['html'])? $value['html'] :'');
				$__scheme_data .= (isset($value['type']) && $value['type'] == 'meta')? '': 
					( isset($value['type'])? "</". esc_attr( $value['type'] ) .">" :'' ); 
			}
			
			// location data
				if( !empty($location_type) && $location_type =='virtual'){
					$__scheme_data .= '<item style="display:none" itemprop="location" itemscope itemtype="http://schema.org/VirtualLocation">';
					if(!empty($location_link)) $__scheme_data .= '<span itemprop="url">'. esc_url( $location_link ) .'</span>';
					$__scheme_data .= "</item>";

					//$_AM = 'https://schema.org/OnlineEventAttendanceMode';
					
				}

				if(!empty($location_address)){

					$__scheme_data .= '<item style="display:none" itemprop="location" itemscope itemtype="http://schema.org/Place">'. ( !empty($location_name)? '<span itemprop="name">'.$location_name.'</span>':'').'<span itemprop="address" itemscope itemtype="http://schema.org/PostalAddress"><item itemprop="streetAddress">'. stripslashes($location_address) .'</item></span></item>';					
				}

				$__scheme_data .= '<item style="display:none" itemprop="eventAttendanceMode" itemscope itemtype="'.$_AM.'"></item>';

			// offer data
				if( $EVENT->get_prop('_seo_offer_price') && $EVENT->get_prop('_seo_offer_currency')){
					$__scheme_data .= '<div itemprop="offers" itemscope itemtype="http://schema.org/Offer">
				        <div class="event-price" itemprop="price" content="'.$EVENT->get_prop('_seo_offer_price').'">'.$EVENT->get_prop('_seo_offer_price').'</div>
				        <meta itemprop="priceCurrency" content="'.$EVENT->get_prop('_seo_offer_currency').'">
				        <meta itemprop="url" content="'.$EVENT->get_permalink().'">
				        <meta itemprop="availability" content="http://schema.org/InStock">
				        <meta itemprop="validFrom" content="'.$_schema_starttime.'">
				    </div>';
				}

		    // organizer data
			    if(!empty($organizer) && isset($organizer->name)){
				    $__scheme_data .= '<div itemprop="organizer" itemscope="" itemtype="http://schema.org/Organization">
				    	<meta itemprop="name" content="'.$organizer->name.'">
				    	'. (!empty($organizer_link)? '<meta itemprop="url" content="'.$organizer_link.'">':'').
				    '</div>';
				}

			// performer data using organizer data
				if( $EVENT->get_prop('evo_event_org_as_perf') && !empty($organizer) && isset($organizer->name)){
					 $__scheme_data .= '<div itemprop="performer" itemscope="" itemtype="http://schema.org/Person">
				    	<meta itemprop="name" content="'.$organizer->name.'">
				    </div>';
				}
		}else{
			$__scheme_data .= '<a href="'.$event_permalink.'"></a>';
		}

		// JSON LD
		if(!empty($schema_jsonld) && $schema_jsonld){
			$__scheme_data .= '<script type="application/ld+json">';				

			// event status
			$_schema_eventstatus = ',"eventStatus":"'. $_ES .'"';

			// location
				$_schema_location = ''; 
				
				if(!empty($location_type) && $location_type == 'virtual' || !empty($location_address)){
					$_schema_location .= ',"location":';
				}

				if(!empty($location_type) && $location_type == 'virtual' || !empty($location_address))
					$_schema_location .= '[';

				if(!empty($location_type) && $location_type == 'virtual'){
					$_schema_location .= '{"@type":"VirtualLocation"';
					if(!empty($location_link)) $_schema_location .= ',"url":"'.$location_link.'"';
					$_schema_location .= '}';
				}
				if(!empty($location_address)){

					if(!empty($location_type) && $location_type == 'virtual')
						$_schema_location .= ',';

					if( !empty($location_name) ) 
						$location_name = str_replace('"', "", $location_name);
					$_name = !empty($location_name)? '"name":"'.$location_name.'",':'';
					
					$_schema_location .= '{"@type":"Place",'.$_name.'"address":{"@type": "PostalAddress","streetAddress":"'. str_replace("\,",",", stripslashes($location_address) ).'"}}';
				}
				if(!empty($location_type) && $location_type == 'virtual' || !empty($location_address)){
					$_schema_location .= ']';
				}

			// organizer 
				$_schema_performer = $_schema_organizer = '';
				if(!empty($organizer) && isset($organizer->name)){
					$_schema_organizer = ',"organizer":{"@type":"Organization","name":"'.$organizer->name.'"'. 
						( !empty($organizer_link)? ',"url":"'.$organizer_link.'"':'').
						'}';				
				}

			// perfomer data using organizer
				if( $EVENT->get_prop('evo_event_org_as_perf') && !empty($organizer) && isset($organizer->name) ){
					$_schema_performer = ',"performer":{"@type":"Person","name":"'.$organizer->name.'"}';
				}

			// offers field
				$_schema_offers = '';
				if( $EVENT->get_prop('_seo_offer_price') && $EVENT->get_prop('_seo_offer_currency')){
					$_schema_offers = ',"offers":{"@type":"Offer","price":"'. esc_html( $EVENT->get_prop('_seo_offer_price') ) .'","priceCurrency":"'. esc_html( $EVENT->get_prop('_seo_offer_currency') ).'","availability":"http://schema.org/InStock","validFrom":"'. esc_html( $_schema_starttime ) .'","url":"'. esc_url( $EVENT->get_permalink() ).'"}';
				}

			// build the schema content
			$__scheme_data .= 
				'{"@context": "http://schema.org","@type": "Event",
				"@id": "event_'. esc_html( $EVENT->get_event_uniqid() ).'",
				"eventAttendanceMode":"'. $_AM .'",
				"name": '.(isset($EVENT->post_title)? '"'.htmlspecialchars( $EVENT->post_title, ENT_QUOTES ) .'"' :'').',
				"url": "'. $EVENT->get_permalink() .'",
				"startDate": "'.$_schema_starttime.'",
				"endDate": "'.$_schema_endtime.'",
				"image":'.(!empty($img_src) &&!empty($img_src)? '"'. esc_url( $img_src ).'"':'""').', 
				"description":"'.$__schema_desc.'"'.
			  	$_schema_location.
			  	$_schema_organizer.
			  	$_schema_performer.
			  	$_schema_offers.
			  	$_schema_eventstatus.
			  	apply_filters('eventon_event_json_schema_adds', '', $EVENT, $EVENT->ID).
			'}';
			$__scheme_data .= "</script>";
		}
		$__scheme_data .= "</div>";

		return $__scheme_data;
	}
}
