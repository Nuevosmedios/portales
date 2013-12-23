<?php 
	defined('_JEXEC') or die;
	$app = JFactory::getApplication();
	$doc = JFactory::getDocument();
	$this->language = $doc->language;
	$this->direction = $doc->direction;
	$sitename = $app->getCfg('sitename');
	$doc->addStyleSheet('templates/'.$this->template.'/css/bootstrap.min.css');
	$doc->addStyleSheet('templates/'.$this->template.'/css/bootstrap-theme.min.css');
	$doc->addStyleSheet('templates/'.$this->template.'/css/font-awesome.min.css');
	$doc->addStyleSheet('templates/'.$this->template.'/css/custom.css');
	$doc->addScript('templates/'.$this->template.'/js/jquery-1.10.2.min.js');
	$doc->addScript('templates/'.$this->template.'/js/jquery-migrate-1.2.1.min');
	$doc->addScript('templates/'.$this->template.'/js/bootstrap.min.js');
	$user = JFactory::getUser();
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<jdoc:include type="head" />
		<!--[if lt IE 9]>
			<script src="<?php echo $this->baseurl ?>/media/jui/js/html5.js"></script>
		<![endif]-->
		<script type="text/javascript">
			var $ = jQuery.noConflict();
		</script>
	</head>
	<body>
		<div id="top" class="row"><div class="container"><div class="pull-right">Faltan x días</div></div></div>
		<div id="top-bar" class="row">
			<div class="container">
				<div class="col-md-7">
					<div id="logo"><jdoc:include type="modules" name="logo" style="none" /></div>
				</div>
				<div class="col-md-5">
					<div id="menu-top-bar"><jdoc:include type="modules" name="menu-top-bar" style="none" /></div>
				</div>
			</div>
		</div>
		<div class="row" id="content-container">
			<div class="container" id="content">
				<div class="row" id="content-back">
					<?php if (JRequest::getVar('view') == 'featured'):?>
						<div class="" id="content-wrapper">
							<div class="">
								<div class="col-md-9"></div>
								<div class="col-md-3">
									<div id="menu">
										<jdoc:include type="modules" name="menu" style="none" />
									</div>
								</div>
							</div>
							<div class="clear">
								<div class="col-md-9 contenedor" id="center-container">
									<jdoc:include type="component" />
								</div>
								<div class="col-md-3 contenedor" id="right-container">
									<jdoc:include type="modules" name="right" style="xhtml" />
								</div>
							</div>
							<div class="overflow-hidden clear" id="banner-senado">
								<div class="col-md-9 text-center"><jdoc:include type="modules" name="footlink1" style="none" /></div>
								<div class="col-md-3 text-center"><jdoc:include type="modules" name="footlink2" style="none" /></div>
							</div>
						</div>
					<?php else: ?>
						<div class="" id="">
							<div class="" id="breadcrumbs"><jdoc:include type="modules" name="breadcrumbs" style="none" /></div>
							<div class="clear">
								<div class="col-md-9"><jdoc:include type="component" /></div>
								<div class="col-md-3"><jdoc:include type="modules" name="right" style="xhtml" /></div>
							</div>
						</div>
					<?php endif; ?>
				</div>
			</div>
			<div class="container" id="footer-container">
				<div class="row" id="footer">
					<div class="text-center">© 2013 - 2014 Todos los derechos reservados Partido de la U y sus candidatos.</div>
				</div>
			</div>
		</div>
	</body>
</html>
<?php unset($this->_scripts[JURI::root(true).'/media/jui/js/bootstrap.min.js']); ?>