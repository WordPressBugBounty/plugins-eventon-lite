<?php
/** 
 * Location Image
 * @version 2.3
 */

$img_src = wp_get_attachment_image_src($location_img_id,'full');

$fullheight = (int)EVO()->calendar->get_opt1_prop('evo_locimgheight',400);

if(!empty($img_src)){

	$cover_content = '';
	$more_btn_html = '';
	
	// text over location image
	$inside = $inner = '';
	if(!empty($location_name) && $EVENT->check_yn('evcal_name_over_img') ){		

		$cover_content .= ( !empty( $location_name ) ? "<h3 class='evo_h3 evofz24i'>{$location_name}</h3>" : '' );
		$cover_content .= (!empty($location_address) ? '<span class="evodb" style="padding-bottom:10px">'. stripslashes($location_address) .'</span>':'' );
		$cover_content .= (!empty($location_description) ? '<span class="location_description evodb">'. $location_description .'</span>':'' );


		if( !empty( $cover_content )){
			$more_btn_html = "<span class='evo_locimg_more evo_transit_all evo_trans_sc1_1 evodfx evofx_jc_c evofx_ai_c evocurp'><i class='fa fa-plus'></i></span>";
		}
	}

	$inside .= "
		<div class='evo_gal_main_img evobgsc evobgpc' style='background-image:url(". $img_src[0] ."); height:100%; width:100%' data-f='{$img_src[0]}' data-w='{$img_src[1]}' data-h='{$img_src[2]}'>
			<div class='evo_locimg_over evodfx evoposa evofx_jc_c evofx_ai_c evofx_dr_c evotop0 evoleft0 evow100p evoh100p'>
				<div class='evo_locimg_over_in evopad30 evotac'>{$cover_content}</div>
			</div>
			<div class='evo_locimg_bottom evoposa evodfx evofx_jc_fe evofx_ai_fe evow100p evoleft0 evobot0'>
				<div class='evo_locimg_right evodfx evofx_jc_fe evofx_ai_c'>
					". ( !empty( $location_name ) ? "<h3 class='evo_h3 evo_locimg_title evotar'>{$location_name}</h3>" : '' ) ."
					{$more_btn_html}
				</div>
			</div>
		</div>";
	
	echo "<div class='evcal_evdata_row evo_metarow_locImg evorow ".( !empty($inside)?'tvi':null)."' style='height:{$fullheight}px; padding:0;' id='". esc_attr( $location_img_id ) ."_locimg' >{$inside}</div>";
} 