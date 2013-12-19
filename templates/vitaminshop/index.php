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
	//$doc->addScript('templates/'.$this->template.'/js/jquery.cookie.js');
	$doc->addScript('templates/'.$this->template.'/js/holder.js');
	$doc->addScript('templates/'.$this->template.'/js/bremn.js');
	$user = JFactory::getUser();
	echo JFactory::getUser()->usertype;
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<jdoc:include type="head" />
		<!--[if lt IE 9]>
			<script src="<?php echo $this->baseurl ?>/media/jui/js/html5.js"></script>
		<![endif]-->
		<link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'>
		<script type="text/javascript">
			var $ = jQuery.noConflict();
		</script>
	</head>
	<body>
		<div id="top-message">This site is only for demo purposes of Black Pearl Development and AltosCloud</div>
		<div class=""><jdoc:include type="message" /></div>
		<div class="container" id="body">
			<div class="" id="header">
				<div class="col-md-6" id="logo"><jdoc:include type="modules" name="logo" style="none" /></div>
				<div class="col-md-6" id="toplinks">
					<ul class="list-unstyled list-inline pull-right">
						<?php if($user->id):?>
							<li id="toplink1-1"><jdoc:include type="modules" name="toplinks1.1" style="none" /></li>
						<?php else: ?>
							<li id="toplink1"><jdoc:include type="modules" name="toplinks1" style="none" /></li>
						<?php endif; ?>
						<li class="hidden-xs" id="toplink2"><jdoc:include type="modules" name="toplinks2" style="none" /></li>
					</ul>
				</div>
			</div>
			<div class="clear row hidden-xs" id="menu">
				<ul class="nav nav-tabs text-center">
					<li class="active"><a href="#vitamins" data-toggle="tab">vitamins supplements, herbs & more</a></li>
					<li><a href="#sports" data-toggle="tab">sports nutrition & workout</a></li>
					<li><a href="#healthy" data-toggle="tab">healthy home</a></li>
					<li><a href="#bath" data-toggle="tab">bath & beauty</a></li>
					<li><a href="#shop" data-toggle="tab">shop by health concern</a></li>
				</ul>
				<div class="tab-content">
					<div class="tab-pane active" id="vitamins"><jdoc:include type="modules" name="menu1" style="none" /></div>
					<div class="tab-pane" id="sports"><jdoc:include type="modules" name="menu2" style="none" /></div>
					<div class="tab-pane" id="healthy"><jdoc:include type="modules" name="menu3" style="none" /></div>
					<div class="tab-pane" id="bath"><jdoc:include type="modules" name="menu4" style="none" /></div>
					<div class="tab-pane" id="shop"><jdoc:include type="modules" name="menu5" style="none" /></div>
				</div>
			</div>
			<div class="panel-group visible-xs" id="menu-accordion">
				<div class="panel panel-default">
					<div class="panel-heading">
						<h4 class="panel-title">
							<a data-toggle="collapse" data-parent="#menu-accordion" href="#collapseVitamin">
								vitamins supplements, herbs & more <i class="fa fa-chevron-circle-right pull-right"></i>
							</a>
						</h4>
					</div>
					<div id="collapseVitamin" class="panel-collapse collapse">
						<div class="panel-body">
							<jdoc:include type="modules" name="menu1" style="none" />
						</div>
					</div>
				</div>
				<div class="panel panel-default">
					<div class="panel-heading">
						<h4 class="panel-title">
							<a data-toggle="collapse" data-parent="#menu-accordion" href="#collapseSport">
								sports nutrition & workout <i class="fa fa-chevron-circle-right pull-right"></i>
							</a>
						</h4>
					</div>
					<div id="collapseSport" class="panel-collapse collapse">
						<div class="panel-body">
							<jdoc:include type="modules" name="menu2" style="none" />
						</div>
					</div>
				</div>
				<div class="panel panel-default">
					<div class="panel-heading">
						<h4 class="panel-title">
							<a data-toggle="collapse" data-parent="#menu-accordion" href="#collapseHealth">
								healthy home <i class="fa fa-chevron-circle-right pull-right"></i>
							</a>
						</h4>
					</div>
					<div id="collapseHealth" class="panel-collapse collapse">
						<div class="panel-body">
							<jdoc:include type="modules" name="menu3" style="none" />
						</div>
					</div>
				</div>
				<div class="panel panel-default">
					<div class="panel-heading">
						<h4 class="panel-title">
							<a data-toggle="collapse" data-parent="#menu-accordion" href="#collapseBath">
								bath & beauty <i class="fa fa-chevron-circle-right pull-right"></i>
							</a>
						</h4>
					</div>
					<div id="collapseBath" class="panel-collapse collapse">
						<div class="panel-body">
							<jdoc:include type="modules" name="menu4" style="none" />
						</div>
					</div>
				</div>
				<div class="panel panel-default">
					<div class="panel-heading">
						<h4 class="panel-title">
							<a data-toggle="collapse" data-parent="#menu-accordion" href="#collapseShop">
								shop by health concern <i class="fa fa-chevron-circle-right pull-right"></i>
							</a>
						</h4>
					</div>
					<div id="collapseShop" class="panel-collapse collapse">
						<div class="panel-body">
							<jdoc:include type="modules" name="menu5" style="none" />
						</div>
					</div>
				</div>
			</div>
			<?php if (JRequest::getVar('view') == 'featured'):?>
				<div class="container" id="promo-container">
					<div class="col-md-9 carousel slide" id="banner" data-ride="carousel">
						<ol class="carousel-indicators">
							<li data-target="#banner" data-slide-to="0" class="active"></li>
							<li data-target="#banner" data-slide-to="1"></li>
						</ol>
						<div class="carousel-inner">
							<jdoc:include type="modules" name="banner" style="none" />
						</div>
					</div>
					<div class="col-md-3" id="promo"><jdoc:include type="modules" name="promo" style="none" /></div>
				</div>
				<div class="container" id="promos-container">
					<div class="col-md-3" id="promo1"><jdoc:include type="modules" name="promo1" style="none" /></div>
					<div class="col-md-3" id="promo2"><jdoc:include type="modules" name="promo2" style="none" /></div>
					<div class="col-md-3" id="promo3"><jdoc:include type="modules" name="promo3" style="none" /></div>
					<div class="col-md-3" id="promo4"><jdoc:include type="modules" name="promo4" style="none" /></div>
				</div>
			<?php else: ?>
				<div class="conatiner">
					<jdoc:include type="component" />
				</div>
			<?php endif; ?>
			<div class="row"><hr /></div>
			<div class="row"></div>
			<div class="row" id="footer">
				<div class="container">
					<jdoc:include type="modules" name="footer" style="none" />
				</div>
			</div>
		</div>
		<?php if($user->id):?>
			<div class="hidden" id="user-id"><?php echo $user->id; ?></div>
		<?php else: ?>
			<!--<div class="hidden" id="user-id">none</div>-->
		<?php endif; ?>
		<?php if($user->id):?>
			<script type="text/javascript">
				function getUserLoyaltyPoints(uid){
					var API = "http://" + window.location.hostname + "/~pablocpantoja/vitaminshop/components/com_store/api.php";
					$.ajax({  
						url: API,
						dataType: 'json',
						data: { "action":"getUserLoyaltyPoints", "uid":uid },
						async: true,
						success: function(data){
							$('#loyalty-points').append('<i class="fa fa-trophy"></i> Points: ' + data[0].points);
							jQuery.cookie('loyal-points',data[0].points);
						}
					});
				}
				getUserLoyaltyPoints(<?php echo $user->id; ?>);
			</script>
		<?php endif; ?>
		<script type="text/javascript" src="<?php echo 'templates/'.$this->template.'/js/jquery.cookie.js' ?>"></script>
		<script type="text/javascript">
			$.cookie.json = true;
			//$.cookie('shoppingcart','');
			$('.logout-button').find('.btn').addClass('btn-sm');
			$('input[type="text"]').addClass('form-control');
			$('input[type="password"]').addClass('form-control');
			$('input[type="email"]').addClass('form-control');
			$('textarea').addClass('form-control');
			$('label').attr('data-toggle','tooltip').removeClass('hasTooltip').attr('data-html','true');
			$(document).on('ready', function(){
				$('[data-toggle=tooltip]').tooltip();
				$('[data-toggle=popover]').popover({'trigger':'click'});
				
			});
			
			var disp = 0;
			
			window.session = {
				options: {
					use_html5_location: false,
					session_timeout: 5
				},
				start: function(data) {
					//console.log(data.device.is_tablet);
					if(!data.device.is_tablet && !data.device.is_phone && !data.device.is_mobile){
						//console.log("Web");
						jQuery('#disp').append("Web");
						disp = "Web";
						jQuery.cookie('disp','2');
					} else if(data.device.is_tablet){
						//console.log('Tablet');
						jQuery('#disp').append("Tablet");
						disp = "Tablet";
						jQuery.cookie('disp','3');
					} else if(data.device.is_phone || data.device.is_mobile){
						//console.log('Phone');
						jQuery('#disp').append("Phone");
						disp = "Phone";
						jQuery.cookie('disp','3');
					}
				}
			};
			
		</script>
		<script type="text/javascript" src="<?php echo 'templates/'.$this->template.'/js/session.js' ?>"></script>
	</body>
</html>