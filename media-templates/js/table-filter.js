$(document).ready(function() {
	$('.filter-search').show();
	$('.filter-query').val('Фильтр, нажмите для поиска');
	$('.filter-query').keyup(function(event){
		$('.show_tr_more').remove();
		if (event.keyCode == 27 || $(this).val() == '') {
			$(this).val('');
			$('.filterResultTable tbody tr').removeClass('visible').show().addClass('visible');
		}
		else {
			filter('.filterResultTable tbody tr', $(this).val());
		}
		$('.swtchnode').remove();
		$('.details-list td').removeClass('hc-pd');
	});
});
function filter(selector, query) {
	query =	$.trim(query);
	query = query.replace(/ /gi, '|');
	$(selector).each(function() {
		($(this).text().search(new RegExp(query, "i")) < 0) ? $(this).hide().removeClass('visible') : $(this).show().addClass('visible');
	});
}
function repeatact(){
	filter('.filterResultTable tbody tr', $('.filter-query').val());
}