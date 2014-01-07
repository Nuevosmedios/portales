<?php
/**
 * @version		$Id: default.php 3411 2013-07-23 15:05:35Z joomlaworks $
 * @package		Frontpage Slideshow
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2013 JoomlaWorks Ltd. All rights reserved.
 * @license		http://www.joomlaworks.net/license
 */

// no direct access
defined('_JEXEC') or die('Restricted access'); ?>

<?php if ($this->showHeading) : ?>
<h2 class="componentheading<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
	<?php echo $this->escape($this->heading); ?>
</h2>
<?php endif; ?>

<?php echo $this->slideshow; ?>
