<?php
/**
* CHRONOFORMS version 4.0
* Copyright (c) 2006 - 2011 Chrono_Man, ChronoEngine.com. All rights reserved.
* Author: Chrono_Man (ChronoEngine.com)
* @license		GNU/GPL
* Visit http://www.ChronoEngine.com for regular updates and information.
**/
defined('_JEXEC') or die('Restricted access');
$mainframe = JFactory::getApplication();
$uri = JFactory::getURI();
?>
<?php $i = 4; ?>
<ul class="nav nav-tabs">
	<li class="active"><a href="#captcha_<?php echo $i; ?>" data-toggle="tab">Captcha Settings</a></li>
</ul>
<?php echo '<div class="tab-content">'; ?>
<?php echo '<div class="tab-pane'.(true ? ' active' : '').'" id="captcha_'.$i.'">'; ?>
	<?php echo $HtmlHelper->input('chronoaction['.$i.'][action_check_captcha_'.$i.'_enabled]', array('type' => 'select', 'label' => 'Enabled', 'options' => array(0 => 'No', 1 => 'Yes'), 'smalldesc' => "You must have a 'Captcha input' under the preview tab in order for this to work.")); ?>
	<?php echo $HtmlHelper->input('chronoaction['.$i.'][action_check_captcha_'.$i.'_error]', array('type' => 'text', 'label' => 'Error Message', 'class' => 'medium_input', 'value' => '')); ?>

	<?php echo $HtmlHelper->input('chronoaction_id['.$i.']', array('type' => 'hidden', 'value' => $i)); ?>
	<?php echo $HtmlHelper->input('chronoaction['.$i.'][type]', array('type' => 'hidden', 'value' => 'check_captcha')); ?>

	<?php $i = 2; ?>
	<?php echo $HtmlHelper->input('chronoaction['.$i.'][action_load_captcha_'.$i.'_fonts]', array('type' => 'select', 'label' => "Use True Type Fonts", 'options' => array(0 => 'No', 1 => 'Yes'), 'smalldesc' => 'True type fonts is better looking but it depends on the GD library config at your server, most users can enable this safely.')); ?>
	<?php echo $HtmlHelper->input('chronoaction['.$i.'][action_load_captcha_'.$i.'_enabled]', array('type' => 'hidden', 'value' => 1)); ?>

	<?php echo $HtmlHelper->input('chronoaction_id['.$i.']', array('type' => 'hidden', 'value' => $i)); ?>
	<?php echo $HtmlHelper->input('chronoaction['.$i.'][type]', array('type' => 'hidden', 'value' => 'load_captcha')); ?>
<?php echo '</div>'; ?>
<?php echo '</div>'; ?>

