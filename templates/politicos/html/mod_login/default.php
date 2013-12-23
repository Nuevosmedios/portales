<?php
/**
 * @package     Joomla.Site
 * @subpackage  Templates.beez3
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
?>
<?php if ($type == 'logout') : ?>
	<form class="form-horizontal" action="<?php echo JRoute::_('index.php', true, $params->get('usesecure')); ?>" method="post" id="login-form" role="form">
	<?php if ($params->get('greeting')) : ?>
		<div class="login-greeting form-group">
		<?php if ($params->get('name') == 0) : ?>
			<?php echo JText::sprintf('MOD_LOGIN_HINAME', htmlspecialchars($user->get('name'))); ?>
		<?php else : ?>
		 	<?php echo JText::sprintf('MOD_LOGIN_HINAME', htmlspecialchars($user->get('username'))); ?>
		<?php endif; ?>
		</div>
	<?php endif; ?>
	<div class="logout-button form-group">
		<input type="submit" name="Submit" class="button" value="<?php echo JText::_('JLOGOUT'); ?>" />
		<input type="hidden" name="option" value="com_users" />
		<input type="hidden" name="task" value="user.logout" />
		<input type="hidden" name="return" value="<?php echo $return; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
	</form>
<?php else : ?>
	<form class="form-horizontal" action="<?php echo JRoute::_('index.php', true, $params->get('usesecure')); ?>" method="post" id="login-form" role="form">
	<?php if ($params->get('pretext')): ?>
		<div class="pretext">
		<p><?php echo $params->get('pretext'); ?></p>
		</div>
	<?php endif; ?>
	
	<div class="form-group" id="form-login-username">
		<label class="col-sm-4 control-label" for="modlgn-username"><?php echo JText::_('MOD_LOGIN_VALUE_USERNAME') ?></label>
		<div class="col-sm-8">
			<input class="form-control" id="modlgn-username" type="text" name="username" class="inputbox"  size="18" />
		</div>
	</div>
	<div class="form-group" id="form-login-password">
		<label class="col-sm-4 control-label" for="modlgn-passwd"><?php echo JText::_('JGLOBAL_PASSWORD') ?></label>
		<div class="col-sm-8">
			<input class="form-control" id="modlgn-passwd" type="password" name="password" class="inputbox" size="18"  />
		</div>
	</div>
	<?php if (count($twofactormethods) > 1): ?>
		<div id="form-login-secretkey" class="control-group">
			<div class="controls">
				<?php if (!$params->get('usetext')) : ?>
					<div class="input-prepend input-append">
						<span class="add-on">
								<label for="modlgn-secretkey" class="element-invisible"><?php echo JText::_('JGLOBAL_SECRETKEY'); ?>
							</label>
						</span>
						<input id="modlgn-secretkey" type="text" name="secretkey" class="input-small" tabindex="0" size="18" />
				</div>
				<?php else: ?>
					<label for="modlgn-secretkey"><?php echo JText::_('JGLOBAL_SECRETKEY') ?></label>
					<input id="modlgn-secretkey" type="text" name="secretkey" class="input-small" tabindex="0" size="18" />
				<?php endif; ?>
			</div>
		</div>
	<?php endif; ?>
	<?php if (JPluginHelper::isEnabled('system', 'remember')) : ?>
		<div class="form-group"id="form-login-remember">
			<div class="col-sm-offset-4 col-sm-10">
				<div class="checkbox">
					<label for="modlgn-remember">
						<input id="modlgn-remember" type="checkbox" name="remember" class="inputbox" value="yes"/> <?php echo JText::_('MOD_LOGIN_REMEMBER_ME') ?>
					</label>
				</div>
			</div>
		</div>
	<?php endif; ?>
	<div class="form-group">
		<div class="col-sm-offset-4 col-sm-10">
			<input type="submit" name="Submit" class="btn btn-success" value="<?php echo JText::_('JLOGIN') ?>" />
		</div>
	</div>
	<input type="hidden" name="option" value="com_users" />
	<input type="hidden" name="task" value="user.login" />
	<input type="hidden" name="return" value="<?php echo $return; ?>" />
	<?php echo JHtml::_('form.token'); ?>
	<div class="container">
		<ul class="list-unstyled">
			
			<?php $usersConfig = JComponentHelper::getParams('com_users'); ?>
			<?php if ($usersConfig->get('allowUserRegistration')) : ?>
				<li>
					<a href="<?php echo JRoute::_('index.php?option=com_users&view=registration'); ?>">
						<?php echo JText::_('MOD_LOGIN_REGISTER'); ?></a>
				</li>
			<?php endif; ?>
		</ul>
	</div>
	<?php if ($params->get('posttext')): ?>
		<div class="posttext">
		<p><?php echo $params->get('posttext'); ?></p>
		</div>
	<?php endif; ?>
	
	</form>
<?php endif; ?>
