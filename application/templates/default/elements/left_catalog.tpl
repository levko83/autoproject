<!-- .item -->
<?php $currenciesNames = Register::get('currenciesNames');?>
<div class="item-menu radius no-left-border">
	<div class="layout radius inside">
		<div class="h2 uppercase">Корзина</div>
		<ul class="menu-list cart-details">
			<li>Товаров в корзине: <span class="xbox-cart"><?=$xcart?></span></li>
			<li>Общая сумма: <span class="xbox-cart-totalsum"><?=PriceHelper::number($xcarttotalsum)?> <?=$currenciesNames[$curExchangeType]?></span></li>
		</ul>
		<div class="clear"></div>
		<div class="<?=(($xcart>0)?'':'no-display')?>" id="go-buy-cart">
		<center><a href="<?=HTTP_ROOT?>/cart/" class="cart_box_button" style="color:#fff !important;">Перейти в корзину</a></center>
		</div>
		<div class="clear"></div>
	</div>
</div>
<!-- .item -->

<!-- .item -->
<div class="item-menu radius no-left-border">
	<div class="layout radius inside">
		<div class="h2 uppercase"><?=$translates['front.catalog.title']?></div>
		<ul class="menu-list" id="nav">
			<?php if (isset($catalog_main) && count($catalog_main)>0){?>
			<?php foreach ($catalog_main as $cm){?>
			<li <?=(isset($home_select) && ($home_select && in_array($cm['id'],$home_select)))?'class="current"':'class="menu_head"'?>>
				<a href="<?php if($cm['url']){?><?=$cm['url']?><?php }else{?><?=HTTP_ROOT?>/category/<?=AliasViewHelper::doTraslit($cm['name'])?>-<?=$cm['id']?>/<?php }?>">
					<?php if ($cm['img']){?><img width="16px" height="16px" src="<?=StaticimgViewHelper::chk('products',$cm['img'])?>"><?php }?>
					<?=$cm['name']?>
				</a>
			</li>
			<?php }?>
			<?php }?>
		</ul>
		<div class="clear"></div>
	</div>
</div>
<!-- .item -->