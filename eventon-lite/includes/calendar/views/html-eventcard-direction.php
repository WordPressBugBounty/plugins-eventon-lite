<?php 
/**
 * EventCard directions html content
 * @version 2.5
 */

$_lang_1 = evo_lang_get('evcalL_getdir_placeholder','Type your address to get directions');
$_lang_2 = evo_lang_get('evcalL_getdir_title','Click here to get directions');
$_lang_3 = evo_lang('Address copied to clipboard!');
$_lang_4 = evo_lang('Copy Destination Address!');

$aria_id = uniqid();
$aria_label = esc_html( evo_lang('Address') .' - '. $EVENT->get_title() .' []');
$aria_label2 = esc_html( evo_lang('Destination Address') .' - '. $EVENT->get_title() .' []');
$readable_location_address = !empty( $location_address )? $location_address: false;


echo "<div class='evo_metarow_getDr evorow evcal_evdata_row evcal_evrow_sm getdirections'>
		<form action='https://maps.google.com/maps' method='get' target='_blank'>
			<input type='hidden' name='daddr' value=\"{$_from_address}\"/> 
			<div class='evo_get_direction_content evo_fx_dr_r evogap10'>
				<span class='evogetdir_header evodfx evofxdrr evofxaic evogap10'>
					<i class='mainicon fa ".get_eventON_icon('evcal__fai_008a', 'fa-route',$evOPT )." evofz24i'></i> 
					<h3 class='evo_h3 evopad0i' style='padding-bottom:5px;'>". evo_lang('Get Directions') ."</h3>
				</span>
				<span class='evogetdir_field evodfx evofx_1_1 evow100p'>	
					<label for='{$aria_id}' class='evo_aria_ready sr-only'>{$aria_label}</label>				
					<input id='{$aria_id}' class='evo_aria_ready_match evoInput2 evobr10 evow100p evopad10 evoff_2 evofz14i evobrdB1 evoboxbb' type='text' name='saddr' placeholder='{$_lang_1}' value='' style='' aria-label='{$aria_label}' />
				</span>
				<div class='evodfx evofxdrr evofxaic evogap10'>
					<i class='fa fa-location-dot'></i>
					<div class='evoposr evofx_1 evoh100p'>";
						if( $readable_location_address):
						echo "<label for='{$aria_id}' class='evo_aria_ready sr-only'>{$aria_label2}</label>		
						<input id='{$aria_id}' class='evo_aria_ready_match evoInput2 evobr10 evow100p evopad5-10 evoff_2 evofz14i evobrdB1 evoop5 evoboxbb evoh100p' type='text' style='' readonly value='{$readable_location_address}' aria-label='{$aria_label2}'/>";
						endif;
						echo "<button class='evo_copy_address evobuttonA evocurp evohoop7 evoposa evopad5' style='right: 3px; top: 4px; background-color: #fff;' data-txt='{$_from_address}' data-t='{$_lang_3}'><i class='far fa-copy evofz16i' title='{$_lang_4}'></i></button>
					</div>
					<button type='submit' class='evo_get_direction_button evcal_btn dfx evofxaic evocurp evohoop7' title='{$_lang_2}' style='    padding: 10px 15px !important;'><i class='fa fa-chevron-right'></i> </button>
				</div>
			</div>
		</form>
	</div>";


$R=  "<div class='evo_metarow_getDr evorow evcal_evdata_row evcal_evrow_sm getdirections'>
	<form action='https://maps.google.com/maps' method='get' target='_blank'>
	<input type='hidden' name='daddr' value=\"". esc_attr( $_from_address )."\"/> 
	<p><input class='evoInput' type='text' name='saddr' placeholder='". esc_html( $_lang_1 ) ."' value=''/>
	<button type='submit' class='evcal_evdata_icons evcalicon_9' title='". esc_html( $_lang_2 ) ."'><i class='fa ".esc_attr( get_eventON_icon('evcal__fai_008a', 'fa-road',$evOPT ) ) ."'></i></button>
	</p></form>
</div>";
