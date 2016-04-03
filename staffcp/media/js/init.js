function setvalueajax(table,field,indexid,id){
	if ($("#"+field+"_checkbox_"+id).is(":checked")){
		sendsetvalue(table,field,1,indexid,id);
	}
	else {
		sendsetvalue(table,field,0,indexid,id);
	}
}
function sendsetvalue(table,field,value,indexid,id){
	$.post('/staffcp/index/setvalueajax/?table='+table+'&field='+field+'&value='+value+'&indexid='+indexid+'&id='+id, null,
	function(data) {
		$("#"+field+"_checkbox_"+id+"_result").html('<img style="vertical-align:middle;" src="/staffcp/media/images/checkbox'+value+'.gif" >'+data);
	});
}
function checkmail(email) {
	var reg = /^[a-z0-9._-]+@[a-z0-9.-]{2,}[.][a-z]{2,4}$/
	return (reg.exec(email)!=null)
}
function checked_all(){
	var status = $('.status_check').attr("checked");
	if (status) { $('.input_check').attr("checked","checked"); }
	else { $('.input_check').removeAttr("checked"); }
}
$(document).ready(function() {
	$('#tabs').tabs();
	$('.cms-list tr:odd').addClass('odd');
});
$(document).ready(function() {	
	$('a[name=modal]').click(function(e) {
		e.preventDefault();
		var id = $(this).attr('href');
		var maskHeight = $(document).height();
		var maskWidth = $(window).width();
		$('#mask').css({'width':maskWidth,'height':maskHeight});
		$('#mask').fadeTo("fast",0.6);	
		var winH = $(window).height();
		var winW = $(window).width();
		$(id).css('top',  winH/2-$(id).height()/2);
		$(id).css('left', winW/2-$(id).width()/2);
		$(id).fadeIn(300); 
	});
	$('.window_drop .close_drop').click(function (e) {
		e.preventDefault();
		$('#mask').hide();
		$('.window_drop').hide();
	});		
	$('#mask').click(function () {
		$(this).hide();
		$('.window_drop').hide();
	});			
});

function setchk(st){
	if ($(st).is(':checked')){
		$('.setchk').attr('checked','checked');
	} else {
		$('.setchk').removeAttr('checked');
	}
}