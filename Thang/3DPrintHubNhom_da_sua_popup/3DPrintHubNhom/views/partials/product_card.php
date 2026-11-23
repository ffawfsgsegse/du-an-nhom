<?php defined('APP_BOOTSTRAPPED')||exit; ?>
<a class="product-card" href="<?=h(url('product',['id'=>$p['id']]))?>">
 <div class="product-visual">
  <?php if(!empty($p['image_path'])):?><img loading="lazy" src="<?=h($p['image_path'])?>" alt="<?=h($p['name'])?>"><?php else:?><span class="shape" aria-hidden="true">⬡</span><?php endif;?>
  <span class="chip"><?=h($p['category_name'])?></span>
 </div>
 <div class="product-info"><small><?=h($p['shop_name'])?> · <?=h($p['material_name'])?></small><h3><?=h($p['name'])?></h3>
 <div class="product-bottom"><strong><?=money($p['price'])?></strong><span class="round-arrow">↗</span></div></div>
</a>
