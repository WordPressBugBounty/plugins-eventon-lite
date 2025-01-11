<?php 
/**
 * Event Edit Meta box Location
 * @version 2.3
 * @fullversion 4.7.4
 */

?>
<div class='evcal_data_block_style1'>
	<p class='edb_icon evcal_edb_map'></p>
	<div class='evcal_db_data'>
		<div class='evcal_location_data_section'>										
			<div class='evo_singular_tax_for_event event_location' data-tax='event_location' data-eventid='<?php echo esc_attr( $p_id );?>'>
			<?php
				echo EVO()->taxonomies->get_meta_box_content( 'event_location' ,esc_attr( $p_id ), esc_html__('location','eventon'));
			?>
			</div>									
		</div>										
		<?php

			// if generate gmap enabled in settings
				$gen_gmap = !$EVENT->check_yn('evcal_gmap_gen') ? true: false;

			// yea no options for location
			foreach(array(
				'evo_access_control_location'=>array('evo_access_control_location',esc_html__('Make location information only visible to logged-in users','eventon')),
				'evcal_hide_locname'=>array('evo_locname',esc_html__('Hide Location Name from Event Card','eventon')),
				'evcal_gmap_gen'=>array('evo_genGmap',esc_html__('Generate Google Map from the address','eventon')),
				'evcal_name_over_img'=>array('evcal_name_over_img',esc_html__('Show location information over location image (If location image exist)','eventon')),
			) as $key=>$val){

				$variable_val = $EVENT->get_prop($key)? $EVENT->get_prop($key): 'no';

				if($variable_val == 'no' && $gen_gmap && $key=='evcal_gmap_gen')
						$variable_val = 'yes';

				EVO()->elements->print_element(
					array(
						'type'=>'yesno_btn',
						'label'=> esc_attr( $val[1] ), 
						'id'=> esc_attr( $key ),
						'value'=> esc_attr( $variable_val )
					)
				);
			}

			// check google maps API key
			if( !EVO()->cal->get_prop('evo_gmap_api_key','evcal_1')){
				echo "<p class='evo_notice'>".esc_html__('Google Maps API key is required for maps to show on event. Please add them via ','eventon') ."<a href='". esc_url( get_admin_url() ) .'admin.php?page=eventon#evcal_005'."'>".esc_html__('Settings','eventon'). "</a></p>";
			}
		?>									
	</div>
</div>
<?php