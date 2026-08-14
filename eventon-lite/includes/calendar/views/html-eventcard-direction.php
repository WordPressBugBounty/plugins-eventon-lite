<?php 
/**
 * EventCard directions html content
 * @version 2.5.6
 */

$_lang_1 = evo_lang_get('evcalL_getdir_placeholder','Type your address to get directions');
$_lang_2 = evo_lang_get('evcalL_getdir_title','Click here to get directions');
$_lang_3 = evo_lang('Address copied to clipboard!');
$_lang_4 = evo_lang('Copy Destination Address!');

$aria_id = uniqid();
$aria_label = esc_html( evo_lang('Address') .' - '. $EVENT->get_title() .' []');
$aria_label2 = esc_html( evo_lang('Destination Address') .' - '. $EVENT->get_title() .' []');
$readable_location_address = !empty( $location_address )? $location_address: false;

$btn_style_class = "evodfx evofxdrr evogap5 evofxaic evocurp evohoop7o evofwb evoHKx2 evoHbgclg20 evopad10i evobr10 evocl1i evofz18i evonobtn";
$input_go_btn_styles = "right: 5px;
    /* margin: 5px; */
    box-sizing: border-box;
    top: 5px;
    bottom: 5px;
    height: calc(100% - 10px);
    padding: 10px 20px !important;";


echo "<div class='evo_metarow_getDr evorow evcal_evdata_row evcal_evrow_sm getdirections'>
		<form action='https://maps.google.com/maps' method='get' target='_blank' rel='noopener noreferrer'>
			<input type='hidden' name='daddr' value=\"{$_from_address}\"/> 
			<div class='evo_get_direction_content evo_fx_dr_r evogap10'>
				<span class='evogetdir_header evodfx evofxdrr evofxaic evogap10'>
					<i class='mainicon fa ".get_eventON_icon('evcal__fai_008a', 'fa-route',$evOPT )." evofz24i'></i> 
					<h3 class='evo_h3 evopad0i' style='padding-bottom:5px;'>". evo_lang('Get Directions') ."</h3>
				</span>";

				// get direction from types address field
				if( EVO()->cal->check_yn('evo_map_dir_field','evcal_1')){
					echo "<span class='evogetdir_field evodfx evofx_1_1 evow100p evoposr'>	
						<label for='{$aria_id}' class='evo_aria_ready sr-only'>{$aria_label}</label>				
						<input id='{$aria_id}' class='evo_aria_ready_match evoInput2 evobr10 evow100p evopad10 evoff_2 evofz14i evobrdB1 evoboxbb' type='text' name='saddr' placeholder='{$_lang_1}' value='' style='' aria-label='{$aria_label}' />
						<button type='submit' class='evo_get_direction_button evcal_btn dfx evofxaic evocurp evohoop7 evoposa' style='{$input_go_btn_styles}' title='{$_lang_2}' aria-label='{$_lang_2}' style='padding: 10px 15px !important;'><i class='fa fa-chevron-right evofz12i'></i> </button>
					</span>";
				}

				// Direction modes
				
				?>
				<p class=''><?php evo_lang_e('How do you plan to get there?');?></p>
				<div class='evodfx evofxdrr evofxaic evogap5'>
					<?php
					foreach([
						['diamond-turn-right', 'best', 'Best Route'],
						['car',          'driving',     'Driving'],
					    ['bus',          'transit',     'Transit'],
					    ['person-walking','walking',    'Walking'],
					    ['person-biking', 'bicycling',  'Bicycling'],
					    //['plane',        'driving',     'Flying'],
					] as $mode){
						$gmap_link = "https://www.google.com/maps/dir/?api=1" .
			                 "&origin=Current+Location" .
			                 "&destination=" . urlencode($_from_address) .
			                 "&travelmode=" . $mode[1];

						?>
						<a href='<?php echo $gmap_link;?>'  target='_blank' rel='noreferrer' aria-label='<?php evo_lang_e( $mode[2] ); ?>'  class='<?php echo $btn_style_class;?>'><i class='fa fa-<?php echo $mode[0];?> evotooltipfree' title='<?php evo_lang_e( $mode[2] ); ?>'></i></a><?php
					}
					?>	
				</div>

				<?php
			echo "</div>
		</form>
	</div>";

