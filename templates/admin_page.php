<!DOCTYPE html>
<html lang="<?= LANG ?>">
	<head>
		<meta charset="utf-8" />
		<base href="<?= $registry->request->root_absolute_url ?>" />
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
		<link type="text/css" rel="stylesheet" href="css/admin.css" />
		<!-- core js -->
		<?=  $registry->variables('core_js') ?>
		<!-- apps js -->
		<?=  $registry->variables('js') ?>
		<?= \Gino\Document::errorMessages() ?>
		<link rel="shortcut icon" href="<?= $this->_registry->favicon ?>" />
		<link href='https://fonts.googleapis.com/css?family=Roboto:300,900,700,300italic' rel='stylesheet' type='text/css' />
		<!-- Gino onload function -->
		<?= \Gino\Javascript::onLoadFunction() ?>
	</head>
	<body>
		<!-- top bar -->
		<nav class="navbar-inverse navbar-fixed-top" role="navigation">
			<div class="container-fluid admin-header">
				<!-- Brand and toggle get grouped for better mobile display -->
				<div class="navbar-header">
					<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#menu-admin-container" aria-expanded="false">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					</button>
					<a class="navbar-brand" href="#" title="Otto">
						<img alt="Logo Otto" src="img/logo.png" style="width: 105px; height: 50px;" />
					</a>
				</div>
				
				<div class="navbar-tools">
					<!-- Collect the nav links, forms, and other content for toggling -->
					<div class="collapse navbar-collapse" id="menu-admin-container" style="overflow: auto;">
					{module classid=5 func=render}
					</div><!-- /.navbar-collapse -->
				</div>
			</div>
		</nav>

		<!-- sidenav -->
		{module sysclassid=12 func=sidenav}

		<div class="container">
			{module id=0}
		</div>
	</body>
</html>
