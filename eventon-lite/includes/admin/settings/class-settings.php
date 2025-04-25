<?php
/**
 *	EventON Settings Main Object
 *	@version 2.4
 */
class EVO_Settings extends EVO_Settings_Designer{
	
	public $page; 
	public $focus_tab;
	public $current_section, $options_pre;
	private $tab_props = false;

	public function __construct(){
		$this->page = (isset($_GET['page']) )? sanitize_text_field( $_GET['page'] ):false;
		//$this->focus_tab = (isset($_GET['tab']) )? sanitize_text_field( urldecode($_GET['tab'])):'evcal_1';
		$this->current_section = (isset($_GET['section']) )? sanitize_text_field( urldecode($_GET['section'])):'';
		$this->options_pre = 'evcal_options_';
		

		// update focus tab based on page
			$page_tabs = array(
				'eventon'=>'evcal_1',
				'eventon-lang'=>'evcal_2',
				'eventon-styles'=>'evcal_3',
				'eventon-extend'=>'evcal_4',
				'eventon-support'=>'evcal_5',
			);
			if( $this->page && array_key_exists( $this->page, $page_tabs) ) $this->focus_tab = $page_tabs[ $this->page ];


		$this->options_pre = 'evcal_options_';
	}

// Styles and scripts
	public function load_styles_scripts(){
		wp_enqueue_media();	

		wp_enqueue_script('evcal_functions', EVO()->assets_path. 'js/eventon_functions.js', array('jquery'), EVO()->version ,true );

		wp_enqueue_style('settings_styles');
		wp_enqueue_script('settings_script');

		EVO()->elements->load_colorpicker();
	}

	public function register_ss(){
		$this->register_styles();
		$this->register_scripts();

		EVO()->elements->register_colorpicker();
	}
	public function register_styles(){
		wp_register_style( 'settings_styles',EVO()->assets_path.'lib/settings/settings.css','',EVO()->version);		
	}

	public function register_scripts(){
		wp_register_script('settings_script',EVO()->assets_path.'lib/settings/settings.js', array('jquery', 'jquery-ui-core', 'jquery-ui-sortable'), EVO()->version, true );
		wp_localize_script('settings_script', 'evoajax', [
	        'nonce' => wp_create_nonce('eventon_settings_save_nonce'),
	    ]);
		
		EVO()->elements->register_shortcode_generator_styles_scripts();
	}

// CONTENT
	function print_page(){
			
		// Get current section
			//$this->focus_tab = (isset($_GET['tab']) )? sanitize_text_field( urldecode($_GET['tab'])):'eventon';
			$this->current_section = (isset($_GET['section']) )? sanitize_text_field( urldecode($_GET['section'])):'';	
			
		// Load eventon settings values for current tab
			$evcal_opt = $this->get_current_tab_values();	
		
		// OTHER options
				$genral_opt = get_option('evcal_options_evcal_1');

		// TABBBED HEADER	
		extract( array(
			'version'=>get_option('eventon_plugin_version'),
			'title'=>__('EventON Lite Settings','eventon'),
			'tabs'=> apply_filters('eventon_settings_tabs',array(
				'eventon'=> 		__('Settings', 'eventon'), 
				'eventon-lang'=> 	__('Language', 'eventon'),
				'eventon-styles'=>	__('Styles', 'eventon'),
				'eventon-extend'=>	__('Extend', 'eventon'),
				'eventon-support'=>	__('Support', 'eventon'),
			)),
			'tab_page'=>'?page=',
			'tab_attr_field'=>'evcal_meta',
			'tab_attr_pre'=>'evcal_',
			'tab_id'=>'evcal_settings'
		) );	

		?>
		<div class="wrap ajde_settings <?php echo $this->focus_tab;?>" id='<?php echo $tab_id;?>'>
			<div class='evo_settings_header'>
				<h2 class='settings_m_header'><?php echo $title;?> (ver <?php echo $version;?>) 
					<span class='evo_set_right'>
						<?php
						do_action('evo_admin_settings_header_right');

						// SETTINGS SAVED MESSAGE
							$updated_code = (isset($_POST['settings-updated']) && $_POST['settings-updated']=='true')? '<div class="evo_updated updatedx fade"><p>'.__('Settings Saved','eventon').'</p></div>':null;
							echo $updated_code;	
						?>
						<span class='evo_trig_form_save evo_admin_btn btn_blue'><?php _e('Save Changes','eventon');?></span>
					</span>
				</h2>
				<div class='evo_settings_nav'>
					<div class='evo_settings_nav_in'>
					<h2 class='nav-tab-wrapper' id='meta_tabs'>
						<?php					
							foreach($tabs as $key=>$val){
								
								echo "<a href='{$tab_page}".$key."' class='{$key} nav-tab ".( ($this->page == $key)? 'nav-tab-active':null)." {$key}' ". 
									( (!empty($tab_attr_field) && !empty($tab_attr_pre))? 
										$tab_attr_field . "='{$tab_attr_pre}{$key}'":'') . ">".$val."</a>";
							}			
						?>		
					</h2>
				</div>
				</div>
			</div>
		<?php

		require_once(AJDE_EVCAL_PATH.'/includes/admin/settings/class-settings-content.php');
	}

// INITIATION
	function get_current_tab_values(){		
		$current_tab_number = substr($this->focus_tab, -1);
		EVO()->cal->reload_option_data( $this->focus_tab );

		$tab_props = $this->tab_props = EVO()->cal->get_op( $this->focus_tab  );

		return array( $current_tab_number => $tab_props );
	}

	function get_prop($field){
		if(!isset($this->tab_props[$field])) return false;
		return $this->tab_props[$field];
	}


// OTHER
	function settings_tab_start($args){
		?>
		<form method="post" action="">
			<?php settings_fields($args['field_group']); ?>
			<?php wp_nonce_field( $args['nonce_key'], $args['nonce_field'] );?>
		<div id="<?php echo esc_attr($args['tab_id']);?>" class="<?php echo esc_attr( implode(' ', $args['classes']) );?>">
			<div class="<?php echo esc_attr( implode(' ', $args['inside_classes']) );?>">
				<?php
	}
	function settings_tab_end(){
		?></div></div><?php
	}
	

}