<!DOCTYPE html>
<html lang="<?= LANG ?>">
  <head>
    <meta charset="utf-8" />
    <base href="<?= $registry->pub->getRootUrl() ?>" />
    <title><?= $registry->title ?></title>
    <meta name="description" content="<?= $registry->description ?>" />
    <meta name="keywords" content="<?= $registry->keywords ?>" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <!-- other meta set from modules -->
    <?=  $registry->variables('meta') ?>
    <!-- other link tags set from modules -->
    <?=  $registry->variables('head_links') ?>
    <!-- system css -->
    <?=  $registry->variables('css') ?>
    <!-- system js -->
    <?=  $registry->variables('js') ?>
    <link rel="shortcut icon" href="<?= $this->_registry->favicon ?>" />
    <link href='http://fonts.googleapis.com/css?family=Roboto:300,900,700,300italic' rel='stylesheet' type='text/css' />
    <!-- Gino onload function -->
	  <? Loader::import('class', 'Javascript') ?>
    <?= Javascript::onLoadFunction() ?>
    <!-- google analytics -->
    <? if($registry->sysconf->google_analytics): ?>
      <script type="text/javascript">
        var _gaq = _gaq || [];
        _gaq.push(['_setAccount', '<?= $registry->sysconf->google_analytic ?>');
        _gaq.push(['_trackPageview']);
        (function() {
          var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
          ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
          var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
        })();
      </script>
    <? endif ?>
  </head>
  <body>
	{module classid=6 func=viewList}
	  <p>MENU</p>
	  {module classid=4 func=render}
	  <p>BREAD</p>
	  {module classid=4 func=breadCrumbs}
 	{module sysclassid=8 func=printHeaderPublic}
	{module pageid=7 func=full}
  </body>
</html>