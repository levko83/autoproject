<?php if (isset($filters) && count($filters)>0){?>
	
	<style>
	.filter { width:100%; display:block; margin-bottom:10px; }
	.filter .filter-name { font-weight:bold; display:block; background:#444444; border-radius:4px; padding:4px 10px; color:#FFF; }
	.filters_contents { max-height:160px; }
	.f-top-x { margin:10px 0 10px; }
	.filter-item { display:block; margin:3px 0px; }
	.inactive { color:#CBCBCB; }
	.t-simple { font-weight:normal; }
	.t-simple a { color:#FFF; font-size:11px; text-decoration:none; }
	.t-simple a:hover { color:#FFF !important; border-bottom:1px dashed #FFF; }
	.filter-price-table { width: 100%; }
	.filter-price-table td { vertical-align:middle; }
	.filter-price-table .filter-price { width:70px; border:0 none !important; }
	.filter-lable { text-align:left; }
	.slide-container { width:210px; display:block; margin:3px auto; }
	</style>
	
	<script type="text/javascript">
	jQuery(document).ready(function(){
		$('.filter-price').number( true, 0, ' ', ' ');
	});
	<?php $maxLimitPrice = (isset($maxLimitPrice)?(ceil(($maxLimitPrice + 100000)/10000)*10000):0);?>
	$(function() {
		$("#slider-range").slider({
			step: 100,
			range: true,
			min: 0,
			max: <?=$maxLimitPrice?>,
			values: [<?=((isset($price_search['from']))?$price_search['from']:(isset($minLimitPrice)?$minLimitPrice:0))?>,<?=((isset($price_search['to']) && $price_search['to'])?$price_search['to']:$maxLimitPrice)?>],
			slide: function(event,ui) {
				$(".pfmin-class").html(ui.values[0]).number( true, 0, ' ', ' ');
				$(".pfmax-class").html(ui.values[1]).number( true, 0, ' ', ' ');
				
				$("#pfmin").val(ui.values[0]);
				$("#pfmax").val(ui.values[1]);
			}
		});
		$(".pfmin-class").html($("#slider-range").slider("values",0)).number( true, 0, ' ', ' ');
		$(".pfmax-class").html($("#slider-range").slider("values",1)).number( true, 0, ' ', ' ');
	});
	</script>
	
	<div class="h1 uppercase f-top-x">Подбор по параметрам</div>
	<form action="" method="GET" id="set_filters_params">
	
	<input type="hidden" id="pfmin" name="price[from]" value="<?=(isset($price_search['from'])?$price_search['from']:0)?>">
	<input type="hidden" id="pfmax" name="price[to]" value="<?=(isset($price_search['to'])?$price_search['to']:0)?>">
	
	<div class="filter">
		<div class="filter-name">Цена</div>
		<div class="clear"></div>
		<div class="filters_contents">
			<div class="slide-container">
				<table class="filter-price-table">
				<tr>
					<td><span class="pfmin-class filter-price"><?=((isset($price_search['from']))?$price_search['from']:(isset($minLimitPrice)?$minLimitPrice:0))?></span></td>
					<td class="t-right"><span class="pfmax-class filter-price"><?=((isset($price_search['to']) && $price_search['to'])?$price_search['to']:(isset($maxLimitPrice)?$maxLimitPrice:0))?></span></td>
				</tr>
				</table>
				<div id="slider-range"></div>
			</div>
		</div>
	</div>

	<?php foreach ($filters as $filter){?>
	<div class="filter">
		<div class="filter-name">
			<?=$filter['name']?> 
			<?php if (isset($allfilters[$filter['id']]) && $allfilters[$filter['id']]){?>
			<?php
				$uri = $_SERVER['REQUEST_URI'];
				$has_params = (strpos($uri, "?") !== false);
				if ($has_params) {
					$uri .= "&";
				}
				else {
					$uri .= "?";
				}
			?>
			<div class="f-right t-simple"><a href="<?=$uri.'inactive[]='.$filter['id']?>">сбросить</a></div>
			<?php }?>
		</div>
		<div class="clear"></div>
		<?php $values = Filters_valuesModel::getByFilter($filter['id'],(isset($pidsall)?$pidsall:array()),$filter['sortby'],$price_search);?>
		<?php if (isset($values) && count($values)>0){?>
		<div class="filters_contents">
			<?php foreach ($values as $dd){?>
			<div class="filter-item <?=(isset($dd['C'])&&$dd['C']>0)?'':'inactive';?>">
				<input type="checkbox" name="filter[<?=$filter['id']?>][]" value="<?=$dd['id']?>" <?=(isset($allfilters[$filter['id']]) && in_array($dd['id'],$allfilters[$filter['id']]))?'checked':'';?> onchange="$('#set_filters_params').submit();">&nbsp;
				<?=$dd['name']?> (<?=$dd['C']?>)
			</div>
			<?php }?>
		</div>
		<?php }?>
	</div>
	<?php }?>
	
	<div class="filter">
		<div class="clear"></div>
		<div class="filters_contents">
		<table class="filter-price-table">
		<tr>
			<td colspan="4" class="t-center"><button type="submit" class="cart_box_button">Подобрать</button></td>
		</tr>
		</table>
		</div>
	</div>
	
	</form>
	
	<script type="text/javascript">
	jQuery(document).ready(function(){
		 $(".filters_contents").mCustomScrollbar({
			scrollButtons:{
				enable:true
			},
			theme:"dark-thin"
		});
	});
	</script>
	
<?php }?>