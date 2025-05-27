/**
 * Javascript: EventON functions for all calendars
 * @version: 2.4
 */
(function($){

 	// Basic
		// Calendar function s
			$.fn.evo_cal_functions = function(O){
				el = this;
				switch(O.return){
					// load shortcodes inside calendar data
					case 'load_shortcodes':
						return el.find('.evo_cal_data').data('sc');		
					break;
					case 'update_shortcodes':
						el.find('.evo_cal_data').data( 'sc', O.SC );
					break;
				}
			};

		// access page GLOBALS
			$.fn.evo_get_global = function(opt){
				var defaults = { S1:'', S2:''};
				var OPT = $.extend({}, defaults, opt);

				var BUS = $('#evo_global_data').data('d');

				if(!(OPT.S1 in BUS)) return false;
				if(!(OPT.S2 in BUS[OPT.S1])) return false;
				return BUS[OPT.S1][OPT.S2];
			}
			$.fn.evo_get_txt = function(opt){
				var defaults = { V:''}
				var OPT = $.extend({}, defaults, opt);

				var BUS = $('#evo_global_data').data('d');
				if(!('txt' in BUS)) return false;
				if(!(OPT.V in BUS.txt)) return false;
				return BUS.txt[OPT.V];
			}
			$.fn.evo_get_cal_def = function(opt){
				var defaults = { V:''}
				var OPT = $.extend({}, defaults, opt);

				var BUS = $('#evo_global_data').data('d');
				if(!('cal_def' in BUS)) return false;
				if(!(OPT.V in BUS.cal_def)) return false;
				return BUS.cal_def[OPT.V];
			}

			// return dms translates values from global data
			// added 4.0
			$.fn.evo_get_dms_vals = function(opt){
				// type = d, d1,d3, m, m3
				// V = 0-x
				var defaults = { type:'d', V:''}
				var OPT = $.extend({}, defaults, opt);

				var BUS = $('#evo_global_data').data('d');			
				if(!('dms' in BUS)) return false;
				if(!(OPT.type in BUS.dms)) return false;

				return BUS.dms[ OPT.type ][ OPT.V ];
				
			}

	// GENERAL AJAX ACCESS @4.9.2
		$.fn.evo_admin_get_ajax = function(opt){

			var el = this,
		        OO = this.evo_process_ajax_params(opt),
		        LB = OO.lbdata.class ? $('body').find('.evo_lightbox.' + OO.lbdata.class) : false,
		        ajax_url = el.evo_get_ajax_url({a: OO.adata.a, e: OO.adata.end, type: OO.adata.ajax_type});


	  		// Run AJAX
  			$.ajax({
				beforeSend: function(){
					if (opt.onBefore) opt.onBefore.call(el, OO, LB);
					el.evo_perform_ajax_run_loader( OO, LB, 'start'  );
				},
				type: 'POST', url: ajax_url, data: OO.adata.data,	dataType:'json',
				success:function(data){	
					
					if (opt.onSuccess || opt.success) {
						// Call with onSuccess:function(data, OO, LB){} 
		                (opt.onSuccess || opt.success).call(el, data, OO, LB);
		            } else {
		                el.evo_perform_ajax_success(OO, data, LB);
		                if (opt.successExtra) opt.successExtra.call(el, OO, data, LB);
		            }					

				},complete:function(){
					if (opt.onComplete) opt.onComplete.call(el, OO, data, LB);
					el.evo_perform_ajax_run_loader( OO, LB, 'end'  );
					
				}
			});				
		}

		// submit forms via ligtbox
		// @since 4.2.2		@updated 4.9.2
		$.fn.evo_ajax_lightbox_form_submit = function(opt , formObj ){
			
  			const el = this;

  			var OO = this.evo_process_ajax_params( opt );

  			console.log(OO);

  			var _lbdata = OO.lbdata;
			var _adata = OO.adata;	
			var _populate_id = OO._populate_id;	
  			
  			var form = this.closest('form');
  			if( formObj !== undefined ) form = formObj;

  			// form required fields validation - @4.9
			if( el.hasClass('validate')){

				var hasError = false;

				$('body').trigger('evo_elm_form_presubmit_validation', [form, function(isValid) {
				    hasError =  isValid ? false: true;
				}]);

			    if( hasError){
			    	LB = el.closest('.evo_lightbox');
			    	LB.evo_lightbox_show_msg({message:'Required fields missing'});
			    	return;
			    }	
			}

  			// for lightbox
				var LB = false;
	  			if( _lbdata.class != '') LB = $('body').find('.evo_lightbox.'+ _lbdata.class );

  			// reset LB message
  			if( LB) LB.evo_lightbox_hide_msg();

  			var ajax_url = el.evo_get_ajax_url({a: _adata.a, e: _adata.end, type: _adata.ajax_type});

  			// Add passed on data from ajax object @4.8.2
			var extra_ajax_data = ('data' in _adata ) ? _adata.data : null;
	  			
  			// Submit form
				form.ajaxSubmit({
					beforeSubmit: function(opt, xhr){
						el.evo_perform_ajax_run_loader( OO, LB, 'start'  );
					},
					dataType: 	'json',	
					url: ajax_url,	type: 	'POST',
					data: extra_ajax_data,
					success:function(data){
						el.evo_perform_ajax_success( OO, data, LB );
					},
					complete:function(){	
						el.evo_perform_ajax_run_loader( OO, LB, 'end'  );
					}
				});
		}

	// perform ajax functions / type = start/end
		$.fn.evo_perform_ajax_run_loader = function( OO , LB, type ){
			var el = this;
			var _lbdata = OO.lbdata;
			var _adata = OO.adata;	

			//console.log(OO);

			var customer_loader_elm = false;
			var loader_btn_el = false;

  			if( _adata.loader_el !='')	customer_loader_elm = _adata.loader_el;
  			if( 'loader_class' in _adata && _adata.loader_class != '') 
  				customer_loader_elm = $('.' + _adata.loader_class);
  			if( _adata.loader_btn_el != '' && _adata.loader_btn_el !== undefined ) loader_btn_el = el;
  			
  			var LB_loader = false;
  			if( LB && 'loader' in _lbdata && _lbdata.loader ) LB_loader = true;

  			if( type == 'start'){

  				var trigger_id = ( 'uid' in OO && OO.uid != '' ) ? OO.uid : OO.ajax_action; // @4.8
  				$('body').trigger('evo_ajax_beforesend_' + trigger_id ,[ OO, el ]);

				if( LB_loader ){
					LB.find('.ajde_popup_text').addClass( 'evoloading loading'); // legacy
					LB.evo_lightbox_start_inloading();
				}
				if( customer_loader_elm ) $( customer_loader_elm ).addClass('evoloading loading');
				if( loader_btn_el ) el.addClass('evobtn_loader'); // loader on button
  			}else{

  				var trigger_id = ( 'uid' in OO && OO.uid != '' ) ? OO.uid : OO.ajax_action; // @4.8

  				$('body').trigger('evo_ajax_complete_' + trigger_id ,[ OO , el ]);
			
				if( LB_loader ){
					LB.find('.ajde_popup_text').removeClass( 'evoloading loading');
					LB.evo_lightbox_stop_inloading();	
				}
				if( customer_loader_elm ) $( customer_loader_elm ).removeClass('evoloading loading');
				if( loader_btn_el ) el.removeClass('evobtn_loader'); // loader on button

				
  			}

			return {
				'l1': customer_loader_elm,
				'l2':LB_loader
			};
		}

		$.fn.evo_perform_ajax_success = function ( OO, data, LB ){
			var el = this;
			var _lbdata = OO.lbdata;
			var _adata = OO.adata;	
			var _populate_id = OO._populate_id;		

			if( !data || data === undefined ) return;	

			// if json is passing data object @4.9
			var _success = ('success' in data) ? data.success : (data.status === 'good');

			var extractedContent = 'content' in data ? data.content: '';
			if( 'data' in data && 'content' in data.data) extractedContent = data.data.content;
			var extractedData = ('data' in data) ? data.data : data;
			extractedData['content'] = extractedContent;

			// Ensure extractedData is an object for property checks; if it’s a string, wrap it
		    if (typeof extractedData !== 'object' || extractedData === null) {
		        extractedData = { msg: extractedData }; // Convert string to object
		    }

		    // Assign success and status to the extracted data
		    extractedData.success = _success;
		    extractedData.status = ('status' in extractedData) ? extractedData.status : (_success ? 'good' : 'bad');

		    // Replace original data with processed data
		    data = extractedData;

			// if inside lightbox
			if( LB.length > 0 ){
				// show message
					if (data && typeof data === 'object' && !Array.isArray(data) && 'msg' in data && data.msg !== '') {
						LB.evo_lightbox_show_msg({
							'type': ( _success ? 'good':'bad'), 
							'message':data.msg, 
							hide_lightbox: (  _success ? _lbdata.hide : false ),	
							hide_message: _lbdata.hide_msg
						});
					}	

				// populate lightbox
				if( data && _lbdata.new_content && 'content' in data && data.content != '' ){

					// populate a specific dom element with content
					if( _populate_id ){
						$('body').find('#'+_populate_id ).replaceWith( data.content );					
					}else{
						LB.evo_lightbox_populate_content({content: data.content});
					}
				}	
								
			}else{
				// populate content
				if( data && _populate_id && 'content' in data && data.content != ''){
					$('body').find('#'+_populate_id ).html( data.content );
				}						
			}

			// Show snackbar message
				if( 'show_snackbar' in  _adata && ('msg' in data)  && data.msg != '' ) 
					el.evo_snackbar({message: data.msg});

			// populate content with matching DOM class names, will set new html @4.7.2
				if( 'populate_dom_classes' in data){
					$.each( data.populate_dom_classes, function( domclass, content){
						$('body').find('.'+ domclass).html( content );
					} );
				}

			// if ajax data pass dom content to be replaced with run through each and replace - @4.2.3
				if( 'refresh_dom_content' in data ){
					$.each(data.refresh_dom_content, function(domid, content){
						$('body').find('#'+ domid).replaceWith( content);
					});
				}

			// for SP content @since 4.5.2
				if(data &&  'sp_content' in data){
					$("body").find('#evops_content').html( data.sp_content);
				}
			// SP footer content @since 4.5.2
				if( data && 'sp_content_foot' in data){
					$("body").find('.evosp_foot').html( data.sp_content_foot);
				}
			// process trumbowyg editors
				$('body').trigger('evo_elm_load_interactivity');


			// assign dynamic vals to DOM element
				setTimeout(function(){
					if( 'evoelms' in data ){
						$.each( data.evoelms , function( uniqueid, elm_data ){

							$('body').find('.has_dynamic_vals').each(function(){

								if( $(this).attr('id') != uniqueid ) return;
								var dynamic_elm = $(this);

								$.each( elm_data , function( elm_key, elmv){
									dynamic_elm.data( elm_key, elmv );
								});
							});
						});
					}
				},200);

			//console.log(OO);
			$('body').trigger('evo_ajax_success_' + OO.uid,[ OO, data , el]);	
		}

	// Process ajax and lightbox values @4.7.2
		$.fn.evo_process_ajax_params = function ( opt ){
			// defaults
			var defz = { 
				'uid':'',

				// @since 4.7.2
				'adata':{}, // @4.7.2 include all ajax data in here, type (ajax,rest,endpoint),action/a, other data
				'lbdata':{},// @4.7.2 lightbox data all in one place, class, title, size, padding
				'_populate_id':'', // loading new content into matching elements outside of lightbox
				
				// legacy values
				'content':'',// passed on dynamic content
				'content_id' :'',// id to get dynamic content from page		
								
				't':'', //title
				'lbc':'',// * lightbox class - REQUIRED
				'lbac':'',// ligthbox additional class 4.6
				'lbsz':'',// lightbox size = mid small, s400, s500, s700, s800
				'lightbox_loader': true,
				'preload_temp_key': 'init', // 4.6
				'load_new_content': true, // @since 4.3.5
				'lb_padding': '', // @4.3.5
				'load_new_content_id':'',

				'ajax':'no',// use ajax to load content yes no
				'ajax_url':'',// load content from ajax file
				'end':'admin',// admin or client end
				'ajax_action':'', // @4.4 pass on ajax endpoint action key
				'a':'',
				'ajax_type':'ajax', // @4.4 ajax type, ajax, rest or endpoint
				'd':'', // data object for ajax
				'other_data':'',
				'ajaxdata':'',				
			};


			// extend passed with defaults
			var OO = $.extend({}, defz, opt);

			// Build processed object
				var processed = {};
				processed['uid'] = OO.uid;


			// Ajax	
				var _adata = ( OO.adata == '') ? {}: OO.adata;

				// type passed value fix
				var passed_type_val = false;
				if( 'type' in _adata && _adata.type != '' &&
					!['ajax', 'rest', 'endpoint'].includes(_adata.type)
				){
					passed_type_val = _adata.type;
					_adata.type = '';
				}

				// set default needed values
				var def_avals = { 
					'a':'',
					'type':'ajax',
					'end':'admin',
					'data': '',
					'loader_el':'',
					'loader_btn_el':'',//4.8
					'loader_class':'',
					'url':'',
				}
				
				// set default values
				$.each( def_avals, function(key, value) {					
					if( key == 'data' && !( 'a' in _adata ) && ('data' in _adata ) && 'a' in _adata.data ) return;
					if( !(key in _adata ) && value != '' ) _adata[ key ] = value;
				});	

				//console.log( _adata );

				// map old to new
				var def_adata_mapping = { 
					'a' : 'a',
					'ajax_action' : 'a',
					'ajax_type':'type',
					'end': 'end',
					'ajax_url': 'url',
					'ajaxdata':'data',
					'd':'data',
				}
				$.each( def_adata_mapping, function(oldV, newV) {					
					if( newV in _adata && _adata[ newV ] != '' ) return;

					if(  oldV in OO && OO[oldV] !== '' ) {
						_adata[newV] = OO[oldV];
					}  
				});	

				if( _adata.data === undefined ) _adata.data = {};

				//console.log( _adata );			

				// Move additional values in _adata to _adata.data
					$.each(_adata, function(key, value) {
						if ( !(key in def_avals)) {
							//_adata.data[key] = value; // Move any extra values to _adata.data
							//delete _adata[key]; // Remove them from the main _adata object
						}
					});

				if( 'data' in _adata ){
					//_adata['data']['nn'] = ( _adata.end == 'client' ) ? evo_general_params.n : evo_admin_ajax_handle.postnonce; 
										
					_adata['data']['nn'] = (typeof evo_admin_ajax_handle !== 'undefined' && evo_admin_ajax_handle !== null) 
					    ? evo_admin_ajax_handle.postnonce 
					    : evo_general_params.n;


					_adata['data']['uid'] = processed['uid'];
					if( passed_type_val ) _adata['data']['type'] = passed_type_val;
					if( 'action' in _adata.data ) _adata['a'] =  _adata.data.action;
					if( 'a' in _adata.data ) _adata['a'] =  _adata.data.a;
					if( 'ajaxdata' in OO ) processed['ajaxdata'] = _adata.data;
				}		

				processed['adata'] = _adata;
				

			// lightbox
				var _lbdata = ( OO.lbdata == '') ? {}: OO.lbdata;

				// if legacy values exists > convert them to new
				var def_lbdata_mapping = {
					'lbc':'class',
				    'lbsz':'size',
				    'lbac' :'additional_class',
				    't':'title',
				    'lb_padding': 'padding',
				    'load_new_content':'new_content',
				    'lightbox_loader': 'loader',
				    'content_id':'content_id',
				    'content':'content',
				    'hide_lightbox':'hide',
				    'hide_message':'hide_msg',
				    'lightbox_key': 'class',
				}
				$.each( def_lbdata_mapping, function(oldV, newV) {
					// if _lbdata has new value > skip
					if (newV in _lbdata && _lbdata[newV] !== '' && _lbdata[newV] !== null && _lbdata[newV] !== undefined) {
				        return; 
				    }
					if(  oldV in OO && OO[oldV] !== '' ) {
						_lbdata[newV] = OO[oldV];
					} 
				});	

				// set default needed values
				var def_lbvals = {
					'padding':'evopad30',
					'loader': false,
					'preload_temp_key':'init',
					'new_content': true,
					'additional_class':'',
					'title':'',
					'hide':false,
					'hide_msg':2000,
					'content':'', // content for lightbox
					'content_id':'', // content id in DOM to grab content for lightbox
				}
				// set default values
				$.each( def_lbvals, function(key, value) {
					if(  key in _lbdata )  return;
					if( value == '') return;
					_lbdata[ key ] = value;
				});


				//console.log( _lbdata.new_content ) ;

				// load lightbox content legacy
				if( OO.ajaxdata.load_lbcontent ) _lbdata['new_content'] = true;
				if( OO.ajaxdata.load_new_content ) _lbdata['new_content'] = true;

				// populate new content @4.7.3
					processed['_populate_id'] =  false;
					if( OO.load_new_content_id != '')  processed['_populate_id'] = OO.load_new_content_id;
					if( 'new_content_id' in _lbdata && _lbdata.new_content_id != '')  processed['_populate_id'] = _lbdata.new_content_id;
					if( 'content_id' in _lbdata && _lbdata.content_id != '') processed['_populate_id'] = _lbdata.content_id;

				processed['lbdata'] = _lbdata;

			// make sure uid is moved to main level
				if( processed.uid == '' && 'uid' in processed['lbdata'] ) processed['uid'] = processed['lbdata']['uid'];

			// add legacy variables for backward compatibility
				$.each(opt, function(oldkey, oldval){
					if( oldkey in processed ) return;
					processed[ oldkey ] = oldval;
				});

			//console.log( processed );

			return processed;
		}

	// LIGHTBOX version 4.2
		// @version 4.2
		$('body').on('click','.evolb_trigger', function(event){
			if( event !== undefined ){
				event.preventDefault();
				event.stopPropagation();
			}
			
			$(this).evo_lightbox_open($(this).data('lbvals'));
		});
		$('body').on('click','.evolb_close_btn', function (){
			const LB = $(this).closest('.evo_lightbox');
			LB.evo_lightbox_close();
		});

		// @since 4.2.2
		$('body').on('click','.evolb_trigger_save, .evo_submit_form', function(event){
			if( event !== undefined ){
				event.preventDefault();
				event.stopPropagation();
			}
			$(this).evo_ajax_lightbox_form_submit( $(this).data('d') );
		});

		$('body').on('click','.evo_trigger_ajax_run', function(event){
			if( event !== undefined ){
				event.preventDefault();
				event.stopPropagation();
			}
			$(this).evo_admin_get_ajax( $(this).data('d') );
		});
		// @since 4.3.3
		$('body').on('evo_lightbox_trigger', function(event, data){
			$('body').evo_lightbox_open(data);
		});
		

	// Lightbox opening
		$.fn.evo_alert = function(opt){ // @since 4.9.2
			var defz = { 
		        'title': 'Confirmation Action',
		        'message': '',
		        'yes_text': 'Proceed',
		        'no_text': 'No',
		        'on_yes': function() {}, // Callback for Yes
		        'on_no': function() {}   // Callback for No
		    };	

		    // Extend passed options with defaults
		    var options = $.extend({}, defz, opt);

		    // Create alert HTML content
		    var alertHtml = '<div class="evo_alert_box">' +
		        '<p class="evotal evopad10i">' + options.message + '</p>' +
		        '<div class="evo_alert_buttons evotar">' +		            
		            '<button class="evo_alert_no evoboxsn evobrn evooln evocurp evohoop7 evopad10-20 evobr20 evobgclt">' + options.no_text + '</button>' +
		            '<button class="evo_alert_yes evoboxsn evobrn evooln evocurp evoHbgc1 evopad10-20 evobr20 evomarr10 evobgclp evoclw">' + options.yes_text + '</button>' +
		        '</div></div>';

		    // Define lightbox options with the alert content
		    var lightboxOptions = {
		        lbdata: {
		            class: 'evo_alert_lightbox',
		            title: options.title || 'Alert',
		            content: alertHtml, // Set the alert HTML as the lightbox content
		            padding: 'pad20', // Optional: Add padding class if needed
		            size:'small',
		        },
		    };

		    // Open the lightbox with the alert content
		    $(this).evo_lightbox_open(lightboxOptions);

		    // After the lightbox is opened, attach event handlers
		    setTimeout(function() {
		        var LIGHTBOX = $('.evo_lightbox.evo_alert_lightbox');
		        
		        LIGHTBOX.find('.evo_alert_yes').on('click', function() {
		            options.on_yes(LIGHTBOX); // Execute Yes callback
		            LIGHTBOX.evo_lightbox_close(); // Close the lightbox
		            removeKeyListeners(); // Remove key listeners
		        });

		        LIGHTBOX.find('.evo_alert_no').on('click', function() {
		            options.on_no(LIGHTBOX); // Execute No callback
		            LIGHTBOX.evo_lightbox_close(); // Close the lightbox
		            removeKeyListeners(); // Remove key listeners
		        });

		        // Keypress handler function
		        function handleKeyPress(event) {
		            if (event.key === 'Escape') { // Esc key
		                options.on_no(LIGHTBOX); // Execute No callback
		                LIGHTBOX.remove(); // Close the lightbox
		                removeKeyListeners(); // Remove key listeners
		            } else if (event.key === 'Enter') { // Enter key
		                options.on_yes(LIGHTBOX); // Execute Yes callback
		                LIGHTBOX.remove(); // Close the lightbox
		                removeKeyListeners(); // Remove key listeners
		            }
		        }

		        // Add keypress listener
		        $(document).on('keydown', handleKeyPress);

		        // Function to remove key listeners
		        function removeKeyListeners() {
		            $(document).off('keydown', handleKeyPress);
		        }

		        // Ensure key listeners are removed when lightbox is closed manually (e.g., via close button)
		        LIGHTBOX.find('.evolb_close_btn').on('click', function() {
		            removeKeyListeners();
		        });
		    }, 350); // Delay to ensure lightbox is fully rendered
		}
		$.fn.evo_lightbox_open = function (opt ){

			var OO = this.evo_process_ajax_params( opt );
			
			var _lbdata = OO.lbdata;
			var _adata = OO.adata;
			var _populate_id = OO._populate_id;

			

			// check if required values missing for lightbox
			if( !('class' in _lbdata) || _lbdata.class == '' ) return;


			const fl_footer = _adata.end == 'client' ? '<div class="evolb_footer"></div>' :'';

			// create lightbox HTML
				var __lb_size = _lbdata.size === undefined ? '' : _lbdata.size;

				var html = '<div class="evo_lightbox '+_lbdata.class+' '+_adata.end+' '+ ( _lbdata.additional_class !== undefined ? _lbdata.additional_class :'') +'" data-lbc="'+_lbdata.class+'"><div class="evolb_content_in"><div class="evolb_content_inin"><div class="evolb_box '+_lbdata.class+' '+ __lb_size +'"><div class="evolb_header"><a class="evolb_backbtn" style="display:none"><i class="fa fa-angle-left"></i></a>';
				if( _lbdata.title !== undefined ) html += '<p class="evolb_title">' + _lbdata.title + '</p>';
				html += '<span class="evolb_close_btn evolbclose "><i class="fa fa-xmark"><i></span></div><div class="evolb_content '+ _lbdata.padding +'"></div><p class="message"></p>'+fl_footer+'</div></div></div></div>';

			$('#evo_lightboxes').append( html );	
			var LIGHTBOX = $('.evo_lightbox.'+ _lbdata.class );		

			
			// Open lightbox on page
				setTimeout( function(){ 
					$('#evo_lightboxes').show();
					LIGHTBOX.addClass('show');	
					$('body').addClass('evo_overflow');
					$('html').addClass('evo_overflow');
				},300);

				// show loading animation
				LIGHTBOX.evo_lightbox_show_open_animation(OO);


			// Load content locally from DOM
			    if (_lbdata.content_id != '') {
			        var content = $('#' + _lbdata.content_id).html();
			        LIGHTBOX.find('.evolb_content').html(content);
			    }
			    if (_lbdata.content != '') {
			        LIGHTBOX.find('.evolb_content').html(_lbdata.content);
			    }

			// run ajax to load content for the lightbox inside
				if( 'a' in _adata  && _adata.a != ''){ // @4.7.2
					LB.evo_admin_get_ajax( OO );
				}
				
			// load content from a AJAX file			
				if( 'url' in _adata && _adata.url != '' ){
					$.ajax({
						beforeSend: function(){},
						url:	OO.ajax_url,
						success:function(data){
							LIGHTBOX.find('.evolb_content').html( data);							
						},complete:function(){}
					});
				}
			
			$('body').trigger('evo_lightbox_processed', [ OO, LIGHTBOX]);
		}

		$.fn.evo_lightbox_close = function(opt) {
		    if (!this.hasClass('show')) return;

		    const defaults = { delay: 500, remove_from_dom: true };
		    const OO = $.extend({}, defaults, opt);
		    const hideDelay = parseInt(OO.delay);
		    const completeClose = this.parent().find('.evo_lightbox.show').length === 1;

		    // Remove 'show' class with delay if needed
		    hideDelay > 500 ? setTimeout(() => this.removeClass('show'), hideDelay - 500) : this.removeClass('show');

		    // Final cleanup after delay
		    setTimeout(() => {
		        if (completeClose) {
		            $('body, html').removeClass('evo_overflow');
		        }
		        if (OO.remove_from_dom) this.remove();
		    }, hideDelay);
		};


		// Other LB functions
			$.fn.evo_lightbox_populate_content = function(opt){
				LB = this;
				var defaults = { 
					'content':'',
				}; var OO = $.extend({}, defaults, opt);
				LB.find('.evolb_content').html( OO.content );
			}
			
			$.fn.evo_lightbox_show_msg = function(opt){
				LB = this;
				var defaults = { 
					'type':'good',
					'message':'',
					'hide_message': false,// hide message after some time pass time or false
					'hide_lightbox': false, // hide lightbox after some time of false
				}; var OO = $.extend({}, defaults, opt);
				LB.find('.message').removeClass('bad good').addClass( OO.type ).html( OO.message ).fadeIn();

				if( OO.hide_message ) setTimeout(function(){  LB.evo_lightbox_hide_msg() }, OO.hide_message );

				if( OO.hide_lightbox ) LB.evo_lightbox_close({ delay: OO.hide_lightbox });
			}
			$.fn.evo_lightbox_hide_msg = function(opt){
				LB = this;
				LB.find('p.message').hide();
			}


			// add preload animations to lightbox u4.6
			$.fn.evo_lightbox_show_open_animation = function(opt){
				LB = this;
				var defaults = { 
					'animation_type':'initial', // animation type initial or saving
					'preload_temp_key': 'init', // 4.6 passed on preload template key
					'end':'admin',
				};
				var OO = $.extend({}, defaults, opt);

				if( OO.animation_type == 'initial'){

					passed_data = (  typeof evo_admin_ajax_handle !== 'undefined') ? evo_admin_ajax_handle: evo_general_params;

					//console.log( passed_data);

					html = passed_data.html.preload_general;
					if( OO.preload_temp_key != 'init') html = passed_data.html[ OO.preload_temp_key ];
					LB.find('.evolb_content').html( html );
				}

				if( OO.animation_type == 'saving')
					LB.find('.evolb_content').addClass('loading');
			}

	// Get Ajax url @since 4.4 @u 4.5.5
		$.fn.evo_get_ajax_url = function(opt){
			var defaults = { 
				a:'', // action key
				e:'client', // end
				type: 'ajax'};
			var OO = $.extend({}, defaults, opt);

			// end point url
			if( OO.type == 'endpoint'){
				var evo_ajax_url = ( OO.e == 'client' || typeof evo_general_params !== 'undefined' )? 
					evo_general_params.evo_ajax_url : evo_admin_ajax_handle.evo_ajax_url;
				return  evo_ajax_url.toString().replace( '%%endpoint%%', OO.a );
			// rest api url
			}else if( OO.type == 'rest' ){
				var evo_ajax_url = ( OO.e == 'client' || typeof evo_general_params !== 'undefined')? 
					evo_general_params.rest_url : evo_admin_ajax_handle.rest_url;
					//console.log(evo_ajax_url);
					//console.log(OO);
				return  evo_ajax_url.toString().replace( '%%endpoint%%', OO.a );
			}else{
				action_add = OO.a != '' ? '?action='+ OO.a: '';
				return ( OO.e == 'client' || typeof evo_general_params !== 'undefined') ? 
					evo_general_params.ajaxurl + action_add : evo_admin_ajax_handle.ajaxurl + action_add;
			}	
		}

	// loading animations @2.3
		$.fn.evo_start_loading = function( opt ){
			var defaults = { type:'1'};
			var OPT = $.extend({}, defaults, opt);
			var el = this;

			if( OPT.type == '1') el.addClass('evoloading loading');
			if( OPT.type == '2') el.addClass('evoloading_2');
		}
		$.fn.evo_stop_loading = function( opt ){
			var el = this;
			var defaults = { type:'1'};
			var OPT = $.extend({}, defaults, opt);

			if( OPT.type == '1') el.removeClass('evoloading loading');
			if( OPT.type == '2') el.removeClass('evoloading_2');
		}
		$.fn.evo_lightbox_start_inloading = function(opt){
			LB = this;
			LB.find('.evolb_content').addClass('loading');
		}
		$.fn.evo_lightbox_stop_inloading = function(opt){
			LB = this;
			LB.find('.evolb_content').removeClass('loading');
		}
		
	// Count down	// @+ 4.5
		$.fn.evo_countdown_get = function(opt){
			var defaults = { gap:'', endutc: ''};
			var OPT = $.extend({}, defaults, opt);
			var gap = OPT.gap;

			// if gap not provided use endutc
			if( gap == '' ){				
				var Mnow = moment().utc();
				var M = moment();
				M.set('millisecond', OPT.endutc );

				gap = OPT.endutc - Mnow.unix();
			}
			
			// if negative gap
			if( gap < 0){
				return {
					'd': 0,
					'h':0,
					'm':0,
					's':0
				}; 
			}
		
			distance = ( gap * 1000);

			var days = Math.floor(distance / (1000 * 60 * 60 * 24));
			var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
			var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
			var seconds = Math.floor((distance % (1000 * 60)) / 1000);

			minutes = minutes<10? '0'+minutes : minutes;
			seconds = seconds<10? '0'+seconds : seconds;

			return {
				'd': days,
				'h':hours,
				'm':minutes,
				's':seconds
			}; 
		};
		$.fn.evo_countdown = function(opt){
			var defaults = { S1:''};
			var OPT = $.extend({}, defaults, opt);
			var el = $(this);

			const day_text = ( el.data('d') !== undefined && el.data('d') != '' )? el.data('d'):'Day';
			const days_text = ( el.data('ds') !== undefined && el.data('ds') != '' )? el.data('ds'):'Days';

			// intial run
			//var gap = parseInt(el.data('gap'));
			var duration = el.data('dur');
			var endutc = parseInt(el.data('endutc'));
			var text = el.data('t');
			
			if(text === undefined) text = '';

			if( el.hasClass('evo_cd_on')) return;

			// get gap using end time utc
				var Mnow = moment().utc();
				var M = moment();
				M.set('millisecond', OPT.endutc );

				gap = endutc - Mnow.unix();


			if( gap > 0 ){

				// initial
				dd = el.evo_countdown_get({ 'gap': gap });
				
				el.html( ( dd.d>0 ? dd.d + ' ' + ( dd.d >1 ? days_text: day_text ) + " "  :'') + dd.h + ":" + dd.m +':'+ dd.s +'  '+text );
				
				el.data('gap', ( gap - 1)  );	// save new gap value
				el.addClass('evo_cd_on');
				
				// set intervals
				var CD = setInterval(function(){
					
					gap = el.data('gap'); // get gap for this cycle
					duration = el.data('dur');	

					const bar_elm = el.closest('.evo_event_progress').find('.evo_ep_bar');						
					if( gap > 0 ){

						// increase bar width if exists
						if( duration !== undefined && bar_elm.length){
							perc = ( (duration - gap)/ duration ) * 100;
							bar_elm.find('b').css('width',perc+'%');							
						}
						
						dd = el.evo_countdown_get({ 'gap': gap });	

						el.html( ( dd.d>0 ? dd.d + ' '  + ( dd.d >1 ? days_text: day_text ) + " ":'') + dd.h + ":" + dd.m +':'+ dd.s +' '+text );
						el.data('gap', ( gap - 1)  );						

					}else{

						const expire_timer_action = el.data('exp_act');

						// perform ajax task after expiration
						if(expire_timer_action !== undefined){
							$('body').trigger('runajax_refresh_now_cal',[ 
								el , 
								el.data('n'),
							]);
						}

						const _complete_text = el.evo_get_txt({V:'event_completed'});

						// live now text
						if(bar_elm.length){
							bar_elm.addClass('evo_completed');
							//el.siblings('.evo_ep_pre').html( _complete_text );
						}


						// event tag & live elements
						if( el.closest('.evcal_desc').length){
							el.closest('.evcal_desc').find('.eventover').html( _complete_text);
							el.closest('.evcal_desc').find('.evo_live_now').remove();
						}

						// event card
						if( el.closest('.eventon_list_event').length){
							el.closest('.eventon_list_event').find('span.evo_live_now').hide();
						}

						el.html('');
						clearInterval(CD);
					}

				},1000);
			}else{
				clearInterval(CD);
			}
		};	
	
	// Handlebar process template data into html
		$.fn.evo_HB_process_template = function(opt){
			var defaults = { TD:'', part:''}
			var OPT = $.extend({}, defaults, opt);

			BUS = $('#evo_global_data').data('d');
						
			template = Handlebars.compile( BUS.temp[ OPT.part ] );
			return template( OPT.TD );
		}

	// Date range and events
		// Date range and events - from webpage
		$.fn.evo_cal_events_in_range = function(opt){
			var defaults = { S:'', E:'', 
				hide: true, 
				closeEC:true,
				showEV: false, // show events
				showEVL: false, // show events list
				showAllEvs: false // show all events regardless of the range
			};
			var OPT = $.extend({}, defaults, opt);
			var CAL = $(this);

			eJSON = CAL.find('.evo_cal_events').data('events');

			R = {};
			html = '';
			json = {};

			show = 0;

			// using events JSON data
			if( eJSON && eJSON.length > 0){
				$.each(eJSON, function(ind, ED){
					eO = CAL.find('#event_'+ ED._ID);
					if(eO === undefined || eO.length==0) return;

					if(OPT.hide)	eO.hide(); // pre hide
					this_show = false;

					// month long or year long events
					if( ED.month_long || ED.year_long ){
						this_show = true;

					}else{
						if(CAL.evo_is_in_range({
							'S': OPT.S,	'E': OPT.E,	'start': ED.event_start_unix ,	'end':ED.event_end_unix 
						})){						
							this_show = true;
						} 
					}

					if( OPT.showAllEvs) this_show = true;
					
					if( this_show){	
						// show event
						if( OPT.showEV) eO.show();

						// close open event cards
						if(OPT.closeEC && SC.evc_open == 'no') eO.find('.event_description').hide().removeClass('open');

						html += eO[0].outerHTML;
						json[ ED._ID] = ED;
						show++;	
					} 
				});
			}else{	
				// get all the events in the events list
				var cal_events = CAL.find('.eventon_list_event');

				cal_events.each(function(index, elm){
					var ED = $(elm).evo_cal_get_basic_eventdata();
					if( !ED) return;

					if(OPT.hide)	$(elm).hide(); // pre hide
					this_show = false;

					// month long or year long events
					if( $(elm).hasClass('month_long') || $(elm).hasClass('year_long') ){
						this_show = true;

					}else{
						if(CAL.evo_is_in_range({
							'S': OPT.S,	'E': OPT.E,	'start': ED.event_start_unix ,	'end':ED.event_end_unix 
						})){						
							this_show = true;
						} 
					}

					if( OPT.showAllEvs) this_show = true;
					
					if( this_show){	
						// show event
						if( OPT.showEV) $(elm).show();

						// close open event cards
						if(OPT.closeEC && SC.evc_open == 'no') 
							$(elm).find('.event_description').hide().removeClass('open');

						html += $(elm)[0].outerHTML;
						json[ ED.uID ] = ED;
						show++;	
					} 
				});
			}


			// No events
			if( OPT.showEV){

				no_event_content = CAL.evo_get_global({S1: 'html', S2:'no_events'});

				tx_noevents = CAL.evo_get_txt({V:'no_events'});
				EL = CAL.find('.eventon_events_list');
				EL.find('.eventon_list_event.no_events').remove();
				if( show == 0 )
					EL.append('<div class="eventon_list_event no_events">'+ no_event_content +'</div>');
			}

			// if show events list
			if( OPT.showEVL){
				CAL.find('.eventon_events_list').show().removeClass('evo_hide');
			}

			R['count'] = show;
			R['html'] = html;
			R['json'] = json;

			return R;
		}

	// check if an event is in the given date range
		$.fn.evo_is_in_range = function(opt){
			var defaults = { S:'', E:'', start:'',end:''}
			var OPT = $.extend({}, defaults, opt);

			S = parseInt(OPT.S);
			E = parseInt(OPT.E);
			start = parseInt(OPT.start);
			end = parseInt(OPT.end);

			return (
				( start <= S && end >= E ) ||
				( start <= S && end >= S && end <= E) ||
				( start <= E && end >= E ) ||
				( start >= S && end <= E )
			) ? true: false;
		}
		$.fn.evo_cal_hide_events = function(){
			CAL = $(this);
			CAL.find('.eventon_list_event').hide();
		}

	// get event data basics from html event on page 
	// ~@version 4.0.3
		$.fn.evo_cal_get_basic_eventdata = function(){
			var ELM = $(this);

			var _time = ELM.data('time');
			if( _time === undefined ) return false;

			const time = _time.split('-');
			const ri = ELM.data('ri').replace('r','');
			const eID = ELM.data('event_id');

			var _event_title = ELM.find('.evcal_event_title').text();
			_event_title = _event_title.replace(/'/g, '&apos;');


			var RR = {
				'uID': eID + '_' + ri,
				'ID': eID ,
				'event_id': eID ,
				'ri': ri,
				'event_start_unix': parseInt(time[0]),
				'event_end_unix': parseInt(time[1]),
				'ux_val': ELM.find('.evcal_list_a').data('ux_val'),
				'event_title': _event_title,
				'hex_color': ELM.data('colr'),
				'hide_et': ELM.hasClass('no_et') ? 'y':'n', // hide endtime
				'evcal_event_color': ELM.data('colr'),
				'unix_start': parseInt(time[0]),// @4.5.7
				'unix_end': parseInt(time[1]),// @4.5.7
			};

			// event type
			RR['ett1'] = {};
			ELM.find('.evoet_eventtypes.ett1 .evoetet_val').each(function(){
				RR['ett1'][ $(this).data('id')] = $(this).data('v');
			});

			// since 4.3.5
			const eventtop_data = ELM.find('.evoet_data').data('d');

			// location
			if(eventtop_data && 'loc.n' in eventtop_data && eventtop_data['loc.n'] != ''){
				RR['location'] = eventtop_data['loc.n'];
			}

			// organizer
			if(eventtop_data && 'orgs' in eventtop_data && eventtop_data.orgs !== undefined ){
				var org_names = '';
				$.each(eventtop_data.orgs, function(index, value){
					org_names += value +' ';
				});
				RR['organizer'] = org_names;
			}

			// event image
			if( ELM.find('.evo_event_schema').length > 0){
				imgObj = ELM.find('.evo_event_schema').find('meta[itemprop=image]').attr('content');
				RR['image_url'] = imgObj;
			}

			// event tags @s 4.5.2
			if( eventtop_data && 'tags' in eventtop_data && eventtop_data.tags !== undefined ){
				//console.log(eventtop_data.tags);
				RR['event_tags'] = eventtop_data.tags;
			}


			return RR;

		}



	// DATE time functions @+2.8		
		$.fn.evo_day_in_month = function(opt){
			var defaults = { M:'', Y:''}
			var OPT = $.extend({}, defaults, opt);

			return new Date(OPT.Y, OPT.M, 0).getDate();
		}
		$.fn.evo_get_day_name_index = function(opt){
			var defaults = { M:'', Y:'', D:''}
			var OPT = $.extend({}, defaults, opt);

			//return moment(OPT.Y+'-'+OPT.M+'-'+OPT.D).utc().day();

			return new Date(  Date.UTC(OPT.Y, OPT.M-1, OPT.D) ).getUTCDay();
		}

	// LIGHTBOX
	// page Lightbox functions @+2.8
	// append to the lightbox main class name .evo_lightbox
		
		// Legacy
			$.fn.evo_prepare_lb = function(){
				$(this).find('.evo_lightbox_body').html('');
			}
			$.fn.evo_show_lb = function(opt){
				var defaults = { RTL:'', calid:''}
				var OPT = $.extend({}, defaults, opt);

				$(this).addClass('show '+ OPT.RTL).attr('data-cal_id', OPT.calid);
				$('body').trigger('evolightbox_show');
			}
			$.fn.evo_append_lb = function(opt){
				var defaults = { C:'', CAL:''}
				var OPT = $.extend({}, defaults, opt);
				$(this).find('.evo_lightbox_body').html( OPT.C);

				if(  OPT.CAL!= '' && OPT.CAL !== undefined && OPT.CAL.hasClass('color')){
					const LIST = $(this).find('.eventon_events_list');
					if( LIST.length>0){
						LIST.find('.eventon_list_event').addClass('color');
					}				
				}
			}

		

	// Shortcodes
		// update shortcode values from filter changes
		$.fn.evo_update_sc_from_filters = function(){
			var ev_cal = $(this); 	
			SC = ev_cal.find('.evo_cal_data').data('sc');		

			var filter_section = ev_cal.find('.eventon_filter_line').first();
			
			filter_section.find('.eventon_filter').each(function(index){
				O = $(this);
				var filter_val = O.data('filter_val');	
				filter_val = filter_val == ''? 'NOT-all': filter_val;				

				ADD = (O.data('fl_o') && O.data('fl_o') != 'IN')? O.data('fl_o')+'-': '';
				SC[ O.data('filter_field') ] = ADD + filter_val;
			});	

			ev_cal.find('.evo_cal_data').data( 'sc', SC );
		}
		
		// get shortcodes from evo bottom
		$.fn.evo_shortcode_data = function(){			
			var ev_cal = $(this);
			return ev_cal.find('.evo_cal_data').data('sc');			
		}

		// get shortcode single value
		$.fn.evo_get_sc_val = function(opt){
			var defaults = {	F:''}
			var OPT = $.extend({}, defaults, opt);
			var ev_cal = $(this); 

			if(OPT.F=='') return false;
			SC = ev_cal.find('.evo_cal_data').data('sc');

			if(!(SC[ OPT.F])) return false;
			return SC[ OPT.F];
		}
		// UPDATE Single value
		$.fn.evo_update_cal_sc = function(opt){
			var defaults = {
				F:'', V:''
			}
			var OPT = $.extend({}, defaults, opt);
			var ev_cal = $(this); 
			SC = ev_cal.find('.evo_cal_data').data('sc');

			SC[ OPT.F ] = OPT.V;

			ev_cal.find('.evo_cal_data').data( 'sc', SC );
		}
		// UPDATE all shortcode values
		$.fn.evo_update_all_cal_sc = function(opt){
			var defaults = {SC:''}
			var OPT = $.extend({}, defaults, opt);
			var CAL = $(this);
			CAL.find('.evo_cal_data').data( 'sc', OPT.SC );
		}

	// OTHERS
		// hex colors // @+2.8
		$.fn.evo_is_hex_dark = function(opt){
			var defaults = { hex:'808080'}
			var OPT = $.extend({}, defaults, opt);

			hex = OPT.hex;

			var c = hex.replace('#','');
			var is_hex = typeof c === 'string' && c.length === 6 && !isNaN(Number('0x' + c));

			if(is_hex){	
				var values = c.split('');
				r = parseInt(values[0].toString() + values[1].toString(), 16);
			    g = parseInt(values[2].toString() + values[3].toString(), 16);
			    b = parseInt(values[4].toString() + values[5].toString(), 16);
			}else{
				var vals = c.substring(c.indexOf('(') +1, c.length -1).split(', ');
				var r = vals[0]  // extract red
				var g = vals[1];  // extract green
				var b = vals[2];
			}

			var luma = ((r * 299) + (g * 587) + (b * 114)) / 1000; // per ITU-R BT.709

			return luma>155? true:false;
		}

		// get rgb from hex code @4.5
			$.fn.evo_rgb_process = function(opt){
				var defaults = { data:'808080',type:'rgb', method:'rgb_to_val'}
				var opt = $.extend({}, defaults, opt);

				const color = opt.data;

				// RGB => hex
				if( opt.method == 'rgb_to_hex'){
					if(color == '1'){
						return;
					}else{
						if(color !=='' && color){
							rgb = color.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
							
							return "#" +
							("0" + parseInt(rgb[1],10).toString(16)).slice(-2) +
							("0" + parseInt(rgb[2],10).toString(16)).slice(-2) +
							("0" + parseInt(rgb[3],10).toString(16)).slice(-2);
						}
					}
				}

				// RGB => rgb val
				if( opt.method == 'rgb_to_val'){
					if( opt.type == 'hex'){
						var rgba = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(color);	
						var rgb = new Array();
						rgb['r']= parseInt(rgba[1], 16);			
						rgb['g']= parseInt(rgba[2], 16);			
						rgb['b']= parseInt(rgba[3], 16);
						
					}else{
						rgb = color;
					}
					
					return parseInt((rgb['r'] + rgb['g'] + rgb['b'])/3);
				}				
			}
			
		// Other data
			$.fn.evo_get_OD = function(){			
				var ev_cal = $(this);
				return ev_cal.find('.evo_cal_data').data('od');			
			}

	// return all filter values for given calendar -- DEP 2.8
		$.fn.evoGetFilters = function(){

			var ev_cal = $(this); 
			var evodata = ev_cal.find('.evo-data');
			
			var filters_on = ( evodata.attr('data-filters_on')=='true')?'true':'false';
			
			// creat the filtering data array if exist
			if(filters_on =='true'){
				var filter_section = ev_cal.find('.eventon_filter_line');
				var filter_array = [];
				

				filter_section.find('.eventon_filter').each(function(index){
					var filter_val = $(this).attr('data-filter_val');
					
					if(filter_val !='all'){
						var filter_ar = {};
						filter_ar['filter_type'] = $(this).attr('data-filter_type');
						filter_ar['filter_name'] = $(this).attr('data-filter_field');

						if($(this).attr('data-fl_o')=='NOT'){
							filter_ar['filter_op']='NOT IN';
						}
						filter_ar['filter_val'] = filter_val;
						filter_array.push(filter_ar);
					}
				});		
			}else{
				var filter_array ='';
			}			
			return filter_array;
		}
		

		// get evo data for a given calendar
		$.fn.evo_getevodata = function(){

			var ev_cal = $(this);
			var evoData = {};
			
			ev_cal.find('.evo-data').each(function(){
				$.each(this.attributes, function(i, attrib){
					var name = attrib.name;
					if(attrib.name!='class' && attrib.name!='style' ){
						name__ = attrib.name.split('-');
						evoData[name__[1]] = attrib.value;	
					}
				});
			});	

			return evoData;
		}

	// eventon loading functions
	// v 2.4.5
		$.fn.evo_loader_animation = function(opt){
			var defaults = {
				direction:'start'
			}
			var OPT = $.extend({}, defaults, opt);

			if(OPT.direction == 'start'){
				$(this).find('#eventon_loadbar').slideDown();
			}else{
				$(this).find('#eventon_loadbar').slideUp();
			}
		}

	// DEPRECATED functions
		$.fn.evo_item_shortcodes = function(){			
			var OBJ = $(this);
			var shortcode_array ={};			
			OBJ.each(function(){	
				$.each(this.attributes, function(i, attrib){
					var name = attrib.name;
					if(attrib.name!='class' && attrib.name!='style' && attrib.value !=''){
						name__ = attrib.name.split('-');
						shortcode_array[name__[1]] = attrib.value;	
					}
				});
			});
			return shortcode_array;
		}
		$.fn.evo_shortcodes = function(){			
			var ev_cal = $(this);
			var shortcode_array ={};
					
			ev_cal.find('.cal_arguments').each(function(){
				$.each(this.attributes, function(i, attrib){
					var name = attrib.name;
					if(attrib.name!='class' && attrib.name!='style' && attrib.value !=''){
						name__ = attrib.name.split('-');
						shortcode_array[name__[1]] = attrib.value;	
					}
				});
			});	
			return shortcode_array;
		}
		

}(jQuery));