<?php
/**
 * EventCard Details
 * @version 2.3
 * @fullversion 4.7.4
 */

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

echo "<div class='evo_metarow_details evorow evcal_evdata_row evcal_event_details". esc_attr( $end_row_class ) ."'>
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

				echo $more_code;

				echo"<div class='clear'></div>
			</div>
		</div>
	</div>";