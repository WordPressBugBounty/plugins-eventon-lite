<?php
/**
 * EventCard Repeat
 * @version 2.3
 * @fullversion 4.7.4
 */

echo "<div class='evo_metarow_repeats evorow evcal_evdata_row evcal_evrow_sm ". esc_attr( $end_row_class ) ."'>
		<span class='evcal_evdata_icons'><i class='fa ". esc_attr( get_eventON_icon('evcal__fai_repeats', 'fa-repeat',$evOPT ) ) ."'></i></span>
		<div class='evcal_evdata_cell'>							
			<h3 class='evo_h3'>". esc_html( eventon_get_custom_language($evoOPT2, 'evcal_lang_repeats','Future Event Times in this Repeating Event Series') ) ."</h3>
			<p class='evo_repeat_series_dates ".($object->clickable?'clickable':'')."'' data-click='". esc_attr( $object->clickable ) ."' data-event_url='". esc_url( $object->event_permalink )."'>";

	$datetime = new evo_datetime();

	// allow for custom date time format passing to repeat event times
	$repeat_start_time_format = apply_filters('evo_eventcard_repeatseries_start_dtformat','');
	$repeat_end_time_format = apply_filters('evo_eventcard_repeatseries_end_dtformat','');
	
	foreach($object->future_intervals as $key=>$interval){
		echo "<span data-repeat='". esc_attr( $key )."' data-l='". esc_url( $EVENT->get_permalink($key,$EVENT->l) ) ."' class='evo_repeat_series_date'>"; 
		

		if($EVENT->is_year_long()){
			echo gmdate('Y', $interval[0]);
		}elseif( $EVENT->is_month_long()){

			echo $EVENT->get_readable_formatted_date( $interval[0], 'F, Y');
			
		}else{

			echo $EVENT->get_readable_formatted_date( $interval[0], $repeat_start_time_format);

			if( $object->showendtime && !empty($interval[1])){

				echo ' - '.$EVENT->get_readable_formatted_date( $interval[1], $repeat_end_time_format);
			}

		}
		
		
		echo "</span>";
	}

echo "</p></div></div>";  