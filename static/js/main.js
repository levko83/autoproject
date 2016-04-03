jQuery(document).ready(function(){
	if (typeof(tabs_cur) != "undefined"){
		jQuery(".tab").hide();
		jQuery("#type-"+tabs_cur).show();
		jQuery(".htabs a").click(function(){
			stringref = jQuery(this).attr("href").split('#')[1];
			jQuery('.tab:not(#'+stringref+')').hide();
			if (jQuery.browser.msie && jQuery.browser.version.substr(0,3) == "6.0") {
				jQuery('.tab#'+stringref).show();
			}
			else {
				jQuery('.tab#'+stringref).fadeIn();
			}
			return false;
		});
	}
	if (typeof(atree) != "undefined"){
		$("#auto-searcher").focus().autocomplete(atree, {
			matchContains: true,
			minChars: 0
		});
		$("#auto-searcher").result(treeviewer);
	}
	if (typeof(reg_page) != "undefined"){
		$('input:radio[name="form[is_firm]"]').change(
		function(){
			if ($(this).is(':checked') && $(this).val() == '1') {
				$("#firm_name").css("display", "block");
				$("#firm_empty").css("display", "block");
			} else {
				$("#firm_name").css("display", "none");
				$("#firm_empty").css("display", "none");
			}
		});
	}
	
	if (typeof(search_article) != "undefined"){
		jQuery(document).ready(function(){
			$(window).bind('hashchange', function() {
				if(location.hash == ''){
					lload();
				}
			});
			var refresh = location.hash;
			if (refresh){
				if(refresh.indexOf('search/number') + 1) {
					var url = refresh.replace('#','')+'&ajax=true';
					$.get(url, null,
					function(data) {				
						$('#ajax_indicator').hide();
						$("#ajax_load_search_data").html(data);
					});
				}
				else lload();
			}
			else lload();
		});
		function lload(){
			$('#ajax_indicator').show();
			$("#ajax_load_search_data").html('');
			$.get('/search/artlookup/?ajax=true&article='+search_article, null,
			function(data) {
				$('#ajax_indicator').hide();
				$("#ajax_load_search_data").html(data);
			});
		}
	}
});

function set_pp(art){
	// alert(art);
	var price = $("#set_price_"+art).val();
	var cc = $("#count_"+art).val();
	var cc = $("#count_"+art).val();
	var price = Math.round(cc*price).toFixed(2);
	$("#new_pp_"+art).html(price);
	// $("#new_pp_"+art).html(cc*price);
	$(document).load("/cart/refresh_id/?id="+art+"&count="+cc);
	set_sum();
}

function check_this_form(step) {
	
}
/*
$('a').click(function(){
    $('html, body').animate({
        scrollTop: $( $.attr(this, 'href') ).offset().top
    }, 500);
    return false;
});

*/
$('#open_shopping_cart').click(function() {
    $.ajax({
        url: '/cart/cart_window/',
        type: 'GET',
        success: function(res) {
            $("#cart_data").html(res);
        }
    });
});

function del_prod(id) {
	if (typeof(delete_info) != "undefined"){
		if (confirm(delete_info)) {
			
			$.get("/cart/delete_prod/", { id: id },
				function(data){
					// alert("Data Loaded: " + data);
				}
			);
			
			$('body').on('click.close_button', '.close', function() {
				$(this).parent().animate({
					opacity: 0
				}, function() {
					var $this = $(this),
						ISSC = $this.closest('.shopping_cart').length,
						collection = $this.parent().index() != 0 && ISSC ?
						$this.add($this.parent()) : $this;
					collection.slideUp(function() {
						if (!ISSC) return;
						var parent = $(this).closest('.shopping_cart'),
							len;
						$(this).remove();
						len = parent.find('.animated_item').length;
						parent.data("len", len);
						Core.mainAnimation.defineNewState(parent, true);
					});

				});

			});
			
			$.get('/cart/totalsum', null,
			function(xboxtotalsum) { 
				$(".total_price").html("&euro;" + xboxtotalsum);
			});
			
			$.get('/cart/total_prod', null,
			function(xboxtotalsum) { 
				$("#open_shopping_cart").attr("data-amount",xboxtotalsum);
			});
			
		}
	} 
}
$('.deliveryblock input[type="radio"]').on('change', function(e) {
    // console.log($( this ).val());
    // console.log($( this ).attr("data-price"));
	var dataprice = $( this ).attr("data-price");
		//alert(parseFloat(dataprice));
	var dataprice1 = $( this ).attr("data-free");
	var total = $('#total').text();
	//	alert(parseFloat(total)+' '+parseFloat(dataprice1)+' '+parseFloat(dataprice));
	if(parseFloat(total)>=(dataprice1)){
	//	alert("dfgdf");
		dataprice = 0.00;
	}
	var total_sum = parseFloat(total) + parseFloat(dataprice);
	total_sum = parseFloat(total_sum).toFixed(2);
	$('#total_delivery').text(dataprice);
	$('#grandtotal').text(total_sum);
//	alert(parseFloat(total_sum)+' '+parseFloat(total)+' '+parseFloat(dataprice));
});
$('.plati input[type="radio"]').on('change', function(e) {
    // console.log($( this ).val());
    // console.log($( this ).attr("data-price"));
	var dataprice = $( this ).attr("data-price");
	var total = $('#total').text();
		var total_delivery = $('#total_delivery').text();
	var total_sum = parseFloat(total) + parseFloat(dataprice)+ parseFloat(total_delivery);
		total_sum = parseFloat(total_sum).toFixed(2);
	$('#grandtotal').text(total_sum);
	$('#paymenttotal').text(dataprice);
	//alert(total_sum+' '+parseFloat(total)+' '+parseFloat(dataprice));
});
function ctab(id){
	jQuery('.htabs li').removeClass('cur');
	jQuery('#li'+id).addClass('cur');
}
function get_mods(id) {
    $.post('/search/searchGetMods/?car_id=' + id, null,
        function(data) {
            $('#carmodel').html(data);
			if (typeof(objname) != "undefined"){
				$('#carvolume').html('<select id="carob" class="form-control"> <option>'+objname+'</option> </select>');
			} else {
				$('#carvolume').html('<select id="carob" class="form-control"> <option>Typ wählen</option> </select>');
			}
        });
}
function isEmpty(str) {
    return (!str || 0 === str.length);
}
function zusubmit(){
			var zu2 = $("input[name='zu2']").val();
			var zu3 = $("input[name='zu3']").val();
			if (!isEmpty(zu2) && !isEmpty(zu3)) location = '/search/getnr/?ZU2='+zu2+'&ZU3='+zu3;
			else alert("Wählen Sie Ihre Automarke oder geben Sie die Schlüsselnummer ein");
		}
