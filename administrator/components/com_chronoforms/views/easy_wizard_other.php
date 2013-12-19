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
<ul class="nav nav-tabs">
	<li class="active"><a href="#tools_18" data-toggle="tab">Tools</a></li>
	<li><a href="#redirect_20" data-toggle="tab">Redirect</a></li>
	<li><a href="#js_css" data-toggle="tab">JS/CSS Settings</a></li>
	<li><a href="#custom_php" data-toggle="tab">Custom PHP code</a></li>
</ul>
<?php echo '<div class="tab-content">'; ?>
<?php echo '<div class="tab-pane'.(true ? ' active' : '').'" id="tools_18">'; ?>
	<?php $i = 18; ?>
	<?php echo $HtmlHelper->input('chronoaction['.$i.'][action_debugger_'.$i.'_enabled]', array('type' => 'select', 'label' => 'Enable Debug', 'options' => array(0 => 'No', 1 => 'Yes'), 'smalldesc' => 'Show debug report after form submission.')); ?>			
	
	<?php echo $HtmlHelper->input('chronoaction_id['.$i.']', array('type' => 'hidden', 'value' => $i)); ?>
	<?php echo $HtmlHelper->input('chronoaction['.$i.'][type]', array('type' => 'hidden', 'value' => 'debugger')); ?>
<?php echo '</div>'; ?>
<?php echo '<div class="tab-pane'.(false ? ' active' : '').'" id="redirect_20">'; ?>
	<?php $i = 20; ?>
	<?php echo $HtmlHelper->input('chronoaction['.$i.'][action_redirect_user_'.$i.'_enabled]', array('type' => 'select', 'label' => 'Enable Redirect', 'options' => array(0 => 'No', 1 => 'Yes'))); ?>			
	<?php echo $HtmlHelper->input('chronoaction['.$i.'][action_redirect_user_'.$i.'_target_url]', array('type' => 'text', 'label' => "Target URL", 'class' => 'big_input', 'smalldesc' => 'The URL to which the user will be redirected after submitting the form.')); ?>
	
	<?php echo $HtmlHelper->input('chronoaction_id['.$i.']', array('type' => 'hidden', 'value' => $i)); ?>
	<?php echo $HtmlHelper->input('chronoaction['.$i.'][type]', array('type' => 'hidden', 'value' => 'redirect_user')); ?>
<?php echo '</div>'; ?>
<?php echo '<div class="tab-pane'.(false ? ' active' : '').'" id="js_css">'; ?>
	<?php $i = 0; ?>
	<?php echo $HtmlHelper->input('chronoaction['.$i.'][action_load_js_'.$i.'_content1]', array('type' => 'textarea', 'label' => "JavaScript Code", 'rows' => 20, 'cols' => 85, 'style' => 'width: 600px; height: 300px;', 'smalldesc' => 'No script tags.')); ?>
	<?php echo $HtmlHelper->input('chronoaction['.$i.'][action_load_js_'.$i.'_enabled]', array('type' => 'hidden', 'value' => 1)); ?>
	<?php echo $HtmlHelper->input('chronoaction_id['.$i.']', array('type' => 'hidden', 'value' => $i)); ?>
	<?php echo $HtmlHelper->input('chronoaction['.$i.'][type]', array('type' => 'hidden', 'value' => 'load_js')); ?>
	
	<?php $i = 1; ?>
	<?php echo $HtmlHelper->input('chronoaction['.$i.'][action_load_css_'.$i.'_content1]', array('type' => 'textarea', 'label' => "CSS Code", 'rows' => 20, 'cols' => 85, 'style' => 'width: 600px; height: 300px;', 'smalldesc' => 'No style tags.')); ?>
	<?php echo $HtmlHelper->input('chronoaction['.$i.'][action_load_css_'.$i.'_enabled]', array('type' => 'hidden', 'value' => 1)); ?>
	<?php echo $HtmlHelper->input('chronoaction_id['.$i.']', array('type' => 'hidden', 'value' => $i)); ?>
	<?php echo $HtmlHelper->input('chronoaction['.$i.'][type]', array('type' => 'hidden', 'value' => 'load_css')); ?>
	
<?php echo '</div>'; ?>

<?php echo '<div class="tab-pane'.(false ? ' active' : '').'" id="custom_php">'; ?>
	<?php $i = 8; ?>
	<?php echo $HtmlHelper->input('chronoaction['.$i.'][action_custom_code_'.$i.'_content1]', array('type' => 'textarea', 'label' => "Before Emails Code", 'rows' => 20, 'cols' => 85, 'style' => 'width: 600px; height: 300px;', 'smalldesc' => 'a chunk of PHP code which will be executed before your emails are sent.')); ?>
	<?php echo $HtmlHelper->input('chronoaction['.$i.'][action_custom_code_'.$i.'_enabled]', array('type' => 'hidden', 'value' => 1)); ?>
	<?php echo $HtmlHelper->input('chronoaction_id['.$i.']', array('type' => 'hidden', 'value' => $i)); ?>
	<?php echo $HtmlHelper->input('chronoaction['.$i.'][type]', array('type' => 'hidden', 'value' => 'custom_code')); ?>
	
	<?php $i = 13; ?>
	<?php echo $HtmlHelper->input('chronoaction['.$i.'][action_custom_code_'.$i.'_content1]', array('type' => 'textarea', 'label' => "After Emails Code", 'rows' => 20, 'cols' => 85, 'style' => 'width: 600px; height: 300px;', 'smalldesc' => 'a chunk of PHP code which will be executed after your emails have been sent.')); ?>
	<?php echo $HtmlHelper->input('chronoaction['.$i.'][action_custom_code_'.$i.'_enabled]', array('type' => 'hidden', 'value' => 1)); ?>
	<?php echo $HtmlHelper->input('chronoaction_id['.$i.']', array('type' => 'hidden', 'value' => $i)); ?>
	<?php echo $HtmlHelper->input('chronoaction['.$i.'][type]', array('type' => 'hidden', 'value' => 'custom_code')); ?>
	
<?php echo '</div>'; ?>
<?php echo '</div>'; ?>