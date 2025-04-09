<?php
/**
 * EventCard Time
 * @version 2.4
 */

$iconTime = "<span class='evcal_evdata_icons'><i class='fa ". esc_attr( get_eventON_icon('evcal__fai_002', 'fa-clock-o',$evOPT ) ) ."'></i></span>";
						
						
// time for event card
$timezone = (!empty($object->timezone)? ' <em class="evo_eventcard_tiemzone evomarl5 evoop7">'. $object->timezone.'</em>':null);

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

echo "<div class='evo_metarow_time evorow evcal_evdata_row evcal_evrow_sm ". esc_attr( $end_row_class ) ."'>
		{$iconTime}
		<div class='evcal_evdata_cell'>							
			<h3 class='evo_h3'>".$iconTime . evo_lang_get('evcal_lang_time','Time')."</h3><p>".$evc_time_text."</p>
		</div>
	</div>";