function get_volumes(mod_id) {
    $.post('/search/searchGetTypes/?mod_id=' + mod_id, null,
        function(data) {
            $('#carvolume').html(data);
        }); 
}
function gotocatalog(type){
	var Base64={_keyStr:"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",encode:function(e){var t="";var n,r,i,s,o,u,a;var f=0;e=Base64._utf8_encode(e);while(f<e.length){n=e.charCodeAt(f++);r=e.charCodeAt(f++);i=e.charCodeAt(f++);s=n>>2;o=(n&3)<<4|r>>4;u=(r&15)<<2|i>>6;a=i&63;if(isNaN(r)){u=a=64}else if(isNaN(i)){a=64}t=t+this._keyStr.charAt(s)+this._keyStr.charAt(o)+this._keyStr.charAt(u)+this._keyStr.charAt(a)}return t},decode:function(e){var t="";var n,r,i;var s,o,u,a;var f=0;e=e.replace(/[^A-Za-z0-9\+\/\=]/g,"");while(f<e.length){s=this._keyStr.indexOf(e.charAt(f++));o=this._keyStr.indexOf(e.charAt(f++));u=this._keyStr.indexOf(e.charAt(f++));a=this._keyStr.indexOf(e.charAt(f++));n=s<<2|o>>4;r=(o&15)<<4|u>>2;i=(u&3)<<6|a;t=t+String.fromCharCode(n);if(u!=64){t=t+String.fromCharCode(r)}if(a!=64){t=t+String.fromCharCode(i)}}t=Base64._utf8_decode(t);return t},_utf8_encode:function(e){e=e.replace(/\r\n/g,"\n");var t="";for(var n=0;n<e.length;n++){var r=e.charCodeAt(n);if(r<128){t+=String.fromCharCode(r)}else if(r>127&&r<2048){t+=String.fromCharCode(r>>6|192);t+=String.fromCharCode(r&63|128)}else{t+=String.fromCharCode(r>>12|224);t+=String.fromCharCode(r>>6&63|128);t+=String.fromCharCode(r&63|128)}}return t},_utf8_decode:function(e){var t="";var n=0;var r=c1=c2=0;while(n<e.length){r=e.charCodeAt(n);if(r<128){t+=String.fromCharCode(r);n++}else if(r>191&&r<224){c2=e.charCodeAt(n+1);t+=String.fromCharCode((r&31)<<6|c2&63);n+=2}else{c2=e.charCodeAt(n+1);c3=e.charCodeAt(n+2);t+=String.fromCharCode((r&15)<<12|(c2&63)<<6|c3&63);n+=3}}return t}}
	var Gmark = $('#scar').val();
	var Gmodification = $('#carmodell').val();
	var Gmodification = Base64.decode(Gmodification);
	var Gmodification = Gmodification.split('$$');
	var Gmodification = Gmodification[0];
	var Gengine = $('#carob').val();
	window.location = '/auto/' + Gmark + '/' + Gmodification + '/' + Gengine + '/';
}

function cart(id,purchase,price,descr,article,brand)
{	
	var cart_item = $('#cart_item_'+id).val();
	// alert(cart_item);
	
	// $('#cart-next').show();
		
	// alert(id);
	// var	cc = 1;
	var	cc = cart_item;
	
	// $.post('/cart/add?id='+id+'&ccount='+cc+'&price='+price+'&brand='+brand+'&article='+article+'&min=0&timedelivery=24', null,
	$.post('/cart/add?id='+id+'&ccount='+cc+'&price='+price+'&purchase='+purchase+'&descr='+descr+'&article='+article+'&brand='+brand+'&min=0&timedelivery=24', null,
		function(xbox) {
			$.get('/cart/totalsum', null,
			function(xboxtotalsum) { 
				$("#open_shopping_cart").attr("data-amount",xbox);
				$(".total_price").html("&euro;" + xboxtotalsum);
				// $("#go-buy-cart").show();
			});
		});
}
function noticecart(){
	/*$('#cart-ajax-'+id).show();
	$('#cart-ajax-'+id).addClass("animated tada");*/
	//setTimeout('alertAjax('+id+')',1000);
}

$(function(){ // document ready

		  if (!!$('.stickybanner').offset()) { // make sure ".sticky" element exists

		    var stickyTop = $('.stickybanner').offset().top; // returns number 

		    $(window).scroll(function(){ // scroll event

		      var windowTop = $(window).scrollTop(); // returns number 

		      if (stickyTop < windowTop){
		        $('.stickybanner').css({ position: 'fixed', top: '70px' });
		      }
		      else {
		        $('.stickybanner').css('position','static');
		      }

		    });

		  }

		});
