<?php
/**
 * EventCard Location
 * @version 2.3
 * @fullversion 4.7.4
 */

$iconLoc = "<span class='evcal_evdata_icons'><i class='fa ". esc_attr( get_eventON_icon('evcal__fai_003', 'fa-map-marker',$evOPT ) ) ."'></i></span>";

						


if(!empty($EventData['location_name']) || !empty($EventData['location_address'])){
	
	$locationLink = (!empty($location_link))? '<a target="'. ($location_link_target=='yes'? '_blank':'') .'" href="'. esc_url( evo_format_link($location_link) ).'">':false;
	
	echo 
	"<div class='evcal_evdata_row evo_metarow_time_location evorow '>
		
			{$iconLoc}
			<div class='evcal_evdata_cell' data-loc_tax_id='". esc_attr( $EventData['location_term_id'] )."'>";

			if( $location_hide){
				echo "<h3 class='evo_h3'>".$iconLoc. evo_lang_get('evcal_lang_location','Location'). "</h3>";
				echo "<p class='evo_location_name'>". EVO()->calendar->helper->get_field_login_message() . "</p>";
			}else{
				
				echo "<h3 class='evo_h3'>".$iconLoc.($locationLink? $locationLink:''). evo_lang_get('evcal_lang_location','Location').($locationLink?'</a>':'')."</h3>";

				if( !empty($location_name) && !$EVENT->check_yn('evcal_hide_locname') )
					echo "<p class='evo_location_name' style='margin-bottom:10px; font-size:15px;'>". $locationLink. $location_name . ($locationLink? '</a>':'') ."</p>";

				// for virtual location
				if( $location_type == 'virtual'){
					if( $locationLink) 
						echo "<p class='evo_virtual_location_url'>" . evo_lang('URL:'). $locationLink . ' '. $location_link."</a></p>";
				}else{

					if(!empty($location_address) && $location_address != $location_name ){
						$encoded_address = urlencode($location_address);
						$gmap_link = "<a href='https://www.google.com/maps?q={$encoded_address}' target='_blank' rel='noopener noreferrer'><i class='fa fa-arrow-up-right-from-square'></i></a>";
    
						echo "<p class='evo_location_address evodfxi evogap10'>". $locationLink . stripslashes($location_address) . ($locationLink? '</a>':'') . $gmap_link . "</p>";
					}
					
				}	

				// location contacts
				if( !empty($loc_phone) || !empty($loc_email)){
					echo "<div class='evopadt5'><p class='evo_location_contact'>". (!empty($loc_phone) ? $loc_phone .' ' :'' ). (!empty($loc_email) ? "<a href='mailto:{$loc_email}'>$loc_email</a>" :'' ) ."</p></div>";
				}

				// open in maps button
				if( EVO()->cal->check_yn('evo_openinapp','evcal_1')){
					$encoded_address = rawurlencode($location_address);
					?>
					<a href="https://maps.apple.com/?q=<?php echo $encoded_address; ?>"
					   target="_blank"
					   rel="noopener noreferrer"
					   class="evo_openmap_trig evo_btn_arr evomart10i"
					   data-d="<?php echo $encoded_address; ?>">
					   <i class="fa fa-location-arrow evodfxi evofxaic" aria-hidden="true" style="    margin: 0 10px 0 0;"></i> 
					   <?php evo_lang_e('Open in Maps');?>
					   <i class='fa fa-chevron-right aria-hidden="true"'></i>
					</a>
   				 <?php 
				}	
			}
			echo "</div>
		
	</div>";
}