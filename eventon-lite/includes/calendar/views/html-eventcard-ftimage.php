<?php 
/** 
 * EventON Event Featured Image
 * @version 2.5.2
 */ 


$__hoverclass = (!empty($object->hovereffect) && $object->hovereffect!='yes')? ' evo_imghover':null;
$__noclickclass = (!empty($object->clickeffect) && $object->clickeffect=='yes')? ' evo_noclick':null;
$__zoom_cursor = (!empty($evOPT['evo_ftim_mag']) && $evOPT['evo_ftim_mag']=='yes')? ' evo_imgCursor':null;

EVO()->cal->set_cur('evcal_1');



// make sure image array object passed
if( $object->main_image && is_array($object->main_image)){

	$IMG = $BGURL = '';
	$main_image = $object->main_image;

	$height = !empty($object->img[2])? $object->img[2]:'';
	$width = !empty($object->img[1])? $object->img[1]:'';

	$new_width = 0;
	$BGURL = '';

	$img_hw_ratio = 1;
	if( $main_image['full_w'] > 0) 
		$img_hw_ratio = (int) $main_image['full_h'] / (int)$main_image['full_w'];

	// minimum height
	$__height = EVO()->cal->get_prop('evo_ftimgheight');
	if( !$__height) $__height = 400;

	// image style 
	$img_sty = EVO()->cal->get_prop('evo_ftimg_height_sty');
	if( !$img_sty) $img_sty = 'def';


	if( $img_sty != 'def') $IMG = "<span style='height:{$__height}px; background-image:url({$object->img})'></span>";
	if( $img_sty == 'def') $BGURL = $object->img;

	if( $img_sty == 'fit' ) $IMG = "<img src='$object->img' style='max-height:100%; max-width:100%;'/>";

	$_mi_data = array(
		'f'=>  $object->img,
		'h'=> $main_image['full_h'],
		'w'=> $main_image['full_w'],
		'ratio'=> $img_hw_ratio,
		'event_id'=>$EVENT->ID,
		'ri'=>$EVENT->ri
	);

	$styles = "height:{$__height}px; background-image:url({$BGURL});";

	echo "<div class='evocard_main_image_hold' data-t='". evo_lang('Loading Image') ."..'>";
	echo "<div class='evocard_main_image evo_img_triglb evocd_img_{$img_sty} evobr15 evobgsc evobgpc evodfx evofx_jc_c evofx_ai_c evofz48 {$img_sty}' style='{$styles}' ". $this->helper->array_to_html_data( $_mi_data ) ." data-t='". evo_lang('Loading Image') ."..'>{$IMG}</div>";
	echo "</div>";

	$R= "<div class='evo_metarow_fimg evorow evc al_evdata_img evcal_evdata_row ". esc_attr( $end_row_class.$__hoverclass.$__zoom_cursor.$__noclickclass )."' data-imgheight='". esc_attr( $main_image['full_h'] ) ."' data-imgwidth='". esc_attr( $main_image['full_w'] ) ."'  style='{$styles}' data-imgstyle='". esc_attr( $object->ftimg_sty )."' data-minheight='". esc_attr( $object->min_height )."' data-status='' ". $this->helper->array_to_html_data( $_mi_data ) ."></div>";
}



