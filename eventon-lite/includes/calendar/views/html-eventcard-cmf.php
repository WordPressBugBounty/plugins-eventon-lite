<?php 
/**
 * Custom Meta Field
 * @version 2.3
 * @fullversion 4.7.4
 */



$cmf_key = 'evcal_ec_f'.$object->x.'a'; // evcal_ec_f1a
$i18n_name = eventon_get_custom_language($evoOPT2,'evcal_cmd_'.$object->x , $evOPT['evcal_ec_f'.$object->x.'a1']);

// user role restriction access validation
if( 
	($object->visibility_type=='admin' && !current_user_can( 'manage_options' ) ) ||
	($object->visibility_type=='loggedin' && !is_user_logged_in() && empty($object->login_needed_message))
){}else{

	//print_r($object);

	// value processing with passed on {}
	$VV = $EVENT->process_dynamic_tags( $EVENT->get_custom_data_value( $object->x ) );		

	echo "<div class='evo_metarow_cusF{$object->x} evorow evcal_evdata_row evcal_evrow_sm '>
			<span class='evcal_evdata_icons'><i class='fa ".$object->imgurl."'></i></span>
			<div class='evcal_evdata_cell'>							
				<h3 class='evo_h3'>".$i18n_name."</h3>";

		// if visible only to loggedin users and user is not logged in
		if( !empty($object->login_needed_message)){
			echo "<div class='evo_custom_content evo_data_val'>". $object->login_needed_message . "</div>";
		}else{

			

			echo "<div class='evo_custom_content evo_data_val evodfx evofx_dr_r evofx_ww evogap20'>";


			echo "<div class=''>";

			// button type
			if($object->type=='button'){

				$link = $EVENT->process_dynamic_tags( $object->valueL );	
				$_target = (!empty($object->_target) && $object->_target=='yes')? 'target="_blank"':null;

				// above button content
				if( $cmf_above = $EVENT->get_prop('_'.$cmf_key.'_T')){
					echo  EVO()->frontend->filter_evo_content( $cmf_above );
				}

				echo "<a href='". $link ."' {$_target} class='evcal_btn evo_cusmeta_btn'>". $VV ."</a>";
			// everything else
			}else{

				echo EVO()->frontend->filter_evo_content( $VV );
													
			}

			echo "</div>";
			echo "</div>";
		}
	
	echo "</div></div>";
}
