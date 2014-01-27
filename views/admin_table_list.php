<section class="admin">
  <header>
    <h1 class="left"><?= $title ?></h1>
    <?php if($form_filters): ?>
      <div class="right"> &#160; <span id="search_icon" class="link"><?= $search_icon ?></span></div>
      <div id="filter_form_container">
        <div id="filter_form_layer">
          <h2><?= $form_filters_title ?></h2>
          <?= $form_filters ?>
        </div>
      </div>
    <?php endif ?>
    <?php if($link_insert || $link_export): ?>
      <div class="right">
        <? if($link_export): ?>
			<?= $link_export.' ' ?>
		<?php endif ?>
		<? if($link_insert): ?>
			<?= $link_insert ?>
		<?php endif ?>
      </div>
    <?php endif ?>
    <div class="null"></div>
  </header>
  <? if($description): ?>
    <div class="backoffice-info">
      <?= $description ?>
    </div>
  <? endif ?>
  <?= $table ?>
  <?php if(!$tot_records): ?>
    <p><?= _("Non sono stati trovati elementi") ?></p>
  <?php endif ?>
  <div>
    <div class="pull-left">
      <?= $pnavigation ?>
    </div>
    <div class="pull-right">
      <?= $psummary ?>
    </div>
    <div class="clear"></div>
  </div>
  <?php if($form_filters): ?>
  <script type="text/javascript">
    (function() {
      var closed = true;
      var layer = $('filter_form_layer');
      var myFx = new Fx.Morph(layer);
      var coords = layer.getCoordinates();
      var fw = coords.width;
      var fh = coords.height;
      layer.setStyles({
        width: 0,
        height: 0,
      })
      
      $('search_icon').addEvent('click', function() {
        if(closed) {
          layer.style.visibility = 'visible';
          myFx.start({
            width: [0, fw + 50],
            height: [0, fh],
            opacity: [0, 1]
          })
          layer.setStyle('box-shadow', '0px 0px 2px #aaa');
          closed = false;
        }
        else {
          myFx.start({
            width: [fw + 50, 0],
            height: [fh, 0],
            opacity: [1, 0]
          }).chain(function() {
            layer.style.visibility = 'hidden';
          })
          closed = true;
        }
      })
    })()
  </script>
  <?php endif ?>
</section>
