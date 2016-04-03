jQuery.extend(jQuery.fn, {
	toplinkwidth: function(){
		var totalWidth = jQuery(document).width();
		var totalContentWidth = jQuery('.resize-wrapper').outerWidth(); // ширина блока с контентом, включая padding
		var totalTopLinkWidth = jQuery(this).children('a').outerWidth(true); // ширина самой кнопки наверх, включая padding и margin
		var h = jQuery(window).width()/2-totalContentWidth/2-totalTopLinkWidth;
		if(h<0){
			// если кнопка не умещается, скрываем её
			jQuery(this).hide();
		} else {
			if($(window).scrollTop() >= 1){
				jQuery(this).show();
			}
			//jQuery(this).css({'padding-right': h+'px'});
		}
	}
});

jQuery(function($){
	var totalWidth = jQuery(document).width();
	var totalContentWidth = jQuery('.resize-wrapper').outerWidth();
	if(20 > (totalWidth-totalContentWidth)){
		$('#top-link').hide();
	}
	
	var topLink = $('#top-link');
	topLink.css({'width': ($(window).width()),'opacity':0.5});
	// если вам не нужно, чтобы кнопка подстраивалась под ширину экрана - удалите следующие четыре строчки в коде
	topLink.toplinkwidth();
	$(window).resize(function(){
		topLink.toplinkwidth();
	});
	$(window).scroll(function() {
		if($(window).scrollTop() >= 1) {
			if(20 > (totalWidth-totalContentWidth)){
				$('#top-link').hide();
			} else {
				topLink.fadeIn(300);
			}
		} else {
			topLink.fadeOut(300);
		}
	});
	topLink.click(function(e) {
		$("body,html").animate({scrollTop: 0}, 500);
		return false;
	});
});