function get_list_of_battery_auto_brands(get_data_from_form){
	$('#wt_step1').show();
    var auto_type_vehicle = jQuery('#list_of_battery_auto_type_vehicles').val();

	    var url = '/batteries/?action=get_list_of_auto_brands&auto_type_vehicle='+auto_type_vehicle;
	    if (get_data_from_form == 1){
	        url = url + '&postajax'; 
	    }
    
    ajax(url, function (answer){
		$('#result_battery_step1').html(answer);
    	$(".chosen-select").chosen({no_results_text: "Oops, nothing found!"});
    	$('#wt_step1').hide();
	});
}
function get_list_of_battery_auto_models(get_data_from_form){
	$('#wt_step2').show();
    var auto_type_vehicle = jQuery('#list_of_battery_auto_type_vehicles').val();
    var auto_brand = jQuery('#list_of_battery_auto_brands').val();

    	var url = '/batteries/?action=get_list_of_auto_models&auto_type_vehicle='+auto_type_vehicle+'&auto_brand='+auto_brand;
    	if (get_data_from_form == 1){
            url = url + '&postajax'; 
        }
        
    ajax(url, function (answer){
		$('#result_battery_step2').html(answer);
    	$(".chosen-select").chosen({no_results_text: "Oops, nothing found!"});
    	$('#wt_step2').hide();
	});
}
function get_list_of_battery_auto_years(get_data_from_form){
	$('#wt_step3').show();
    var auto_type_vehicle = jQuery('#list_of_battery_auto_type_vehicles').val();
    var auto_brand = jQuery('#list_of_battery_auto_brands').val();
    var auto_model = jQuery('#list_of_battery_auto_models').val();

    	var url = '/batteries/?action=get_list_of_auto_years&auto_type_vehicle='+auto_type_vehicle+'&auto_brand='+auto_brand+'&auto_model='+auto_model;
    	if (get_data_from_form == 1){
	        url = url + '&postajax'; 
	    }
    
    ajax(url, function (answer){
		$('#result_battery_step3').html(answer);
    	$(".chosen-select").chosen({no_results_text: "Oops, nothing found!"});
    	$('#wt_step3').hide();
	});
}
function get_list_of_battery_auto_modifications(get_data_from_form){
	$('#wt_step4').show();
    var auto_type_vehicle = jQuery('#list_of_battery_auto_type_vehicles').val();
    var auto_brand = jQuery('#list_of_battery_auto_brands').val();
    var auto_model = jQuery('#list_of_battery_auto_models').val();
    var auto_year = jQuery('#list_of_battery_auto_years').val();

    	var url = '/batteries/?action=get_list_of_auto_modifications&auto_type_vehicle='+auto_type_vehicle+'&auto_brand='+auto_brand+'&auto_model='+auto_model+'&auto_year='+auto_year;
    	if (get_data_from_form == 1){
	        url = url + '&postajax'; 
	    }
    
    ajax(url, function (answer){
		$('#result_battery_step4').html(answer);
    	$(".chosen-select").chosen({no_results_text: "Oops, nothing found!"});
    	$('#wt_step4').hide();
	});
}
function get_battery_results(get_data_from_form){
	$('#wt_step5').show();

	var auto_type_vehicle = jQuery('#list_of_battery_auto_type_vehicles').val();
	var auto_brand = jQuery('#list_of_battery_auto_brands').val();
	var auto_model = jQuery('#list_of_battery_auto_models').val();
	var auto_year = jQuery('#list_of_battery_auto_years').val();
	var auto_modification = jQuery('#list_of_battery_auto_modifications').val();
	var url = '/batteries/?action=get_results&auto_type_vehicle='+auto_type_vehicle+'&auto_brand='+auto_brand+'&auto_model='+auto_model+'&auto_year='+auto_year+'&auto_modification='+auto_modification;

	if (get_data_from_form == 1){
		location = url+'&post=true';
	}
	
    jQuery.get(url, function(contents) {
        jQuery('#bc_filter_batteries_results').html(contents);
        $('#wt_step5').hide();
    })
}