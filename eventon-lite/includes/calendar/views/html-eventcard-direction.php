<?php 
/**
 * EventCard directions html content
 * @version 2.3
 */

$_lang_1 = evo_lang_get('evcalL_getdir_placeholder','Type your address to get directions');
$_lang_2 = evo_lang_get('evcalL_getdir_title','Click here to get directions');

$_from_address = false;
if(!empty($location_address)) $_from_address = $location_address;
if(!empty($location_getdir_latlng) && $location_getdir_latlng =='yes' && !empty($location_latlng)){
	$_from_address = $location_latlng;
}

if($_from_address){

echo "<div class='evo_metarow_getDr evorow evcal_evdata_row evcal_evrow_sm getdirections'>
	<form action='https://maps.google.com/maps' method='get' target='_blank'>
	<input type='hidden' name='daddr' value=\"". esc_attr( $_from_address )."\"/> 
	<p><input class='evoInput' type='text' name='saddr' placeholder='". esc_html( $_lang_1 ) ."' value=''/>
	<button type='submit' class='evcal_evdata_icons evcalicon_9' title='". esc_html( $_lang_2 ) ."'><i class='fa ".esc_attr( get_eventON_icon('evcal__fai_008a', 'fa-road',$evOPT ) ) ."'></i></button>
	</p></form>
</div>";
}