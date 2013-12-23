<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php
   // Add 
   $baseurl = JURI::base( true);
   if ( substr( $baseurl, -10) == '/index.php') { $baseurl = substr( $baseurl, 0, strlen( $baseurl)-10); }
   $jpath_base = dirname( dirname( dirname( dirname( dirname( dirname( __FILE__))))));
   $templateurl = $baseurl
                . str_replace( DS, '/', str_replace( $jpath_base, '', dirname( __FILE__)))
                ;

	$document = & JFactory::getDocument();
	$document->addStyleSheet( $templateurl.'/error.css');
?>
<div id="multisites-error">
<div class="err-bg">
<div class="err-left">
<div class="err-right">
<div class="err-top">
<div class="err-tl">
<div class="err-tr">
<div class="err-bottom">
<div class="err-bl">
<div class="err-br">

   <div class="err-area">
      <div id="multisites-error-type-<?php echo $this->errorType; ?>" class="err-type">
         <div class="err-type-title"><?php echo JText::_( $this->errorType) .'!'; ?></div>
         <div class="err-Msg"><?php echo $this->errorMsg; ?></div>
      </div>
   </div>
   
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
