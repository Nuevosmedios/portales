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
		<script type="text/javascript">
			var $ = jQuery.noConflict();
		</script>
	</head>
	<body>
		<div id="body">
			<div class="container" id="main">
				<div class="" id="header">
					<div class="col-md-4" id="logo"><jdoc:include type="modules" name="logo" style="none" /></div>
					<div class="col-md-8" id="header-right">
						<div class="pull-right col-md-4">
							<jdoc:include type="modules" name="search" style="none" />
						</div>
						<div class="pull-right" id="top-menu">
							<jdoc:include type="modules" name="topmenu" style="none" />
						</div>
					</div>
				</div>
				<div class="clear">
					<nav class="navbar navbar-default" role="navigation">
						<div class="navbar-header">
							<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#mainmenu">
								<span class="sr-only">Toggle navigation</span>
								<span class="icon-bar"></span>
								<span class="icon-bar"></span>
								<span class="icon-bar"></span>
							</button>
						</div>
						<div class="collapse navbar-collapse" id="mainmenu">
							<jdoc:include type="modules" name="menu" style="none" />
						</div>
					</nav>
				</div>
				<div class="clear" id="banner">
					<jdoc:include type="modules" name="banner" style="none" />
				</div>
				<div class="clear" id="homelinks">
					<div class="col-md-4" id="homelink1"><jdoc:include type="modules" name="homelink1" style="none" /></div>
					<div class="col-md-4" id="homelink2"><jdoc:include type="modules" name="homelink2" style="none" /></div>
					<div class="col-md-4" id="homelink3"><jdoc:include type="modules" name="homelink3" style="none" /></div>
				</div>
				<div class="clear" id="footcopy">
					<jdoc:include type="modules" name="footcopy" style="none" />
				</div>
			</div>
		</div>
		<jdoc:include type="modules" name="googleanalytics" style="none" />
	</body>
</html>
<?php unset($this->_scripts[JURI::root(true).'/media/jui/js/bootstrap.min.js']); ?>