<?php
/**
 * Event Edit Meta box Health Guidance
 * @version 2.3
 * @fullversion 4.8
 */


$related_events = $EVENT->get_prop('ev_releated');


echo "<div class='evcal_data_block_style1'>
<div class='evcal_db_data evo_rel_events_box'>";
	
	EVO()->elements->print_hidden_inputs( array(
		'ev_releated' => esc_attr( $related_events ),
		'ev_related_event_id'=> $EVENT->ID,
		'ev_related_text' => __('Configure Related Event Details','eventon'),
	));


	if($EVENT->is_repeating_event()){
		echo "<p>".esc_html__('NOTE: You can not select a repeat instance of this event as related event.','eventon').'</p>';
	}
	?>
	<span class='ev_rel_events_list'><?php
		if($related_events){
			$D = json_decode($related_events, true);

			$rel_events = array();

			foreach($D as $I=>$N){
				$id = explode('-', $I);
				$EE = new EVO_Event($id[0]);
				$x = isset($id[1])? $id[1]:'0';
				$time = $EE->get_formatted_smart_time($x);
				
				$rel_events[ $I.'.'. $EE->get_start_time() ] =  "<span class='l' data-id='{$I}'><span class='t'>{$time}</span><span class='n'>{$N}</span><i class='fa fa-close'></i></span>";
			}

			//krsort($rel_events);

			foreach($rel_events as $html){
				echo wp_kses_post( $html );
			}
			
		}
	?></span>

	<div class='evopadt10'>
		<?php 
			EVO()->elements->get_element(array(
				'type'=>'detailed_button', '_echo'=> true,
				'name'=>__('Add related event','eventon'),
				'description'=>__('Configure Related Event Details','eventon'),
				'field_after_content'=> "Confiure",
				'row_class'=> 'evo_bordern evomarb5',
				'field_class'=>'evo_configure_related_events'
			));
		?>
	</div>

<?php echo "</div></div>";