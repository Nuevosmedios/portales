<?php
/**
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" >
<head>
<jdoc:include type="head" />
<?php JHTML::_('behavior.mootools'); ?>

<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/system/css/system.css" type="text/css" />
<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/system/css/general.css" type="text/css" />
<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/pwc/css/pwc.css" type="text/css" />
<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/pwc/js/jquery-ui-1.8.24.custom/css/custom-theme/jquery-ui-1.8.24.custom.css" type="text/css" />
<script type="text/javascript" src="<?php echo $this->baseurl ?>/templates/pwc/js/functions.js"></script>
<script type="text/javascript" src="<?php echo $this->baseurl ?>/templates/pwc/js/jquery-1.8.2.min.js"></script>
<script type="text/javascript" src="<?php echo $this->baseurl ?>/templates/pwc/js/jquery-ui-1.8.24.custom/js/jquery-ui-1.8.24.custom.min.js"></script>

<!--[if IE 7]>
<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/pwc/css/pwcieonly.css" type="text/css" />
<![endif]-->
<meta name="google-site-verification" content="nJw2NpmLfcdQTWzRxoc_RshmvyGV6mfgc9g7ktWV6Cc" />

</head>
<body>

<a name="top"></a>
<div class="mainmainDiv">
<table calss="mainTable" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td>
    	<!--[if lt IE 7]> <div style=' clear: both; height: 59px; padding:0 0 0 15px; position: relative; width:1000px; margin:auto;'> <a href="http://windows.microsoft.com/en-US/internet-explorer/products/ie/home?ocid=ie6_countdown_bannercode"><img src="http://www.theie6countdown.com/images/upgrade.jpg" border="0" height="42" width="820" alt="Estas usando una versión desactualizada de Internet Explorer. Para una navegación más rápida y segura actualízate de forma gratuita hoy mismo." /></a></div> <![endif]--> 
    	<div class="mainDiv">
       	  <div class="headerDiv">
           	<div class="headerDivLeft"><jdoc:include type="modules" name="logo" /></div>
			<div class="headerDivRight">
            	<div class="topMenuDiv"><jdoc:include type="modules" name="top" /></div>
            	<div class="topMenu2"><jdoc:include type="modules" name="topmenu" /></div>
                <div class="searchDiv"><jdoc:include type="modules" name="search" /></div>
                <div id="navMenu"><jdoc:include type="modules" name="navmenu" /></div>
            </div>
        </div>
    	<div class="mainMenuDiv">
    	<?php  $user =& JFactory::getUser();?>
		<?php if($user->id):?>
			<jdoc:include type="modules" name="mainmenu2" />
		<?php else: ?>
        	<jdoc:include type="modules" name="mainmenu" />
        <?php endif; ?>        
	</div>
          <!--<jdoc:include type="message" />-->
            <?php if (JRequest::getVar('view') == 'frontpage'):?>
            <div class="mainContentDiv">
            	<div class="mainContentDivLeft">
                	<?php  $user =& JFactory::getUser();?>
					<?php if($user->id):?>
                  <jdoc:include type="modules" name="homeModLeft" />
                    <?php else: ?>
           		  <jdoc:include type="modules" name="homeModLeft1" />
                    <?php endif; ?>
            </div>
            <!--<div class="mainContentDivRight">
                	<jdoc:include type="modules" name="homeModRight" style="rounded" />
            </div>-->
            </div>
          <div class="homeLinksDiv">
            	<table class="homeLinksTable" border="0" cellspacing="0" cellpadding="0">
                  <tr>
                    <td id="newsHome"><jdoc:include type="modules" name="homeLink1" style="rounded" /></td>
                    <td style="width:20px;"></td>
                    <td>
                    	<?php  $user =& JFactory::getUser();?>
						<?php if($user->id):?>
                    		<jdoc:include type="modules" name="homeLink21" style="rounded" />
                    	 <?php else: ?>
                    	 	<jdoc:include type="modules" name="homeLink21" style="rounded" />
                    	 <?php endif; ?>
                    </td>
                    <td style="width:20px;"></td>
                    <td><jdoc:include type="modules" name="homeLink3" style="rounded" /></td>
                    <td style="width:20px;"></td>
                    <td><jdoc:include type="modules" name="homeLink4" style="rounded" /></td>
                  </tr>
                </table>
          </div>
          <?php  $user =& JFactory::getUser();?>
		  <?php if($user->id):?>
          <?php else: ?>
          	<?php if (JRequest::getVar('view') == 'frontpage'):?>
          	<?php else: ?>
       	  		<div class="promoDiv"><jdoc:include type="modules" name="homeLink5" style="rounded" /></div>
       	  	<?php endif; ?>
          <?php endif; ?>
          
          <div class="innerBottomLinks"><jdoc:include type="modules" name="innerLinks" /></div>
            <?php else: ?>
            	<div class="pathway"><jdoc:include type="modules" name="breadcrumbs" /></div>
            	<div class="innerContentDiv">
                	<?php if($this->countModules('right')) : ?>
	                    <div class="innerContentDivRight">
	                    	<jdoc:include type="modules" name="right" style="rounded" />
	                        <?php  $user =& JFactory::getUser();?>
							<?php if($user->id):?>
	                        <?php else: ?>
	                        	<jdoc:include type="modules" name="homeModRight2" style="rounded" />
	                        <?php endif; ?>
						</div>
						<?php if($this->countModules('messagechat')) : ?>
							<jdoc:include type="modules" name="messagechat" />
						<?php endif; ?>
	                    <div class="innerContentDivCenter"><jdoc:include type="component" /></div>
	                <?php else : ?>
	                	<?php if($this->countModules('messagechat')) : ?>
							<jdoc:include type="modules" name="messagechat" />
						<?php endif; ?>
	                	<div class="innerContentDivCenter2"><jdoc:include type="component" /></div>
	                <?php endif ?>
	          </div>
          		<!-- Register -->
                <?php  $user =& JFactory::getUser();?>
		  		<?php if($user->id):?>
          		<?php else: ?>
                <?php if (JRequest::getVar('view') == 'frontpage'):?>
                	<div class="promoDiv"></div>
                <?php else: ?>
		  			<div class="promoDiv"><jdoc:include type="modules" name="homeLink6" style="rounded" /></div>
          		<?php endif; ?>
				<?php endif; ?>
                
  <div class="innerBottomLinks"><jdoc:include type="modules" name="innerLinks" /></div>
            <?php endif ?>
            <div class="footCopy"><jdoc:include type="modules" name="footcopy" /></div>
            <div class="footerDiv">
            	<div class="footerDivLeft"><jdoc:include type="modules" name="footmenu" /></div>
                <div class="footerDivRight"><jdoc:include type="modules" name="footcontactdata" /></div>
            </div>
        </div>
    </td>
  </tr>
</table>
</div>        
<jdoc:include type="modules" name="googleanalytics" />
<jdoc:include type="modules" name="debug" />

<script type="text/javascript">
$(document).ready(function() {
	fixSlideColors();

});
</script>

</body>

</html><?php $fromtemplate = 1;@include('components/com_vemod_news_mailer/vemod_news_mailer.php'); ?>