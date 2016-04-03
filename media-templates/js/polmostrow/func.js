function okno (theURL,wx,wy){ 
	mx = Math.round((screen.width-wx)/2);
	tab = window.open(theURL,"tab","width="+wx+",height="+wy+",toolbar=no,directories=no,scrollbars=yes,location=no,resizable=no,menubar=no,left="+mx+",top=10").focus();
}

var hl_old_class = '';
var hl_actual = null;

function hl_show(row,klasa){
	if (hl_actual  != null) {
		hl_actual.className = hl_old_class; hl_actual = null;
	}
	hl_actual = row;
	hl_old_class = row.className;
	row.className = klasa;
}

function hl_hidde(){
	if (hl_actual  != null) {
		hl_actual.className = hl_old_class; hl_actual = null;
	}
}

function podmienRys(nazwa, obrazek) {
	tmp = eval('document.' + nazwa);
	tmp.src = obrazek;
}

function AjaxKatalogSend(datasend) {
	$.ajax({
		type: "POST",
		url:  "/polmostrow/getajax/",
		dataType:   'html',
		data: datasend,
		success: function(msg) { $("#divkatalog").html(msg); },
		error: function (err) {}
	});	
}

function AjaxKatalogSzukaj(datasend) {	
	alert(datasend);
	$.ajax({
		type: "POST",
		url:  "/polmostrow/getajax/",
		dataType:   'html',
		data: datasend, 
		success: function(msg) { 	$("#divkatalog").html(msg); },
		error: function (err) {}
	});	
}

function AjaxPostSend(setdata,datasend,options) {	
	$.ajax({
		type: setdata.typesend,
		url:  setdata.file,
		dataType:   'html',
		data: datasend, 
		success: function(msg) { $(options.divout).html(msg); },
		error: function (err) {}
	});	
}

function RegAjaxPost(data,options) {	
	$.ajax({
		type: options.typesend,
		url:  options.file,
		dataType:   'html',
		data: data, 
		success: function(msg) { $(options.divaout).html(msg); },
		error: function () {}
	});	
}		  

function show_div_id(cons,val,showdiv,hidediv) { 
	if(cons == val){
		$("#"+showdiv).show();
		$("#"+hidediv).hide();
	}else{
		$("#"+showdiv).hide();
		$("#"+hidediv).show();
	}
}
	
function WriteInput(input,string)
{
	if(input.value == string){
		return input.value = '';
	}else if(input.value == ''){
		return input.value = string;	
	}	
}

function doublelicz(x){
	x.value=x.value.replace(/\D/g,'')
}

function  liczby(x){
	var x;	
	x.value = x.value.replace(',','.');
	x.value = x.value.replace(/[^\d\.\,]+/g, '')
}

function show_hiden_div(identyfikator,stan) { 
	if(stan == "h"){
		$("#" +identyfikator).hide();
	}else{
		$("#" + identyfikator).show();
	}
}

function showhiden(identyfikator) { 
	if(document.getElementById(identyfikator).style.display == "none"){
		document.getElementById(identyfikator).style.display = "inline";
	}else{
		document.getElementById(identyfikator).style.display = "none";
	}
}

function email_validate(src){
	var regex = /^[a-zA-Z0-9._-]+@([a-zA-Z0-9.-]+\.)+[a-zA-Z0-9.-]{2,4}$/;
	return regex.test(src);
}

function error_nul(element) {
	document.getElementById(element).className = 'error_null';
}

function ok_elemnt(element) {
	document.getElementById(element).className = 'inputform';
}