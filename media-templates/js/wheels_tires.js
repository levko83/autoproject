var ABSOLUTE_URL_TO_AJAX_FILE_TIRE = '/wheels_tires/ajax/';

function get_car_info_tire(select, what_get){
	
	if(what_get == 'get_results'){
		
		var id_auto_brand			= $("#list_of_auto_brands option:selected").val();
		var id_auto_model			= $("#list_of_auto_models option:selected").val();
		var id_auto_year			= $("#list_of_auto_years option:selected").val();
		var id_auto_modification	= $("#list_of_auto_modifications option:selected").val();
		var value_auto_brand        = $("#list_of_auto_brands option:selected").text();
		var value_auto_model		= $("#list_of_auto_models option:selected").text();
		var value_auto_year			= $("#list_of_auto_years option:selected").text();
		var value_auto_modification	= $("#list_of_auto_modifications option:selected").text();
		$('#wt_step4').show();
		ajax(ABSOLUTE_URL_TO_AJAX_FILE_TIRE+'?what_get=get_results&id_auto_brand='+id_auto_brand+'&id_auto_model='+id_auto_model+'&id_auto_year='+id_auto_year+'&id_auto_modification='+id_auto_modification+'&value_auto_brand='+value_auto_brand+'&value_auto_model='+value_auto_model+'&value_auto_year='+value_auto_year+'&value_auto_modification='+value_auto_modification, function (answer){
			filter_tires_results.innerHTML = answer;
			$('#wt_step4').hide();
		});
		
	} else {
		
		if(what_get == 'get_list_of_auto_models'){
			$('#wt_step1').show();
		}
		if(what_get == 'get_list_of_auto_years'){
			$('#wt_step2').show();
		}
		if(what_get == 'get_list_of_auto_modifications'){
			$('#wt_step3').show();
		}
		
		ajax(ABSOLUTE_URL_TO_AJAX_FILE_TIRE+'?what_get='+what_get+'&filter_parameter='+select[select.selectedIndex].value, function (answer){
			if(what_get == 'get_list_of_auto_models'){
				$('#wt_models').html(answer);
				$('#wt_step1').hide();
			}
			if(what_get == 'get_list_of_auto_years'){
				$('#wt_years').html(answer);
				$('#wt_step2').hide();
			}
			if(what_get == 'get_list_of_auto_modifications'){
				$('#wt_engines').html(answer);
				$('#wt_step3').hide();
			}
		});
		
	}
}

function get_car_info_tire_index(select, what_get){
	
	if(what_get == 'get_list_of_auto_models'){
		$('#wt_step1').show();
	}
	if(what_get == 'get_list_of_auto_years'){
		$('#wt_step2').show();
	}
	if(what_get == 'get_list_of_auto_modifications'){
		$('#wt_step3').show();
	}
	ajax(ABSOLUTE_URL_TO_AJAX_FILE_TIRE+'?index=index&what_get='+what_get+'&filter_parameter='+select[select.selectedIndex].value, function (answer){
		if(what_get == 'get_list_of_auto_models'){
			$('#wt_models').html(answer);
			$('#wt_step1').hide();
		}
		if(what_get == 'get_list_of_auto_years'){
			$('#wt_years').html(answer);
			$('#wt_step2').hide();
		}
		if(what_get == 'get_list_of_auto_modifications'){
			$('#wt_engines').html(answer);
			$('#wt_step3').hide();
		}
	});
}

function facechange (objName) {
	if ($(objName).css('display') == 'none'){
		$(objName).animate({height: 'show'}, 400);
	}else{
		$(objName).animate({height: 'hide'}, 200);
	}
}

function get_url_vars() {
	var vars = {};
	var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
		vars[key] = value;
	});
	return vars;
}

function ajax(url, callbackFunction){
	this.bindFunction = function (caller, object){
		return function(){
			return caller.apply(object, [object]);
		};
	};
	this.stateChange = function (object){
		if (this.request.readyState==4)
			this.callbackFunction(this.request.responseText);
	};
	this.getRequest = function(){
		if (window.ActiveXObject)
			return new ActiveXObject('Microsoft.XMLHTTP');
		else if (window.XMLHttpRequest)
			return new XMLHttpRequest();
		return false;
	};
	this.postBody = (arguments[2] || "");
	this.callbackFunction=callbackFunction;
	this.url=url;
	this.request = this.getRequest();
	if(this.request){
		var req = this.request;
		req.onreadystatechange = this.bindFunction(this.stateChange, this);
		if (this.postBody!==""){
			req.open("POST", url, true);
			req.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
			req.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			req.setRequestHeader('Connection', 'close');
		}else{
			req.open("GET", url, true);
		}
		req.send(this.postBody);
	}
}
