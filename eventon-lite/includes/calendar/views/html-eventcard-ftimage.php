<?php 
/** 
 * EventON Event Featured Image
 * @version 2.3
 * @fullversion 4.7.4
 */ 


$__hoverclass = (!empty($object->hovereffect) && $object->hovereffect!='yes')? ' evo_imghover':null;
$__noclickclass = (!empty($object->clickeffect) && $object->clickeffect=='yes')? ' evo_noclick':null;
$__zoom_cursor = (!empty($evOPT['evo_ftim_mag']) && $evOPT['evo_ftim_mag']=='yes')? ' evo_imgCursor':null;



// if set to direct image
if(!empty($evOPT['evo_ftimg_height_sty']) && $evOPT['evo_ftimg_height_sty']=='direct'){
	// ALT Text for the image
		$alt = !empty($object->img_id)? get_post_meta($object->img_id,'_wp_attachment_image_alt', true):false;
		$alt = !empty($alt)? 'alt="'. esc_html( $alt ) .'"': '';
	echo "<div class='evo_metarow_directimg evcal_evdata_row'><img class='evo_event_main_img' src='". esc_url( $object->img )."' ".  $alt ."/></div>";
}else{

	// make sure image array object passed
	if( $object->main_image && is_array($object->main_image)){

		$main_image = $object->main_image;

		$height = !empty($object->img[2])? $object->img[2]:'';
		$width = !empty($object->img[1])? $object->img[1]:'';

		echo "<div class='evo_metarow_fimg evorow evcal_evdata_img evcal_evdata_row ". esc_attr( $end_row_class.$__hoverclass.$__zoom_cursor.$__noclickclass )."' data-imgheight='". esc_attr( $main_image['full_h'] ) ."' data-imgwidth='". esc_attr( $main_image['full_w'] ) ."'  style='background-image: url(\"". esc_url( $object->img )."\")' data-imgstyle='". esc_attr( $object->ftimg_sty )."' data-minheight='". esc_attr( $object->min_height )."' data-status=''></div>";
	}
}


