jQuery(document).ready(function(){
	
	$(".chosen-select").chosen({no_results_text: "Oops, nothing found!"});
	/*$("h1,h2").addClass("animated fadeInRight");*/
	$('#index-products').jcarousel({
		auto:0,scroll:1,wrap:'circular',animation:3000
	});
	
	$("#setSortSearch2").click(function() {
		$('.info-notice').hide();
		$('.sort-by-module2').toggle(); return false; 
	});
	
	$("#setSortCurrency").click(function() {
		$('.info-notice').hide();
		$('.sort-by-module3').toggle(); return false; 
	});
	
	$("a[rel=lightbox]").fancybox();
	$('#overlayOut').fadeOut(100);
	
	$('.header .links li').each(function(index){jQuery(this).addClass('link-'+(index+1));});
	$("a[rel=lightbox]").fancybox({'transitionIn':'none','transitionOut':'none','titlePosition':'over','titleFormat':function(title, currentArray, currentIndex, currentOpts) {return '<span id="fancybox-title-over">' + (currentIndex + 1) + ' / ' + currentArray.length + (title.length ? ' &nbsp; ' + title : '') + '</span>';}});
	$(".fancy_inline").fancybox({'titlePosition':'inside','transitionIn':'none','transitionOut':'none'});
	$(".pChart").fancybox();
	
	$(".iframe").fancybox({'width':'75%','height':'75%','autoScale':false,'transitionIn':'none','transitionOut':'none','type':'iframe','top':'100px'});
	$(".iframestatic300").fancybox({'width':'300px','autoScale':false,'transitionIn':'none','transitionOut':'none','type':'iframe','top':'100px'});
	
	$('#list-offices').on('click',function(){
		$('.info-notice').hide();
		$('#listoffices').toggle();
		return false;
	});
	
	$('#city-disagree, #choise-office').click(function(){
		$.get('/office/city_disagree/', null,
		function(data) {
			$('#choise-office').parent().hide();
		});
		return false;
	});
	
	/* keyup mouseup mouseover mouseout mousemove mousereave mouseenter */
	$('.i-cart-item').on("keyup mouseup mouseover mouseout mousemove mousereave mouseenter", function(){
		var max = $(this).attr('max');
		var setval = $(this).val();
		if (setval == ''){ // если пусто то делаем -1 для того чтоб пользователь успел изменить число
			setval = -1;
		}
		max = parseInt(max);
		setval = parseInt(setval);
		if (setval == -1){
			setval = '';
		}
		if (setval > max){
			$(this).val(max);
		} else {
			
			$(this).val(setval);
		}
	});

});

function sg(id){
	$('.saved_groups').css('height','auto'); 
	$(id).remove();
}

function addnote(article,brand,descr,url,account_id,noteid){
	$.get('/search_notepad/addnote/?article='+article+'&brand='+brand+'&descr='+descr+'&url='+url+'&account_id='+account_id, null,
	function(data) {
		$('#note-'+noteid).html('<img src="/media-templates/icons/lightbulb.png" alt="В блокнот" title="В блокнот">');
	});
}
function unsetnote(id,noteid){
	$.get('/search_notepad/unsetnote/?id='+id, null,
	function(data) {
		$('#note-'+noteid).html('<img src="/media-templates/icons/lightbulb_off.png" alt="В блокнот" title="В блокнот">');
	});
}

function looksorting(url,sort){
	var refresh = location.hash;
	if (refresh){
		$("#ajax_load_search_data").html('');
		$('#ajax_indicator').show();
		if(refresh.indexOf('search/number') + 1) {
			var url = refresh.replace('#','')+'&ajax=true&sort='+sort+'&cached=1';
			$.get(url, null,
			function(data) {				
				$('#ajax_indicator').hide();
				$("#ajax_load_search_data").html(data);
			});
		}
		else if(refresh.indexOf('search/details') + 1){
			var url = refresh.replace('#','')+'&ajax=true&sort='+sort+'&cached=1';
			$.get(url, null,
			function(data) {				
				$('#ajax_indicator').hide();
				$("#ajax_load_search_data").html(data);
			});
		}
	}
	else {
		looksortingcatalog(url,sort);
	}
}

function looksortingcatalog(url,sort){
	if (url){
		$("#ajax_load_search_data").html('');
		$('#ajax_indicator').show();
		var url = url+'&sort='+sort+'&cached=1';
		location = url;
	}
}

function group_result_search(item,key){
	$('.'+key+'_toggle').toggle();
	return false;
}

function lightboxView(i){
	$("a[rel=lightbox_"+i+"]").fancybox({'transitionIn':'none','transitionOut':'none','titlePosition':'over','titleFormat':function(title, currentArray, currentIndex, currentOpts) {return '<span id="fancybox-title-over">' + (currentIndex + 1) + ' / ' + currentArray.length + (title.length ? ' &nbsp; ' + title : '') + '</span>';}
	});
}

function cart(item_id,id,type,price,purchase,descr,article,brand,min,timedelivery) {
	
	var cart_item_min = $('#cart_item_min_'+item_id).val();
	var cart_item = $('#cart_item_'+item_id).val();
	
	if (cart_item%cart_item_min) {
		alert('Внимание! Заказ возможен только с минимальной кратностью: '+cart_item_min+'');
	} else {
		
		$('#cart-next').show();
		
		cc = cart_item;
		if (!cart_item)
			cc = 1;
			
		if (!timedelivery)
			timedelivery = 0;
		
		$.post('/cart/add?id='+id+'&ccount='+cc+'&type='+type+'&price='+price+'&purchase='+purchase+'&descr='+descr+'&article='+article+'&brand='+brand+'&min='+min+'&timedelivery='+timedelivery, null,
		function(xbox) {
			$.get('/cart/totalsum', null,
			function(xboxtotalsum) {
				$(".xbox-cart").html(xbox);
				$(".xbox-cart-totalsum").html(xboxtotalsum);
				$("#go-buy-cart").show();
			});
		});
	}
}

function models(id) {
	$('#ajax_MOD_ID').html('пожалуйста, подождите...');
	$.post('/search/get_list_models/?id='+id+'&ajax=true', null,
	function(data) {
		$("#ajax_MOD_ID").html(data);
	});
}

function noticecart(id){
	$('#cart-ajax-'+id).show();
	$('#cart-ajax-'+id).addClass("animated tada");
	setTimeout('alertAjax('+id+')',1000);
}

function alertAjax(id){
	$('#cart-ajax-'+id).hide();
}

function mftab(obj,id){
	$('.mftabs > div.my-tab').hide();
	$('.tabNavigation>li>a').removeClass('selected');
	$('#'+id).show();
	$(obj).addClass('selected');
	$('#u'+id).addClass('selected');
}

$(document).ready(function () {
	$('.croll').click(function (e) {
		e.preventDefault();
		var el = $(this);
		roll(el);
	})
});

function roll(el) {
	$(el).parent().parent().find('.slide-info').slideToggle(1200,function(){
		/*$('html, body').animate({
		 scrollTop: $(this).offset().top
		 }, 200);*/
	});
	el.toggleClass('roll');
}

$(window).load(function() {
	$('#marketing-slider').nivoSlider({directionNav:false,controlNav:false});
});