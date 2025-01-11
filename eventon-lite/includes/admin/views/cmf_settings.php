<?php 
/**
 * Event CMF settings
 * @version 2.3
 * @version 4.7.4
 */

$EVENT = new EVO_Event( $post_data['event_id'] );
EVO()->cal->set_cur('evcal_1');
$metabox_array = array();

?>
<div class=''>
	<form class=''>
		<?php wp_nonce_field( 'evo_save_secondary_settings', 'evo_noncename' );?>
		<?php

		EVO()->elements->print_hidden_inputs(array(
			'event_id'=> $EVENT->ID,
		));


		// get each cmf
		$cmf_count = evo_calculate_cmd_count( EVO()->cal->get_op('evcal_1') );
		for($x =1; $x<=$cmf_count; $x++){	
			if(!eventon_is_custom_meta_field_good($x)) continue;

			$fa_icon_class = EVO()->cal->get_prop('evcal__fai_00c'.$x);		

			$visibility_type = (!empty($evcal_opt1['evcal_ec_f'.$x.'a4']) )? $evcal_opt1['evcal_ec_f'.$x.'a4']:'all' ;
			$__field_key = 'evcal_ec_f'.$x.'a';

			$metabox_array[] = array(
				'id'=>'evcal_ec_f'.$x.'a1',
				'variation'=>'customfield',
				'name'=>	EVO()->cal->get_prop('evcal_ec_f'.$x.'a1'),		
				'iconURL'=> $fa_icon_class,
				'iconPOS'=>'',
				'x'=>$x,
				'visibility_type'=>$visibility_type,
				'type'=>'code',
				'content'=>'',
				'slug'=>'evcal_ec_f'.$x.'a1',
				'field_key'=> $__field_key,
				'field_type'=> EVO()->cal->get_prop( $__field_key .'2')
			);
		}

		if( count($metabox_array)>0):	

			$html_fields = array();

			// print each cmf field
			foreach($metabox_array as $index=>$mBOX){
				
				extract( $mBOX );

				EVO()->cal->set_cur('evcal_1');

				$x = $mBOX['x'];
				$__field_key = 'evcal_ec_f'.$x.'a'; // evcal_ec_f1a
				$__field_id = '_'.$__field_key .'1_cus';
				$__field_type = EVO()->cal->get_prop( $__field_key .'2','evcal_1');


				// add to html data
				$html_fields[] = $__field_id;

				// field header data
				?>
				<div class='evo_border evobr20 evomarb20 ' data-id='<?php echo $__field_id;?>'>
					<div class='evodfx evofx_dr_r evofx_jc_sb evoborderb evopad20'>
						<span class='evofz18 evofw700 evoff_1 evodb'><i class='fa <?php echo $iconURL;?>'></i> <?php echo $name;?></span>
						<span class='evofz14 evoop5 evofsi'><?php _e('Visibility');?> <b><?php echo $visibility_type;?></b></span>
					</div>
					<div class='evopad30'>
				<?php
						
					
										

					// FIELD
					$__saved_field_value = ($EVENT->get_meta_null( $__field_id ) );
					
					switch ($__field_type) {
						case 'textareaX':
							wp_editor($__saved_field_value, $__field_id, array('wpautop' => true ));
							break;

						case 'textarea':
						case 'textarea_trumbowig':
							echo EVO()->elements->get_element(array(
								'type'=> 'wysiwyg',
								'id'=> $__field_id,
								'name'=> __('Field Content','eventon'),
								'value'=> $__saved_field_value
							));	
							break;

						case 'textarea_basic':

							echo EVO()->elements->get_element(array(
								'type'=> 'textarea',
								'id'=> $__field_id,
								'name'=> __('Field Content','eventon'),
								'value'=> $__saved_field_value
							));	
							break;

						case 'button':
							$__saved_field_link = ($EVENT->get_meta_null("_" . $__field_key . "1_cusL")  );
							$input_value = ( !empty($__saved_field_value) ? addslashes($__saved_field_value ) :'' );

							echo EVO()->elements->get_element(array(
								'type'=>'textarea',
								'id'=> '_'.$__field_key .'_T',
								'value'=> $EVENT->get_meta_null( '_'.$__field_key .'_T'),
								'name'=>__('Above Button Content (Optional)','eventon')
							));
							echo EVO()->elements->get_element(array(
								'type'=>'input',
								'id'=> $__field_id,
								'value'=> $input_value,
								'name'=>__('Button Text','eventon')
							));
							echo EVO()->elements->get_element(array(
								'type'=>'input',
								'id'=> $__field_id.'L',
								'value'=> $__saved_field_link,
								'name'=>__('Button Link','eventon')
							));
												
							// open in new window
							$onw = ($EVENT->get_meta_null("_evcal_ec_f".$x."_onw") );
							?>

							<span class='yesno_row evo'>
								<?php 	
								echo EVO()->elements->yesno_btn(array(
									'id'=>'_evcal_ec_f'.$x . '_onw',
									'var'=> $EVENT->get_prop('_evcal_ec_f'.$x . '_onw'),
									'input'=>true,
									'label'=>__('Open in New window','eventon')
								));?>											
							</span>
						<?php
							break;
						default:
							echo EVO()->elements->get_element(array(
								'type'=> 'input',
								'id'=> $__field_id,
								'name'=> '',
								'value'=> $__saved_field_value
							));	
							break;	
					}
					

					echo "</div>";
				echo "</div>";
			}

			// pass html fields on the settings form
			EVO()->elements->print_hidden_inputs(array(
				'html_fields'=> json_encode( $html_fields ),
			));

			EVO()->elements->print_trigger_element(
				array(
					'title'=> __('Save Changes','eventon'),
					'uid'=> 'evo_save_secondary_settings',
					'lbdata'=> array(
						'class'=>'config_cmf_data'
					),
					'adata'=> array(
						'a'=>'eventon_save_secondary_settings',
						'end'=>'admin',
					)
				),'trig_form_submit');
			

		else:
			echo '<p class="pad20"><span class="evomarb10" style="display:block">' . __('You do not have any custom meta fields activated.') . '</span><a class="evo_btn" href="'. get_admin_url(null, 'admin.php?page=eventon#evcal_009','admin') .'">'. __('Activate Custom Meta Fields','eventon') . '</a></p>';
		endif;

		?>
	</form>
</div>