<?php
/*
	styles tab for eventon settings
	version: 2.3
*/
?>

<div id="evcal_3" class="postbox evcal_admin_meta curve">	
	
	<div class="inside">
		<h2><?php esc_html_e('Add your own custom styles','eventon');?></h2>
		<p><i><?php esc_html_e('Please use text area below to write your own CSS styles to override or fix style/layout changes in your calendar. <br/>These styles will be appended into the dynamic styles sheet loaded on the front-end.','eventon')?></i></p>
		
		<div class='evoadmin_code_container evopad20 evomart20'>
			<textarea class='evcal_styles_dynamic' style='width:100%; height:350px' name='evcal_styles'><?php echo esc_textarea(get_option('evcal_styles') );?></textarea>				
		</div>


		<h2 style='padding-top:30px'><?php esc_html_e('Auto generated Dynamic Styles','eventon');?></h2>
		<p><i><?php esc_html_e('If your dynamic styles (appearance changes in eventon settings) do not reflect on front-end, it could be that your website is blocking eventon from using wp_filesystems() to write these dynamic styles to "eventon_dynamic_styles.css". <br/>In this case please <b>copy</b> the below CSS styles and paste it on your theme styles (style.css).','eventon')?></i></p>

		<div class='evoadmin_code_container evopad20 evomart20'>
			<textarea class='evcal_styles_dynamic' readonly style='width:100%; height:350px;' name='evcal_styles_dynamic'><?php
				ob_start();
				include(AJDE_EVCAL_PATH.'/assets/css/dynamic_styles.php');

				$content = ob_get_clean();
				echo esc_html( $content );
			?></textarea>	
		</div>

		<p><i><?php printf( __('NOTE: These styles will update everytime you make changes in eventon appearance settings','eventon') );?></i></p>	

		
	</div>
</div>
<input type="submit" class="evo_admin_btn btn_prime" value="<?php esc_html_e('Save Changes') ?>" />
</form>